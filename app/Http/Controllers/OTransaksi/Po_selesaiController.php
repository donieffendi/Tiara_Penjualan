<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use App\Http\Traits\Terbilang;

use App\Models\OTransaksi\Po_selesai;
use App\Models\OTransaksi\Po_selesaid;
use App\Models\Master\Wilayah;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;


class Po_selesaiController extends Controller
{
	use Terbilang;
	
    public function index()
    {
        return view('otransaksi_po_selesai.index');
    }

    // public function index_posting(Request $request)
    public function index_posting()
    {   
        return view('otransaksi_po_selesai.post');
    }


	public function browsewilayah()
    {
		$wilayah = DB::table('wilayah')->select('*')->orderBy('WILAYAH', 'ASC')->get();
		return response()->json($wilayah);
	}

    public function getPo_selesai(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }
		
		$po_selesai = DB::SELECT("SELECT a.no_bukti AS NO_BUKTI, a.tgl AS TGL, a.kodes AS KODES, a.namas AS NAMAS, a.POSTED, 
                                    b.kd_brg AS KD_BRG, b.na_brg AS NA_BRG,
                                    b.qty AS QTY, b.kirim AS KIRIM, b.sisa AS SISA, b.sls AS SLS, B.NO_ID AS NO_ID from po a, pod b  
                    where a.no_bukti = b.no_bukti and b.sls = 0 
                    ORDER BY NO_BUKTI ");
	  
       
        return Datatables::of($po_selesai)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="assistant" || Auth::user()->divisi=="penjualan") 
                {
                    $btnEdit = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="po_selesai/edit/' . $row->NO_ID . '" ';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="po_selesai/delete/' . $row->NO_ID . '" ';

                    $btnPrivilege ='
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="po_selesai/print/' . $row->NO_ID . '">
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
                            <a class="dropdown-item" href="po_selesai/show/' . $row->NO_ID . '">
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
                    <input type="checkbox" name="cek[]" class="form-control cek" ' . (($row->SLS == 1) ? "checked" : "") . '  value="' . $row->NO_ID . '" ' . (($row->SLS == 1) ? "disabled" : "") . '></input>
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
                return redirect('/po_selesai/index-posting')->with('gagal', 'Terlalu cepat. Ulangi 5 detik lagi..');
            }
        }

        session()->put('posttimer', time());
        $CEK = $request->input('cek');
 
 
        $hasil = "";

        if ($CEK) {
            foreach ($CEK as $key => $value) {

                    DB::SELECT("UPDATE POD SET SLS =1 WHERE NO_ID =" . $CEK[$key] . ";");
   
			}
        }
        else
        {
            $hasil = $hasil ."Tidak ada PO yang dipilih! ; ";
        }

        if($hasil!='')
        {
            return redirect('/sup')->with('status', 'Proses Po Selesai ..')->with('gagal', $hasil);
        }
        else
        {
            return redirect('/sup')->with('status', 'Proses PO selesai berhasil..');
        }

    }

    
    public function store(Request $request)
    {
        
        return redirect('/po_selesai')->with('status', 'Data baru '.$no_bukti.' berhasil ditambahkan');
    }

    public function show(Po_selesai $po_selesai)
    {
        
        return view('otransaksi_po_selesai.show', $data);
    }

    public function edit(Po_selesai $po_selesai)
    {

        return view('otransaksi_po_selesai.edit', $data);
    }

    public function update(Request $request, Po_selesai $po_selesai)
    {
        return redirect('/po_selesai')->with('status', 'Data '.$po_selesai->NO_BUKTI.' berhasil dihapus');
    }


    public function cetak(Po_selesai $po_selesai, Request $request)
    {

    }
}
