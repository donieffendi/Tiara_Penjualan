<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\Brg;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DB;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class BrgController extends Controller
{
    public function index()
    {
        return view('master_barang.index');
    }

    public function getBrg(Request $request)
    {
        $cbg = Auth::user()->CBG;

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }
        $bulan = substr($periode, 0, 2);
        $tahun = substr($periode, 3, 4);
        
        $brg = DB::table('brgdt as a')
            ->join('brg as b', 'a.kd_brg', '=', 'b.kd_brg')
            ->leftJoin(DB::raw("(SELECT sup.kodes, sup.namas AS nama, sup.kota AS kt, sup.almt_k AS alamat FROM sup) AS ole"), 'b.supp', '=', 'ole.kodes')
            ->select(
                'b.sub',
                'b.kelompok',
                DB::raw("LEFT(b.kd_brg,3) AS subnd"),
                DB::raw("RIGHT(b.kd_brg,4) AS kdbar"),
                'b.kd_brg',
                'b.na_brg',
                'b.item_sup',
                'b.barcode',
                'b.type',
                'b.ket_kem',
                'b.ket_uk',
                'b.supp',
                'ole.nama AS nsup',
                'ole.alamat',
                'ole.kt AS kota',
                'b.mo',
                'b.moo',
                'b.retur',
                'b.sp_l',
                'b.sp_lf',
                'b.KK',
                'b.ppn',
                'a.lph',
                'a.dtr',
                'b.margin',
                'a.klk',
                'a.kdlaku',
                'b.dc',
                'b.merk',
                'ole.nama',
                'b.NO_ID'
            )
            ->where('a.cbg', $cbg)
            ->where('a.yer', $tahun);

        if ($request->filled('sub')) {
            $brg->whereRaw("LEFT(b.kd_brg,3) = ?", [$request->sub]);
        }

        return DataTables::of($brg)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                // Hanya show button untuk semua user
                return '
            <div class="dropdown show" style="text-align: center">
                <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" data-toggle="dropdown">
                    <i class="fas fa-bars"></i>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="brg/show?idx=' . $row->NO_ID . '">
                        <i class="fas fa-eye"></i> View
                    </a>
                </div>
            </div>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    // AJAX: Lookup Supplier
    public function lookupSupplier(Request $request)
    {
        try {
            $kodes = $request->kodes;
            $sup = DB::table('sup')
                ->where('kodes', $kodes)
                ->first();

            if ($sup) {
                return response()->json([
                    'success' => true,
                    'data' => $sup
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Supplier tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // AJAX: Lookup Sub/Kelompok
    public function lookupSub(Request $request)
    {
        try {
            $sub = $request->sub;
            $aotprice = DB::table('aotprice')
                ->where('sub', $sub)
                ->first();

            if ($aotprice) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'sub' => $aotprice->SUB,
                        'kelompok' => $aotprice->KELOMPOK,
                        'persen' => $aotprice->PERSEN ?? 0
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Sub/Kelompok tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // AJAX: Auto-generate kode barang berikutnya
    public function getNextKdBar(Request $request)
    {
        try {
            $subnd = $request->subnd;
            $result = DB::selectOne("
                SELECT LEFT(kdbar,3) AS subnd,
                       RIGHT(TRIM(MAX(kdbar)),4) AS kdbarnd
                FROM brg
                WHERE LENGTH(kdbar) > 4
                  AND LEFT(TRIM(kdbar),3) = ?
            ", [$subnd]);

            $kdbar = '0001';
            if ($result && $result->kdbarnd) {
                $kdbar = str_pad((int)$result->kdbarnd + 1, 4, '0', STR_PAD_LEFT);
            }

            return response()->json([
                'success' => true,
                'kdbar' => $kdbar
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // AJAX: Load data cabang untuk DataTable
    public function getBrgDtCabang(Request $request)
    {
        try {
            $kd_brg = $request->kd_brg;
            $cbg = Auth::user()->CBG;

            if ($kd_brg) {
                // Edit mode: load existing data
                $data = DB::select("
                    SELECT brgdt.cbg, brgdt.kdlaku, brgdt.srmax, brgdt.srmin,
                           brgdt.smax, brgdt.smin, brgdt.hj, brgdt.hb,
                           brgdt.klk, brg.margin, brgdt.dtr, brgdt.lph
                    FROM brgdt, brg
                    WHERE brgdt.kd_brg = brg.kd_brg
                      AND brgdt.kd_brg = ?
                      AND brgdt.yer = YEAR(NOW())
                ", [$kd_brg]);
            } else {
                // New mode: load all cabang with empty values
                $data = DB::select("
                    SELECT kode AS cbg, '' AS kdlaku, 0 AS srmax, 0 AS srmin,
                           0 AS smax, 0 AS smin, 0 AS hj, 0 AS hb,
                           '' AS klk, 0 AS margin, 0 AS dtr, 0 AS lph
                    FROM toko
                    WHERE STA IN ('MA','CB','DC')
                ");
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'subnd' => 'required',
                'kdbar' => 'required',
                'na_brg' => 'required',
                'sub' => 'required'
            ]);

            $kd_brg = $request->subnd . $request->kdbar;
            $cbg = Auth::user()->CBG;
            $username = Auth::user()->username;
            $mode = $request->mode; // 'add' or 'edit'

            DB::beginTransaction();

            if ($mode == 'edit') {
                // UPDATE existing record
                $no_id = $request->no_id;

                DB::table('brg')
                    ->where('NO_ID', $no_id)
                    ->update([
                        'na_brg' => $request->na_brg,
                        'item_sup' => $request->item_sup ?? '',
                        'barcode' => $request->barcode ?? '',
                        'type' => $request->type ?? '',
                        'ket_kem' => $request->ket_kem ?? '',
                        'ket_uk' => $request->ket_uk ?? '',
                        'supp' => $request->supp ?? '',
                        'mo' => $request->mo ?? 0,
                        'moo' => $request->moo ?? 0,
                        'retur' => $request->retur ?? 'T',
                        'ppn' => $request->ppn ?? 0,
                        'margin' => $request->margin ?? 0,
                        'dc' => $request->dc ?? 0,
                        'usrnm' => $username,
                        'tg_smp' => now()
                    ]);

                // Update brgdt for all cabang
                $cabangData = json_decode($request->cabang_data, true);
                foreach ($cabangData as $item) {
                    DB::table($item['cbg'] . '.brgdt')
                        ->where('kd_brg', $kd_brg)
                        ->where('cbg', $item['cbg'])
                        ->where('yer', date('Y'))
                        ->update([
                            'kdlaku' => $item['kdlaku'] ?? '',
                            'klk' => $item['klk'] ?? '',
                            'lph' => $item['lph'] ?? 0,
                            'dtr' => $item['dtr'] ?? 0,
                            'usrnm' => $username,
                            'tg_smp' => now()
                        ]);
                }
            } else {
                // INSERT new record
                // Check if already exists
                $exists = DB::table('brg')->where('kd_brg', $kd_brg)->exists();
                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode barang sudah ada!'
                    ], 422);
                }

                // Insert to brg table
                DB::table('brg')->insert([
                    'kd_brg' => $kd_brg,
                    'kdbar' => $kd_brg,
                    'sub' => $request->sub,
                    'kelompok' => $request->kelompok,
                    'na_brg' => $request->na_brg,
                    'item_sup' => $request->item_sup ?? '',
                    'barcode' => $request->barcode ?? '',
                    'type' => $request->type ?? '',
                    'ket_kem' => $request->ket_kem ?? '',
                    'ket_uk' => $request->ket_uk ?? '',
                    'supp' => $request->supp ?? '',
                    'mo' => $request->mo ?? 0,
                    'moo' => $request->moo ?? 0,
                    'retur' => $request->retur ?? 'T',
                    'sp_l' => $request->sp_l ?? '',
                    'sp_lf' => $request->sp_lf ?? '',
                    'kk' => $request->kk ?? '',
                    'ppn' => $request->ppn ?? 0,
                    'margin' => $request->margin ?? 0,
                    'dc' => $request->dc ?? 0,
                    'usrnm' => $username,
                    'tg_smp' => now()
                ]);

                // Insert to brgdt for all cabang
                $cabangData = json_decode($request->cabang_data, true);
                foreach ($cabangData as $item) {
                    DB::table($item['cbg'] . '.brgdt')->insert([
                        'kd_brg' => $kd_brg,
                        'cbg' => $item['cbg'],
                        'kdlaku' => $item['kdlaku'] ?? '',
                        'klk' => $item['klk'] ?? '',
                        'lph' => $item['lph'] ?? 0,
                        'dtr' => $item['dtr'] ?? 0,
                        'srmin' => $item['srmin'] ?? 0,
                        'srmax' => $item['srmax'] ?? 0,
                        'smin' => $item['smin'] ?? 0,
                        'smax' => $item['smax'] ?? 0,
                        'hj' => $item['hj'] ?? 0,
                        'hb' => $item['hb'] ?? 0,
                        'yer' => date('Y'),
                        'usrnm' => $username,
                        'tg_smp' => now()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data barang berhasil disimpan!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store barang: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */



    // ganti 15

    public function edit(Request $request)
    {
        try {
            $idx = $request->idx ?? 0;
            $cbg = Auth::user()->CBG;
            $username = Auth::user()->username;
            $flag = session('flag', 'TGZ'); // dari session login

            if ($idx == 0) {
                // Mode tambah baru
                $brg = null;
                $data = [
                    'brg' => null,
                    'mode' => 'add',
                    'flag' => $flag
                ];
            } else {
                // Mode edit
                $brg = DB::selectOne("
                    SELECT b.*,
                           LEFT(b.kd_brg,3) AS subnd,
                           RIGHT(b.kd_brg,4) AS kdbar,
                           s.namas AS nsup,
                           s.almt_k AS alamat,
                           s.kota,
                           a.kelompok
                    FROM brg b
                    LEFT JOIN sup s ON b.supp = s.kodes
                    LEFT JOIN aotprice a ON b.sub = a.SUB
                    WHERE b.NO_ID = ?
                ", [$idx]);

                if (!$brg) {
                    return redirect('/brg')->with('error', 'Data tidak ditemukan');
                }

                $data = [
                    'brg' => $brg,
                    'mode' => 'edit',
                    'flag' => $flag
                ];
            }

            return view('master_barang.edit', $data);
        } catch (\Exception $e) {
            Log::error('Error edit barang: ' . $e->getMessage());
            return redirect('/brg')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Request $request)
    {
        try {
            $idx = $request->idx ?? 0;
            $cbg = Auth::user()->CBG;
            $flag = session('flag', 'TGZ');

            $brg = DB::selectOne("
                SELECT b.*,
                       LEFT(b.kd_brg,3) AS subnd,
                       RIGHT(b.kd_brg,4) AS kdbar,
                       s.namas AS nsup,
                       s.almt_k AS alamat,
                       s.kota,
                       a.kelompok
                FROM brg b
                LEFT JOIN sup s ON b.supp = s.kodes
                LEFT JOIN aotprice a ON b.sub = a.SUB
                WHERE b.NO_ID = ?
            ", [$idx]);

            if (!$brg) {
                return redirect('/brg')->with('error', 'Data tidak ditemukan');
            }

            $data = [
                'brg' => $brg,
                'mode' => 'view',
                'flag' => $flag
            ];

            return view('master_barang.show', $data);
        } catch (\Exception $e) {
            Log::error('Error show barang: ' . $e->getMessage());
            return redirect('/brg')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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

    public function destroy(Request $request, Brg $brg)
    {

        // ganti 23
        $deleteBrg = Brg::find($brg->NO_ID);

        // ganti 24

        $deleteBrg->delete();

        // ganti
        return redirect('/brg')->with('status', 'Data berhasil dihapus');
    }

    public function Print(Request $request)
    {
        // Ambil filter dari request (misalnya dikirim via tombol print)
        $sub = $request->input('sub');

        // Nama file laporan Jasper
        $file = 'Daftar_Barang'; // ubah sesuai nama file .jrxml kamu, misalnya 'brg_list.jrxml'
        $PHPJasperXML = new \PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // === Query utama (sesuai dengan query DataTables kamu) ===
        $query = DB::table('brgdt as a')
            ->join('brg as b', 'a.kd_brg', '=', 'b.kd_brg')
            ->leftJoin(DB::raw("(SELECT sup.kodes, sup.namas AS nama, sup.kota AS kt, sup.almt_k AS alamat FROM sup) AS ole"), 'b.supp', '=', 'ole.kodes')
            ->select(
                'b.dc',
                'b.sub',
                'b.kelompok',
                'b.kd_brg',
                DB::raw("LEFT(b.kd_brg,3) as subnd"),
                DB::raw("RIGHT(b.kd_brg,4) as kdbar"),
                'b.na_brg',
                'b.nmbar',
                'b.item_sup',
                'b.type',
                'b.ket_kem',
                'b.ket_uk',
                'b.supp',
                'b.mo',
                'b.moo',
                'b.retur',
                'a.dtr',
                'a.klk',
                'b.sp_l',
                'b.sp_lf',
                'b.KK',
                'b.ppn',
                'a.lph',
                'b.usrnm',
                'b.tg_smp',
                'b.margin',
                'b.barcode',
                'ole.nama',
                'ole.alamat',
                'ole.kt',
                'b.merk'
            );

        // Filter sesuai input user
        if (!empty($sub)) {
            $query->whereRaw("LEFT(b.kd_brg,3) = ?", [$sub]);
        }

        $result = $query->orderBy('b.kd_brg')->get();

        // === Konversi hasil ke array untuk Jasper ===
        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'DC'        => $row->dc,
                'SUB'       => $row->sub,
                'KELOMPOK'  => $row->kelompok,
                'KD_BRG'    => $row->kd_brg,
                'NA_BRG'    => $row->na_brg,
                'KET_UK'    => $row->ket_uk,
                'KET_KEM'   => $row->ket_kem,
                'MERK'      => $row->merk,
                'SUPP'      => $row->supp,
                'NAMA'      => $row->nama,
                'ALAMAT'    => $row->alamat,
                'KOTA'      => $row->kt,
                'TYPE'      => $row->type,
                'BARCODE'   => $row->barcode,
            ];
        }

        // Kirim data ke Jasper
        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I"); // "I" artinya inline (tampil di browser)
    }
}
