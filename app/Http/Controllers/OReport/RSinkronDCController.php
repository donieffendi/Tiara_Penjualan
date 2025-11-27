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

class RSinkronDCController extends Controller
{
    public function report()
    {
        $cbg = Auth::user()->CBG;
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', $cbg);
        session()->put('filter_jenis', '');
        session()->put('filter_tanggal', '');
        session()->put('filter_tanggal2', '');

        // Get available jenis for the initial load
        $jenisOptions = ['-' => '--Pilih Jenis Report--', 'TANDA_DC' => 'TANDA * DC'];

        return view('oreport_sinkrondc.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilSinkron' => []
        ]);
    }

    public function getSinkronDCReport(Request $request)
    {
        $cbg = Auth::user()->CBG; // ambil cabang dari user login
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $cbg);
        session()->put('filter_jenis', $request->jenis);
        session()->put('filter_tanggal', $request->tanggal);
        session()->put('filter_tanggal2', $request->tanggal2);

        $hasilSinkron = [];

        // Ambil data jika filter lengkap
        if (!empty($request->jenis) && !empty($request->tanggal) && !empty($request->tanggal2)) {
            $hasilSinkron = $this->getSinkronDCDataRange(
                $cbg,
                $request->jenis,
                $request->tanggal,
                $request->tanggal2
            );
            // dd($hasilSinkron);
        }

        return view('oreport_sinkrondc.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilSinkron' => $hasilSinkron
        ]);
    }

    /**
     * Get available jenis options based on cabang
     * Mengimplementasikan logika dari FormShow di Delphi
     */
    private function getSinkronDCDataRange($cbg, $jenis, $tglDr, $tglSmp)
    {
        try {
            $this->validateSinkronInput($cbg, $jenis, $tglDr);
            $this->validateSinkronInput($cbg, $jenis, $tglSmp);

            $startDate = date('Y-m-d', strtotime($tglDr));
            $endDate   = date('Y-m-d', strtotime($tglSmp));

            // Sesuaikan value jenis agar cocok dengan SP Delphi
            if ($jenis === 'TANDA_DC') {
                $jenis = 'TANDA * DC';
            }

            $hasilData = DB::select('CALL tgz.pjl_report_sinkron_dc(?, ?, ?, ?)', [
                $cbg,
                trim($jenis),
                $startDate,
                $endDate
            ]);

            $result = [];
            foreach ($hasilData as $row) {
                $result[] = (array) $row;
            }

            $this->logSinkronActivity('retrieve_data_range', $cbg, $jenis, "{$startDate} s/d {$endDate}", count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getSinkronDCDataRange: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Get sinkronisasi DC data
     * Mengimplementasikan logika dari procedure Tampil di Delphi
     */
    private function getSinkronDCData($cbg, $jenis, $tanggal)
    {
        try {
            // Validate input - sesuai dengan validasi di Delphi
            $this->validateSinkronInput($cbg, $jenis, $tanggal);

            // Format tanggal sesuai dengan format yang digunakan di Delphi
            // FormatDateTime('yyyy-mm-dd',DTtgl.Date)

            if ($jenis === 'TANDA_DC') {
                $jenis = 'TANDA * DC';
            }

            $formattedDate = date('Y-m-d', strtotime($tanggal));

            // Call stored procedure sesuai dengan logika Delphi
            // com.SQL.Text:='call tgz.pjl_report_sinkron_dc(:cbg,:jns,:tgl);';
            $hasilData = DB::select('CALL tgz.pjl_report_sinkron_dc(?, ?, ?)', [
                $cbg,
                trim($jenis),
                $formattedDate
            ]);

            // Convert ke array untuk konsistensi dengan format yang diharapkan
            $result = [];
            foreach ($hasilData as $row) {
                $result[] = (array) $row;
            }

            // Log activity untuk monitoring
            $this->logSinkronActivity('retrieve_data', $cbg, $jenis, $tanggal, count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getSinkronDCData: ' . $e->getMessage(), [
                'cbg' => $cbg,
                'jenis' => $jenis,
                'tanggal' => $tanggal
            ]);
            throw $e;
        }
    }

    /**
     * Validasi input sesuai dengan logika di Delphi
     */
    private function validateSinkronInput($cbg, $jenis, $tanggal)
    {
        // Validasi jenis tidak boleh kosong - sesuai dengan Delphi
        // if trim(txtjenis.Text)<>'' then
        if (empty(trim($jenis))) {
            throw new \Exception('Jenis Report Tidak Boleh Kosong');
        }

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

        // Validate cabang exists in toko table
        $cabangExists = DB::table('tgz.toko')
            ->where('KODE', $cbg)
            ->whereIn('STA', ['MA', 'CB', 'DC'])
            ->exists();

        if (!$cabangExists) {
            throw new \Exception('Cabang tidak valid atau tidak aktif!');
        }
    }

    /**
     * Get daftar cabang yang valid
     */
    public function getCabangList()
    {
        try {
            $query = "
                SELECT KODE, NAMA, STA
                FROM tgz.toko
                WHERE STA IN ('MA', 'CB', 'DC')
                ORDER BY NO_ID ASC
            ";

            return DB::select($query);
        } catch (\Exception $e) {
            Log::error('Error in getCabangList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * AJAX endpoint untuk mendapatkan jenis options berdasarkan cabang
     */
    public function getJenisOptionsByCabang(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => ['-' => '--Pilih Jenis Report--', 'TANDA_DC' => 'TANDA * DC']
        ]);
    }

    /**
     * Export ke Excel - mengimplementasikan logika dari btnExcelClick di Delphi
     */
    public function exportToExcel(Request $request)
    {
        try {
            // Validate input
            $this->validateSinkronInput($request->cbg, $request->jenis, $request->tanggal);

            $data = $this->getSinkronDCData($request->cbg, $request->jenis, $request->tanggal);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk diekspor'], 200);
            }

            // Generate filename sesuai dengan pattern yang umum digunakan
            $filename = 'sinkron_dc_' . $request->cbg . '_' . str_replace(' ', '_', $request->jenis) . '_' . date('Ymd', strtotime($request->tanggal)) . '_' . date('His') . '.xlsx';

            // Implementasi export Excel menggunakan library yang sesuai
            // Misalnya menggunakan PhpSpreadsheet atau library lainnya

            // Log the export activity
            $this->logSinkronActivity('export_excel', $request->cbg, $request->jenis, $request->tanggal, count($data));

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'record_count' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in exportToExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate laporan Jasper - disesuaikan dengan struktur data sinkron DC
     */
    public function jasperSinkronDCReport(Request $request)
    {
        try {
            // Validate input
            $this->validateSinkronInput($request->cbg, $request->jenis, $request->tanggal);

            $file = 'sinkrondc_report';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            // Set session values
            session()->put('filter_cbg', $request->cbg);
            session()->put('filter_jenis', $request->jenis);
            session()->put('filter_tanggal', $request->tanggal);

            $data = [];

            $hasilSinkron = $this->getSinkronDCData($request->cbg, $request->jenis, $request->tanggal);
            // dd($hasilSinkron);
            foreach ($hasilSinkron as $item) {
                // Map data sesuai dengan struktur yang diharapkan Jasper report
                $data[] = [
                    'CBG' => $item['CBG'] ?? '',
                    'JENIS' => $request->jenis,
                    'TANGGAL' => date('d/m/Y', strtotime($request->tanggal)),
                    'KD_BRG' => $item['KD_BRG'] ?? '',
                    'NA_BRG' => $item['NA_BRG'] ?? '',
                    'SUB' => $item['SUB'] ?? '',
                    'NILAI1' => $item['NILAI1'] ?? 0,
                    'NILAI2' => $item['NILAI2'] ?? 0,
                    'SELISIH' => ($item['NILAI1'] ?? 0) - ($item['NILAI2'] ?? 0),
                    'STATUS' => $item['STATUS'] ?? '',
                    'KETERANGAN' => $item['KETERANGAN'] ?? ''
                ];
            }

            // Log the report generation
            $this->logSinkronActivity('generate_report', $request->cbg, $request->jenis, $request->tanggal, count($data));

            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasperSinkronDCReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get summary statistics untuk dashboard
     */
    public function getSummaryStatistics(Request $request)
    {
        try {
            if (empty($request->cbg) || empty($request->jenis) || empty($request->tanggal)) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            $data = $this->getSinkronDCData($request->cbg, $request->jenis, $request->tanggal);

            $summary = [
                'total_records' => count($data),
                'total_match' => 0,
                'total_diff' => 0,
                'total_error' => 0
            ];

            foreach ($data as $item) {
                $status = $item['STATUS'] ?? '';
                switch (strtoupper($status)) {
                    case 'MATCH':
                        $summary['total_match']++;
                        break;
                    case 'DIFF':
                    case 'DIFFERENT':
                        $summary['total_diff']++;
                        break;
                    case 'ERROR':
                        $summary['total_error']++;
                        break;
                }
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
     * Helper method untuk logging activity
     */
    private function logSinkronActivity($action, $cbg, $jenis, $tanggal, $recordCount = 0)
    {
        Log::info("SinkronDC: {$action}", [
            'cbg' => $cbg,
            'jenis' => $jenis,
            'tanggal' => $tanggal,
            'record_count' => $recordCount,
            'user' => auth()->user()->id ?? 'system',
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
        }

        return 'Terjadi kesalahan pada database: ' . $errorMessage;
    }

    /**
     * Cleanup method untuk membersihkan session yang sudah tidak digunakan
     */
    public function cleanupSession()
    {
        session()->forget(['filter_cbg', 'filter_jenis', 'filter_tanggal']);
        return response()->json(['success' => true]);
    }

    /**
     * Method untuk mendapatkan data dalam format yang berbeda (JSON, CSV, dll)
     */
    public function getDataInFormat(Request $request, $format = 'json')
    {
        try {
            $this->validateSinkronInput($request->cbg, $request->jenis, $request->tanggal);
            $data = $this->getSinkronDCData($request->cbg, $request->jenis, $request->tanggal);

            switch (strtolower($format)) {
                case 'json':
                    return response()->json(['success' => true, 'data' => $data]);

                case 'csv':
                    // Implement CSV export logic here
                    return response('CSV not implemented yet', 501);

                default:
                    return response()->json(['error' => 'Format tidak didukung'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error in getDataInFormat: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}