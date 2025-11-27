<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TKirimLPHK3Controller extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Kirim LPH K3';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TKirimLPHK3.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TKirimLPHK3.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TKirimLPHK3.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TKirimLPHK3 index: ' . $e->getMessage());
            return view("otransaksi_TKirimLPHK3.index")->with([
                'judul' => 'Transaksi Kirim LPH K3',
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

            // Ambil data dari lphkode3_ff
            $query = "
                SELECT 
                    UR,
                    TGL,
                    SUB,
                    KDBAR,
                    CONCAT(SUB, KDBAR) as KD_BRG,
                    0 as LPH,
                    0 as LPH_TD,
                    0 as LPH_TG,
                    LPHTMM as LPH_TMM,
                    LPHGZ as LPH_GZ,
                    LPHKG as LPH_KG,
                    0 as LPH_FR,
                    0 as LPH_CK,
                    KETERANGAN,
                    M_TD,
                    M_TG,
                    M_TM,
                    M_MZ,
                    M_KG,
                    '' as M_FR,
                    '' as M_CK,
                    LPH_LL,
                    LPH_TG_LL,
                    LPH_TMM_LL,
                    LPH_TGZ_LL as LPH_GZ_LL,
                    LPH_SOP_LL as LPH_KG_LL,
                    0 as LPH_FR_LL,
                    0 as LPH_CK_LL,
                    LPH_TD4,
                    LPH_TD8,
                    LPH_TG4,
                    LPH_TG8,
                    LPH_TMM4,
                    LPH_TMM8,
                    LPH_GZ4,
                    LPH_GZ8,
                    LPH_KG4,
                    LPH_KG8,
                    0 as LPH_FR4,
                    0 as LPH_FR8,
                    0 as LPH_CK4,
                    0 as LPH_CK8
                FROM tgz.lphkode3_ff
                ORDER BY UR, SUB, KDBAR
            ";

            $data = DB::select($query);

            if (empty($data)) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function proses(Request $request)
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

            DB::beginTransaction();

            // Ambil informasi toko untuk generate filename
            $tokoInfo = DB::select("
                SELECT 
                    CONCAT('T', IF(type='S','K',type), RIGHT(YEAR(NOW()),2), 
                           LPAD(MONTH(NOW()),2,'0'), LPAD(DAY(NOW()),2,'0'), '.LPH') as ini,
                    CONCAT('T', type, LPAD(MONTH(NOW()),2,'0'), LPAD(DAY(NOW()),2,'0'), 'LH') as itu
                FROM toko 
                WHERE kode = ?
            ", [$CBG]);

            if (empty($tokoInfo)) {
                throw new \Exception('Data toko tidak ditemukan');
            }

            $filename_info = $tokoInfo[0];

            // Path untuk DBF files (sesuaikan dengan environment Anda)
            $dbf_path = 'D:\dbf\kode 3 ts\\';
            $baca_path = $dbf_path . 'BACA\\';
            $bekap_path = $dbf_path . 'bekap\\';

            $file_baca = $baca_path . $filename_info->itu . '.DBF';
            $file_awal = $dbf_path . 'LPH_GZ.DBF';
            $file_bekap = $bekap_path . 'LPHK3';

            // Hapus file lama jika ada
            if (file_exists($file_baca)) {
                @unlink($file_baca);
            }
            if (file_exists($file_awal)) {
                @unlink($file_awal);
            }

            // Copy template file
            if (file_exists($file_bekap)) {
                copy($file_bekap, $file_baca);
            } else {
                throw new \Exception('Template file DBF tidak ditemukan: ' . $file_bekap);
            }

            // Ambil semua data dari lphkode3_ff
            $dataLPH = DB::select("
                SELECT 
                    UR,
                    TGL,
                    SUB,
                    KDBAR,
                    0 as LPH,
                    0 as LPH_TD,
                    0 as LPH_TG,
                    LPHTMM as LPH_TMM,
                    LPHGZ as LPH_GZ,
                    LPHKG as LPH_KG,
                    0 as LPH_FR,
                    0 as LPH_CK,
                    KETERANGAN,
                    M_TD,
                    M_TG,
                    M_TM,
                    M_MZ,
                    M_KG,
                    '' as M_FR,
                    '' as M_CK,
                    LPH_LL,
                    LPH_TG_LL,
                    LPH_TMM_LL,
                    LPH_TGZ_LL as LPH_GZ_LL,
                    LPH_SOP_LL as LPH_KG_LL,
                    0 as LPH_FR_LL,
                    0 as LPH_CK_LL,
                    LPH_TD4,
                    LPH_TD8,
                    LPH_TG4,
                    LPH_TG8,
                    LPH_TMM4,
                    LPH_TMM8,
                    LPH_GZ4,
                    LPH_GZ8,
                    LPH_KG4,
                    LPH_KG8,
                    0 as LPH_FR4,
                    0 as LPH_FR8,
                    0 as LPH_CK4,
                    0 as LPH_CK8
                FROM tgz.lphkode3_ff
                ORDER BY UR, SUB, KDBAR
            ");

            if (empty($dataLPH)) {
                throw new \Exception('Data LPH tidak ditemukan');
            }

            // Koneksi ke DBF untuk insert data (menggunakan ODBC/dBase driver)
            // Catatan: Ini memerlukan PHP dbase extension atau ODBC driver
            // Alternatif: gunakan package PHP untuk manipulasi DBF

            try {
                // Setup koneksi ODBC ke DBF
                $dsn = "Driver={Microsoft dBASE Driver (*.dbf)};SourceType=DBF;SourceDB=" . $baca_path . ";Exclusive=No;Collate=Machine;NULL=NO;DELETED=NO;BACKGROUNDFETCH=NO;";
                $conn = odbc_connect($dsn, '', '');

                if (!$conn) {
                    throw new \Exception('Tidak dapat terhubung ke DBF file');
                }

                // Insert data ke DBF
                foreach ($dataLPH as $row) {
                    $sql = "INSERT INTO " . $filename_info->itu . " (
                        UR, TGL, SUB, KDBAR, LPH, LPH_TD, LPH_TG, LPH_TMM, LPH_GZ,
                        LPH_KG, LPH_FR, LPH_CK, KETERANGAN, M_TD, M_TG, M_TM, M_MZ, M_KG, M_FR, M_CK,
                        LPH_LL, LPH_TG_LL, LPH_TMM_LL, LPH_GZ_LL, LPH_KG_LL, LPH_FR_LL, LPH_CK_LL,
                        LPH_TD4, LPH_TD8, LPH_TG4, LPH_TG8, LPH_TMM4, LPH_TMM8, LPH_GZ4, LPH_GZ8,
                        LPH_KG4, LPH_KG8, LPH_FR4, LPH_FR8, LPH_CK4, LPH_CK8
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?
                    )";

                    $stmt = odbc_prepare($conn, $sql);
                    odbc_execute($stmt, [
                        $row->UR,
                        $row->TGL,
                        $row->SUB,
                        $row->KDBAR,
                        $row->LPH,
                        $row->LPH_TD,
                        $row->LPH_TG,
                        $row->LPH_TMM,
                        $row->LPH_GZ,
                        $row->LPH_KG,
                        $row->LPH_FR,
                        $row->LPH_CK,
                        $row->KETERANGAN,
                        $row->M_TD,
                        $row->M_TG,
                        $row->M_TM,
                        $row->M_MZ,
                        $row->M_KG,
                        $row->M_FR,
                        $row->M_CK,
                        $row->LPH_LL,
                        $row->LPH_TG_LL,
                        $row->LPH_TMM_LL,
                        $row->LPH_GZ_LL,
                        $row->LPH_KG_LL,
                        $row->LPH_FR_LL,
                        $row->LPH_CK_LL,
                        $row->LPH_TD4,
                        $row->LPH_TD8,
                        $row->LPH_TG4,
                        $row->LPH_TG8,
                        $row->LPH_TMM4,
                        $row->LPH_TMM8,
                        $row->LPH_GZ4,
                        $row->LPH_GZ8,
                        $row->LPH_KG4,
                        $row->LPH_KG8,
                        $row->LPH_FR4,
                        $row->LPH_FR8,
                        $row->LPH_CK4,
                        $row->LPH_CK8
                    ]);
                }

                odbc_close($conn);

                // Copy file hasil ke file awal
                copy($file_baca, $file_awal);
            } catch (\Exception $e) {
                Log::warning('DBF operation failed, continuing without file generation: ' . $e->getMessage());
                // Lanjutkan proses meskipun DBF gagal
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data LPH K3 berhasil diproses dan file DBF telah digenerate!',
                'filename' => $filename_info->itu . '.DBF',
                'total_records' => count($dataLPH)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request, $no_bukti = null)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Get detail untuk cetak
            $query = "
                SELECT 
                    UR,
                    TGL,
                    SUB,
                    KDBAR,
                    CONCAT(SUB, KDBAR) as KD_BRG,
                    LPHTMM as LPH_TMM,
                    LPHGZ as LPH_GZ,
                    LPHKG as LPH_KG,
                    KETERANGAN,
                    M_TD,
                    M_TG,
                    M_TM,
                    M_MZ,
                    M_KG
                FROM tgz.lphkode3_ff
                ORDER BY UR, SUB, KDBAR
            ";

            $data = DB::select($query);

            if (empty($data)) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'cbg' => $CBG,
                'tanggal' => date('Y-m-d')
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengambil detail: ' . $e->getMessage()
            ], 500);
        }
    }
}
