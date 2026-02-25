<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPJasperXML;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use Yajra\DataTables\Facades\DataTables;

class TPostingStokOpnameController extends Controller
{
    public $judul = 'Posting Stok Opname';
    public $FLAGZ = 'AO';

    public function index(Request $request)
    {
        try {
            Log::info('=== TPostingStokOpname INDEX ===', [
                'user' => Auth::user()->username ?? 'unknown',
                'cbg'  => Auth::user()->CBG ?? null,
            ]);

            if (! $request->session()->has('periode')) {
                Log::warning('Periode belum diset');
                return view("otransaksi_TPostingStokOpname.index")->with([
                    'judul'   => $this->judul,
                    'flagz'   => $this->FLAGZ,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.',
                ]);
            }

            $CBG = Auth::user()->CBG ?? null;
            if (! $CBG) {
                Log::error('User tidak memiliki CBG');
                return view("otransaksi_TPostingStokOpname.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.',
                ]);
            }

            Log::info('Halaman index dimuat sukses', ['cbg' => $CBG]);

            return view("otransaksi_TPostingStokOpname.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'cbg'   => $CBG,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPostingStokOpname index: ' . $e->getMessage());
            return view("otransaksi_TPostingStokOpname.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function gettpostingstokopname_posting(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            if (! $CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $flagz = $request->flagz ?? 'AO';

            //sementara untuk trial flag dimatikan dulu karena gak ada data
            $query = DB::table('stockb')
                ->selectRaw("
                CONCAT(LEFT(nolap, 2), RIGHT(nolap, 5)) AS bukt,
                no_bukti,
                tgl,
                notes,
                total_qty
            ")
                ->where('posted', 0)
                // ->where('flag', $flagz)
                ->orderBy('no_bukti');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('cek_checkbox', function ($row) {
                    return '<input type="checkbox" class="cek-item checkItem" value="' . $row->no_bukti . '">';
                })
                ->rawColumns(['cek_checkbox'])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function posting_bulk(Request $request)
    {
        try {
            $CBG      = Auth::user()->CBG ?? null;
            $USERNAME = Auth::user()->username ?? 'unknown';

            if (! $CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $noBuktiList = $request->input('no_bukti_list', []);
            $flagz       = $request->input('flagz', 'AO');

            if (empty($noBuktiList)) {
                return response()->json(['error' => 'Tidak ada data yang dipilih untuk diposting'], 400);
            }

            if (count($noBuktiList) > 6) {
                return response()->json(['error' => 'Maksimal 6 dokumen dapat diproses sekaligus'], 400);
            }

            DB::beginTransaction();

            $processedReports = [];

            foreach ($noBuktiList as $noBukti) {
                $report = $this->processPosting($noBukti, $flagz, $CBG);
                if ($report) {
                    $processedReports[] = $report;
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Posting berhasil',
                'bukti'   => $noBuktiList,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function processPosting($noBukti, $flagz, $CBG)
    {

        try {

            $TGL     = Carbon::now()->format('d/m/Y');
            $JAM     = Carbon::now()->addHour()->toTimeString();
            $details = DB::select(" SELECT kd_brg, qty
            FROM stockbd
            WHERE no_bukti = ?", [$noBukti]);

            foreach ($details as $d) {
                DB::statement("
                UPDATE brgdt
                SET
                    ln00 = ln00 + ?,
                    ak00 = aw00 + ma00 - ke00 + ln00
                WHERE kd_brg = ?
                AND cbg = ?
            ", [
                    $d->qty,
                    $d->kd_brg,
                    $CBG,
                ]);
            }
            dd($noBukti);

            DB::statement("CALL poststkb(?)", [$noBukti]);

            $reportData = DB::select("SELECT
                    CONCAT(LEFT(stockbz.nolap,2), RIGHT(stockbz.nolap,5)) AS bukt,
                    stockbz.no_bukti,
                    stockbz.tgl,
                    stockbzd.KD_BRG,
                    stockbzd.NA_BRG,
                    stockbzd.ket_uk,
                    stockbzd.hj,
                    IF(qty >= 0, qty, 0) AS pos,
                    IF(qty < 0, qty * -1, 0) AS neg,
                    IF(qty >= 0, 100 * (qty / stockbzd.saldo), 0) AS posp,
                    IF(qty < 0, 100 * ((qty * -1) / stockbzd.saldo), 0) AS negp,
                    IF(qty >= 0, ROUND(qty * hj), 0) AS posr,
                    IF(qty < 0, ROUND((qty * -1) * hj), 0) AS negr
                FROM stockbz, stockbzd
                WHERE stockbz.no_bukti = stockbzd.no_bukti
                AND stockbz.no_bukti = ?
                AND stockbz.cbg = ?
            --    AND qty <> 0
            ", [$noBukti, $CBG]);
            return [
                'no_bukti' => $noBukti,
                'tgl'      => $TGL,
                'jam'      => $JAM,
                'detail'   => $reportData,
            ];

            // $file         = 'print_posting_so';
            // $PHPJasperXML = new PHPJasperXML();
            // $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            // $cleanData                    = json_decode(json_encode($reportData), true);
            // $PHPJasperXML->arrayParameter = [
            //     "TGL" => $TGL,
            //     "JAM" => $JAM,
            // ];

            // $PHPJasperXML->setData($cleanData);
            // while (ob_get_level() > 0) {
            //     ob_end_clean();
            // }
            // $PHPJasperXML->outpage("I");
            // return;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function posting_bulk_print(Request $request)
    {
        $list = $request->query('list', '');

        if (empty($list)) {
            return "Parameter list kosong!";
        }

        $noBuktiArr = explode(',', $list);

        $CBG = Auth::user()->CBG ?? null;

        $mergedData = [];

        foreach ($noBuktiArr as $noBukti) {

            $rows = DB::select("
            SELECT
                CONCAT(LEFT(stockbz.nolap,2), RIGHT(stockbz.nolap,5)) AS bukt,
                stockbz.no_bukti,
                stockbz.tgl,
                stockbzd.KD_BRG,
                stockbzd.NA_BRG,
                stockbzd.ket_uk,
                stockbzd.hj,
                IF(qty >= 0, qty, 0) AS pos,
                IF(qty < 0, qty * -1, 0) AS neg,
                IF(qty >= 0, 100 * (qty / stockbzd.saldo), 0) AS posp,
                IF(qty < 0, 100 * ((qty * -1) / stockbzd.saldo), 0) AS negp,
                IF(qty >= 0, ROUND(qty * hj), 0) AS posr,
                IF(qty < 0, ROUND((qty * -1) * hj), 0) AS negr
            FROM stockbz, stockbzd
            WHERE stockbz.no_bukti = stockbzd.no_bukti
            AND stockbz.no_bukti = ?
            AND stockbz.cbg = ?
        ", [$noBukti, $CBG]);

            foreach ($rows as $r) {
                $mergedData[] = (array) $r;
            }
        }

        $file = 'print_posting_so';

        $PHPJasperXML = new \PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

        $PHPJasperXML->arrayParameter = [
            "TGL" => now()->format('d/m/Y'),
            "JAM" => now()->format('H:i:s'),
        ];

        $PHPJasperXML->setData($mergedData);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $PHPJasperXML->outpage("I");
        exit;
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
                'connection'              => 'mysql',
                'no_bukti'                => $noBukti,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlReport)),
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
                'data'     => $reportQuery,
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
            $CBG   = Auth::user()->CBG ?? null;
            $flagz = $request->input('flagz', 'FS');

            Log::info('Generate laporan jasper', [
                'cbg'   => $CBG,
                'flagz' => $flagz,
            ]);

            if (! $CBG) {
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
                'connection'              => 'mysql',
                'flagz'                   => $flagz,
                'cbg'                     => $CBG,
                'sql'                     => $sqlJasper,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlJasper)),
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

                array_push($data, [
                    'NO_BUKTI' => $value->no_bukti,
                    'TANGGAL'  => date('d-m-Y', strtotime($value->tgl)),
                    'SUB'      => $value->sub,
                    'USER'     => $value->usrnm,
                    'TOTAL'    => number_format($totalItem->total ?? 0, 0, ',', '.'),
                    'STATUS'   => $value->status_text,
                    'JUDUL'    => $judul,
                ]);
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
