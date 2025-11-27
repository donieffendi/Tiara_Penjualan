<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Perid;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";
use PHPJasperXML;

class RjualRekananController extends Controller
{

    public function index() {
        $cbg = DB::SELECT("SELECT KODE FROM toko WHERE STA IN ('MA','CB')");
		session()->put('filter_cbg', '');
        $per = Perid::query()->get();
		session()->put('filter_per', '');

        session()->put('filter_tglDari', date("d-m-Y"));
		session()->put('filter_tglSampai', date("d-m-Y"));

        return view('master_report_penjualan_rekanan.index')->with('cbg', $cbg)->with('per', $per);
    }

    public function getRjualRekanan(Request $request)
    {
    // $cbg = Auth::user()->CBG;
    $cbg = $request->cbg;
    $per = $request->perio;
    $tgl1 = date('Y-m-d', strtotime(str_replace('/', '-', $request->tglDr)));
    $tgl2 = date('Y-m-d', strtotime(str_replace('/', '-', $request->tglSmp)));
    $sub1 = $request->sub1;
    $sub2 = $request->sub2;
    
    // $per=session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
    // $bulan = session()->get('periode')['bulan'];

    $bulan = substr($per, 0, 2);

    DB::SELECT("UPDATE juald$bulan b,JUAL$bulan a,( select a.NAMA,a.TGLM,a.TGLS,b.SUB,b.KOMISI from tgz.rekananh a, tgz.rekanand b where a.NO_BUKTI=b.NO_BUKTI and b.SUB BETWEEN '$sub1' and '$sub2' ) c
                SET b.KOMISI=c.KOMISI WHERE b.NO_BUKTI=a.NO_BUKTI and a.REKANAN = c.nama and b.SUB2=c.sub and a.rekanan<>''  AND a.JNS_KASIR='HAPPY FRESH' AND  DATE(a.TGL) BETWEEN '$tgl1' AND '$tgl2'"); /*harusnya X1 dan X2 dari entrian bukan 2001-01-01*/
    DB::SELECT("SET @cek:='' ");
    $brg = DB::SELECT("SELECT AA.*,IF(AA.NO_BUKTI=@cek,0,AA.TOTALA) TOTALX, @cek:=AA.NO_BUKTI BKTX,0 TOTN,brg.KET_UK, brg.SUPP, '$tgl1' as tgl1, '$tgl2' as tgl2 FROM /*harusnya tgl1 dan tgl2 dari entrian X1 dan X2 bukan 2001-01-01*/
                            (SELECT juald$bulan.NO_ID, jual$bulan.NO_BUKTI,jual$bulan.TGL as tgl,juald$bulan.KD_BRG,
                            jual$bulan.REKANAN,
                            jual$bulan.KSR,
                            juald$bulan.KOMISI, 
                            juald$bulan.NA_BRG,sum(juald$bulan.qty) as QTY,       
                            juald$bulan.harga as HARGA,                          
                            juald$bulan.hargavip HVIP,                         
                            sum(juald$bulan.diskon) as DISKON,                        
                            juald$bulan.disc as DISC,                            
                            sum(juald$bulan.nppn)as NPPN,                             
                            sum(juald$bulan.dpp)as DPP,                               
                            sum(juald$bulan.total)as TOTAL,                           
                            jual$bulan.totala as TOTALA,                                                     
                            juald$bulan.FLAG, juald$bulan.TYPE,                    
                            juald$bulan.PER                                           
                            FROM juald$bulan, jual$bulan
                            WHERE  jual$bulan.CBG='$cbg'                                
                            AND jual$bulan.NO_BUKTI=juald$bulan.NO_BUKTI           
                            AND jual$bulan.FLAG='JL'                                
                            AND jual$bulan.TGL>='$tgl1' AND jual$bulan.TGL<='$tgl2' AND jual$bulan.JNS_KASIR='HAPPY FRESH'       
                            GROUP BY juald$bulan.NO_BUKTI,juald$bulan.KD_BRG
                            ) AS AA, BRG                                        
                            WHERE AA.KD_BRG=brg.KD_BRG                                   

                            AND brg.SUB>='$sub1' and brg.SUB<='$sub2' order by NO_BUKTI,KD_BRG "); /*harusnya sub1 dan sub2 dari entrian SUB1 dan SUB2*/
		
        return Datatables::of($brg)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
					{   
                        // url untuk delete di index
                        $url = "'".url("brg/delete/" . $row->NO_ID )."'";
                        // batas
                        
                        $btnDelete = ' onclick="deleteRow('.$url.')"';
    
                        $btnPrivilege =
                            '
                                    <a class="dropdown-item" href="brg/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <hr></hr>
                                    <a hidden class="dropdown-item btn btn-danger" ' . $btnDelete . '>
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                        Delete
                                    </a>
                            ';
                    } else {
                        $btnPrivilege = '';
                    }
    
                    $actionBtn =
                        '
                        <div class="dropdown show" style="text-align: center">
                            <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bars"></i>
                            </a>
    
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <a hidden class="dropdown-item" href="brg/show/' . $row->NO_ID . '">
                                <i class="fas fa-eye"></i>
                                    Lihat
                                </a>
    
                                ' . $btnPrivilege . '
                            </div>
                        </div>
                        ';
    
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
    }

    public function store(Request $request)
    {


        $this->validate(
            $request,
            // GANTI 9

            [
                'NA_BRG'       => 'required'

            ]

        );

        $query = DB::table('brg')->select('KD_BRG')->orderByDesc('KD_BRG')->first();
        
        $kd_brg = '';
        if ($query) {
            
            $query = $query->KD_BRG;
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $kd_brg = $query;
            
        } else {
            $kd_brg = '0001' ;
        }

        $CBG = Auth::user()->CBG;
 
        // Insert Header

        // ganti 10

        $brg = Brg::create(
            [
                'KD_BRG'         => ($kd_brg == null) ? "" : $kd_brg,
                'NA_BRG'         => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'TYPE'           => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'SATUAN'         => ($request['SATUAN'] == null) ? "" : $request['SATUAN'],
                'KET_UK'         => ($request['KET_UK'] == null) ? "" : $request['KET_UK'],
                'KET_KEM'        => ($request['KET_KEM'] == null) ? "" : $request['KET_KEM'],
                'DIAMETER'       => (float) str_replace(',', '', $request['DIAMETER']),
                'TEBAL'          => (float) str_replace(',', '', $request['TEBAL']),
                'PANJANG'        => (float) str_replace(',', '', $request['PANJANG']),
                'KG'             => (float) str_replace(',', '', $request['KG']),
                'SMIN'           => (float) str_replace(',', '', $request['SMIN']),
                'SMAX'           => (float) str_replace(',', '', $request['SMAX']),
                'HB'             => (float) str_replace(',', '', $request['HB']),
                'HS'             => (float) str_replace(',', '', $request['HS']),
                'HB_NAIK'        => (float) str_replace(',', '', $request['HB_NAIK']),
                'H_MINC'         => (float) str_replace(',', '', $request['H_MINC']),
                'LEBAR'          => (float) str_replace(',', '', $request['LEBAR']),
                'PN'             => ($request['PN'] == null) ? "" : $request['PN'],
                'GROUP'          => ($request['GROUP'] == null) ? "" : $request['GROUP'],
                'SUB_GROUP'      => ($request['SUB_GROUP'] == null) ? "" : $request['SUB_GROUP'],
                'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now(),
                'BL_PER'         => date('Y-m-d', strtotime($request['BL_PER'])),
                'BL_AKR'         => date('Y-m-d', strtotime($request['BL_AKR'])),
                'JL_AKR'         => date('Y-m-d', strtotime($request['JL_AKR'])),
                'SUPP'           => ($request['KODES'] == null) ? "" : $request['KODES'],
                'KLK'            => ($request['KLK'] == null) ? "" : $request['KLK'],
                'LOKASI'         => ($request['LOKASI'] == null) ? "" : $request['LOKASI'],
                'KELOMPOK'       => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'UP_HB'          => ($request['UP_HB'] == null) ? "" : $request['UP_HB'],
                'ALASAN'         => ($request['ALASAN'] == null) ? "" : $request['ALASAN'],
                'TD_OD'          => ($request['TD_OD'] == null) ? "" : $request['TD_OD'],
                'HJUAL'          => (float) str_replace(',', '', $request['HJUAL']),
                'MARGIN'         => (float) str_replace(',', '', $request['MARGIN']),
                'HJ2'            => (float) str_replace(',', '', $request['HJ2']),
                'CBG'            => $CBG
            ]
        );

        //  ganti 11

	    $kd_brgx = $request['KD_BRG'];
		
		$Brg = Brg::where('KD_BRG', $kd_brgx )->first();

        // DB::SELECT("UPDATE brg,  brgdx
        //                     SET  brgdx.ID =  brg.NO_ID  WHERE  brg.KD_BRG =  brgdx.KD_BRG 
		// 					AND  brg.KD_BRG='$kd_brgx';");
					       
        //return redirect('/brg/edit/?idx=' . $brg->NO_ID . '&tipx=edit')->with('statusInsert', 'Data baru berhasil ditambahkan');
		return redirect('/brg')->with('statusInsert', 'Data baru berhasil ditambahkan');	
		
		}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    

    // ganti 15

    public function edit(Request $request , Brg $brg)
    {

        // ganti 16
        $tipx = $request->tipx;

		$idx = $request->idx;
					
        $cbg = Auth::user()->CBG;
		
		if ( $idx =='0' && $tipx=='undo'  )
	    {
			$tipx ='top';
			
		   }
		   
		if ($tipx=='search') {
			
		   	
    	   $kodex = $request->kodex;
		   
		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brg
		                 where KD_BRG = '$kodex'						 
		                 ORDER BY KD_BRG ASC  LIMIT 1" );
						 
			
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = 0; 
			  }
		
					
		}
		
		if ($tipx=='top') {
			
		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brg      
		                 ORDER BY KD_BRG ASC  LIMIT 1" );
					 
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = 0; 
			  }
			  
		}
		
		
		if ($tipx=='prev' ) {
			
    	   $kodex = $request->kodex;
			
		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brg      
		             where KD_BRG < 
					 '$kodex' ORDER BY KD_BRG DESC LIMIT 1" );
			

			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = $idx; 
			  }
			  
			  
			  
			  

		}
		if ($tipx=='next' ) {
			
				
      	   $kodex = $request->kodex;
	   
		   $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brg   
		             where KD_BRG > 
					 '$kodex' ORDER BY KD_BRG ASC LIMIT 1" );
					 
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = $idx; 
			  }
			  
			
		}

		if ($tipx=='bottom') {
		  
    		$bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brg     
		              ORDER BY KD_BRG DESC  LIMIT 1" );
					 
			if(!empty($bingco)) 
			{
				$idx = $bingco[0]->NO_ID;
			  }
			else
			{
				$idx = 0; 
			  }
			  
			
		}


		if ( $tipx=='undo' || $tipx=='search' )
	    {
        
			$tipx ='edit';
			
		   }
		
		//   $kd_brg = $brg->KD_BRG; 
	
	  	if ( $idx != 0 ) 
		{   
			$brg = Brg::where('NO_ID', $idx )->first();
            $kd_brg = $brg->KD_BRG;
            $head = DB::SELECT("SELECT A.NO_ID, A.SUB, A.KELOMPOK, A.KDBAR, A.KD_BRG, A.NA_BRG, A.ITEM_UNI,
                concat('[',if(A.KD_BRG<>left(A.BARCODE,7),'=',if(C.S_BAR='Y','V','&')),']',' ',A.NA_BRG) as NMBAR, 
                A.MARGIN, A.BARCODE, A.TYPE, A.SUPP, A.MO, A.KET_KEM, A.KET_UK,
                A.MOO, A.RETUR, A.SP_L, A.SP_LF, A.SP_LZ, A.KK, A.PPN, A.KOSONG,
                IF(YEAR(A.TGL_KOSONG)>2001,A.TGL_KOSONG,'') TGL_KOSONG,
                IF(YEAR(A.tg_smp)>2001,A.tg_smp,'') TG_SMP,
                A.KMP, A.KMP1, A.KMP2,A.ON_DC,
                B.GAK00, B.AK00, B.GAK00+B.AK00 AS STOK, B.TD_OD, B.CAT_OD, B.PSN, B.HJ,
                IF(YEAR(B.TGL_OD)>2001,B.TGL_OD,'') TGL_OD,
                IF(YEAR(B.TGL_BK)>2001,B.TGL_BK,'') TGL_BK,
                C.NAMAS, C.ALMT_K, C.KOTA
                FROM 	brgdt B, brg A LEFT JOIN sup C ON A.SUPP=C.KODES
                WHERE A.KD_BRG=B.KD_BRG AND A.KD_BRG='$kd_brg'
                LIMIT 1");

                // var_dump($head); die();

            $brg_dc_ts = DB::SELECT("SELECT STOK_DC, DTR,DTR2,DTR_MANUAL,DTR_1M FROM brg_dc_ts WHERE KD_BRG='$kd_brg'");

            $supd2 = DB::SELECT("SELECT supd2.harga as HB,supd2.D1 AS D1,supd2.D2 AS D2,supd2.D3 AS D3, 
                        supd2.PPN AS PPN,concat(supd2.cat,supd2.cat2,supd2.cat3) as keti 
                        from supd2,brg where supd2.KD_BRG=brg.KD_BRG and supd2.kd_brg='$kd_brg' and supd2.supp='".$brg->SUPP."'");

            $dis = DB::SELECT("SELECT dis.no_bukti FROM dis,disd where 
                                DIS.no_bukti=disd.no_bukti and DIS.TGL_MULAI<=date(now()) 
                                and DIS.TGL_SLS>=date(now()) and disd.kd_brg='$kd_brg'");
            // var_dump($dis); die();
	     }
		 else
		 {
             $brg = new Brg;			 
		 }

        $detailpbl = DB::SELECT("SELECT belid.NO_ID NO, belid.KD_BRG, belid.NA_BRG, beli.NO_BUKTI, beli.NO_PO, belid.sisapo QTY_PO, beli.TGL, 
                                        beli.KODES, beli.NAMAS, belid.qty, belid.harga, belid.total, 
                                        belid.qtyk XD, belid.kemasan, 
                                        belid.PPN, belid.DISKON1 D1, belid.DISKON2 D2, belid.DISKON3 D3, belid.DISKON4 D4, '1' as POSTED 
                                from beliz beli, belizd belid 
                                WHERE beli.NO_BUKTI=belid.no_bukti AND beli.flag<>'RB' 
                                AND date(beli.TGL) BETWEEN DATE_SUB(CURDATE(),INTERVAL 120 DAY) and CURDATE() 
                                AND belid.KD_BRG='$kd_brg' 
                                UNION ALL 
                                SELECT belid.NO_ID NO, belid.KD_BRG, belid.NA_BRG, beli.NO_BUKTI, beli.NO_PO, belid.sisapo QTY_PO, beli.TGL, 
                                        beli.KODES, beli.NAMAS, belid.qty, belid.harga, belid.total, 
                                        belid.qtyk XD, belid.kemasan, 
                                        belid.PPN, belid.DISKON1 D1, belid.DISKON2 D2, belid.DISKON3 D3, belid.DISKON4 D4, '0' as POSTED 
                                from beli, belid 
                                WHERE beli.NO_BUKTI=belid.no_bukti AND beli.flag<>'RB' 
                                AND date(beli.TGL) BETWEEN DATE_SUB(CURDATE(),INTERVAL 30 DAY) and CURDATE() 
                                AND belid.KD_BRG='$kd_brg'
                                ORDER BY TGL DESC" );

        $detailord = DB::SELECT("SELECT * FROM 
                                        (select 'ORDER SELA' as flag, survey.NO_BUKTI,survey.NO_AGENDA as no_po, 
                                        survey.TGL,survey.CBG,surveyd.NA_BRG,surveyd.R_PBL as QTY,          
                                        surveyd.HB_PBL as harga, surveyd.R_PBL*surveyd.HB_PBL as total,     
                                        surveyd.KET_KEM, surveyd.PPN                                        
                                        from survey, surveyd                                                
                                        where survey.NO_BUKTI=surveyd.AG_PBL and survey.flag='BS' AND     
                                        surveyd.KD_BRG='$kd_brg' and survey.cbg='$cbg' ORDER BY TGL desc limit 5) AS NAN 
                                        UNION ALL                                                                    
                                        SELECT * FROM                                                                
                                        (select 'SURVEY PENJUALAN' as flag,survey.NO_BUKTI,survey.NO_AGENDA as no_po, 
                                        survey.TGL,survey.CBG,surveyd.NA_BRG,surveyd.R_PBL as QTY,                   
                                        surveyd.HB_PBL as harga, surveyd.R_PBL*surveyd.HB_PBL as total,              
                                        surveyd.KET_KEM, surveyd.PPN                                                 
                                        from survey, surveyd                                                         
                                        where survey.NO_BUKTI=surveyd.AG_PBL and survey.flag='PS' AND              
                                        surveyd.KD_BRG='$kd_brg' and survey.cbg='$cbg' ORDER BY TGL desc limit 5) AS NDA ");
        
        $detaildtl = DB::SELECT("SELECT brgdt.PSN,date(brgdt.TGL_PSN) AS TGL_PSN, brgdt.TGL_TRM, 
                                        brgdt.BKT_TRM,brgdt.LPH,brgdt.SRMIN,brgdt.BKT_TK,  
                                        brgdt.TGL_TK,brgdt.TGL_AT,brgdt.BKT_AT,brg.margin, 
                                if(brg.PPN=1,'Y','N') AS PPN,brgdt.HB          
                                from brgdt,brg 
                                WHERE brgdt.kd_brg=brg.kd_brg and BRGDT.kd_brg='$kd_brg' 
                                and BRGDT.cbg='$cbg' ORDER BY BRGDT.KD_BRG ");
if(!$dis) $dis=DB::SELECT("SELECT '' as no_bukti");
if(!$brg_dc_ts) $brg_dc_ts=DB::SELECT("SELECT 0 AS STOK_DC, 0 AS DTR, 0 AS DTR2, 0 AS DTR_MANUAL, 0 AS DTR_1M");
if(!$supd2) $supd2=DB::SELECT("SELECT 0 as HB, 0 AS D1, 0 AS D2, 0 AS D3, 
                        0 AS PPN");
		 $data = [
                    'brg'              => $brg,
                    'header'           => $head,
                    'supd2'            => $supd2,
                    'brg_dc_ts'        => $brg_dc_ts,
                    'dis'              => $dis,
                    'detailpbl'        => $detailpbl,
                    'detailord'        => $detailord,
                    'detaildtl'        => $detaildtl,
                ];				
                
        return view('master_brg.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx ]);		
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request, Brg $brg)
    {

        $this->validate(
            $request,
            [

                // ganti 19

                'KD_BRG'       => 'required',
                'NA_BRG'       => 'required'
            ]
        );

        // ganti 20
		
        $CBG = Auth::user()->CBG;
 
        $tipx = 'edit';
		$idx = $request->idx;

        $brg->update(
            [

                'NA_BRG'         => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'TYPE'           => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'SATUAN'         => ($request['SATUAN'] == null) ? "" : $request['SATUAN'],
                'KET_UK'         => ($request['KET_UK'] == null) ? "" : $request['KET_UK'],
                'KET_KEM'        => ($request['KET_KEM'] == null) ? "" : $request['KET_KEM'],
                'DIAMETER'       => (float) str_replace(',', '', $request['DIAMETER']),
                'TEBAL'          => (float) str_replace(',', '', $request['TEBAL']),
                'PANJANG'        => (float) str_replace(',', '', $request['PANJANG']),
                'KG'             => (float) str_replace(',', '', $request['KG']),
                'SMIN'           => (float) str_replace(',', '', $request['SMIN']),
                'SMAX'           => (float) str_replace(',', '', $request['SMAX']),
                'HB'             => (float) str_replace(',', '', $request['HB']),
                'HS'             => (float) str_replace(',', '', $request['HS']),
                'HB_NAIK'        => (float) str_replace(',', '', $request['HB_NAIK']),
                'H_MINC'         => (float) str_replace(',', '', $request['H_MINC']),
                'LEBAR'          => (float) str_replace(',', '', $request['LEBAR']),
                'PN'             => ($request['PN'] == null) ? "" : $request['PN'],
                'GROUP'          => ($request['GROUP'] == null) ? "" : $request['GROUP'],
                'SUB_GROUP'      => ($request['SUB_GROUP'] == null) ? "" : $request['SUB_GROUP'],		 
                'SUPP'           => ($request['KODES'] == null) ? "" : $request['KODES'],		 
                'KLK'            => ($request['KLK'] == null) ? "" : $request['KLK'],		 
				'USRNM'          => Auth::user()->username,
                'TG_SMP'         => Carbon::now(),
                'BL_PER'         => date('Y-m-d', strtotime($request['BL_PER'])),
                'BL_AKR'         => date('Y-m-d', strtotime($request['BL_AKR'])),
                'JL_AKR'         => date('Y-m-d', strtotime($request['JL_AKR'])),
                'SUPP'           => ($request['KODES'] == null) ? "" : $request['KODES'],
                'KLK'            => ($request['KLK'] == null) ? "" : $request['KLK'],
                'LOKASI'         => ($request['LOKASI'] == null) ? "" : $request['LOKASI'],
                'KELOMPOK'       => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'UP_HB'          => ($request['UP_HB'] == null) ? "" : $request['UP_HB'],
                'ALASAN'         => ($request['ALASAN'] == null) ? "" : $request['ALASAN'],
                'TD_OD'          => ($request['TD_OD'] == null) ? "" : $request['TD_OD'],
                'HJUAL'          => (float) str_replace(',', '', $request['HJUAL']),
                'MARGIN'         => (float) str_replace(',', '', $request['MARGIN']),
                'HJ2'            => (float) str_replace(',', '', $request['HJ2']),
                'CBG'            => $CBG
            ]
        );

        ////////////////////////////////////////////////////

        // $brg = Brg::where('KD_BRG', $kd_brgx )->first();

        //  ganti 21

        //return redirect('/brg/edit/?idx=' . $brg->NO_ID . '&tipx=edit');
        // return redirect('/brg/edit/?idx=' . $Brg->NO_ID . '&tipx=edit');	
		return redirect('/brg')->with('status', 'Data berhasil diupdate');
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request , Brg $brg)
    {

        // ganti 23
        $deleteBrg = Brg::find($brg->NO_ID);

        // ganti 24

        $deleteBrg->delete();

        // ganti 
        return redirect('/brg')->with('status', 'Data berhasil dihapus');
    }

    // public function Print(Request $request)
    // {
    //     $search = $request->input('search', ''); // ambil parameter search dari URL

    //     $file = 'Laporan_Komisi';
    //     $PHPJasperXML = new \PHPJasperXML();
    //     $PHPJasperXML->load_xml_file(base_path('app/reportc01/phpjasperxml/' . $file . '.jrxml'));

    //     // tambahkan kondisi pencarian jika ada input search
    //     $where = '';
    //     if (!empty($search)) {
    //         $where = "WHERE (KODE LIKE '%$search%' OR NAMA LIKE '%$search%')";
    //     }

    //     $brg = DB::SELECT("SELECT AA.*,IF(AA.NO_BUKTI=@cek,0,AA.totala) totalx, @cek:=AA.NO_BUKTI bktx,0 totn,brg.KET_UK, brg.SUPP, '$tgl1' as tgl1, '$tgl2' as tgl2 FROM /*harusnya tgl1 dan tgl2 dari entrian X1 dan X2 bukan 2001-01-01*/
    //                         (SELECT juald$bulan.NO_ID, jual$bulan.NO_BUKTI,jual$bulan.TGL as tgl,juald$bulan.KD_BRG,
    //                         jual$bulan.REKANAN,
    //                         jual$bulan.KSR,
    //                         juald$bulan.KOMISI, 
    //                         juald$bulan.NA_BRG,sum(juald$bulan.qty) as QTY,       
    //                         juald$bulan.harga as HARGA,                          
    //                         juald$bulan.hargavip as HVIP,                         
    //                         sum(juald$bulan.diskon) as DISKON,                        
    //                         juald$bulan.disc as DISC,                            
    //                         sum(juald$bulan.nppn)as NPPN,                             
    //                         sum(juald$bulan.dpp)as DPP,                               
    //                         sum(juald$bulan.total)as TOTAL,                           
    //                         jual$bulan.totala as TOTALA,                                                     
    //                         juald$bulan.FLAG, juald$bulan.TYPE,                    
    //                         juald$bulan.PER, '$tgl1' AS TGL1, '$tgl2' AS TGL2                                           
    //                         FROM juald$bulan, jual$bulan
    //                         WHERE  jual$bulan.CBG='$cbg'                                
    //                         AND jual$bulan.NO_BUKTI=juald$bulan.NO_BUKTI           
    //                         AND jual$bulan.flag='JL'                                
    //                         AND jual$bulan.tgl>='$tgl1' AND jual$bulan.tgl<='$tgl2' AND jual$bulan.JNS_KASIR='HAPPY FRESH'       
    //                         GROUP BY juald$bulan.NO_BUKTI,juald$bulan.KD_BRG
    //                         ) AS AA, BRG                                        
    //                         WHERE AA.KD_BRG=brg.KD_BRG                                   

    //                         AND brg.sub>='$sub1' and brg.sub<='$sub2'   order by NO_BUKTI,kd_brg ");

    //     $data = [];

    //     foreach ($query as $value) {
    //         $data[] = [
    //             'KODE'   => $value->KODE,
    //             'NAMA'   => $value->NAMA
    //         ];
    //     }

    //     $PHPJasperXML->setData($data);
    //     ob_end_clean();
    //     $PHPJasperXML->outpage("I"); // tampil langsung di browser
    // }

    public function Print(Request $request)
    {
        $cbg = $request->cbg;
        $per = $request->perio;
        $tgl1 = date('Y-m-d', strtotime(str_replace('/', '-', $request->tglDr)));
        $tgl2 = date('Y-m-d', strtotime(str_replace('/', '-', $request->tglSmp)));
        $sub1 = $request->sub1;
        $sub2 = $request->sub2;
        $search = $request->search;

        $bulan = substr($per, 0, 2);

        // ===== CONTOH FILTER KHUSUS search (opsional)
        $filterSearch = "";
        if (!empty($search)) {
            $filterSearch = " AND (AA.KD_BRG LIKE '%$search%' 
                                OR AA.NA_BRG LIKE '%$search%'
                                OR AA.REKANAN LIKE '%$search%')";
        }

        $brg = DB::SELECT("
            SELECT AA.*, IF(AA.NO_BUKTI=@cek,0,AA.TOTALA) TOTALX,
            @cek:=AA.NO_BUKTI BKTX, brg.KET_UK, brg.SUPP,
            '$tgl1' as TGL1, '$tgl2' as TGL2
            FROM
            (
                SELECT juald$bulan.NO_ID, jual$bulan.NO_BUKTI, jual$bulan.TGL as TGL,
                juald$bulan.KD_BRG, jual$bulan.REKANAN, jual$bulan.KSR,
                juald$bulan.KOMISI, juald$bulan.NA_BRG,
                sum(juald$bulan.qty) as QTY,
                juald$bulan.harga as HARGA,
                juald$bulan.hargavip HVIP,
                sum(juald$bulan.diskon) as DISKON,
                juald$bulan.disc as DISC,
                sum(juald$bulan.nppn) as NPPN,
                sum(juald$bulan.dpp) as DPP,
                sum(juald$bulan.total) as TOTAL,
                jual$bulan.totala as TOTALA,
                juald$bulan.FLAG, juald$bulan.TYPE, juald$bulan.PER
                FROM juald$bulan
                JOIN jual$bulan ON jual$bulan.NO_BUKTI = juald$bulan.NO_BUKTI
                WHERE jual$bulan.CBG='$cbg'
                AND jual$bulan.FLAG='JL'
                AND jual$bulan.JNS_KASIR='HAPPY FRESH'
                AND jual$bulan.TGL BETWEEN '$tgl1' AND '$tgl2'
                GROUP BY juald$bulan.NO_BUKTI, juald$bulan.KD_BRG
            ) AS AA
            JOIN BRG ON AA.KD_BRG = brg.KD_BRG
            WHERE brg.SUB BETWEEN '$sub1' AND '$sub2'
            $filterSearch
            ORDER BY AA.NO_BUKTI, AA.KD_BRG
        ");

        // === kirim data ke Jasper ===
        $data = json_decode(json_encode($brg), true);

        $file = 'Laporan_Komisi';
        $PHPJasperXML = new \PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path('app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        $PHPJasperXML->setData($data);

        ob_end_clean();
        return $PHPJasperXML->outpage("I");
    }
}