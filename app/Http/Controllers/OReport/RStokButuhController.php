<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RStokButuhController extends Controller
{
    public function browsePo(Request $request)
    {
		$periode = session()->get('periode')['bulan'].'/'.session()->get('periode')['tahun'];
		$bulan = session()->get('periode')['bulan'];
		$tahun = session()->get('periode')['tahun'];

		$po = DB::SELECT("SELECT NO_BUKTI, TGL, KODES, NAMAS, ALAMAT, KOTA, KD_BRG, NA_BRG, HARGA, KG, KIRIM, SISA, TOTAL, NOTES from po WHERE GOL='Y' and year(TGL)='$tahun' and KD_BRG='".$request->KD_BRG."' ORDER BY NO_BUKTI");
        return response()->json($po);
    }

    public function browseSo(Request $request)
    {
		$periode = session()->get('periode')['bulan'].'/'.session()->get('periode')['tahun'];
		$bulan = session()->get('periode')['bulan'];
		$tahun = session()->get('periode')['tahun'];

		$po = DB::SELECT("SELECT NO_BUKTI, TGL, KODEC, NAMAC, ALAMAT, KOTA, KD_BRG, NA_BRG, HARGA, KG, KIRIM, SISA, TOTAL, NOTES from so WHERE GOL='Y' and year(TGL)='$tahun' and KD_BRG='".$request->KD_BRG."' ORDER BY NO_BUKTI");
        return response()->json($po);
    }
    
    public function index()
    {
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');
		session()->put('filter_brg2', '');
		session()->put('filter_nabrg2', '');

        return view('oreport_stokbutuh.index')->with(['hasil' => []]);
    }
	
	public function jasperStokButuh(Request $request) 
	{
		$file 	= 'stokbutuh';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));

            $periode = session()->get('periode')['bulan'].'/'.session()->get('periode')['tahun'];
            $bulan = session()->get('periode')['bulan'];
            $tahun = session()->get('periode')['tahun'];
			$filterbrg = " AND KD_BRG<>'' " ;
			if($request->brg1)
			{
				$filterbrg = " AND KD_BRG between '".$request->brg1."' and '".$request->brg2."' " ;
			}
		
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
			session()->put('filter_brg2', $request->brg2);
			session()->put('filter_nabrg2', $request->nabrg2);

		$queryakum = DB::SELECT("SET @akum:=0;");
		$query = DB::SELECT(
		"SELECT KD_BRG, NA_BRG, AK12, coalesce(SISAPO,0) as SISAPO, coalesce(SISASO,0) as SISASO, 
		-- coalesce(SISASO,0)-coalesce(SISAPO,0)-AK12 as BUTUH, 
		AK12+coalesce(SISAPO,0)-coalesce(SISASO,0) as BUTUH, HRT12 from (
			SELECT KD_BRG, NA_BRG, AK12, (SELECT sum(SISA) from po WHERE KD_BRG=brgd.KD_BRG ) SISAPO, (SELECT sum(SISA) from so WHERE 
			KD_BRG=brgd.KD_BRG ) SISASO, HRT12 
			from brgd WHERE YER='$tahun' and KD_BRG in (SELECT KD_BRG from brg WHERE GOL='Y') $filterbrg
		) as rekap
		");

		if($request->has('filter'))
		{
			return view('oreport_stokbutuh.index')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'KD_BRG' => $query[$key]->KD_BRG,
				'NA_BRG' => $query[$key]->NA_BRG,
				'SISAPO' => $query[$key]->SISAPO,
				'SISASO' => $query[$key]->SISASO,
				'BUTUH' => $query[$key]->BUTUH,
				'HRT12' => $query[$key]->HRT12,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}
	
}
