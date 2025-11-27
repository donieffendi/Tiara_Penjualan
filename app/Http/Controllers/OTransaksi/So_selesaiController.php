<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use App\Http\Traits\Terbilang;

use App\Models\OTransaksi\So_selesai;
use App\Models\OTransaksi\So_selesaid;
use App\Models\Master\Wilayah;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;


class So_selesaiController extends Controller
{
	use Terbilang;
	
    public function index()
    {
        return view('otransaksi_so_selesai.index');
    }

    // public function index_posting(Request $request)
    public function index_posting()
    {   
        return view('otransaksi_so_selesai.post');
    }


	public function browsewilayah()
    {
		$wilayah = DB::table('wilayah')->select('*')->orderBy('WILAYAH', 'ASC')->get();
		return response()->json($wilayah);
	}

    public function getSo_selesai(Request $request)
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

        $so_selesai = DB::SELECT("SELECT a.no_bukti AS NO_BUKTI, a.tgl AS TGL, a.KODEC AS KODEC, a.NAMAC AS NAMAC, a.POSTED, 
                                        b.kd_brg AS KD_BRG, b.na_brg AS NA_BRG,
                                        b.qty AS QTY, b.kirim AS KIRIM, b.sisa AS SISA, b.sls AS SLS, A.NO_ID AS NO_ID 
                                    from so a, sod b  
                    where a.no_bukti = b.no_bukti and b.sls = 0 
                    ORDER BY NO_BUKTI ");
	  
       
        return Datatables::of($so_selesai)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="assistant" || Auth::user()->divisi=="penjualan") 
                {
                    $btnEdit = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="so_selesai/edit/' . $row->NO_ID . '" ';
                    $btnDelete = ($row->POSTED == 1) ? ' onclick= "alert(\'Transaksi ' . $row->NO_BUKTI . ' sudah diposting!\')" href="#" ' : ' href="so_selesai/delete/' . $row->NO_ID . '" ';

                    $btnPrivilege ='
                                <a class="dropdown-item" ' . $btnEdit . '>
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a class="dropdown-item btn btn-danger" href="so_selesai/print/' . $row->NO_ID . '">
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
                            <a class="dropdown-item" href="so_selesai/show/' . $row->NO_ID . '">
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
                return redirect('/so_selesai/index-posting')->with('gagal', 'Terlalu cepat. Ulangi 5 detik lagi..');
            }
        }

        session()->put('posttimer', time());
        $CEK = $request->input('cek');
 
 
        $hasil = "";

        if ($CEK) {
            foreach ($CEK as $key => $value) {
                
                DB::SELECT("UPDATE SOD SET SLS =1 WHERE NO_ID =" . $CEK[$key] . ";");
			   
			}
        }
        else
        {
            $hasil = $hasil ."Tidak ada SO yang dipilih! ; ";
        }

        if($hasil!='')
        {
            return redirect('/cust')->with('status', 'Proses Posting SO Request..')->with('gagal', $hasil);
        }
        else
        {
            return redirect('/cust')->with('status', 'Posting SO selesai..');
        }

    }

    
    public function store(Request $request)
    {
        
        return redirect('/so_selesai')->with('status', 'Data baru '.$no_bukti.' berhasil ditambahkan');
    }

    public function show(So_selesai $so_selesai)
    {
        
        return view('otransaksi_so_selesai.show', $data);
    }

    public function edit(So_selesai $so_selesai)
    {

        return view('otransaksi_so_selesai.edit', $data);
    }

    public function update(Request $request, So_selesai $so_selesai)
    {
        return redirect('/so_selesai')->with('status', 'Data '.$so_selesai->NO_BUKTI.' berhasil dihapus');
    }


    public function cetak(So_selesai $so_selesai, Request $request)
    {

    }
}
