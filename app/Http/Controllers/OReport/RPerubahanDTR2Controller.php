<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RPerubahanDTR2Controller extends Controller
{
    private $cbgMaster = '';

    public function __construct()
    {
        // Initialize cbgMaster sesuai dengan logika FormShow di Delphi
        $this->initializeCbgMaster();
    }

    /**
     * Initialize cbgMaster - mengimplementasikan logika dari FormShow
     * com1.SQL.Text:='SELECT KODE from toko WHERE STA="MA"';
     */
    private function initializeCbgMaster()
    {
        try {
            $masterToko = DB::table('toko')
                ->select('KODE')
                ->where('STA', 'MA')
                ->first();

            $this->cbgMaster = $masterToko ? $masterToko->KODE : '';
        } catch (\Exception $e) {
            Log::error('Error initializing cbgMaster: ' . $e->getMessage());
            $this->cbgMaster = '';
        }
    }

    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables - sesuai dengan kebutuhan form
        session()->put('filter_cbg', '');
        session()->put('filter_tanggal', date('Y-m-d')); // Set default ke hari ini seperti DtTgl.date:=date;

        return view('oreport_perubahandtr2.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilSinkron' => [],
            'defaultDate' => date('Y-m-d')
        ]);
    }

    /**
     * Mengimplementasikan logika dari btnProsesClick dan Tampil procedure
     */
    public function getSinkronDCReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_tanggal', $request->tanggal);

        $hasilSinkron = [];

        // Validasi input sesuai dengan kebutuhan procedure Tampil
        if (!empty($request->cbg) && !empty($request->tanggal)) {
            try {
                $hasilSinkron = $this->executeRDtrProcedure($request->cbg, $request->tanggal);
            } catch (\Exception $e) {
                Log::error('Error executing R DTR procedure: ' . $e->getMessage());
                session()->flash('error', 'Terjadi kesalahan saat memproses data: ' . $e->getMessage());
            }
        }

        return view('oreport_perubahandtr2.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilSinkron' => $hasilSinkron,
            'defaultDate' => $request->tanggal ?? date('Y-m-d')
        ]);
    }

    /**
     * Mengimplementasikan procedure Tampil dari Delphi
     * com.SQL.Text:='call '+cbgMast+'.pjl_r_dtr(:cbg,:jns,:tgl,:userx);';
     */
    private function executeRDtrProcedure($cbg, $tanggal)
    {
        try {
            // Validate input
            $this->validateRDtrInput($cbg, $tanggal);

            // Format tanggal sesuai dengan format di Delphi
            // FormatDateTime('yyyy-mm-dd',DTtgl.Date)
            $formattedDate = date('Y-m-d', strtotime($tanggal));

            // Get current user seperti FrMenu.Label2.Caption
            $currentUser = Auth::user()->username ?? Auth::user()->name ?? 'system';

            // Call stored procedure sesuai dengan logika Delphi
            // com.SQL.Text:='call '+cbgMast+'.pjl_r_dtr(:cbg,:jns,:tgl,:userx);';
            $procedureCall = $this->cbgMaster . '.pjl_r_dtr';

            $hasilData = DB::select("CALL {$procedureCall}(?, ?, ?, ?)", [
                $cbg,           // :cbg - sesuai dengan FrMenu.Flag.Caption
                'HAPUS_DTR2',   // :jns - fixed value seperti di Delphi
                $formattedDate, // :tgl
                $currentUser    // :userx
            ]);

            // Convert ke array untuk konsistensi
            $result = [];
            foreach ($hasilData as $row) {
                $result[] = (array) $row;
            }

            // Log activity untuk monitoring
            $this->logRDtrActivity('execute_procedure', $cbg, $tanggal, count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in executeRDtrProcedure: ' . $e->getMessage(), [
                'cbg' => $cbg,
                'tanggal' => $tanggal,
                'cbgMaster' => $this->cbgMaster
            ]);
            throw $e;
        }
    }

    /**
     * Validasi input sesuai dengan kebutuhan procedure
     */
    private function validateRDtrInput($cbg, $tanggal)
    {
        if (empty($cbg)) {
            throw new \Exception('Cabang harus diisi!');
        }

        if (empty($tanggal)) {
            throw new \Exception('Tanggal harus diisi!');
        }

        // Validate date format
        if (!strtotime($tanggal)) {
            throw new \Exception('Format tanggal tidak valid!');
        }

        // Validate cbgMaster is initialized
        if (empty($this->cbgMaster)) {
            throw new \Exception('Master cabang tidak ditemukan!');
        }

        // Validate cabang exists
        $cabangExists = DB::table('toko')
            ->where('KODE', $cbg)
            ->exists();

        if (!$cabangExists) {
            throw new \Exception('Cabang tidak ditemukan!');
        }
    }

    /**
     * Export ke Excel - mengimplementasikan logika dari Button2Click di Delphi
     * ExportGridToXLSX(SaveDialog1.FileName, cxgrid1);
     */
    public function exportToExcel(Request $request)
    {
        try {
            // Validate input
            $this->validateRDtrInput($request->cbg, $request->tanggal);

            $data = $this->executeRDtrProcedure($request->cbg, $request->tanggal);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk diekspor'], 200);
            }

            // Generate filename sesuai dengan pattern yang umum digunakan
            $filename = 'rdtr_hapus_' . $request->cbg . '_' . date('Ymd', strtotime($request->tanggal)) . '_' . date('His') . '.xlsx';

            // Log the export activity
            $this->logRDtrActivity('export_excel', $request->cbg, $request->tanggal, count($data));

            // Return data untuk diproses di frontend atau implement export logic disini
            return response()->json([
                'success' => true,
                'filename' => $filename,
                'record_count' => count($data),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate laporan Jasper - disesuaikan dengan procedure Tampil
     * if com.RecordCount>0 then frxRdtr2.ShowReport();
     */
    public function jasperRDtrReport(Request $request)
    {
        try {
            // Validate input
            $this->validateRDtrInput($request->cbg, $request->tanggal);

            $file = 'rdtr2_report'; // Sesuaikan dengan nama file jasper report
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            // Set session values
            session()->put('filter_cbg', $request->cbg);
            session()->put('filter_tanggal', $request->tanggal);

            $hasilData = $this->executeRDtrProcedure($request->cbg, $request->tanggal);

            // Check if data exists seperti di Delphi: if com.RecordCount>0
            if (empty($hasilData)) {
                return response()->json(['message' => 'Tidak ada data untuk ditampilkan'], 200);
            }

            $data = [];
            foreach ($hasilData as $item) {
                // Map data sesuai dengan struktur yang diharapkan Jasper report
                $data[] = [
                    'CBG' => $item['CBG'] ?? $request->cbg,
                    'TANGGAL' => date('d/m/Y', strtotime($request->tanggal)),
                    'JENIS' => 'HAPUS_DTR2',
                    'USER' => Auth::user()->username ?? Auth::user()->name ?? 'system',
                    // Tambahkan field lain sesuai dengan struktur data dari procedure
                    'KD_BRG' => $item['KD_BRG'] ?? '',
                    'NA_BRG' => $item['NA_BRG'] ?? '',
                    'QTY' => $item['QTY'] ?? 0,
                    'NILAI' => $item['NILAI'] ?? 0,
                    'STATUS' => $item['STATUS'] ?? '',
                    'KETERANGAN' => $item['KETERANGAN'] ?? ''
                ];
            }

            // Log the report generation
            $this->logRDtrActivity('generate_report', $request->cbg, $request->tanggal, count($data));

            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasperRDtrReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get daftar cabang yang valid - untuk populate dropdown
     */
    public function getCabangList()
    {
        try {
            $query = "
                SELECT KODE, NAMA, STA
                FROM toko
                WHERE STA IN ('MA', 'CB', 'DC')
                ORDER BY KODE ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getCabangList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get summary statistics untuk dashboard
     */
    public function getSummaryStatistics(Request $request)
    {
        try {
            if (empty($request->cbg) || empty($request->tanggal)) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            $data = $this->executeRDtrProcedure($request->cbg, $request->tanggal);

            $summary = [
                'total_records' => count($data),
                'cabang' => $request->cbg,
                'tanggal' => date('d/m/Y', strtotime($request->tanggal)),
                'jenis_proses' => 'HAPUS_DTR2',
                'processed_by' => Auth::user()->username ?? Auth::user()->name ?? 'system'
            ];

            // Tambahkan statistik khusus sesuai dengan data yang ada
            if (!empty($data)) {
                $summary['total_nilai'] = array_sum(array_column($data, 'NILAI'));
                $summary['total_qty'] = array_sum(array_column($data, 'QTY'));
            }

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getSummaryStatistics: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk validasi koneksi database dan procedure
     */
    public function validateDatabaseConnection()
    {
        try {
            // Test connection ke cbgMaster
            if (empty($this->cbgMaster)) {
                return response()->json(['error' => 'Master cabang tidak ditemukan'], 500);
            }

            // Test procedure existence
            $testQuery = "SHOW PROCEDURE STATUS WHERE Name = 'pjl_r_dtr' AND Db = '{$this->cbgMaster}'";
            $procedureExists = DB::select($testQuery);

            if (empty($procedureExists)) {
                return response()->json(['error' => 'Procedure pjl_r_dtr tidak ditemukan'], 500);
            }

            return response()->json([
                'success' => true,
                'cbg_master' => $this->cbgMaster,
                'procedure_status' => 'OK'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in validateDatabaseConnection: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper method untuk logging activity
     */
    private function logRDtrActivity($action, $cbg, $tanggal, $recordCount = 0)
    {
        Log::info("RDtr: {$action}", [
            'cbg' => $cbg,
            'tanggal' => $tanggal,
            'jenis' => 'HAPUS_DTR2',
            'record_count' => $recordCount,
            'cbg_master' => $this->cbgMaster,
            'user' => Auth::user()->username ?? Auth::user()->name ?? 'system',
            'timestamp' => now()
        ]);
    }

    /**
     * Method untuk handle error dan menampilkan pesan yang sesuai
     */
    private function handleDatabaseError(\Exception $e)
    {
        $errorMessage = $e->getMessage();

        // Handle specific database errors
        if (strpos($errorMessage, 'procedure') !== false) {
            return 'Stored procedure tidak ditemukan atau tidak dapat dijalankan';
        } elseif (strpos($errorMessage, 'connection') !== false) {
            return 'Koneksi database bermasalah';
        } elseif (strpos($errorMessage, 'timeout') !== false) {
            return 'Query timeout, coba dengan rentang data yang lebih kecil';
        } elseif (strpos($errorMessage, 'Access denied') !== false) {
            return 'Akses ke database ditolak, periksa hak akses user';
        }

        return 'Terjadi kesalahan pada database: ' . $errorMessage;
    }

    /**
     * Cleanup method untuk membersihkan session yang sudah tidak digunakan
     */
    public function cleanupSession()
    {
        session()->forget(['filter_cbg', 'filter_tanggal']);
        return response()->json(['success' => true]);
    }

    /**
     * Method untuk mendapatkan data dalam format yang berbeda (JSON, CSV, dll)
     */
    public function getDataInFormat(Request $request, $format = 'json')
    {
        try {
            $this->validateRDtrInput($request->cbg, $request->tanggal);
            $data = $this->executeRDtrProcedure($request->cbg, $request->tanggal);

            switch (strtolower($format)) {
                case 'json':
                    return response()->json(['success' => true, 'data' => $data]);

                case 'csv':
                    // Implement CSV export logic here if needed
                    return response('CSV export not implemented yet', 501);

                default:
                    return response()->json(['error' => 'Format tidak didukung'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error in getDataInFormat: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk refresh cbgMaster jika diperlukan
     */
    public function refreshCbgMaster()
    {
        $this->initializeCbgMaster();
        return response()->json([
            'success' => true,
            'cbg_master' => $this->cbgMaster
        ]);
    }
}