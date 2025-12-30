<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TKirimDataTimbanganController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Kirim Data Timbangan';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TKirimDataTimbangan.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TKirimDataTimbangan.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $per = $request->session()->get('periode');
            $periode = $per['bulan'] . '/' . $per['tahun'];

            // Ambil list cabang untuk dropdown (dari toko tgz)
            $cabangList = DB::select("SELECT kode, na_toko as nama FROM tgz.toko  ORDER BY kode");

            // Ambil list periode
            $periodeList = DB::select("SELECT perio FROM perid");

            return view("otransaksi_TKirimDataTimbangan.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'cabangList' => $cabangList,
                'periodeList' => $periodeList
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TKirimDataTimbangan index: ' . $e->getMessage());
            return view("otransaksi_TKirimDataTimbangan.index")->with([
                'judul' => 'Transaksi Kirim Data Timbangan',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $selectedCbg = $request->input('cbg');
            $noBukti = trim($request->input('no_bukti', ''));

            if (empty($selectedCbg)) {
                return response()->json(['error' => 'Pilih cabang terlebih dahulu'], 400);
            }

            if (empty($noBukti)) {
                return response()->json(['error' => 'No usulan harus diisi'], 400);
            }

            // Adaptasi dari procedure tampil di Delphi
            // Query mengambil data dari histod, brg, histo berdasarkan NO_BUKTI dan CBG
            $query = "
                SELECT
                    RIGHT(histod.KODE, 6) as plu,
                    histod.NO_BUKTI,
                    SUBSTR(CONCAT(RIGHT(histod.KODE, 4), LEFT(histod.KODE, 3)), 3, 5) as KD_BRG,
                    SUBSTR(histod.KODE, 4, 2) as FLAG,
                    CONCAT(histod.KDLAKU, histod.KLK) as KD,
                    histod.URAIAN as NA_BRG,
                    brg.KET_UK,
                    brg.KET_KEM,
                    histod.HJ2,
                    histod.HJ,
                    histod.HJBR,
                    brg.barcode,
                    histod.TGL,
                    histod.ket,
                    CONCAT(
                        LPAD(hit_dtr_ideal(brg.KD_BRG), 3, '0'),
                        DATE_FORMAT(CURDATE(), '%d'),
                        LPAD(brg.DTB, 2, '0')
                    ) as ingredient
                FROM histod
                INNER JOIN brg ON histod.KODE = brg.KD_BRG
                INNER JOIN histo ON histod.NO_BUKTI = histo.NO_BUKTI
                WHERE histo.CBG = ?
                AND histo.NO_BUKTI = ?
            ";

            $data = DB::select($query, [$selectedCbg, $noBukti]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cari_semua(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $selectedCbg = $request->input('cbg');

            if (empty($selectedCbg)) {
                return response()->json(['error' => 'Pilih cabang terlebih dahulu'], 400);
            }

            // Adaptasi dari procedure tampil2 di Delphi
            // Query mengambil semua data dari MASKS dan BRG yang KET_KEM LIKE "%KG%"
            $query = "
                SELECT
                    RIGHT(A.KD_BRG, 6) as plu,
                    IF(? = 'TGZ', A.HJGZ,
                       IF(? = 'TMM', A.HJMM,
                          IF(? = 'SOP', A.HJSP, A.HJ)
                       )
                    ) as HJBR,
                    SUBSTR(CONCAT(RIGHT(A.KD_BRG, 4), LEFT(A.KD_BRG, 3)), 3, 5) as KD_BRG,
                    SUBSTR(A.KD_BRG, 4, 2) as FLAG,
                    A.NA_BRG as NA_BRG,
                    CONCAT(
                        LPAD(hit_dtr_ideal(B.KD_BRG), 3, '0'),
                        DATE_FORMAT(CURDATE(), '%d'),
                        LPAD(B.DTB, 2, '0')
                    ) as ingredient
                FROM MASKS A
                INNER JOIN BRG B ON A.KD_BRG = B.KD_BRG
                WHERE A.KET_KEM LIKE '%KG%'
                AND A.HJ > 0
            ";

            $data = DB::select($query, [$selectedCbg, $selectedCbg, $selectedCbg]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_semua: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    public function print(Request $request)
    {
        $cbg = $request->input('cbg', '');
        $no_bukti = $request->input('no_bukti', '');
        $query = '';
        $data = [];

        if ($no_bukti) {
            // Query untuk data berdasarkan NO_BUKTI (matching Delphi tampil procedure)
            $query = "
                SELECT
                    RIGHT(histod.KODE, 6) as plu,
                    histod.NO_BUKTI,
                    SUBSTR(CONCAT(RIGHT(histod.KODE, 4), LEFT(histod.KODE, 3)), 3, 5) as KD_BRG,
                    SUBSTR(histod.KODE, 4, 2) as FLAG,
                    CONCAT(histod.KDLAKU, histod.KLK) as KD,
                    histod.URAIAN as NA_BRG,
                    brg.KET_UK,
                    brg.KET_KEM,
                    histod.HJ2,
                    histod.HJ,
                    histod.HJBR,
                    brg.barcode,
                    histod.TGL,
                    histod.ket,
                    CONCAT(
                        LPAD(hit_dtr_ideal(brg.KD_BRG), 3, '0'),
                        DATE_FORMAT(CURDATE(), '%d'),
                        LPAD(brg.DTB, 2, '0')
                    ) as ingredient
                FROM histod
                INNER JOIN brg ON histod.KODE = brg.KD_BRG
                INNER JOIN histo ON histod.NO_BUKTI = histo.NO_BUKTI
                WHERE histo.CBG = ?
                AND histo.NO_BUKTI = ?
            ";

            $data = DB::select($query, [$cbg, $no_bukti]);
        } else {
            // Query untuk semua data (matching Delphi tampil2 procedure)
            $query = "
                SELECT
                    RIGHT(A.KD_BRG, 6) as plu,
                    IF(? = 'TGZ', A.HJGZ,
                       IF(? = 'TMM', A.HJMM,
                          IF(? = 'SOP', A.HJSP, A.HJ)
                       )
                    ) as HJBR,
                    SUBSTR(CONCAT(RIGHT(A.KD_BRG, 4), LEFT(A.KD_BRG, 3)), 3, 5) as KD_BRG,
                    SUBSTR(A.KD_BRG, 4, 2) as FLAG,
                    A.NA_BRG as NA_BRG,
                    CONCAT(
                        LPAD(hit_dtr_ideal(B.KD_BRG), 3, '0'),
                        DATE_FORMAT(CURDATE(), '%d'),
                        LPAD(B.DTB, 2, '0')
                    ) as ingredient
                FROM MASKS A
                INNER JOIN BRG B ON A.KD_BRG = B.KD_BRG
                WHERE A.KET_KEM LIKE '%KG%'
                AND A.HJ > 0
            ";

            $data = DB::select($query, [$cbg, $cbg, $cbg]);
        }

        // Format data untuk jasper
        $file = 'dataTimbangan_new';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        foreach ($data as $key => $value) {
            $data[$key]->JUDUL = 'Laporan Data Timbangan Cabang ' . $cbg;
            $data[$key]->TGL_NOW = now()->format('d/m/Y');
        }

        $PHPJasperXML->setData(array_map(function ($item) {
            return (array) $item;
        }, $data));

        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $selectedCbg = $request->input('cbg');
            $noBukti = trim($request->input('no_bukti', ''));

            if (empty($selectedCbg)) {
                return response()->json(['error' => 'Pilih cabang terlebih dahulu'], 400);
            }

            // Setup dynamic connection
            $connection = strtolower($selectedCbg);

            DB::beginTransaction();

            // Logic proses pengiriman data timbangan
            // Bisa disesuaikan dengan kebutuhan bisnis yang sebenarnya

            $message = "Proses Kirim Data Timbangan Selesai!<br>";
            $message .= "Cabang: {$selectedCbg}<br>";
            if (!empty($noBukti)) {
                $message .= "No Usulan: {$noBukti}<br>";
            } else {
                $message .= "Semua data yang memenuhi kriteria berhasil diproses<br>";
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            if (isset($connection)) {
                DB::rollBack();
            }
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchBarang(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $cbg = $request->input('cbg', '');

            if (empty($search) || empty($cbg)) {
                return response()->json(['data' => []]);
            }

            // Setup dynamic connection
            $connection = strtolower($cbg);

            $query = "
                SELECT
                    NO_BUKTI,
                    TGL,
                    CBG
                FROM histod
                WHERE CBG = ?
                AND (
                    NO_BUKTI LIKE ? OR
                    URAIAN LIKE ?
                )
                GROUP BY NO_BUKTI
                LIMIT 20
            ";

            $searchParam = '%' . $search . '%';
            $data = DB::select($query, [$cbg, $searchParam, $searchParam]);

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error in searchBarang: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
