<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TPostingKasirController extends Controller
{
    var $judul = 'Posting Kasir';
    var $FLAGZ = 'PK';

    public function index(Request $request)
    {
        try {
            if (!$request->session()->has('periode')) {
                return view("otransaksi_tpostingkasir.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            // Cek apakah user memiliki akses CBG
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_tpostingkasir.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            // Log untuk debugging
            Log::info('TPostingKasir Index accessed', [
                'user' => Auth::user()->username ?? 'unknown',
                'cbg' => $CBG,
                'periode' => $request->session()->get('periode')
            ]);

            return view("otransaksi_tpostingkasir.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPostingKasir index: ' . $e->getMessage());

            return view("otransaksi_tpostingkasir.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function browse(Request $request)
    {
        // Method ini tidak digunakan di Delphi, bisa dihapus
        return response()->json(['error' => 'Method tidak digunakan'], 400);
    }

    public function gettpostingkasir_posting(Request $request)
    {
        try {
            // Log request untuk debugging
            Log::info('gettpostingkasir_posting called', [
                'user' => Auth::user()->username ?? 'unknown',
                'has_periode' => $request->session()->has('periode')
            ]);

            if ($request->session()->has('periode')) {
                $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
            } else {
                Log::warning('Periode belum diset');
                return response()->json([
                    'error' => 'Periode belum diset. Silakan set periode terlebih dahulu.',
                    'draw' => intval($request->input('draw', 0)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], 200);
            }

            $CBG = Auth::user()->CBG;
            if (!$CBG) {
                Log::warning('CBG tidak ditemukan untuk user: ' . Auth::user()->username);
                return response()->json([
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.',
                    'draw' => intval($request->input('draw', 0)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], 200);
            }

            // Cek koneksi database dan tabel
            try {
                $tableExists = DB::select("SHOW TABLES LIKE 'jual'");
                if (empty($tableExists)) {
                    Log::error('Tabel jual tidak ditemukan');
                    return response()->json([
                        'error' => 'Tabel jual tidak ditemukan di database',
                        'draw' => intval($request->input('draw', 0)),
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => []
                    ], 200);
                }
            } catch (\Exception $e) {
                Log::error('Error checking table: ' . $e->getMessage());
            }

            Log::info('Fetching data', ['periode' => $periode, 'cbg' => $CBG]);

            // Use Query Builder for DataTables server-side processing
            $query = DB::table('jual')
                ->select(
                    'NO_ID',
                    'NO_BUKTI',
                    'TGL',
                    DB::raw('kodeC as KODES'),
                    DB::raw('namaC as NAMAS'),
                    'TOTAL',
                    DB::raw('posted as POSTED'),
                    DB::raw("CASE WHEN posted = 1 THEN 'Posted' ELSE 'Unposted' END as STATUS")
                )
                ->where('per', $periode)
                ->where('CBG', $CBG)
                ->orderBy('TGL', 'DESC')
                ->orderBy('NO_BUKTI', 'DESC');

            $count = $query->count();
            Log::info('Data fetched successfully', ['count' => $count]);

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return $row->TGL ? date('Y-m-d', strtotime($row->TGL)) : '-';
                })
                ->editColumn('TOTAL', function ($row) {
                    return $row->TOTAL ?? 0;
                })
                ->editColumn('KODES', function ($row) {
                    return $row->KODES ?? '-';
                })
                ->editColumn('NAMAS', function ($row) {
                    return $row->NAMAS ?? '-';
                })
                ->addColumn('cek', function ($row) {
                    return '<input type="checkbox" name="cek[]" class="form-control cek" ' .
                        ($row->POSTED == 1 ? 'checked' : '') .
                        ' value="' . $row->NO_BUKTI . '">';
                })
                ->rawColumns(['cek'])
                ->make(true);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database Query Error in gettpostingkasir_posting', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? []
            ]);
            return response()->json([
                'error' => 'Error database: ' . $e->getMessage(),
                'draw' => intval($request->input('draw', 0)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in gettpostingkasir_posting', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'draw' => intval($request->input('draw', 0)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 200);
        }
    }

    /**
     * Posting Bulk - Sesuai implementasi Delphi
     * Menggunakan stored procedure postjualtgl dengan parameter tgl dan cbg
     * DENGAN VALIDASI DATA SEBELUM MEMANGGIL PROCEDURE
     */
    public function posting_bulk(Request $request)
    {
        DB::beginTransaction();

        try {
            $tgl = $request->tgl_posting;
            $CBG = Auth::user()->CBG;

            if (!$tgl) {
                return response()->json(['error' => 'Tanggal posting harus diisi'], 400);
            }

            if (!$CBG) {
                return response()->json(['error' => 'CBG tidak ditemukan'], 400);
            }

            if ($request->session()->has('periode')) {
                $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
            } else {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            // Cek periode sudah ditutup atau belum
            $cekperid = DB::select("SELECT POSTED from perid WHERE PERIO = ?", [$periode]);
            if (!empty($cekperid)) {
                $posted = $cekperid[0]->POSTED ?? $cekperid[0]->posted ?? null;
                if ($posted == 1 || $posted == '1') {
                    return response()->json(['error' => 'Periode sudah ditutup'], 400);
                }
            }

            // Format tanggal ke format MySQL (yyyy-mm-dd)
            $tglFormatted = date('Y-m-d', strtotime($tgl));

            Log::info('Validating data before calling procedure', [
                'tgl_input' => $tgl,
                'tgl_formatted' => $tglFormatted,
                'cbg' => $CBG,
                'user' => Auth::user()->username
            ]);

            // ===== VALIDASI DATA SEBELUM MEMANGGIL PROCEDURE =====
            // 1. Cek apakah ada data jual untuk tanggal dan CBG tersebut
            // TIDAK mengecek BKTKLR karena akan diisi oleh stored procedure
            $cekData = DB::select("
                SELECT COUNT(*) as jumlah, 
                       LEFT(TRIM(PER), 2) AS MON, 
                       RIGHT(TRIM(PER), 4) AS YER 
                FROM jual 
                WHERE DATE(TGL) = ? AND CBG = ?
                GROUP BY PER
                LIMIT 1
            ", [$tglFormatted, $CBG]);

            // Log hasil query untuk debugging
            Log::info('Validasi query result', [
                'query' => 'SELECT COUNT(*) FROM jual WHERE DATE(TGL) = ? AND CBG = ?',
                'params' => [$tglFormatted, $CBG],
                'result_count' => count($cekData),
                'result_data' => $cekData
            ]);

            if (empty($cekData) || $cekData[0]->jumlah == 0) {
                Log::warning('Tidak ada data untuk diposting', [
                    'tgl_formatted' => $tglFormatted,
                    'cbg' => $CBG,
                    'cekData' => $cekData
                ]);

                return response()->json([
                    'error' => 'Tidak ada data transaksi untuk tanggal ' . date('d-m-Y', strtotime($tgl)) . ' di cabang ' . $CBG
                ], 400);
            }

            $MONX = $cekData[0]->MON;
            $YERX = $cekData[0]->YER;

            Log::info('Data validation passed', [
                'jumlah_data' => $cekData[0]->jumlah,
                'bulan' => $MONX,
                'tahun' => $YERX
            ]);

            // 2. Cek apakah sudah pernah diposting sebelumnya
            $cekPosting = DB::select("
                SELECT COUNT(*) as sudah 
                FROM histo_posting 
                WHERE KET = 'postjualtgl' 
                  AND DATE(TGL) = ? 
                  AND CBG = ?
            ", [$tglFormatted, $CBG]);

            $sudahPosting = $cekPosting[0]->sudah ?? 0;

            if ($sudahPosting > 0) {
                Log::warning('Data sudah pernah diposting', [
                    'tgl' => $tglFormatted,
                    'cbg' => $CBG
                ]);
                return response()->json([
                    'error' => 'Data tanggal ' . date('d-m-Y', strtotime($tgl)) . ' sudah pernah diposting sebelumnya'
                ], 400);
            }

            // 3. Validasi tabel target (juald01-juald12, jual01-jual12) ada atau tidak
            $targetTable = 'juald' . str_pad($MONX, 2, '0', STR_PAD_LEFT);
            $targetTableJual = 'jual' . str_pad($MONX, 2, '0', STR_PAD_LEFT);

            try {
                // SHOW TABLES tidak support prepared statement, harus menggunakan string langsung
                $cekTabel = DB::select("SHOW TABLES LIKE '" . $targetTable . "'");
                if (empty($cekTabel)) {
                    Log::error('Tabel target tidak ditemukan', ['table' => $targetTable]);
                    return response()->json([
                        'error' => 'Tabel ' . $targetTable . ' tidak ditemukan. Hubungi administrator.'
                    ], 500);
                }

                $cekTabelJual = DB::select("SHOW TABLES LIKE '" . $targetTableJual . "'");
                if (empty($cekTabelJual)) {
                    Log::error('Tabel target tidak ditemukan', ['table' => $targetTableJual]);
                    return response()->json([
                        'error' => 'Tabel ' . $targetTableJual . ' tidak ditemukan. Hubungi administrator.'
                    ], 500);
                }
            } catch (\Exception $e) {
                Log::error('Error validating target tables', ['error' => $e->getMessage()]);
                return response()->json([
                    'error' => 'Error validasi tabel: ' . $e->getMessage()
                ], 500);
            }

            Log::info('All validations passed, calling postjualtgl procedure', [
                'tgl' => $tglFormatted,
                'cbg' => $CBG
            ]);

            // ===== PANGGIL STORED PROCEDURE =====
            // Gunakan DB::statement untuk memanggil stored procedure
            try {
                Log::info('Calling postjualtgl stored procedure', [
                    'tgl' => $tglFormatted,
                    'cbg' => $CBG
                ]);

                // Call stored procedure using DB::statement
                DB::statement('CALL postjualtgl(?, ?)', [$tglFormatted, $CBG]);

                Log::info('postjualtgl procedure completed successfully', [
                    'tgl' => $tglFormatted,
                    'cbg' => $CBG,
                    'total_transaksi' => $cekData[0]->jumlah
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Posting Selesai untuk tanggal ' . date('d-m-Y', strtotime($tgl)) . ' - Total: ' . $cekData[0]->jumlah . ' transaksi'
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                $errorMessage = $e->getMessage();
                Log::error('Database Error in postjualtgl procedure', [
                    'error' => $errorMessage,
                    'code' => $e->getCode(),
                    'sql' => $e->getSql() ?? 'N/A'
                ]);

                // Parse error message untuk memberikan pesan yang lebih user-friendly
                if (strpos($errorMessage, '1329') !== false) {
                    return response()->json([
                        'error' => 'Tidak ada data yang memenuhi kriteria untuk diposting. Pastikan data transaksi memiliki BKTKLR yang valid.'
                    ], 400);
                }

                if (strpos($errorMessage, 'PROCEDURE') !== false && strpos($errorMessage, 'does not exist') !== false) {
                    return response()->json([
                        'error' => 'Stored procedure postjualtgl tidak ditemukan di database. Hubungi administrator.'
                    ], 500);
                }

                return response()->json([
                    'error' => 'Error procedure: ' . $errorMessage
                ], 500);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            Log::error('Database Error in posting_bulk', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? []
            ]);

            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in posting_bulk: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Posting gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method posting dan unposting tidak diperlukan lagi
     * karena Delphi hanya menggunakan stored procedure postjualtgl
     * Namun tetap dipertahankan untuk backward compatibility
     */
    public function posting(Request $request)
    {
        return response()->json([
            'error' => 'Method ini tidak digunakan. Gunakan posting bulk dengan stored procedure.'
        ], 400);
    }

    public function unposting(Request $request)
    {
        return response()->json([
            'error' => 'Method unposting tidak tersedia. Posting dilakukan via stored procedure postjualtgl.'
        ], 400);
    }

    public function jasper(Request $request)
    {
        // Method ini tidak ada di Delphi, bisa dihapus
        return redirect()->back()->with('error', 'Fitur cetak laporan tidak tersedia');
    }
}
