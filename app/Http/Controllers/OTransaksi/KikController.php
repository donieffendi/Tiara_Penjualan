<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use App\Http\Traits\Terbilang;

use App\Models\OTransaksi\Kik;
use App\Models\OTransaksi\Kikd;
use App\Models\Master\Wilayah;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;


class KikController extends Controller
{
	use Terbilang;
	
    public function index()
    {
        return view('otransaksi_kik.index');
    }

    // public function index_posting(Request $request)
    public function index_posting()
    {   
        // $wilayah = Wilayah::query()->get();
		// session()->put('filter_wilayah', '');

        // if(strtolower($request->JNS)=='1')
        // {
        //     $judul = "Tipe 1";
        // }
        // else if(strtolower($request->JNS)=='2')
        // {
        //     $judul = "Tipe 2";
        // }
        // return view('otransaksi_kik.post')->with(['judul' => $judul, 'jenis' => strtolower($request->JNS)]);
        return view('otransaksi_kik.post');
    }


	public function browsewilayah()
    {
		$wilayah = DB::table('wilayah')->select('*')->orderBy('WILAYAH', 'ASC')->get();
		return response()->json($wilayah);
	}

    public function getKik(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        if (!empty($request->wilayah1))
		{
			$filterwilayah = " and WILAYAH1 ='".$request->wilayah."' ";
		}

        $CBG = Auth::user()->CBG;
		
        $kik = DB::SELECT("SELECT * from orderkd
                    WHERE CBG = '$CBG'
                    ORDER BY NO_BUKTI ");
	  
       
        return Datatables::of($kik)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="assistant" || Auth::user()->divisi=="penjualan") 
                {
                    $btnEdit = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="kik/edit/' . $row->NO_ID . '" ';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="kik/delete/' . $row->NO_ID . '" ';

                    $btnPrivilege ='
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="kik/print/' . $row->NO_ID . '">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                    Print
                                </a> 									
                                <hr></hr>
                                <a class="dropdown-item btn btn-danger" ' . $btnDelete . '>
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
                            <a class="dropdown-item" href="kik/show/' . $row->NO_ID . '">
                            <i class="fas fa-eye"></i>
                                Lihat
                            </a>

                            ' . $btnPrivilege . '
                        </div>
                    </div>
                    ';

                return $actionBtn;
            })
            ->addColumn('cek', function ($row) {
                return
                    '
                    <input type="checkbox" name="cek[]" class="form-control cek" ' . (($row->POSTED == 1) ? "checked" : "") . '  value="' . $row->NO_ID . '" ' . (($row->POSTED == 1) ? "disabled" : "") . '></input>
                    ';
            })

            ->rawColumns(['action', 'cek', 'qtyt'])
            ->make(true);
    }

    public function posting(Request $request)
    {   
		
        if ($request->session()->has('posttimer'))
        {
            if ( (time() - $request->session()->get('posttimer')) <= 5)
            {
                session()->put('posttimer', time());
                return redirect('/kik/index-posting')->with('gagal', 'Terlalu cepat. Ulangi 5 detik lagi..');
            }
        }

        session()->put('posttimer', time());
        $CEK = $request->input('cek');
        // $STA = $request->input('STA');
 
 
        $hasil = "";

        if ($CEK) {
            foreach ($CEK as $key => $value) {
				
                    //$STA = $request->input('STA');
					
				$periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
				$bulan    = session()->get('periode')['bulan'];
				$tahun    = substr(session()->get('periode')['tahun'], -2);

				$query1 = DB::SELECT("SELECT row_id as no_id,kodecus AS KODEC ,'' as WILAYAH1,nama as NAMAC,'' as FLAG,
											no_chgb as NO_CHBG,bank,giro as TOTAL,tgljt as jtempo,bank_setor as BANK_SETOR,biaya,tgl_setor
									FROM lk_giro 
									where tgl_setor!='0000-00-00' and val_cet=0 and wilayah='LOKAL' and giro<>0 and NO_ID =" . $CEK[$key] . ";");
				
			}
        }
        else
        {
            $hasil = $hasil ."Tidak ada Slip Bank yang dipilih! ; ";
        }

        if($hasil!='')
        {
            return redirect('/kik/index-posting')->with('status', 'Proses cetak Slip Bank Request..')->with('gagal', $hasil);
        }
        else
        {
            return redirect('/kik/index-posting')->with('status', 'Cetak Slip Bank selesai..');
        }

    }

    public function create()
    {
        return view('otransaksi_surats.create');
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'NO_BUKTI'       => 'required',
                'TGL'      => 'required',
                'KODEC'       => 'required',
                'KD_BRG'      => 'required',
            ]
        );

        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);
        $query = DB::table('surats')->select(DB::raw("TRIM(NO_BUKTI) AS NO_BUKTI"))->where('PER', $periode)->where('FLAG', 'SJ')->where('GOL', 'Y')->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'SJY' . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = 'SJY' . $tahun . $bulan . '-0001';
        }

        // Insert Header
        $surats = Surats::create(
            [
                'NO_BUKTI'         => $no_bukti,
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'PER'              => $periode,
                'NO_SO'            => ($request['NO_SO'] == null) ? "" : $request['NO_SO'],
                'KODEC'            => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'            => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'FLAG'             => 'SJ',
                'GOL'               => 'Y',
                'ACNOA'            => '113101',
                'NACNOA'           => 'PIUTANG USAHA ',
                'ACNOB'            => '711102',
                'NACNOB'           => 'PEND. / BIAYA LAIN2',
                'TRUCK'            => ($request['TRUCK'] == null) ? "" : $request['TRUCK'],
                'SOPIR'            => ($request['SOPIR'] == null) ? "" : $request['SOPIR'],
                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'KD_BRG'           => ($request['KD_BRG'] == null) ? "" : $request['KD_BRG'],
                'NA_BRG'           => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'KODET'            => ($request['KODET'] == null) ? "" : $request['KODET'],
                'NAMAT'            => ($request['NAMAT'] == null) ? "" : $request['NAMAT'],
                'KG1'               => (float) str_replace(',', '', $request['KG']),
                'KG'                => (float) str_replace(',', '', $request['KG']),
                'SISA'                => (float) str_replace(',', '', $request['SISA']),
                'HARGA'             => (float) str_replace(',', '', $request['HARGA']),
                'TOTAL'             => (float) str_replace(',', '', $request['TOTAL']),
                'SISA'              => (float) str_replace(',', '', $request['TOTAL']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now()

            ]
        );
        
        DB::SELECT("CALL suratsins('". $no_bukti ."') ");

        return redirect('/surats')->with('status', 'Data baru '.$no_bukti.' berhasil ditambahkan');
    }

    public function show(Surats $surats)
    {
        $no_bukti = $surats->NO_BUKTI;
        $data = [
            'header'        => $surats
        ];
        return view('otransaksi_surats.show', $data);
    }

    public function edit(Surats $surats)
    {

        $no_bukti = $surats->NO_BUKTI;
        $data = [
            'header'        => $surats
        ];

        return view('otransaksi_surats.edit', $data);
    }

    public function update(Request $request, Surats $surats)
    {
        $this->validate(
            $request,
            [
                'TGL'      => 'required',
                //'KODEC'       => 'required',
                'KD_BRG'      => 'required',
            ]
        );

        DB::SELECT("CALL suratsdel('". $request['NO_BUKTI'] ."') ");

        $surats->update(
            [
                'TGL'              => date('Y-m-d', strtotime($request['TGL'])),
                'NO_SO'            => ($request['NO_SO'] == null) ? "" : $request['NO_SO'],
                'KODEC'            => ($request['KODEC'] == null) ? "" : $request['KODEC'],
                'NAMAC'            => ($request['NAMAC'] == null) ? "" : $request['NAMAC'],
                'ALAMAT'           => ($request['ALAMAT'] == null) ? "" : $request['ALAMAT'],
                'KOTA'             => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'NOTES'            => ($request['NOTES'] == null) ? "" : $request['NOTES'],
                'KD_BRG'           => ($request['KD_BRG'] == null) ? "" : $request['KD_BRG'],
                'NA_BRG'           => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'KODET'            => ($request['KODET'] == null) ? "" : $request['KODET'],
                'NAMAT'            => ($request['NAMAT'] == null) ? "" : $request['NAMAT'],
                'TRUCK'            => ($request['TRUCK'] == null) ? "" : $request['TRUCK'],
                'SOPIR'            => ($request['SOPIR'] == null) ? "" : $request['SOPIR'],
                'KG1'              => (float) str_replace(',', '', $request['KG']),
                'KG'               => (float) str_replace(',', '', $request['KG']),
                'SISA'               => (float) str_replace(',', '', $request['SISA']),
                'HARGA'            => (float) str_replace(',', '', $request['HARGA']),
                'TOTAL'            => (float) str_replace(',', '', $request['TOTAL']),
                'SISA'             => (float) str_replace(',', '', $request['TOTAL']),
                'USRNM'            => Auth::user()->username,
                'TG_SMP'           => Carbon::now()
            ]
        );

        DB::SELECT("CALL suratsins('". $request['NO_BUKTI'] ."') ");

        return redirect('/surats')->with('status', 'Data '.$surats->NO_BUKTI.' berhasil diedit');
    }

    public function destroy(Surats $surats)
    {
        DB::SELECT("CALL suratsdel('". $surats->NO_BUKTI ."') ");
        Surats::find($surats->NO_ID)->delete();

        return redirect('/surats')->with('status', 'Data '.$surats->NO_BUKTI.' berhasil dihapus');
    }


    public function cetak(Kik $kik, Request $request)
    {

		$jenisx = $kik->JENIS;
		$no_bukti = $kik->NO_CHBG;
		$file ='';
		 
		if ( $jenisx == 'DANAMON' ) {
            $file     = 'Transaksi_CekBG_Cek_Danamon';
        } else if ( $jenisx == 'BRI' ) {
            $file     = 'Transaksi_CekBG_Cek_Bri';
        } else if ( $jenisx == 'BCA-SETORAN' ) {
            $file     = 'Transaksi_CekBG_Cek_Bca';
        } else if ( $jenisx == 'BCA-SETORAN-KLIRING' ) {
            $file     = 'Transaksi_CekBG_Slip_Storan';
        } else if ( $jenisx == 'DANAMON2' ) {
            $file     = 'Transaksi_CekBG_Cek_Danamon';
        }else if ( $jenisx == 'UOB' ) {
            $file     = 'Transaksi_CekBG_Cek_UOB';
        }else if ( $jenisx == 'DANAMON-1B' ) {
            $file     = 'Transaksi_CekBG_Cek_Bca';
        }else if ( $jenisx == 'BRI-NEW' ) {
            $file     = 'Transaksi_CekBG_Cek_Bri';
        }


        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

		$terbilangx = ucwords($this->pembilang($kik->TOTAL));
		$PHPJasperXML->arrayParameter = array("TERBILANG" => (string) $terbilangx);
				
		$query = DB::SELECT(

		"SELECT *
			FROM pms_piu
			WHERE NO_CHBG='" . $no_bukti . "' "
		);

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, array(

                'JTEMPO' => $query[$key]->JTEMPO,
                'TGL_SETOR' => $query[$key]->TGL,
                'NO_GIRO' => $query[$key]->NO_CHBG,
                'NILAI' => $query[$key]->TOTAL,
                'BIAYA' => $query[$key]->BIAYA,
                'BANK' => $query[$key]->BANK,
                'KOTA_SETOR' => $query[$key]->KOTA_SETOR,
                'BANK_SETOR' => $query[$key]->BANK_SETOR,
                'JUMLAH_TERBILANG' => $terbilangx,
				//'JUMLAH_TERBILANG' => ucwords($this->pembilang($query[$key]->TJUMLAH)),
            ));
		}     
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}
