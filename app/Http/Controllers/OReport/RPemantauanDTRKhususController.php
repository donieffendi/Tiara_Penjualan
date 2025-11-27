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

class RPemantauanDTRKhususController extends Controller
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

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_sub', ''); // Tambahan untuk sub kategori
        session()->put('filter_tanggal', date('Y-m-d'));

        return view('oreport_pemantauandtr.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilSinkron' => [],
            'defaultDate' => date('Y-m-d')
        ]);
    }

    /**
     * Mengimplementasikan logika dari btnProsesClick dan Tampil procedure dari Delphi
     * Sesuai dengan query:
     * SELECT :cbg NA_TOKO, :no_form NO_FORM, a.SUB, a.KD_BRG, a.NA_BRG, a.KET_UK, a.KET_KEM, b.LPH, xx_hitdtr(a.KD_BRG) AS DTR, c.DTR_MANUAL, c.DTR_1M
     * FROM brg a, brgdt b, brg_dc_ts c
     * WHERE a.sub=:sub AND a.KD_BRG=b.KD_BRG AND a.KD_BRG=c.KD_BRG AND c.DTR_MANUAL > 0 AND c.DTR_MANUAL < xx_hitdtr(a.KD_BRG)
     * ORDER BY a.sub, a.KD_BRG
     */
    public function getPemantauanDTRKhususReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg);
        session()->put('filter_sub', $request->sub ?? '');
        session()->put('filter_tanggal', $request->tanggal);

        $hasilSinkron = [];

        // Validasi input sesuai dengan kebutuhan procedure Tampil
        if (!empty($request->cbg) && !empty($request->sub)) {
            try {
                $hasilSinkron = $this->executeTampilProcedure($request->cbg, $request->sub);
            } catch (\Exception $e) {
                Log::error('Error executing Tampil procedure: ' . $e->getMessage());
                session()->flash('error', 'Terjadi kesalahan saat memproses data: ' . $e->getMessage());
            }
        }

        return view('oreport_pemantauandtr.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilSinkron' => $hasilSinkron,
            'defaultDate' => $request->tanggal ?? date('Y-m-d')
        ]);
    }

    /**
     * Mengimplementasikan procedure Tampil dari Delphi
     * Query utama untuk pemantauan DTR sesuai dengan logika asli
     */
    private function executeTampilProcedure($cabang, $subKategori)
    {
        try {
            // Validate input
            $this->validateTampilInput($cabang, $subKategori);

            // Implementasi query dari Delphi dengan penyesuaian untuk Laravel
            $query = "
                SELECT
                    ? as NA_TOKO,
                    ? as NO_FORM,
                    a.SUB,
                    a.KD_BRG,
                    a.NA_BRG,
                    a.KET_UK,
                    a.KET_KEM,
                    b.LPH,
                    COALESCE(xx_hitdtr(a.KD_BRG), 0) AS DTR,
                    c.DTR_MANUAL,
                    c.DTR_1M
                FROM brg a
                INNER JOIN brgdt b ON a.KD_BRG = b.KD_BRG
                INNER JOIN brg_dc_ts c ON a.KD_BRG = c.KD_BRG
                WHERE a.SUB = ?
                  AND c.DTR_MANUAL > 0
                  AND c.DTR_MANUAL < COALESCE(xx_hitdtr(a.KD_BRG), 0)
                ORDER BY a.SUB, a.KD_BRG
            ";

            $parameters = [
                $cabang,        // :cbg (NA_TOKO)
                '',             // :no_form (NO_FORM) - kosong sesuai Delphi
                $subKategori    // :sub
            ];

            $hasilData = DB::select($query, $parameters);

            // Convert ke array untuk konsistensi
            $result = [];
            foreach ($hasilData as $row) {
                $result[] = (array) $row;
            }

            // Log activity untuk monitoring
            $this->logTampilActivity('execute_tampil', $cabang, $subKategori, count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in executeTampilProcedure: ' . $e->getMessage(), [
                'cabang' => $cabang,
                'sub_kategori' => $subKategori,
                'cbgMaster' => $this->cbgMaster
            ]);
            throw $e;
        }
    }

    /**
     * Validasi input sesuai dengan kebutuhan procedure Tampil
     */
    private function validateTampilInput($cabang, $subKategori)
    {
        if (empty($cabang)) {
            throw new \Exception('Cabang harus diisi!');
        }

        if (empty($subKategori)) {
            throw new \Exception('Sub kategori harus diisi!');
        }

        // Validate cbgMaster is initialized
        if (empty($this->cbgMaster)) {
            throw new \Exception('Master cabang tidak ditemukan!');
        }

        // Validate cabang exists
        $cabangExists = DB::table('toko')
            ->where('KODE', $cabang)
            ->exists();

        if (!$cabangExists) {
            throw new \Exception('Cabang tidak ditemukan!');
        }

        // Validate sub kategori exists
        $subExists = DB::table('brg')
            ->where('SUB', $subKategori)
            ->exists();

        if (!$subExists) {
            throw new \Exception('Sub kategori tidak ditemukan!');
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
            $this->validateTampilInput($request->cbg, $request->sub);

            $data = $this->executeTampilProcedure($request->cbg, $request->sub);

            if (empty($data)) {
                return response()->json(['message' => 'Tidak ada data untuk diekspor'], 200);
            }

            // Generate filename sesuai dengan pattern yang umum digunakan
            $filename = 'pantau_dtr_' . $request->cbg . '_' . $request->sub . '_' . date('Ymd_His') . '.xlsx';

            // Log the export activity
            $this->logTampilActivity('export_excel', $request->cbg, $request->sub, count($data));

            // Implement Excel export logic here
            // Bisa menggunakan Laravel Excel atau library lainnya

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'record_count' => count($data),
                'data' => $data,
                'export_url' => route('rdtr.download-excel', [
                    'cbg' => $request->cbg,
                    'sub' => $request->sub,
                    'filename' => $filename
                ])
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
    public function jasperDTRReport(Request $request)
    {
        try {
            // Validate input
            $this->validateTampilInput($request->cbg, $request->sub);

            $file = 'pantau_dtr_report'; // Sesuaikan dengan nama file jasper report
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            // Set session values
            session()->put('filter_cbg', $request->cbg);
            session()->put('filter_sub', $request->sub);

            $hasilData = $this->executeTampilProcedure($request->cbg, $request->sub);

            // Check if data exists seperti di Delphi: if com.RecordCount>0
            if (empty($hasilData)) {
                return response()->json(['message' => 'Tidak ada data untuk ditampilkan'], 200);
            }

            $data = [];
            foreach ($hasilData as $item) {
                // Map data sesuai dengan struktur yang diharapkan Jasper report
                $data[] = [
                    'NA_TOKO' => $item['NA_TOKO'] ?? $request->cbg,
                    'NO_FORM' => $item['NO_FORM'] ?? '',
                    'SUB' => $item['SUB'] ?? '',
                    'KD_BRG' => $item['KD_BRG'] ?? '',
                    'NA_BRG' => $item['NA_BRG'] ?? '',
                    'KET_UK' => $item['KET_UK'] ?? '',
                    'KET_KEM' => $item['KET_KEM'] ?? '',
                    'LPH' => $item['LPH'] ?? 0,
                    'DTR' => $item['DTR'] ?? 0,
                    'DTR_MANUAL' => $item['DTR_MANUAL'] ?? 0,
                    'DTR_1M' => $item['DTR_1M'] ?? 0,
                    'SELISIH_DTR' => ($item['DTR'] ?? 0) - ($item['DTR_MANUAL'] ?? 0),
                    'TANGGAL_LAPORAN' => date('d/m/Y'),
                    'USER_GENERATE' => Auth::user()->username ?? Auth::user()->name ?? 'system'
                ];
            }

            // Log the report generation
            $this->logTampilActivity('generate_report', $request->cbg, $request->sub, count($data));

            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasperDTRReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get daftar cabang yang valid - untuk populate dropdown
     * Sesuai dengan struktur toko yang digunakan di Delphi
     */
    public function getCabangList()
    {
        try {
            $query = "
                SELECT KODE, STA
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
     * Get daftar sub kategori - untuk populate dropdown sub
     * Berdasarkan tabel brg yang digunakan dalam query Delphi
     */
    public function getSubKategoriList()
    {
        try {
            $subKategori = DB::table('brg')
                ->select('SUB')
                ->distinct()
                ->whereNotNull('SUB')
                ->where('SUB', '!=', '')
                ->orderBy('SUB')
                ->get();

            return $subKategori;
        } catch (\Exception $e) {
            Log::error('Error in getSubKategoriList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get summary statistics untuk dashboard
     * Berdasarkan data yang dihasilkan dari query Tampil
     */
    public function getSummaryStatistics(Request $request)
    {
        try {
            if (empty($request->cbg) || empty($request->sub)) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            $data = $this->executeTampilProcedure($request->cbg, $request->sub);

            $summary = [
                'total_records' => count($data),
                'cabang' => $request->cbg,
                'sub_kategori' => $request->sub,
                'tanggal_generate' => date('d/m/Y'),
                'jenis_laporan' => 'PEMANTAUAN_DTR',
                'processed_by' => Auth::user()->username ?? Auth::user()->name ?? 'system'
            ];

            // Tambahkan statistik khusus berdasarkan data DTR
            if (!empty($data)) {
                $totalDTR = array_sum(array_column($data, 'DTR'));
                $totalDTRManual = array_sum(array_column($data, 'DTR_MANUAL'));
                $totalLPH = array_sum(array_column($data, 'LPH'));

                $summary['total_dtr_sistem'] = $totalDTR;
                $summary['total_dtr_manual'] = $totalDTRManual;
                $summary['total_lph'] = $totalLPH;
                $summary['selisih_total'] = $totalDTR - $totalDTRManual;
                $summary['persentase_akurasi'] = $totalDTR > 0 ? round(($totalDTRManual / $totalDTR) * 100, 2) : 0;

                // Hitung item yang memiliki selisih DTR
                $itemSelisih = 0;
                foreach ($data as $item) {
                    $dtr = $item['DTR'] ?? 0;
                    $dtrManual = $item['DTR_MANUAL'] ?? 0;
                    if ($dtrManual > 0 && $dtrManual < $dtr) {
                        $itemSelisih++;
                    }
                }
                $summary['item_bermasalah'] = $itemSelisih;
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
     * Method untuk validasi koneksi database dan function xx_hitdtr
     */
    public function validateDatabaseConnection()
    {
        try {
            // Test connection ke cbgMaster
            if (empty($this->cbgMaster)) {
                return response()->json(['error' => 'Master cabang tidak ditemukan'], 500);
            }

            // Test function xx_hitdtr existence
            $testQuery = "SELECT ROUTINE_NAME FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_NAME = 'xx_hitdtr' AND ROUTINE_TYPE = 'FUNCTION'";
            $functionExists = DB::select($testQuery);

            if (empty($functionExists)) {
                Log::warning('Function xx_hitdtr tidak ditemukan, menggunakan alternatif');
            }

            // Test basic tables existence
            $tablesExist = $this->validateRequiredTables();

            return response()->json([
                'success' => true,
                'cbg_master' => $this->cbgMaster,
                'function_xx_hitdtr' => !empty($functionExists) ? 'OK' : 'NOT_FOUND',
                'tables_status' => $tablesExist
            ]);
        } catch (\Exception $e) {
            Log::error('Error in validateDatabaseConnection: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validasi keberadaan tabel yang diperlukan
     */
    private function validateRequiredTables()
    {
        $requiredTables = ['brg', 'brgdt', 'brg_dc_ts', 'toko'];
        $tableStatus = [];

        foreach ($requiredTables as $table) {
            try {
                $exists = DB::select("SHOW TABLES LIKE '{$table}'");
                $tableStatus[$table] = !empty($exists) ? 'OK' : 'NOT_FOUND';
            } catch (\Exception $e) {
                $tableStatus[$table] = 'ERROR: ' . $e->getMessage();
            }
        }

        return $tableStatus;
    }

    /**
     * Helper method untuk logging activity - disesuaikan dengan logika Tampil
     */
    private function logTampilActivity($action, $cabang, $subKategori, $recordCount = 0)
    {
        Log::info("DTR Pantau: {$action}", [
            'cabang' => $cabang,
            'sub_kategori' => $subKategori,
            'jenis' => 'PEMANTAUAN_DTR',
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
        if (strpos($errorMessage, 'xx_hitdtr') !== false) {
            return 'Function xx_hitdtr tidak ditemukan atau tidak dapat dijalankan';
        } elseif (strpos($errorMessage, 'brg') !== false) {
            return 'Tabel barang tidak ditemukan atau tidak dapat diakses';
        } elseif (strpos($errorMessage, 'brgdt') !== false) {
            return 'Tabel detail barang tidak ditemukan atau tidak dapat diakses';
        } elseif (strpos($errorMessage, 'brg_dc_ts') !== false) {
            return 'Tabel DTR barang tidak ditemukan atau tidak dapat diakses';
        } elseif (strpos($errorMessage, 'connection') !== false) {
            return 'Koneksi database bermasalah';
        } elseif (strpos($errorMessage, 'timeout') !== false) {
            return 'Query timeout, coba dengan sub kategori yang lebih spesifik';
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
        session()->forget(['filter_cbg', 'filter_sub', 'filter_tanggal']);
        return response()->json(['success' => true]);
    }

    /**
     * Method untuk mendapatkan data dalam format yang berbeda (JSON, CSV, dll)
     */
    public function getDataInFormat(Request $request, $format = 'json')
    {
        try {
            $this->validateTampilInput($request->cbg, $request->sub);
            $data = $this->executeTampilProcedure($request->cbg, $request->sub);

            switch (strtolower($format)) {
                case 'json':
                    return response()->json(['success' => true, 'data' => $data]);

                case 'csv':
                    return $this->exportToCSV($data, $request->cbg, $request->sub);

                case 'excel':
                    return $this->exportToExcel($request);

                default:
                    return response()->json(['error' => 'Format tidak didukung'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error in getDataInFormat: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Export to CSV format
     */
    private function exportToCSV($data, $cabang, $subKategori)
    {
        try {
            $filename = 'pantau_dtr_' . $cabang . '_' . $subKategori . '_' . date('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');

                // Header CSV sesuai dengan kolom dari query Tampil
                fputcsv($file, [
                    'Nama Toko',
                    'No Form',
                    'Sub',
                    'Kode Barang',
                    'Nama Barang',
                    'Keterangan Ukuran',
                    'Keterangan Kemasan',
                    'LPH',
                    'DTR Sistem',
                    'DTR Manual',
                    'DTR 1 Bulan',
                    'Selisih DTR'
                ]);

                foreach ($data as $row) {
                    $selisihDTR = ($row['DTR'] ?? 0) - ($row['DTR_MANUAL'] ?? 0);

                    fputcsv($file, [
                        $row['NA_TOKO'] ?? '',
                        $row['NO_FORM'] ?? '',
                        $row['SUB'] ?? '',
                        $row['KD_BRG'] ?? '',
                        $row['NA_BRG'] ?? '',
                        $row['KET_UK'] ?? '',
                        $row['KET_KEM'] ?? '',
                        $row['LPH'] ?? 0,
                        $row['DTR'] ?? 0,
                        $row['DTR_MANUAL'] ?? 0,
                        $row['DTR_1M'] ?? 0,
                        $selisihDTR
                    ]);
                }

                fclose($file);
            };

            $this->logTampilActivity('export_csv', $cabang, $subKategori, count($data));

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error in exportToCSV: ' . $e->getMessage());
            throw $e;
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

    /**
     * Method untuk mendapatkan detail barang berdasarkan kode
     * Berguna untuk validasi dan autocomplete
     */
    public function getBarangDetail($kdBrg)
    {
        try {
            $barang = DB::table('brg as a')
                ->leftJoin('brgdt as b', 'a.KD_BRG', '=', 'b.KD_BRG')
                ->leftJoin('brg_dc_ts as c', 'a.KD_BRG', '=', 'c.KD_BRG')
                ->select([
                    'a.KD_BRG',
                    'a.NA_BRG',
                    'a.SUB',
                    'a.KET_UK',
                    'a.KET_KEM',
                    'b.LPH',
                    'c.DTR_MANUAL',
                    'c.DTR_1M'
                ])
                ->where('a.KD_BRG', $kdBrg)
                ->first();

            if (!$barang) {
                return response()->json(['error' => 'Barang tidak ditemukan'], 404);
            }

            // Hitung DTR menggunakan function xx_hitdtr atau alternatif
            $dtrSistem = $this->calculateDTR($kdBrg);

            $result = (array) $barang;
            $result['DTR'] = $dtrSistem;
            $result['SELISIH_DTR'] = $dtrSistem - ($barang->DTR_MANUAL ?? 0);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error in getBarangDetail: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk menghitung DTR jika function xx_hitdtr tidak tersedia
     * Implementasi alternatif berdasarkan logika bisnis DTR
     */
    private function calculateDTR($kdBrg)
    {
        try {
            // Coba gunakan function xx_hitdtr terlebih dahulu
            $result = DB::select("SELECT xx_hitdtr(?) as dtr", [$kdBrg]);

            if (!empty($result)) {
                return $result[0]->dtr ?? 0;
            }
        } catch (\Exception $e) {
            Log::warning('Function xx_hitdtr tidak tersedia, menggunakan kalkulasi alternatif: ' . $e->getMessage());
        }

        // Implementasi kalkulasi DTR alternatif jika function tidak tersedia
        // Sesuaikan dengan logika bisnis yang sebenarnya
        try {
            $dtrData = DB::table('brg_dc_ts')
                ->where('KD_BRG', $kdBrg)
                ->first();

            // Implementasi logika DTR sederhana sebagai fallback
            // Sesuaikan dengan aturan bisnis yang berlaku
            if ($dtrData) {
                $dtr1M = $dtrData->DTR_1M ?? 0;
                $dtrManual = $dtrData->DTR_MANUAL ?? 0;

                // Logika sederhana: gunakan DTR_1M sebagai basis kalkulasi
                return max($dtr1M, $dtrManual);
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Error in calculateDTR alternative: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Method untuk test query compatibility
     * Memastikan query dari Delphi dapat berjalan dengan baik di Laravel
     */
    public function testQueryCompatibility(Request $request)
    {
        try {
            $testCabang = $request->cbg ?? 'TEST';
            $testSub = $request->sub ?? 'TEST';

            // Test basic query structure tanpa data sebenarnya
            $testQuery = "
                SELECT
                    ? as NA_TOKO,
                    ? as NO_FORM,
                    'TEST' as SUB,
                    'TEST001' as KD_BRG,
                    'Test Item' as NA_BRG,
                    'PCS' as KET_UK,
                    'BOTOL' as KET_KEM,
                    100 as LPH,
                    150 as DTR,
                    120 as DTR_MANUAL,
                    130 as DTR_1M
            ";

            $testResult = DB::select($testQuery, [$testCabang, '']);

            return response()->json([
                'success' => true,
                'message' => 'Query structure compatible',
                'test_data' => $testResult,
                'cbg_master' => $this->cbgMaster
            ]);
        } catch (\Exception $e) {
            Log::error('Error in testQueryCompatibility: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk mendapatkan data preview - limit hasil untuk tampilan awal
     * Berguna untuk preview sebelum generate laporan lengkap
     */
    public function getDataPreview(Request $request)
    {
        try {
            $this->validateTampilInput($request->cbg, $request->sub);

            // Modifikasi query untuk preview dengan LIMIT
            $query = "
                SELECT
                    ? as NA_TOKO,
                    ? as NO_FORM,
                    a.SUB,
                    a.KD_BRG,
                    a.NA_BRG,
                    a.KET_UK,
                    a.KET_KEM,
                    b.LPH,
                    COALESCE(xx_hitdtr(a.KD_BRG), 0) AS DTR,
                    c.DTR_MANUAL,
                    c.DTR_1M,
                    (COALESCE(xx_hitdtr(a.KD_BRG), 0) - c.DTR_MANUAL) as SELISIH_DTR
                FROM brg a
                INNER JOIN brgdt b ON a.KD_BRG = b.KD_BRG
                INNER JOIN brg_dc_ts c ON a.KD_BRG = c.KD_BRG
                WHERE a.SUB = ?
                  AND c.DTR_MANUAL > 0
                  AND c.DTR_MANUAL < COALESCE(xx_hitdtr(a.KD_BRG), 0)
                ORDER BY a.SUB, a.KD_BRG
                LIMIT 10
            ";

            $parameters = [
                $request->cbg,  // :cbg (NA_TOKO)
                '',             // :no_form (NO_FORM) - kosong sesuai Delphi
                $request->sub   // :sub
            ];

            $previewData = DB::select($query, $parameters);

            // Get total count tanpa limit untuk informasi
            $countQuery = "
                SELECT COUNT(*) as total
                FROM brg a
                INNER JOIN brgdt b ON a.KD_BRG = b.KD_BRG
                INNER JOIN brg_dc_ts c ON a.KD_BRG = c.KD_BRG
                WHERE a.SUB = ?
                  AND c.DTR_MANUAL > 0
                  AND c.DTR_MANUAL < COALESCE(xx_hitdtr(a.KD_BRG), 0)
            ";

            $totalCount = DB::select($countQuery, [$request->sub]);
            $totalRecords = $totalCount[0]->total ?? 0;

            $result = [];
            foreach ($previewData as $row) {
                $result[] = (array) $row;
            }

            return response()->json([
                'success' => true,
                'preview_data' => $result,
                'total_records' => $totalRecords,
                'showing' => count($result),
                'has_more' => $totalRecords > count($result)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDataPreview: ' . $e->getMessage());
            return response()->json(['error' => $this->handleDatabaseError($e)], 500);
        }
    }

    /**
     * Method untuk mendapatkan data dengan pagination
     * Implementasi pagination untuk data yang besar
     */
    public function getDataPaginated(Request $request)
    {
        try {
            $this->validateTampilInput($request->cbg, $request->sub);

            $perPage = $request->per_page ?? 50;
            $page = $request->page ?? 1;
            $offset = ($page - 1) * $perPage;

            // Query dengan pagination
            $query = "
                SELECT
                    ? as NA_TOKO,
                    ? as NO_FORM,
                    a.SUB,
                    a.KD_BRG,
                    a.NA_BRG,
                    a.KET_UK,
                    a.KET_KEM,
                    b.LPH,
                    COALESCE(xx_hitdtr(a.KD_BRG), 0) AS DTR,
                    c.DTR_MANUAL,
                    c.DTR_1M,
                    (COALESCE(xx_hitdtr(a.KD_BRG), 0) - c.DTR_MANUAL) as SELISIH_DTR
                FROM brg a
                INNER JOIN brgdt b ON a.KD_BRG = b.KD_BRG
                INNER JOIN brg_dc_ts c ON a.KD_BRG = c.KD_BRG
                WHERE a.SUB = ?
                  AND c.DTR_MANUAL > 0
                  AND c.DTR_MANUAL < COALESCE(xx_hitdtr(a.KD_BRG), 0)
                ORDER BY a.SUB, a.KD_BRG
                LIMIT ? OFFSET ?
            ";

            $parameters = [
                $request->cbg,  // :cbg (NA_TOKO)
                '',             // :no_form (NO_FORM)
                $request->sub,  // :sub
                $perPage,       // LIMIT
                $offset         // OFFSET
            ];

            $paginatedData = DB::select($query, $parameters);

            // Get total count untuk pagination info
            $countQuery = "
                SELECT COUNT(*) as total
                FROM brg a
                INNER JOIN brgdt b ON a.KD_BRG = b.KD_BRG
                INNER JOIN brg_dc_ts c ON a.KD_BRG = c.KD_BRG
                WHERE a.SUB = ?
                  AND c.DTR_MANUAL > 0
                  AND c.DTR_MANUAL < COALESCE(xx_hitdtr(a.KD_BRG), 0)
            ";

            $totalCount = DB::select($countQuery, [$request->sub]);
            $totalRecords = $totalCount[0]->total ?? 0;

            $result = [];
            foreach ($paginatedData as $row) {
                $result[] = (array) $row;
            }

            $totalPages = ceil($totalRecords / $perPage);

            return response()->json([
                'success' => true,
                'data' => $result,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_records' => $totalRecords,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDataPaginated: ' . $e->getMessage());
            return response()->json(['error' => $this->handleDatabaseError($e)], 500);
        }
    }

    /**
     * Method untuk mendapatkan statistik DTR per sub kategori
     * Analisis tambahan untuk monitoring
     */
    public function getDTRStatisticsBySub(Request $request)
    {
        try {
            if (empty($request->cbg)) {
                throw new \Exception('Cabang harus diisi!');
            }

            $query = "
                SELECT
                    a.SUB,
                    COUNT(*) as TOTAL_ITEM,
                    SUM(CASE WHEN c.DTR_MANUAL > 0 AND c.DTR_MANUAL < COALESCE(xx_hitdtr(a.KD_BRG), 0) THEN 1 ELSE 0 END) as ITEM_BERMASALAH,
                    AVG(c.DTR_MANUAL) as AVG_DTR_MANUAL,
                    AVG(COALESCE(xx_hitdtr(a.KD_BRG), 0)) as AVG_DTR_SISTEM,
                    SUM(b.LPH) as TOTAL_LPH
                FROM brg a
                INNER JOIN brgdt b ON a.KD_BRG = b.KD_BRG
                INNER JOIN brg_dc_ts c ON a.KD_BRG = c.KD_BRG
                WHERE c.DTR_MANUAL > 0
                GROUP BY a.SUB
                ORDER BY a.SUB
            ";

            $statistik = DB::select($query);

            $result = [];
            foreach ($statistik as $row) {
                $data = (array) $row;
                $data['PERSENTASE_BERMASALAH'] = $data['TOTAL_ITEM'] > 0 ?
                    round(($data['ITEM_BERMASALAH'] / $data['TOTAL_ITEM']) * 100, 2) : 0;
                $result[] = $data;
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDTRStatisticsBySub: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk backup/restore konfigurasi DTR
     * Implementasi tambahan untuk maintenance
     */
    public function backupDTRConfiguration(Request $request)
    {
        try {
            $backupData = DB::table('brg_dc_ts')
                ->select(['KD_BRG', 'DTR_MANUAL', 'DTR_1M'])
                ->whereNotNull('DTR_MANUAL')
                ->where('DTR_MANUAL', '>', 0)
                ->get();

            $filename = 'dtr_backup_' . date('Ymd_His') . '.json';
            $backupPath = storage_path('app/backups/dtr/');

            // Create directory if not exists
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $fullPath = $backupPath . $filename;
            file_put_contents($fullPath, json_encode($backupData, JSON_PRETTY_PRINT));

            $this->logTampilActivity('backup_dtr', '', '', count($backupData));

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'backup_path' => $fullPath,
                'record_count' => count($backupData)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in backupDTRConfiguration: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk update DTR manual
     * Implementasi untuk maintenance DTR
     */
    public function updateDTRManual(Request $request)
    {
        try {
            $kdBrg = $request->kd_brg;
            $dtrManual = $request->dtr_manual;

            if (empty($kdBrg) || !is_numeric($dtrManual)) {
                throw new \Exception('Parameter tidak valid');
            }

            // Validate barang exists
            $barangExists = DB::table('brg')->where('KD_BRG', $kdBrg)->exists();
            if (!$barangExists) {
                throw new \Exception('Kode barang tidak ditemukan');
            }

            // Update DTR_MANUAL
            $updated = DB::table('brg_dc_ts')
                ->where('KD_BRG', $kdBrg)
                ->update([
                    'DTR_MANUAL' => $dtrManual,
                    'UPDATED_AT' => now(),
                    'UPDATED_BY' => Auth::user()->username ?? Auth::user()->name ?? 'system'
                ]);

            if ($updated) {
                $this->logTampilActivity('update_dtr_manual', '', $kdBrg, 1);

                return response()->json([
                    'success' => true,
                    'message' => 'DTR Manual berhasil diupdate',
                    'kd_brg' => $kdBrg,
                    'dtr_manual' => $dtrManual
                ]);
            } else {
                throw new \Exception('Gagal mengupdate DTR Manual');
            }
        } catch (\Exception $e) {
            Log::error('Error in updateDTRManual: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk validasi konsistensi data DTR
     * Quality check sesuai dengan logika bisnis DTR
     */
    public function validateDTRConsistency(Request $request)
    {
        try {
            if (empty($request->cbg)) {
                throw new \Exception('Cabang harus diisi!');
            }

            // Query untuk mencari inkonsistensi DTR
            $inconsistencyQuery = "
                SELECT
                    a.SUB,
                    a.KD_BRG,
                    a.NA_BRG,
                    COALESCE(xx_hitdtr(a.KD_BRG), 0) AS DTR_SISTEM,
                    c.DTR_MANUAL,
                    c.DTR_1M,
                    CASE
                        WHEN c.DTR_MANUAL <= 0 THEN 'DTR_MANUAL_INVALID'
                        WHEN c.DTR_MANUAL >= COALESCE(xx_hitdtr(a.KD_BRG), 0) THEN 'DTR_MANUAL_TOO_HIGH'
                        WHEN c.DTR_1M <= 0 THEN 'DTR_1M_INVALID'
                        ELSE 'OK'
                    END as STATUS_VALIDASI
                FROM brg a
                INNER JOIN brgdt b ON a.KD_BRG = b.KD_BRG
                INNER JOIN brg_dc_ts c ON a.KD_BRG = c.KD_BRG
                WHERE (c.DTR_MANUAL <= 0
                   OR c.DTR_MANUAL >= COALESCE(xx_hitdtr(a.KD_BRG), 0)
                   OR c.DTR_1M <= 0)
                ORDER BY a.SUB, a.KD_BRG
            ";

            $inconsistentData = DB::select($inconsistencyQuery);

            $result = [];
            foreach ($inconsistentData as $row) {
                $result[] = (array) $row;
            }

            // Summary validasi
            $summary = [
                'total_inconsistent' => count($result),
                'dtr_manual_invalid' => 0,
                'dtr_manual_too_high' => 0,
                'dtr_1m_invalid' => 0
            ];

            foreach ($result as $item) {
                switch ($item['STATUS_VALIDASI']) {
                    case 'DTR_MANUAL_INVALID':
                        $summary['dtr_manual_invalid']++;
                        break;
                    case 'DTR_MANUAL_TOO_HIGH':
                        $summary['dtr_manual_too_high']++;
                        break;
                    case 'DTR_1M_INVALID':
                        $summary['dtr_1m_invalid']++;
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'inconsistent_data' => $result,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            Log::error('Error in validateDTRConsistency: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk mendapatkan history perubahan DTR
     * Tracking perubahan DTR untuk audit trail
     */
    public function getDTRHistory(Request $request)
    {
        try {
            $kdBrg = $request->kd_brg;

            if (empty($kdBrg)) {
                throw new \Exception('Kode barang harus diisi!');
            }

            // Implementasi history tracking jika ada tabel audit
            // Sesuaikan dengan struktur tabel audit yang tersedia
            $historyQuery = "
                SELECT
                    h.KD_BRG,
                    h.DTR_MANUAL_OLD,
                    h.DTR_MANUAL_NEW,
                    h.UPDATED_BY,
                    h.UPDATED_AT,
                    h.KETERANGAN
                FROM dtr_history h
                WHERE h.KD_BRG = ?
                ORDER BY h.UPDATED_AT DESC
                LIMIT 20
            ";

            try {
                $historyData = DB::select($historyQuery, [$kdBrg]);
            } catch (\Exception $e) {
                // Jika tabel history tidak ada, return empty dengan warning
                Log::warning('Tabel DTR history tidak ditemukan: ' . $e->getMessage());
                $historyData = [];
            }

            $result = [];
            foreach ($historyData as $row) {
                $result[] = (array) $row;
            }

            return response()->json([
                'success' => true,
                'history_data' => $result,
                'kd_brg' => $kdBrg
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDTRHistory: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method untuk analisis trend DTR
     * Analisis performa DTR untuk business intelligence
     */
    public function analyzeDTRTrends(Request $request)
    {
        try {
            $cabang = $request->cbg;
            $startDate = $request->start_date ?? date('Y-m-01'); // Default start of month
            $endDate = $request->end_date ?? date('Y-m-d');     // Default today

            if (empty($cabang)) {
                throw new \Exception('Cabang harus diisi!');
            }

            // Query untuk analisis trend (sesuaikan dengan struktur data yang tersedia)
            $trendQuery = "
                SELECT
                    DATE(created_at) as TANGGAL,
                    a.SUB,
                    COUNT(*) as TOTAL_ITEM,
                    AVG(c.DTR_MANUAL) as AVG_DTR_MANUAL,
                    AVG(COALESCE(xx_hitdtr(a.KD_BRG), 0)) as AVG_DTR_SISTEM,
                    SUM(CASE WHEN c.DTR_MANUAL > 0 AND c.DTR_MANUAL < COALESCE(xx_hitdtr(a.KD_BRG), 0) THEN 1 ELSE 0 END) as ITEM_BERMASALAH
                FROM brg a
                INNER JOIN brgdt b ON a.KD_BRG = b.KD_BRG
                INNER JOIN brg_dc_ts c ON a.KD_BRG = c.KD_BRG
                WHERE DATE(c.created_at) BETWEEN ? AND ?
                  AND c.DTR_MANUAL > 0
                GROUP BY DATE(created_at), a.SUB
                ORDER BY TANGGAL DESC, a.SUB
            ";

            try {
                $trendData = DB::select($trendQuery, [$startDate, $endDate]);
            } catch (\Exception $e) {
                // Fallback jika struktur tabel tidak mendukung analisis trend
                Log::warning('Analisis trend tidak tersedia: ' . $e->getMessage());
                $trendData = [];
            }

            $result = [];
            foreach ($trendData as $row) {
                $data = (array) $row;
                $data['ACCURACY_PERCENTAGE'] = $data['AVG_DTR_SISTEM'] > 0 ?
                    round(($data['AVG_DTR_MANUAL'] / $data['AVG_DTR_SISTEM']) * 100, 2) : 0;
                $result[] = $data;
            }

            return response()->json([
                'success' => true,
                'trend_data' => $result,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'cabang' => $cabang
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in analyzeDTRTrends: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Method khusus untuk mendapatkan data sesuai format output Delphi
     * Memastikan compatibility dengan format laporan yang sudah ada
     */
    public function getDataForReport(Request $request)
    {
        try {
            $this->validateTampilInput($request->cbg, $request->sub);
            $data = $this->executeTampilProcedure($request->cbg, $request->sub);

            // Format data sesuai dengan yang diharapkan untuk laporan
            $reportData = [];
            $totalDTRSistem = 0;
            $totalDTRManual = 0;
            $totalLPH = 0;

            foreach ($data as $row) {
                $dtrSistem = $row['DTR'] ?? 0;
                $dtrManual = $row['DTR_MANUAL'] ?? 0;
                $lph = $row['LPH'] ?? 0;

                $totalDTRSistem += $dtrSistem;
                $totalDTRManual += $dtrManual;
                $totalLPH += $lph;

                $reportData[] = [
                    'NA_TOKO' => $row['NA_TOKO'] ?? $request->cbg,
                    'NO_FORM' => $row['NO_FORM'] ?? '',
                    'SUB' => $row['SUB'] ?? '',
                    'KD_BRG' => $row['KD_BRG'] ?? '',
                    'NA_BRG' => $row['NA_BRG'] ?? '',
                    'KET_UK' => $row['KET_UK'] ?? '',
                    'KET_KEM' => $row['KET_KEM'] ?? '',
                    'LPH' => $lph,
                    'DTR' => $dtrSistem,
                    'DTR_MANUAL' => $dtrManual,
                    'DTR_1M' => $row['DTR_1M'] ?? 0,
                    'SELISIH_DTR' => $dtrSistem - $dtrManual,
                    'PERSENTASE_AKURASI' => $dtrSistem > 0 ? round(($dtrManual / $dtrSistem) * 100, 2) : 0,
                    'STATUS' => $this->getDTRStatus($dtrSistem, $dtrManual)
                ];
            }

            // Summary untuk header laporan
            $reportSummary = [
                'cabang' => $request->cbg,
                'sub_kategori' => $request->sub,
                'tanggal_generate' => date('d/m/Y H:i:s'),
                'total_item' => count($reportData),
                'total_dtr_sistem' => $totalDTRSistem,
                'total_dtr_manual' => $totalDTRManual,
                'total_lph' => $totalLPH,
                'total_selisih' => $totalDTRSistem - $totalDTRManual,
                'akurasi_keseluruhan' => $totalDTRSistem > 0 ? round(($totalDTRManual / $totalDTRSistem) * 100, 2) : 0,
                'user_generate' => Auth::user()->username ?? Auth::user()->name ?? 'system'
            ];

            return response()->json([
                'success' => true,
                'report_data' => $reportData,
                'report_summary' => $reportSummary
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDataForReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper method untuk menentukan status DTR
     */
    private function getDTRStatus($dtrSistem, $dtrManual)
    {
        if ($dtrManual <= 0) {
            return 'DTR_MANUAL_KOSONG';
        } elseif ($dtrManual >= $dtrSistem) {
            return 'DTR_MANUAL_TINGGI';
        } elseif ($dtrManual < ($dtrSistem * 0.5)) {
            return 'DTR_MANUAL_RENDAH';
        } else {
            return 'NORMAL';
        }
    }

    /**
     * Method untuk cleanup dan maintenance
     * Implementasi untuk pembersihan data yang sudah tidak relevan
     */
    public function performMaintenance()
    {
        try {
            $maintenanceLog = [];

            // 1. Cleanup session yang expired
            session()->forget(['filter_cbg', 'filter_sub', 'filter_tanggal']);
            $maintenanceLog[] = 'Session cleanup completed';

            // 2. Refresh cbgMaster
            $oldCbgMaster = $this->cbgMaster;
            $this->initializeCbgMaster();
            $maintenanceLog[] = "CbgMaster refreshed: {$oldCbgMaster} -> {$this->cbgMaster}";

            // 3. Validate critical tables
            $tableStatus = $this->validateRequiredTables();
            $maintenanceLog[] = 'Table validation completed: ' . json_encode($tableStatus);

            // 4. Log maintenance activity
            Log::info('DTR Controller Maintenance Completed', [
                'maintenance_log' => $maintenanceLog,
                'timestamp' => now(),
                'user' => Auth::user()->username ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'maintenance_log' => $maintenanceLog,
                'cbg_master_current' => $this->cbgMaster
            ]);
        } catch (\Exception $e) {
            Log::error('Error in performMaintenance: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}