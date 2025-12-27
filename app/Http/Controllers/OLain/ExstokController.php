<?php

namespace App\Http\Controllers\OLain;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Cust;
use DataTables;
use Auth;
use DB;

use XBase\TableEditor;
use XBase\TableReader;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class ExstokController extends Controller
{

	public function index()
    {

        return view('olain_exstok.index')->with(['hasil' => []]);
    }
	
	

	public function jasperSoReport(Request $request) 
	{
		$file 	= 'son';
		$PHPJasperXML = new PHPJasperXML();
		$PHPJasperXML->load_xml_file(base_path().('/app/reportc01/phpjasperxml/'.$file.'.jrxml'));
		
			// Check Filter
			if (!empty($request->gol))
			{
				$filtergol = " and so.GOL='".$request->gol."' ";
			}
			
			if (!empty($request->kodec))
			{
				$filterkodec = " and so.KODEC='".$request->kodec."' ";
			}
			
			if (!empty($request->kodet))
			{
				$filterkodet = " and KODET='".$request->kodet."' ";
			}
			
			if (!empty($request->tglDr) && !empty($request->tglSmp))
			{
				$tglDrD = date("Y-m-d", strtotime($request->tglDr));
				$tglSmpD = date("Y-m-d", strtotime($request->tglSmp));
				$filtertgl = " AND so.TGL between '".$tglDrD."' and '".$tglSmpD."' ";
			}
			
			if (!empty($request->sls))
			{
				$sls = $request->sls=='Y' ? '1' : '0';
				$filtersls = " and so.SLS='".$sls."' ";
			}
			
			if (!empty($request->brg1))
			{
				$filterbrg = " and sod.KD_BRG='".$request->brg1."' ";
			}

			session()->put('filter_gol', $request->gol);
			session()->put('filter_kodec1', $request->kodec);
			session()->put('filter_namac1', $request->NAMAC);
			session()->put('filter_tglDari', $request->tglDr);
			session()->put('filter_tglSampai', $request->tglSmp);
			session()->put('filter_sls', $request->sls);
			session()->put('filter_brg1', $request->brg1);
			session()->put('filter_nabrg1', $request->nabrg1);
		
		if($filtergol == 'B'){
				$query = DB::SELECT("SELECT so.NO_BUKTI AS NO_BUKTI, so.TGL AS TGL, so.KODEC AS KODEC, so.NAMAC AS NAMAC, sod.KD_BHN AS KD_BRG, sod.NA_BHN AS NA_BRG, 
								sod.QTY AS QTY, sod.HARGA AS HARGA, sod.TOTAL AS TOTAL, sod.KET AS KET, so.GOL AS GOL 
						from so,sod
						WHERE so.NO_BUKTI=sod.NO_BUKTI
						$filtertgl $filtergol $filterkodec 
						/*order by so.KODEC,so.NO_BUKTI*/;
				"); 

		} else {

			$query = DB::SELECT("SELECT so.NO_BUKTI AS NO_BUKTI, so.TGL AS TGL, so.KODEC AS KODEC, so.NAMAC AS NAMAC, sod.KD_BRG AS KD_BRG, sod.NA_BRG AS NA_BRG, 
								sod.QTY AS QTY, sod.HARGA AS HARGA, sod.TOTAL AS TOTAL, sod.KET AS KET, so.GOL AS GOL  
						from so,sod
						WHERE so.NO_BUKTI=sod.NO_BUKTI
						$filtertgl $filtergol $filterkodec 
						/*order by so.KODEC,so.NO_BUKTI*/;
				");
		}
		
		
		if($request->has('filter'))
		{
			return view('olain_exstok.index')->with(['hasil' => $query]);
		}

		$data=[];
		foreach ($query as $key => $value)
		{
			array_push($data, array(
				'NO_SO' => $query[$key]->NO_SO,
				'TGL' => $query[$key]->TGL,
				'KODEC' => $query[$key]->KODEC,
				'NAMAC' => $query[$key]->NAMAC,
				'KD_BRG' => $query[$key]->KD_BRG,
				'NA_BRG' => $query[$key]->NA_BRG,
				'KG' => $query[$key]->KG,
				'HARGA' => $query[$key]->HARGA,
				'TOTAL' => $query[$key]->TOTAL,
				'NOTES' => $query[$key]->NOTES,
				'KIRIM' => $query[$key]->KIRIM,
				'SISA' => $query[$key]->SISA,
			));
		}
		$PHPJasperXML->setData($data);
		ob_end_clean();
		$PHPJasperXML->outpage("I");
	}

	public function exportStockToko(Request $request)
	{
		$basePath  = getcwd();
		$exportDir = $basePath . '\\dbf\\export';

		// === Bersihkan file lama (> 7 hari)
		foreach (scandir($exportDir) as $file) {
			if ($file === '.' || $file === '..') continue;

			$filePath = $exportDir . '\\' . $file;
			if (is_file($filePath) && filemtime($filePath) < time() - (3600 * 24 * 7)) {
				unlink($filePath);
			}
		}

		// === Ambil DB
		$db = strtolower(Auth::user()->CBG);
		if ($db === '') {
			return '500x';
		}

		// === Jenis toko
		$jns = DB::selectOne(
			"SELECT TH FROM $db.toko WHERE KODE = ?", [$db]
		)->TH;

		$tableName   = $jns . 'STOK.DBF';
		$dbfFilePath = $exportDir . '\\' . $tableName;

		// === Copy template DBF
		copy(
			'\\\\10.10.30.132\\Common\\terbaru_APF\\cetakbiru\\expstoktoko.dbf',
			$dbfFilePath
		);

		// === Baca struktur DBF
		$table = new TableReader($dbfFilePath);
		$fields = [];
		foreach ($table->getColumns() as $col) {
			$fields[] = $col->getName();
		}
		$table->close();

		// === QUERY DATA
		$data = DB::select("
			SELECT
				left(a.KD_BRG,3)              as sub,
				right(a.KD_BRG,4)             as kdbar,
				b.AK00+b.GAK00                as stok,
				date_format(now(),'%Y%m%d')   as tanggal,
				if(a.ON_DC=0, 'L', '')        as kiriml,
				coalesce(c.DTR_1M,4)          as dtr_m1
			FROM $db.brg a
			JOIN $db.brgdt b ON a.KD_BRG=b.KD_BRG
			LEFT JOIN $db.brg_dc_ts c ON b.KD_BRG=c.KD_BRG
			WHERE b.YER=year(now())
			AND left(a.KD_BRG,3)=a.SUB
			ORDER BY a.KD_BRG
		");

		// === TULIS KE DBF
		$table = new TableEditor($dbfFilePath);

		foreach ($data as $row) {
			$row = (array)$row;
			$rec = $table->appendRecord();

			foreach ($fields as $field) {
				$rec->set($field, $row[$field] ?? null);
			}

			$table->writeRecord();
		}

		$table->save()->close();

		// === COPY KE DC
		copy(
			$dbfFilePath,
			'\\\\192.168.0.100\\spkirim\\DC_O' . $jns . '\\' . $tableName
		);

		return $tableName;
	}

}
