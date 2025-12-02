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

            // Query dari tabel lapbh untuk Stock Opname
            $sql = "
                SELECT
                    no_bukti,
                    tgl,
                    sub,
                    usrnm,
                    posted as cek
                FROM lapbh
                WHERE flag = '{$flagz}'
                AND cbg = '{$CBG}'
                ORDER BY no_bukti DESC
            ";

            Log::info('QUERY - Get Data Posting', [
                'connection' => 'mysql',
                'sql' => $sql,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sql))
            ]);

            $query = DB::select("
                SELECT
                    no_bukti,
                    tgl,
                    sub,
                    usrnm,
                    posted as cek
                FROM lapbh
                WHERE flag = ?
                AND cbg = ?
                ORDER BY no_bukti DESC
            ", [$flagz, $CBG]);

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
                ->addColumn('notes', function ($row) {
                    return '-';
                })
                ->addColumn('namas', function ($row) {
                    return '-';
                })
                ->addColumn('total', function ($row) {
                    // Hitung total dari lapbhd
                    $total = DB::select("
                        SELECT IFNULL(COUNT(*), 0) as total
                        FROM lapbhd
                        WHERE no_bukti = ?
                    ", [$row->no_bukti]);
                    return number_format($total[0]->total ?? 0, 0, ',', '.');
                })
                ->addColumn('no_post', function ($row) {
                    return $row->no_bukti;
                })
                ->addColumn('cek_checkbox', function ($row) {
                    $disabled = $row->cek == 1 ? 'disabled' : '';
                    return '<input type="checkbox" class="form-check-input cek-item" value="' . $row->no_bukti . '" data-cek="' . $row->cek . '" ' . $disabled . '>';
                })
                ->addColumn('status', function ($row) {
                    if ($row->cek == 1) {
                        return '<span class="badge badge-success">Posted</span>';
                    } else {
                        return '<span class="badge badge-warning">Open</span>';
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

            // Gunakan default connection (mysql/tgz)
            DB::beginTransaction();
            Log::info('Database transaction dimulai');

            $processedReports = [];

            foreach ($noBuktiList as $noBukti) {
                Log::info('Memproses posting untuk no_bukti: ' . $noBukti);
                $reportData = $this->processPosting($noBukti, $flagz, $CBG);
                if ($reportData) {
                    $processedReports[] = $reportData;
                }
                Log::info('Posting berhasil untuk no_bukti: ' . $noBukti);
            }

            DB::commit();
            Log::info('=== POSTING BULK SELESAI SUKSES ===', [
                'jumlah_dokumen' => count($noBuktiList)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Posting berhasil untuk ' . count($noBuktiList) . ' dokumen',
                'reports' => $processedReports
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

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
            Log::info('Memulai processPosting', [
                'no_bukti' => $noBukti,
                'flagz' => $flagz,
                'cbg' => $CBG
            ]);

            // Ambil detail transaksi dari lapbhd
            $sqlDetail = "
                SELECT
                    lapbhd.no_id,
                    lapbhd.kd_brg,
                    lapbhd.saldo,
                    lapbhd.flag
                FROM lapbhd
                WHERE lapbhd.no_bukti = '{$noBukti}'
                AND lapbhd.cek = 1
            ";

            Log::info('QUERY - Detail Stok Opname', [
                'connection' => 'mysql',
                'sql' => $sqlDetail,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlDetail))
            ]);

            $details = DB::select("
                SELECT
                    lapbhd.no_id,
                    lapbhd.kd_brg,
                    lapbhd.saldo,
                    lapbhd.flag
                FROM lapbhd
                WHERE lapbhd.no_bukti = ?
                AND lapbhd.cek = 1
            ", [$noBukti]);

            Log::info('Detail data ditemukan: ' . count($details) . ' item');

            // Process setiap detail - update stok
            foreach ($details as $detail) {
                $kdBrg = $detail->kd_brg;
                $saldo = $detail->saldo;

                Log::info('Proses item', [
                    'kd_brg' => $kdBrg,
                    'saldo' => $saldo
                ]);

                // Update brgdt (stok opname akan menyesuaikan stok akhir)
                $sqlUpdate = "UPDATE brgdt SET saldo = {$saldo} WHERE kd_brg = '{$kdBrg}' AND cbg = '{$CBG}' AND yer = YEAR(NOW())";
                Log::info('QUERY - Update brgdt', [
                    'connection' => 'mysql',
                    'kd_brg' => $kdBrg,
                    'saldo' => $saldo,
                    'cbg' => $CBG,
                    'raw_query_untuk_navicat' => $sqlUpdate
                ]);

                DB::statement("
                    UPDATE brgdt
                    SET saldo = ?
                    WHERE kd_brg = ?
                    AND cbg = ?
                    AND yer = YEAR(NOW())
                ", [$saldo, $kdBrg, $CBG]);
            }

            // Update status posted di lapbh
            Log::info('Update status posted untuk no_bukti: ' . $noBukti);

            $sqlUpdatePosted = "UPDATE lapbh SET posted = 1, tg_smp = NOW() WHERE no_bukti = '{$noBukti}'";
            Log::info('QUERY - Update Posted Status', [
                'connection' => 'mysql',
                'no_bukti' => $noBukti,
                'raw_query_untuk_navicat' => $sqlUpdatePosted
            ]);

            DB::statement("
                UPDATE lapbh
                SET posted = 1, tg_smp = NOW()
                WHERE no_bukti = ?
            ", [$noBukti]);
            Log::info('Status posted berhasil diupdate');

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

            $sqlReport = "
                SELECT
                    lapbh.no_bukti,
                    lapbh.tgl,
                    lapbhd.kd_brg,
                    lapbhd.na_brg,
                    brg.ket_uk,
                    lapbhd.hj,
                    lapbhd.saldo
                FROM lapbh
                INNER JOIN lapbhd ON lapbh.no_bukti = lapbhd.no_bukti
                LEFT JOIN brg ON brg.kd_brg = lapbhd.kd_brg
                WHERE lapbh.no_bukti = '{$noBukti}'
                AND lapbhd.cek = 1
            ";

            Log::info('QUERY - Generate Report Data', [
                'connection' => 'mysql',
                'no_bukti' => $noBukti,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlReport))
            ]);

            $reportQuery = DB::select("
                SELECT
                    lapbh.no_bukti,
                    lapbh.tgl,
                    lapbhd.kd_brg,
                    lapbhd.na_brg,
                    brg.ket_uk,
                    lapbhd.hj,
                    lapbhd.saldo
                FROM lapbh
                INNER JOIN lapbhd ON lapbh.no_bukti = lapbhd.no_bukti
                LEFT JOIN brg ON brg.kd_brg = lapbhd.kd_brg
                WHERE lapbh.no_bukti = ?
                AND lapbhd.cek = 1
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

            $sqlJasper = "
                SELECT
                    no_bukti,
                    tgl,
                    sub,
                    usrnm,
                    IF(posted = 1, 'Sudah Posting', 'Belum Posting') as status_text
                FROM lapbh
                WHERE flag = '{$flagz}'
                AND cbg = '{$CBG}'
                ORDER BY no_bukti DESC
            ";

            Log::info('QUERY - Jasper Report', [
                'connection' => 'mysql',
                'flagz' => $flagz,
                'cbg' => $CBG,
                'sql' => $sqlJasper,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlJasper))
            ]);

            $query = DB::select("
                SELECT
                    no_bukti,
                    tgl,
                    sub,
                    usrnm,
                    IF(posted = 1, 'Sudah Posting', 'Belum Posting') as status_text
                FROM lapbh
                WHERE flag = ?
                AND cbg = ?
                ORDER BY no_bukti DESC
            ", [$flagz, $CBG]);

            $data = [];
            foreach ($query as $value) {
                // Hitung total item
                $totalItem = DB::selectOne("
                    SELECT COUNT(*) as total
                    FROM lapbhd
                    WHERE no_bukti = ?
                ", [$value->no_bukti]);

                array_push($data, array(
                    'NO_BUKTI' => $value->no_bukti,
                    'TANGGAL' => date('d-m-Y', strtotime($value->tgl)),
                    'SUB' => $value->sub,
                    'USER' => $value->usrnm,
                    'TOTAL' => number_format($totalItem->total ?? 0, 0, ',', '.'),
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
