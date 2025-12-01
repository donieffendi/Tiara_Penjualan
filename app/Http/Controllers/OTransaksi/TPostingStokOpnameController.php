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

class TPostingStokOpnameController extends Controller
{
    var $judul = 'Posting Stok Opname';
    var $FLAGZ = 'FS';

    public function index(Request $request)
    {
        try {
            Log::info('=== TPostingStokOpname INDEX ===', [
                'user' => Auth::user()->username ?? 'unknown',
                'cbg' => Auth::user()->CBG ?? null
            ]);

            if (!$request->session()->has('periode')) {
                Log::warning('Periode belum diset');
                return view("otransaksi_TPostingStokOpname.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::error('User tidak memiliki CBG');
                return view("otransaksi_TPostingStokOpname.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            Log::info('Halaman index dimuat sukses', ['cbg' => $CBG]);

            return view("otransaksi_TPostingStokOpname.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPostingStokOpname index: ' . $e->getMessage());
            return view("otransaksi_TPostingStokOpname.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function gettpostingstokopname_posting(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            Log::info('=== REQUEST getData ===', [
                'all_params' => $request->all(),
                'cbg' => $CBG,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            if (!$CBG) {
                Log::error('User tidak memiliki CBG di method getData');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $flagz = $request->input('flagz', 'FS');
            if (empty($flagz)) {
                $flagz = 'FS';
            }

            Log::info('Get data posting stok opname', [
                'cbg' => $CBG,
                'flagz' => $flagz
            ]);

            // Gunakan database connection sesuai CBG (TIDAK ada filter cbg di WHERE)
            $connection = strtolower($CBG);

            $sql = "
                SELECT
                    no_bukti,
                    tgl,
                    notes,
                    namas,
                    total_qty as total,
                    no_post,
                    posted as cek
                FROM stockb
                WHERE flag = '{$flagz}'
                AND posted = 0
                ORDER BY no_bukti
            ";

            Log::info('QUERY - Get Data Posting', [
                'connection' => $connection,
                'sql' => $sql,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sql))
            ]);

            $query = DB::connection($connection)->select("
                SELECT
                    no_bukti,
                    tgl,
                    notes,
                    namas,
                    total_qty as total,
                    no_post,
                    posted as cek
                FROM stockb
                WHERE flag = ?
                AND posted = 0
                ORDER BY no_bukti
            ", [$flagz]);

            $recordCount = is_array($query) ? count($query) : (is_object($query) ? count((array)$query) : 0);
            Log::info('Data ditemukan: ' . $recordCount . ' record');

            if ($recordCount > 0) {
                Log::info('Sample data pertama:', [
                    'data' => json_encode($query[0] ?? null)
                ]);
            } else {
                Log::warning('TIDAK ADA DATA ditemukan untuk flagz: ' . $flagz . ' dengan posted = 0');
            }

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->editColumn('tgl', function ($row) {
                    return date('d-m-Y', strtotime($row->tgl));
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->total, 0, ',', '.');
                })
                ->addColumn('cek_checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check-input cek-item" value="' . $row->no_bukti . '" data-cek="' . $row->cek . '">';
                })
                ->addColumn('status', function ($row) {
                    if ($row->cek == 1) {
                        return '<span class="badge badge-success">Posted</span>';
                    } else {
                        return '<span class="badge badge-danger">Belum</span>';
                    }
                })
                ->rawColumns(['cek_checkbox', 'status'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in gettpostingstokopname_posting', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function posting_bulk(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $USERNAME = Auth::user()->username ?? 'unknown';

            if (!$CBG) {
                Log::error('Posting bulk gagal: User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $noBuktiList = $request->input('no_bukti_list', []);
            $flagz = $request->input('flagz', 'FS');

            Log::info('=== MULAI POSTING BULK ===', [
                'cbg' => $CBG,
                'username' => $USERNAME,
                'flagz' => $flagz,
                'jumlah_dokumen' => count($noBuktiList),
                'no_bukti_list' => $noBuktiList
            ]);

            if (empty($noBuktiList)) {
                Log::warning('Tidak ada data yang dipilih');
                return response()->json(['error' => 'Tidak ada data yang dipilih untuk diposting'], 400);
            }

            // Batasi maksimal 6 dokumen per proses (sesuai Delphi)
            if (count($noBuktiList) > 6) {
                Log::warning('Melebihi batas maksimal: ' . count($noBuktiList) . ' dokumen');
                return response()->json(['error' => 'Maksimal 6 dokumen dapat diproses sekaligus'], 400);
            }

            // Gunakan connection sesuai CBG
            $connection = strtolower($CBG);
            DB::connection($connection)->beginTransaction();
            Log::info('Database transaction dimulai pada connection: ' . $connection);

            $processedReports = [];

            foreach ($noBuktiList as $noBukti) {
                Log::info('Memproses posting untuk no_bukti: ' . $noBukti);
                $reportData = $this->processPosting($noBukti, $flagz, $CBG);
                if ($reportData) {
                    $processedReports[] = $reportData;
                }
                Log::info('Posting berhasil untuk no_bukti: ' . $noBukti);
            }

            DB::connection($connection)->commit();
            Log::info('=== POSTING BULK SELESAI SUKSES ===', [
                'jumlah_dokumen' => count($noBuktiList)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Posting berhasil untuk ' . count($noBuktiList) . ' dokumen',
                'reports' => $processedReports
            ]);
        } catch (\Exception $e) {
            $connection = strtolower($CBG ?? 'mysql');
            DB::connection($connection)->rollBack();

            Log::error('=== POSTING BULK GAGAL ===', [
                'flagz' => $flagz ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Posting gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processPosting($noBukti, $flagz, $CBG)
    {
        try {
            $connection = strtolower($CBG);

            Log::info('Memulai processPosting', [
                'no_bukti' => $noBukti,
                'flagz' => $flagz,
                'cbg' => $CBG,
                'connection' => $connection
            ]);

            // Ambil detail transaksi dari stockbd
            $sqlDetail = "
                SELECT
                    stockbd.NO_ID,
                    stockbd.KD_BRG,
                    stockbd.QTY,
                    stockbd.FLAG
                FROM stockbd
                WHERE stockbd.no_bukti = '{$noBukti}'
            ";

            Log::info('QUERY - Detail Stok Opname', [
                'connection' => $connection,
                'sql' => $sqlDetail,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlDetail))
            ]);

            $details = DB::connection($connection)->select("
                SELECT
                    stockbd.NO_ID,
                    stockbd.KD_BRG,
                    stockbd.QTY,
                    stockbd.FLAG
                FROM stockbd
                WHERE stockbd.no_bukti = ?
            ", [$noBukti]);

            Log::info('Detail data ditemukan: ' . count($details) . ' item');

            // Process setiap detail - update stok
            foreach ($details as $detail) {
                $kdBrg = $detail->KD_BRG;
                $qty = $detail->QTY;

                Log::info('Proses item', [
                    'kd_brg' => $kdBrg,
                    'qty' => $qty
                ]);

                // Update brgfcd (Stok Opname FC)
                $sqlUpdate = "UPDATE brgfcd SET ln00 = ln00 + {$qty}, ak00 = aw00 + ma00 - ke00 + ln00 WHERE kd_brg = '{$kdBrg}'";
                Log::info('QUERY - Update brgfcd', [
                    'connection' => $connection,
                    'kd_brg' => $kdBrg,
                    'qty' => $qty,
                    'raw_query_untuk_navicat' => $sqlUpdate
                ]);

                DB::connection($connection)->statement("
                    UPDATE brgfcd
                    SET
                        ln00 = ln00 + ?,
                        ak00 = aw00 + ma00 - ke00 + ln00
                    WHERE kd_brg = ?
                ", [$qty, $kdBrg]);
            }

            // Call stored procedure untuk post
            Log::info('Memanggil stored procedure poststkb untuk no_bukti: ' . $noBukti);

            $sqlCallSP = "CALL poststkb('{$noBukti}')";
            Log::info('QUERY - Call Stored Procedure', [
                'connection' => $connection,
                'no_bukti' => $noBukti,
                'raw_query_untuk_navicat' => $sqlCallSP
            ]);

            DB::connection($connection)->statement("CALL poststkb(?)", [$noBukti]);
            Log::info('Stored procedure poststkb selesai');

            // Generate data untuk report
            $reportData = $this->generateReportData($noBukti, $CBG);

            return $reportData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function generateReportData($noBukti, $CBG)
    {
        try {
            $connection = strtolower($CBG);

            $sqlReport = "
                SELECT
                    CONCAT(LEFT(stockbz.nolap, 2), RIGHT(stockbz.nolap, 5)) as bukt,
                    stockbz.no_bukti,
                    stockbz.tgl,
                    stockbzd.KD_BRG,
                    stockbzd.NA_BRG,
                    maskfc.ket_uk,
                    maskfc.hj,
                    IF(qty >= 0, qty, 0) as pos,
                    IF(qty < 0, qty * -1, 0) as neg,
                    IF(qty >= 0, 100 * (qty) / stockbzd.saldo, 0) as posp,
                    IF(qty < 0, 100 * (qty * -1) / stockbzd.saldo, 0) as negp,
                    IF(qty >= 0, ROUND(qty * maskfc.hj), 0) as posr,
                    IF(qty < 0, ROUND(qty * -1 * maskfc.hj), 0) as negr
                FROM stockbz
                INNER JOIN stockbzd ON stockbz.no_bukti = stockbzd.no_bukti
                LEFT JOIN maskfc ON maskfc.KD_BRG = stockbzd.KD_BRG
                WHERE stockbz.no_bukti = '{$noBukti}'
                AND qty <> 0
            ";

            Log::info('QUERY - Generate Report Data', [
                'connection' => $connection,
                'no_bukti' => $noBukti,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlReport))
            ]);

            $reportQuery = DB::connection($connection)->select("
                SELECT
                    CONCAT(LEFT(stockbz.nolap, 2), RIGHT(stockbz.nolap, 5)) as bukt,
                    stockbz.no_bukti,
                    stockbz.tgl,
                    stockbzd.KD_BRG,
                    stockbzd.NA_BRG,
                    maskfc.ket_uk,
                    maskfc.hj,
                    IF(qty >= 0, qty, 0) as pos,
                    IF(qty < 0, qty * -1, 0) as neg,
                    IF(qty >= 0, 100 * (qty) / stockbzd.saldo, 0) as posp,
                    IF(qty < 0, 100 * (qty * -1) / stockbzd.saldo, 0) as negp,
                    IF(qty >= 0, ROUND(qty * maskfc.hj), 0) as posr,
                    IF(qty < 0, ROUND(qty * -1 * maskfc.hj), 0) as negr
                FROM stockbz
                INNER JOIN stockbzd ON stockbz.no_bukti = stockbzd.no_bukti
                LEFT JOIN maskfc ON maskfc.KD_BRG = stockbzd.KD_BRG
                WHERE stockbz.no_bukti = ?
                AND qty <> 0
            ", [$noBukti]);

            return [
                'no_bukti' => $noBukti,
                'data' => $reportQuery
            ];
        } catch (\Exception $e) {
            Log::error('Error generating report data: ' . $e->getMessage());
            return null;
        }
    }

    public function jasper(Request $request)
    {
        try {
            $judul = $this->judul;
            $CBG = Auth::user()->CBG ?? null;
            $flagz = $request->input('flagz', 'FS');

            Log::info('Generate laporan jasper', [
                'cbg' => $CBG,
                'flagz' => $flagz
            ]);

            if (!$CBG) {
                Log::error('Jasper error: User tidak memiliki CBG');
                return redirect()->back()->with('error', 'User tidak memiliki akses cabang');
            }

            $connection = strtolower($CBG);

            $sqlJasper = "
                SELECT
                    no_bukti,
                    tgl,
                    notes,
                    namas,
                    total_qty as total,
                    IF(posted = 1, 'Sudah Posting', 'Belum Posting') as status_text
                FROM stockb
                WHERE flag = '{$flagz}'
                ORDER BY no_bukti
            ";

            Log::info('QUERY - Jasper Report', [
                'connection' => $connection,
                'flagz' => $flagz,
                'sql' => $sqlJasper,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlJasper))
            ]);

            $query = DB::connection($connection)->select("
                SELECT
                    no_bukti,
                    tgl,
                    notes,
                    namas,
                    total_qty as total,
                    IF(posted = 1, 'Sudah Posting', 'Belum Posting') as status_text
                FROM stockb
                WHERE flag = ?
                ORDER BY no_bukti
            ", [$flagz]);

            $data = [];
            foreach ($query as $value) {
                array_push($data, array(
                    'NO_BUKTI' => $value->no_bukti,
                    'TANGGAL' => date('d-m-Y', strtotime($value->tgl)),
                    'NOTES' => $value->notes,
                    'SUPPLIER' => $value->namas,
                    'TOTAL' => number_format($value->total, 0, ',', '.'),
                    'STATUS' => $value->status_text,
                    'JUDUL' => $judul
                ));
            }

            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/posting_stok_opname.jrxml');
            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate laporan: ' . $e->getMessage());
        }
    }
}
