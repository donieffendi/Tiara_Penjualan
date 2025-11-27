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
use Illuminate\Support\Facades\File;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TPKBusanaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = 'Posting KSB (Kasir Busana)';
    var $FLAGZ = 'KSB';

    function setFlag(Request $request)
    {
        $this->judul = "Posting KSB (Kasir Busana)";
        $this->FLAGZ = 'KSB';
    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        return view("otransaksi_TPKBusana.index")->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Get Toko/Store data (equivalent to cbgx query in Delphi)
     */
    public function getToko(Request $request)
    {
        $toko = DB::select("SELECT kode, nama FROM toko ORDER BY kode");

        if (empty($toko)) {
            return response()->json(['error' => 'Data toko tidak ditemukan'], 404);
        }

        return response()->json($toko);
    }

    /**
     * Get data for export (equivalent to main processing in Delphi Button1Click)
     */
    public function getExportData(Request $request)
    {
        $tanggal = $request->tanggal;

        if (empty($tanggal)) {
            return response()->json(['error' => 'Tanggal harus diisi'], 400);
        }

        // Get month from date (equivalent to bulan variable in Delphi)
        $bulan = str_pad(date('m', strtotime($tanggal)), 2, '0', STR_PAD_LEFT);

        // Get toko data (equivalent to cbgx query)
        $toko_list = DB::select("SELECT * FROM toko");

        if (empty($toko_list)) {
            return response()->json(['error' => 'Data toko tidak ditemukan'], 404);
        }

        $export_data = [];

        // Process each toko (equivalent to for loop in Delphi)
        foreach ($toko_list as $index => $toko) {
            $cbg = trim($toko->kode);

            // Build query for each shift (P and S) as in Delphi
            $shift_data = $this->getShiftData($cbg, $bulan, $tanggal, 'P'); // Pagi shift
            $shift_data_s = $this->getShiftData($cbg, $bulan, $tanggal, 'S'); // Sore shift

            $export_data = array_merge($export_data, $shift_data, $shift_data_s);
        }

        return response()->json([
            'success' => true,
            'data' => $export_data,
            'total_records' => count($export_data)
        ]);
    }

    /**
     * Get shift data (equivalent to complex SQL query building in Delphi)
     */
    private function getShiftData($cbg, $bulan, $tanggal, $shift)
    {
        // Complex query equivalent to the SQL building in Delphi
        $query = "
            SELECT
                ksr,
                CASE
                    WHEN bukti2 <> '' AND LENGTH(juald{$bulan}.bukti2) > 10 THEN
                        RIGHT(LEFT(juald{$bulan}.ket, 3), 1)
                    ELSE
                        CASE
                            WHEN jual{$bulan}.cbg = 'TGZ' THEN 'C'
                            WHEN jual{$bulan}.cbg = 'TMM' THEN 'G'
                            WHEN jual{$bulan}.cbg = 'SOP' THEN 'F'
                            ELSE ''
                        END
                END as ccr,
                '' as lbl,
                '' as sub,
                juald{$bulan}.KD_BRG as kdbr,
                juald{$bulan}.NA_BRG as nmbr,
                CASE
                    WHEN juald{$bulan}.qty > 0 THEN
                        CASE
                            WHEN juald{$bulan}.type = 'BC' THEN 'J'
                            WHEN juald{$bulan}.type = 'RF' THEN 'Z'
                            ELSE 'Z'
                        END
                    ELSE 'Z'
                END as kdtr,
                juald{$bulan}.qty,
                juald{$bulan}.harga as hgm,
                juald{$bulan}.harga as hgs,
                ROUND(juald{$bulan}.diskon / juald{$bulan}.qty) as sdis1,
                0 as sdisc,
                juald{$bulan}.diskon as ndis1,
                0 as ndisc,
                juald{$bulan}.total as njual,
                0 as nbulat,
                0 as kred,
                0 as admkk,
                RIGHT(juald{$bulan}.no_bukti, 6) as kitir,
                '' as kpt,
                0 as dis1,
                '' as kk_sts,
                '' as dis_khs,
                RIGHT(juald{$bulan}.no_bukti, 6) as nuksr,
                '' as kd_rlt,
                TIME(jual{$bulan}.tg_smp) as jam,
                juald{$bulan}.bukti2 as no_ttp,
                '' as ST_CNT,
                CASE
                    WHEN jual{$bulan}.cbg = 'TGZ' THEN 'T'
                    WHEN jual{$bulan}.cbg = 'TMM' THEN 'M'
                    WHEN jual{$bulan}.cbg = 'SOP' THEN 'P'
                    ELSE ''
                END as ST_OUT,
                '' as ST_ATP,
                '' as ST_PRO,
                '' as ST_PJK,
                ? as shift_type,
                ? as cbg_code
            FROM {$cbg}.jual{$bulan}, {$cbg}.juald{$bulan}
            WHERE jual{$bulan}.no_bukti = juald{$bulan}.no_bukti
                AND LENGTH(TRIM(juald{$bulan}.kd_brg)) > 7
                AND jual{$bulan}.flag <> 'ZP'
                AND jual{$bulan}.flag = 'JL'
                AND jual{$bulan}.shift = ?
                AND jual{$bulan}.tgl = ?
        ";

        try {
            $results = DB::select($query, [$shift, $cbg, $shift, $tanggal]);
            return $results;
        } catch (\Exception $e) {
            Log::error("Error getting shift data for CBG: {$cbg}, Shift: {$shift} - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Process KSB Export (main processing equivalent to Delphi Button1Click)
     */
    public function processKSBExport(Request $request)
    {
        $this->validate($request, [
            'tanggal' => 'required|date'
        ]);

        $tanggal = $request->tanggal;
        $tgl_format = date('Y-m-d', strtotime($tanggal));

        try {
            DB::beginTransaction();

            // Get month for dynamic table names
            $bulan = str_pad(date('m', strtotime($tanggal)), 2, '0', STR_PAD_LEFT);

            // Generate filename equivalent to Delphi logic
            $filename_p = $this->generateFilename('P', $tanggal); // IP + date + .DBF
            $filename_s = $this->generateFilename('S', $tanggal); // IS + date + .DBF

            // Get toko data
            $toko_list = DB::select("SELECT * FROM toko ORDER BY kode");

            if (empty($toko_list)) {
                throw new \Exception('Data toko tidak ditemukan');
            }

            $total_exported = 0;

            // Process Shift P (Pagi)
            $export_data_p = $this->processShiftExport($toko_list, $bulan, $tgl_format, 'P');
            if (!empty($export_data_p)) {
                $this->createExportFile($filename_p, $export_data_p);
                $total_exported += count($export_data_p);
            }

            // Process Shift S (Sore)
            $export_data_s = $this->processShiftExport($toko_list, $bulan, $tgl_format, 'S');
            if (!empty($export_data_s)) {
                $this->createExportFile($filename_s, $export_data_s);
                $total_exported += count($export_data_s);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Export KSB berhasil!',
                'files' => [
                    'shift_p' => $filename_p,
                    'shift_s' => $filename_s
                ],
                'total_records' => $total_exported,
                'tanggal' => $tanggal
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in KSB Export: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal export KSB: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process shift export data
     */
    private function processShiftExport($toko_list, $bulan, $tanggal, $shift)
    {
        $export_data = [];

        foreach ($toko_list as $toko) {
            $cbg = trim($toko->kode);
            $shift_data = $this->getShiftData($cbg, $bulan, $tanggal, $shift);

            if (!empty($shift_data)) {
                $export_data = array_merge($export_data, $shift_data);
            }
        }

        return $export_data;
    }

    /**
     * Generate filename equivalent to Delphi logic
     */
    private function generateFilename($prefix, $tanggal)
    {
        $day = str_pad(date('d', strtotime($tanggal)), 2, '0', STR_PAD_LEFT);
        $month = str_pad(date('m', strtotime($tanggal)), 2, '0', STR_PAD_LEFT);
        $year = substr(date('Y', strtotime($tanggal)), 2, 2);

        // Format: I + P/S + DD + MM + YY + .DBF
        return "I{$prefix}{$day}{$month}{$year}.DBF";
    }

    /**
     * Create export file (equivalent to DBF creation in Delphi)
     */
    private function createExportFile($filename, $data)
    {
        try {
            // Since we can't create actual DBF files easily in PHP, we'll create CSV format
            // that can be converted to DBF if needed
            $export_path = storage_path('exports/ksb/');

            if (!File::exists($export_path)) {
                File::makeDirectory($export_path, 0755, true);
            }

            $csv_filename = str_replace('.DBF', '.csv', $filename);
            $full_path = $export_path . $csv_filename;

            $handle = fopen($full_path, 'w');

            if (!empty($data)) {
                // Write header
                $first_row = (array) $data[0];
                fputcsv($handle, array_keys($first_row));

                // Write data
                foreach ($data as $row) {
                    fputcsv($handle, (array) $row);
                }
            }

            fclose($handle);

            Log::info("Export file created: " . $full_path);
        } catch (\Exception $e) {
            Log::error("Error creating export file {$filename}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Download export file
     */
    public function downloadExport(Request $request)
    {
        $filename = $request->filename;

        if (empty($filename)) {
            return response()->json(['error' => 'Filename tidak valid'], 400);
        }

        $filepath = storage_path('exports/ksb/' . $filename);

        if (!File::exists($filepath)) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        return response()->download($filepath);
    }

    /**
     * Get export history/status
     */
    public function getExportStatus(Request $request)
    {
        $export_path = storage_path('exports/ksb/');

        if (!File::exists($export_path)) {
            return response()->json(['files' => []]);
        }

        $files = File::files($export_path);
        $file_list = [];

        foreach ($files as $file) {
            $file_list[] = [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'created' => date('Y-m-d H:i:s', $file->getMTime()),
                'path' => $file->getRealPath()
            ];
        }

        // Sort by creation time, newest first
        usort($file_list, function ($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });

        return response()->json(['files' => $file_list]);
    }

    /**
     * Validate export data before processing
     */
    public function validateExportData(Request $request)
    {
        $tanggal = $request->tanggal;

        if (empty($tanggal)) {
            return response()->json(['error' => 'Tanggal harus diisi'], 400);
        }

        $tgl_format = date('Y-m-d', strtotime($tanggal));
        $bulan = str_pad(date('m', strtotime($tanggal)), 2, '0', STR_PAD_LEFT);

        try {
            // Check if there's data for the selected date
            $toko_list = DB::select("SELECT kode FROM toko LIMIT 5"); // Sample check
            $total_records = 0;

            foreach ($toko_list as $toko) {
                $cbg = trim($toko->kode);

                // Check both shifts
                $count_p = $this->getRecordCount($cbg, $bulan, $tgl_format, 'P');
                $count_s = $this->getRecordCount($cbg, $bulan, $tgl_format, 'S');

                $total_records += $count_p + $count_s;
            }

            return response()->json([
                'valid' => true,
                'total_records' => $total_records,
                'message' => $total_records > 0 ?
                    "Ditemukan {$total_records} record untuk tanggal {$tanggal}" :
                    "Tidak ada data untuk tanggal {$tanggal}"
            ]);
        } catch (\Exception $e) {
            Log::error('Error validating export data: ' . $e->getMessage());
            return response()->json([
                'valid' => false,
                'error' => 'Error validasi data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get record count for validation
     */
    private function getRecordCount($cbg, $bulan, $tanggal, $shift)
    {
        try {
            $query = "
                SELECT COUNT(*) as total
                FROM {$cbg}.jual{$bulan}, {$cbg}.juald{$bulan}
                WHERE jual{$bulan}.no_bukti = juald{$bulan}.no_bukti
                    AND LENGTH(TRIM(juald{$bulan}.kd_brg)) > 7
                    AND jual{$bulan}.flag <> 'ZP'
                    AND jual{$bulan}.flag = 'JL'
                    AND jual{$bulan}.shift = ?
                    AND jual{$bulan}.tgl = ?
            ";

            $result = DB::select($query, [$shift, $tanggal]);
            return $result[0]->total ?? 0;
        } catch (\Exception $e) {
            Log::warning("Error getting record count for CBG: {$cbg}, Shift: {$shift}");
            return 0;
        }
    }

    /**
     * Clean old export files
     */
    public function cleanOldExports(Request $request)
    {
        $days = $request->days ?? 30; // Default 30 days
        $export_path = storage_path('exports/ksb/');

        if (!File::exists($export_path)) {
            return response()->json(['message' => 'Export directory tidak ditemukan']);
        }

        $files = File::files($export_path);
        $deleted_count = 0;
        $cutoff_time = time() - ($days * 24 * 60 * 60);

        foreach ($files as $file) {
            if ($file->getMTime() < $cutoff_time) {
                File::delete($file->getRealPath());
                $deleted_count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$deleted_count} file export lama",
            'deleted_count' => $deleted_count
        ]);
    }

    /**
     * Get summary statistics for dashboard
     */
    public function getExportSummary(Request $request)
    {
        $date_from = $request->date_from ?? date('Y-m-01'); // First day of current month
        $date_to = $request->date_to ?? date('Y-m-d'); // Today

        // Get summary data
        $export_path = storage_path('exports/ksb/');
        $total_files = 0;
        $total_size = 0;

        if (File::exists($export_path)) {
            $files = File::files($export_path);
            $total_files = count($files);

            foreach ($files as $file) {
                $total_size += $file->getSize();
            }
        }

        return response()->json([
            'summary' => [
                'total_export_files' => $total_files,
                'total_size_mb' => round($total_size / 1024 / 1024, 2),
                'date_range' => [
                    'from' => $date_from,
                    'to' => $date_to
                ]
            ]
        ]);
    }

    // Keep existing methods for backward compatibility

    public function getTFaktur(Request $request)
    {
        // Keep original method for compatibility
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
                return '
                <div class="dropdown show" style="text-align: center">
                    <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item" href="#" onclick="exportKSB(\'' . $row->NO_SJ . '\')">
                            <i class="fa fa-download" aria-hidden="true"></i> Export KSB
                        </a>
                    </div>
                </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('otransaksi_TPKBusana.create')->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id = null)
    {
        return view('otransaksi_TPKBusana.edit')->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->processKSBExport($request);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Update method not applicable for KSB export']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return redirect()->back()->with('error', 'Hapus data tidak diizinkan!');
    }

    // API endpoints for compatibility
    public function getdata(Request $request)
    {
        return $this->getExportData($request);
    }

    public function jasper(Request $request)
    {
        // Generate report using PHPJasperXML
        $judul = $this->judul;
        $CBG = Auth::user()->CBG;

        $data = []; // Add your report data here

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/ksb_export_report.jrxml');

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    // Additional utility methods
    public function getAvailableDates(Request $request)
    {
        $bulan = date('m');
        $tahun = date('Y');

        if ($request->has('bulan')) {
            $bulan = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
        }

        if ($request->has('tahun')) {
            $tahun = $request->tahun;
        }

        // Get available dates that have transaction data
        $dates = [];
        try {
            $toko_sample = DB::select("SELECT kode FROM toko LIMIT 1");
            if (!empty($toko_sample)) {
                $cbg = $toko_sample[0]->kode;

                $available_dates = DB::select("
                    SELECT DISTINCT DATE(tgl) as tanggal
                    FROM {$cbg}.jual{$bulan}
                    WHERE YEAR(tgl) = ? AND MONTH(tgl) = ?
                        AND flag = 'JL' AND flag <> 'ZP'
                    ORDER BY tanggal DESC
                ", [$tahun, intval($bulan)]);

                $dates = array_column($available_dates, 'tanggal');
            }
        } catch (\Exception $e) {
            Log::warning('Error getting available dates: ' . $e->getMessage());
        }

        return response()->json(['dates' => $dates]);
    }

    public function getTokoStats(Request $request)
    {
        $tanggal = $request->tanggal;

        if (empty($tanggal)) {
            return response()->json(['error' => 'Tanggal harus diisi'], 400);
        }

        $bulan = str_pad(date('m', strtotime($tanggal)), 2, '0', STR_PAD_LEFT);
        $tgl_format = date('Y-m-d', strtotime($tanggal));

        $toko_list = DB::select("SELECT kode, nama FROM toko ORDER BY kode");
        $stats = [];

        foreach ($toko_list as $toko) {
            $cbg = trim($toko->kode);

            $count_p = $this->getRecordCount($cbg, $bulan, $tgl_format, 'P');
            $count_s = $this->getRecordCount($cbg, $bulan, $tgl_format, 'S');

            $stats[] = [
                'kode' => $cbg,
                'nama' => $toko->nama,
                'shift_p' => $count_p,
                'shift_s' => $count_s,
                'total' => $count_p + $count_s
            ];
        }

        return response()->json(['stats' => $stats]);
    }
}
