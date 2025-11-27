<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RPemantauanBarangController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_hari', '');

        return view('oreport_pemantauanbarang.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilBarangMacet' => [],
            'hasilBarangSlowMoving' => [],
            'hasilBarangLamaKosong' => []
        ]);
    }

    public function getPemantauanBarangReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg ?? '');
        session()->put('filter_hari', $request->hari ?? '');

        // Default (kosong)
        $hasilBarangMacet = collect();
        $hasilBarangSlowMoving = collect();
        $hasilBarangLamaKosong = collect();

        if (!empty($request->cbg) && !empty($request->hari)) {
            try {
                $hasilBarangMacet = collect($this->getReportData($request->cbg, 'MACET', $request->hari));
                $hasilBarangSlowMoving = collect($this->getReportData($request->cbg, 'SM', $request->hari));
                $hasilBarangLamaKosong = collect($this->getReportData($request->cbg, 'LK', $request->hari));
            } catch (\Exception $e) {
                Log::error('Error in getPemantauanBarangReport: ' . $e->getMessage());

                return view('oreport_pemantauanbarang.report')->with([
                    'cbg' => $cbg,
                    'per' => $per,
                    'hasilBarangMacet' => collect(),
                    'hasilBarangSlowMoving' => collect(),
                    'hasilBarangLamaKosong' => collect(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        

		return view('oreport_pemantauanbarang.report')->with([
			'cbg' => $cbg,
			'per' => $per,
			'hasilBarangMacet' => $hasilBarangMacet,
			'hasilBarangSlowMoving' => $hasilBarangSlowMoving,
			'hasilBarangLamaKosong' => $hasilBarangLamaKosong
		]);

    }


    private function paginateCollection(Collection $items, $perPage = 20, $page = null, $pageName = 'page')
	{
		$page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

		$currentPageItems = $items->slice(($page - 1) * $perPage, $perPage)->values();

		return new LengthAwarePaginator(
			$currentPageItems,
			$items->count(),
			$perPage,
			$page,
			[
				'path' => request()->url(),
				'query' => request()->query(),
				'pageName' => $pageName,
			]
		);
	}


    /**
     * Implementasi logika dari procedure report_frmrpantau_brg() pada Delphi
     * Mengambil data berdasarkan jenis pemantauan: MACET, SM, atau LK
     */
    private function getReportData($cbg, $jenis, $hari)
    {
        try {
            // Validasi input
            $this->validateInput($cbg, $jenis, $hari);

            // Validate cabang exists in toko table
            $cabangExists = DB::table('tgz.toko')
                ->where('KODE', $cbg)
                ->whereIn('STA', ['MA', 'CB', 'DC'])
                ->exists();

            if (!$cabangExists) {
                throw new \Exception('Cabang tidak valid atau tidak aktif!');
            }

            // Call stored procedure sesuai dengan logik Delphi
            $query = "CALL {$cbg}.report_frmrpantau_brg(?, ?)";

            $hasilData = DB::select($query, [
                $jenis,
                floatval($hari)
            ]);

            // Transform data sesuai format yang dibutuhkan
            $result = [];
            foreach ($hasilData as $item) {
                $result[] = [
                    'KD_BRG' => $item->KD_BRG ?? '',
                    'NA_BRG' => $item->NA_BRG ?? '',
                    'KET_UK' => $item->KET_UK ?? '',
                    'KET_KEM' => $item->KET_KEM ?? '',
                    'SUPP' => $item->SUPP ?? '',
                    'KDLAKU' => $item->KDLAKU ?? '',
                    'KLK' => $item->KLK ?? 0,
                    'STOCKT' => $item->TK ?? 0,
                    'STOCKG' => $item->GD ?? 0,
                    'QTY_TRM' => $item->QTY_TRM ?? 0,
                    'SRMAX' => $item->SRMAX ?? 0,
                    'TRM' => $item->TRM ?? '',
                    'TGL_TRM' => $item->TGL_TRM ?? '',
                    'TGL_BK' => $item->TGL_BK ?? '',
                    'TGL_AT' => $item->TGL_AT ?? '',
                    'KSR' => $item->KSR ?? null,
                    'TDX' => $item->TDX ?? '',
                    'SELISIH_HARI' => $this->calculateSelisihHari($item, $jenis),
                    'STATUS_BARANG' => $this->getStatusBarang($item, $jenis),
                    'NILAI_STOCK' => $this->calculateNilaiStock($item)
                ];
            }

            // Log activity
            $this->logActivity('get_report_data', $cbg, $jenis, $hari, count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getReportData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate laporan Jasper
     */
    public function jasperPemantauanBarangReport(Request $request)
    {
        try {
            $file = 'pemantauanbarang';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

            // Set session values
            session()->put('filter_cbg', $request->cbg ?? '');
            session()->put('filter_hari', $request->hari ?? '');

            $data = [];

            if (!empty($request->cbg) && !empty($request->hari) && $request->hari != 0) {
                $jenis = $request->jenis ?? 'MACET';
                $hasilPemantauan = $this->getReportData($request->cbg, $jenis, $request->hari);
                $namaToko = $this->getNamaToko($request->cbg);

                foreach ($hasilPemantauan as $item) {
                    $data[] = array_merge($item, [
                        'CBG' => $request->cbg,
                        'NAMA_TOKO' => $namaToko,
                        'JENIS_LAPORAN' => $jenis,
                        'FILTER_HARI' => $request->hari,
                        'TANGGAL_CETAK' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            $PHPJasperXML->setData($data);

            // Set parameters untuk report
            $PHPJasperXML->arrayParameter = [
                "CBG" => $request->cbg ?? '',
                "JENIS_LAPORAN" => $request->jenis ?? 'MACET',
                "FILTER_HARI" => $request->hari ?? 0,
                "NAMA_TOKO" => $this->getNamaToko($request->cbg ?? ''),
                "TANGGAL_CETAK" => date('d/m/Y H:i:s'),
                "TOTAL_RECORDS" => count($data)
            ];

            ob_end_clean();
            $PHPJasperXML->outpage("I"); // I = Inline view, D = Download, F = Save to file

        } catch (\Exception $e) {
            Log::error('Error in jasperPemantauanBarangReport: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Helper methods
    private function calculateSelisihHari($item, $jenis)
    {
        try {
            $today = now();
            $targetDate = null;

            switch ($jenis) {
                case 'MACET':
                    $targetDate = $item->TGL_TK ?? null;
                    break;
                case 'SM':
                    $targetDate = $item->TGL_AT ?? null;
                    break;
                case 'LK':
                    $targetDate = $item->TGL_BK ?? $item->TGL_AT ?? null;
                    break;
            }

            if ($targetDate) {
                return $today->diffInDays($targetDate);
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getStatusBarang($item, $jenis)
    {
        switch ($jenis) {
            case 'MACET':
                return 'Barang Macet';
            case 'SM':
                return 'Slow Moving';
            case 'LK':
                return 'Lama Kosong';
            default:
                return 'Normal';
        }
    }

    private function calculateNilaiStock($item)
    {
        $stockt = floatval($item->STOCKT ?? 0);
        $stockg = floatval($item->STOCKG ?? 0);
        return $stockt * $stockg;
    }

    private function validateInput($cbg, $jenis, $hari)
    {
        if (empty($cbg)) {
            throw new \Exception('Cabang harus diisi!');
        }

        if (empty($jenis)) {
            throw new \Exception('Jenis laporan harus diisi!');
        }

        if (empty($hari) || $hari == 0) {
            throw new \Exception('Filter hari harus diisi!');
        }

        if (!in_array($jenis, ['MACET', 'SM', 'LK'])) {
            throw new \Exception('Jenis laporan tidak valid!');
        }

        if (!is_numeric($hari) || $hari <= 0) {
            throw new \Exception('Filter hari harus berupa angka positif!');
        }

        return true;
    }

    private function logActivity($action, $cbg, $jenis, $hari, $recordCount = 0)
    {
        Log::info("PemantauanBarang: {$action}", [
            'cbg' => $cbg,
            'jenis' => $jenis,
            'hari' => $hari,
            'record_count' => $recordCount,
            'user' => auth()->user()->id ?? 'system',
            'timestamp' => now()
        ]);
    }

    public function getNamaToko($cbg)
    {
        try {
            $result = DB::table('tgz.toko')
                ->select('NA_TOKO')
                ->where('KODE', $cbg)
                ->first();

            return $result ? $result->NA_TOKO : '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }
}
