<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TPostingAkhirBulanController extends Controller
{
    var $judul = 'Posting Akhir Bulan';
    var $FLAGZ = 'PAB';

    public function index(Request $request)
    {
        if (!$request->session()->has('periode')) {
            return view("otransaksi_TPostingAkhirBulan.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'warning' => 'Periode belum diset'
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
            $tahun = $request->session()->get('periode')['tahun'];
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
            $tglPosting = $request->tgl_posting;

            if (!$tglPosting) {
                return response()->json(['error' => 'Tanggal posting harus diisi'], 400);
            }

            $cekBulan = DB::SELECT("SELECT LPAD(MONTH(NOW()),2,0) as bulin, LPAD(MONTH(?),2,0) as bulan", [$tglPosting]);
            $bulan = $cekBulan[0]->bulan;
            $bulin = $cekBulan[0]->bulin;

            if ($bulan == $bulin) {
                return response()->json(['error' => 'Belum saatnya diposting...'], 400);
            }

            $tahunPosting = date('Y', strtotime($tglPosting));
            $kdPeri = $bulan . '/' . $tahunPosting;

            $cekPosted = DB::select("SELECT closingjl FROM perid WHERE KD_PERI = ?", [$kdPeri]);

            if (count($cekPosted) > 0 && $cekPosted[0]->closingjl == 1) {
                return response()->json(['error' => 'Sudah diposting...'], 400);
            }

            DB::beginTransaction();

            $cabangList = DB::SELECT("SELECT * FROM toko WHERE STA IN ('MA','CB') ORDER BY NO_ID");

            foreach ($cabangList as $cabang) {
                $cbg = trim($cabang->kode);

                DB::statement("ALTER TABLE " . $cbg . ".jual 
                    DROP COLUMN NO_ID,
                    MODIFY COLUMN no_bukti varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '' FIRST,
                    DROP PRIMARY KEY");

                DB::statement("ALTER TABLE " . $cbg . ".jual 
                    ADD COLUMN no_id int(11) NOT NULL AUTO_INCREMENT FIRST,
                    MODIFY COLUMN no_bukti varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '' AFTER `no_id`,
                    ADD PRIMARY KEY (no_id)");

                DB::statement("ALTER TABLE " . $cbg . ".juald 
                    DROP COLUMN NO_ID,
                    MODIFY COLUMN ID int(10) NOT NULL DEFAULT 0 FIRST,
                    DROP PRIMARY KEY");

                DB::statement("ALTER TABLE " . $cbg . ".juald 
                    ADD COLUMN NO_ID int(11) NOT NULL AUTO_INCREMENT FIRST,
                    MODIFY COLUMN ID int(10) NOT NULL DEFAULT 0 AFTER `NO_ID`,
                    ADD PRIMARY KEY (`NO_ID`)");

                DB::statement("UPDATE " . $cbg . ".juald, " . $cbg . ".jual 
                    SET juald.id=jual.no_id 
                    WHERE juald.no_bukti=jual.no_bukti");

                DB::statement("UPDATE " . $cbg . ".jualby, " . $cbg . ".jual 
                    SET jualby.id=jual.no_id 
                    WHERE jualby.no_bukti=jual.no_bukti");

                $maxId = DB::SELECT("SELECT MAX(no_id) as maxid FROM " . $cbg . ".juald");
                $idmax = $maxId[0]->maxid ?? 0;

                DB::update("UPDATE " . $cbg . ".nom SET jum = ?", [$idmax]);

                DB::update("UPDATE " . $cbg . ".perid SET closingjl=1 WHERE KD_PERI = ?", [$kdPeri]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Posting Selesai...'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in posting: ' . $e->getMessage());
            return response()->json(['error' => 'Posting gagal: ' . $e->getMessage()], 500);
        }
    }
}
