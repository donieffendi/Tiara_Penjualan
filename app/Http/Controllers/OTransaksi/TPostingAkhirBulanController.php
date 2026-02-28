<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class TPostingAkhirBulanController extends Controller
{
    public $judul = 'Posting Akhir Bulan';
    public $FLAGZ = 'PAB';

    public function index(Request $request)
    {
        if (! $request->session()->has('periode')) {
            return view("otransaksi_TPostingAkhirBulan.index")->with([
                'judul'   => $this->judul,
                'flagz'   => $this->FLAGZ,
                'warning' => 'Periode belum diset',
            ]);
        }

        return view("otransaksi_TPostingAkhirBulan.index")->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    public function gettpostingakhirbulan_posting(Request $request)
    {
        try {
            if (! $request->session()->has('periode')) {
                return response()->json([
                    'error'           => 'Periode belum diset',
                    'draw'            => intval($request->input('draw', 0)),
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                ], 200);
            }

            $bulan   = $request->session()->get('periode')['bulan'];
            $tahun   = $request->session()->get('periode')['tahun'];
            $periode = str_pad($bulan, 2, '0', STR_PAD_LEFT) . '/' . $tahun;

            $query = DB::SELECT("
                SELECT
                    KD_PERI,
                    CONCAT(LPAD(MONTH(CONCAT(SUBSTRING(KD_PERI, 4, 4), '-', SUBSTRING(KD_PERI, 1, 2), '-01')), 2, 0), '/', YEAR(CONCAT(SUBSTRING(KD_PERI, 4, 4), '-', SUBSTRING(KD_PERI, 1, 2), '-01'))) as periode_format,
                    closingjl as status
                FROM perid
                WHERE KD_PERI = ?
                ORDER BY KD_PERI DESC
            ", [$periode]);

            return Datatables::of($query)
                ->addIndexColumn()
                ->addColumn('status_text', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="status-posted"><i class="fas fa-check-circle"></i> Sudah Posting</span>';
                    } else {
                        return '<span class="status-unposted"><i class="fas fa-times-circle"></i> Belum Posting</span>';
                    }
                })
                ->rawColumns(['status_text'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in gettpostingakhirbulan_posting: ' . $e->getMessage());
            return response()->json([
                'error'           => 'Terjadi kesalahan: ' . $e->getMessage(),
                'draw'            => intval($request->input('draw', 0)),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ], 200);
        }
    }

    public function posting(Request $request)
    {
        $tgl = Carbon::parse($request->tgl_posting)->format('Y-m-d');

        $bulan         = date('m', strtotime($tgl));
        $bulanSekarang = date('m');

        if ($bulan == $bulanSekarang) {
            return response()->json([
                'message' => 'Belum saatnya diposting...',
            ], 400);
        }

        $perid = DB::selectOne("SELECT closingjl
                                    FROM perid
                                    WHERE KD_PERI = CONCAT(LPAD(MONTH(?),2,'0'),'/',YEAR(?))
                                ", [$tgl, $tgl]);

        if ($perid && $perid->closingjl == 1) {
            return response()->json([
                'message' => 'Sudah diposting...',
            ], 400);
        }

        $tokos = DB::table('toko')
            ->whereIn('STA', ['MA', 'CB'])
            ->orderBy('NO_ID')
            ->get();

        DB::beginTransaction();

        try {

            foreach ($tokos as $toko) {

                $cbg = $toko->kode;
                // UPDATE RELASI
                DB::statement("
                UPDATE {$cbg}.juald d
                JOIN {$cbg}.jual j ON d.no_bukti = j.no_bukti
                SET d.id = j.no_id
            ");

                DB::statement("
                UPDATE {$cbg}.jualby b
                JOIN {$cbg}.jual j ON b.no_bukti = j.no_bukti
                SET b.id = j.no_id
            ");

                // GET MAX ID
                $maxid = DB::selectOne("
                SELECT MAX(no_id) as maxid
                FROM {$cbg}.juald
            ");

                $idmax = $maxid->maxid ?? 0;

                // UPDATE NOM
                DB::statement("
                UPDATE {$cbg}.nom
                SET jum = ?
            ", [$idmax]);

                // UPDATE PERID
                DB::statement("
                UPDATE {$cbg}.perid
                SET closingjl = 1
                WHERE KD_PERI = CONCAT(LPAD(MONTH(?),2,'0'),'/',YEAR(?))
            ", [$tgl, $tgl]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Posting Selesai...',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
