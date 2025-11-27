<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Tpondg;
use Auth;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;

class BrgJasaPaController extends Controller
{

    public function index()
    {
        return view('master_keperluan_barang_dan_jasa_panitia_acara.index');
    }

        public function browse_dept_pa(Request $request)
    {
        $jasa = DB::SELECT("SELECT KD, DEP from nddafdep ORDER BY KD ASC");
        return response()->json($jasa);
    }

    public function getBrgJasaPA(Request $request)
    {
        $per = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        
        $query = DB::SELECT("SELECT NO_ID, NO_BUKTI, NA_BRG, QTY, SATUAN, UKURAN, HARGA, CBG, KD_DEPT, PER, 
                                TOTAL, BATAS1, TG_PBL, AKUNT, KET, TGL, DEPT, USRNM, POSTED
                                FROM tpondg where PER = '$per' AND KET = 'ACARA'
                                GROUP BY  NO_BUKTI ORDER BY NO_BUKTI asc ");

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    $btnPrivilege =
                    '
                                    <a class="dropdown-item" href="brg-jasa-pa/edit/?idx=' . $row->NO_ID . '&tipx=edit";
                                    <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <hr></hr>
                                     <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="brg-jasa-pa/delete/' . $row->NO_ID . '">
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
                                <a hidden class="dropdown-item" href="brg-jasa-pa/show/' . $row->NO_ID . '">
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
        //  dd($request->all());
        // $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        // $perid = Perid::where('NO_ID', $kd_peri)->first();
        // if ($perid && $perid->posted == 1) {
        //     return response()->json(['error' => 'Periode sudah diposting, tidak bisa simpan.'], 400);
        // }

        // // 2. Generate Nomor Bukti jika baru
        // $no_bukti = $request->no_bukti;
        // if ($no_bukti == '+' || empty($no_bukti)) {
        //     $bulan = substr($periode, 0, 2);
        //     $tahun = substr($periode, 2, 4);

        //     // ambil kode type dari toko
        //     $toko  = Toko::where('kode', $request->cbg)->first();
        //     $kode2 = $toko ? $toko->type : '';

        //     // ambil nomor urut dari notrans
        //     $fieldNom = "nom" . $bulan;
        //     $notrans  = Notrans::where('trans', 'TJASA')->where('per', $tahun)->first();

        //     $r1 = $notrans ? $notrans->$fieldNom : 0;
        //     $r1 = $r1 + 1;

        //     // update notrans
        //     if ($notrans) {
        //         $notrans->update([$fieldNom => $r1]);
        //     }

        //     // format nomor bukti
        //     $kode     = 'ND' . substr($tahun, 2, 2) . $bulan;
        //     $bkt1     = str_pad($r1, 4, '0', STR_PAD_LEFT);
        //     $no_bukti = $kode . '-' . $bkt1 . $kode2;
        // }
        $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];

        $bulan = str_pad(session()->get('periode')['bulan'], 2, '0', STR_PAD_LEFT);
        $tahun = session()->get('periode')['tahun'];

        $query = DB::table('tpondg')
            ->select('no_bukti')
            ->where('no_bukti', 'like', 'BJP' . $tahun . $bulan . '%')
            ->orderByDesc('no_bukti')
            ->first();

        if ($query) {
            $lastNumber = intval(substr($query->no_bukti, -3));
            $newNumber  = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $no_bukti = 'BJP' . $tahun . $bulan . $newNumber;

        // Insert Header

        // ganti 10

        $jumlah = count($request->na_brg);

        for ($i = 0; $i < $jumlah; $i++) {
            Tpondg::create([
                'no_bukti' => $no_bukti,
                'tgl'      => $request->tgl ?? null,
                'notes'    => $request->notes ?? '',
                'kd_dept'  => $request->kd_dept ?? '',
                'dept'     => $request->dept ?? '',
                'per'      => $request->per ?? '',
                'cbg'      => $request->cbg ?? '',
                'usrnm'    => $request->usrnm ?? '',
                'na_brg'   => $request->na_brg[$i] ?? '',
                'qty'      => $request->qty[$i] ?? 0,
                'satuan'   => $request->satuan[$i] ?? '',
                'ukuran'   => $request->ukuran[$i] ?? '',
                'merk'     => $request->merk[$i] ?? '',
                'harga'    => $request->harga[$i] ?? 0,
                'total'    => $request->total[$i] ?? 0,
                'batas1'   => $request->batas1[$i] ?? '',
                'panitia'  => $request->panitia ?? '',
                'seksi'    => $request->seksi ?? '',
                'kepl'     => $request->kepl ?? '',
                'tg_smp'   => Carbon::now(),
            ]);
        }

        return redirect('/brg-jasa-pa')->with('statusInsert', 'Data baru berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 15

    public function edit(Request $request, Tpondg $brgJasa)
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

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from tpondg
		                 where NO_BUKTI = '$kodex'
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'top') {

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from tpondg
		                 ORDER BY NO_BUKTI ASC  LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }

        }

        if ($tipx == 'prev') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from tpondg
		             where NO_BUKTI <
					 '$kodex' ORDER BY NO_BUKTI DESC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }
        if ($tipx == 'next') {

            $kodex = $request->kodex;

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from tpondg
		             where NO_BUKTI >
					 '$kodex' ORDER BY NO_BUKTI ASC LIMIT 1");

            if (! empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = $idx;
            }

        }

        if ($tipx == 'bottom') {

            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from tpondg
		              ORDER BY NO_BUKTI DESC  LIMIT 1");

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
            $tpondg = Tpondg::where('NO_ID', $idx)->first();
        } else {
            $tpondg = new Tpondg();
            $tpondg->batas1 = Carbon::now();
            $tpondg->tgl = Carbon::now();
        }

        $data = [
            'header' => $tpondg,
        ];

        return view('master_keperluan_barang_dan_jasa_panitia_acara.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tpondg $brgJasa)
    {
        $no_bukti = $brgJasa->no_bukti;

        // hapus detail lama
        Tpondg::where('no_bukti', $no_bukti)->delete();

        $jumlah = is_array($request->na_brg) ? count($request->na_brg) : 0;

        for ($i = 0; $i < $jumlah; $i++) {
            Tpondg::create([
                'no_bukti' => $no_bukti,
                'tgl'      => $request->tgl,
                'notes'    => $request->notes,
                'kd_dept'  => $request->kd_dept,
                'dept'     => $request->dept,
                'per'      => $request->per,
                'cbg'      => $request->cbg,
                'usrnm'    => $request->usrnm,
                'na_brg'   => $request->na_brg[$i] ?? '',
                'qty'      => $request->qty[$i] ?? 0,
                'satuan'   => $request->satuan[$i] ?? '',
                'ukuran'   => $request->ukuran[$i] ?? '',
                'merk'     => $request->merk[$i] ?? '',
                'harga'    => $request->harga[$i] ?? 0,
                'total'    => $request->total[$i] ?? 0,
                'batas1'   => $request->batas1[$i] ?? '',
                'panitia'  => $request->panitia,
                'seksi'    => $request->seksi,
                'kepl'     => $request->kepl,
                'tg_smp'   => Carbon::now(),
            ]);
        }

        return redirect('/brg-jasa-pa')->with('statusUpdate', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Master\Rute  $rute
     * @return \Illuminate\Http\Response
     */

    // ganti 22

    public function destroy(Request $request, Tpondg $brgJasa)
    {

        // ganti 23
        $deletex = Tpondg::find($brgJasa->NO_ID);

        // ganti 24

        $deletex->delete();

        // ganti
        return redirect('/brg-jasa-pa')->with('status', 'Data berhasil dihapus');
    }

    public function Print(Request $request)
    {
        // Ambil filter dari request (misalnya dikirim via tombol print)
        $sub = $request->input('sub');

        // Nama file laporan Jasper
        $file = 'brg_jasa_pa_print'; // ubah sesuai nama file .jrxml kamu, misalnya 'brg_list.jrxml'
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

    public function PrintLap(Request $request)
    {
        // Ambil filter dari request (misalnya dikirim via tombol print)
        $sub = $request->input('sub');

        // Nama file laporan Jasper
        $file = 'brg_jasa_pa_laporan'; // ubah sesuai nama file .jrxml kamu, misalnya 'brg_list.jrxml'
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