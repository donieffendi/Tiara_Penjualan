<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Brg;
use App\Models\Master\Brgch;
use App\Models\Master\BrgDetail;
use App\Models\Master\Brgfc;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BrgfcController extends Controller
{
    //
    public function index()
    {
        return view('master_brgfc_used.index');
    }

    public function get(Request $request)
    {

        $subDr = $request->input('subDr');
        $subSmp = $request->input('subSmp');

        // $sql = "SELECT NO_ID, KD_BRG, NA_BRG, KET_UK, KET_KEM, PPN, TARIK, MASA_EXP, SUPP, NAMAS, SUB FROM brgfc";
        $sql = "SELECT
                a.NO_ID,
                a.SUB,
                a.KD_BRG,
                a.NA_BRG,
                a.KET_UK,
                a.KET_KEM,
                a.PPN,
                a.TARIK,
                a.MASA_EXP,
                b.KODES AS SUPP,
                b.NAMAS AS NAMAS
            FROM
                brgfc AS a
            LEFT JOIN
                brgfc AS b ON a.SUPP = b.KODES";
        $bindings = [];

        if (!empty($subDr) && !empty($subSmp)) {
            $sql .= " WHERE a.SUB BETWEEN ? AND ?";
            $bindings[] = $subDr;
            $bindings[] = $subSmp;
        }

        $sql .= " ORDER BY KD_BRG";

        $brgfc = DB::select($sql, $bindings);

        return Datatables::of($brgfc)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer" || Auth::user()->divisi == "owner" || Auth::user()->divisi == "sales") {
                    // url untuk delete di index
                    $url = "'" . url("brgfc/delete/" . $row->NO_ID) . "'";
                    // batas

                    $btnDelete = ' onclick="deleteRow(' . $url . ')"';

                    $btnPrivilege =
                        '
                                    <a class="dropdown-item" href="brgfc/edit/?idx=' . $row->NO_ID . '&tipx=edit";
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
                                <a hidden class="dropdown-item" href="brgfc/show/' . $row->NO_ID . '">
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        dd($request->all());

        $this->validate(
            $request,
            // GANTI 9

            [
                'SUB'       => 'required'
            ]
        );

        // Insert Header

        // $CBG = Auth::user()->CBG;

        // ganti 10

        $brgfc = Brgfc::create(
            [
                'SUB'       => ($request['SUB'] == null) ? "" : $request['SUB'],
                'KD_BRG'    => ($request['kdbar'] == null) ? "" : $request['kdbar'],
                'NA_BRG'    => ($request['na_brg'] == null) ? "" : $request['na_brg'],
                'HJ'        => ($request['hj'] == null) ? 0 : (float) str_replace(',', '', $request['hj']),
                'HB'        => ($request['hb'] == null) ? 0 : (float) str_replace(',', '', $request['hb']),
                'LOKASI'    => ($request['LOKASI'] == null) ? "" : $request['LOKASI'],
                'KELOMPOK'  => ($request['KELOMPOK'] == null) ? "" : $request['KELOMPOK'],
                'TKP'       => ($request['tkp'] == null) ? "" : $request['tkp'],
                'FLAGSTOK'  => ($request['flagstok'] == null) ? "" : $request['flagstok'],
                'SUPP'      => ($request['supp'] == null) ? "" : $request['supp'],
                'NAMAS'     => ($request['namas'] == null) ? "" : $request['namas'],
                'BARCODE'   => ($request['BARCODE'] == null) ? "" : $request['BARCODE'],
                'STAND'     => ($request['STAND'] == null) ? "" : $request['STAND'],
                'TYPE'      => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'DIS'       => ($request['dis'] == null) ? 0 : (float) str_replace(',', '', $request['dis']),
                'MARGIN'    => ($request['MARGIN'] == null) ? 0 : (float) str_replace(',', '', $request['MARGIN']),
            ]
        );

        return redirect('/brgfc')->with('statusInsert', 'Data baru berhasil ditambahkan');
    }

    public function edit(Request $request, BrgDetail $brgfc)
    {

        // ganti 1
        $tipx = $request->tipx;

        $idx = $request->idx;



        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';
        }

        if ($tipx == 'search') {


            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODES from brgfc
		                 where KODES = '$kodex'
		                 ORDER BY KODES ASC  LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, KODES from brgfc
		                 ORDER BY KODES ASC  LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }


        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODES from brgfc
		             where KODES <
					 '$kodex' ORDER BY KODES DESC LIMIT 1");


            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }
        if ($tipx == 'next') {


            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, KODES from brgfc
		             where KODES >
					 '$kodex' ORDER BY KODES ASC LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }
        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, KODES from brgfc
		              ORDER BY KODES DESC  LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }


        if ($tipx == 'undo' || $tipx == 'search') {

            $tipx = 'edit';
        }


        if ($idx != 0) {
            $brgfc = brgfc::where('NO_ID', $idx)->first();
        } else {
            $brgfc = new brgfc;
            $brgfc->TGL = Carbon::now();
            $brgfc->TG_NPWP = Carbon::now();
        }

        $data = [
            'header' => $brgfc,
        ];
        return view('master_brgfc_used.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Sup  $Sup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sup $Sup)
    {

        $this->validate(
            $request,
            [
                // ganti 19
                'KODES'       => 'required',

            ]
        );

        $Sup->update(
            [
                'NAMAS'         => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'NAMA'          => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'TYPE'          => ($request['TYPE'] == null) ? "" : $request['TYPE'],
                'SUP_BARU'      => ($request['SUP_BARU'] == null) ? "" : $request['SUP_BARU'],
                'GOLONGAN'      => ($request['GOLONGAN'] == null) ? "" : $request['GOLONGAN'],
                'PEMILIK'       => ($request['PEMILIK'] == null) ? "" : $request['PEMILIK'],
                'TLP_R'         => ($request['TLP_R'] == null) ? "" : $request['TLP_R'],
                'ALMT_R'        => ($request['ALMT_R'] == null) ? "" : $request['ALMT_R'],
                'ALMT_K'        => ($request['ALMT_K'] == null) ? "" : $request['ALMT_K'],
                'KOTA'          => ($request['KOTA'] == null) ? "" : $request['KOTA'],
                'ALMT_GD'       => ($request['ALMT_GD'] == null) ? "" : $request['ALMT_GD'],
                'TLP_K'         => ($request['TLP_K'] == null) ? "" : $request['TLP_K'],
                'NO_FAX'        => ($request['NO_FAX'] == null) ? "" : $request['NO_FAX'],
                'NO_HP'         => ($request['NO_HP'] == null) ? "" : $request['NO_HP'],
                'NO_TELEX'      => ($request['NO_TELEX'] == null) ? "" : $request['NO_TELEX'],
                'EMAIL'         => ($request['EMAIL'] == null) ? "" : $request['EMAIL'],
                'EMAIL2'        => ($request['EMAIL2'] == null) ? "" : $request['EMAIL2'],
                'EMAIL3'        => ($request['EMAIL3'] == null) ? "" : $request['EMAIL3'],
                'GOL_BRG'       => ($request['GOL_BRG'] == null) ? "" : $request['GOL_BRG'],
                'KD_PEMBY'      => ($request['KD_PEMBY'] == null) ? "" : $request['KD_PEMBY'],
                'STM_PEMBL'     => ($request['STM_PEMBL'] == null) ? "" : $request['STM_PEMBL'],
                'DISC_PS'       => (float) str_replace(',', '', $request['DISC_PS']),
                'JEN_BRG1'      => ($request['JEN_BRG1'] == null) ? "" : $request['JEN_BRG1'],
                'CARA'          => ($request['CARA'] == null) ? "" : $request['CARA'],
                'BG_PERS'       => ($request['BG_PERS'] == null) ? "" : $request['BG_PERS'],
                'STTS'          => ($request['STTS'] == null) ? "" : $request['STTS'],
                'SUB'           => ($request['SUB'] == null) ? "" : $request['SUB'],
                'KD_BANK'       => ($request['KD_BANK'] == null) ? "" : $request['KD_BANK'],
                'NPWP'          => ($request['NPWP'] == null) ? "" : $request['NPWP'],
                'NPPKP'         => ($request['NPPKP'] == null) ? "" : $request['NPPKP'],
                'NAMA_B'        => ($request['NAMA_B'] == null) ? "" : $request['NAMA_B'],
                'NM_NPWP'       => ($request['NM_NPWP'] == null) ? "" : $request['NM_NPWP'],
                'CABANG_B'      => ($request['CABANG_B'] == null) ? "" : $request['CABANG_B'],
                'NO_NPWP'       => ($request['NO_NPWP'] == null) ? "" : $request['NO_NPWP'],
                'KOTA_B'        => ($request['KOTA_B'] == null) ? "" : $request['KOTA_B'],
                'AL_NPWP'       => ($request['AL_NPWP'] == null) ? "" : $request['AL_NPWP'],
                'AN_B'          => ($request['AN_B'] == null) ? "" : $request['AN_B'],
                'NOREK'         => ($request['NOREK'] == null) ? "" : $request['NOREK'],
                'TG_NPWP'       => date('Y-m-d', strtotime($request['TG_NPWP'])),
                'SERI'          => ($request['SERI'] == null) ? "" : $request['SERI'],
                'FO_KLB'        => (float) str_replace(',', '', $request['FO_KLB']),
                'NF_KLB'        => (float) str_replace(',', '', $request['NF_KLB']),
                'PB_KLB'        => (float) str_replace(',', '', $request['PB_KLB']),
                'ST_KLB'        => (float) str_replace(',', '', $request['ST_KLB']),
                'FF_KLB'        => (float) str_replace(',', '', $request['FF_KLB']),
                'BS_KLB'        => (float) str_replace(',', '', $request['BS_KLB']),
                'MATERAI'       => ($request['MATERAI'] == null) ? "" : $request['MATERAI'],
                'ZONE'          => ($request['ZONE'] == null) ? "" : $request['ZONE'],
                'CETAK_SBY'     => ($request['CETAK_SBY'] == null) ? "" : $request['CETAK_SBY'],
                'ACC_PPN'       => ($request['ACC_PPN'] == null) ? "" : $request['ACC_PPN'],
                'CAT_LO'        => ($request['CAT_LO'] == null) ? "" : $request['CAT_LO'],
                'DIS_P4'        => (float) str_replace(',', '', $request['DIS_P4']),
                'RETUR'         => ($request['RETUR'] == null) ? "" : $request['RETUR'],
                'KET_HAPUS'     => ($request['KET_HAPUS'] == null) ? "" : $request['KET_HAPUS'],
                'JMN_RETUR'     => (float) str_replace(',', '', $request['JMN_RETUR']),
                'HARI'          => ($request['HARI'] == null) ? "" : $request['HARI'],
                'TND_SPL'       => ($request['TND_SPL'] == null) ? "" : $request['TND_SPL'],
                'B_CODE'        => ($request['B_CODE'] == null) ? "" : $request['B_CODE'],
                'JAMIN_RET'     => ($request['JAMIN_RET'] == null) ? "" : $request['JAMIN_RET'],
                'TGL'           => date('Y-m-d', strtotime($request['TGL'])),
                'VA_GZ'         => ($request['VA_GZ'] == null) ? "" : $request['VA_GZ'],
                'S_BAR'         => ($request['S_BAR'] == null) ? "" : $request['S_BAR'],
                'NOREK_GZ'      => ($request['NOREK_GZ'] == null) ? "" : $request['NOREK_GZ'],
                'AN_B_GZ'       => ($request['AN_B_GZ'] == null) ? "" : $request['AN_B_GZ'],
                'ANB_VA_GZ'     => ($request['ANB_VA_GZ'] == null) ? "" : $request['ANB_VA_GZ'],
                'EMAIL_GZ'      => ($request['EMAIL_GZ'] == null) ? "" : $request['EMAIL_GZ'],
                'D_BUTOR'       => ($request['D_BUTOR'] == null) ? "" : $request['D_BUTOR'],
                'CAT_RET'       => ($request['CAT_RET'] == null) ? "" : $request['CAT_RET'],
                'CAT_PRM'       => ($request['CAT_PRM'] == null) ? "" : $request['CAT_PRM'],
                'SR_TERBIT'     => (float) str_replace(',', '', $request['SR_TERBIT']),
                'BONAFIT'       => ($request['BONAFIT'] == null) ? "" : $request['BONAFIT'],
                'KEL_PAJAK'     => ($request['KEL_PAJAK'] == null) ? "" : $request['KEL_PAJAK'],
                'N_AKTIF'       => ($request['N_AKTIF'] == null) ? "" : $request['N_AKTIF'],
                'KETNAKTIF'     => ($request['KETNAKTIF'] == null) ? "" : $request['KETNAKTIF'],
                'LAIN1'         => ($request['LAIN1'] == null) ? "" : $request['LAIN1'],
                'LAIN2'         => ($request['LAIN2'] == null) ? "" : $request['LAIN2'],
                'KOD_MIN'       => ($request['KOD_MIN'] == null) ? "" : $request['KOD_MIN'],
                'KLB2'          => (float) str_replace(',', '', $request['KLB2']),
                'ORDR'          => (float) str_replace(',', '', $request['ORDR']),
                'BY_KR'         => ($request['BY_KR'] == null) ? "" : $request['BY_KR'],
                'URAIAN1'       => ($request['URAIAN1'] == null) ? "" : $request['URAIAN1'],
                'URAIAN2'       => ($request['URAIAN2'] == null) ? "" : $request['URAIAN2'],
                'KLK'           => ($request['KLK'] == null) ? "" : $request['KLK'],
                'CAT_SP'        => ($request['CAT_SP'] == null) ? "" : $request['CAT_SP'],
                'SP'            => (float) str_replace(',', '', $request['SP']),
                'JAM'           => ($request['JAM'] == null) ? "" : $request['JAM'],
                // 'CBG'           => $CBG,
                'TG_SMP'        => Carbon::now()
            ]
        );

        return redirect('/brgfc')->with('status', 'Data baru berhasil diedit');
    }
}