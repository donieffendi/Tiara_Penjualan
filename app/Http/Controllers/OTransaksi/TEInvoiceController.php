<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TEInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = 'Invoice Pelanggan';
    var $FLAGZ = 'IJ';

    function setFlag(Request $request)
    {
        $this->judul = "Invoice Pelanggan";
        $this->FLAGZ = 'IJ';
    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        return view("otransaksi_TEInvoice.index")->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Display Invoice data (equivalent to Delphi tampil procedure)
     */
    public function getInvoicePlg(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;
        $mon = substr($periode, 0, 2); // Get month part for dynamic table

        // Main query equivalent to Delphi's tampil procedure for Invoice
        $invoice = DB::SELECT("
            SELECT A.NO_BUKTI, A.NO_SJ, A.KODEC, A.NAMAC,
                   SUM(A.totala) as TOTAL, DATE(B.TG_SMP) as TGL,
                   B.USERX, B.NAMA, B.ALAMAT, B.NO_FAKTUR,
                   CASE WHEN A.NO_SJ IS NOT NULL AND A.NO_SJ <> '' THEN 1 ELSE 0 END as has_invoice
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.PER = ? AND A.CBG = ?
            GROUP BY A.NO_SJ, A.KODEC, A.NAMAC
            ORDER BY A.NO_BUKTI DESC
        ", [$periode, $CBG]);

        return Datatables::of($invoice)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    $btnEdit = ' href="invoice-plg/edit/?idx=' . $row->NO_BUKTI . '&tipx=edit"';
                    $btnDelete = ' onclick="deleteRow(\'' . url("invoice-plg/delete/" . $row->NO_BUKTI) . '\')" ';

                    $btnPrivilege = '
                        <a class="dropdown-item" ' . $btnEdit . '>
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a class="dropdown-item" href="#" onclick="printSingle(\'' . $row->NO_BUKTI . '\')">
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
            ->addColumn('status', function ($row) {
                return $row->has_invoice ? '<span class="badge badge-success">Sudah Invoice</span>' : '<span class="badge badge-warning">Belum Invoice</span>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    /**
     * Get available invoices for selection (equivalent to btnMenuClick in Delphi)
     */
    public function getAvailableInvoices(Request $request)
    {
        $per = $request->per;
        $mon = substr($per, 0, 2);

        if (empty($per)) {
            return response()->json([]);
        }

        // Query equivalent to Delphi's com2 query in btnMenuClick
        $invoices = DB::SELECT("
            SELECT A.NO_BUKTI, A.NO_SJ, A.KODEC, SUM(A.totala) as TOTALX,
                   DATE(B.TG_SMP) as TGLX, B.USERX
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.NO_SJ <> '' AND A.PER = ?
            GROUP BY A.NO_SJ
            ORDER BY A.NO_SJ
        ", [$per]);

        return response()->json($invoices);
    }

    /**
     * Get invoice details by bukti (equivalent to tampil procedure in Delphi)
     */
    public function getInvoiceByBukti(Request $request)
    {
        $bukti = $request->bukti;
        $per = $request->per;
        $mon = substr($per, 0, 2);

        if (empty($bukti) || empty($per)) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        // Query equivalent to Delphi's tampil procedure
        $details = DB::SELECT("
            SELECT A.no_bukti, A.tgl, A.totala, A.ppn, A.dpp, A.no_SJ,
                   A.kodec, A.NAMAC, A.ALAMAT, '' as NAMA
            FROM jual{$mon} A
            WHERE A.no_bukti = ?
            ORDER BY A.TGL ASC
        ", [$bukti]);

        if (empty($details)) {
            return response()->json(['error' => 'Tidak ditemukan, atau salah kode!'], 404);
        }

        return response()->json($details);
    }

    /**
     * Get customer data by code (equivalent to txtkdlangKeyUp in Delphi)
     */
    public function getCustomerByCode(Request $request)
    {
        $kd = trim($request->kd);

        if (empty($kd)) {
            return response()->json(['error' => 'Kode pelanggan tidak boleh kosong'], 400);
        }

        // Query equivalent to cim query in Delphi
        $customer = DB::SELECT("
            SELECT NAMA as nam, ALAMAT as alm
            FROM cusx_fakt
            WHERE KDLANG = ?
        ", [$kd]);

        if (empty($customer)) {
            return response()->json(['error' => 'Tidak ditemukan, atau salah kode pelanggan!'], 404);
        }

        return response()->json($customer[0]);
    }

    /**
     * Get member CRM data (equivalent to member_crm query in Delphi)
     */
    public function getMemberCrm(Request $request)
    {
        $kodec = $request->kodec;

        if (empty($kodec)) {
            return response()->json(['error' => 'Kode member tidak boleh kosong'], 400);
        }

        $member = DB::SELECT("
            SELECT KODEC, NAMAC, ALAMAT, NPWP
            FROM member_crm
            WHERE KODEC = ?
        ", [$kodec]);

        if (empty($member)) {
            return response()->json(['error' => 'Tidak ditemukan, atau salah kode member!'], 404);
        }

        return response()->json($member[0]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check period posting status
        if (request()->session()->has('periode')) {
            $periode = request()->session()->get('periode')['bulan'] . '/' . request()->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $posted = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);

        if (!empty($posted) && $posted[0]->posted == 1) {
            return redirect('/invoice-plg')->with('error', 'Closed Period');
        }

        return view('otransaksi_TEInvoice.edit');
    }

    /**
     * Show the form for editing the specified resource.
     * Equivalent to cxGrid1DBTableView1DblClick procedure in Delphi
     */
    public function edit($no_bukti = null)
    {
        $tipx = request()->tipx;
        $idx = request()->idx ?? $no_bukti;

        if ($no_bukti === null) {
            $no_bukti = request()->idx ?? '';
        }

        $readonly_mode = false;

        if ($tipx == 'new') {
            // Check period status before creating new
            if (request()->session()->has('periode')) {
                $periode = request()->session()->get('periode')['bulan'] . '/' . request()->session()->get('periode')['tahun'];
            } else {
                $periode = '';
            }

            $posted = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);

            if (!empty($posted) && $posted[0]->posted == 1) {
                return redirect('/invoice-plg')->with('error', 'Closed Period');
            }

            // For new records
            $header = (object) [
                'NO_BUKTI' => '',
                'NO_SJ' => '',
                'TGL' => date('Y-m-d'),
                'KODEC' => '',
                'NAMAC' => '',
                'ALAMAT' => '',
                'NAMA' => '',
                'TOTAL' => 0,
                'PPN' => 0,
                'DPP' => 0
            ];
            $detail = collect();
        } else {
            // Load existing data (equivalent to edit mode in Delphi)
            $mon = substr(session()->get('periode')['bulan'], 0, 2);

            // Get invoice data based on NO_SJ
            $invoice_data = DB::SELECT("
                SELECT KODEC, NAMAC, NAMA, ALAMAT
                FROM member_crm
                WHERE KODEC = (
                    SELECT KODEC FROM jual{$mon} WHERE NO_SJ = ? LIMIT 1
                )
            ", [$no_bukti]);

            if (empty($invoice_data)) {
                return redirect('/invoice-plg')->with('error', 'Data tidak ditemukan');
            }

            $member_data = $invoice_data[0];

            // Get detail transactions
            $detail_raw = DB::SELECT("
                SELECT A.no_bukti, A.kodec, A.NAMAC, B.NAMA, B.ALAMAT,
                       A.tgl, A.totala, A.ppn, A.dpp, A.no_SJ
                FROM jual{$mon} A, tg_inv B
                WHERE A.NO_SJ = B.NO_BUKTI AND A.NO_SJ = ? AND A.PER = ?
                ORDER BY A.TGL ASC
            ", [$no_bukti, session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun']]);

            $header = (object) [
                'NO_BUKTI' => $no_bukti,
                'NO_SJ' => $no_bukti,
                'KODEC' => $member_data->KODEC,
                'NAMAC' => $member_data->NAMAC,
                'NAMA' => $member_data->NAMA,
                'ALAMAT' => $member_data->ALAMAT
            ];

            $detail = collect($detail_raw);
        }

        return view('otransaksi_TEInvoice.edit', compact('header', 'detail', 'tipx', 'idx', 'readonly_mode'));
    }

    /**
     * Generate invoice number (equivalent to NO_TRANSX procedure in Delphi)
     */
    private function generateInvoiceNumber()
    {
        $transx = 'INVOICE_KASIR';
        $cbg = Auth::user()->CBG;
        $form_name = 'frmInvoice_plg';
        $versi = 'IJ';

        // Insert transaction type if not exists
        DB::statement("
            INSERT INTO notrans (PER, TRANS)
            SELECT YEAR(NOW()), ? WHERE NOT EXISTS
            (SELECT TRANS FROM notrans WHERE TRANS = ? LIMIT 1)
        ", [$transx, $transx]);

        // Call stored procedure equivalent
        $result = DB::SELECT("
            CALL NO_TRANSX(?, ?, ?, ?, ?)
        ", [$transx, $form_name, $cbg, $versi, 'IJ']);

        if (empty($result)) {
            throw new \Exception('Create NO.BUKTI bermasalah! x539');
        }

        return $result[0]->BUKTIX;
    }

    /**
     * Store a newly created resource in storage.
     * Equivalent to btnSimpanClick procedure in Delphi
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'ALAMAT' => 'required',
            'NAMA' => 'required',
            'selected_invoices' => 'required|array'
        ]);

        if (empty(trim($request->ALAMAT))) {
            return back()->with('error', 'Alamat tidak boleh kosong!');
        }

        if (empty($request->selected_invoices)) {
            return back()->with('error', 'Isi data terlebih dahulu!');
        }

        try {
            DB::beginTransaction();

            // Generate invoice number
            $no_bukti = $this->generateInvoiceNumber();
            $mon = substr(session()->get('periode')['bulan'], 0, 2);

            // Update selected transactions with invoice number
            foreach ($request->selected_invoices as $bukti) {
                if ($bukti['cek'] == 1) {
                    DB::UPDATE("
                        UPDATE jual{$mon}
                        SET NO_SJ = ?
                        WHERE NO_BUKTI = ?
                    ", [$no_bukti, $bukti['no_bukti']]);
                }
            }

            // Insert into tg_inv
            DB::INSERT("
                INSERT INTO tg_inv
                (NO_BUKTI, FLAG, USERX, TG_SMP, TGL_FAKTUR, ALAMAT, NAMA)
                VALUES (?, 'JL', ?, NOW(), CURDATE(), ?, ?)
            ", [
                $no_bukti,
                Auth::user()->username,
                trim($request->ALAMAT),
                trim($request->NAMA)
            ]);

            DB::commit();

            return redirect('/invoice-plg')->with('success', 'Save Data Success. No.Invoice: ' . $no_bukti);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $no_bukti)
    {
        $this->validate($request, [
            'ALAMAT' => 'required',
            'NAMA' => 'required'
        ]);

        try {
            DB::beginTransaction();

            // Update tg_inv
            DB::UPDATE("
                UPDATE tg_inv
                SET ALAMAT = ?, NAMA = ?, TG_SMP = NOW()
                WHERE NO_BUKTI = ?
            ", [
                trim($request->ALAMAT),
                trim($request->NAMA),
                $no_bukti
            ]);

            DB::commit();

            return redirect('/invoice-plg')->with('success', 'Data berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal update data: ' . $e->getMessage());
        }
    }

    /**
     * Print invoice report (equivalent to RekapClick in Delphi)
     */
    public function printInvoice(Request $request)
    {
        $selected_bukti = $request->selected_bukti;

        if (empty($selected_bukti)) {
            return response()->json(['error' => 'Pilih data yang akan dicetak'], 400);
        }

        $mon = substr(session()->get('periode')['bulan'], 0, 2);
        $bukti_list = implode("','", $selected_bukti);

        // Query equivalent to Delphi's kd.SQL query in RekapClick
        $report_data = DB::SELECT("
            SELECT A.no_bukti, A.tgl, A.totala as total, A.ppn, A.dpp, A.no_SJ,
                   A.per, A.kodec, A.NAMAC, B.NAMA, B.ALAMAT, C.no_faktur
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            LEFT JOIN tg_inv C ON A.NO_SJ = C.NO_BUKTI
            WHERE A.NO_BUKTI IN ('{$bukti_list}')
            ORDER BY A.TGL ASC
        ");

        if (empty($report_data)) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return $this->generateInvoiceReport($report_data, 'invoice_pelanggan');
    }

    /**
     * Generate invoice report using PHPJasperXML
     */
    private function generateInvoiceReport($data, $reportType)
    {
        $PHPJasperXML = new PHPJasperXML();

        $reportFile = $this->getInvoiceReportFile($reportType);
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/' . $reportFile);

        $reportData = [];
        foreach ($data as $record) {
            $reportData[] = (array) $record;
        }

        $PHPJasperXML->setData($reportData);

        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * Get invoice report file based on type
     */
    private function getInvoiceReportFile($reportType)
    {
        $reportFiles = [
            'invoice_pelanggan' => 'invoice_pelanggan.jrxml',
            'invoice_pelanggan_detail' => 'invoice_pelanggan_detail.jrxml'
        ];

        return $reportFiles[$reportType] ?? 'invoice_pelanggan.jrxml';
    }

    /**
     * Generate main report (equivalent to jasper method)
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

        $mon = substr($periode, 0, 2);

        $query = DB::SELECT("
            SELECT A.NO_BUKTI, A.NO_SJ, A.TGL, A.KODEC, A.NAMAC,
                   SUM(A.totala) as TOTAL, A.PPN, A.DPP,
                   B.NAMA, B.ALAMAT, B.TGL_FAKTUR
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.PER = ? AND A.CBG = ?
            GROUP BY A.NO_SJ
            ORDER BY A.NO_BUKTI
        ", [$periode, $CBG]);

        $data = [];
        foreach ($query as $key => $value) {
            $data[] = [
                'NO_BUKTI' => $value->NO_BUKTI,
                'NO_SJ' => $value->NO_SJ,
                'TGL' => $value->TGL,
                'KODEC' => $value->KODEC,
                'NAMAC' => $value->NAMAC,
                'TOTAL' => $value->TOTAL,
                'PPN' => $value->PPN,
                'DPP' => $value->DPP,
                'NAMA' => $value->NAMA,
                'ALAMAT' => $value->ALAMAT,
                'TGL_FAKTUR' => $value->TGL_FAKTUR,
                'JUDUL' => $judul,
                'CBG' => $CBG
            ];
        }

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/invoice_pelanggan_laporan.jrxml');

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * Get invoice summary for dashboard
     */
    public function getInvoiceSummary(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;
        $mon = substr($periode, 0, 2);

        $summary = DB::SELECT("
            SELECT
                COUNT(DISTINCT A.NO_BUKTI) as total_transaksi,
                COUNT(DISTINCT A.NO_SJ) as total_invoice,
                SUM(A.totala) as total_amount,
                COUNT(CASE WHEN A.NO_SJ IS NOT NULL AND A.NO_SJ <> '' THEN 1 END) as invoiced_count,
                COUNT(CASE WHEN A.NO_SJ IS NULL OR A.NO_SJ = '' THEN 1 END) as not_invoiced_count
            FROM jual{$mon} A
            WHERE A.PER = ? AND A.CBG = ?
        ", [$periode, $CBG]);

        return response()->json($summary[0] ?? []);
    }

    /**
     * Validate transaction before processing
     */
    private function validateTransaction($request)
    {
        $errors = [];

        if (empty(trim($request->ALAMAT))) {
            $errors[] = 'Alamat tidak boleh kosong!';
        }

        if (empty(trim($request->NAMA))) {
            $errors[] = 'Nama tidak boleh kosong!';
        }

        if (empty($request->selected_invoices)) {
            $errors[] = 'Pilih minimal satu transaksi!';
        }

        return $errors;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($no_bukti)
    {
        return redirect('/invoice-plg')->with('error', 'Hapus data tidak diizinkan!');
    }
}
