<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TFormBayarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = 'Instruksi Pembayaran';
    var $FLAGZ = 'TB';

    function setFlag(Request $request)
    {
        $this->judul = "Instruksi Pembayaran";
        $this->FLAGZ = 'TB';
    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        return view("otransaksi_TFormBayar.index")->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Display payment instruction data (equivalent to Tampil procedure)
     */
    public function getTagi(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;

        // Main query equivalent to Delphi's Tampil procedure
        $tagi = DB::SELECT("SELECT NO_ID, no_bukti, NO_TRANS, tgl, penagih, notes, total, posted, usrnm, namas, klb, PRNT
                           FROM tagi
                           WHERE PER = '$periode'
                           ORDER BY NO_BUKTI");

        return Datatables::of($tagi)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    $btnEdit = ($row->posted == 1) ?
                        ' onclick= "alert(\'Transaksi ' . $row->no_bukti . ' sudah diposting!\')" href="#" ' :
                        ' href="tformbayar/edit/?idx=' . $row->NO_ID . '&tipx=edit"';

                    $btnDelete = ($row->posted == 1) ?
                        ' onclick= "alert(\'Transaksi ' . $row->no_bukti . ' sudah diposting!\')" href="#" ' :
                        ' onclick="deleteRow(\'' . url("tformbayar/delete/" . $row->NO_ID) . '\')" ';

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
            ->rawColumns(['action', 'cek'])
            ->make(true);
    }

    /**
     * Alias method for getTagi to match route naming convention
     */
    public function gettformbayar(Request $request)
    {
        return $this->getTagi($request);
    }

    /**
     * Get detail data for payment instruction (equivalent to detail query)
     */
    public function getDetailtformbayar(Request $request)
    {
        $no_bukti = $request->no_bukti;

        if (empty($no_bukti)) {
            return response()->json([]);
        }

        $detail = DB::SELECT("
            SELECT no_trm, tgl_trm, ket, TOTAL, notes, jns, no_sp, no_pjk
            FROM tagid
            WHERE NO_BUKTI = '$no_bukti'
            ORDER BY tgl_trm ASC, no_trm ASC
        ");

        return response()->json($detail);
    }
    /**
     * Print single payment instruction (equivalent to p1Click)
     */
    public function printSingle(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $usrnm = Auth::user()->username;

        if (empty($no_bukti)) {
            return response()->json(['error' => 'No bukti tidak boleh kosong'], 400);
        }

        // Check period posting status
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $cekperid = DB::SELECT("SELECT POSTED from perid WHERE PERIO='$periode'");
        if (!empty($cekperid) && $cekperid[0]->POSTED == 1) {
            return response()->json(['error' => 'Periode sudah ditutup'], 400);
        }

        // Form information retrieval
        $no_form = $this->getFormInfo();
        $toko_info = $this->getTokoInfo();

        // Main payment instruction query equivalent to Delphi's hero.sql.Text
        $hero = DB::SELECT("
            SELECT sup.EMAIL,
                   (SELECT upper(database())) as CBG,
                   :no_form as no_form,
                   :na_toko as na_toko,
                   :typ_pers as typ_pers,
                   :alamat_pers as alamat_pers,
                   :usrnm as usrnm,

                   -- Bank code logic equivalent to Delphi IF statements
                   IF(tagi.BYR='API', '014',
                      IF(tagi.KD_BANK NOT IN ('008','009','014','011','013','022'),
                         IF(tagi.KD_BANK='015', '022', '011'),
                         tagi.KD_BANK)) as X,

                   -- Account number generation
                   IF(tagi.BYR='API' OR tagi.BYR='TRF',
                      CONCAT(tagi.KD_BANK_BAYAR,
                             RIGHT(LEFT(TRIM(tagi.NO_BUKTI), 6), 4),
                             RIGHT(TRIM(tagi.NO_BUKTI), 4)),
                      '') as nom,

                   tagi.tgl,
                   (@buk:=tagi.no_bukti) as bukit,

                   -- Due date calculation with KLB (equivalent to Delphi JTEMPO calculation)
                   COALESCE(
                       xx_jtempo((SELECT DATE_ADD(MIN(TGL_TRM), INTERVAL
                                        (DATEDIFF(MAX(TGL_TRM), MIN(TGL_TRM))/2)+klb DAY) AS JTEMPO
                                 FROM TAGID
                                 WHERE NO_BUKTI=bukit AND (jns='BL' OR jns='B3' OR jns='B5' OR jns='B8'))),
                       xx_jtempo(tagi.TGL)
                   ) AS JTEMPO,

                   tagi.no_bukti, tagi.notes, tagi.nama_b, tagi.penagih,
                   tagid.no_trm, tagid.tgl_trm, tagid.ket,
                   tagi.kodes, tagi.retur, tagi.lain, tagi.badm, tagid.TOTAL0,

                   -- Tax deduction logic
                   IF(LENGTH(NO_PJK) > 5, RIGHT(NO_PJK, 8), '') AS POT,

                   tagid.TOTAL, tagi.namas, tagi.kota,
                   tagi.alamat, tagi.golongan, tagi.byr, tagi.klb,
                   tagi.norek, tagi.anb, tagi.tgl_trf, tagi.type,

                   -- Transfer fee note
                   IF(badm > 0, 'Dipotong Biaya Transfer', '') as ketby,

                   UPPER(tagid.no_sp) as no_sp, tagid.no_pjk,
                   tagi.LAIN as lainh, tagi.NO_TRANS, tagi.prom as bprom,
                   tagi.total as totalall, tagid.jns, sup.gol_brg,

                   -- Tax total calculation
                   (SELECT SUM(tagid.pot) FROM tagid WHERE jns='TL' AND no_bukti=bukit) as potpn,

                   -- Bank name mapping
                   tagi.NA_BANK_BAYAR as AGENG_GANTENG

            FROM tagi, tagid, sup
            JOIN (SELECT @buk:='') as ini ON 1=1
            WHERE tagi.no_bukti = tagid.no_bukti
              AND tagi.kodes = sup.kodes
              AND tagi.no_bukti = :no_bukti
              AND (jns='BL' OR jns='B8' OR jns='B3' OR jns='B5')
            ORDER BY tagi.no_bukti, tagid.tgl_trm ASC, tagid.no_trm ASC
        ", [
            'no_bukti' => $no_bukti,
            'usrnm' => $usrnm,
            'no_form' => $no_form,
            'na_toko' => $toko_info['na_toko'],
            'typ_pers' => $toko_info['typ_pers'],
            'alamat_pers' => $toko_info['alamat_pers']
        ]);

        if (empty($hero)) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Update JTEMPO for each record (equivalent to Delphi while loop)
        foreach ($hero as $record) {
            DB::UPDATE("UPDATE tagi SET jtempo = ? WHERE no_bukti = ?", [
                $record->JTEMPO,
                $record->no_bukti
            ]);
        }

        // Check if there are billable items (equivalent to Delphi cekNota logic)
        $cekNota = 0;
        foreach ($hero as $record) {
            if (in_array($record->jns, ['B3', 'B5', 'B8', 'BL'])) {
                $cekNota++;
            }
        }

        // Mark as printed
        DB::UPDATE("UPDATE tagi SET prnt = 1 WHERE no_bukti = ?", [$no_bukti]);

        // Generate report based on cekNota
        if ($cekNota > 0) {
            return $this->generateReport($hero, 'frxBayar');
        } else {
            return $this->generateReport($hero, 'frxBayar_kosong');
        }
    }

    /**
     * Print range of payment instructions (equivalent to Button1Click)
     */
    public function printRange(Request $request)
    {
        $txtbukti1 = trim($request->txtbukti1);
        $txtbukti2 = trim($request->txtbukti2);
        $cbKertas = $request->cbKertas ?? 'PENDEK';

        if (empty($txtbukti1) || empty($txtbukti2)) {
            return response()->json(['error' => 'Range bukti tidak boleh kosong'], 400);
        }

        // Check if first character matches (equivalent to Delphi leftstr validation)
        if (substr($txtbukti1, 0, 1) !== substr($txtbukti2, 0, 1)) {
            return response()->json(['error' => 'Tydacc bisa gitu....'], 400);
        }

        $usrnm = Auth::user()->username;

        // Check if it's F-type voucher (equivalent to Delphi F check)
        if (substr($txtbukti1, 0, 1) === 'F') {
            return $this->printFTypeVouchers($txtbukti1, $txtbukti2);
        }

        // Form information retrieval
        $no_form = $this->getFormInfo();
        $toko_info = $this->getTokoInfo();

        // Main range query
        $hero = DB::SELECT("
            SELECT sup.EMAIL,
                   (SELECT upper(database())) as CBG,
                   :no_form as no_form,
                   :na_toko as na_toko,
                   :typ_pers as typ_pers,
                   :alamat_pers as alamat_pers,
                   :usrnm as usrnm,

                   IF(tagi.BYR='API', '014',
                      IF(tagi.KD_BANK NOT IN ('008','009','014','011','013','022'),
                         IF(tagi.KD_BANK='015', '022', '011'),
                         tagi.KD_BANK)) as X,

                   IF(tagi.BYR='API' OR tagi.BYR='TRF',
                      CONCAT(tagi.KD_BANK_BAYAR,
                             RIGHT(LEFT(TRIM(tagi.NO_BUKTI), 6), 4),
                             RIGHT(TRIM(tagi.NO_BUKTI), 4)),
                      '') as nom,

                   tagi.tgl,
                   (@buk:=tagi.no_bukti) as bukit,

                   COALESCE(
                       xx_jtempo((SELECT DATE_ADD(MIN(TGL_TRM), INTERVAL
                                        (DATEDIFF(MAX(TGL_TRM), MIN(TGL_TRM))/2)+klb DAY) AS JTEMPO
                                 FROM TAGID
                                 WHERE NO_BUKTI=bukit AND (jns='BL' OR jns='B3' OR jns='B5' OR jns='B8'))),
                       xx_jtempo(tagi.TGL)
                   ) AS JTEMPO,

                   tagi.no_bukti, tagi.notes, tagi.nama_b, tagi.penagih,
                   tagid.no_trm, tagid.tgl_trm, tagid.ket,
                   tagi.kodes, tagi.retur, tagi.lain, tagi.badm, tagid.TOTAL0,

                   IF(LENGTH(NO_PJK) > 5, RIGHT(NO_PJK, 8), '') AS POT,

                   tagid.TOTAL, tagi.namas, tagi.kota,
                   tagi.alamat, tagi.golongan, tagi.byr, tagi.klb,
                   tagi.norek, tagi.anb, tagi.tgl_trf, tagi.type,

                   IF(badm > 0, 'Dipotong Biaya Transfer', '') as ketby,

                   UPPER(tagid.no_sp) as no_sp, tagid.no_pjk,
                   tagi.LAIN as lainh, tagi.NO_TRANS, tagi.prom as bprom,
                   tagi.total as totalall, tagid.jns, sup.gol_brg,

                   (SELECT SUM(tagid.pot) FROM tagid WHERE jns='TL' AND no_bukti=bukit) as potpn,

                   tagi.NA_BANK_BAYAR as AGENG_GANTENG

            FROM tagi, tagid, sup
            JOIN (SELECT @buk:='') as ini ON 1=1
            WHERE tagi.no_bukti = tagid.no_bukti
              AND tagi.kodes = sup.kodes
              AND tagi.no_bukti BETWEEN :txtbukti1 AND :txtbukti2
              AND (jns='BL' OR jns='B8' OR jns='B3' OR jns='B5')
            ORDER BY tagi.no_bukti, tagid.tgl_trm ASC, tagid.no_trm ASC
        ", [
            'txtbukti1' => $txtbukti1,
            'txtbukti2' => $txtbukti2,
            'usrnm' => $usrnm,
            'no_form' => $no_form,
            'na_toko' => $toko_info['na_toko'],
            'typ_pers' => $toko_info['typ_pers'],
            'alamat_pers' => $toko_info['alamat_pers']
        ]);

        // Update JTEMPO for each record
        foreach ($hero as $record) {
            DB::UPDATE("UPDATE tagi SET jtempo = ? WHERE no_bukti = ?", [
                $record->JTEMPO,
                $record->no_bukti
            ]);
        }

        // Mark range as printed
        DB::UPDATE("UPDATE tagi SET prnt = 1 WHERE no_bukti BETWEEN ? AND ?", [$txtbukti1, $txtbukti2]);

        // Set paper size
        $paperHeight = ($cbKertas === 'PANJANG') ? 279.0 : 139.5;

        return $this->generateReport($hero, 'frxBayar', $paperHeight);
    }

    /**
     * Handle F-type voucher printing (equivalent to Delphi F-type logic)
     */
    private function printFTypeVouchers($txtbukti1, $txtbukti2)
    {
        $hero = DB::SELECT("
            SELECT ppn, no_bukti, no_trans, tgl, kodes, namas, '26.008.000' as acno,
                   KLB, DATE(NOW()) AS TINGGIL, (@buk:=no_bukti) as bukit,
                   (SELECT MIN(tgl_trm) FROM TAGID WHERE NO_BUKTI=bukit) AS tglmin,
                   (SELECT MAX(tgl_trm) FROM TAGID WHERE NO_BUKTI=bukit) AS tglmax
            FROM tagi
            JOIN (SELECT @buk:='') as ini ON 1=1
            WHERE TRIM(NO_BUKTI) <> '+'
              AND no_trans BETWEEN :X1 AND :X2
            ORDER BY no_bukti
        ", [
            'X1' => $txtbukti1,
            'X2' => $txtbukti2
        ]);

        return $this->generateReport($hero, 'frxPotong');
    }

    /**
     * Get form information (equivalent to Delphi form info retrieval)
     */
    private function getFormInfo()
    {
        // This would typically come from a form management system
        return 'T-AKK1-055.1';
    }

    /**
     * Get store/company information (equivalent to Delphi toko info)
     */
    private function getTokoInfo()
    {
        $CBG = Auth::user()->CBG;

        // This would typically retrieve store information from database
        $toko = DB::SELECT("SELECT * FROM toko WHERE CBG = ?", [$CBG]);

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
     * Generate report using PHPJasperXML (equivalent to Delphi frx reports)
     */
    private function generateReport($data, $reportType, $paperHeight = null)
    {
        $PHPJasperXML = new PHPJasperXML();

        // Load appropriate report template
        $reportFile = $this->getReportFile($reportType);
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/' . $reportFile);

        // Convert data to array format
        $reportData = [];
        foreach ($data as $record) {
            $reportData[] = (array) $record;
        }

        $PHPJasperXML->setData($reportData);

        // Set paper height if specified
        if ($paperHeight) {
            // This would be implemented in the specific report template
        }

        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * Get report file based on type
     */
    private function getReportFile($reportType)
    {
        $reportFiles = [
            'frxBayar' => 'instruksi_pembayaran.jrxml',
            'frxBayar_kosong' => 'instruksi_pembayaran_kosong.jrxml',
            'frxPotong' => 'potong_pajak.jrxml',
            'frxbayar_separo' => 'instruksi_pembayaran_separo.jrxml'
        ];

        return $reportFiles[$reportType] ?? 'instruksi_pembayaran.jrxml';
    }


    //////////////////////////////////////////////////////////////////////////////////

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'TGL' => 'required',
                'KODES' => 'required',
                'USRNM' => 'required'
            ]
        );

        //////     nomer otomatis
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;
        $bulan    = session()->get('periode')['bulan'];
        $tahun    = substr(session()->get('periode')['tahun'], -2);

        $query = DB::table('tagi')->select('NO_BUKTI')->where('PER', $periode)->where('FLAG', 'TG')->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'TG' . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = 'TG' . $tahun . $bulan . '-0001';
        }

        // Insert Header
        DB::table('tagi')->insert(
            [
                'NO_BUKTI' => $no_bukti,
                'TGL' => date('Y-m-d', strtotime($request['TGL'])),
                'PER' => $periode,
                'KODES' => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS' => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'FLAG' => 'TG',
                'CBG' => $CBG,
                'USRNM' => Auth::user()->username,
                'TG_SMP' => Carbon::now(),
                'created_by' => Auth::user()->username,
                'created_at' => Carbon::now()
            ]
        );

        $no_id = DB::getPdo()->lastInsertId();
        return redirect('/tformbayar')->with('status', 'Data berhasil ditambahkan');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('otransaksi_TFormBayar.create');
    }

    /**
     * Display the specified resource.
     */
    public function show($no_id)
    {
        $header = DB::table('tagi')->where('NO_ID', $no_id)->first();
        $detail = DB::table('tagid')->where('NO_BUKTI', $header->NO_BUKTI)->get();

        return view('otransaksi_TFormBayar.show', compact('header', 'detail'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($no_id = null)
    {
        $tipx = request()->tipx;
        $idx = request()->idx ?? $no_id;
        $flagz = request()->flagz ?? 'TB';

        // If no_id is not passed as parameter, try to get it from request
        if ($no_id === null) {
            $no_id = request()->idx ?? 0;
        }

        if ($tipx == 'new') {
            // For new records, create empty objects
            $header = (object) [
                'NO_ID' => 0,
                'NO_BUKTI' => '',
                'TGL' => date('Y-m-d'),
                'KODES' => '',
                'NAMAS' => '',
                'NOTES' => '',
                'POSTED' => 0,
                'TRANSFER_KE' => '',
                'BANK' => '',
                'PEMBAYARAN' => '',
                'CARA_BAYAR' => '',
                'KLB' => 0,
                'NO_TANDA_TERIMA' => '',
                'TYPE' => 'ALL'
            ];
            $detail = collect(); // Empty collection for new records
        } else {
            $header = DB::table('tagi')->where('NO_ID', $no_id)->first();
            if ($header && $header->NO_BUKTI) {
                $detail = DB::table('tagid')->where('NO_BUKTI', $header->NO_BUKTI)->orderBy('REC')->get();
            } else {
                $detail = collect(); // Empty collection if no header or no bukti
            }
        }

        return view('otransaksi_TFormBayar.edit', compact('header', 'detail', 'tipx', 'idx', 'flagz'));
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
                'KODES' => 'required'
            ]
        );

        // Update Header
        DB::table('tagi')->where('NO_ID', $no_id)->update(
            [
                'TGL' => date('Y-m-d', strtotime($request['TGL'])),
                'KODES' => ($request['KODES'] == null) ? "" : $request['KODES'],
                'NAMAS' => ($request['NAMAS'] == null) ? "" : $request['NAMAS'],
                'USRNM' => Auth::user()->username,
                'TG_SMP' => Carbon::now(),
                'updated_by' => Auth::user()->username,
                'updated_at' => Carbon::now()
            ]
        );

        return redirect('/tformbayar')->with('status', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($no_id)
    {
        // Get the header data first
        $tagi = DB::table('tagi')->where('NO_ID', $no_id)->first();

        if (!$tagi) {
            return redirect('/tformbayar')->with('error', 'Data tidak ditemukan');
        }

        // Delete detail records first
        DB::table('tagid')->where('NO_BUKTI', $tagi->NO_BUKTI)->delete();

        // Delete header
        DB::table('tagi')->where('NO_ID', $no_id)->delete();

        return redirect('/tformbayar')->with('status', 'Data berhasil dihapus');
    }

    /**
     * Posting function for payment instructions (equivalent to Delphi posting)
     */
    public function posting(Request $request)
    {
        $validate = $request->validate([
            'tgl_posting' => 'required|date',
            'cek' => 'required'
        ]);

        $tgl_posting = $request->tgl_posting;
        $user = Auth::user()->username;

        if (!empty($request->cek)) {
            foreach ($request->cek as $id) {
                $tagi = DB::table('tagi')->where('NO_ID', $id)->first();

                if ($tagi && $tagi->POSTED == 0) {
                    // Update posting status
                    DB::table('tagi')->where('NO_ID', $id)->update([
                        'POSTED' => 1,
                        'TGL_POSTING' => $tgl_posting,
                        'USRNM_POSTING' => $user,
                        'updated_by' => $user,
                        'updated_at' => Carbon::now()
                    ]);

                    // Create general ledger entries if needed
                    $this->createJurnalEntries($tagi);
                }
            }
            return redirect('/tformbayar')->with('status', 'Posting berhasil dilakukan');
        } else {
            return redirect('/tformbayar')->with('error', 'Tidak ada data yang dipilih untuk posting');
        }
    }

    /**
     * Create journal entries for posted payment instructions
     */
    private function createJurnalEntries($tagi)
    {
        // Get accounting configuration
        $CBG = Auth::user()->CBG;

        // Basic journal entry for payment instruction
        $periode = explode('/', $tagi->PER);
        $bulan = str_pad($periode[0], 2, '0', STR_PAD_LEFT);
        $tahun = $periode[1];

        // Create debit entry (Accounts Payable)
        $this->createJurnalEntry([
            'NO_BUKTI' => $tagi->NO_BUKTI,
            'TGL' => $tagi->TGL,
            'KETERANGAN' => 'Instruksi Pembayaran ' . $tagi->NO_BUKTI,
            'KD_BRG' => '',
            'DEBET' => $tagi->TOTAL,
            'KREDIT' => 0,
            'PER' => $tagi->PER,
            'CBG' => $CBG
        ]);

        // Create credit entry (Cash/Bank)
        $this->createJurnalEntry([
            'NO_BUKTI' => $tagi->NO_BUKTI,
            'TGL' => $tagi->TGL,
            'KETERANGAN' => 'Instruksi Pembayaran ' . $tagi->NO_BUKTI,
            'KD_BRG' => '',
            'DEBET' => 0,
            'KREDIT' => $tagi->TOTAL,
            'PER' => $tagi->PER,
            'CBG' => $CBG
        ]);
    }

    /**
     * Helper function to create journal entry
     */
    private function createJurnalEntry($data)
    {
        DB::table('jur')->insert([
            'NO_BUKTI' => $data['NO_BUKTI'],
            'TGL' => $data['TGL'],
            'URAIAN' => $data['KETERANGAN'],
            'DEBET' => $data['DEBET'],
            'KREDIT' => $data['KREDIT'],
            'PER' => $data['PER'],
            'CBG' => $data['CBG'],
            'USRNM' => Auth::user()->username,
            'TG_SMP' => Carbon::now(),
            'created_by' => Auth::user()->username,
            'created_at' => Carbon::now()
        ]);
    }

    /**
     * Unposting function for payment instructions
     */
    public function unposting(Request $request)
    {
        $validate = $request->validate([
            'cek' => 'required'
        ]);

        $user = Auth::user()->username;

        if (!empty($request->cek)) {
            foreach ($request->cek as $id) {
                $tagi = DB::table('tagi')->where('NO_ID', $id)->first();

                if ($tagi && $tagi->POSTED == 1) {
                    // Check if period is still open
                    $periode = $tagi->PER;
                    $cekperid = DB::table('perid')->where('PERIO', $periode)->first();

                    if ($cekperid && $cekperid->POSTED == 1) {
                        return redirect('/tformbayar')->with('error', 'Periode sudah ditutup, tidak bisa unposting');
                    }

                    // Update unposting status
                    DB::table('tagi')->where('NO_ID', $id)->update([
                        'POSTED' => 0,
                        'TGL_POSTING' => null,
                        'USRNM_POSTING' => null,
                        'updated_by' => $user,
                        'updated_at' => Carbon::now()
                    ]);

                    // Delete related journal entries
                    DB::table('jur')->where('NO_BUKTI', $tagi->NO_BUKTI)->delete();
                }
            }
            return redirect('/tformbayar')->with('status', 'Unposting berhasil dilakukan');
        } else {
            return redirect('/tformbayar')->with('error', 'Tidak ada data yang dipilih untuk unposting');
        }
    }

    /**
     * Print report for payment instructions (equivalent to Button2Click with PANJANG paper)
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
            SELECT NO_BUKTI, TGL, KODES, NAMAS, TOTAL, NOTES, KLB, BYR
            FROM tagi
            WHERE PER = '$periode' AND FLAG = 'TG'
            ORDER BY NO_BUKTI
        ");

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $value->NO_BUKTI,
                'TGL' => $value->TGL,
                'KODES' => $value->KODES,
                'NAMAS' => $value->NAMAS,
                'TOTAL' => $value->TOTAL,
                'NOTES' => $value->NOTES,
                'KLB' => $value->KLB,
                'BYR' => $value->BYR,
                'JUDUL' => $judul,
                'CBG' => $CBG
            ));
        }

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/instruksi_pembayaran_laporan.jrxml');

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
}