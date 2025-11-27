<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LKartuStockController extends Controller
{
    public function index()
    {
        try {
            $cbg = DB::select("SELECT * FROM toko WHERE STA IN ('MA','CB','DC') ORDER BY NO_ID ASC");
            if (empty($cbg)) {
                $cbg = [];
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $cbg = [];
        }
        $periode = session('periode', date('m-Y'));
        $filter_cbg = session('filter_cbg');
        $filter_periode = session('filter_periode');
        $filter_kode_brg = session('filter_kode_brg');

        $data = [
            'cbg' => $cbg,
            'periode' => $periode,
            'filter_cbg' => $filter_cbg,
            'filter_periode' => $filter_periode,
            'filter_kode_brg' => $filter_kode_brg,
            'hasilKartu' => [],
            'hasilData' => []
        ];

        return view('logistik_kartu_stock.index', $data);
    }

    public function getKartuStock(Request $request)
    {
        $action = $request->get('action');

        if ($action == 'filter') {
            if ($request->has('tipe_toko') && $request->get('tipe_toko') == 'toko') {
                return $this->getDataPeriode($request);
            } else {
                return $this->getDataKartu($request);
            }
        }

        return response()->json(['success' => false]);
    }

    private function getDataPeriode(Request $request)
    {
        $cbg = $request->get('cbg');
        $periode = $request->get('periode');
        $supp = $request->get('supp');
        $sub = $request->get('sub');
        $kode_brg = $request->get('kode_brg');
        $barcode = $request->get('barcode');
        $na_brg = $request->get('na_brg');

        session(['filter_cbg' => $cbg, 'filter_periode' => $periode]);

        $yearstring = substr($periode, -4);
        $monthstring = substr($periode, 0, 2);

        $check_year = DB::select("SELECT ? as XX, YEAR(CURDATE()) as YER", [$yearstring]);
        $tahun = ($check_year[0]->XX == $check_year[0]->YER) ? '' : $yearstring;

        $brgdt = $cbg . '.brglogd' . $tahun;

        $whereClause = '';
        $params = [$cbg];

        if (!empty($supp)) {
            $whereClause .= ' AND A.supp = ?';
            $params[] = $supp;
        }
        if (!empty($sub)) {
            $whereClause .= ' AND A.sub = ?';
            $params[] = $sub;
        }
        if (!empty($kode_brg)) {
            $whereClause .= ' AND A.kd_brg = ?';
            $params[] = $kode_brg;
        }
        if (!empty($barcode)) {
            $whereClause .= ' AND A.Barcode = ?';
            $params[] = $barcode;
        }
        if (!empty($na_brg)) {
            $whereClause .= ' AND A.na_brg LIKE ?';
            $params[] = '%' . $na_brg . '%';
        }

        $query = "
            SELECT A.TYPE, A.KD_BRG, A.sub, A.supp, A.kdbar, A.NA_BRG, A.TARIK, A.MASA_EXP,
                   A.KET_UK, A.KET_KEM, B.SRMIN, B.SRMAX, B.lph, B.KLK, B.KDLAKU, B.DTR,
                   B.gAK00 as stockg, B.AK00 as stockt, B.rAK00 as stockr,
                   B.HB, B.hj, B.lambat, B.psn as statpsn,
                   CONCAT(B.td_od,'-',B.cat_od) as tdod, A.supp, A.sp_l, A.sp_lf, A.Barcode, A.RETUR
            FROM {$cbg}.brglog A, {$brgdt} B
            WHERE A.KD_BRG = B.KD_BRG AND B.cbg = ? {$whereClause}
            ORDER BY A.KD_BRG";

        $data = DB::select($query, $params);

        return DataTables::of(collect($data))
            ->addIndexColumn()
            ->editColumn('HB', function ($row) {
                return number_format($row->HB, 0);
            })
            ->editColumn('hj', function ($row) {
                return number_format($row->hj, 0);
            })
            ->editColumn('stockg', function ($row) {
                return number_format($row->stockg, 0);
            })
            ->editColumn('stockt', function ($row) {
                return number_format($row->stockt, 0);
            })
            ->editColumn('stockr', function ($row) {
                return number_format($row->stockr, 0);
            })
            ->make(true);
    }

    private function getDataKartu(Request $request)
    {
        $cbg = $request->get('cbg');
        $periode = $request->get('periode');
        $kode_brg = $request->get('kode_brg');
        $tipe = $request->get('tipe_toko', 'toko');

        session(['filter_cbg' => $cbg, 'filter_periode' => $periode, 'filter_kode_brg' => $kode_brg]);

        $yearstring = substr($periode, -4);
        $bulan = substr($periode, 0, 2);
        $brgdt = $cbg . '.brglogd' . (($yearstring == date('Y')) ? '' : $yearstring);

        if ($tipe == 'toko') {
            return $this->getKartuToko($cbg, $periode, $kode_brg, $brgdt, $bulan, $yearstring);
        } else {
            return $this->getKartuRetur($cbg, $periode, $kode_brg, $brgdt, $bulan);
        }
    }

    private function getKartuToko($cbg, $periode, $kode_brg, $brgdt, $bulan, $yearstring)
    {
        $query = "
            SELECT *, @AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN AS SALDO FROM (
                SELECT 'Saldo Awal' as no_bukti, '' as tgl, kd_brg, NA_BRG, AW{$bulan} as awal,
                       0 as masuk, 0 as keluar, 0 AS LAIN, 'AW' AS FLAG, 0 AS URT
                FROM {$brgdt} brglogd
                WHERE yer=? AND KD_BRG=? AND cbg=? AND aw{$bulan}<>0

                UNION ALL

                SELECT beliz.no_bukti, beliz.TGL, belizd.KD_BRG, belizd.NA_BRG, 0 as awal,
                       belizd.qty AS MASUK, 0 AS KELUAR, 0 AS LAIN, beliz.FLAG, 1 AS URT
                FROM {$cbg}.beliz, {$cbg}.belizd
                WHERE beliz.NO_BUKTI=belizd.NO_BUKTI AND beliz.CBG=?
                  AND beliz.PER=? AND (beliz.flag='BL' OR beliz.flag='BZ' OR beliz.flag='BD'
                  OR beliz.flag='B3' OR beliz.flag='B5' OR beliz.flag='B8')
                  AND belizd.kd_brg=? AND belizd.qty<>0
                  AND belizd.kdlaku<>'0' AND belizd.kdlaku<>'1'

                UNION ALL

                SELECT no_bukti, TGL, KD_BRG, NA_BRG, 0 as awal, 0 AS MASUK, QTY AS KELUAR,
                       0 AS LAIN, FLAG, 3 AS URT
                FROM {$cbg}.juald{$bulan}
                WHERE cbg=? AND PER=? AND (FLAG='JL' OR FLAG='RF' OR flag='EC')
                  AND juald{$bulan}.KD_BRG=?
            ) AS AA
            JOIN (SELECT @AKHIR:=0) AS BB ON 1=1
            ORDER BY KD_BRG, tgl, urt";

        $params = [$yearstring, $kode_brg, $cbg, $cbg, $periode, $kode_brg, $cbg, $periode, $kode_brg];
        $data = DB::select($query, $params);

        return response()->json(['data' => $data]);
    }

    private function getKartuRetur($cbg, $periode, $kode_brg, $brgdt, $bulan)
    {
        $query = "
            SELECT *, @AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN AS SALDO FROM (
                SELECT 'Saldo Awal' AS no_bukti, '' AS TGL, KD_BRG, NA_BRG,
                       RAW{$bulan} as awal, 0 AS MASUK, 0 AS KELUAR, 0 AS LAIN, '' AS FLAG, 0 AS URT
                FROM {$brgdt} brglogd
                WHERE brglogd.cbg=? AND KD_BRG=? AND RAW{$bulan}<>0

                UNION ALL

                SELECT A.no_bukti, A.TGL, B.KD_BRG, B.NA_BRG,
                       0 AS AWAL, 0 AS MASUK, 0 AS KELUAR, B.qty AS LAIN, A.FLAG, 1 AS URT
                FROM {$cbg}.lgstockbz A, {$cbg}.lgstockbzd B
                WHERE A.NO_BUKTI=B.NO_BUKTI AND A.CBG=? AND A.PER=?
                  AND A.flag='BS' AND B.KD_BRG=? AND B.qty<>0

                UNION ALL

                SELECT lgretur.no_bukti, lgretur.TGL, lgreturd.KD_BRG, lgreturd.NA_BRG,
                       0 as awal, 0 AS MASUK, lgreturd.qty AS KELUAR, 0 AS LAIN, lgretur.FLAG, 5 AS URT
                FROM {$cbg}.lgretur, {$cbg}.lgreturd
                WHERE lgretur.NO_BUKTI=lgreturd.NO_BUKTI AND lgretur.cbg=? AND lgretur.POSTED=1
                  AND lgretur.PER=? AND lgretur.flag='VR' AND lgreturd.KD_BRG=?
                  AND lgreturd.qty<>0 AND lgreturd.kdlaku='5'
            ) AS AA
            JOIN (SELECT @AKHIR:=0) AS BB ON 1=1
            ORDER BY tgl, urt ASC";

        $params = [$cbg, $kode_brg, $cbg, $periode, $kode_brg, $cbg, $periode, $kode_brg];
        $data = DB::select($query, $params);

        return response()->json(['data' => $data]);
    }

    public function browse(Request $request)
    {
        $q = $request->get('q', '');
        $cbg = $request->get('cbg');

        if (!empty($q)) {
            $barang = DB::select("
                SELECT kd_brg, na_brg, ket_uk, ket_kem, barcode
                FROM {$cbg}.brglog
                WHERE (na_brg LIKE ? OR kd_brg LIKE ? OR barcode LIKE ?)
                ORDER BY kd_brg
                LIMIT 50", ["%$q%", "%$q%", "%$q%"]);
        } else {
            $barang = DB::select("
                SELECT kd_brg, na_brg, ket_uk, ket_kem, barcode
                FROM {$cbg}.brglog
                ORDER BY kd_brg
                LIMIT 50");
        }

        return response()->json($barang);
    }

    public function getBarangDetail(Request $request)
    {
        $kd_brg = $request->kd_brg;
        $cbg = $request->cbg;

        $barang = DB::select(
            "
            SELECT A.kd_brg, A.na_brg, A.ket_uk, A.ket_kem, A.barcode,
                   B.kdlaku, B.hj, B.hb, B.ak00 as saldo
            FROM {$cbg}.brglog A
            LEFT JOIN {$cbg}.brglogd B ON A.kd_brg = B.kd_brg AND B.cbg = ? AND B.yer = YEAR(NOW())
            WHERE A.kd_brg = ? OR A.barcode = ?",
            [$cbg, $kd_brg, $kd_brg]
        );

        if (!empty($barang)) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'data' => $barang[0]
            ]);
        }

        return response()->json([
            'success' => true,
            'exists' => false,
            'data' => null
        ]);
    }
}
