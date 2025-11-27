<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TPostingReportController extends Controller
{
    var $judul = 'Posting Report';
    var $FLAGZ = 'PR';

    public function index(Request $request)
    {
        try {
            if (!$request->session()->has('periode')) {
                return view("otransaksi_TPostingReport.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TPostingReport.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            return view("otransaksi_TPostingReport.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPostingReport index: ' . $e->getMessage());
            return view("otransaksi_TPostingReport.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function gettpostingreport_posting(Request $request)
    {
        try {
            if (!$request->session()->has('periode')) {
                return response()->json([
                    'error' => 'Periode belum diset',
                    'draw' => intval($request->input('draw', 0)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], 200);
            }

            $bulan = $request->session()->get('periode')['bulan'];
            $CBG = Auth::user()->CBG;

            if (!$CBG) {
                return response()->json([
                    'error' => 'CBG tidak ditemukan',
                    'draw' => intval($request->input('draw', 0)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], 200);
            }

            $monthString = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            $tableName = 'juald' . $monthString;

            // Get database name and check if table exists
            $dbName = config('database.connections.mysql.database');
            $tableExists = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = ? 
                AND table_name = ?
            ", [$dbName, $tableName]);

            if (empty($tableExists) || $tableExists[0]->count == 0) {
                return response()->json([
                    'error' => "Tabel {$tableName} tidak ditemukan",
                    'draw' => intval($request->input('draw', 0)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], 200);
            }

            Log::info('TPostingReport getData: Querying data', [
                'table' => $tableName,
                'cbg' => $CBG
            ]);

            $query = DB::SELECT("
                SELECT DATE(tgl) as tgl, 
                       DATE_FORMAT(tgl, '%d-%m-%Y') as tgl_format,
                       COUNT(*) as jml_transaksi,
                       SUM(total) as total_amount,
                       IF(EXISTS(SELECT 1 FROM {$CBG}.repjuald WHERE DATE(tgl) = DATE(r.tgl) LIMIT 1), 1, 0) as posted
                FROM (
                    SELECT DISTINCT tgl, total
                    FROM {$tableName}
                    WHERE cbg = ?
                    AND flag NOT IN ('ZP', 'OB')
                ) r
                GROUP BY DATE(tgl)
                ORDER BY tgl DESC
            ", [$CBG]);

            Log::info('TPostingReport getData: Query result', [
                'count' => count($query)
            ]);

            return Datatables::of($query)
                ->addIndexColumn()
                ->addColumn('cek', function ($row) {
                    return '<input type="checkbox" name="cek[]" class="form-control cek" ' .
                        ($row->posted == 1 ? 'disabled checked' : '') .
                        ' value="' . $row->tgl . '">';
                })
                ->addColumn('status', function ($row) {
                    if ($row->posted == 1) {
                        return '<span class="status-posted"><i class="fas fa-check-circle"></i> Posted</span>';
                    } else {
                        return '<span class="status-unposted"><i class="fas fa-times-circle"></i> Unposted</span>';
                    }
                })
                ->rawColumns(['cek', 'status'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in gettpostingreport_posting: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'draw' => intval($request->input('draw', 0)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 200);
        }
    }

    public function posting(Request $request)
    {
        try {
            $request->validate([
                'tgl_posting' => 'required|date'
            ]);

            $tgl = $request->tgl_posting;

            Log::info('TPostingReport Posting Started:', ['tgl' => $tgl]);

            if (!$request->session()->has('periode')) {
                Log::warning('TPostingReport: Periode belum diset');
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            $bulan = $request->session()->get('periode')['bulan'];
            $monthString = str_pad($bulan, 2, '0', STR_PAD_LEFT);

            Log::info('TPostingReport: Checking if already posted', ['tgl' => $tgl]);

            $cekPosted = DB::SELECT("SELECT * FROM repjuald WHERE DATE(tgl) = ? LIMIT 1", [$tgl]);
            if (count($cekPosted) > 0) {
                Log::warning('TPostingReport: Already posted', ['tgl' => $tgl]);
                return response()->json(['error' => 'Tanggal tersebut sudah terposting'], 400);
            }

            DB::beginTransaction();

            // Get cabang list with proper column names
            $cabangQuery = "SELECT NO_ID, KODE, NAMA_TOKO, TYPE, STA FROM toko WHERE STA IN ('MA','CB') ORDER BY NO_ID";

            Log::info('TPostingReport: Getting cabang list', ['query' => $cabangQuery]);

            $cabangList = DB::SELECT($cabangQuery);

            if (empty($cabangList)) {
                Log::warning('TPostingReport: No cabang found');
                DB::rollBack();
                return response()->json(['error' => 'Tidak ada data cabang yang ditemukan'], 400);
            }

            Log::info('TPostingReport: Processing cabang', ['count' => count($cabangList)]);

            foreach ($cabangList as $cabang) {
                // Check which property exists
                $cbg = null;

                if (isset($cabang->KODE)) {
                    $cbg = trim($cabang->KODE);
                } elseif (isset($cabang->kode)) {
                    $cbg = trim($cabang->kode);
                } elseif (isset($cabang->Kode)) {
                    $cbg = trim($cabang->Kode);
                } else {
                    Log::error('TPostingReport: Property kode not found in cabang object', [
                        'cabang_properties' => get_object_vars($cabang)
                    ]);
                    continue;
                }

                Log::info('TPostingReport: Processing cabang', [
                    'cbg' => $cbg,
                    'nama' => $cabang->NAMA_TOKO ?? $cabang->nama_toko ?? 'N/A'
                ]);

                try {
                    $insertQuery = "
                        INSERT INTO " . $cbg . ".repjuald 
                        (sub, sub2, kd_brg, na_brg, barcode, qty, harga, diskon, ppn, nppn, dpp, tkp, total, flag, type, per, tgl, cbg, kodes)
                        SELECT 
                            LEFT(juald" . $monthString . ".KD_BRG, 3),
                            juald" . $monthString . ".sub2,
                            juald" . $monthString . ".KD_BRG,
                            juald" . $monthString . ".NA_BRG,
                            juald" . $monthString . ".BARCODE,
                            SUM(juald" . $monthString . ".qty),
                            juald" . $monthString . ".harga,
                            SUM(juald" . $monthString . ".diskon),
                            juald" . $monthString . ".ppn,
                            SUM(juald" . $monthString . ".nppn),
                            SUM(juald" . $monthString . ".dpp),
                            SUM(juald" . $monthString . ".tkp),
                            SUM(juald" . $monthString . ".total),
                            juald" . $monthString . ".flag,
                            juald" . $monthString . ".type,
                            juald" . $monthString . ".per,
                            juald" . $monthString . ".tgl,
                            juald" . $monthString . ".cbg,
                            brg.supp
                        FROM " . $cbg . ".juald" . $monthString . ", " . $cbg . ".brg
                        WHERE juald" . $monthString . ".KD_BRG = brg.KD_BRG 
                        AND juald" . $monthString . ".flag <> 'ZP'
                        AND juald" . $monthString . ".flag <> 'OB'
                        AND DATE(juald" . $monthString . ".tgl) = ?
                        GROUP BY KD_BRG, TGL, CBG 
                        ORDER BY tgl, cbg, kd_brg
                    ";

                    Log::info('TPostingReport: Inserting to repjuald', [
                        'cbg' => $cbg,
                        'tgl' => $tgl
                    ]);

                    DB::statement($insertQuery, [$tgl]);

                    $weekInfoQuery = "
                        SELECT 
                            WEEKOFYEAR(?) as minggu,
                            IF(MONTH(?) = 12 AND WEEKOFYEAR(?) = 1, YEAR(?) + 1, YEAR(?)) as yer,
                            LPAD(MONTH(?), 2, 0) as mon,
                            DATE_SUB(?, INTERVAL 30 DAY) as ini,
                            DAYOFWEEK(?) as hari
                    ";

                    $weekInfo = DB::SELECT($weekInfoQuery, [$tgl, $tgl, $tgl, $tgl, $tgl, $tgl, $tgl, $tgl]);

                    if (empty($weekInfo)) {
                        Log::warning('TPostingReport: Week info not found', ['cbg' => $cbg]);
                        continue;
                    }

                    $perhps = $weekInfo[0]->ini;
                    $minggu = $weekInfo[0]->minggu;
                    $yer = $weekInfo[0]->yer;
                    $hari = $weekInfo[0]->hari;

                    Log::info('TPostingReport: Week info', [
                        'cbg' => $cbg,
                        'minggu' => $minggu,
                        'yer' => $yer,
                        'hari' => $hari
                    ]);

                    DB::statement("DELETE FROM " . $cbg . ".repjuald WHERE DATE(tgl) = ?", [$perhps]);

                    if ($hari == '1') {
                        Log::info('TPostingReport: Inserting to repjual (hari = 1)', ['cbg' => $cbg]);

                        DB::statement("
                            INSERT INTO " . $cbg . ".repjual 
                            (minggu, yer, sub, sub2, kd_brg, na_brg, barcode, qty, harga, diskon, ppn, nppn, dpp, tkp, total, flag, type, cbg, kodes)
                            SELECT 
                                ?,
                                ?,
                                sub,
                                sub2,
                                kd_brg,
                                na_brg,
                                barcode,
                                SUM(qty),
                                SUM(harga),
                                SUM(diskon),
                                ppn,
                                SUM(nppn),
                                SUM(dpp),
                                SUM(tkp),
                                SUM(total),
                                flag,
                                type,
                                cbg,
                                kodes
                            FROM " . $cbg . ".repjuald
                            WHERE WEEKOFYEAR(tgl) = ?
                            GROUP BY KD_BRG, CBG 
                            ORDER BY cbg, kd_brg
                        ", [$minggu, $yer, $minggu]);

                        DB::statement("
                            DELETE FROM " . $cbg . ".repjual 
                            WHERE minggu = ? AND yer = ? - 1
                        ", [$minggu, $yer]);
                    }

                    Log::info('TPostingReport: Cabang processed successfully', ['cbg' => $cbg]);
                } catch (\Exception $e) {
                    Log::error('TPostingReport: Error processing cabang', [
                        'cbg' => $cbg,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue to next cabang instead of failing completely
                    continue;
                }
            }

            DB::commit();

            Log::info('TPostingReport: Posting completed successfully', ['tgl' => $tgl]);

            return response()->json([
                'success' => true,
                'message' => 'Posting Selesai'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TPostingReport Error in posting: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Posting gagal: ' . $e->getMessage()], 500);
        }
    }

    public function unposting(Request $request)
    {
        try {
            $request->validate(['cek' => 'required']);

            if (empty($request->cek)) {
                Log::warning('TPostingReport Unposting: No data selected');
                return response()->json(['error' => 'Tidak ada data yang dipilih'], 400);
            }

            Log::info('TPostingReport Unposting Started:', ['dates' => $request->cek]);

            DB::beginTransaction();

            // Get cabang list with proper column names
            $cabangQuery = "SELECT NO_ID, KODE, NAMA_TOKO, TYPE, STA FROM toko WHERE STA IN ('MA','CB') ORDER BY NO_ID";

            Log::info('TPostingReport Unposting: Getting cabang list', ['query' => $cabangQuery]);

            $cabangList = DB::SELECT($cabangQuery);

            if (empty($cabangList)) {
                Log::warning('TPostingReport Unposting: No cabang found');
                DB::rollBack();
                return response()->json(['error' => 'Tidak ada data cabang yang ditemukan'], 400);
            }

            Log::info('TPostingReport Unposting: Processing dates', [
                'date_count' => count($request->cek),
                'cabang_count' => count($cabangList)
            ]);

            foreach ($request->cek as $tgl) {
                Log::info('TPostingReport Unposting: Processing date', ['tgl' => $tgl]);

                foreach ($cabangList as $cabang) {
                    // Check which property exists
                    $cbg = null;

                    if (isset($cabang->KODE)) {
                        $cbg = trim($cabang->KODE);
                    } elseif (isset($cabang->kode)) {
                        $cbg = trim($cabang->kode);
                    } elseif (isset($cabang->Kode)) {
                        $cbg = trim($cabang->Kode);
                    } else {
                        Log::error('TPostingReport Unposting: Property kode not found in cabang object', [
                            'cabang_properties' => get_object_vars($cabang)
                        ]);
                        continue;
                    }

                    try {
                        Log::info('TPostingReport Unposting: Deleting from repjuald', [
                            'cbg' => $cbg,
                            'tgl' => $tgl
                        ]);

                        DB::statement("DELETE FROM " . $cbg . ".repjuald WHERE DATE(tgl) = ?", [$tgl]);
                    } catch (\Exception $e) {
                        Log::error('TPostingReport Unposting: Error deleting from cabang', [
                            'cbg' => $cbg,
                            'tgl' => $tgl,
                            'error' => $e->getMessage()
                        ]);
                        // Continue to next cabang
                        continue;
                    }
                }
            }

            DB::commit();

            Log::info('TPostingReport: Unposting completed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Unposting berhasil'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TPostingReport Error in unposting: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Unposting gagal: ' . $e->getMessage()], 500);
        }
    }

    public function jasper(Request $request)
    {
        try {
            if (!$request->session()->has('periode')) {
                return redirect()->back()->with('error', 'Periode belum diset');
            }

            $bulan = $request->session()->get('periode')['bulan'];
            $CBG = Auth::user()->CBG;

            if (!$CBG) {
                return redirect()->back()->with('error', 'CBG tidak ditemukan');
            }

            $monthString = str_pad($bulan, 2, '0', STR_PAD_LEFT);

            Log::info('TPostingReport Jasper: Generating report', [
                'cbg' => $CBG,
                'bulan' => $monthString
            ]);

            $query = DB::SELECT("
                SELECT DATE(tgl) as tgl, 
                       DATE_FORMAT(tgl, '%d-%m-%Y') as tgl_format,
                       COUNT(*) as jml_transaksi,
                       SUM(total) as total_amount,
                       IF(EXISTS(SELECT 1 FROM {$CBG}.repjuald WHERE DATE(tgl) = DATE(r.tgl) LIMIT 1), 'Posted', 'Unposted') as status
                FROM (
                    SELECT DISTINCT tgl, total
                    FROM juald" . $monthString . "
                    WHERE cbg = ?
                    AND flag NOT IN ('ZP', 'OB')
                ) r
                GROUP BY DATE(tgl)
                ORDER BY tgl DESC
            ", [$CBG]);

            $data = [];
            foreach ($query as $value) {
                $data[] = [
                    'TGL' => $value->tgl_format,
                    'JML_TRANSAKSI' => number_format($value->jml_transaksi, 0, ',', '.'),
                    'TOTAL' => number_format($value->total_amount, 2, ',', '.'),
                    'STATUS' => $value->status,
                    'JUDUL' => $this->judul,
                    'CBG' => $CBG
                ];
            }

            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/posting_report_laporan.jrxml');
            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate laporan: ' . $e->getMessage());
        }
    }
}
