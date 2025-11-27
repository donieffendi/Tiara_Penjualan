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

class TBayarFCController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = 'Instruksi Pembayaran Food Center';
    var $FLAGZ = 'FC';

    function setFlag(Request $request)
    {
        $this->judul = "Instruksi Pembayaran Food Center";
        $this->FLAGZ = 'FC';
    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        return view("otransaksi_TBayarFC.index")->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Display Food Center payment instruction data (equivalent to Tampil procedure)
     */
    public function getTagiFC(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;

        // Main query equivalent to Delphi's Tampil procedure for Food Center
        $tagifc = DB::SELECT("SELECT NO_ID, no_bukti, tgl, penagih, notes, total, posted, usrnm, namas, PRNT
                             FROM tagifc
                             WHERE PER = '$periode'
                             ORDER BY NO_BUKTI");

        return Datatables::of($tagifc)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if (Auth::user()->divisi == "programmer") {
                    $btnEdit = ($row->posted == 1) ?
                        ' onclick= "alert(\'Transaksi ' . $row->no_bukti . ' sudah diposting!\')" href="#" ' :
                        ' href="tbayarfc/edit/?idx=' . $row->NO_ID . '&tipx=edit"';

                    $btnDelete = ($row->posted == 1) ?
                        ' onclick= "alert(\'Transaksi ' . $row->no_bukti . ' sudah diposting!\')" href="#" ' :
                        ' onclick="deleteRow(\'' . url("tbayarfc/delete/" . $row->NO_ID) . '\')" ';

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
     * Alias method for getTagiFC to match route naming convention
     */
    public function gettbayarfc(Request $request)
    {
        return $this->getTagiFC($request);
    }

    /**
     * Get detail data for Food Center payment instruction (equivalent to detail query)
     */
    public function getDetailtbayarfc(Request $request)
    {
        $no_bukti = $request->no_bukti;

        if (empty($no_bukti)) {
            return response()->json([]);
        }

        $detail = DB::SELECT("
            SELECT KET, TOTAL, total0, margin
            FROM tagifcd
            WHERE NO_BUKTI = '$no_bukti'
            ORDER BY REC ASC
        ");

        return response()->json($detail);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check period posting status equivalent to Delphi's NewClick
        if (request()->session()->has('periode')) {
            $periode = request()->session()->get('periode')['bulan'] . '/' . request()->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $posted = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);

        if (!empty($posted) && $posted[0]->posted == 1) {
            return redirect('/tbayarfc')->with('error', 'Closed Period');
        }

        return view('otransaksi_TBayarFC.create');
    }

    /**
     * Show the form for editing the specified resource.
     * Equivalent to cxGrid1DBTableView1DblClick procedure and tampil logic
     */
    public function edit($no_id = null)
    {
        $tipx = request()->tipx;
        $idx = request()->idx ?? $no_id;
        $flagz = request()->flagz ?? 'FC';

        // If no_id is not passed as parameter, try to get it from request
        if ($no_id === null) {
            $no_id = request()->idx ?? 0;
        }

        $readonly_mode = false; // Default value

        if ($tipx == 'new') {
            // Check period status before creating new
            if (request()->session()->has('periode')) {
                $periode = request()->session()->get('periode')['bulan'] . '/' . request()->session()->get('periode')['tahun'];
            } else {
                $periode = '';
            }

            try {
                $posted = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);
            } catch (\Exception $e) {
                $posted = null;
            }

            if (!empty($posted) && $posted[0]->posted == 1) {
                return redirect('/tbayarfc')->with('error', 'Closed Period');
            }

            // For new records, create empty objects
            $header = (object) [
                'NO_ID' => 0,
                'NO_BUKTI' => '',
                'TGL' => date('Y-m-d'),
                'TGL1' => date('Y-m-d'),
                'TGL2' => date('Y-m-d'),
                'KODES' => '',
                'NAMAS' => '',
                'NOTES' => '',
                'POSTED' => 0,
                'NO_KASIR' => '',
                'NAMA_B' => '',
                'NOREK' => '',
                'ANB' => '',
                'PROM' => 0,
                'NMARGIN' => 0,
                'MARGIN' => 0,
                'TARGET' => 0,
                'TOTAL' => 0,
                'JUMLAH' => 0,
                'LAIN' => 0
            ];
            $detail = collect(); // Empty collection for new records
            $supplier_data = null;
        } else {
            // Equivalent to Delphi: loading data from selected record
            $header = DB::table('tagifc')->where('NO_ID', $no_id)->first();

            if (!$header) {
                return redirect('/tbayarfc')->with('error', 'Data tidak ditemukan');
            }

            // Check if posted - equivalent to Delphi posted check
            if ($header->POSTED == 1) {
                // Data sudah diposting, set read-only mode (equivalent to mati procedure)
                $readonly_mode = true;
            } else {
                $readonly_mode = false;
            }

            // Get detail data from TAGIFCD - equivalent to Delphi com.SQL query
            if ($header && $header->NO_BUKTI) {
                $detail_raw = DB::SELECT("
                    SELECT no_id, rec, total, total0, total_up, total_down, margin, ket, type as jns
                    FROM tagifcd
                    WHERE NO_BUKTI = ?
                    ORDER BY rec
                ", [$header->NO_BUKTI]);

                $detail = collect($detail_raw);
            } else {
                $detail = collect();
            }

            // Get supplier data based on NO_KASIR (equivalent to txtstandExit)
            $supplier_data = null;
            if ($header->NO_KASIR) {
                $supplier_result = DB::SELECT("
                    SELECT kodes, namas, nama_b, no_rek as norek, an_b as anb, margin as nmargin, target
                    FROM supfc
                    WHERE stand = ?
                ", [$header->NO_KASIR]);

                if (!empty($supplier_result)) {
                    $supplier_data = $supplier_result[0];
                }
            }

            // Generate sales data if dates are set (equivalent to tampil procedure)
            $sales_data = collect();
            if ($header->TGL1 && $header->TGL2 && $header->NO_KASIR) {
                $sales_data = $this->generateSalesData($header->TGL1, $header->TGL2, $header->NO_KASIR, $header->TARGET ?? 0, $header->NMARGIN ?? 0);
            }

            // Merge detail with sales data for display
            $detail = $detail->merge($sales_data);
        }

        return view('otransaksi_TBayarFC.edit', compact('header', 'detail', 'tipx', 'idx', 'flagz', 'supplier_data', 'readonly_mode'));
    }

    /**
     * Generate sales data for AJAX request (public method for HTTP access)
     */
    public function generateSalesDataAjax(Request $request)
    {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $stand = $request->stand;
        $target = floatval($request->target ?? 0);
        $nmargin = floatval($request->nmargin ?? 0);

        if (empty($tgl1) || empty($tgl2) || empty($stand)) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        $sales_data = $this->generateSalesData($tgl1, $tgl2, $stand, $target, $nmargin);

        return response()->json($sales_data);
    }

    /**
     * Generate sales data equivalent to Delphi tampil procedure
     */
    private function generateSalesData($tgl1, $tgl2, $stand, $target, $nmargin)
    {
        // Calculate margin_down (margin jika dibawah target)
        $margin_down = intval($target * ($nmargin / 100));

        // Get month range - equivalent to Delphi month calculation
        $start_month = intval(date('m', strtotime($tgl1)));
        $end_month = intval(date('m', strtotime($tgl2)));

        // Handle year crossing
        if ($end_month < $start_month) {
            $end_month += 12;
        }

        // Build dynamic SQL for multiple juald tables (equivalent to Delphi loop)
        $union_queries = [];
        $current_month = $start_month;

        while ($current_month <= $end_month) {
            $month_str = str_pad(($current_month > 12) ? $current_month - 12 : $current_month, 2, '0', STR_PAD_LEFT);

            $union_queries[] = "
                SELECT juald{$month_str}.KD_BRG, juald{$month_str}.NA_BRG,
                       SUM(juald{$month_str}.qty) as qtylaku,
                       SUM(juald{$month_str}.total) as totlaku,
                       DATE(juald{$month_str}.tgl) as tgl
                FROM juald{$month_str}, brgfc
                WHERE juald{$month_str}.KD_BRG = brgfc.KD_BRG
                  AND brgfc.STAND = ?
                  AND juald{$month_str}.flag = 'FC'
                  AND DATE(juald{$month_str}.tgl) BETWEEN ? AND ?
                GROUP BY juald{$month_str}.tgl, juald{$month_str}.KD_BRG
            ";

            $current_month++;
        }

        $full_query = "
            SELECT tgl, SUM(qtylaku) as qtylaku, SUM(totlaku) as total
            FROM (" . implode(' UNION ALL ', $union_queries) . ") as combined_sales
            GROUP BY tgl
            ORDER BY tgl ASC
        ";

        try {
            // Execute the dynamic query with parameters
            $params = [];
            for ($i = 0; $i < count($union_queries); $i++) {
                $params[] = $stand;
                $params[] = $tgl1;
                $params[] = $tgl2;
            }

            $sales_results = DB::SELECT($full_query, $params);

            // Process results equivalent to Delphi while loop
            $sales_data = collect();
            foreach ($sales_results as $sale) {
                $sale_data = (object) [
                    'no_id' => 0,
                    'rec' => 0,
                    'ket' => date('d-m-Y', strtotime($sale->tgl)),
                    'total' => $sale->total,
                    'totala' => $sale->total,
                    'jns' => 'BY'
                ];

                // Calculate margin based on target (equivalent to Delphi if condition)
                if ($sale->total > $target) {
                    $sale_data->margin = 0;
                    $sale_data->total_up = $sale->total;
                    $sale_data->total_down = 0;
                } else {
                    $sale_data->margin = $margin_down;
                    $sale_data->total_up = 0;
                    $sale_data->total_down = $sale->total;
                }

                $sales_data->push($sale_data);
            }

            return $sales_data;
        } catch (\Exception $e) {
            // Log error and return empty collection
            Log::error('Error generating sales data: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get supplier data by stand/kasir (equivalent to txtstandExit)
     */
    public function getSupplierByStand(Request $request)
    {
        $stand = trim($request->stand);

        if (empty($stand)) {
            return response()->json(['error' => 'Stand tidak boleh kosong'], 400);
        }

        $supplier = DB::SELECT("
            SELECT kodes, namas, nama_b, no_rek as norek, an_b as anb, margin as nmargin, target
            FROM supfc
            WHERE stand = ?
        ", [$stand]);

        if (empty($supplier)) {
            return response()->json(['error' => 'Data supplier tidak ditemukan'], 404);
        }

        return response()->json($supplier[0]);
    }

    /**
     * Calculate totals equivalent to Delphi Hitung procedure
     */
    public function calculateTotals(Request $request)
    {
        $details = $request->details ?? [];
        $nmargin = floatval($request->nmargin ?? 0);

        $x1 = 0; // Total for 'BY' type
        $x2 = 0; // Total for other types
        $total_up = 0;
        $total_down = 0;

        foreach ($details as $detail) {
            if ($detail['jns'] == 'BY') {
                $x1 += floatval($detail['total'] ?? 0);
                $total_up += floatval($detail['total_up'] ?? 0);
                $total_down += floatval($detail['total_down'] ?? 0);
            } else {
                $x2 += floatval($detail['total'] ?? 0);
            }
        }

        // Calculate margin (equivalent to Delphi margin calculation)
        $margin = intval($total_up * $nmargin / 100) + $total_down;

        // Calculate promotion (equivalent to Delphi txtProm calculation)
        $prom = intval(($x1 - $margin) * (1 / 100));

        // Calculate net amount (equivalent to Delphi nett calculation)
        $nett = $x1 - $margin - $prom - 5000; // 5000 is bank charge

        return response()->json([
            'total' => $x1,
            'total_tr' => $x2,
            'margin' => $margin,
            'prom' => $prom,
            'nett' => $nett,
            'total_up' => $total_up,
            'total_down' => $total_down
        ]);
    }

    /**
     * Print single Food Center payment instruction (equivalent to p1Click and Panel3Click)
     */
    public function printSingle(Request $request)
    {
        $no_bukti = $request->no_bukti;

        if (empty($no_bukti)) {
            return response()->json(['error' => 'No bukti tidak boleh kosong'], 400);
        }

        // Get form information (equivalent to Delphi form retrieval)
        $no_form = $this->getFormInfo();
        $toko_info = $this->getTokoInfo();

        // Main Food Center payment instruction query equivalent to Delphi's hero.sql.Text
        $hero = DB::SELECT("
            SELECT :no_form as no_form,
                   :na_toko as na_toko,
                   :typ_pers as typ_pers,
                   :alamat_pers as alamat_pers,
                   tagifc.TGL1,
                   tagifc.TGL2,
                   tagifc.KODES,
                   tagifc.NAMAS,
                   tagifc.NO_KASIR,
                   5000 as BANK,
                   tagifc.NAMA_B,
                   tagifc.norek,
                   tagifc.ANB,
                   tagifc.prom,
                   tagifc.margin,
                   (tagifcd.total0 - tagifcd.total) as pendapatan,
                   tagifc.total as total_nota,
                   tagifc.jumlah as nett,
                   tagifcd.KET,
                   tagifcd.TOTAL,
                   tagifc.lain,
                   tagifcd.margin as mx
            FROM tagifc, tagifcd
            WHERE tagifcd.NO_BUKTI = tagifc.NO_BUKTI
              AND tagifc.NO_BUKTI = :bukti
            ORDER BY tagifcd.REC ASC
        ", [
            'bukti' => $no_bukti,
            'no_form' => $no_form,
            'na_toko' => $toko_info['na_toko'],
            'typ_pers' => $toko_info['typ_pers'],
            'alamat_pers' => $toko_info['alamat_pers']
        ]);

        if (empty($hero)) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Mark as printed (equivalent to Delphi: update tagifc set prnt=1)
        DB::UPDATE("UPDATE tagifc SET prnt = 1 WHERE no_bukti = ?", [$no_bukti]);

        // Generate reports (equivalent to Delphi: frxBayar.ShowReport(); frxBayar2.ShowReport();)
        return $this->generateFCReport($hero, 'frxBayarFC');
    }

    /**
     * Print range of Food Center payment instructions (equivalent to Button1Click)
     */
    public function printRange(Request $request)
    {
        $txtbukti1 = trim($request->txtbukti1);
        $txtbukti2 = trim($request->txtbukti2);

        if (empty($txtbukti1) || empty($txtbukti2)) {
            return response()->json(['error' => 'Range bukti tidak boleh kosong'], 400);
        }

        // Get form information
        $no_form = $this->getFormInfo();
        $toko_info = $this->getTokoInfo();

        // Range query for Food Center
        $hero = DB::SELECT("
            SELECT :no_form as no_form,
                   :na_toko as na_toko,
                   :typ_pers as typ_pers,
                   :alamat_pers as alamat_pers,
                   tagifc.TGL1,
                   tagifc.TGL2,
                   tagifc.KODES,
                   tagifc.NAMAS,
                   tagifc.NO_KASIR,
                   5000 as BANK,
                   tagifc.NAMA_B,
                   tagifc.norek,
                   tagifc.ANB,
                   tagifc.prom,
                   tagifc.margin,
                   (tagifcd.total0 - tagifcd.total) as pendapatan,
                   tagifc.total as total_nota,
                   tagifc.jumlah as nett,
                   tagifcd.KET,
                   tagifcd.TOTAL,
                   tagifc.lain,
                   tagifcd.margin as mx
            FROM tagifc, tagifcd
            WHERE tagifcd.NO_BUKTI = tagifc.NO_BUKTI
              AND tagifc.NO_BUKTI BETWEEN :txtbukti1 AND :txtbukti2
            ORDER BY tagifc.NO_BUKTI, tagifcd.REC ASC
        ", [
            'txtbukti1' => $txtbukti1,
            'txtbukti2' => $txtbukti2,
            'no_form' => $no_form,
            'na_toko' => $toko_info['na_toko'],
            'typ_pers' => $toko_info['typ_pers'],
            'alamat_pers' => $toko_info['alamat_pers']
        ]);

        // Mark range as printed
        DB::UPDATE("UPDATE tagifc SET prnt = 1 WHERE no_bukti BETWEEN ? AND ?", [$txtbukti1, $txtbukti2]);

        return $this->generateFCReport($hero, 'frxBayarFC_Range');
    }

    /**
     * Generate Food Center report using PHPJasperXML
     */
    private function generateFCReport($data, $reportType)
    {
        $PHPJasperXML = new PHPJasperXML();

        // Load appropriate Food Center report template
        $reportFile = $this->getFCReportFile($reportType);
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
     * Get Food Center report file based on type
     */
    private function getFCReportFile($reportType)
    {
        $reportFiles = [
            'frxBayarFC' => 'instruksi_pembayaran_foodcenter.jrxml',
            'frxBayarFC_Range' => 'instruksi_pembayaran_foodcenter_range.jrxml',
            'frxBayarFC_All' => 'instruksi_pembayaran_foodcenter_all.jrxml'
        ];

        return $reportFiles[$reportType] ?? 'instruksi_pembayaran_foodcenter.jrxml';
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'TGL' => 'required',
                'TGL1' => 'required',
                'TGL2' => 'required',
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

        // Generate NO_BUKTI for Food Center
        $query = DB::table('tagifc')->select('NO_BUKTI')->where('PER', $periode)->orderByDesc('NO_BUKTI')->limit(1)->get();

        if ($query != '[]') {
            $query = substr($query[0]->NO_BUKTI, -4);
            $query = str_pad($query + 1, 4, 0, STR_PAD_LEFT);
            $no_bukti = 'FC' . $tahun . $bulan . '-' . $query;
        } else {
            $no_bukti = 'FC' . $tahun . $bulan . '-0001';
        }

        // Insert Header to tagifc
        DB::table('tagifc')->insert([
            'NO_BUKTI' => $no_bukti,
            'TGL' => date('Y-m-d', strtotime($request['TGL'])),
            'TGL1' => date('Y-m-d', strtotime($request['TGL1'])),
            'TGL2' => date('Y-m-d', strtotime($request['TGL2'])),
            'PER' => $periode,
            'KODES' => $request['KODES'],
            'NAMAS' => $request['NAMAS'],
            'NOTES' => $request['NOTES'] ?? '',
            'NO_KASIR' => $request['NO_KASIR'] ?? '',
            'NAMA_B' => $request['NAMA_B'] ?? '',
            'NOREK' => $request['NOREK'] ?? '',
            'ANB' => $request['ANB'] ?? '',
            'PROM' => $request['PROM'] ?? 0,
            'MARGIN' => $request['MARGIN'] ?? 0,
            'TOTAL' => $request['TOTAL'] ?? 0,
            'JUMLAH' => $request['JUMLAH'] ?? 0,
            'LAIN' => $request['LAIN'] ?? 0,
            'CBG' => $CBG,
            'USRNM' => Auth::user()->username,
            'TG_SMP' => Carbon::now(),
            'created_by' => Auth::user()->username,
            'created_at' => Carbon::now()
        ]);

        return redirect('/tbayarfc')->with('status', 'Data berhasil ditambahkan');
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
                'TGL1' => 'required',
                'TGL2' => 'required',
                'KODES' => 'required',
                'NAMAS' => 'required'
            ]
        );

        // Update Header in tagifc
        DB::table('tagifc')->where('NO_ID', $no_id)->update([
            'TGL' => date('Y-m-d', strtotime($request['TGL'])),
            'TGL1' => date('Y-m-d', strtotime($request['TGL1'])),
            'TGL2' => date('Y-m-d', strtotime($request['TGL2'])),
            'KODES' => $request['KODES'],
            'NAMAS' => $request['NAMAS'],
            'NOTES' => $request['NOTES'] ?? '',
            'NO_KASIR' => $request['NO_KASIR'] ?? '',
            'NAMA_B' => $request['NAMA_B'] ?? '',
            'NOREK' => $request['NOREK'] ?? '',
            'ANB' => $request['ANB'] ?? '',
            'PROM' => $request['PROM'] ?? 0,
            'MARGIN' => $request['MARGIN'] ?? 0,
            'TOTAL' => $request['TOTAL'] ?? 0,
            'JUMLAH' => $request['JUMLAH'] ?? 0,
            'LAIN' => $request['LAIN'] ?? 0,
            'USRNM' => Auth::user()->username,
            'TG_SMP' => Carbon::now(),
            'updated_by' => Auth::user()->username,
            'updated_at' => Carbon::now()
        ]);

        return redirect('/tbayarfc')->with('status', 'Data berhasil diupdate');
    }

    /**
     * Generate report for Food Center payment instructions
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
            SELECT NO_BUKTI, TGL, TGL1, TGL2, KODES, NAMAS, TOTAL, NOTES,
                   NO_KASIR, NAMA_B, NOREK, ANB, PROM, MARGIN, JUMLAH, LAIN
            FROM tagifc
            WHERE PER = '$periode'
            ORDER BY NO_BUKTI
        ");

        $data = [];
        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $value->NO_BUKTI,
                'TGL' => $value->TGL,
                'TGL1' => $value->TGL1,
                'TGL2' => $value->TGL2,
                'KODES' => $value->KODES,
                'NAMAS' => $value->NAMAS,
                'TOTAL' => $value->TOTAL,
                'NOTES' => $value->NOTES,
                'NO_KASIR' => $value->NO_KASIR,
                'NAMA_B' => $value->NAMA_B,
                'NOREK' => $value->NOREK,
                'ANB' => $value->ANB,
                'PROM' => $value->PROM,
                'MARGIN' => $value->MARGIN,
                'JUMLAH' => $value->JUMLAH,
                'LAIN' => $value->LAIN,
                'JUDUL' => $judul,
                'CBG' => $CBG
            ));
        }

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/instruksi_pembayaran_foodcenter_laporan.jrxml');

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * Print all Food Center payment instructions
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
        $no_form = $this->getFormInfo();
        $toko_info = $this->getTokoInfo();

        // Query all Food Center data for the period
        $hero = DB::SELECT("
            SELECT :no_form as no_form,
                   :na_toko as na_toko,
                   :typ_pers as typ_pers,
                   :alamat_pers as alamat_pers,
                   tagifc.TGL1,
                   tagifc.TGL2,
                   tagifc.KODES,
                   tagifc.NAMAS,
                   tagifc.NO_KASIR,
                   5000 as BANK,
                   tagifc.NAMA_B,
                   tagifc.norek,
                   tagifc.ANB,
                   tagifc.prom,
                   tagifc.margin,
                   (tagifcd.total0 - tagifcd.total) as pendapatan,
                   tagifc.total as total_nota,
                   tagifc.jumlah as nett,
                   tagifcd.KET,
                   tagifcd.TOTAL,
                   tagifc.lain,
                   tagifcd.margin as mx
            FROM tagifc, tagifcd
            WHERE tagifcd.NO_BUKTI = tagifc.NO_BUKTI
              AND tagifc.PER = :periode
              AND tagifc.CBG = :cbg
            ORDER BY tagifc.NO_BUKTI, tagifcd.REC ASC
        ", [
            'periode' => $periode,
            'cbg' => $CBG,
            'no_form' => $no_form,
            'na_toko' => $toko_info['na_toko'],
            'typ_pers' => $toko_info['typ_pers'],
            'alamat_pers' => $toko_info['alamat_pers']
        ]);

        // Mark all as printed
        DB::UPDATE("UPDATE tagifc SET prnt = 1 WHERE PER = ? AND CBG = ?", [$periode, $CBG]);

        return $this->generateFCReport($hero, 'frxBayarFC_All');
    }

    /**
     * Posting Food Center payment instructions
     */
    public function posting(Request $request)
    {
        $this->validate($request, [
            'tgl_posting' => 'required|date',
            'cek' => 'required|array'
        ]);

        $tgl_posting = $request->tgl_posting;
        $bukti_list = $request->cek;

        try {
            DB::beginTransaction();

            foreach ($bukti_list as $no_bukti) {
                // Update posting status for Food Center
                DB::UPDATE(
                    "UPDATE tagifc SET posted = 1, tgl_posting = ? WHERE no_bukti = ?",
                    [$tgl_posting, $no_bukti]
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data Food Center berhasil diposting'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal posting data Food Center: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unposting Food Center payment instructions
     */
    public function unposting(Request $request)
    {
        $this->validate($request, [
            'cek' => 'required|array'
        ]);

        $bukti_list = $request->cek;

        try {
            DB::beginTransaction();

            foreach ($bukti_list as $no_bukti) {
                // Update unposting status for Food Center
                DB::UPDATE(
                    "UPDATE tagifc SET posted = 0, tgl_posting = NULL WHERE no_bukti = ?",
                    [$no_bukti]
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data Food Center berhasil di-unposting'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal unposting data Food Center: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get posted Food Center data for verification
     */
    public function gettbayarfc_posting(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $CBG = Auth::user()->CBG;

        $tagifc = DB::SELECT("SELECT NO_ID, no_bukti, tgl, posted, tgl_posting, usrnm, namas
                             FROM tagifc
                             WHERE PER = '$periode' AND CBG = '$CBG' AND posted = 1
                             ORDER BY NO_BUKTI");

        return response()->json($tagifc);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($no_id)
    {
        // Following Delphi logic - deletion is disabled
        return redirect('/tbayarfc')->with('error', 'Mau ngapain? ( >.<)==0)-3-) ');
    }
}
