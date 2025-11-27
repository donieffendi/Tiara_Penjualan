<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TPNonDagangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = 'Transaksi Pembayaran Non Dagang';
    var $FLAGZ = 'NB';

    function setFlag(Request $request)
    {
        $this->judul = "Transaksi Pembayaran Non Dagang";
        $this->FLAGZ = 'NB';
    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        return view("otransaksi_TPNonDagang.index")->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Display NonBeli data (equivalent to Tampil procedure)
     * Based on: SELECT * FROM NONBELI where PER =:periode AND CBG=:FLAG2 AND FLAG='NB'
     */
    public function getTpNonDagang(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;

        // Main query equivalent to Delphi's Tampil procedure
        $nonbeli = DB::SELECT("SELECT NO_ID, no_bukti, tgl, jtempo, kodes, namas, notes,
                              total, posted, usrnm, gol, pph1, pph2, pph3, materai,
                              lain, diskon, ppn, nett, PRNT, tg_smp
                             FROM nonbeli
                             WHERE PER = '$periode'
                             AND CBG = '$CBG'
                             AND FLAG = 'NB'
                             ORDER BY NO_BUKTI");

        return Datatables::of($nonbeli)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    $btnEdit = ($row->posted == 1) ?
                        ' onclick= "alert(\'Transaksi ' . $row->no_bukti . ' sudah diposting!\')" href="#" ' :
                        ' href="tpnondagang/edit/?idx=' . $row->NO_ID . '&tipx=edit"';

                    $btnDelete = ($row->posted == 1) ?
                        ' onclick= "alert(\'Transaksi ' . $row->no_bukti . ' sudah diposting!\')" href="#" ' :
                        ' onclick="deleteRow(\'' . url("tpnondagang/delete/" . $row->NO_ID) . '\')" ';

                    $btnPrivilege = '
                        <a class="dropdown-item" ' . $btnEdit . '>
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a class="dropdown-item" href="#" onclick="printSingle(\'' . $row->no_bukti . '\')">
                            <i class="fa fa-print" aria-hidden="true"></i> Print
                        </a>
                        <hr></hr>
                        <a class="dropdown-item btn btn-danger" ' . $btnDelete . '>
                            <i class="fa fa-trash" aria-hidden="true"></i> Delete
                        </a>';
                } else {
                    $btnPrivilege = '';
                }

                return '
                <div class="dropdown show" style="text-align: center">
                    <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        ' . $btnPrivilege . '
                    </div>
                </div>';
            })
            ->addColumn('cek', function ($row) {
                return '<input type="checkbox" name="cek[]" class="form-control cek" ' .
                    (($row->posted == 1) ? "checked" : "") . ' value="' . $row->no_bukti . '" ' .
                    (($row->posted == 2) ? "disabled" : "") . '></input>';
            })
            ->editColumn('total', function ($row) {
                return number_format($row->total, 2, ',', '.');
            })
            ->editColumn('nett', function ($row) {
                return number_format($row->nett, 2, ',', '.');
            })
            ->rawColumns(['action', 'cek'])
            ->make(true);
    }

    /**
     * Get detail data for NonBeli (equivalent to detail query)
     */
    public function getDetailtpnondagang(Request $request)
    {
        $no_bukti = $request->no_bukti;

        if (empty($no_bukti)) {
            return response()->json([]);
        }

        $detail = DB::SELECT("
            SELECT ID, rec, acno, ket, total, tgl as agenda
            FROM nonbelid
            WHERE NO_BUKTI = '$no_bukti'
            ORDER BY REC ASC
        ");

        return response()->json($detail);
    }

    /**
     * Show the form for creating a new resource.
     * Equivalent to NewClick procedure
     */
    public function create()
    {
        // Check period posting status equivalent to Delphi's NewClick
        if (request()->session()->has('periode')) {
            $periode = request()->session()->get('periode')['bulan'] . '/' . request()->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $header = null;
        $detail = null;
        $supplier_data = null;
        try {
            $posted = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), "Unknown column 'kd_peri'")) {
                // Set all variables to null and continue to create view
                $header = null;
                $detail = null;
                $supplier_data = null;
                return view('otransaksi_TPNonDagang.create', compact('header', 'detail', 'supplier_data'));
            }
            throw $e;
        }
        if (!empty($posted) && $posted[0]->posted == 1) {
            return redirect('/tpnondagang')->with('error', 'Closed Period');
        }
        return view('otransaksi_TPNonDagang.create', compact('header', 'detail', 'supplier_data'));
    }

    /**
     * Show the form for editing the specified resource.
     * Equivalent to cxGrid1DBTableView1DblClick procedure
     */
    public function edit($no_id = null)
    {
        // --- Delphi logic mapping ---
        $tipx = request()->tipx;
        $idx = request()->idx ?? $no_id;
        $flagz = request()->flagz ?? 'NB';
        $readonly_mode = false;

        // Periode dari session
        if (request()->session()->has('periode')) {
            $periode = request()->session()->get('periode')['bulan'] . '/' . request()->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        // Jika mode new (tambah data)
        if ($tipx == 'new') {
            // Validasi periode (Delphi: cek posted)
            try {
                $posted = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);
            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), "Unknown column 'kd_peri'")) {
                    $posted = null;
                    return redirect('/tpnondagang')->with('error', "Kolom 'kd_peri' tidak ditemukan pada tabel perid.");
                }
                throw $e;
            }
            if (!empty($posted) && $posted[0]->posted == 1) {
                return redirect('/tpnondagang')->with('error', 'Closed Period');
            }

            // Default header (Delphi: hapus/hidup)
            $header = (object) [
                'NO_ID' => 0,
                'NO_BUKTI' => '',
                'TGL' => date('Y-m-d'),
                'JTEMPO' => date('Y-m-d'),
                'KODES' => '',
                'NAMAS' => '',
                'NOTES' => '',
                'POSTED' => 0,
                'GOL' => '',
                'PPH1' => 0,
                'PPH2' => 0,
                'PPH3' => 0,
                'MATERAI' => 0,
                'LAIN' => 0,
                'DISKON' => 0,
                'PPN' => 0,
                'TOTAL' => 0,
                'NETT' => 0
            ];
            $detail = collect();
            $supplier_data = null;
        } else {
            // Ambil data header berdasarkan NO_ID (Delphi: FormShow)
            $header = DB::table('nonbeli')->where('NO_ID', $no_id)->first();
            if (!$header) {
                return redirect('/tpnondagang')->with('error', 'Data tidak ditemukan');
            }

            // Cek status posted (Delphi: mati/hidup)
            $readonly_mode = ($header->POSTED == 1);

            // Ambil detail (Delphi: belid SQL)
            $detail = collect();
            if ($header->NO_BUKTI) {
                $detail_raw = DB::SELECT("SELECT ID, rec, acno, ket, total, tgl as agenda FROM nonbelid WHERE NO_BUKTI = ? ORDER BY REC ASC", [$header->NO_BUKTI]);
                foreach ($detail_raw as $row) {
                    $detail->push($row);
                }
            }

            // Ambil data supplier (Delphi: txtkodesExit)
            $supplier_data = null;
            if ($header->KODES) {
                $supplier_result = DB::SELECT("SELECT kodes, namas, no_rek, an_b, nama_b, kota_b FROM sup WHERE kodes = ?", [$header->KODES]);
                if (!empty($supplier_result)) {
                    $supplier_data = $supplier_result[0];
                }
            }
        }

        // Kirim ke view
        return view('otransaksi_TPNonDagang.edit', compact('header', 'detail', 'tipx', 'idx', 'flagz', 'supplier_data', 'readonly_mode'));
    }

    /**
     * Get supplier data by code (equivalent to supplier validation in Delphi)
     */
    public function getSupplierByCode(Request $request)
    {
        $kodes = trim($request->kodes);

        if (empty($kodes)) {
            return response()->json(['error' => 'Kode supplier tidak boleh kosong'], 400);
        }

        $supplier = DB::SELECT("
            SELECT kodes, namas, no_rek, an_b, nama_b, kota_b
            FROM sup
            WHERE kodes = ?
        ", [$kodes]);

        if (empty($supplier)) {
            return response()->json(['error' => 'Data supplier tidak ditemukan'], 404);
        }

        return response()->json($supplier[0]);
    }

    /**
     * Validate account code (equivalent to account validation in Delphi)
     */
    public function validateAccount(Request $request)
    {
        $acno = trim($request->acno);

        if (empty($acno)) {
            return response()->json(['error' => 'Kode akun tidak boleh kosong'], 400);
        }

        $account = DB::SELECT("
            SELECT acno, nama
            FROM account
            WHERE acno = ?
        ", [$acno]);

        if (empty($account)) {
            return response()->json(['error' => 'Kode akun tidak ditemukan'], 404);
        }

        return response()->json($account[0]);
    }

    /**
     * Print single NonBeli document (equivalent to Panel3Click and Print1Click)
     */
    public function printSingle(Request $request)
    {
        $no_bukti = $request->no_bukti;

        if (empty($no_bukti)) {
            return response()->json(['error' => 'No bukti tidak boleh kosong'], 400);
        }

        // Get form information (equivalent to Delphi form retrieval)
        $form_info = $this->getFormInfo();
        $toko_info = $this->getTokoInfo();

        // Main NonBeli query equivalent to Delphi's hero.sql.Text
        $hero = DB::SELECT("
            SELECT :na_toko as na_toko,
                   :typ_pers as typ_pers,
                   :alamat_pers as alamat_pers,
                   if(sup.NAMA_B = '', 'PEMBAYARAN', 'PEMBAYARAN VIA TRANSFER') AS JUDUL,
                   (SELECT IF (JUDUL = 'PEMBAYARAN', :AKUN_BYR, :AKUN_TLX)) AS NO_FORM,
                   lpad(month(nonbeli.TGL), 2, 0) as BULAN,
                   nonbeli.NO_BUKTI,
                   nonbeli.KODES,
                   nonbeli.NAMAS,
                   sup.NO_REK,
                   sup.AN_B,
                   sup.NAMA_B,
                   sup.KOTA_B,
                   nonbeli.TGL,
                   nonbeli.JTEMPO,
                   nonbeli.PER,
                   nonbeli.notes,
                   nonbeli.POSTED,
                   nonbeli.tgl_posted,
                   nonbeli.usrnm,
                   nonbeli.tg_smp,
                   nonbeli.GOL,
                   nonbeli.CBG,
                   nonbeli.PPH1,
                   nonbeli.PPH2,
                   nonbeli.PPH3,
                   nonbeli.MATERAI,
                   nonbeli.lain,
                   nonbeli.DISKON,
                   nonbeli.PPN,
                   nonbeli.NETT,
                   nonbelid.agenda,
                   nonbelid.tgl as tgluye,
                   nonbelid.ID,
                   nonbelid.rec,
                   nonbelid.acno,
                   nonbelid.ket,
                   nonbelid.total as totald
            FROM nonbeli, nonbelid, sup
            WHERE nonbeli.NO_BUKTI = nonbelid.no_bukti
              AND nonbeli.KODES = sup.KODES
              AND nonbeli.NO_BUKTI = :bukti
            ORDER BY nonbelid.rec ASC
        ", [
            'bukti' => $no_bukti,
            'AKUN_BYR' => $form_info['akun_byr'],
            'AKUN_TLX' => $form_info['akun_tlx'],
            'na_toko' => $toko_info['na_toko'],
            'typ_pers' => $toko_info['typ_pers'],
            'alamat_pers' => $toko_info['alamat_pers']
        ]);

        if (empty($hero)) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Mark as printed (equivalent to Delphi: update nonbeli set prnt=1)
        DB::UPDATE("UPDATE nonbeli SET prnt = 1 WHERE no_bukti = ?", [$no_bukti]);

        // Generate reports (equivalent to Delphi: frxNonBeli.ShowReport())
        return $this->generateNonBeliReport($hero, 'frxNonBeli');
    }

    /**
     * Print range of NonBeli documents (equivalent to Button1Click)
     */
    public function printRange(Request $request)
    {
        $txtbukti1 = trim($request->txtbukti1);
        $txtbukti2 = trim($request->txtbukti2);

        if (empty($txtbukti1) || empty($txtbukti2)) {
            return response()->json(['error' => 'Range bukti tidak boleh kosong'], 400);
        }

        // Get form information
        $form_info = $this->getFormInfo();
        $toko_info = $this->getTokoInfo();

        // Range query for NonBeli
        $hero = DB::SELECT("
            SELECT lpad(month(nonbeli.TGL), 2, 0) as BULAN,
                   :na_toko as na_toko,
                   :typ_pers as typ_pers,
                   :alamat_pers as alamat_pers,
                   if(sup.NAMA_B = '', 'PEMBAYARAN', 'PEMBAYARAN VIA TRANSFER') AS JUDUL,
                   (SELECT IF (JUDUL = 'PEMBAYARAN', :AKUN_BYR, :AKUN_TLX)) AS NO_FORM,
                   nonbeli.NO_BUKTI,
                   nonbeli.KODES,
                   nonbeli.NAMAS,
                   sup.NO_REK,
                   sup.AN_B,
                   sup.NAMA_B,
                   sup.KOTA_B,
                   nonbeli.TGL,
                   nonbeli.JTEMPO,
                   nonbeli.PER,
                   nonbeli.notes,
                   nonbeli.POSTED,
                   nonbeli.tgl_posted,
                   nonbeli.usrnm,
                   nonbeli.tg_smp,
                   nonbeli.GOL,
                   nonbeli.CBG,
                   nonbeli.PPH1,
                   nonbeli.PPH2,
                   nonbeli.PPH3,
                   nonbeli.MATERAI,
                   nonbeli.lain,
                   nonbeli.DISKON,
                   nonbeli.PPN,
                   nonbeli.NETT,
                   nonbelid.agenda,
                   nonbelid.tgl as tgluye,
                   nonbelid.ID,
                   nonbelid.rec,
                   nonbelid.acno,
                   nonbelid.ket,
                   nonbelid.total as totald
            FROM nonbeli, nonbelid, sup
            WHERE nonbeli.NO_BUKTI = nonbelid.no_bukti
              AND nonbeli.KODES = sup.KODES
              AND nonbeli.NO_BUKTI BETWEEN :txtbukti1 AND :txtbukti2
            ORDER BY nonbeli.NO_BUKTI, nonbelid.rec ASC
        ", [
            'txtbukti1' => $txtbukti1,
            'txtbukti2' => $txtbukti2,
            'AKUN_BYR' => $form_info['akun_byr'],
            'AKUN_TLX' => $form_info['akun_tlx'],
            'na_toko' => $toko_info['na_toko'],
            'typ_pers' => $toko_info['typ_pers'],
            'alamat_pers' => $toko_info['alamat_pers']
        ]);

        // Mark range as printed
        DB::UPDATE("UPDATE nonbeli SET prnt = 1 WHERE no_bukti BETWEEN ? AND ?", [$txtbukti1, $txtbukti2]);

        return $this->generateNonBeliReport($hero, 'frxNonBeli_Range');
    }

    /**
     * Generate NonBeli report using PHPJasperXML
     */
    private function generateNonBeliReport($data, $reportType)
    {
        $PHPJasperXML = new PHPJasperXML();

        // Load appropriate NonBeli report template
        $reportFile = $this->getNonBeliReportFile($reportType);
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/' . $reportFile);

        // Convert data to array format
        $reportData = [];
        foreach ($data as $record) {
            $reportData[] = (array) $record;
        }

        $PHPJasperXML->setData($reportData);

        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * Get NonBeli report file based on type
     */
    private function getNonBeliReportFile($reportType)
    {
        $reportFiles = [
            'frxNonBeli' => 'pembayaran_non_dagang.jrxml',
            'frxNonBeli_Range' => 'pembayaran_non_dagang_range.jrxml',
            'frxNonBeli_All' => 'pembayaran_non_dagang_all.jrxml'
        ];

        return $reportFiles[$reportType] ?? 'pembayaran_non_dagang.jrxml';
    }

    /**
     * Get form information (equivalent to Delphi form info retrieval)
     */
    private function getFormInfo()
    {
        // Get form numbers for both payment types (equivalent to Delphi form retrieval logic)
        $CBG = Auth::user()->CBG;

        $form_byr = DB::SELECT("
            SELECT tokoform.NO_BUKTI
            FROM toko, tokoform
            WHERE toko.TYP = tokoform.TYP
              AND toko.KODE = ?
              AND tokoform.KD_PRNT = 'NON-BELI'
        ", [$CBG]);

        $form_tlx = DB::SELECT("
            SELECT tokoform.NO_BUKTI
            FROM toko, tokoform
            WHERE toko.TYP = tokoform.TYP
              AND toko.KODE = ?
              AND tokoform.KD_PRNT = 'NON-BELI-TLX'
        ", [$CBG]);

        return [
            'akun_byr' => !empty($form_byr) ? $form_byr[0]->NO_BUKTI : 'T-AKK1-055.1',
            'akun_tlx' => !empty($form_tlx) ? $form_tlx[0]->NO_BUKTI : 'T-AKK1-055.2'
        ];
    }

    /**
     * Get store/company information (equivalent to Delphi toko info)
     */
    private function getTokoInfo()
    {
        $CBG = Auth::user()->CBG;

        $toko = DB::SELECT("SELECT NA_TOKO, TYP_PERS, ALAMAT FROM toko WHERE KODE = ?", [$CBG]);

        if (!empty($toko)) {
            return [
                'na_toko' => $toko[0]->NA_TOKO ?? '',
                'typ_pers' => $toko[0]->TYP_PERS ?? '',
                'alamat_pers' => $toko[0]->ALAMAT ?? ''
            ];
        }

        return [
            'na_toko' => '',
            'typ_pers' => '',
            'alamat_pers' => ''
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'TGL' => 'required',
                'JTEMPO' => 'required',
                'KODES' => 'required',
                'NAMAS' => 'required'
            ]
        );

        // Get periode
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;
        $bulan = session()->get('periode')['bulan'];
        $tahun = substr(session()->get('periode')['tahun'], -2);

        // Generate NO_BUKTI for NonBeli
        $query = DB::table('nonbeli')->select('NO_BUKTI')->where('PER', $periode)->where('CBG', $CBG)->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'NB' . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = 'NB' . $tahun . $bulan . '-0001';
        }

        try {
            DB::beginTransaction();

            // Insert Header to nonbeli
            DB::table('nonbeli')->insert([
                'NO_BUKTI' => $no_bukti,
                'TGL' => date('Y-m-d', strtotime($request['TGL'])),
                'JTEMPO' => date('Y-m-d', strtotime($request['JTEMPO'])),
                'PER' => $periode,
                'FLAG' => 'NB',
                'KODES' => $request['KODES'],
                'NAMAS' => $request['NAMAS'],
                'NOTES' => $request['NOTES'] ?? '',
                'GOL' => $request['GOL'] ?? '',
                'PPH1' => $request['PPH1'] ?? 0,
                'PPH2' => $request['PPH2'] ?? 0,
                'PPH3' => $request['PPH3'] ?? 0,
                'MATERAI' => $request['MATERAI'] ?? 0,
                'LAIN' => $request['LAIN'] ?? 0,
                'DISKON' => $request['DISKON'] ?? 0,
                'PPN' => $request['PPN'] ?? 0,
                'TOTAL' => $request['TOTAL'] ?? 0,
                'NETT' => $request['NETT'] ?? 0,
                'CBG' => $CBG,
                'USRNM' => Auth::user()->username,
                'TG_SMP' => Carbon::now(),
                'created_by' => Auth::user()->username,
                'created_at' => Carbon::now()
            ]);

            // Insert detail items if provided
            if ($request->has('detail') && is_array($request['detail'])) {
                $rec = 1;
                foreach ($request['detail'] as $detail_item) {
                    DB::table('nonbelid')->insert([
                        'NO_BUKTI' => $no_bukti,
                        'REC' => $rec,
                        'ACNO' => $detail_item['acno'] ?? '',
                        'KET' => $detail_item['ket'] ?? '',
                        'TOTAL' => $detail_item['total'] ?? 0,
                        'TGL' => $detail_item['agenda'] ?? date('Y-m-d'),
                        'created_by' => Auth::user()->username,
                        'created_at' => Carbon::now()
                    ]);
                    $rec++;
                }
            }

            DB::commit();

            return redirect('/tpnondagang')->with('status', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $no_id)
    {
        $this->validate(
            $request,
            [
                'TGL' => 'required',
                'JTEMPO' => 'required',
                'KODES' => 'required',
                'NAMAS' => 'required'
            ]
        );

        try {
            DB::beginTransaction();

            // Get current NO_BUKTI
            $current_data = DB::table('nonbeli')->where('NO_ID', $no_id)->first();
            if (!$current_data) {
                throw new \Exception('Data tidak ditemukan');
            }

            // Update Header in nonbeli
            DB::table('nonbeli')->where('NO_ID', $no_id)->update([
                'TGL' => date('Y-m-d', strtotime($request['TGL'])),
                'JTEMPO' => date('Y-m-d', strtotime($request['JTEMPO'])),
                'KODES' => $request['KODES'],
                'NAMAS' => $request['NAMAS'],
                'NOTES' => $request['NOTES'] ?? '',
                'GOL' => $request['GOL'] ?? '',
                'PPH1' => $request['PPH1'] ?? 0,
                'PPH2' => $request['PPH2'] ?? 0,
                'PPH3' => $request['PPH3'] ?? 0,
                'MATERAI' => $request['MATERAI'] ?? 0,
                'LAIN' => $request['LAIN'] ?? 0,
                'DISKON' => $request['DISKON'] ?? 0,
                'PPN' => $request['PPN'] ?? 0,
                'TOTAL' => $request['TOTAL'] ?? 0,
                'NETT' => $request['NETT'] ?? 0,
                'USRNM' => Auth::user()->username,
                'TG_SMP' => Carbon::now(),
                'updated_by' => Auth::user()->username,
                'updated_at' => Carbon::now()
            ]);

            // Update detail items if provided
            if ($request->has('detail') && is_array($request['detail'])) {
                // Delete existing detail records
                DB::table('nonbelid')->where('NO_BUKTI', $current_data->NO_BUKTI)->delete();

                // Insert new detail records
                $rec = 1;
                foreach ($request['detail'] as $detail_item) {
                    DB::table('nonbelid')->insert([
                        'NO_BUKTI' => $current_data->NO_BUKTI,
                        'REC' => $rec,
                        'ACNO' => $detail_item['acno'] ?? '',
                        'KET' => $detail_item['ket'] ?? '',
                        'TOTAL' => $detail_item['total'] ?? 0,
                        'TGL' => $detail_item['agenda'] ?? date('Y-m-d'),
                        'updated_by' => Auth::user()->username,
                        'updated_at' => Carbon::now()
                    ]);
                    $rec++;
                }
            }

            DB::commit();

            return redirect('/tpnondagang')->with('status', 'Data berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal mengupdate data: ' . $e->getMessage());
        }
    }

    /**
     * Browse supplier data (equivalent to supplier lookup in Delphi)
     */
    public function browseSupplier(Request $request)
    {
        $search = $request->search ?? '';

        $suppliers = DB::SELECT("
            SELECT kodes, namas, no_rek, an_b, nama_b, kota_b
            FROM sup
            WHERE kodes LIKE ? OR namas LIKE ?
            ORDER BY kodes
            LIMIT 50
        ", ["%$search%", "%$search%"]);

        return response()->json($suppliers);
    }

    /**
     * Browse account data (equivalent to account lookup in Delphi)
     */
    public function browseAccount(Request $request)
    {
        $search = $request->search ?? '';

        $accounts = DB::SELECT("
            SELECT acno, nama
            FROM account
            WHERE acno LIKE ? OR nama LIKE ?
            ORDER BY acno
            LIMIT 50
        ", ["%$search%", "%$search%"]);

        return response()->json($accounts);
    }

    /**
     * Generate report for NonBeli payment instructions
     */
    public function jasper(Request $request)
    {
        $judul = $this->judul;
        $CBG = Auth::user()->CBG;

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $query = DB::SELECT("
            SELECT NO_BUKTI, TGL, JTEMPO, KODES, NAMAS, TOTAL, NETT, NOTES,
                   GOL, PPH1, PPH2, PPH3, MATERAI, LAIN, DISKON, PPN
            FROM nonbeli
            WHERE PER = '$periode' AND CBG = '$CBG' AND FLAG = 'NB'
            ORDER BY NO_BUKTI
        ");

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $value->NO_BUKTI,
                'TGL' => $value->TGL,
                'JTEMPO' => $value->JTEMPO,
                'KODES' => $value->KODES,
                'NAMAS' => $value->NAMAS,
                'TOTAL' => $value->TOTAL,
                'NETT' => $value->NETT,
                'NOTES' => $value->NOTES,
                'GOL' => $value->GOL,
                'PPH1' => $value->PPH1,
                'PPH2' => $value->PPH2,
                'PPH3' => $value->PPH3,
                'MATERAI' => $value->MATERAI,
                'LAIN' => $value->LAIN,
                'DISKON' => $value->DISKON,
                'PPN' => $value->PPN,
                'JUDUL' => $judul,
                'CBG' => $CBG
            ));
        }

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/pembayaran_non_dagang_laporan.jrxml');

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * Print all NonBeli payment instructions
     */
    public function printAll(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;

        // Get form information
        $form_info = $this->getFormInfo();
        $toko_info = $this->getTokoInfo();

        // Query all NonBeli data for the period
        $hero = DB::SELECT("
            SELECT lpad(month(nonbeli.TGL), 2, 0) as BULAN,
                   :na_toko as na_toko,
                   :typ_pers as typ_pers,
                   :alamat_pers as alamat_pers,
                   if(sup.NAMA_B = '', 'PEMBAYARAN', 'PEMBAYARAN VIA TRANSFER') AS JUDUL,
                   (SELECT IF (JUDUL = 'PEMBAYARAN', :AKUN_BYR, :AKUN_TLX)) AS NO_FORM,
                   nonbeli.NO_BUKTI,
                   nonbeli.KODES,
                   nonbeli.NAMAS,
                   sup.NO_REK,
                   sup.AN_B,
                   sup.NAMA_B,
                   sup.KOTA_B,
                   nonbeli.TGL,
                   nonbeli.JTEMPO,
                   nonbeli.PER,
                   nonbeli.notes,
                   nonbeli.POSTED,
                   nonbeli.tgl_posted,
                   nonbeli.usrnm,
                   nonbeli.tg_smp,
                   nonbeli.GOL,
                   nonbeli.CBG,
                   nonbeli.PPH1,
                   nonbeli.PPH2,
                   nonbeli.PPH3,
                   nonbeli.MATERAI,
                   nonbeli.lain,
                   nonbeli.DISKON,
                   nonbeli.PPN,
                   nonbeli.NETT,
                   nonbelid.agenda,
                   nonbelid.tgl as tgluye,
                   nonbelid.ID,
                   nonbelid.rec,
                   nonbelid.acno,
                   nonbelid.ket,
                   nonbelid.total as totald
            FROM nonbeli, nonbelid, sup
            WHERE nonbeli.NO_BUKTI = nonbelid.no_bukti
              AND nonbeli.KODES = sup.KODES
              AND nonbeli.PER = :periode
              AND nonbeli.CBG = :cbg
              AND nonbeli.FLAG = 'NB'
            ORDER BY nonbeli.NO_BUKTI, nonbelid.rec ASC
        ", [
            'periode' => $periode,
            'cbg' => $CBG,
            'AKUN_BYR' => $form_info['akun_byr'],
            'AKUN_TLX' => $form_info['akun_tlx'],
            'na_toko' => $toko_info['na_toko'],
            'typ_pers' => $toko_info['typ_pers'],
            'alamat_pers' => $toko_info['alamat_pers']
        ]);

        // Mark all as printed
        DB::UPDATE("UPDATE nonbeli SET prnt = 1 WHERE PER = ? AND CBG = ? AND FLAG = 'NB'", [$periode, $CBG]);

        return $this->generateNonBeliReport($hero, 'frxNonBeli_All');
    }

    /**
     * Posting NonBeli payment instructions
     */
    public function posting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tgl_posting' => 'required|date',
            'cek' => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $tgl_posting = $request->tgl_posting;
        $bukti_list = $request->cek;

        try {
            DB::beginTransaction();

            foreach ($bukti_list as $no_bukti) {
                // Check if already posted
                $existing = DB::SELECT("SELECT posted FROM nonbeli WHERE no_bukti = ?", [$no_bukti]);

                if (!empty($existing) && $existing[0]->posted == 1) {
                    continue; // Skip already posted documents
                }

                // Update posting status for NonBeli
                DB::UPDATE(
                    "UPDATE nonbeli SET posted = 1, tgl_posted = ? WHERE no_bukti = ?",
                    [$tgl_posting, $no_bukti]
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data NonBeli berhasil diposting'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal posting data NonBeli: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unposting NonBeli payment instructions
     */
    public function unposting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cek' => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $bukti_list = $request->cek;

        try {
            DB::beginTransaction();

            foreach ($bukti_list as $no_bukti) {
                // Update unposting status for NonBeli
                DB::UPDATE(
                    "UPDATE nonbeli SET posted = 0, tgl_posted = NULL WHERE no_bukti = ?",
                    [$no_bukti]
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data NonBeli berhasil di-unposting'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal unposting data NonBeli: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get posted NonBeli data for verification
     */
    public function getTpNonDagang_posting(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;

        $nonbeli = DB::SELECT("SELECT NO_ID, no_bukti, tgl, posted, tgl_posted, usrnm, namas
                             FROM nonbeli
                             WHERE PER = '$periode' AND CBG = '$CBG' AND FLAG = 'NB' AND posted = 1
                             ORDER BY NO_BUKTI");

        return response()->json($nonbeli);
    }

    /**
     * Get agenda data for detail entry
     */
    public function getAgendaData(Request $request)
    {
        $tgl = $request->tgl ?? date('Y-m-d');
        $kodes = $request->kodes ?? '';

        if (empty($kodes)) {
            return response()->json(['error' => 'Kode supplier diperlukan'], 400);
        }

        // This could be expanded based on business logic for agenda data
        $agenda_data = [
            'tgl' => $tgl,
            'kodes' => $kodes,
            'agenda' => 'AUTO-' . date('Ymd', strtotime($tgl))
        ];

        return response()->json($agenda_data);
    }

    /**
     * Calculate totals based on detail items (equivalent to calculation logic in Delphi)
     */
    public function calculateTotals(Request $request)
    {
        $details = $request->details ?? [];
        $pph1_rate = floatval($request->pph1_rate ?? 0);
        $pph2_rate = floatval($request->pph2_rate ?? 0);
        $pph3_rate = floatval($request->pph3_rate ?? 0);
        $ppn_rate = floatval($request->ppn_rate ?? 0);
        $materai = floatval($request->materai ?? 0);
        $lain = floatval($request->lain ?? 0);
        $diskon = floatval($request->diskon ?? 0);

        $total_detail = 0;

        foreach ($details as $detail) {
            $total_detail += floatval($detail['total'] ?? 0);
        }

        // Calculate taxes
        $pph1 = $total_detail * ($pph1_rate / 100);
        $pph2 = $total_detail * ($pph2_rate / 100);
        $pph3 = $total_detail * ($pph3_rate / 100);
        $ppn = $total_detail * ($ppn_rate / 100);

        // Calculate net total
        $total = $total_detail + $ppn + $materai + $lain - $diskon;
        $nett = $total - $pph1 - $pph2 - $pph3;

        return response()->json([
            'total_detail' => $total_detail,
            'pph1' => $pph1,
            'pph2' => $pph2,
            'pph3' => $pph3,
            'ppn' => $ppn,
            'total' => $total,
            'nett' => $nett
        ]);
    }

    /**
     * Validate period status before operations
     */
    public function validatePeriod(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            return response()->json(['error' => 'Periode tidak ditemukan'], 400);
        }

        try {
            $posted = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), "Unknown column 'kd_peri'")) {
                $posted = null;
                return response()->json(['error' => "Kolom 'kd_peri' tidak ditemukan pada tabel perid."], 400);
            }
            throw $e;
        }
        if (!empty($posted) && $posted[0]->posted == 1) {
            return response()->json(['error' => 'Closed Period'], 400);
        }
        return response()->json(['status' => 'OK', 'message' => 'Periode aktif']);
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;

        $stats = DB::SELECT("
            SELECT
                COUNT(*) as total_transaksi,
                COUNT(CASE WHEN posted = 1 THEN 1 END) as total_posted,
                COUNT(CASE WHEN posted = 0 THEN 1 END) as total_unposted,
                SUM(nett) as total_nominal,
                SUM(CASE WHEN posted = 1 THEN nett ELSE 0 END) as nominal_posted,
                SUM(CASE WHEN posted = 0 THEN nett ELSE 0 END) as nominal_unposted
            FROM nonbeli
            WHERE PER = ? AND CBG = ? AND FLAG = 'NB'
        ", [$periode, $CBG]);

        return response()->json($stats[0] ?? []);
    }

    /**
     * Export to Excel format
     */
    public function exportExcel(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;

        $data = DB::SELECT("
            SELECT NO_BUKTI, TGL, JTEMPO, KODES, NAMAS, TOTAL, NETT, NOTES,
                   CASE WHEN posted = 1 THEN 'POSTED' ELSE 'UNPOSTED' END as status
            FROM nonbeli
            WHERE PER = '$periode' AND CBG = '$CBG' AND FLAG = 'NB'
            ORDER BY NO_BUKTI
        ");

        // Convert to CSV format for simple export
        $filename = 'nonbeli_' . str_replace('/', '_', $periode) . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['NO_BUKTI', 'TGL', 'JTEMPO', 'KODES', 'NAMAS', 'TOTAL', 'NETT', 'NOTES', 'STATUS']);

            // Add data rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->NO_BUKTI,
                    $row->TGL,
                    $row->JTEMPO,
                    $row->KODES,
                    $row->NAMAS,
                    $row->TOTAL,
                    $row->NETT,
                    $row->NOTES,
                    $row->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk operations for posting
     */
    public function bulkPosting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tgl_posting' => 'required|date',
            'filter_type' => 'required|in:all,range,selected',
            'bukti_list' => 'required_if:filter_type,selected|array',
            'tgl_dari' => 'required_if:filter_type,range|date',
            'tgl_sampai' => 'required_if:filter_type,range|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            return response()->json(['error' => 'Periode tidak ditemukan'], 400);
        }

        $CBG = Auth::user()->CBG;
        $tgl_posting = $request->tgl_posting;

        try {
            DB::beginTransaction();

            $query = "UPDATE nonbeli SET posted = 1, tgl_posted = ? WHERE PER = ? AND CBG = ? AND FLAG = 'NB' AND posted = 0";
            $params = [$tgl_posting, $periode, $CBG];

            if ($request->filter_type == 'range') {
                $query .= " AND TGL BETWEEN ? AND ?";
                $params[] = $request->tgl_dari;
                $params[] = $request->tgl_sampai;
            } elseif ($request->filter_type == 'selected') {
                $placeholders = str_repeat('?,', count($request->bukti_list) - 1) . '?';
                $query .= " AND NO_BUKTI IN ($placeholders)";
                $params = array_merge($params, $request->bukti_list);
            }

            $affected = DB::update($query, $params);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil posting $affected transaksi"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal bulk posting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Following Delphi logic - deletion might be restricted
     */
    public function destroy($no_id)
    {
        try {
            $nonbeli = DB::table('nonbeli')->where('NO_ID', $no_id)->first();

            if (!$nonbeli) {
                return redirect('/tpnondagang')->with('error', 'Data tidak ditemukan');
            }

            if ($nonbeli->POSTED == 1) {
                return redirect('/tpnondagang')->with('error', 'Data sudah diposting, tidak bisa dihapus');
            }

            DB::beginTransaction();

            // Delete detail records first
            DB::table('nonbelid')->where('NO_BUKTI', $nonbeli->NO_BUKTI)->delete();

            // Delete header record
            DB::table('nonbeli')->where('NO_ID', $no_id)->delete();

            DB::commit();

            return redirect('/tpnondagang')->with('status', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect('/tpnondagang')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Index for posting view
     */
    public function index_posting(Request $request)
    {
        return view("otransaksi_TPNonDagang.index_posting")->with([
            'judul' => $this->judul . ' - Posting',
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Autocomplete for supplier search
     */
    public function autocompleteSupplier(Request $request)
    {
        $search = $request->q ?? '';
        $limit = $request->limit ?? 10;

        $suppliers = DB::SELECT("
            SELECT kodes as id, CONCAT(kodes, ' - ', namas) as text
            FROM sup
            WHERE (kodes LIKE ? OR namas LIKE ?)
            ORDER BY kodes
            LIMIT ?
        ", ["%$search%", "%$search%", $limit]);

        return response()->json($suppliers);
    }

    /**
     * Autocomplete for account search
     */
    public function autocompleteAccount(Request $request)
    {
        $search = $request->q ?? '';
        $limit = $request->limit ?? 10;

        $accounts = DB::SELECT("
            SELECT acno as id, CONCAT(acno, ' - ', nama) as text
            FROM account
            WHERE (acno LIKE ? OR nama LIKE ?)
            ORDER BY acno
            LIMIT ?
        ", ["%$search%", "%$search%", $limit]);

        return response()->json($accounts);
    }
}