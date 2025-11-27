<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TPengajuanHargaFreshFoodController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Pengajuan Harga Fresh Food';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TPengajuanHargaFreshFood.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TPengajuanHargaFreshFood.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TPengajuanHargaFreshFood.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPengajuanHargaFreshFood index: ' . $e->getMessage());
            return view("otransaksi_TPengajuanHargaFreshFood.index")->with([
                'judul' => 'Transaksi Pengajuan Harga Fresh Food',
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

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            $filename = $request->input('filename', '');

            if (empty($filename)) {
                return response()->json(['error' => 'Nama file harus diisi'], 400);
            }

            // Cek prefix file (UH atau U3) - untuk data dari Histo
            $x1 = substr($filename, 0, 2);

            if ($x1 == 'UH' || $x1 == 'U3') {
                // Ambil data dari Histo/Histod
                $query = "
                    SELECT 
                        histo.NO_BUKTI,
                        histo.TGL,
                        histo.POSTED,
                        histod.rec,
                        LEFT(histod.KODE, 3) as sub,
                        RIGHT(histod.KODE, 4) as kdbar,
                        histod.URAIAN as na_brg,
                        histod.HJ as hj,
                        histod.HJBR as harga,
                        CASE WHEN histod.HJBR > histod.HJ THEN 1 ELSE 0 END as pst
                    FROM histo
                    INNER JOIN histod ON histo.NO_BUKTI = histod.NO_BUKTI
                    WHERE (histo.FLAG = 'UH' OR histo.FLAG = 'U3')
                    AND histo.NO_BUKTI = ?
                    ORDER BY histod.rec
                ";

                $data = DB::select($query, [$filename]);

                if (empty($data)) {
                    return response()->json(['error' => 'Nomor Bukti Tidak Ditemukan'], 404);
                }

                // Cek apakah sudah posted
                $isPosted = !empty($data) && $data[0]->POSTED == 1;

                return Datatables::of(collect($data))
                    ->addIndexColumn()
                    ->addColumn('kd_brg', function ($row) {
                        return $row->sub . $row->kdbar;
                    })
                    ->addColumn('oke', function ($row) {
                        $checked = $row->pst == 1 ? 'checked' : '';
                        $disabled = $row->pst == 1 ? 'disabled' : '';
                        return '<input type="checkbox" class="chk-oke" data-rec="' . $row->rec . '" ' . $checked . ' ' . $disabled . '>';
                    })
                    ->editColumn('harga', function ($row) {
                        return number_format($row->harga, 2);
                    })
                    ->editColumn('hj', function ($row) {
                        return number_format($row->hj, 2);
                    })
                    ->with([
                        'posted' => $isPosted,
                        'no_bukti' => !empty($data) ? $data[0]->NO_BUKTI : ''
                    ])
                    ->rawColumns(['oke'])
                    ->make(true);
            } else {
                // Cek apakah file sudah pernah dientri
                $cekHisto = DB::select("
                    SELECT * FROM histo 
                    WHERE kode = ? 
                    AND cbg = ?
                ", [$filename, $CBG]);

                if (!empty($cekHisto)) {
                    return response()->json(['error' => 'File ini sudah pernah dientri!'], 400);
                }

                // Get nama toko
                $toko = DB::select("
                    SELECT NA_TOKO 
                    FROM toko 
                    WHERE KODE = ?
                ", [$CBG]);

                $namaToko = !empty($toko) ? $toko[0]->NA_TOKO : '';

                // Path file DBF
                $namaFile = $filename . '.HRG';
                $fileAwal = 'A:\\dbf\\kode 3 ts\\' . $namaFile;

                // Generate nama file baca
                $namaBaru = substr($filename, 0, 2) . substr($filename, 3, 5);
                $fileBaca = 'D:\\dbf\\kode 3 ts\\baca\\' . $namaBaru . '.DBF';

                // Copy file
                if (!file_exists($fileAwal)) {
                    return response()->json(['error' => 'File tidak ditemukan di: ' . $fileAwal], 404);
                }

                if (!copy($fileAwal, $fileBaca)) {
                    return response()->json(['error' => 'Gagal menyalin file'], 500);
                }

                // Baca file DBF menggunakan PDO
                try {
                    $connectionString = "odbc:Driver={Microsoft Visual FoxPro Driver};SourceType=DBF;SourceDB=" . dirname($fileBaca) . ";Exclusive=No;";
                    $pdo = new \PDO($connectionString);

                    $stmt = $pdo->query("SELECT SUB, KDBAR, HARGA FROM " . $namaBaru . " ORDER BY SUB ASC, KDBAR ASC");
                    $dataFile = $stmt->fetchAll(\PDO::FETCH_OBJ);

                    $result = [];
                    $rec = 1;

                    foreach ($dataFile as $row) {
                        // Hitung harga baru dengan margin dan pembulatan
                        $hargaBaru = DB::select("
                            SELECT 
                                ? + ROUND((? * MARGIN) / 100) AS HJ_BARU,
                                (CEILING((? + ROUND((? * MARGIN) / 100)) / 10) * 10) as bulat
                            FROM brg 
                            WHERE kd_brg = CONCAT(?, ?)
                        ", [$row->HARGA, $row->HARGA, $row->HARGA, $row->HARGA, $row->SUB, $row->KDBAR]);

                        if (empty($hargaBaru)) continue;

                        // Get harga jual dan nama barang
                        $brgDetail = DB::select("
                            SELECT na_brg, hj 
                            FROM brgdt 
                            WHERE kd_brg = CONCAT(?, ?)
                        ", [$row->SUB, $row->KDBAR]);

                        if (empty($brgDetail)) continue;

                        $hargaBulat = $hargaBaru[0]->bulat;
                        $hargaJual = $brgDetail[0]->hj;
                        $namaBarang = $brgDetail[0]->na_brg;

                        $pst = $hargaBulat > $hargaJual ? 1 : 0;

                        $result[] = (object)[
                            'rec' => $rec,
                            'sub' => $row->SUB,
                            'kdbar' => $row->KDBAR,
                            'na_brg' => $namaBarang,
                            'harga' => $hargaBulat,
                            'hj' => $hargaJual,
                            'pst' => $pst,
                            'nmtoko' => $namaToko
                        ];

                        $rec++;
                    }

                    return Datatables::of(collect($result))
                        ->addIndexColumn()
                        ->addColumn('kd_brg', function ($row) {
                            return $row->sub . $row->kdbar;
                        })
                        ->addColumn('oke', function ($row) {
                            $checked = $row->pst == 1 ? 'checked' : '';
                            return '<input type="checkbox" class="chk-oke" data-rec="' . $row->rec . '" ' . $checked . '>';
                        })
                        ->editColumn('harga', function ($row) {
                            return '<input type="text" class="form-control form-control-sm text-right edit-harga" data-rec="' . $row->rec . '" value="' . number_format($row->harga, 2, '.', '') . '">';
                        })
                        ->editColumn('hj', function ($row) {
                            return number_format($row->hj, 2);
                        })
                        ->with([
                            'posted' => false,
                            'no_bukti' => ''
                        ])
                        ->rawColumns(['oke', 'harga'])
                        ->make(true);
                } catch (\Exception $e) {
                    Log::error('Error reading DBF file: ' . $e->getMessage());
                    return response()->json(['error' => 'Gagal membaca file DBF: ' . $e->getMessage()], 500);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            $filename = $request->input('filename', '');
            $tanggal = $request->input('tanggal', date('Y-m-d'));
            $dataItems = $request->input('items', []);

            if (empty($filename)) {
                return response()->json(['error' => 'Nama file harus diisi'], 400);
            }

            if (empty($dataItems)) {
                return response()->json(['error' => 'Tidak ada data untuk diproses'], 400);
            }

            // Cek apakah sudah pernah diproses
            $cekHisto = DB::select("
                SELECT * FROM histo 
                WHERE kode = ? 
                AND cbg = ?
            ", [$filename, $CBG]);

            if (!empty($cekHisto)) {
                return response()->json(['error' => 'File sudah diproses'], 400);
            }

            DB::beginTransaction();

            // Generate nomor bukti
            $monthString = substr($periode, 0, 2);
            $yearString = substr($periode, -2);

            // Get type toko
            $toko = DB::select("
                SELECT type 
                FROM toko 
                WHERE kode = ?
            ", [$CBG]);

            $kode2 = !empty($toko) ? $toko[0]->type : '';
            $kode = 'U3' . $yearString . $monthString;

            // Get nomor terakhir
            $noTrans = DB::select("
                SELECT NOM{$monthString} as NO_BUKTI 
                FROM notrans 
                WHERE trans = 'HJ3' 
                AND PER = ?
            ", [substr($periode, -4)]);

            $nomorBaru = (!empty($noTrans) ? $noTrans[0]->NO_BUKTI : 0) + 1;

            // Update nomor di notrans
            DB::statement("
                UPDATE notrans 
                SET NOM{$monthString} = ? 
                WHERE trans = 'HJ3' 
                AND PER = ?
            ", [$nomorBaru, substr($periode, -4)]);

            $noBuktiBaru = $kode . '-' . str_pad($nomorBaru, 4, '0', STR_PAD_LEFT) . $kode2;

            // Insert ke HISTO
            DB::statement("
                INSERT INTO HISTO (
                    NO_BUKTI, kode, TGL, FLAG, CBG, TYPE, USRNM, PER
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $noBuktiBaru,
                $filename,
                $tanggal,
                'U3',
                $CBG,
                1,
                $username,
                $periode
            ]);

            // Get ID histo
            $histoId = DB::select("
                SELECT no_id 
                FROM HISTO 
                WHERE no_bukti = ?
            ", [$noBuktiBaru]);

            $idHisto = !empty($histoId) ? $histoId[0]->no_id : 0;

            // Insert detail
            $rec = 1;
            foreach ($dataItems as $item) {
                if (isset($item['pst']) && $item['pst'] == 1) {
                    DB::statement("
                        INSERT INTO HISTOD (
                            NO_BUKTI, id, rec, kode, uraian, hj, hjbr, ket, tgl
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ", [
                        $noBuktiBaru,
                        $idHisto,
                        $rec,
                        $item['sub'] . $item['kdbar'],
                        $item['na_brg'],
                        $item['hj'],
                        $item['harga'],
                        'TS' . $filename
                    ]);

                    $rec++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diproses!',
                'no_bukti' => $noBuktiBaru
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $noBukti = $request->input('no_bukti', '');

            if (empty($noBukti)) {
                return response()->json(['error' => 'Nomor bukti harus diisi'], 400);
            }

            // Get detail untuk cetak
            $query = "
                SELECT 
                    histo.kode as file,
                    histod.NO_BUKTI,
                    histod.KODE,
                    histod.HJ,
                    histod.HJBR,
                    TRIM(CONCAT(histod.URAIAN, ' ', IFNULL(brg.KET_UK, ''))) as NA_BRG,
                    brg.KET_KEM
                FROM histo
                INNER JOIN histod ON histo.no_bukti = histod.no_bukti
                LEFT JOIN brg ON brg.KD_BRG = histod.KODE
                WHERE histo.NO_BUKTI = ?
                ORDER BY histod.KODE ASC
            ";

            $data = DB::select($query, [$noBukti]);

            if (empty($data)) {
                return response()->json(['error' => 'No.bukti salah!'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengambil detail: ' . $e->getMessage()
            ], 500);
        }
    }
}
