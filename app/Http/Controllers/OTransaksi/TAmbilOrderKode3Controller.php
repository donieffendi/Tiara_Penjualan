<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;
class TAmbilOrderKode3Controller extends Controller
{
    public function getSubList($cbg)
    {
        try {
            if (empty($cbg)) {
                return [];
            }

            $query = "
                SELECT DISTINCT A.SUB
                FROM {$cbg}.brg A
                INNER JOIN {$cbg}.brgdt B ON A.KD_BRG = B.KD_BRG
                WHERE A.TD_OD = ''
                  AND B.LPH > 0
                  AND A.SUB IS NOT NULL
                  AND A.SUB != ''
                ORDER BY A.SUB ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getSubList: ' . $e->getMessage());
            return [];
        }
    }
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Ambil Order Kode 3';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TAmbilOrderKode3.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TAmbilOrderKode3.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $per = $request->session()->get('periode');
            $periode = $per['bulan'] .'/'.$per['tahun'];
            // Ambil list cabang untuk dropdown
            $cabangList = DB::select(
                "SELECT DISTINCT KODE, NA_TOKO from toko");
            

            return view("otransaksi_TAmbilOrderKode3.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'sub' => $SUB??'',
                'periode' => $periode,
                'cabangList' => $cabangList
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TAmbilOrderKode3 index: ' . $e->getMessage());
            return view("otransaksi_TAmbilOrderKode3.index")->with([
                'judul' => 'Transaksi Ambil Order Kode 3',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $per = $request->session()->get('periode');
            $selectedCbg = $request->input('cbg');
            $sub = $request->input('sub', '');

            if (empty($selectedCbg)) {
                return response()->json(['error' => 'Pilih cabang terlebih dahulu'], 400);
            }

            if (empty($sub)) {
                return response()->json(['error' => 'Sub barang harus diisi'], 400);
            }
            $kolomStok = "AK" . $per['bulan'];
            // Query untuk mengambil data order berdasarkan sub (adaptasi dari Delphi)
            $query = "
                SELECT CBG,SUB,KDBAR,kd_brg, na_brg, nilai,kodes, namas,Kemasan,ket_kem,TGL_MULAI,TGL_PSN AS tgl_order,kdlaku,'SP',type,if(date(TGL_MULAI)<>date(NOW()),'CC','C') AS OPERATOR,stok as qty, ket_uk, if(kdlaku='3' ,'T','G') AS EXP,KLKX,ORDR,PPN,if(date(TGL_MULAI)<>date(NOW()),NO_SP,'') AS PO_LALU 
                FROM ( 
                
                SELECT BB.*,if(sup.kodes is null,concat(BB.supp,'-?'),sup.kodes) as kodes,if(sup.kodes is null,'??',sup.namas) as namas,if(sup.kodes is null,0,sup.ORDR) AS ORDR,
                (1.5 * BB.lph)- ordr + (0.30*bb.lph)- BB.AKHIR AS YY,IF( (SELECT YY)<=dtr,dtr,(SELECT YY)) AS NILAI   

                FROM (
                select AA.*,
                IF(AA.KDLAKU='3' ,AKBRGDT,0) AS AKHIR 

                FROM (
                select brgdt.CBG,BRG.SUB,BRG.KDBAR,BRG.kd_brg, BRG.na_brg,brg.type, brg.SUPP, substr(trim(brg.KET_KEM),((LOCATE('/',trim(brg.ket_kem))+1))) AS kemasan,brgdt.NO_SP,if(brgdt.tgl_aw>brgdt.TGL_TRM,brgdt.tgl_aw,date(now())) as tgl_mulai, brgdt.TGL_PSN, ".$kolomStok. " as stok, ket_uk,
                if(BRGDt.AK00 is null,0,if(brgdt.AK00<0,0,brgdt.AK00)) AS AKBRGDT, 
                ceiling(1.5*brgdt.LPH*IF(brgdt.KLK<'U',ASCII(brgdt.KLK)-64,(((ASCII(brgdt.KLK)-64-20)*5)+20))) AS MINTx,
                if(brgdt.KDLAKU='3' ,if(BRGDt.srmin is null,0,brgdt.srmin),0) AS MINT,
                if(brgdt.KDLAKU='3' ,if(BRGDt.srmax is null,0,brgdt.srmax),0) AS MAXT,   
                brgdT.DTR,BRG.MO,brgdt.lph,BRGdt.KDLAKU,brg.ket_kem, 
                        IF(brgdt.KLK<'U',ASCII(brgdt.KLK)-64,(((ASCII(brgdt.KLK)-64-20)*5)+20))AS KLK,brgdt.KLK AS KLKX,if(trim(brg.PPN)='',0,brg.ppn) PPN from brg  ,BRGDT
                        where brg.KD_BRG=brgdT.KD_BRG AND 
                brgdT.CBG=? ANd brgdt.PSN='' AND brgdT.YER=YEAR(NOW()) AND BRGdt.KDLAKU ='3' and brgdt.lph>0 AND (TRIM(left(BRG.SUPP,1))<>'P' AND TRIM(left(BRG.SUPP,1))<>'Q') and brgdt.TD_OD=''   
                and brg.F_ADA='Y' and brg.F_PANEN='M' and brg.SUB=?
                ) AS AA   
                HAVING AKHIR <=if(CBG='TGZ',if(MINT>MINTX,MINT,MINTX),MINT)  
                ) AS BB LEFT JOIN SUP ON BB.SUPP=sup.KODES 
                ) AS CC  ORDER BY KD_BRG;


            ";

            $data = DB::select($query, [$selectedCbg,$sub]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $selectedCbg = $request->input('cbg');
            $sub = $request->input('sub', '');

            if (empty($selectedCbg)) {
                return response()->json(['error' => 'Pilih cabang terlebih dahulu'], 400);
            }

            if (empty($sub)) {
                return response()->json(['error' => 'Sub barang harus diisi'], 400);
            }

            DB::beginTransaction();

            // Panggil stored procedure spkode3 (adaptasi dari Delphi)
            // com.SQL.Text:='call spkode3(:cbg, :sub)';
            DB::statement("CALL spkode3(?, ?)", [$selectedCbg, $sub]);

            // Query untuk mendapatkan hasil dari spkode3
            $result = DB::select("
                SELECT * FROM spkode3 
                WHERE sub = ? 
                AND qty <> 0
            ", [$sub]);

            DB::commit();

            $message = "Proses Selesai!<br>";
            $message .= "Data berhasil diproses: " . count($result) . " item<br>";
            $message .= "Silakan dicek kembali.";

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => count($result),
                'data' => $result
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchBarang(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $cbg = $request->input('cbg', '');

            if (empty($search) || empty($cbg)) {
                return response()->json(['data' => []]);
            }

            $query = "
                SELECT 
                    LEFT(kd_brg, 3) as sub,
                    kd_brg,
                    na_brg
                FROM {$cbg}.brg
                WHERE LEFT(kd_brg, 1) = '3'
                AND (
                    kd_brg LIKE ? OR
                    na_brg LIKE ? OR
                    LEFT(kd_brg, 3) LIKE ?
                )
                GROUP BY sub
                LIMIT 20
            ";

            $searchParam = '%' . $search . '%';
            $data = DB::select($query, [$searchParam, $searchParam, $searchParam]);

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error in searchBarang: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function print(Request $request)
    {
        $file = 'orderKode3';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $per = $request->session()->get('periode');
        $selectedCbg = $request->input('cbg');
        $sub = $request->input('sub', '');

        if (empty($selectedCbg)) {
            return response()->json(['error' => 'Pilih cabang terlebih dahulu'], 400);
        }

        if (empty($sub)) {
            return response()->json(['error' => 'Sub barang harus diisi'], 400);
        }
        $kolomStok = "AK" . $per['bulan'];
        // Query untuk mengambil data order berdasarkan sub (adaptasi dari Delphi)
        $query = "
            SELECT CBG,SUB,KDBAR,kd_brg, na_brg, nilai,kodes, namas,Kemasan,ket_kem,TGL_MULAI,TGL_PSN AS tgl_order,kdlaku,'SP',type,if(date(TGL_MULAI)<>date(NOW()),'CC','C') AS OPERATOR,stok as qty, ket_uk, if(kdlaku='3' ,'T','G') AS EXP,KLKX,ORDR,PPN,if(date(TGL_MULAI)<>date(NOW()),NO_SP,'') AS PO_LALU 
            FROM ( 
            
            SELECT BB.*,if(sup.kodes is null,concat(BB.supp,'-?'),sup.kodes) as kodes,if(sup.kodes is null,'??',sup.namas) as namas,if(sup.kodes is null,0,sup.ORDR) AS ORDR,
            (1.5 * BB.lph)- ordr + (0.30*bb.lph)- BB.AKHIR AS YY,IF( (SELECT YY)<=dtr,dtr,(SELECT YY)) AS NILAI   

            FROM (
            select AA.*,
            IF(AA.KDLAKU='3' ,AKBRGDT,0) AS AKHIR 

            FROM (
            select brgdt.CBG,BRG.SUB,BRG.KDBAR,BRG.kd_brg, BRG.na_brg,brg.type, brg.SUPP, substr(trim(brg.KET_KEM),((LOCATE('/',trim(brg.ket_kem))+1))) AS kemasan,brgdt.NO_SP,if(brgdt.tgl_aw>brgdt.TGL_TRM,brgdt.tgl_aw,date(now())) as tgl_mulai, brgdt.TGL_PSN, ".$kolomStok. " as stok, ket_uk,
            if(BRGDt.AK00 is null,0,if(brgdt.AK00<0,0,brgdt.AK00)) AS AKBRGDT, 
            ceiling(1.5*brgdt.LPH*IF(brgdt.KLK<'U',ASCII(brgdt.KLK)-64,(((ASCII(brgdt.KLK)-64-20)*5)+20))) AS MINTx,
            if(brgdt.KDLAKU='3' ,if(BRGDt.srmin is null,0,brgdt.srmin),0) AS MINT,
            if(brgdt.KDLAKU='3' ,if(BRGDt.srmax is null,0,brgdt.srmax),0) AS MAXT,   
            brgdT.DTR,BRG.MO,brgdt.lph,BRGdt.KDLAKU,brg.ket_kem, 
                    IF(brgdt.KLK<'U',ASCII(brgdt.KLK)-64,(((ASCII(brgdt.KLK)-64-20)*5)+20))AS KLK,brgdt.KLK AS KLKX,if(trim(brg.PPN)='',0,brg.ppn) PPN from brg  ,BRGDT
                    where brg.KD_BRG=brgdT.KD_BRG AND 
            brgdT.CBG=? ANd brgdt.PSN='' AND brgdT.YER=YEAR(NOW()) AND BRGdt.KDLAKU ='3' and brgdt.lph>0 AND (TRIM(left(BRG.SUPP,1))<>'P' AND TRIM(left(BRG.SUPP,1))<>'Q') and brgdt.TD_OD=''   
            and brg.F_ADA='Y' and brg.F_PANEN='M' and brg.SUB=?
            ) AS AA   
            HAVING AKHIR <=if(CBG='TGZ',if(MINT>MINTX,MINT,MINTX),MINT)  
            ) AS BB LEFT JOIN SUP ON BB.SUPP=sup.KODES 
            ) AS CC  ORDER BY KD_BRG;


        ";

        $data = DB::select($query, [$selectedCbg,$sub]);
        foreach ($data as $key => $value) {
                $data[$key]->JUDUL = 'Laporan Order Kode 3 Cabang '.$selectedCbg;
                $data[$key]->TGL_NOW = now()->format('d/m/Y');
        }
        $PHPJasperXML->setData(array_map(function ($item) {
            return (array) $item;
        }, $data));
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}
