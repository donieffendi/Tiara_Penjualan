<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Cust;
use DataTables;
use Auth;
use DB;

include_once base_path()."/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

use \koolreport\laravel\Friendship;
use \koolreport\bootstrap4\Theme;

class RSoController extends Controller
{

	public function report()
    {
		$kodec = Cust::query()->get();
		session()->put('filter_gol', '');
		session()->put('filter_kodec1', '');
		session()->put('filter_namac1', '');
		session()->put('filter_kodet1', '');
		session()->put('filter_namat1', '');
		session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));
		session()->put('filter_sls', '');
		session()->put('filter_brg1', '');
		session()->put('filter_nabrg1', '');

        return view('oreport_so.report')->with(['kodec' => $kodec])->with(['hasil' => []]);
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
			return view('oreport_so.report')->with(['hasil' => $query]);
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

	public function prosesso( Request $request )
    {	
		$cbg = Auth::user()->CBG;
		$judul = DB::SELECT("SELECT if(time(now()) > time('09:00:00'), 'X', '') AS JAM, day(curdate()) AS HARI");
		$judul1 = $judul[0]->JAM == 'X' ? 'TERLAMBAT POSTING' : 'MAX POSTING JAM 9 AM';
		$judul2 = $judul[0]->JAM == 'X' ? 'TERLEWAT JAM 9 AM, HARAP INFO KE KA AKT' : '';

		$periode = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
		$bulan = substr($periode,0,2);
		$yeritu = substr($periode,3,4);

		$tahun = DB::SELECT("SELECT year(now()) AS TAHUN_INI, DATE_FORMAT(now(), '%m/%Y') AS PER_INI");
		$yerini = $tahun[0]->TAHUN_INI;
		$perini = $tahun[0]->PER_INI;
		
		if($request->has('ambil')){
			$cekDouble = DB::SELECT("CALL cek_brg_double");

			if(count($cekDouble) > 0){
				return redirect('/prosesso')->with('judul1', $judul1)->with('judul2', $judul2)->with('status', 'Master Data Ada Yang Double!');
			}

			if($judul[0]->HARI != '1'){
				return redirect('/prosesso')->with('judul1', $judul1)->with('judul2', $judul2)->with('status', 'Posting Harus Tanggal 1!');
			}

			if($judul[0]->JAM == 'X'){
				return redirect('/prosesso')->with('judul1', $judul1)->with('judul2', $judul2)->with('status', 'Terlambat Posting!');
			}

			if($periode == $perini){
				return redirect('/prosesso')->with('judul1', $judul1)->with('judul2', $judul2)->with('status', 'Tidak Bisa Menutup Periode Berjalan!');
			}
			
			$cekLolos = DB::SELECT("SELECT HPP FROM $cbg.PERID WHERE KD_PERI = '$periode'");

			if($cekLolos[0]->HPP != '0'){
				return redirect('/prosesso')->with('judul1', $judul1)->with('judul2', $judul2)->with('status', 'SO Cabang '.$cbg.' Sudah Di Proses! ');
			} else {
				$Tbrgdt = "brgdt$periode";
				$Tbrgd = "brgd$periode";
				$Tbrghd = "brghd$periode";

				if($yerini == $yeritu){
					DB::SELECT("UPDATE $cbg.brgdt
									SET brgdt.aw$bulan=brgdt.aw00,
										brgdt.ma$bulan=brgdt.ma00,
										brgdt.ke$bulan=brgdt.ke00,
										brgdt.ln$bulan=brgdt.ln00,
										brgdt.ak$bulan=brgdt.ak00,
										brgdt.harga$bulan=brgdt.hb
									where yer='$yeritu'");
					DB::SELECT("UPDATE $cbg.brgdt set brgdt.aw00=brgdt.ak00,brgdt.ma00=0,brgdt.ke00=0,brgdt.ln00=0 where yer='$yeritu'");
					DB::SELECT("UPDATE $cbg.brgdt set brgdt.raw$bulan=brgdt.raw00,
                                            brgdt.rma$bulan=brgdt.rma00,
                                            brgdt.rke$bulan=brgdt.rke00,
                                            brgdt.rln$bulan=brgdt.rln00,
                                            brgdt.rak$bulan=brgdt.rak00 
                                            where yer='$yeritu'");
					DB::SELECT("UPDATE $cbg.brgdt set brgdt.raw00=brgdt.rak00,brgdt.rma00=0,
										brgdt.rke00=0,brgdt.rln00=0 where yer='$yeritu'");
					DB::SELECT("UPDATE $cbg.brgdt SET
									rAK01 = rAW01 + rMA01 - rKE01 + rLN01, rAW02 = rAK01,
									rAK02 = rAW02 + rMA02 - rKE02 + rLN02, rAW03 = rAK02,  
									rAK03 = rAW03 + rMA03 - rKE03 + rLN03, rAW04 = rAK03,  
									rAK04 = rAW04 + rMA04 - rKE04 + rLN04, rAW05 = rAK04,  
									rAK05 = rAW05 + rMA05 - rKE05 + rLN05, rAW06 = rAK05,  
									rAK06 = rAW06 + rMA06 - rKE06 + rLN06, rAW07 = rAK06,  
									rAK07 = rAW07 + rMA07 - rKE07 + rLN07, rAW08 = rAK07,  
									rAK08 = rAW08 + rMA08 - rKE08 + rLN08, rAW09 = rAK08,  
									rAK09 = rAW09 + rMA09 - rKE09 + rLN09, rAW10 = rAK09,  
									rAK10 = rAW10 + rMA10 - rKE10 + rLN10, rAW11 = rAK10,  
									rAK11 = rAW11 + rMA11 - rKE11 + rLN11, rAW12 = rAK11,  
									rAK12 = rAW12 + rMA12 - rKE12 + rLN12 where  left(kd_brg,3)<>'011' and yer = '$yeritu'");
					DB::SELECT("UPDATE $cbg.brgd set brgd.aw$bulan=brgd.aw00,
                                    brgd.ma$bulan=brgd.ma00,
                                    brgd.ke$bulan=brgd.ke00,
                                    brgd.ln$bulan=brgd.ln00,
                                    brgd.ak$bulan=brgd.ak00
                                    where brgd.yer='$yeritu' ");
					DB::SELECT("UPDATE $cbg.brgd, 
								 $cbg.brgdt 
								 set
								 brgd.harga$bulan=brgdt.hb
								 where brgd.kd_brg=brgdt.kd_brg and brgd.cbg=brgdt.cbg and brgdt.yer=brgd.yer and brgd.yer=$yeritu");
					DB::SELECT("UPDATE $cbg.brgd set brgd.aw00=brgd.ak00,brgd.ma00=0,brgd.ke00=0,brgd.ln00=0 where yer='$yeritu'");
					DB::SELECT("UPDATE $cbg.brgdt,$cbg.brgd set brgdt.gaw00=brgd.aw00,brgdt.gma00=0,brgdt.gke00=0,brgdt.gln00=0,
										brgdt.gak00=brgd.ak00 
								where brgdt.kd_brg=brgd.kd_brg and brgdt.cbg=brgd.cbg and brgdt.yer=brgd.yer and brgd.yer='$yeritu'");
					DB::SELECT("UPDATE $cbg.brgD SET AK01 = AW01 + MA01 - KE01 + LN01, AW02 = AK01,
										AK02 = AW02 + MA02 - KE02 + LN02, AW03 = AK02,   
										AK03 = AW03 + MA03 - KE03 + LN03, AW04 = AK03,   
										AK04 = AW04 + MA04 - KE04 + LN04, AW05 = AK04,   
										AK05 = AW05 + MA05 - KE05 + LN05, AW06 = AK05,   
										AK06 = AW06 + MA06 - KE06 + LN06, AW07 = AK06,   
										AK07 = AW07 + MA07 - KE07 + LN07, AW08 = AK07,   
										AK08 = AW08 + MA08 - KE08 + LN08, AW09 = AK08,   
										AK09 = AW09 + MA09 - KE09 + LN09, AW10 = AK09,   
										AK10 = AW10 + MA10 - KE10 + LN10, AW11 = AK10,   
										AK11 = AW11 + MA11 - KE11 + LN11, AW12 = AK11,   
										AK12 = AW12 + MA12 - KE12 + LN12 where left(kd_brg,3)<>'011' and yer = '$yeritu'");
					DB::SELECT("UPDATE $cbg.brgdt SET AK01 = AW01 + MA01 - KE01 + LN01, AW02 = AK01,
										AK02 = AW02 + MA02 - KE02 + LN02, AW03 = AK02,   
										AK03 = AW03 + MA03 - KE03 + LN03, AW04 = AK03,   
										AK04 = AW04 + MA04 - KE04 + LN04, AW05 = AK04,   
										AK05 = AW05 + MA05 - KE05 + LN05, AW06 = AK05,   
										AK06 = AW06 + MA06 - KE06 + LN06, AW07 = AK06,   
										AK07 = AW07 + MA07 - KE07 + LN07, AW08 = AK07,   
										AK08 = AW08 + MA08 - KE08 + LN08, AW09 = AK08,   
										AK09 = AW09 + MA09 - KE09 + LN09, AW10 = AK09,   
										AK10 = AW10 + MA10 - KE10 + LN10, AW11 = AK10,   
										AK11 = AW11 + MA11 - KE11 + LN11, AW12 = AK11,   
										AK12 = AW12 + MA12 - KE12 + LN12 where  left(kd_brg,3)<>'011' and   yer = '$yeritu' ");

					if($cbg == 'TGZ' || $cbg == 'SOP' || $cbg == 'TMM'){
						DB::SELECT("UPDATE $cbg.brghd
											set aw00=ak$bulan, ma00=0, ke00=0, ln00=0, ak00=ak$bulan
											where yer='$yeritu'");
						DB::SELECT("UPDATE $cbg.brghd
											set gaw=gak$bulan, gma=0, gke=0, gln=0, gak=gak$bulan
											where yer='$yeritu'");
						DB::SELECT("UPDATE $brg.brghd '
										SET AK01 = AW01 + MA01 - KE01 + LN01, AW02 = AK01,
										AK02 = AW02 + MA02 - KE02 + LN02, AW03 = AK02,   
										AK03 = AW03 + MA03 - KE03 + LN03, AW04 = AK03,   
										AK04 = AW04 + MA04 - KE04 + LN04, AW05 = AK04,   
										AK05 = AW05 + MA05 - KE05 + LN05, AW06 = AK05,   
										AK06 = AW06 + MA06 - KE06 + LN06, AW07 = AK06,   
										AK07 = AW07 + MA07 - KE07 + LN07, AW08 = AK07,   
										AK08 = AW08 + MA08 - KE08 + LN08, AW09 = AK08,   
										AK09 = AW09 + MA09 - KE09 + LN09, AW10 = AK09,   
										AK10 = AW10 + MA10 - KE10 + LN10, AW11 = AK10,   
										AK11 = AW11 + MA11 - KE11 + LN11, AW12 = AK11,   
										AK12 = AW12 + MA12 - KE12 + LN12 where yer = '$yeritu'");
						DB::SELECT("UPDATE $cbg.brghd '
										SET GAK01 = GAW01 + GMA01 - GKE01 + GLN01, GAW02 = GAK01,
                                		GAK02 = GAW02 + GMA02 - GKE02 + GLN02, GAW03 = GAK02,   
                                		GAK03 = GAW03 + GMA03 - GKE03 + GLN03, GAW04 = GAK03,   
                                		GAK04 = GAW04 + GMA04 - GKE04 + GLN04, GAW05 = GAK04,   
                                		GAK05 = GAW05 + GMA05 - GKE05 + GLN05, GAW06 = GAK05,   
                                		GAK06 = GAW06 + GMA06 - GKE06 + GLN06, GAW07 = GAK06,   
                                		GAK07 = GAW07 + GMA07 - GKE07 + GLN07, GAW08 = GAK07,   
                                		GAK08 = GAW08 + GMA08 - GKE08 + GLN08, GAW09 = GAK08,   
                                		GAK09 = GAW09 + GMA09 - GKE09 + GLN09, GAW10 = GAK09,   
                                		GAK10 = GAW10 + GMA10 - GKE10 + GLN10, GAW11 = GAK10,   
                                		GAK11 = GAW11 + GMA11 - GKE11 + GLN11, GAW12 = GAK11,   
                                		GAK12 = GAW12 + GMA12 - GKE12 + GLN12 where yer = '$yeritu'");
					}

					DB::SELECT("UPDATE $cbg.perid set hpp=1 where kd_peri='$periode'");
				} else {
					DB::SELECT("UPDATE $cbg.brgdt$yeritu, $cbg.brgdt
								SET
									brgdt$yeritu.aw$bulan=brgdt.aw00, 
									brgdt$yeritu.ma$bulan=brgdt.ma00, 
									brgdt$yeritu.ke$bulan=brgdt.ke00, 
									brgdt$yeritu.ln$bulan=brgdt.ln00, 
									brgdt$yeritu.ak$bulan=brgdt.ak00, 
									brgdt$yeritu.harga$bulan=brgdt.hb 
								where brgdt$yeritu.kd_brg=brgdt.kd_brg and brgdt$yeritu.yer='$yeritu'");
					
					DB::SELECT("UPDATE $cbg.brgdt set brgdt.aw00=brgdt.ak00,brgdt.ma00=0,brgdt.ke00=0,brgdt.ln00=0 where yer=$yeritu+1");

					DB::SELECT("UPDATE $cbg.brgdt$yeritu,$cbg.brgdt 
										set brgdt$yeritu.raw$bulan=brgdt.raw00, 
										    brgdt$yeritu.rma$bulan=brgdt.rma00, 
                                            brgdt$yeritu.rke$bulan=brgdt.rke00, 
                                            brgdt$yeritu.rln$bulan=brgdt.rln00, 
                                            brgdt$yeritu.rak$bulan=brgdt.rak00  
                                        where brgdt$yeritu.kd_brg=brgdt.kd_brg and brgdt$yeritu.yer='$yeritu'");

					DB::SELECT("UPDATE $cbg.brgdt set brgdt.raw00=brgdt.rak00,brgdt.rma00=0,
									brgdt.rke00=0,brgdt.rln00=0 where yer=$yeritu+1");

					DB::SELECT("UPDATE $cbg.brgdt$yeritu SET
									rAK01 = rAW01 + rMA01 - rKE01 + rLN01, rAW02 = rAK01,
									rAK02 = rAW02 + rMA02 - rKE02 + rLN02, rAW03 = rAK02,   
									rAK03 = rAW03 + rMA03 - rKE03 + rLN03, rAW04 = rAK03,   
									rAK04 = rAW04 + rMA04 - rKE04 + rLN04, rAW05 = rAK04,   
									rAK05 = rAW05 + rMA05 - rKE05 + rLN05, rAW06 = rAK05,   
									rAK06 = rAW06 + rMA06 - rKE06 + rLN06, rAW07 = rAK06,   
									rAK07 = rAW07 + rMA07 - rKE07 + rLN07, rAW08 = rAK07,   
									rAK08 = rAW08 + rMA08 - rKE08 + rLN08, rAW09 = rAK08,   
									rAK09 = rAW09 + rMA09 - rKE09 + rLN09, rAW10 = rAK09,   
									rAK10 = rAW10 + rMA10 - rKE10 + rLN10, rAW11 = rAK10,   
									rAK11 = rAW11 + rMA11 - rKE11 + rLN11, rAW12 = rAK11,   
									rAK12 = rAW12 + rMA12 - rKE12 + rLN12 where  left(kd_brg,3)<>'011' and yer = '$yeritu'");

					DB::SELECT("UPDATE $cbg.brgd$yeritu,$cbg.brgd set brgd$yeritu.aw$bulan=brgd.aw00, 
                                          brgd$yeritu.ma$bulan=brgd.ma00, 
                                          brgd$yeritu.ke$bulan=brgd.ke00, 
                                          brgd$yeritu.ln$bulan=brgd.ln00, 
                                          brgd$yeritu.ak$bulan=brgd.ak00 
                                          where brgd$yeritu.kd_brg=brgd.kd_brg and brgd$yeritu.yer='$yeritu'");

					DB::SELECT("UPDATE $cbg.brgd$yeritu,$cbg.brgdt$yeritu 
								set brgd$yeritu.harga$bulan=brgdt$yeritu.hb 
								where brgd$yeritu.kd_brg=brgdt$yeritu.kd_brg 
								and brgd$yeritu.cbg=brgdt$yeritu.cbg 
								and brgdt$yeritu.yer=brgd$yeritu.yer 
								and brgd$yeritu.yer='$yeritu'");

					DB::SELECT("UPDATE $cbg.brgd set brgd.aw00=brgd.ak00,brgd.ma00=0,brgd.ke00=0,brgd.ln00=0 where yer=$yeritu+1");

					DB::SELECT("UPDATE $cbg.brgdt,$cbg.brgd 
								set brgdt.gaw00=brgd.aw00,brgdt.gma00=0,brgdt.gke00=0,brgdt.gln00=0,brgdt.gak00=brgd.ak00 
								where brgdt.kd_brg=brgd.kd_brg 
								and brgdt.cbg=brgd.cbg 
								and brgdt.yer=brgd.yer
								 and brgd.yer='$yeritu'");

					DB::SELECT("UPDATE $cbg.brgd$yeritu SET AK01 = AW01 + MA01 - KE01 + LN01, AW02 = AK01,
                             AK02 = AW02 + MA02 - KE02 + LN02, AW03 = AK02,   
                             AK03 = AW03 + MA03 - KE03 + LN03, AW04 = AK03,   
                             AK04 = AW04 + MA04 - KE04 + LN04, AW05 = AK04,   
                             AK05 = AW05 + MA05 - KE05 + LN05, AW06 = AK05,   
                             AK06 = AW06 + MA06 - KE06 + LN06, AW07 = AK06,   
                             AK07 = AW07 + MA07 - KE07 + LN07, AW08 = AK07,   
                             AK08 = AW08 + MA08 - KE08 + LN08, AW09 = AK08,   
                             AK09 = AW09 + MA09 - KE09 + LN09, AW10 = AK09,   
                             AK10 = AW10 + MA10 - KE10 + LN10, AW11 = AK10,   
                             AK11 = AW11 + MA11 - KE11 + LN11, AW12 = AK11,   
                             AK12 = AW12 + MA12 - KE12 + LN12 where left(kd_brg,3)<>'011' and yer = '$yeritu'");

					DB::SELECT("UPDATE $cbg.brgdt$yeritu SET AK01 = AW01 + MA01 - KE01 + LN01, AW02 = AK01,
                             AK02 = AW02 + MA02 - KE02 + LN02, AW03 = AK02,   
                             AK03 = AW03 + MA03 - KE03 + LN03, AW04 = AK03,   
                             AK04 = AW04 + MA04 - KE04 + LN04, AW05 = AK04,   
                             AK05 = AW05 + MA05 - KE05 + LN05, AW06 = AK05,   
                             AK06 = AW06 + MA06 - KE06 + LN06, AW07 = AK06,   
                             AK07 = AW07 + MA07 - KE07 + LN07, AW08 = AK07,   
                             AK08 = AW08 + MA08 - KE08 + LN08, AW09 = AK08,   
                             AK09 = AW09 + MA09 - KE09 + LN09, AW10 = AK09,   
                             AK10 = AW10 + MA10 - KE10 + LN10, AW11 = AK10,   
                             AK11 = AW11 + MA11 - KE11 + LN11, AW12 = AK11,   
                             AK12 = AW12 + MA12 - KE12 + LN12 where  left(kd_brg,3)<>'011' and yer = '$yeritu'");

					if($cbg == 'TGZ' || $cbg == 'SOP' || $cbg == 'TMM'){
						DB::SELECT("UPDATE $cbg.brghd$yeritu 
                             	 SET AK01 = AW01 + MA01 - KE01 + LN01, AW02 = AK01,
                                 AK02 = AW02 + MA02 - KE02 + LN02, AW03 = AK02,   
                                 AK03 = AW03 + MA03 - KE03 + LN03, AW04 = AK03,   
                                 AK04 = AW04 + MA04 - KE04 + LN04, AW05 = AK04,   
                                 AK05 = AW05 + MA05 - KE05 + LN05, AW06 = AK05,   
                                 AK06 = AW06 + MA06 - KE06 + LN06, AW07 = AK06,   
                                 AK07 = AW07 + MA07 - KE07 + LN07, AW08 = AK07,   
                                 AK08 = AW08 + MA08 - KE08 + LN08, AW09 = AK08,   
                                 AK09 = AW09 + MA09 - KE09 + LN09, AW10 = AK09,   
                                 AK10 = AW10 + MA10 - KE10 + LN10, AW11 = AK10,   
                                 AK11 = AW11 + MA11 - KE11 + LN11, AW12 = AK11,   
                                 AK12 = AW12 + MA12 - KE12 + LN12 where yer = $yeritu");
						
						DB::SELECT("UPDATE $cbg.brghd$yeritu 
                             	 SET GAK01 = GAW01 + GMA01 - GKE01 + GLN01, GAW02 = GAK01,
                                 GAK02 = GAW02 + GMA02 - GKE02 + GLN02, GAW03 = GAK02,   
                                 GAK03 = GAW03 + GMA03 - GKE03 + GLN03, GAW04 = GAK03,   
                                 GAK04 = GAW04 + GMA04 - GKE04 + GLN04, GAW05 = GAK04,   
                                 GAK05 = GAW05 + GMA05 - GKE05 + GLN05, GAW06 = GAK05,   
                                 GAK06 = GAW06 + GMA06 - GKE06 + GLN06, GAW07 = GAK06,   
                                 GAK07 = GAW07 + GMA07 - GKE07 + GLN07, GAW08 = GAK07,   
                                 GAK08 = GAW08 + GMA08 - GKE08 + GLN08, GAW09 = GAK08,   
                                 GAK09 = GAW09 + GMA09 - GKE09 + GLN09, GAW10 = GAK09,   
                                 GAK10 = GAW10 + GMA10 - GKE10 + GLN10, GAW11 = GAK10,   
                                 GAK11 = GAW11 + GMA11 - GKE11 + GLN11, GAW12 = GAK11,   
                                 GAK12 = GAW12 + GMA12 - GKE12 + GLN12 where yer = '$yeritu'");
					}

					DB::SELECT("UPDATE $cbg.perid set hpp=1 where kd_peri='$periode'");

					DB::SELECT("UPDATE $cbg.perid set hpp$cbg=1 where kd_peri='$periode'");
				}
			}
		}
		

        return view('oreport_so.proses')->with('judul1', $judul1)->with('judul2', $judul2);
    }
	

}
