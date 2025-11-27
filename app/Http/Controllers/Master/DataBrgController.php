<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Master\Foodfc;
use App\Models\Master\Maskfc;
use DataTables;
use Auth;
use DB;
use Carbon\Carbon;

class DataBrgController extends Controller
{

    public function index() {
        return view('master_data_barang.index');
    }

    public function browse(Request $request)
    {
        $dbrg = DB::SELECT("SELECT KODES, NAMAS FROM supfc ORDER BY KODES");
        return response()->json($dbrg);
    }

    public function getDataBrg(Request $request)
    {
    
        $dbrg = DB::SELECT("SELECT * FROM brgfc ORDER BY KD_BRG");
		
        return Datatables::of($dbrg)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
					if (Auth::user()->divisi=="programmer" || Auth::user()->divisi=="owner" || Auth::user()->divisi=="sales")
					{   
                        // url untuk delete di index
                        $url = "'".url("dbrg/delete/" . $row->NO_ID )."'";
                        // batas
                        
                        $btnDelete = ' onclick="deleteRow('.$url.')"';
    
                        $btnPrivilege =
                            '
                                    <a class="dropdown-item" href="dbrg/edit/?idx=' . $row->NO_ID . '&tipx=edit";
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
                                <a hidden class="dropdown-item" href="dbrg/show/' . $row->NO_ID . '">
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

    // public function store(Request $request)
    // {


    //     $this->validate(
    //         $request,
    //         // GANTI 9

    //         [
    //             'NA_BRG'       => 'required'
    //         ]

    //     );

    //     $sub   = $request->SUB;
    //     $kdbar = $request->KDBAR;

    //     // KD_BRG hasil gabungan
    //     $kd_brg = $sub . $kdbar;

    //     $CBG = Auth::user()->CBG;
 
    //     // Insert Header

    //     // ganti 10

    //     $dbrg = Foodfc::create(
    //         [   
    //             'KD_BRG'         => ($kd_brg == null) ? "" : $kd_brg,
    //             'SUB'            => ($request['SUB'] == null) ? "" : $request['SUB'],
    //             'KDBAR'          => ($request['KDBAR'] == null) ? "" : $request['KDBAR'],
    //             'NA_BRG'         => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
    //             'SUPP'           => ($request['SUPP'] == null) ? "" : $request['SUPP'],
    //             'NAMAS'          => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
    //             'KELOMPOK'       => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
    //             'BARCODE'        => ($request['BARCODE'] == null) ? "" : $request['BARCODE'],
    //             'HB'             => (float) str_replace(',', '', $request['HB']),
    //             'STAND'          => ($request['STAND'] == null) ? "" : $request['STAND'],
    //             'LOC_TG'         => ($request['LOC_TG'] == null) ? "" : $request['LOC_TG'],
    //             'HJ'             => (float) str_replace(',', '', $request['HJ']),
    //             'TYPE'           => ($request['TYPE'] == null) ? "" : $request['TYPE'],
    //             'TKP'            => ($request['TKP'] == null) ? "" : $request['TKP'],
    //             'FLAGSTOK'       => ($request['FLAGSTOK'] == null) ? "" : $request['FLAGSTOK'],
    //             'MARGIN'         => (float) str_replace(',', '', $request['MARGIN']),
    //             'DIS'            => (float) str_replace(',', '', $request['DIS']),

    //             'USRNM'          => Auth::user()->username,
    //             'TG_SMP'         => Carbon::now(),

    //         ]
    //     );

    //     //  ganti 11

	//     $kd_brgx = $request['KD_BRG'];
		
	// 	$dbrg = Foodfc::where('KD_BRG', $kd_brgx )->first();
        
	// 	return redirect('/dbrg')->with('statusInsert', 'Data baru berhasil ditambahkan');	
		
	// }

    public function store(Request $request)
    {   
        $this->validate($request, [
            'NA_BRG' => 'required'
        ]);

        DB::beginTransaction();
        try {

            // 1. Buat KD_BRG
            $sub   = $request->SUB;
            $kdbar = $request->KDBAR;
            $kd_brg = $sub . $kdbar;

            // 2. Insert ke Foodfc
            $food = Foodfc::create([
                'KD_BRG'   => $kd_brg,
                'SUB'      => ($request['SUB'] == null) ? "" : $request['SUB'],
                'KDBAR'    => ($request['KDBAR'] == null) ? "" : $request['KDBAR'],
                'NA_BRG'   => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'SUPP'     => ($request['SUPP'] == null) ? "" : $request['SUPP'],
                'NAMAS'    => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'KELOMPOK' => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'BARCODE'  => ($request['BARCODE'] == null) ? "" : $request['BARCODE'],
                'HB'       => (float) str_replace(',', '', $request['HB']),
                'STAND'    => ($request['STAND'] == null) ? "" : $request['STAND'],
                'LOK_TG'   => ($request['LOC_TG'] == null) ? "" : $request['LOC_TG'],
                'HJ'       => (float) str_replace(',', '', $request['HJ']),
                'TYPE'     => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'TKP'      => ($request['TKP'] == null) ? "" : $request['TKP'],
                'FLAGSTOK' => ($request['FLAGSTOK'] == null) ? "" : $request['FLAGSTOK'],
                'MARGIN'   => (float) str_replace(',', '', $request['MARGIN']),
                'DIS'      => (float) str_replace(',', '', $request['DIS']),

                'USRNM'    => Auth::user()->username,
                'TG_SMP'   => Carbon::now(),
            ]);

            // 3. Insert ke maskFC — mirip delphi kamu
            DB::table('maskfc')->insert([
                'SUB'       => ($request['SUB'] == null) ? "" : $request['SUB'],
                'SUB2'      => ($request['SUB'] == null) ? "" : $request['SUB'],
                'KDBAR'     => ($request['KDBAR'] == null) ? "" : $request['KDBAR'],
                'KD_BRG'    => $kd_brg,
                'NMBAR'     => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'HJ'        => (float) str_replace(',', '', $request['HJ']),
                'LOC'       => ($request['LOK_TG'] == null) ? "" : $request['LOK_TG'],
                'NOITEM'    => ($request['KDBAR'] == null) ? "" : $request['KDBAR'],
                'HB'        => (float) str_replace(',', '', $request['HB']),
                'KELOMPOK'  => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'BARCODE'   => ($request['BARCODE'] == null) ? "" : $request['BARCODE'],
                'DIS'       => (float) str_replace(',', '', $request['DIS']),
                'MARGIN'    => (float) str_replace(',', '', $request['MARGIN']),
                'STAND'     => ($request['STAND'] == null) ? "" : $request['STAND'],
                'TKP'       => ($request['TKP'] == null) ? "" : $request['TKP'],
                'FLAGSTOK'  => ($request['FLAGSTOK'] == null) ? "" : $request['FLAGSTOK'],
                'NAMAS'     => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'KODES'     => ($request['SUPP'] == null) ? "" : $request['SUPP'],
                'HJGO'      => (float) str_replace(',', '', $request['HJGO']),
                'MARGIN_GO' => (float) str_replace(',', '', $request['MARGIN_GO']),
            ]);

            DB::commit();

            return redirect('/dbrg')->with('statusInsert', 'Data baru berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    

    // ganti 15

    public function edit(Request $request, Foodfc $dbrg)
    {

        // ganti 16
        $tipx = $request->tipx;

        $idx = $request->idx;

        $cbg = Auth::user()->CBG;

        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';

        }

        if ($tipx == 'search') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brgfc
		                 where KD_BRG = '$kodex'
		                 ORDER BY KD_BRG ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brgfc
		                 ORDER BY KD_BRG ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brgfc
		             where KD_BRG <
					 '$kodex' ORDER BY KD_BRG  DESC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }
        if ($tipx == 'next') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KD_BRG  from brgfc
		             where KD_BRG >
					 '$kodex' ORDER BY KD_BRG ASC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, KD_BRG from brgfc
		              ORDER BY KD_BRG DESC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'undo' || $tipx == 'search') {

            $tipx = 'edit';

        }

        if ($idx != 0) {
            $dbrg = Foodfc::where('NO_ID', $idx )->first();
            $mask = DB::table('maskfc')
            ->where('KD_BRG', $dbrg->KD_BRG)
            ->first();
        } else {
            $dbrg = new Foodfc;
            $mask = new Maskfc;
        }

        $data = [
            'header' => $dbrg,
            'mask'   => $mask
        ];

        return view('master_data_barang.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request, Foodfc $dbrg)
    {
        DB::beginTransaction();
        try {

            $this->validate($request, [
                'NA_BRG' => 'required',
            ]);

            $kd_brg = $request->KD_BRG;   // Primary key untuk update kedua table
            $CBG = Auth::user()->CBG;

            // ============================
            // 1. UPDATE Foodfc
            // ============================
            $dbrg->update([
                'NA_BRG'   => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'SUPP'     => ($request['SUPP'] == null) ? "" : $request['SUPP'],
                'NAMAS'    => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'KELOMPOK' => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'BARCODE'  => ($request['BARCODE'] == null) ? "" : $request['BARCODE'],
                'HB'       => (float) str_replace(',', '', $request['HB']),
                'STAND'    => ($request['STAND'] == null) ? "" : $request['STAND'],
                'LOK_TG'   => ($request['LOC_TG'] == null) ? "" : $request['LOC_TG'],
                'HJ'       => (float) str_replace(',', '', $request['HJ']),
                'TYPE'     => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'TKP'      => ($request['TKP'] == null) ? "" : $request['TKP'],
                'FLAGSTOK' => ($request['FLAGSTOK'] == null) ? "" : $request['FLAGSTOK'],
                'MARGIN'   => (float) str_replace(',', '', $request['MARGIN']),
                'DIS'      => (float) str_replace(',', '', $request['DIS']),

                'USRNM'    => Auth::user()->username,
                'TG_SMP'   => Carbon::now(),
            ]);

            // ============================
            // 2. UPDATE maskfc
            // ============================
            DB::table('maskfc')
                ->where('KD_BRG', $kd_brg)
                ->update([
                'NMBAR'     => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
                'HJ'        => (float) str_replace(',', '', $request['HJ']),
                'LOC'       => ($request['LOK_TG'] == null) ? "" : $request['LOK_TG'],
                'NOITEM'    => ($request['KDBAR'] == null) ? "" : $request['KDBAR'],
                'HB'        => (float) str_replace(',', '', $request['HB']),
                'KELOMPOK'  => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'BARCODE'   => ($request['BARCODE'] == null) ? "" : $request['BARCODE'],
                'DIS'       => (float) str_replace(',', '', $request['DIS']),
                'MARGIN'    => (float) str_replace(',', '', $request['MARGIN']),
                'STAND'     => ($request['STAND'] == null) ? "" : $request['STAND'],
                'TKP'       => ($request['TKP'] == null) ? "" : $request['TKP'],
                'FLAGSTOK'  => ($request['FLAGSTOK'] == null) ? "" : $request['FLAGSTOK'],
                'NAMAS'     => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'KODES'     => ($request['SUPP'] == null) ? "" : $request['SUPP'],
                'HJGO'      => (float) str_replace(',', '', $request['HJGO']),
                'MARGIN_GO' => (float) str_replace(',', '', $request['MARGIN_GO']),
            ]);

            DB::commit();
            return redirect('/dbrg')->with('status', 'Data berhasil diupdate');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // public function update(Request $request, Foodfc $dbrg)
    // {

    //     $this->validate(
    //         $request,
    //         [

    //             // ganti 19

    //             'KD_BRG'       => 'required',
    //             'NA_BRG'       => 'required'
    //         ]
    //     );

    //     // ganti 20
		
    //     $CBG = Auth::user()->CBG;
 
    //     $tipx = 'edit';
	// 	$idx = $request->idx;

    //     $dbrg->update(
    //         [

    //             'NA_BRG'         => ($request['NA_BRG'] == null) ? "" : $request['NA_BRG'],
    //             'TYPE'           => ($request['TYPE'] == null) ? "" : $request['TYPE'],
    //             'SATUAN'         => ($request['SATUAN'] == null) ? "" : $request['SATUAN'],
    //             'KET_UK'         => ($request['KET_UK'] == null) ? "" : $request['KET_UK'],
    //             'KET_KEM'        => ($request['KET_KEM'] == null) ? "" : $request['KET_KEM'],
    //             'DIAMETER'       => (float) str_replace(',', '', $request['DIAMETER']),
    //             'TEBAL'          => (float) str_replace(',', '', $request['TEBAL']),
    //             'PANJANG'        => (float) str_replace(',', '', $request['PANJANG']),
    //             'KG'             => (float) str_replace(',', '', $request['KG']),
    //             'SMIN'           => (float) str_replace(',', '', $request['SMIN']),
    //             'SMAX'           => (float) str_replace(',', '', $request['SMAX']),
    //             'HB'             => (float) str_replace(',', '', $request['HB']),
    //             'HS'             => (float) str_replace(',', '', $request['HS']),
    //             'HB_NAIK'        => (float) str_replace(',', '', $request['HB_NAIK']),
    //             'H_MINC'         => (float) str_replace(',', '', $request['H_MINC']),
    //             'LEBAR'          => (float) str_replace(',', '', $request['LEBAR']),
    //             'PN'             => ($request['PN'] == null) ? "" : $request['PN'],
    //             'GROUP'          => ($request['GROUP'] == null) ? "" : $request['GROUP'],
    //             'SUB_GROUP'      => ($request['SUB_GROUP'] == null) ? "" : $request['SUB_GROUP'],		 
    //             'SUPP'           => ($request['KODES'] == null) ? "" : $request['KODES'],		 
    //             'KLK'            => ($request['KLK'] == null) ? "" : $request['KLK'],		 
	// 			'USRNM'          => Auth::user()->username,
    //             'TG_SMP'         => Carbon::now(),
    //             'BL_PER'         => date('Y-m-d', strtotime($request['BL_PER'])),
    //             'BL_AKR'         => date('Y-m-d', strtotime($request['BL_AKR'])),
    //             'JL_AKR'         => date('Y-m-d', strtotime($request['JL_AKR'])),
    //             'SUPP'           => ($request['KODES'] == null) ? "" : $request['KODES'],
    //             'KLK'            => ($request['KLK'] == null) ? "" : $request['KLK'],
    //             'LOKASI'         => ($request['LOKASI'] == null) ? "" : $request['LOKASI'],
    //             'KELOMPOK'       => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
    //             'UP_HB'          => ($request['UP_HB'] == null) ? "" : $request['UP_HB'],
    //             'ALASAN'         => ($request['ALASAN'] == null) ? "" : $request['ALASAN'],
    //             'TD_OD'          => ($request['TD_OD'] == null) ? "" : $request['TD_OD'],
    //             'HJUAL'          => (float) str_replace(',', '', $request['HJUAL']),
    //             'MARGIN'         => (float) str_replace(',', '', $request['MARGIN']),
    //             'HJ2'            => (float) str_replace(',', '', $request['HJ2']),
    //             'CBG'            => $CBG
    //         ]
    //     );

    //     ////////////////////////////////////////////////////

    //     // $brg = Brg::where('KD_BRG', $kd_brgx )->first();

    //     //  ganti 21

    //     //return redirect('/brg/edit/?idx=' . $brg->NO_ID . '&tipx=edit');
    //     // return redirect('/brg/edit/?idx=' . $Brg->NO_ID . '&tipx=edit');	
	// 	return redirect('/dbrg')->with('status', 'Data berhasil diupdate');
		
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request , Foodfc $dbrg)
    {

        // ganti 23
        $deleteDbrg = Foodfc::find($dbrg->NO_ID);

        // ganti 24

        $deleteDbrg->delete();

        // ganti 
        return redirect('/dbrg')->with('status', 'Data berhasil dihapus');
    }

    public function cetak(Request $request)
    {
        $search = $request->input('search', ''); // ambil parameter search dari URL

        $file = 'FoodCentre';
        $PHPJasperXML = new \PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path('app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // tambahkan kondisi pencarian jika ada input search
        $where = '';
        if (!empty($search)) {
            $where = "WHERE (KD_BRG LIKE '%$search%' OR NA_BRG LIKE '%$search%')";
        }

        $query = DB::SELECT("
            SELECT KD_BRG, NA_BRG, KET_UK, KET_KEM
            FROM brgfc
            $where
            ORDER BY KD_BRG
        ");

        $data = [];

        foreach ($query as $value) {
            $data[] = [
                'KD_BRG'   => $value->KD_BRG,
                'NA_BRG'   => $value->NA_BRG,
                'KET_UK'   => $value->KET_UK,
                'KET_KEM' => $value->KET_KEM,
            ];
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I"); // tampil langsung di browser
    }
}
