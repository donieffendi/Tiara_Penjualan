<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TCetakLabelHargaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Cetak Label Harga';
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return view("otransaksi_TCetakLabelHarga.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TCetakLabelHarga.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            if (is_array($periode)) {
                $periodeDisplay = ($periode['bulan'] ?? '01') . '/' . ($periode['tahun'] ?? date('Y'));
            } else {
                $periodeDisplay = $periode;
            }

            return view("otransaksi_TCetakLabelHarga.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periodeDisplay,
                'username' => $username
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TCetakLabelHarga index: ' . $e->getMessage());
            return view("otransaksi_TCetakLabelHarga.index")->with([
                'judul' => 'Cetak Label Harga',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            if (!$CBG) {
                return response()->json(['error' => 'Akses ditolak'], 403);
            }

            $jenis = strtoupper($request->input('jenis', 'BIASA'));
            $kode = trim($request->input('kode', ''));
            $sub = trim($request->input('sub', ''));
            $tanggal = $request->input('tanggal', date('Y-m-d'));
            $kali = (int) $request->input('kali', 1);

            $data = collect([]);

            if ($jenis === 'FF') {
                // Fast Forward - Perubahan LPH
                $data = $this->getDataFF($CBG, $tanggal);
            } else {
                // Biasa - Berdasarkan kode atau sub
                if (!empty($kode)) {
                    $data = $this->getDataByKode($CBG, $kode, $kali);
                } elseif (!empty($sub)) {
                    $data = $this->getDataBySub($CBG, $sub);
                }
            }

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($row) {
                    static $index = 0;
                    return ++$index;
                })
                ->editColumn('hjbr', function ($row) {
                    return number_format($row->hjbr ?? 0, 0, ',', '.');
                })
                ->editColumn('lph', function ($row) {
                    return number_format($row->lph ?? 0, 2, ',', '.');
                })
                ->editColumn('dtr', function ($row) {
                    return number_format($row->dtr ?? 0, 0, ',', '.');
                })
                ->editColumn('ON_DC', function ($row) {
                    return $row->ON_DC === 'L' ?
                        '<span class="badge badge-success">LAKU</span>' :
                        '<span class="badge badge-warning">DC</span>';
                })
                ->rawColumns(['ON_DC'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getDataFF($CBG, $tanggal)
    {
        $query = "
            SELECT 
                C.UR as NO_BUKTI,
                A.KD_BRG as kode,
                A.NA_BRG as uraian,
                konversi_harga_ons(A.KD_BRG, B.HJ) as hjbr,
                DATE_FORMAT(CURDATE(), '%d/%m/%y') as pers,
                A.BARCODE,
                A.sub,
                A.KDBAR,
                A.KET_UK,
                konversi_kms_ons(A.KD_BRG, A.KET_KEM) as KET_KEM,
                B.KDLAKU,
                B.LPH,
                A.SP_L,
                A.SP_LF,
                IF(A.ON_DC = 0, 'L', 'D') ON_DC,
                B.DTR,
                IF(B.KDLAKU = 8, A.TARIK, 0) TARIK,
                IF(ROUND(A.DTB * B.LPH) < 3, 
                   3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), 
                   CEILING(A.DTB * B.LPH)) DTR_IDEALX,
                COALESCE(D.SUSUN, 0) as SUSUN,
                COALESCE(D.MUKA, 0) as MUKA,
                COALESCE(D.DTR_1M / D.SUSUN, 0) as KAPRAK,
                COALESCE(D.DTR_1M, 0) as DTR_1M,
                COALESCE(D.DTR, 0) as DTR_DC,
                COALESCE(D.TANDA, '') as TANDA,
                IF(LEFT(A.NA_BRG, 1) = '3', 
                   CONCAT(B.DTR, ' / ', IF(ROUND(A.DTB * B.LPH) < 3, 3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), CEILING(A.DTB * B.LPH))),
                   CONCAT(COALESCE(D.MUKA, 0), ' (', ROUND(COALESCE(D.SUSUN, 0)), '.', 
                          ROUND(COALESCE(D.DTR_1M, 0)), ') ', COALESCE(D.TANDA, ''))) BEDA,
                IF(A.TARIK > 0, CONCAT(A.TARIK_TIPE, A.TARIK), '') AS MASA_TARIK,
                A.DTB,
                A.DTB as dtb
            FROM brg A
            INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
            INNER JOIN tgz.lphkode3 C ON A.KD_BRG = C.KD_BRG
            LEFT JOIN brg_dc_ts D ON C.KD_BRG = D.KD_BRG
            WHERE B.cbg = ? 
                AND B.yer = YEAR(NOW()) 
                AND DATE(B.TGL_LPH) = ?
                AND LEFT(A.BARCODE, 1) <> '#'
                AND IF(? = 'TGZ', C.LPH_GZ_LL <> C.LPHGZ,
                    IF(? = 'TMM', C.LPH_TMM_LL <> C.LPHTMM, C.LPH_KG_LL <> C.LPHKG))
            ORDER BY A.KD_BRG
        ";

        $results = DB::select($query, [$CBG, $tanggal, $CBG, $CBG]);
        return collect($results);
    }

    private function getDataByKode($CBG, $kode, $kali = 1)
    {
        $prefix = strtoupper(substr($kode, 0, 2));

        if ($prefix === 'UH' || $prefix === 'U3') {
            return $this->getDataUsulan($CBG, $kode, $prefix);
        } elseif ($prefix === 'UK') {
            return $this->getDataUsulanKapasitas($CBG, $kode);
        } elseif ($prefix === 'UD' || $prefix === 'US') {
            return $this->getDataUsulanDC($CBG, $kode);
        } else {
            return $this->getDataBarangBiasa($CBG, $kode, $kali);
        }
    }

    private function getDataUsulan($CBG, $noBukti, $prefix)
    {
        $dtrField = $prefix === 'U3' ? 'B.DTR' : 'D.DTR';

        $query = "
            SELECT 
                C.NO_BUKTI,
                C.kode,
                C.uraian,
                konversi_harga_ons(C.kode, C.HJBR) as hjbr,
                DATE_FORMAT(CURDATE(), '%d/%m/%y') as pers,
                A.BARCODE,
                A.sub,
                A.KDBAR,
                A.KET_UK,
                konversi_kms_ons(C.kode, A.KET_KEM) as KET_KEM,
                B.KDLAKU,
                B.LPH,
                A.SP_L,
                A.SP_LF,
                IF(A.ON_DC = 0, 'L', 'D') ON_DC,
                B.DTR,
                IF(B.KDLAKU = 8, A.TARIK, 0) TARIK,
                IF(ROUND(A.DTB * B.LPH) < 3, 
                   3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), 
                   CEILING(A.DTB * B.LPH)) DTR_IDEALX,
                COALESCE(D.SUSUN, 0) as SUSUN,
                COALESCE(D.MUKA, 0) as MUKA,
                COALESCE(D.DTR_1M / D.SUSUN, 0) as KAPRAK,
                COALESCE(D.DTR_1M, 0) as DTR_1M,
                COALESCE({$dtrField}, 0) as DTR_DC,
                COALESCE(D.TANDA, '') as TANDA,
                IF(LEFT(A.NA_BRG, 1) = '3',
                   CONCAT(B.DTR, ' / ', IF(ROUND(A.DTB * B.LPH) < 3, 3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), CEILING(A.DTB * B.LPH))),
                   CONCAT(COALESCE(D.MUKA, 0), ' (', ROUND(COALESCE(D.SUSUN, 0)), '.', 
                          ROUND(COALESCE(D.DTR_1M, 0)), ') ', COALESCE(D.TANDA, ''))) BEDA,
                IF(A.TARIK > 0, CONCAT(A.TARIK_TIPE, A.TARIK), '') AS MASA_TARIK,
                A.DTB,
                A.DTB as dtb
            FROM brg A
            INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
            INNER JOIN histod C ON A.KD_BRG = C.KODE
            LEFT JOIN brg_dc_ts D ON C.KODE = D.KD_BRG
            WHERE B.cbg = ? 
                AND B.yer = YEAR(NOW()) 
                AND C.NO_BUKTI = ?
                AND LEFT(A.BARCODE, 1) <> '#'
            ORDER BY C.kode
        ";

        $results = DB::select($query, [$CBG, $noBukti]);
        return collect($results);
    }

    private function getDataUsulanKapasitas($CBG, $noBukti)
    {
        $query = "
            SELECT 
                C.NO_BUKTI,
                C.kode,
                C.uraian,
                konversi_harga_ons(C.kode, B.HJ) as hjbr,
                DATE_FORMAT(CURDATE(), '%d/%m/%y') as pers,
                A.BARCODE,
                A.sub,
                A.KDBAR,
                A.KET_UK,
                konversi_kms_ons(C.kode, A.KET_KEM) as KET_KEM,
                B.KDLAKU,
                B.LPH,
                A.SP_L,
                A.SP_LF,
                IF(A.ON_DC = 0, 'L', 'D') ON_DC,
                B.DTR,
                IF(B.KDLAKU = 8, A.TARIK, 0) TARIK,
                IF(ROUND(A.DTB * B.LPH) < 3, 
                   3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), 
                   CEILING(A.DTB * B.LPH)) DTR_IDEALX,
                COALESCE(D.SUSUN, 0) as SUSUN,
                COALESCE(D.MUKA, 0) as MUKA,
                COALESCE(D.DTR_1M / D.SUSUN, 0) as KAPRAK,
                COALESCE(D.DTR_1M, 0) as DTR_1M,
                COALESCE(D.DTR, 0) as DTR_DC,
                COALESCE(D.TANDA, '') as TANDA,
                IF(LEFT(A.NA_BRG, 1) = '3',
                   CONCAT(B.DTR, ' / ', IF(ROUND(A.DTB * B.LPH) < 3, 3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), CEILING(A.DTB * B.LPH))),
                   CONCAT(COALESCE(D.MUKA, 0), ' (', ROUND(COALESCE(D.SUSUN, 0)), '.', 
                          ROUND(COALESCE(D.DTR_1M, 0)), ') ', COALESCE(D.TANDA, ''))) BEDA,
                IF(A.TARIK > 0, CONCAT(A.TARIK_TIPE, A.TARIK), '') AS MASA_TARIK,
                A.DTB,
                A.DTB as dtb
            FROM brg A
            INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
            INNER JOIN histod C ON A.KD_BRG = C.KODE
            LEFT JOIN brg_dc_ts D ON C.KODE = D.KD_BRG
            WHERE B.cbg = ? 
                AND B.yer = YEAR(NOW()) 
                AND C.NO_BUKTI = ?
                AND DATE(D.TG_REPAIR) >= C.TGL
                AND LEFT(A.BARCODE, 1) <> '#'
            ORDER BY C.kode
        ";

        $results = DB::select($query, [$CBG, $noBukti]);
        return collect($results);
    }

    private function getDataUsulanDC($CBG, $kode)
    {
        $query = "
            SELECT 
                A.kd_brg as kode,
                A.NA_BRG as uraian,
                konversi_harga_ons(A.kd_brg, B.hj) as hjbr,
                DATE_FORMAT(CURDATE(), '%d/%m/%y') as pers,
                A.BARCODE,
                A.sub,
                A.KDBAR,
                A.KET_UK,
                konversi_kms_ons(A.KD_BRG, A.KET_KEM) as KET_KEM,
                B.KDLAKU,
                B.LPH,
                A.SP_L,
                A.SP_LF,
                IF(A.ON_DC = 0, 'L', 'D') ON_DC,
                B.DTR,
                IF(B.KDLAKU = 8, A.TARIK, 0) TARIK,
                IF(ROUND(A.DTB * B.LPH) < 3, 
                   3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), 
                   CEILING(A.DTB * B.LPH)) DTR_IDEALX,
                COALESCE(C.SUSUN, 0) as SUSUN,
                COALESCE(C.MUKA, 0) as MUKA,
                COALESCE(C.DTR_1M / C.SUSUN, 0) as KAPRAK,
                COALESCE(C.DTR_1M, 0) as DTR_1M,
                COALESCE(C.DTR, 0) as DTR_DC,
                COALESCE(C.TANDA, '') as TANDA,
                IF(LEFT(A.NA_BRG, 1) = '3', 
                   CONCAT(B.DTR, ' / ', IF(ROUND(A.DTB * B.LPH) < 3, 3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), CEILING(A.DTB * B.LPH))), 
                   CONCAT(COALESCE(C.MUKA, 0), ' (', ROUND(COALESCE(C.SUSUN, 0)), '.', 
                          ROUND(COALESCE(C.DTR_1M, 0)), ') ', COALESCE(C.TANDA, ''))) BEDA,
                IF(A.TARIK > 0, CONCAT(A.TARIK_TIPE, A.TARIK), '') AS MASA_TARIK,
                A.DTB,
                A.DTB as dtb
            FROM brg A
            INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
            LEFT JOIN brg_dc_ts C ON B.KD_BRG = C.KD_BRG
            WHERE B.CBG = ? 
                AND B.yer = YEAR(NOW())
                AND LEFT(A.BARCODE, 1) <> '#'
                AND A.kd_brg IN (
                    SELECT KD_BRG 
                    FROM usul_susun_dcts 
                    WHERE NO_BUKTI = ? AND POSTED = 1
                )
            ORDER BY A.kd_brg
        ";

        $results = DB::select($query, [$CBG, $kode]);
        return collect($results);
    }

    private function getDataBarangBiasa($CBG, $kode, $kali)
    {
        $query = "
            SELECT 
                A.kd_brg as kode,
                A.NA_BRG as uraian,
                konversi_harga_ons(A.kd_brg, B.hj) as hjbr,
                DATE_FORMAT(CURDATE(), '%d/%m/%y') as pers,
                A.BARCODE,
                A.sub,
                A.KDBAR,
                A.KET_UK,
                konversi_kms_ons(A.KD_BRG, A.KET_KEM) as KET_KEM,
                B.KDLAKU,
                B.LPH,
                A.SP_L,
                A.SP_LF,
                IF(A.ON_DC = 0, 'L', 'D') ON_DC,
                B.DTR,
                IF(B.KDLAKU = 8, A.TARIK, 0) TARIK,
                IF(ROUND(A.DTB * B.LPH) < 3, 
                   3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), 
                   CEILING(A.DTB * B.LPH)) DTR_IDEALX,
                COALESCE(C.SUSUN, 0) as SUSUN,
                COALESCE(C.MUKA, 0) as MUKA,
                COALESCE(C.DTR_1M / C.SUSUN, 0) as KAPRAK,
                COALESCE(C.DTR_1M, 0) as DTR_1M,
                COALESCE(C.DTR, 0) as DTR_DC,
                COALESCE(C.TANDA, '') as TANDA,
                IF(LEFT(A.NA_BRG, 1) = '3', 
                   CONCAT(B.DTR, ' / ', IF(ROUND(A.DTB * B.LPH) < 3, 3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), CEILING(A.DTB * B.LPH))), 
                   CONCAT(COALESCE(C.MUKA, 0), ' (', ROUND(COALESCE(C.SUSUN, 0)), '.', 
                          ROUND(COALESCE(C.DTR_1M, 0)), ') ', COALESCE(C.TANDA, ''))) BEDA,
                IF(A.TARIK > 0, CONCAT(A.TARIK_TIPE, A.TARIK), '') AS MASA_TARIK,
                A.DTB,
                A.DTB as dtb
            FROM brg A
            INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
            LEFT JOIN brg_dc_ts C ON B.KD_BRG = C.KD_BRG
            WHERE B.CBG = ? 
                AND B.yer = YEAR(NOW())
                AND A.kd_brg = ?
                AND LEFT(A.BARCODE, 1) <> '#'
            ORDER BY A.kd_brg
        ";

        $result = DB::select($query, [$CBG, $kode]);

        // Duplicate data berdasarkan nilai kali
        $data = collect([]);
        foreach ($result as $row) {
            for ($i = 0; $i < $kali; $i++) {
                $data->push(clone $row);
            }
        }

        return $data;
    }

    private function getDataBySub($CBG, $sub)
    {
        $query = "
            SELECT 
                A.kd_brg as kode,
                A.NA_BRG as uraian,
                konversi_harga_ons(A.kd_brg, B.hj) as hjbr,
                DATE_FORMAT(CURDATE(), '%d/%m/%y') as pers,
                A.BARCODE,
                A.sub,
                A.KDBAR,
                A.KET_UK,
                konversi_kms_ons(A.KD_BRG, A.KET_KEM) as KET_KEM,
                B.KDLAKU,
                B.LPH,
                A.SP_L,
                A.SP_LF,
                IF(A.ON_DC = 0, 'L', 'D') ON_DC,
                B.DTR,
                IF(B.KDLAKU = 8, A.TARIK, 0) TARIK,
                IF(ROUND(A.DTB * B.LPH) < 3, 
                   3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), 
                   CEILING(A.DTB * B.LPH)) DTR_IDEALX,
                COALESCE(C.SUSUN, 0) as SUSUN,
                COALESCE(C.MUKA, 0) as MUKA,
                COALESCE(C.DTR_1M / C.SUSUN, 0) as KAPRAK,
                COALESCE(C.DTR_1M, 0) as DTR_1M,
                COALESCE(C.DTR, 0) as DTR_DC,
                COALESCE(C.TANDA, '') as TANDA,
                IF(LEFT(A.NA_BRG, 1) = '3', 
                   CONCAT(B.DTR, ' / ', IF(ROUND(A.DTB * B.LPH) < 3, 3 * SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.ket_kem)) + 1))), CEILING(A.DTB * B.LPH))), 
                   CONCAT(COALESCE(C.MUKA, 0), ' (', ROUND(COALESCE(C.SUSUN, 0)), '.', 
                          ROUND(COALESCE(C.DTR_1M, 0)), ') ', COALESCE(C.TANDA, ''))) BEDA,
                IF(A.TARIK > 0, CONCAT(A.TARIK_TIPE, A.TARIK), '') AS MASA_TARIK,
                A.DTB,
                A.DTB as dtb
            FROM brg A
            INNER JOIN brgdt B ON A.KD_BRG = B.KD_BRG
            LEFT JOIN brg_dc_ts C ON B.KD_BRG = C.KD_BRG
            WHERE B.CBG = ? 
                AND B.yer = YEAR(NOW())
                AND LEFT(A.kd_brg, 3) = ?
                AND LEFT(A.BARCODE, 1) <> '#'
            ORDER BY A.kd_brg
        ";

        $results = DB::select($query, [$CBG, $sub]);
        return collect($results);
    }

    public function proses(Request $request)
    {
        try {
            $action = $request->input('action');

            switch ($action) {
                case 'cetak':
                    return $this->cetakLabel($request);
                case 'clear':
                    return response()->json(['success' => true, 'message' => 'Data berhasil dibersihkan']);
                default:
                    return response()->json(['error' => 'Action tidak valid'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function cetakLabel(Request $request)
    {

		$jenis = strtoupper($request->input('jenis', 'BIASA'));
        $kode = trim($request->input('kode', ''));
        $sub = trim($request->input('sub', ''));
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $kali = (int) $request->input('kali', 1);
		
		return response()->json([
			'success' => true,
			'message' => 'Data siap dicetak',
			'redirect' => route('cetaklabelharga_print', [
				'jenis' => $jenis,
				'kode' => $kode,
				'sub' => $sub,
				'tanggal' => $tanggal,
				'kali' => $kali
			])
		]);

    }
	
	public function printLabel(Request $request)
    {

        $CBG = Auth::user()->CBG ?? null;
        $jenis = strtoupper($request->input('jenis', 'BIASA'));
        $kode = trim($request->input('kode', ''));
        $sub = trim($request->input('sub', ''));
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $kali = (int) $request->input('kali', 1);

        $query = collect([]);

        if ($jenis === 'FF') {
            // Fast Forward - Perubahan LPH
            $query = $this->getDataFF($CBG, $tanggal);
        } else {
            // Biasa - Berdasarkan kode atau sub
            if (!empty($kode)) {
                $query = $this->getDataByKode($CBG, $kode, $kali);
            } elseif (!empty($sub)) {
                $query = $this->getDataBySub($CBG, $sub);
            }
        }

        $file = 'label_barcode';
        $data = [];
        foreach ($query as $value) {

            $persShort = join('/', explode('/', $value->pers ?? '') ?: []);

            $data[] = [
                'URAIAN' => $value->uraian ?? '',
                'KET_KEM'  => $value->KET_KEM ?? '',
                'KET_UK'  => $value->KET_UK ?? '',
                'BEDA' => $value->BEDA ?? '',
                'HJBR'   => $value->hjbr ?? 0,
                'SUB'   => $value->sub ?? '',
                'KDBAR'   => $value->KDBAR ?? '',
                'BARCODE'      => str_replace("'", "", (string)$value->BARCODE),
                'PERS'       => substr($persShort, 0, 5),
                'KDLAKU'       => $value->KDLAKU ?? 0,
                'MASA_TARIK' => $value->MASA_TARIK ?? '',
            ];
        }
        //dd($data);
        $PHPJasperXML = new \PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . "/app/reportc01/phpjasperxml/$file.jrxml");
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    
    }

}
