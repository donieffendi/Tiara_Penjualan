<?php
namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class ROrderNonKode3Controller extends Controller
{
    public function report()
    {
        Log::info('Generating report for ROrderNonKode3');
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables
        session()->put('filter_cbg', '');
        session()->put('filter_sub1', '');
        session()->put('filter_sub2', '');

        return view('oreport_ordernonkode3.report')->with([
            'cbg'                => $cbg,
            'per'                => $per,
            'hasilOrderNonKode3' => [],
        ]);
    }

    public function getOrderNonKode3Report(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Set filter values to session
        session()->put('filter_cbg', $request->cbg ?? '');
        session()->put('filter_sub1', $request->sub1 ?? '');
        session()->put('filter_sub2', $request->sub2 ?? '');

        $hasilOrderNonKode3 = [];

        if (! empty($request->cbg) && ! empty($request->sub1) && ! empty($request->sub2)) {
            try {
                $hasilOrderNonKode3 = $this->getOrderNonKode3Data($request->cbg, $request->sub1, $request->sub2);
            } catch (\Exception $e) {
                Log::error('Error in getOrderNonKode3Report: ' . $e->getMessage());
                return view('oreport_ordernonkode3.report')->with([
                    'cbg'                => $cbg,
                    'per'                => $per,
                    'hasilOrderNonKode3' => [],
                    'error'              => $e->getMessage(),
                ]);
            }
        }

        return view('oreport_ordernonkode3.report')->with([
            'cbg'                => $cbg,
            'per'                => $per,
            'hasilOrderNonKode3' => $hasilOrderNonKode3,
        ]);
    }

    private function getOrderNonKode3Data($cbg, $sub1, $sub2)
    {
        try {
            // Validasi input
            $this->validateInput($cbg, $sub1, $sub2);

            // Get nama toko berdasarkan kode cabang
            $namaToko = $this->getNamaToko($cbg);
            // dd($namaToko);

            if (empty($namaToko)) {
                throw new \Exception('Cabang tidak ditemukan atau tidak valid!');
            }

            $currentYear = date('Y');

            $hasilData = DB::select("  SELECT *, '$namaToko' as NA_TOKO, '$sub1' as SUB1, '$sub2' as SUB2, left(KD_BRG,3) as SUB,
                if(QTY_SP>=DTR,'OKE',
                    if(QTY_SP>0,'ON ORD',
                    if(CEILING(GREATEST(SRMIN,DTR_APF/2))-STOK<3 AND QTY_SP<3,'ORD<3/BLM SR-',
                        if(QTY_SP=0,'TMO','')))) as KET
                FROM
                (
                    SELECT a.KD_BRG, a.NA_BRG, a.KET_UK, a.SUPP, b.LPH, c.DTR,
                    if(a.SUB in ('031','108','137','143','145','146'), 1.5*b.LPH*if(xx_hitklk(b.KLK)>10, 10, tgz.xx_hitklk(b.KLK)), 2.5*b.LPH*2)  as SRMIN,
                    c.DTR+c.DTR2 DTR_APF, b.AK00+b.GAK00 as STOK,
                    coalesce( (SELECT sum(y.qty) from po x, pod y
                                WHERE x.NO_BUKTI=y.NO_BUKTI AND y.KD_BRG=a.KD_BRG AND x.TYPE<>'KS'
                                AND x.utuh='U' AND x.FLAG in ('PO','SP')) ,0) as QTY_SP
                    from brg a, brgdt b
                    LEFT JOIN brg_dc_ts c on b.KD_BRG=c.KD_BRG
                    WHERE a.KD_BRG=b.KD_BRG AND b.YER=year(now()) AND b.TD_OD=''
                    AND LEFT(a.NA_BRG,1) not in ('3','5','8') AND a.KET_KEM<>'' AND a.SUB not in ('151','158')  AND a.SUB BETWEEN '$sub1' AND '$sub2'
                    HAVING STOK<=CEILING(GREATEST(SRMIN,DTR_APF/2))
                ) as rnonkode3
                ORDER BY KD_BRG ");

            // Transform data sesuai format yang dibutuhkan untuk datatable
            $result = [];
            foreach ($hasilData as $item) {
                $result[] = [
                    'NA_TOKO'      => $item->NA_TOKO ?? '',
                    'SUB1'         => $item->SUB1 ?? '',
                    'SUB2'         => $item->SUB2 ?? '',
                    'KD_BRG'       => $item->KD_BRG ?? '', // Sub Item
                    'NA_BRG'       => $item->NA_BRG ?? '', // Nama Barang
                    'KET_UK'       => $item->KET_UK ?? '', // Ukuran
                    'SUPP'         => $item->SUPP ?? '',
                    'LPH'          => number_format($item->LPH ?? 0, 0),   // LPH
                    'DTR'          => number_format($item->DTR ?? 0, 0),   // DTR
                    'SRMIN'        => number_format($item->SRMIN ?? 0, 2), // SRMIN
                    'DTR_APF'      => number_format($item->DTR_APF ?? 0, 0),
                    'STOK'         => number_format($item->STOK ?? 0, 0),   // Stok
                    'QTY_SP'       => number_format($item->QTY_SP ?? 0, 0), // On SP
                    'SUB'          => $item->SUB ?? '',
                    'TANGGAL_CETAK' => date('d-m-Y'),
                    'KETERANGAN'   => $item->KETERANGAN ?? '', // Keterangan
                                                               // Perhitungan tambahan untuk analisis
                    'THRESHOLD'    => ceil(max($item->SRMIN ?? 0, ($item->DTR_APF ?? 0) / 2)),
                    'SELISIH_STOK' => ($item->STOK ?? 0) - ceil(max($item->SRMIN ?? 0, ($item->DTR_APF ?? 0) / 2)),
                ];
            }

            // Log activity
            $this->logActivity('get_order_nonkode3', $cbg, "SUB: {$sub1}-{$sub2}", count($result));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getOrderNonKode3Data: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getNamaToko($cbg)
    {
        try {
            $result = DB::table('tgz.toko')
                ->select('NAMA_TOKO')
                ->where('KODE', $cbg)
                ->whereIn('STA', ['MA', 'CB', 'DC']) // Pastikan toko aktif
                ->first();

            return $result ? $result->NAMA_TOKO : '';
        } catch (\Exception $e) {
            Log::error('Error in getNamaToko: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get daftar cabang yang valid
     */
    public function getCabangList()
    {
        try {
            $query = "
            SELECT KODE, NAMA_TOKO as NAMA, STA
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
     * Validasi input parameters
     */
    private function validateInput($cbg, $sub1 = null, $sub2 = null)
    {
        if (empty($cbg)) {
            throw new \Exception('Cabang harus diisi!');
        }

        if ($sub1 !== null && empty($sub1)) {
            throw new \Exception('Entrian Sub (Dari) masih kosong..');
        }

        if ($sub2 !== null && empty($sub2)) {
            throw new \Exception('Entrian Sub (Sampai) masih kosong..');
        }

        // Validate cabang format
        if (! preg_match('/^[A-Z0-9]+$/', $cbg)) {
            throw new \Exception('Format cabang tidak valid!');
        }

        // Validate SUB format if provided
        if ($sub1 !== null && ! preg_match('/^[0-9]{3}$/', $sub1)) {
            throw new \Exception('Format Sub (Dari) tidak valid! Harus 3 digit angka.');
        }

        if ($sub2 !== null && ! preg_match('/^[0-9]{3}$/', $sub2)) {
            throw new \Exception('Format Sub (Sampai) tidak valid! Harus 3 digit angka.');
        }

        // Validate range SUB
        if ($sub1 !== null && $sub2 !== null && $sub1 > $sub2) {
            throw new \Exception('Sub (Dari) tidak boleh lebih besar dari Sub (Sampai)!');
        }

        // Validate cabang exists in toko table
        $cabangExists = DB::table('tgz.toko')
            ->where('KODE', $cbg)
            ->whereIn('STA', ['MA', 'CB', 'DC'])
            ->exists();

        if (! $cabangExists) {
            throw new \Exception('Cabang tidak valid atau tidak aktif!');
        }

        return true;
    }

    /**
     * Helper method untuk logging
     */
    private function logActivity($action, $cbg, $additionalInfo = '', $recordCount = 0)
    {
        Log::info("OrderNonKode3: {$action}", [
            'cbg'             => $cbg,
            'additional_info' => $additionalInfo,
            'record_count'    => $recordCount,
            'user'            => auth()->user()->id ?? 'system',
            'timestamp'       => now(),
        ]);
    }

    /**
     * Generate Jasper Report
     */
    public function jasperOrderNonKode3Report(Request $request)
    {
        try {
            $cbg  = $request->cbg ?? '';
            $sub1 = $request->sub1 ?? '';
            $sub2 = $request->sub2 ?? '';

            $data     = $this->getOrderNonKode3Data($cbg, $sub1, $sub2);
            $namaToko = $this->getNamaToko($cbg);

            if (empty($data)) {
                return redirect()->back()->with('error', 'Tidak ada data untuk dicetak!');
            }

            // Prepare Jasper parameters
            $parameters = [
                'REPORT_TITLE'  => 'LAPORAN ORDER NON-KODE 3',
                'COMPANY_NAME'  => 'PT. SUMBER ALFARIA TRIJAYA',
                'CABANG'        => $cbg,
                'NAMA_TOKO'     => $namaToko,
                'SUB_DARI'      => $sub1,
                'SUB_SAMPAI'    => $sub2,
                'TANGGAL_CETAK' => date('d/m/Y H:i:s'),
                'USER_CETAK'    => auth()->user()->name ?? 'System',
            ];

            // Generate report using PHPJasperXML

            $file         = 'ordernonkode3_report';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            $PHPJasperXML->setData($data);
            // dd($data);

            ob_end_clean();
            $PHPJasperXML->outpage("I");

        } catch (\Exception $e) {
            Log::error('Error in jasperOrderNonKode3Report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate report: ' . $e->getMessage());
        }
    }

    /**
     * Get summary statistics for dashboard
     */
    public function getSummaryStats($data)
    {
        if (empty($data)) {
            return [
                'total_items'  => 0,
                'total_stok'   => 0,
                'total_on_sp'  => 0,
                'items_tmo'    => 0,
                'items_oke'    => 0,
                'items_on_ord' => 0,
            ];
        }

        $stats = [
            'total_items'  => count($data),
            'total_stok'   => 0,
            'total_on_sp'  => 0,
            'items_tmo'    => 0,
            'items_oke'    => 0,
            'items_on_ord' => 0,
        ];

        foreach ($data as $item) {
            // Convert formatted numbers back to numeric for calculation
            $stok  = (float) str_replace(',', '', $item['STOK']);
            $qtySp = (float) str_replace(',', '', $item['QTY_SP']);

            $stats['total_stok'] += $stok;
            $stats['total_on_sp'] += $qtySp;

            // Count by keterangan
            switch ($item['KETERANGAN']) {
                case 'TMO':
                    $stats['items_tmo']++;
                    break;
                case 'OKE':
                    $stats['items_oke']++;
                    break;
                case 'ON ORD':
                    $stats['items_on_ord']++;
                    break;
            }
        }

        return $stats;
    }
}