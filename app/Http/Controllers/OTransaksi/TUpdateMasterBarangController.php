<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TUpdateMasterBarangController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Update Master Barang';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TUpdateMasterBarang.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TUpdateMasterBarang.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            // Ambil list cabang untuk dropdown
            $cabangList = DB::connection('tgz')->select("SELECT cbg, nama FROM toko WHERE cbg != 'TGZ' ORDER BY cbg");

            return view("otransaksi_TUpdateMasterBarang.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'cabangList' => $cabangList
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TUpdateMasterBarang index: ' . $e->getMessage());
            return view("otransaksi_TUpdateMasterBarang.index")->with([
                'judul' => 'Transaksi Update Master Barang',
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

            $kodeType = $request->input('kode_type', '3'); // 3 atau 5
            $selectedCbg = $request->input('cbg');

            if (!$selectedCbg) {
                return response()->json(['error' => 'Pilih outlet/cabang terlebih dahulu'], 400);
            }

            $connection = strtolower($selectedCbg);

            // Query untuk mengambil data dari database outlet yang dipilih berdasarkan kode awalan
            $query = "
                SELECT 
                    LEFT(kd_brg, 3) as sub,
                    RIGHT(kd_brg, 4) as kdbar,
                    kd_brg,
                    na_brg,
                    ket_uk,
                    ket_kem,
                    klk,
                    supp as kodes,
                    tgl_saran,
                    h_saran,
                    f_panen,
                    f_ada,
                    ppn,
                    dtb,
                    qb,
                    qj,
                    '' as valid
                FROM brg
                WHERE LEFT(kd_brg, 1) = ?
                ORDER BY sub, kdbar
            ";

            $data = DB::connection($connection)->select($query, [$kodeType]);

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

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $kodeType = $request->input('kode_type', '3');
            $selectedCbg = $request->input('cbg');
            $dataItems = $request->input('items', []);

            if (empty($selectedCbg)) {
                return response()->json(['error' => 'Pilih outlet terlebih dahulu!'], 400);
            }

            if (empty($dataItems)) {
                return response()->json(['error' => 'Tidak ada data untuk diproses'], 400);
            }

            $connection = strtolower($selectedCbg);

            DB::connection($connection)->beginTransaction();

            $successCount = 0;
            $errorCount = 0;

            foreach ($dataItems as $item) {
                try {
                    if ($kodeType == '3') {
                        // Update tabel brg untuk kode 3
                        DB::connection($connection)->statement("
                            UPDATE brg 
                            SET 
                                NA_BRG = ?,
                                NMBAR = ?,
                                KET_UK = ?,
                                KET_KEM = ?,
                                SUPP = ?,
                                F_PANEN = ?,
                                F_ADA = ?,
                                QB = ?,
                                QJ = ?,
                                PPN = ?
                            WHERE kd_brg = ?
                        ", [
                            $item['na_brg'],
                            $item['na_brg'],
                            $item['ket_uk'],
                            $item['ket_kem'],
                            $item['kodes'],
                            $item['f_panen'],
                            $item['f_ada'],
                            $item['qb'],
                            $item['qj'],
                            $item['ppn'],
                            $item['kd_brg']
                        ]);

                        // Update tabel brgdt untuk kode 3
                        DB::connection($connection)->statement("
                            UPDATE brgdt 
                            SET 
                                NA_BRG = ?,
                                KLK = ?
                            WHERE kd_brg = ?
                        ", [
                            $item['na_brg'],
                            $item['klk'],
                            $item['kd_brg']
                        ]);

                        // Update tabel masks untuk kode 3
                        DB::connection($connection)->statement("
                            UPDATE masks 
                            SET 
                                NA_BRG = ?,
                                NMBAR = ?,
                                KET_UK = ?,
                                KET_KEM = ?,
                                SUPP = ?,
                                PPN = ?
                            WHERE kd_brg = ?
                        ", [
                            $item['na_brg'],
                            $item['na_brg'],
                            $item['ket_uk'],
                            $item['ket_kem'],
                            $item['kodes'],
                            $item['ppn'],
                            $item['kd_brg']
                        ]);
                    } else {
                        // Update tabel brg untuk kode 5
                        DB::connection($connection)->statement("
                            UPDATE brg 
                            SET 
                                NA_BRG = ?,
                                NMBAR = ?,
                                KET_UK = ?,
                                KET_KEM = ?,
                                SUPP = ?,
                                PPN = ?
                            WHERE kd_brg = ?
                        ", [
                            $item['na_brg'],
                            $item['na_brg'],
                            $item['ket_uk'],
                            $item['ket_kem'],
                            $item['kodes'],
                            $item['ppn'],
                            $item['kd_brg']
                        ]);

                        // Update tabel brgdt untuk kode 5
                        DB::connection($connection)->statement("
                            UPDATE brgdt 
                            SET NA_BRG = ?
                            WHERE kd_brg = ?
                        ", [
                            $item['na_brg'],
                            $item['kd_brg']
                        ]);

                        // Update tabel masks untuk kode 5
                        DB::connection($connection)->statement("
                            UPDATE masks 
                            SET 
                                NA_BRG = ?,
                                KET_UK = ?,
                                KET_KEM = ?,
                                SUPP = ?,
                                PPN = ?
                            WHERE kd_brg = ?
                        ", [
                            $item['na_brg'],
                            $item['ket_uk'],
                            $item['ket_kem'],
                            $item['kodes'],
                            $item['ppn'],
                            $item['kd_brg']
                        ]);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    Log::error('Error updating item: ' . $item['kd_brg'] . ' - ' . $e->getMessage());
                    $errorCount++;
                }
            }

            DB::connection($connection)->commit();

            $message = "Proses Update selesai!<br>";
            $message .= "Outlet: {$selectedCbg}<br>";
            $message .= "Kode Type: {$kodeType}<br>";
            $message .= "Berhasil: {$successCount} item<br>";
            if ($errorCount > 0) {
                $message .= "Gagal: {$errorCount} item";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'successCount' => $successCount,
                'errorCount' => $errorCount
            ]);
        } catch (\Exception $e) {
            if (isset($connection)) {
                DB::connection($connection)->rollBack();
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
            $kodeType = $request->input('kode_type', '3');
            $cbg = $request->input('cbg');

            if (empty($search)) {
                return response()->json(['data' => []]);
            }

            if (empty($cbg)) {
                return response()->json(['error' => 'Pilih outlet/cabang terlebih dahulu'], 400);
            }

            $connection = strtolower($cbg);

            $query = "
                SELECT 
                    kd_brg,
                    na_brg,
                    ket_uk,
                    ket_kem
                FROM brg
                WHERE LEFT(kd_brg, 1) = ?
                AND (
                    kd_brg LIKE ? OR
                    na_brg LIKE ?
                )
                LIMIT 20
            ";

            $searchParam = '%' . $search . '%';
            $data = DB::connection($connection)->select($query, [$kodeType, $searchParam, $searchParam]);

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error in searchBarang: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
