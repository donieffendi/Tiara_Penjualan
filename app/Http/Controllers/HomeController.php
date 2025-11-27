<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    
    
		
	public function hitungUlang()
    {
        $bulan    = session()->get('periode')['bulan'];
        $tahun    = session()->get('periode')['tahun'];
        
		DB::SELECT("Call hitungacno('$tahun');");
		DB::SELECT("Call hitungbrg('$tahun');");
		DB::SELECT("Call hitungcust('$tahun');");
		DB::SELECT("Call hitungsup('$tahun');");
	//	DB::SELECT("Call hitungbelijual('$tahun');");
        return view('dashboard')->with('status', 'Hitung Ulang selesai..');
    }

	public function akhirbulan()
    {

        $periode  = session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
		 
		DB::SELECT("Call akhirbulan ('$periode');");
        return view('dashboard')->with('status', 'Akhir Bulan selesai..');
    }

	public function akhirtahun()
    {

        $tahun    = session()->get('periode')['tahun'];;
		 
		DB::SELECT("Call akhirtahun('$tahun');");
        return view('dashboard')->with('status', 'Akhir Tahun selesai..');
    }
    
	public function kosong()
    {
	//DB::SELECT("call kosong;");
        //return view('dashboard')->with('status', 'Kosongi Data selesai..');
    }
    
}
