<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TFakturController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = 'Faktur Pajak';
    var $FLAGZ = 'FK';

    function setFlag(Request $request)
    {
        $this->judul = "Faktur Pajak";
        $this->FLAGZ = 'FK';
    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        return view("otransaksi_tfaktur.index")->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Get TFaktur data (equivalent to Delphi FormShow and com2 query)
     */
    public function getTFaktur(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;
        $mon = substr($periode, 0, 2); // Get month part for dynamic table

        if (empty($periode)) {
            return response()->json(['error' => 'Periode belum diset'], 400);
        }

        // Main query equivalent to Delphi's com2 query
        $tfaktur = DB::SELECT("
            SELECT A.NO_BUKTI, A.NO_SJ, A.KODEC, SUM(A.totala) as TOTALX,
                   DATE(B.TG_SMP) as TGLX, B.USERX, B.NO_FAKTUR
            FROM jual{$mon} A
            INNER JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.NO_SJ <> '' AND A.PER = ? AND A.FLAG <> 'ZP'
            GROUP BY A.NO_SJ
            ORDER BY A.NO_SJ
        ", [$periode]);

        if (empty($tfaktur)) {
            return response()->json(['error' => 'Tidak ada data! x270'], 404);
        }

        return Datatables::of($tfaktur)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    $btnEdit = ' href="tfaktur/edit/?idx=' . $row->NO_SJ . '&tipx=edit"';
                    $btnDelete = ' onclick="deleteRow(\'' . url("tfaktur/delete/" . $row->NO_SJ) . '\')" ';

                    $btnPrivilege = '
                        <a class="dropdown-item" ' . $btnEdit . '>
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a class="dropdown-item" href="#" onclick="printSingle(\'' . $row->NO_SJ . '\')">
                            <i class="fa fa-print" aria-hidden="true"></i> Print
                        </a>
                        <a class="dropdown-item" href="#" onclick="generateCSV(\'' . $row->NO_FAKTUR . '\')">
                            <i class="fa fa-file-csv" aria-hidden="true"></i> Export CSV
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
            ->addColumn('status_faktur', function ($row) {
                return !empty($row->NO_FAKTUR) ? '<span class="badge badge-success">Sudah Faktur</span>' : '<span class="badge badge-warning">Belum Faktur</span>';
            })
            ->rawColumns(['action', 'status_faktur'])
            ->make(true);
    }

    /**
     * Get faktur details by NO_SJ (equivalent to cxGrid1DBTableView1DblClick in Delphi)
     */
    public function getFakturDetails(Request $request)
    {
        $no_sj = $request->no_sj;
        $per = $request->per;
        $mon = substr($per, 0, 2);

        if (empty($no_sj) || empty($per)) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        // Query equivalent to Delphi's detailed query in cxGrid1DBTableView1DblClick
        $details = DB::SELECT("
            SELECT A.NO_SJ, A.KODEC, A.NAMAC, B.NAMA, B.ALAMAT,
                   LEFT(A.PER, 2) as MASA_PAJAK,
                   SUM(A.TOTALA) as TOTALX, SUM(A.DPP) as DPPX, SUM(A.PPN) as PPNX,
                   B.NO_FAKTUR, B.TGL_FAKTUR, B.NPWP
            FROM tg_inv B
            INNER JOIN jual{$mon} A ON A.NO_SJ = B.NO_BUKTI
            WHERE A.NO_SJ = ? AND A.PER = ? AND A.FLAG <> 'ZP'
            GROUP BY A.NO_SJ
        ", [$no_sj, $per]);

        if (empty($details)) {
            return response()->json(['error' => 'Tidak ditemukan x219!'], 404);
        }

        return response()->json($details[0]);
    }

    /**
     * Check for zero quantity items (equivalent to btnCSVClick validation in Delphi)
     */
    public function cekFaktur(Request $request)
    {
        $faktur = trim($request->faktur);
        $per = $request->per;
        $mon = substr($per, 0, 2);

        if (empty($faktur)) {
            return response()->json(['error' => 'No. Faktur tidak boleh kosong'], 400);
        }

        // Check for zero quantity items - equivalent to Delphi validation
        $zeroQtyItems = DB::SELECT("
            SELECT B.na_brg
            FROM jual{$mon} A
            INNER JOIN juald{$mon} B ON A.NO_BUKTI = B.NO_BUKTI
            INNER JOIN tg_inv C ON A.NO_SJ = C.NO_BUKTI
            WHERE B.QTY = 0 AND C.NO_FAKTUR = ?
        ", [$faktur]);

        if (!empty($zeroQtyItems)) {
            return response()->json([
                'error' => 'Ada data dengan qty 0, harap dicek ulang.',
                'items' => $zeroQtyItems
            ], 422);
        }

        // Check if faktur already exists
        $existingFaktur = DB::SELECT("
            SELECT NO_BUKTI
            FROM tg_inv
            WHERE NO_FAKTUR = ?
            LIMIT 1
        ", [$faktur]);

        if (!empty($existingFaktur)) {
            return response()->json([
                'error' => 'No.Faktur sudah di invoice : ' . $existingFaktur[0]->NO_BUKTI
            ], 422);
        }

        return response()->json(['success' => true, 'message' => 'Faktur valid']);
    }

    /**
     * Update faktur (equivalent to btnSimpanClick in Delphi)
     */
    public function updateFaktur(Request $request)
    {
        $this->validate($request, [
            'faktur' => 'required',
            'npwp' => 'required',
            'nama' => 'required',
            'alamat' => 'required',
            'no_sj' => 'required'
        ]);

        $faktur = trim($request->faktur);
        $npwp = trim($request->npwp);
        $nama = trim($request->nama);
        $alamat = trim($request->alamat);
        $no_sj = trim($request->no_sj);

        try {
            DB::beginTransaction();

            // Check if faktur number already exists
            $existingFaktur = DB::SELECT("
                SELECT NO_BUKTI
                FROM tg_inv
                WHERE NO_FAKTUR = ?
                LIMIT 1
            ", [$faktur]);

            if (!empty($existingFaktur)) {
                throw new \Exception('No.Faktur sudah di invoice : ' . $existingFaktur[0]->NO_BUKTI);
            }

            // Update tg_inv - equivalent to Delphi's UPDATE query
            DB::UPDATE("
                UPDATE tg_inv
                SET NO_FAKTUR = ?,
                    TGL_FAKTUR = CURDATE(),
                    NPWP = ?,
                    NAMA = ?,
                    ALAMAT = ?
                WHERE NO_BUKTI = ?
            ", [$faktur, $npwp, $nama, $alamat, $no_sj]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success',
                'faktur' => $faktur
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate CSV export (equivalent to btnCSVClick in Delphi)
     */
    public function generateCsv(Request $request)
    {
        $faktur = trim($request->faktur);
        $alamat = $request->alamat ?? '';

        if (empty($faktur)) {
            return response()->json(['error' => 'No. Faktur tidak boleh kosong'], 400);
        }

        try {
            // First validate no zero quantity items
            $per = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
            $mon = substr($per, 0, 2);

            $zeroQtyItems = DB::SELECT("
                SELECT B.na_brg
                FROM jual{$mon} A
                INNER JOIN juald{$mon} B ON A.NO_BUKTI = B.NO_BUKTI
                INNER JOIN tg_inv C ON A.NO_SJ = C.NO_BUKTI
                WHERE B.QTY = 0 AND C.NO_FAKTUR = ?
            ", [$faktur]);

            if (!empty($zeroQtyItems)) {
                return response()->json([
                    'error' => 'Ada data dengan qty 0, harap dicek ulang.',
                    'items' => $zeroQtyItems
                ], 422);
            }

            // Update FAKTURX01 fields - equivalent to Delphi's update queries
            DB::UPDATE("
                UPDATE jual{$mon} A
                INNER JOIN juald{$mon} B ON A.NO_BUKTI = B.NO_BUKTI
                INNER JOIN tg_inv C ON A.NO_SJ = C.NO_BUKTI
                SET B.FAKTURX01 = ?
                WHERE C.NO_FAKTUR = ?
            ", [$faktur, $faktur]);

            DB::UPDATE("
                UPDATE jual{$mon} A
                INNER JOIN juald{$mon} B ON A.NO_BUKTI = B.NO_BUKTI
                INNER JOIN tg_inv C ON A.NO_SJ = C.NO_BUKTI
                SET A.FAKTURX01 = ?, A.DPP_PJK = A.DPP, A.PPN_PJK = A.PPN
                WHERE C.NO_FAKTUR = ?
            ", [$faktur, $faktur]);

            // Generate CSV data - equivalent to Delphi's complex query
            $csvData = DB::SELECT("
                SELECT 1 as REC, 'FK,KD_JENIS_TRANSAKSI,FG_PENGGANTI,NOMOR_FAKTUR,MASA_PAJAK,TAHUN_PAJAK,TANGGAL_FAKTUR,NPWP,NAMA,ALAMAT_LENGKAP,JUMLAH_DPP,JUMLAH_PPN,JUMLAH_PPNBM,ID_KETERANGAN_TAMBAHAN,FG_UANG_MUKA,UANG_MUKA_DPP,UANG_MUKA_PPN,UANG_MUKA_PPNBM,REFERENSI' as SATU

                UNION ALL

                SELECT 2 as REC, 'LT,NPWP,NAMA,JALAN,BLOK,NOMOR,RT,RW,KECAMATAN,KELURAHAN,KABUPATEN,PROPINSI,KODE_POS,NOMOR_TELEPON,,,,,,' as SATU

                UNION ALL

                SELECT 3 as REC, 'OF,KODE_OBJEK,NAMA,HARGA_SATUAN,JUMLAH_BARANG,HARGA_TOTAL,DISKON,DPP,PPN,TARIF_PPNBM,PPNBM,,,,,,,,,' as SATU

                UNION ALL

                SELECT 4 as REC, CONCAT_WS(',', 'FK', '01', 0, ?, MONTH(B.TGL_FAKTUR), YEAR(B.TGL_FAKTUR),
                    DATE_FORMAT(B.TGL_FAKTUR, '%d/%m/%Y'), B.NPWP, B.NAMA, ?,
                    ROUND(SUM(A.DPP)), ROUND(SUM(A.PPN)), 0, '0', 0, 0, 0, 0, '0') as SATU
                FROM jual{$mon} A
                INNER JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
                WHERE A.FLAG <> 'ZP' AND B.NO_FAKTUR = ?
                GROUP BY B.NO_FAKTUR

                UNION ALL

                SELECT 5 as REC, CONCAT_WS(',', 'LT', B.NPWP, B.NAMA, ?, '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0') as SATU
                FROM jual{$mon} A
                INNER JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
                WHERE B.NO_FAKTUR = ?
                LIMIT 1

                UNION ALL

                SELECT 6 as REC, CONCAT_WS(',', 'OF', 'A', B.NA_BRG, ROUND(B.HARGA), ROUND(SUM(B.QTY)),
                    ROUND(SUM(B.TOTAL)), ROUND(SUM(B.DISKON)), ROUND(SUM(B.DPP)), ROUND(SUM(B.nppn)),
                    '0', '0', '0,0,0,0,0,0,0,0') as SATU
                FROM jual{$mon} A
                INNER JOIN juald{$mon} B ON A.NO_BUKTI = B.NO_BUKTI
                INNER JOIN tg_inv C ON A.NO_SJ = C.NO_BUKTI
                WHERE A.NO_BUKTI = B.NO_BUKTI AND A.NO_SJ = C.NO_BUKTI
                    AND C.NO_FAKTUR = ? AND A.FLAG <> 'ZP'
                GROUP BY B.NA_BRG, B.HARGA
                ORDER BY B.NA_BRG

                ORDER BY REC
            ", [$faktur, $alamat, $faktur, $alamat, $faktur, $faktur]);

            // Create CSV content
            $csvContent = '';
            foreach ($csvData as $row) {
                $csvContent .= $row->SATU . "\n";
            }

            // Generate filename
            $filename = date('Ymd_His') . '_' . $faktur . '.csv';

            return response()->json([
                'success' => true,
                'message' => 'Export selesai',
                'filename' => $filename,
                'content' => $csvContent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal generate CSV: ' . $e->getMessage()
            ], 500);
        }
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
            return redirect('/tfaktur')->with('error', 'Closed Period');
        }

        return view('otransaksi_tfaktur.edit');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($no_sj = null)
    {
        $tipx = request()->tipx;
        $idx = request()->idx ?? $no_sj;

        if ($no_sj === null) {
            $no_sj = request()->idx ?? '';
        }

        if ($tipx == 'new') {
            // Check period status before creating new
            if (request()->session()->has('periode')) {
                $periode = request()->session()->get('periode')['bulan'] . '/' . request()->session()->get('periode')['tahun'];
            } else {
                $periode = '';
            }

            $posted = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);

            if (!empty($posted) && $posted[0]->posted == 1) {
                return redirect('/tfaktur')->with('error', 'Closed Period');
            }

            // For new records
            $header = (object) [
                'NO_SJ' => '',
                'KODEC' => '',
                'NAMAC' => '',
                'NAMA' => '',
                'ALAMAT' => '',
                'NO_FAKTUR' => '',
                'NPWP' => '',
                'MASA_PAJAK' => '',
                'TOTALX' => 0,
                'DPPX' => 0,
                'PPNX' => 0
            ];
            $detail = collect();
            $status = 'simpan';
        } else {
            // Load existing data (equivalent to edit mode in Delphi)
            $periode = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
            $mon = substr($periode, 0, 2);

            $header_data = DB::SELECT("
                SELECT A.NO_SJ, A.KODEC, A.NAMAC, B.NAMA, B.ALAMAT,
                       LEFT(A.PER, 2) as MASA_PAJAK,
                       SUM(A.TOTALA) as TOTALX, SUM(A.DPP) as DPPX, SUM(A.PPN) as PPNX,
                       B.NO_FAKTUR, B.TGL_FAKTUR, B.NPWP
                FROM tg_inv B
                INNER JOIN jual{$mon} A ON A.NO_SJ = B.NO_BUKTI
                WHERE A.NO_SJ = ? AND A.PER = ? AND A.FLAG <> 'ZP'
                GROUP BY A.NO_SJ
            ", [$no_sj, $periode]);

            if (empty($header_data)) {
                return redirect('/tfaktur')->with('error', 'Data tidak ditemukan');
            }

            $header = $header_data[0];
            $status = !empty($header->NO_FAKTUR) ? 'edit' : 'simpan';

            // Get detail data if needed
            $detail = collect();
        }

        return view('otransaksi_tfaktur.edit', compact('header', 'detail', 'tipx', 'idx', 'status'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Implementation for storing new tfaktur if needed
        return response()->json(['message' => 'Store method not implemented for this form']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $no_sj)
    {
        return $this->updateFaktur($request);
    }

    /**
     * Generate report using PHPJasperXML (equivalent to jasper method in Delphi)
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
                   B.NAMA, B.ALAMAT, B.TGL_FAKTUR, B.NO_FAKTUR
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.PER = ? AND A.CBG = ? AND A.FLAG <> 'ZP'
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
                'NO_FAKTUR' => $value->NO_FAKTUR,
                'JUDUL' => $judul,
                'CBG' => $CBG
            ];
        }

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/tfaktur_report.jrxml');

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * Print single faktur
     */
    public function printSingle(Request $request)
    {
        $no_sj = $request->no_sj;

        if (empty($no_sj)) {
            return response()->json(['error' => 'No SJ tidak boleh kosong'], 400);
        }

        // Implementation for single print
        return $this->generatePrintData([$no_sj]);
    }

    /**
     * Generate print data
     */
    private function generatePrintData($no_sj_list)
    {
        $periode = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $mon = substr($periode, 0, 2);
        $sj_list = implode("','", $no_sj_list);

        $report_data = DB::SELECT("
            SELECT A.NO_BUKTI, A.TGL, A.TOTALA as TOTAL, A.PPN, A.DPP, A.NO_SJ,
                   A.PER, A.KODEC, A.NAMAC, B.NAMA, B.ALAMAT, B.NO_FAKTUR
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.NO_SJ IN ('{$sj_list}') AND A.FLAG <> 'ZP'
            ORDER BY A.TGL ASC
        ");

        if (empty($report_data)) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return $this->generateReport($report_data, 'tfaktur_print');
    }

    /**
     * Generate report using PHPJasperXML
     */
    private function generateReport($data, $reportType)
    {
        $PHPJasperXML = new PHPJasperXML();

        $reportFile = $this->getReportFile($reportType);
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
     * Get report file based on type
     */
    private function getReportFile($reportType)
    {
        $reportFiles = [
            'tfaktur_print' => 'tfaktur_print.jrxml',
            'tfaktur_report' => 'tfaktur_report.jrxml'
        ];

        return $reportFiles[$reportType] ?? 'tfaktur_report.jrxml';
    }

    /**
     * Get summary data for dashboard
     */
    public function getTFakturSummary(Request $request)
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
                COUNT(DISTINCT A.NO_SJ) as total_invoice,
                COUNT(CASE WHEN B.NO_FAKTUR IS NOT NULL AND B.NO_FAKTUR <> '' THEN 1 END) as faktur_created,
                COUNT(CASE WHEN B.NO_FAKTUR IS NULL OR B.NO_FAKTUR = '' THEN 1 END) as faktur_pending,
                SUM(A.totala) as total_amount
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.PER = ? AND A.CBG = ? AND A.FLAG <> 'ZP'
        ", [$periode, $CBG]);

        return response()->json($summary[0] ?? []);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($no_sj)
    {
        return redirect('/tfaktur')->with('error', 'Hapus data tidak diizinkan!');
    }

    /**
     * Additional methods for API endpoints based on routes
     */

    public function getdata(Request $request)
    {
        return $this->getTFaktur($request);
    }

    public function browse(Request $request)
    {
        // Browse implementation if needed
        return response()->json([]);
    }

    public function browse_detail(Request $request)
    {
        // Browse detail implementation if needed
        return response()->json([]);
    }

    public function browseuang(Request $request)
    {
        // Browse uang implementation if needed
        return response()->json([]);
    }

    public function getDetailTFaktur(Request $request)
    {
        return $this->getFakturDetails($request);
    }

    public function printFaktur(Request $request)
    {
        return $this->printSingle($request);
    }

    public function posting(Request $request)
    {
        // Posting implementation if needed
        return response()->json(['message' => 'Posting not implemented']);
    }

    public function unposting(Request $request)
    {
        // Unposting implementation if needed
        return response()->json(['message' => 'Unposting not implemented']);
    }

    public function getTFaktur_posting(Request $request)
    {
        // Get posting data implementation if needed
        return response()->json([]);
    }

    public function index_posting(Request $request)
    {
        return view("otransaksi_tfaktur.posting")->with([
            'judul' => $this->judul . ' - Posting',
            'flagz' => $this->FLAGZ,
        ]);
    }

    public function repost($no_sj)
    {
        // Repost implementation if needed
        return redirect('/tfaktur')->with('success', 'Repost berhasil untuk No SJ: ' . $no_sj);
    }

    public function cetak($no_id)
    {
        // Print by ID implementation
        return $this->printSingle(request()->merge(['no_sj' => $no_id]));
    }

    public function jspoc($no_id)
    {
        // JSPOC implementation if needed
        return response()->json(['message' => 'JSPOC not implemented for ID: ' . $no_id]);
    }

    /**
     * Get supplier data by code (if needed for form)
     */
    public function getSupplierByCode(Request $request)
    {
        $kd = trim($request->kd);

        if (empty($kd)) {
            return response()->json(['error' => 'Kode supplier tidak boleh kosong'], 400);
        }

        $supplier = DB::SELECT("
            SELECT NAMA as nam, ALAMAT as alm
            FROM sup
            WHERE KODES = ?
        ", [$kd]);

        if (empty($supplier)) {
            return response()->json(['error' => 'Tidak ditemukan, atau salah kode supplier!'], 404);
        }

        return response()->json($supplier[0]);
    }

    /**
     * Get customer/member data by code (equivalent to member_crm query in original)
     */
    public function getMemberByCode(Request $request)
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
     * Get agenda data (if needed)
     */
    public function getAgendaData(Request $request)
    {
        // Implementation for agenda data if needed
        return response()->json(['message' => 'Agenda data not implemented']);
    }

    /**
     * Validate account (if needed)
     */
    public function validateAccount(Request $request)
    {
        // Implementation for account validation if needed
        return response()->json(['valid' => true]);
    }

    /**
     * Print range of faktur
     */
    public function printRange(Request $request)
    {
        $start_sj = $request->start_sj;
        $end_sj = $request->end_sj;

        if (empty($start_sj) || empty($end_sj)) {
            return response()->json(['error' => 'Range No SJ harus diisi'], 400);
        }

        $periode = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $mon = substr($periode, 0, 2);

        // Get range of NO_SJ
        $sj_list = DB::SELECT("
            SELECT DISTINCT A.NO_SJ
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.NO_SJ BETWEEN ? AND ?
                AND A.FLAG <> 'ZP'
            ORDER BY A.NO_SJ
        ", [$start_sj, $end_sj]);

        if (empty($sj_list)) {
            return response()->json(['error' => 'Tidak ada data dalam range tersebut'], 404);
        }

        $no_sj_array = array_column($sj_list, 'NO_SJ');
        return $this->generatePrintData($no_sj_array);
    }

    /**
     * Print all faktur for current period
     */
    public function printAll(Request $request)
    {
        $periode = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $mon = substr($periode, 0, 2);
        $cbg = Auth::user()->CBG;

        // Get all NO_SJ for current period
        $sj_list = DB::SELECT("
            SELECT DISTINCT A.NO_SJ
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.PER = ? AND A.CBG = ?
                AND A.FLAG <> 'ZP'
            ORDER BY A.NO_SJ
        ", [$periode, $cbg]);

        if (empty($sj_list)) {
            return response()->json(['error' => 'Tidak ada data untuk periode ini'], 404);
        }

        $no_sj_array = array_column($sj_list, 'NO_SJ');
        return $this->generatePrintData($no_sj_array);
    }

    /**
     * Print report (general report)
     */
    public function printReport(Request $request)
    {
        return $this->jasper($request);
    }

    /**
     * Validate before processing (equivalent to cek_kanan in Delphi)
     */
    private function validateFakturData($request)
    {
        $errors = [];

        if (empty(trim($request->nama))) {
            $errors[] = 'Nama Customer Kosong!';
        }

        if (empty(trim($request->alamat))) {
            $errors[] = 'Alamat Kosong!';
        }

        if (empty(trim($request->invoice))) {
            $errors[] = 'Invoice Kosong!';
        }

        if (empty(trim($request->nama))) {
            $errors[] = 'Nama Kosong!';
        }

        if (empty(trim($request->alamat))) {
            $errors[] = 'Alamat Kosong!';
        }

        if (empty(trim($request->faktur))) {
            $errors[] = 'No.Faktur Kosong!';
        }

        if (empty(trim($request->npwp))) {
            $errors[] = 'NPWP Kosong!';
        }

        if (empty(trim($request->masa_pajak))) {
            $errors[] = 'Masa Pajak Kosong!';
        }

        return $errors;
    }

    /**
     * Reset form data (equivalent to reset_kanan in Delphi)
     */
    public function resetForm(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Form berhasil direset',
            'data' => [
                'nama' => '',
                'alamat' => '',
                'invoice' => '',
                'faktur' => '',
                'nama' => '',
                'alamat' => '',
                'npwp' => '',
                'masa_pajak' => '',
                'keterangan' => ''
            ]
        ]);
    }

    /**
     * Get available invoices for selection (if needed for dropdown)
     */
    public function getAvailableInvoices(Request $request)
    {
        $per = $request->per;
        $mon = substr($per, 0, 2);

        if (empty($per)) {
            return response()->json([]);
        }

        // Get invoices that don't have faktur yet
        $invoices = DB::SELECT("
            SELECT A.NO_BUKTI, A.NO_SJ, A.KODEC, SUM(A.totala) as TOTALX,
                   DATE(B.TG_SMP) as TGLX, B.USERX, B.NO_FAKTUR
            FROM jual{$mon} A
            INNER JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.NO_SJ <> '' AND A.PER = ? AND A.FLAG <> 'ZP'
                AND (B.NO_FAKTUR IS NULL OR B.NO_FAKTUR = '')
            GROUP BY A.NO_SJ
            ORDER BY A.NO_SJ
        ", [$per]);

        return response()->json($invoices);
    }

    /**
     * Get invoice summary statistics
     */
    public function getInvoiceStats(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;
        $mon = substr($periode, 0, 2);

        if (empty($periode)) {
            return response()->json(['error' => 'Periode belum diset'], 400);
        }

        $stats = DB::SELECT("
            SELECT
                COUNT(DISTINCT A.NO_SJ) as total_invoice,
                COUNT(CASE WHEN B.NO_FAKTUR IS NOT NULL AND B.NO_FAKTUR <> '' THEN 1 END) as sudah_faktur,
                COUNT(CASE WHEN B.NO_FAKTUR IS NULL OR B.NO_FAKTUR = '' THEN 1 END) as belum_faktur,
                SUM(A.totala) as total_amount,
                SUM(A.dpp) as total_dpp,
                SUM(A.ppn) as total_ppn
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.PER = ? AND A.CBG = ? AND A.FLAG <> 'ZP'
        ", [$periode, $CBG]);

        return response()->json($stats[0] ?? []);
    }

    /**
     * Export to Excel/CSV for reporting
     */
    public function exportFaktur(Request $request)
    {
        $format = $request->format ?? 'csv';
        $periode = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $mon = substr($periode, 0, 2);
        $cbg = Auth::user()->CBG;

        $data = DB::SELECT("
            SELECT A.NO_BUKTI, A.NO_SJ, A.TGL, A.KODEC, A.NAMAC,
                   A.totala as TOTAL, A.PPN, A.DPP,
                   B.NAMA, B.ALAMAT, B.TGL_FAKTUR, B.NO_FAKTUR, B.NPWP
            FROM jual{$mon} A
            LEFT JOIN tg_inv B ON A.NO_SJ = B.NO_BUKTI
            WHERE A.PER = ? AND A.CBG = ? AND A.FLAG <> 'ZP'
            ORDER BY A.NO_BUKTI
        ", [$periode, $cbg]);

        if ($format === 'csv') {
            $filename = 'faktur_export_' . date('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');

                // Add headers
                fputcsv($file, [
                    'NO_BUKTI',
                    'NO_SJ',
                    'TGL',
                    'KODEC',
                    'NAMAC',
                    'TOTAL',
                    'PPN',
                    'DPP',
                    'NAMA',
                    'ALAMAT',
                    'TGL_FAKTUR',
                    'NO_FAKTUR',
                    'NPWP'
                ]);

                // Add data
                foreach ($data as $row) {
                    fputcsv($file, (array) $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return response()->json(['error' => 'Format tidak didukung'], 400);
    }

    /**
     * Get detail items for a specific faktur (if needed for detail view)
     */
    public function getFakturDetailItems(Request $request)
    {
        $no_sj = $request->no_sj;
        $periode = session()->get('periode')['bulan'] . '/' . session()->get('periode')['tahun'];
        $mon = substr($periode, 0, 2);

        if (empty($no_sj)) {
            return response()->json(['error' => 'No SJ tidak boleh kosong'], 400);
        }

        $details = DB::SELECT("
            SELECT B.NO_BUKTI, B.KD_BRG, B.NA_BRG, B.SATUAN,
                   B.QTY, B.HARGA, B.TOTAL, B.DISKON, B.DPP, B.nppn as PPN
            FROM jual{$mon} A
            INNER JOIN juald{$mon} B ON A.NO_BUKTI = B.NO_BUKTI
            INNER JOIN tg_inv C ON A.NO_SJ = C.NO_BUKTI
            WHERE A.NO_SJ = ? AND A.FLAG <> 'ZP'
            ORDER BY B.NO_BUKTI, B.REC
        ", [$no_sj]);

        return response()->json($details);
    }
}
