<?php
namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Brg;
use App\Models\Master\Cbg;
// ganti 1

use Carbon\Carbon;
use Auth;
use DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

// ganti 2
class RKarstkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function kartu()
    {
        session()->put('filter_brg1', '');
        session()->put('filter_nabrg1', '');
        session()->put('filter_brg2', '');
        session()->put('filter_nabrg2', '');
        session()->put('filter_tglDr', now()->format('d-m-Y'));
        session()->put('filter_tglSmp', now()->format('d-m-Y'));
        $brg = Brg::orderBy('KD_BRG', 'ASC')->get();
        $cbg = Cbg::groupBy('CBG')->get();
// GANTI 3 //
        return view('oreport_brg.kartu')->with(['brg' => $brg])->with(['cbg' => $cbg])->with(['hasil' => []]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function jasperStokKartu(Request $request)
    {
        // $cbg = Cbg::groupBy('CBG')->get();
        $cbg = Auth::user()->CBG;
        // dd($cbg);

        $file         = 'karstk';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Ganti format tanggal input agar sama dengan database
        $tglDrD  = date("Y-m-d", strtotime($request['tglDr']));
        $tglSmpD = date("Y-m-d", strtotime($request['tglSmp']));

        // Convert tanggal agar ambil start of day/end of day
        $tglDr  = Carbon::parse($request->tglDr)->startOfDay();
        $tglSmp = Carbon::parse($request->tglSmp)->endOfDay();

        $periode = date("m/Y", strtotime($request['tglDr']));
        $bulan   = date("m", strtotime($request['tglDr']));
        $tahun   = date("Y", strtotime($request['tglDr']));
        // $filterbrg = "KD_BRG<>'' " ;
        $filterhdh   = " AND hdhd.KD_BRG<>'' ";
        $filterambil = " AND ambild.KD_BRG<>'' ";
        $filterstock = " AND stockad.KD_BRG<>'' ";
        $brg1        = isset($request->brg1) ? trim($request->brg1) : '';
        $brg2        = isset($request->brg2) ? trim($request->brg2) : '';

        if ($request->brg1 && $request->brg2) {
            if ($request->brg1 === $request->brg2) {
                $filterbrg = "KD_BRG = '".$request->brg1."' AND CBG = '$cbg'";
                $filterbrg2   = "$request->brg1";
                $filterhdh   = " AND hdhd.KD_BRG = '" . $request->brg1 . "' AND hdh.CBG = '$cbg'";
                $filterkirim = " AND hkirimd.KD_BRG = '" . $request->brg1 . "' AND hkirim.CBG = '$cbg'";
                $filterambil = " AND ambild.KD_BRG = '" . $request->brg1 . "' AND ambil.CBG = '$cbg'";
                $filterstock = " AND stockad.KD_BRG = '" . $request->brg1 . "' AND stocka.CBG = '$cbg'";
                $filterterima = " AND hterimad.KD_BRG = '" . $request->brg1 . "' AND hterima.CBG = '$cbg'";
            } else {
                $filterbrg   = "KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "'";
                $filterbr2   = "AND KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "'";
                $filterhdh   = " AND hdhd.KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "' ";
                $filterkirim = " AND hkirimd.KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "' ";
                $filterambil = " AND ambild.KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "' ";
                $filterstock = " AND stockad.KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "' ";
                $filterterima = " AND hterimad.KD_BRG BETWEEN '" . $request->brg1 . "' AND '" . $request->brg2 . "' ";
            }
        }

        $tgawal = $tahun . '-' . $bulan . '-01';

        session()->put('filter_brg1', $request->brg1);
        session()->put('filter_nabrg1', $request->nabrg1);
        session()->put('filter_brg2', $request->brg2);
        session()->put('filter_nabrg2', $request->nabrg2);
        session()->put('filter_tglDr', $request->tglDr);
        session()->put('filter_tglSmp', $request->tglSmp);
        // dd($tglDrD,$bulan, $filterbrg,$tahun, $periode, $tglSmpD, $filterhdh, $filterambil, $filterstock );
        $queryakum = DB::SELECT("SET @akum:=0;");
        $query     = DB::SELECT("
        SELECT
            COALESCE(NO_BUKTI, '') AS NO_BUKTI,
            TGL, KD_BRG, NA_BRG, URAIAN,
            AWAL, MASUK, KELUAR, LAIN,
            IF(@kdbrg<>KD_BRG, @akum:=AWAL+MASUK-KELUAR+LAIN, @akum:=@akum+AWAL+MASUK-KELUAR+LAIN) AS SALDO,
            @kdbrg:=KD_BRG AS ganti, URUTAN
        FROM (
            -- Saldo Awal
            SELECT NULL AS NO_BUKTI, '$tglDrD' AS TGL, KD_BRG, NA_BRG,
                'SALDO AWAL' AS URAIAN,
                SUM(AWAL) AS AWAL, 0 AS MASUK, 0 AS KELUAR, 0 AS LAIN, 1 AS URUTAN
            FROM (
                SELECT KD_BRG, NA_BRG, AW$bulan AS AWAL
                FROM brgd WHERE $filterbrg AND YER='$tahun'
            ) AS AWAL00
            GROUP BY KD_BRG

            UNION ALL

            -- Transaksi lainnya dengan COALESCE untuk NO_BUKTI
            SELECT COALESCE(hdh.NO_BUKTI, '') AS NO_BUKTI, DATE_FORMAT(hdh.TGL, '%d-%m-%Y') AS TGL,
                hdhd.KD_BRG, hdhd.NA_BRG, CONCAT('BELI-', TRIM(hdh.NAMAS)) AS URAIAN,
                0 AS AWAL, hdhd.QTY AS MASUK, 0 AS KELUAR, 0 AS LAIN, 2 AS URUTAN
            FROM hdh JOIN hdhd ON hdh.NO_BUKTI = hdhd.NO_BUKTI
            WHERE hdh.TGL BETWEEN '$tglDrD' AND '$tglSmpD' $filterhdh AND hdhd.QTY <> 0 AND hdh.PER='$periode'

            UNION ALL

            SELECT COALESCE(ambil.NO_BUKTI, '') AS NO_BUKTI, DATE_FORMAT(ambil.TGL, '%d-%m-%Y') AS TGL,
                ambild.KD_BRG, ambild.NA_BRG, CONCAT('JUAL-', TRIM(ambil.NAMAC)) AS URAIAN,
                0 AS AWAL, 0 AS MASUK, ambild.QTY AS KELUAR, 0 AS LAIN, 4 AS URUTAN
            FROM ambil JOIN ambild ON ambil.NO_BUKTI = ambild.NO_BUKTI
            WHERE ambil.TGL BETWEEN '$tglDrD' AND '$tglSmpD' $filterambil AND ambil.PER='$periode'

            UNION ALL

            SELECT COALESCE(hkirim.NO_BUKTI, '') AS NO_BUKTI, DATE_FORMAT(hkirim.TGL, '%d-%m-%Y') AS TGL,
                hkirimd.KD_BRG, hkirimd.NA_BRG, CONCAT('KIRIM-', TRIM(hkirim.CBG_TUJU)) AS URAIAN,
                0 AS AWAL, 0 AS MASUK, hkirimd.QTY AS KELUAR, 0 AS LAIN, 4 AS URUTAN
            FROM hkirim JOIN hkirimd ON hkirim.NO_BUKTI = hkirimd.NO_BUKTI
            WHERE hkirim.TGL BETWEEN '$tglDrD' AND '$tglSmpD' $filterkirim AND hkirim.PER='$periode'

            UNION ALL

            SELECT COALESCE(hterima.NO_BUKTI, '') AS NO_BUKTI, DATE_FORMAT(hterima.TGL, '%d-%m-%Y') AS TGL,
                hterimad.KD_BRG, hterimad.NA_BRG, CONCAT('TERIMA-', TRIM(hterima.CBG_DARI)) AS URAIAN,
                0 AS AWAL, hterimad.QTY AS MASUK, 0 AS KELUAR, 0 AS LAIN, 4 AS URUTAN
            FROM hterima JOIN hterimad ON hterima.NO_BUKTI = hterimad.NO_BUKTI
            WHERE hterima.TGL BETWEEN '$tglDrD' AND '$tglSmpD' $filterterima AND hterima.PER='$periode'

            UNION ALL

            SELECT COALESCE(stocka.NO_BUKTI, '') AS NO_BUKTI, DATE_FORMAT(stocka.TGL, '%d-%m-%Y') AS TGL,
                stockad.KD_BRG, stockad.NA_BRG, CONCAT('KOREKSI-') AS URAIAN,
                0 AS AWAL, 0 AS MASUK, 0 AS KELUAR, stockad.QTY AS LAIN, 4 AS URUTAN
            FROM stocka JOIN stockad ON stocka.NO_BUKTI = stockad.NO_BUKTI
            WHERE stocka.TGL BETWEEN '$tglDrD' AND '$tglSmpD' $filterstock AND stocka.PER='$periode'

        ) AS kartustok;
    ");

    // $query = DB::SELECT("
    //     	SELECT *, if(@kdbrg<>KD_BRG,@akum:=AWAL+MASUK-KELUAR+LAIN,@akum:=@akum+AWAL+MASUK-KELUAR+LAIN) as SALDO,@kdbrg:=KD_BRG as ganti, URUTAN from
	// 	(
	// 		SELECT ' ' AS NO_BUKTI, '$tglDrD'  AS TGL, KD_BRG AS KD_BRG, NA_BRG AS NA_BRG, 
	// 		'SALDO AWAL' URAIAN, 
	// 		SUM(AWAL) AS AWAL, 0 MASUK, 0 KELUAR, 0 AS LAIN, 1 as URUTAN
	// 		from
	// 		(

	// 			SELECT CONCAT(KD_BRG,'-',CBG) AS KD_BRG , NA_BRG, AW$bulan AS AWAL 
	// 			from brgd WHERE KD_BRG='$brg' and YER='$tahun'
				
	// 			UNION ALL
				
	// 			SELECT CONCAT(hdhd.KD_BRG,'-',hdhd.CBG) AS KD_BRG, hdhd.NA_BRG, hdhd.QTY AS AWAL 
	// 			from hdh, hdhd where hdh.NO_BUKTI = hdhd.NO_BUKTI and hdh.TGL<'$tglDrD' 
	// 			and hdhd.KD_BRG='$brg' and hdh.PER='$periode' and  hdhd.QTY <> 0  union all
		
	// 			SELECT CONCAT(ambild.KD_BRG,'-',ambild.CBG) AS KD_BRG, ambild.NA_BRG, ( ambild.QTY * -1 ) AS AWAL 
	// 			from ambil, ambild where ambil.NO_BUKTI = ambild.NO_BUKTI and ambil.TGL<'$tglDrD' 
	// 			and ambild.KD_BRG='$brg' and ambil.PER='$periode' and  ambild.QTY <> 0  union all
				
				
	// 			SELECT CONCAT(stockad.KD_BRG,'-',stockad.CBG) as KD_BRG, stockad.NA_BRG, stockad.QTY AS AWAL 
	// 			from stocka, stockad where stocka.NO_BUKTI = stockad.NO_BUKTI and stocka.TGL<'$tglDrD' 
	// 			and stockad.KD_BRG='$brg' and stocka.PER='$periode' 

				
	// 		) as AWAL00
	// 		group by KD_BRG 
	// 		UNION ALL

	// 		SELECT hdh.NO_BUKTI,  DATE_FORMAT(hdh.TGL, '%d-%m-%Y') AS TGL, CONCAT(hdhd.KD_BRG,'-',hdhd.CBG) AS KD_BRG, hdhd.NA_BRG, CONCAT('BELI-',TRIM(hdh.NAMAS)) AS URAIAN, 0 AWAL, hdhd.QTY AS MASUK, 0 AS KELUAR, 0 AS LAIN,  2 as URUTAN 
	// 		from hdh, hdhd where hdh.NO_BUKTI=hdhd.NO_BUKTI AND hdh.TGL BETWEEN '$tglDrD' and '$tglSmpD' $filterhdh and hdhd.QTY <> 0 and hdh.PER='$periode' union all


	// 		SELECT ambil.NO_BUKTI, DATE_FORMAT(ambil.TGL, '%d-%m-%Y') AS TGL, CONCAT(ambild.KD_BRG,'-',ambild.CBG) AS KD_BRG,  ambild.NA_BRG, CONCAT('JUAL-',TRIM(ambil.NAMAC)) AS URAIAN, 0 AWAL, 0 AS MASUK, ambild.QTY AS KELUAR,  0 AS LAIN, 4 as URUTAN  
	// 		from ambil, ambild where ambil.NO_BUKTI = ambild.NO_BUKTI and ambil.TGL BETWEEN '$tglDrD' and '$tglSmpD' $filterambil and ambil.PER='$periode' union all

	// 		SELECT stocka.NO_BUKTI, DATE_FORMAT(stocka.TGL, '%d-%m-%Y') AS TGL, CONCAT(stockad.KD_BRG,'-',stockad.CBG) as KD_BRG, stockad.NA_BRG, CONCAT('KOREKSI-') AS URAIAN, 0 AWAL, 0 AS MASUK, 0 AS KELUAR, stockad.QTY AS LAIN, 5 as URUTAN  
	// 		from stocka, stockad where stocka.NO_BUKTI = stockad.NO_BUKTI and stocka.TGL BETWEEN '$tglDrD' and '$tglSmpD' $filterstock and stocka.PER='$periode' union all

    //         SELECT hkirim.NO_BUKTI, DATE_FORMAT(hkirim.TGL, '%d-%m-%Y') AS TGL, CONCAT(hkirimd.KD_BRG,'-',hkirimd.CBG) as KD_BRG, hkirimd.NA_BRG, CONCAT('KOREKSI-') AS URAIAN, 0 AWAL, 0 AS MASUK, 0 AS KELUAR, hkirimd.QTY AS LAIN, 6 as URUTAN  
	// 		from hkirim, hkirimd where hkirim.NO_BUKTI = hkirimd.NO_BUKTI and hkirim.TGL BETWEEN '$tglDrD' and '$tglSmpD' $filterkirim and hkirim.PER='$periode'
			
	// 		order by KD_BRG, TGL, NO_BUKTI, URUTAN ASC
			
	// 	) as kartustok  ;
	// 	");

        $brg = Brg::where('KD_BRG', '<>', 'ZZ')->get();
        if ($request->has('filter')) {
            return view('oreport_brg.kartu')->with(['brg' => $brg])->with(['hasil' => $query]);
        }

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, [
                'NO_BUKTI' => $query[$key]->NO_BUKTI,
                'TGL'      => $query[$key]->TGL,
                // 'KD_BRG' => $query[$key]->KD_BRG,
                'KD_BRG'   => "`" . strval($query[$key]->KD_BRG),
                'CBG'      => "`" . strval($query[$key]->CBG),
                'NA_BRG'   => $query[$key]->NA_BRG,
                'URAIAN'   => $query[$key]->URAIAN,
                'AWAL'     => $query[$key]->AWAL,
                'MASUK'    => $query[$key]->MASUK,
                'KELUAR'   => $query[$key]->KELUAR,
                'LAIN'     => $query[$key]->LAIN,
                'AKHIR'    => $query[$key]->SALDO,
            ]);
        }
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

}