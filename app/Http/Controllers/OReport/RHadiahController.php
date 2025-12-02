<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Format;

class RHadiahController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');

        session()->put('filter_gol', '');
        session()->put('filter_kodes1', '');
        session()->put('filter_namas1', '');
        session()->put('filter_brg1', '');
        session()->put('filter_nabrg1', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();


        return view('oreport_hadiah.report')->with([
            'cbg' => $cbg,
            'per' => $per,
        ]);
    }

    public function getHadiahReport(Request $request)
    {
        $cbgCode     = $request->cbg;
        $periode     = $request->periode;
        $periodeParts = explode('/', $periode);
        $type     = $request->type;
        $query='';
        $data = [];
        $cbgTable = '';
        session()->put('filter_cbg', $cbgCode);

        if (!empty($cbgCode)) {
            $cbgTable = $cbgCode . '.';
        }
        if($type == 'stok_gudang'){
            $query = "SELECT KD_BRGh, NA_BRGh, gaw as AW, gma as MA, gke as KE, gln as LN, gak as AK  FROM brghd WHERE CBG = '$cbgCode' ORDER BY KD_BRGh";
        }
        else if($type == 'perincian'){
            $query = "SELECT KD_BRGh, NA_BRGh, AW".$periodeParts[0]." AS AW, MA".$periodeParts[0]." AS MA, KE".$periodeParts[0]." AS KE, LN".$periodeParts[0]." AS LN, AK".$periodeParts[0]. " AS AK FROM brghd WHERE CBG = '$cbgCode' ORDER BY KD_BRGh";       
        }
        else if($type == 'card'){
            $cbgCode    = $request->cbg;
            $periode    = $request->periode;
            $tglDari    = $request->tglDari;
            $tglSampai  = $request->tglSampai;
            $kodeDari   = $request->kodeDari;
            $kodeSampai = $request->kodeSampai;

            $perx1 = substr($periode, 0,2);
            
            $cbg   = $cbgCode;
            $tabelbrghd = "brghd";    // sesuaikan jika beda

            $cbgCode    = $request->cbg;
            $periode    = $request->periode;
            $tglDari    = $request->tglDari;
            $tglSampai  = $request->tglSampai;
            $kodeDari   = $request->kodeDari;
            $kodeSampai = $request->kodeSampai;

            $perx1 = $periodeParts[0];
            $cbg   = $cbgCode;
            $tabelbrghd = "brghd";

            $query = "
                 select no_bukti,tgl,kd_brgh,na_brgh,awal,masuk,keluar,lain,baris, 
                    IF(@kd_brgh=kd_brgh,@AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN,@AKHIR:=AWAL+MASUK-KELUAR+LAIN) AS AKHIR, 
                    @kd_brgh:=kd_brgh FROM ( SELECT no_bukti,TGL,kd_brgh,na_brgh,awal,masuk,keluar,lain,0 as baris from ( 
                    select 'Saldo Awal' as no_bukti,date('$tglDari') as tgl,kd_brgh,na_brgh,sum(awal)+sum(masuk)-sum(keluar)+sum(lain) as awal, 0 as masuk,0 as keluar,0 as lain  from ( 
                    select kd_brgh,na_brgh,aw" . $perx1 . " as awal,0 as masuk,0 as keluar,0 as lain from brghd where aw" . $perx1 . "<>0 and kd_brgh>='$kodeDari' and  kd_brgh<='$kodeSampai'  union all 
                    select hdhd.kd_brgh,hdhd.na_brgh,0 as awal,hdhd.qty as masuk, 0 as keluar, 0 as lain from hdh, hdhd where 
                    hdh.no_bukti = hdhd.no_bukti and hdh.flag='HM' and hdh.per='11/2025' and hdh.cbg='TGZ' and hdhd.kd_brgh>='$kodeDari' and  hdhd.kd_brgh<='$kodeSampai'  and tgl<'$tglDari' union all 
                    select hdhd.kd_brgh,hdhd.na_brgh,0 as awal,0 as masuk,hdhd.qty as keluar,0 as lain from hdh, hdhd where hdh.no_bukti=hdhd.no_bukti 
                    and hdh.per='11/2025' and hdh.flag='HK' and hdh.cbg='TGZ' and hdhd.kd_brgh>='$kodeDari' and  hdhd.kd_brgh<='$kodeSampai'  and hdh.tgl<'$tglDari' union all 
                    select hdhd.kd_brgh,hdhd.na_brgh,0 as awal,0 as masuk,hdhd.qty as keluar, hdhd.qty as lain from hdh, hdhd where hdh.no_bukti=hdhd.no_bukti 
                    and hdh.per='11/2025' and hdh.flag='FH' and hdh.cbg='TGZ' and hdhd.kd_brgh>='$kodeDari' and  hdhd.kd_brgh<='$kodeSampai'  and hdh.tgl<'$tglDari'  
                    ) as AA 
                    GROUP BY kd_brgh ) AS BB WHERE  kd_brgh<>'$kodeDari' and AWAL <> 0  UNION all 
                    select hdh.no_bukti,hdh.TGL,hdhd.kd_brgh,hdhd.na_brgh,0 as awal,hdhd.qty as masuk, 0 as keluar, 0 as lain,1 as baris from hdh, hdhd where 
                    hdh.no_bukti = hdhd.no_bukti and hdh.cbg='TGZ' and hdh.flag='HM' and hdh.per='11/2025'  and 
                    kd_brgh>='$kodeDari' and  kd_brgh<='$kodeSampai'  and tgl >='$tglDari' and tgl<='$tglSampai' UNION ALL 
                    select hdh.NO_BUKTI,hdh.tgl, hdhd.kd_brgh,hdhd.na_brgh,0 as awal,0 as masuk,hdhd.qty as keluar,0 as lain,2 as baris from hdh, hdhd where hdh.no_bukti=hdhd.no_bukti 
                    and hdh.per='11/2025' and hdh.flag='HK' and hdh.cbg='TGZ' and hdhd.kd_brgh>='$kodeDari' and  hdhd.kd_brgh<='$kodeSampai'  and hdh.tgl >='$tglDari' and hdh.tgl<='$tglSampai'  UNION ALL 
                    select hdh.NO_BUKTI,hdh.tgl, hdhd.kd_brgh,hdhd.na_brgh,0 as awal,0 as masuk,0 as keluar,HDHD.QTY as lain,2 as baris from hdh, hdhd where hdh.no_bukti=hdhd.no_bukti 
                    and hdh.per='11/2025' and hdh.flag='FH' and hdh.cbg='TGZ' and hdhd.kd_brgh>='$kodeDari' and  hdhd.kd_brgh<='$kodeSampai'  and hdh.tgl >='$tglDari' and hdh.tgl<='$tglSampai'  
                    ) AS ZZZ JOIN (SELECT @kd_brgh:='',@AKHIR:=0) AS QQ ON 1=1 ORDER BY kd_brgh,TGL,BARIS ";
        }

        $data = DB::select($query);

        // Kirim ke view
        return $data;
    }


    public function jasperHadiahReport(Request $request)
    {
        $file ='';
       
        $cbgCode     = $request->cbg;
        $periode     = $request->periode;
        $periodeParts = explode('/', $periode);
        $type     = $request->type;
        $query = '';
        $data = [];
        $cbgTable = '';
        session()->put('filter_cbg', $cbgCode);

        if (!empty($cbgCode)) {
            $cbgTable = $cbgCode . '.';
        }
        if ($request->has('cetak_perincian')) {
            $query = "SELECT '".now()->format('d/m/Y')."' AS TGL, KD_BRGh, NA_BRGh, AW" . $periodeParts[0] . " AS AW, MA" . $periodeParts[0] . " AS MA, KE" . $periodeParts[0] . " AS KE, LN" . $periodeParts[0] . " AS LN, AK" . $periodeParts[0] . " AS AK FROM brghd WHERE CBG = '$cbgCode' ORDER BY KD_BRGh";
            $file         = 'rhadiah-p';
        } else if ($request->has('cetak_card')) {
            $cbgCode    = $request->cbg;
            $periode    = $request->periode;
            $tglDari    = $request->tgl_dari;
            $tglSampai  = $request->tgl_sampai;
            $kodeDari   = $request->kode_dari;
            $kodeSampai = $request->kode_sampai;

            $perx1 = explode('/', $periode)[0];

            $cbg   = $cbgCode;

            $query = "
                 select '" . now()->format('d/m/Y') . "' AS TGL_NOW, no_bukti,tgl,kd_brgh,na_brgh,awal,masuk,keluar,lain,baris, 
                    IF(@kd_brgh=kd_brgh,@AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN,@AKHIR:=AWAL+MASUK-KELUAR+LAIN) AS AKHIR, 
                    @kd_brgh:=kd_brgh FROM ( SELECT no_bukti,TGL,kd_brgh,na_brgh,awal,masuk,keluar,lain,0 as baris from ( 
                    select 'Saldo Awal' as no_bukti,date('$tglDari') as tgl,kd_brgh,na_brgh,sum(awal)+sum(masuk)-sum(keluar)+sum(lain) as awal, 0 as masuk,0 as keluar,0 as lain  from ( 
                    select kd_brgh,na_brgh,aw" . $perx1 . " as awal,0 as masuk,0 as keluar,0 as lain from brghd where aw" . $perx1 . "<>0 and kd_brgh>='$kodeDari' and  kd_brgh<='$kodeSampai'  union all 
                    select hdhd.kd_brgh,hdhd.na_brgh,0 as awal,hdhd.qty as masuk, 0 as keluar, 0 as lain from hdh, hdhd where 
                    hdh.no_bukti = hdhd.no_bukti and hdh.flag='HM' and hdh.per='11/2025' and hdh.cbg='TGZ' and hdhd.kd_brgh>='$kodeDari' and  hdhd.kd_brgh<='$kodeSampai'  and tgl<'$tglDari' union all 
                    select hdhd.kd_brgh,hdhd.na_brgh,0 as awal,0 as masuk,hdhd.qty as keluar,0 as lain from hdh, hdhd where hdh.no_bukti=hdhd.no_bukti 
                    and hdh.per='11/2025' and hdh.flag='HK' and hdh.cbg='TGZ' and hdhd.kd_brgh>='$kodeDari' and  hdhd.kd_brgh<='$kodeSampai'  and hdh.tgl<'$tglDari' union all 
                    select hdhd.kd_brgh,hdhd.na_brgh,0 as awal,0 as masuk,hdhd.qty as keluar, hdhd.qty as lain from hdh, hdhd where hdh.no_bukti=hdhd.no_bukti 
                    and hdh.per='11/2025' and hdh.flag='FH' and hdh.cbg='TGZ' and hdhd.kd_brgh>='$kodeDari' and  hdhd.kd_brgh<='$kodeSampai'  and hdh.tgl<'$tglDari'  
                    ) as AA 
                    GROUP BY kd_brgh ) AS BB WHERE  kd_brgh<>'$kodeDari' and AWAL <> 0  UNION all 
                    select hdh.no_bukti,hdh.TGL,hdhd.kd_brgh,hdhd.na_brgh,0 as awal,hdhd.qty as masuk, 0 as keluar, 0 as lain,1 as baris from hdh, hdhd where 
                    hdh.no_bukti = hdhd.no_bukti and hdh.cbg='TGZ' and hdh.flag='HM' and hdh.per='11/2025'  and 
                    kd_brgh>='$kodeDari' and  kd_brgh<='$kodeSampai'  and tgl >='$tglDari' and tgl<='$tglSampai' UNION ALL 
                    select hdh.NO_BUKTI,hdh.tgl, hdhd.kd_brgh,hdhd.na_brgh,0 as awal,0 as masuk,hdhd.qty as keluar,0 as lain,2 as baris from hdh, hdhd where hdh.no_bukti=hdhd.no_bukti 
                    and hdh.per='11/2025' and hdh.flag='HK' and hdh.cbg='TGZ' and hdhd.kd_brgh>='$kodeDari' and  hdhd.kd_brgh<='$kodeSampai'  and hdh.tgl >='$tglDari' and hdh.tgl<='$tglSampai'  UNION ALL 
                    select hdh.NO_BUKTI,hdh.tgl, hdhd.kd_brgh,hdhd.na_brgh,0 as awal,0 as masuk,0 as keluar,HDHD.QTY as lain,2 as baris from hdh, hdhd where hdh.no_bukti=hdhd.no_bukti 
                    and hdh.per='11/2025' and hdh.flag='FH' and hdh.cbg='TGZ' and hdhd.kd_brgh>='$kodeDari' and  hdhd.kd_brgh<='$kodeSampai'  and hdh.tgl >='$tglDari' and hdh.tgl<='$tglSampai'  
                    ) AS ZZZ JOIN (SELECT @kd_brgh:='',@AKHIR:=0) AS QQ ON 1=1 ORDER BY kd_brgh,TGL,BARIS ";
            $file         = 'rhadiah-c';
        } else if ($request->has('cetak_stok_gudang')) {
            $query = "SELECT '" . now()->format('d/m/Y') . "' AS TGL, KD_BRGh, NA_BRGh, gaw as AW, gma as MA, gke as KE, gln as LN, gak as AK  FROM brghd WHERE CBG = '$cbgCode' AND GAK > 0 ORDER BY KD_BRGh";
            $file         = 'rhadiah-s';
        }
        $data = DB::select($query);
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));



        $PHPJasperXML->setData(array_map(function ($item) {
            return (array) $item;
        },$data));
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}
