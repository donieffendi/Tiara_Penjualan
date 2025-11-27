<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RKartuStokController extends Controller
{
    public function report()
    {
        // Get all branches
        try {
            // $cbg = DB::select("SELECT DISTINCT CBG FROM master_cbg ORDER BY CBG");

            $cbg = Cbg::groupBy('CBG')->get();

            // Get periods
            // $per = DB::select("SELECT * FROM master_perid ORDER BY PERID DESC");
            $per = Perid::query()->get();

        } catch (\Exception $e) {
            $cbg = [];
            $per = [];
        }

        // Initialize session variables
        session()->put('filter_cbg', session()->get('filter_cbg', ''));
        session()->put('filter_per', session()->get('filter_per', date("m-Y")));
        session()->put('filter_kd_brg', session()->get('filter_kd_brg', ''));
        session()->put('filter_jenis', session()->get('filter_jenis', 'toko'));

        return view('oreport_kartustok.report')->with([
            'cbg' => $cbg,
            'per' => $per,
            'hasilKartu' => [],
        ]);
    }

    public function getKartuStokReport(Request $request)
    {
        try {
            $cbgCode = trim($request->cbg);
            $periode = trim($request->periode); // Format: MM-YYYY
            $kdBrg = trim($request->kd_brg);
            $jenis = $request->jenis ?? 'toko';

            // Validation
            if (empty($cbgCode) || empty($periode) || empty($kdBrg)) {
                return back()->with('error', 'Parameter tidak lengkap. Harap isi Cabang, Periode, dan Kode Barang.');
            }

            // Save to session
            session()->put('filter_cbg', $cbgCode);
            session()->put('filter_per', $periode);
            session()->put('filter_kd_brg', $kdBrg);
            session()->put('filter_jenis', $jenis);

            // Parse periode
            $bulan = substr($periode, 0, 2);
            $tahun = substr($periode, 3, 4);

            // Determine table names
            $brgdtTable = $cbgCode . '.brgdt';
            $brgdTable = $cbgCode . '.brgd';

            if ($tahun != date('Y')) {
                $brgdtTable .= $tahun;
                $brgdTable .= $tahun;
            }

            $hasilKartu = [];

            // Get data based on type
            if ($jenis === 'toko') {
                $hasilKartu = $this->getKartuStockToko($cbgCode, $periode, $kdBrg, $bulan, $tahun, $brgdtTable, $brgdTable);
            } elseif ($jenis === 'gudang') {
                $hasilKartu = $this->getKartuStockGudang($cbgCode, $periode, $kdBrg, $bulan, $tahun, $brgdTable);
            } elseif ($jenis === 'retur') {
                $hasilKartu = $this->getKartuStockRetur($cbgCode, $periode, $kdBrg, $bulan, $tahun, $brgdtTable);
            }

            $cbg = DB::select("SELECT DISTINCT CBG FROM master_cbg ORDER BY CBG");
            $per = DB::select("SELECT * FROM master_perid ORDER BY PERID DESC");

            return view('oreport_kartustok.report')->with([
                'cbg' => $cbg,
                'per' => $per,
                'hasilKartu' => $hasilKartu,
                'success' => 'Data berhasil dimuat.',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function getKartuStockToko($cbgCode, $periode, $kdBrg, $bulan, $tahun, $brgdtTable, $brgdTable)
    {
        $query = "SELECT * , @AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN AS SALDO FROM (
            SELECT 'Saldo Awal' as no_bukti, '' as tgl, a.kd_brg, a.NA_BRG,
                   IF(:bulan1=MONTH(NOW()), a.AW00, a.AW{$bulan}) + b.aw{$bulan} as awal,
                   0 as masuk, 0 as keluar, 0 AS LAIN,
                   'AW' AS FLAG, 0 AS URT
            FROM {$brgdtTable} a, {$brgdTable} b
            WHERE a.KD_BRG = b.KD_BRG
            AND a.yer = :year1
            AND b.yer = :year2
            AND a.KD_BRG = :kdBrg1
            AND a.cbg = :cbg1
            AND b.cbg = :cbg2

            UNION ALL

            -- Pembelian (BL, BZ, BD, B3, B5, B8) - Toko
            SELECT beliz.no_bukti, beliz.TGL, belizd.KD_BRG, belizd.NA_BRG, 0 as awal,
                   belizd.qty AS MASUK, 0 AS KELUAR, 0 AS LAIN, beliz.FLAG, 1 AS URT
            FROM {$cbgCode}.beliz, {$cbgCode}.belizd
            WHERE beliz.NO_BUKTI = belizd.NO_BUKTI
            AND beliz.CBG = :cbg3
            AND beliz.PER = :periode1
            AND beliz.flag IN ('BL', 'BZ', 'BD', 'B3', 'B5', 'B8')
            AND belizd.kd_brg = :kdBrg2
            AND belizd.qty <> 0
            AND belizd.kdlaku NOT IN ('0', '1')

            UNION ALL

            -- Order Toko (OT)
            SELECT stockaz.NO_PO as no_bukti, stockaz.TGL, stockazd.KD_BRG, stockazd.NA_BRG, 0 as awal,
                   stockazd.QTY AS MASUK, 0 AS KELUAR, 0 AS LAIN, stockaz.FLAG, 2 AS URT
            FROM {$cbgCode}.stockaz, {$cbgCode}.stockazd
            WHERE stockaz.NO_BUKTI = stockazd.NO_BUKTI
            AND stockaz.PER = :periode2
            AND stockaz.cbg = :cbg4
            AND stockaz.flag = 'OT'
            AND stockazd.KD_BRG = :kdBrg3
            AND stockazd.qty <> 0

            UNION ALL

            -- Order Outlet (OO) - Keluar dari Toko
            SELECT stockaz.no_bukti, stockaz.TGL, stockazd.KD_BRG, stockazd.NA_BRG, 0 as awal,
                   0 AS MASUK, stockazd.QTY AS KELUAR, 0 AS LAIN, stockaz.FLAG, 2 AS URT
            FROM {$cbgCode}.stockaz, {$cbgCode}.stockazd
            WHERE stockaz.NO_BUKTI = stockazd.NO_BUKTI
            AND stockaz.PER = :periode3
            AND stockaz.flag = 'OO'
            AND stockazd.abl <> 'GD'
            AND stockazd.KD_BRG = :kdBrg4
            AND stockaz.cbg = :cbg5
            AND stockazd.qty <> 0

            UNION ALL

            -- Order DC (OD)
            SELECT stockaz.no_bukti, stockaz.TGL, stockazd.KD_BRG, stockazd.NA_BRG, 0 as awal,
                   0 AS MASUK, stockazd.QTY AS KELUAR, 0 AS LAIN, stockaz.FLAG, 2 AS URT
            FROM {$cbgCode}.stockaz, {$cbgCode}.stockazd
            WHERE stockaz.NO_BUKTI = stockazd.NO_BUKTI
            AND stockaz.PER = :periode4
            AND stockaz.flag = 'OD'
            AND stockazd.KD_BRG = :kdBrg5
            AND stockaz.cbg = :cbg6
            AND stockazd.qty <> 0

            UNION ALL

            -- Jual Toko (JT)
            SELECT stockaz.no_bukti, stockaz.TGL, stockazd.KD_BRG, stockazd.NA_BRG, 0 as awal,
                   stockazd.qty AS MASUK, 0 AS KELUAR, 0 AS LAIN, stockaz.FLAG, 2 AS URT
            FROM {$cbgCode}.stockaz, {$cbgCode}.stockazd
            WHERE stockaz.NO_BUKTI = stockazd.NO_BUKTI
            AND stockaz.CBG = :cbg7
            AND stockaz.PER = :periode5
            AND stockaz.flag = 'JT'
            AND stockazd.KD_BRG = :kdBrg6
            AND stockazd.qty <> 0
            AND stockazd.kdlaku NOT IN ('0', '1')

            UNION ALL

            -- Order Produksi (OP) - Masuk
            SELECT stockaz.no_bukti, stockaz.TGL, stockazd.KD_BRG, stockazd.NA_BRG, 0 as awal,
                   stockazd.QTY AS MASUK, 0 AS KELUAR, 0 AS LAIN, stockaz.FLAG, 2 AS URT
            FROM {$cbgCode}.stockaz, {$cbgCode}.stockazd
            WHERE stockaz.NO_BUKTI = stockazd.NO_BUKTI
            AND stockaz.PER = :periode6
            AND stockaz.flag = 'OP'
            AND stockazd.JNS = 'A'
            AND stockazd.KD_BRG = :kdBrg7
            AND stockaz.cbg = :cbg8
            AND stockazd.qty <> 0

            UNION ALL

            -- Pembelian Gudang (BL, BZ, BD) - Masuk ke Toko dari Gudang
            SELECT beliz.no_bukti, beliz.tg_smp as TGL, belizd.KD_BRG, belizd.NA_BRG, 0 AS AWAL,
                   belizd.qty AS MASUK, 0 AS KELUAR, 0 AS LAIN, beliz.FLAG, 3 AS URT
            FROM {$cbgCode}.beliz, {$cbgCode}.belizd
            WHERE beliz.NO_BUKTI = belizd.NO_BUKTI
            AND beliz.CBG = :cbg9
            AND beliz.PER = :periode7
            AND beliz.flag IN ('BL', 'BZ', 'BD')
            AND belizd.kd_brg = :kdBrg8
            AND belizd.qty <> 0
            AND belizd.kdlaku IN ('0', '1')

            UNION ALL

            -- Order Produksi (OP) - Keluar (Typ T)
            SELECT stockaz.no_bukti, stockaz.TGL, stockazd.KD_BRG, stockazd.NA_BRG, 0 as awal,
                   0 AS MASUK, stockazd.QTY AS KELUAR, 0 AS LAIN, stockaz.FLAG, 3 AS URT
            FROM {$cbgCode}.stockaz, {$cbgCode}.stockazd
            WHERE stockaz.NO_BUKTI = stockazd.NO_BUKTI
            AND stockaz.PER = :periode8
            AND stockaz.flag = 'OP'
            AND stockazd.JNS = 'B'
            AND stockazd.TYP = 'T'
            AND stockazd.KD_BRG = :kdBrg9
            AND stockaz.cbg = :cbg10
            AND stockazd.qty <> 0

            UNION ALL

            -- Retur VR
            SELECT retur.no_bukti, retur.TGL, returd.KD_BRG, returd.NA_BRG, 0 as awal,
                   0 AS MASUK, 0 AS KELUAR, returd.qty AS LAIN, retur.FLAG, 5 AS URT
            FROM {$cbgCode}.retur, {$cbgCode}.returd
            WHERE retur.NO_BUKTI = returd.NO_BUKTI
            AND retur.PER = :periode9
            AND retur.flag = 'VR'
            AND returd.KD_BRG = :kdBrg10
            AND returd.kdlaku = '5'
            AND retur.POSTED = 1
            AND retur.cbg = :cbg11
            AND returd.qty <> 0

            UNION ALL

            -- Retur GR
            SELECT retur.no_bukti, retur.TGL, returd.KD_BRG, returd.NA_BRG, 0 as awal,
                   0 AS MASUK, 0 AS KELUAR, returd.qty AS LAIN, retur.FLAG, 5 AS URT
            FROM {$cbgCode}.retur, {$cbgCode}.returd
            WHERE retur.NO_BUKTI = returd.NO_BUKTI
            AND retur.PER = :periode10
            AND retur.flag = 'GR'
            AND returd.KD_BRG = :kdBrg11
            AND retur.cbg = :cbg12
            AND returd.qty <> 0
            AND retur.POSTED = 1

            UNION ALL

            -- Retur OX
            SELECT retur.no_bukti, retur.TGL, returd.KD_BRG, returd.NA_BRG, 0 as awal,
                   0 AS MASUK, 0 AS KELUAR, returd.qty AS LAIN, retur.FLAG, 5 AS URT
            FROM {$cbgCode}.retur, {$cbgCode}.returd
            WHERE retur.NO_BUKTI = returd.NO_BUKTI
            AND retur.cbg = :cbg13
            AND retur.PER = :periode11
            AND retur.flag = 'OX'
            AND retur.POSTED = 1
            AND returd.KD_BRG = :kdBrg12
            AND returd.qty <> 0
            AND returd.kdlaku NOT IN ('0', '1')

            UNION ALL

            -- Penyesuaian PM (-)
            SELECT stockbz.no_bukti, stockbz.TGL, stockbzd.KD_BRG, stockbzd.NA_BRG, 0 as awal,
                   0 AS MASUK, 0 AS KELUAR, stockbzd.qty * -1 AS LAIN, stockbz.FLAG, 5 AS URT
            FROM {$cbgCode}.stockbz, {$cbgCode}.stockbzd
            WHERE stockbz.NO_BUKTI = stockbzd.NO_BUKTI
            AND stockbz.PER = :periode12
            AND stockbz.flag = 'PM'
            AND stockbzd.KD_BRG = :kdBrg13
            AND stockbz.cbg = :cbg14
            AND stockbzd.qty <> 0

            UNION ALL

            -- Penyesuaian PM (+) KD_BRG2
            SELECT stockbz.no_bukti, stockbz.TGL, stockbzd.KD_BRG2 as KD_BRG, stockbzd.NA_BRG2 as NA_BRG, 0 as awal,
                   0 AS MASUK, 0 AS KELUAR, stockbzd.qty2 AS LAIN, stockbz.FLAG, 5 AS URT
            FROM {$cbgCode}.stockbz, {$cbgCode}.stockbzd
            WHERE stockbz.NO_BUKTI = stockbzd.NO_BUKTI
            AND stockbz.PER = :periode13
            AND stockbz.flag = 'PM'
            AND stockbzd.KD_BRG2 = :kdBrg14
            AND stockbz.cbg = :cbg15
            AND stockbzd.qty2 <> 0

            UNION ALL

            -- Mutasi Transfer (MT, AO, AK)
            SELECT stockbz.no_bukti, stockbz.TGL, stockbzd.KD_BRG, stockbzd.NA_BRG, 0 as awal,
                   0 AS MASUK, 0 AS KELUAR, stockbzd.qty AS LAIN, stockbz.FLAG, 5 AS URT
            FROM {$cbgCode}.stockbz, {$cbgCode}.stockbzd
            WHERE stockbz.NO_BUKTI = stockbzd.NO_BUKTI
            AND stockbz.PER = :periode14
            AND stockbz.flag IN ('MT', 'AO', 'AK')
            AND stockbzd.KD_BRG = :kdBrg15
            AND stockbz.cbg = :cbg16
            AND stockbzd.qty <> 0

            UNION ALL

            -- Transfer Stock (TS)
            SELECT stockbz.no_bukti, stockbz.tgl_posted as TGL, stockbzd.KD_BRG, stockbzd.NA_BRG, 0 as awal,
                   0 AS MASUK, stockbzd.qty AS KELUAR, 0 AS LAIN, stockbz.FLAG, 5 AS URT
            FROM {$cbgCode}.stockbz, {$cbgCode}.stockbzd
            WHERE stockbz.NO_BUKTI = stockbzd.NO_BUKTI
            AND stockbz.CBG = :cbg17
            AND stockbz.PER = :periode15
            AND stockbz.flag = 'TS'
            AND stockbzd.KD_BRG = :kdBrg16
            AND stockbzd.qty <> 0

            UNION ALL

            -- Penjualan (JL, RF, EC)
            SELECT no_bukti, TGL, KD_BRG, NA_BRG, 0 as awal,
                   0 AS MASUK, QTY AS KELUAR, 0 AS LAIN, FLAG, 3 AS URT
            FROM {$cbgCode}.juald{$bulan}
            WHERE cbg = :cbg18
            AND PER = :periode16
            AND FLAG IN ('JL', 'RF', 'EC')
            AND KD_BRG = :kdBrg17

            UNION ALL

            -- Survey Pembelian (BS)
            SELECT survey.NO_BUKTI, survey.TGL, surveyd.KD_BRG, surveyd.NA_BRG, 0 as awal,
                   surveyd.R_PBL AS MASUK, 0 AS KELUAR, 0 AS LAIN, survey.flag, 5 AS URT
            FROM {$cbgCode}.survey, {$cbgCode}.surveyd
            WHERE survey.NO_BUKTI = surveyd.AG_PBL
            AND survey.POSTED = 1
            AND surveyd.KDLAKU NOT IN ('0', '1')
            AND survey.flag = 'BS'
            AND survey.PER = :periode17
            AND survey.CBG = :cbg19
            AND surveyd.KD_BRG = :kdBrg18

            UNION ALL

            -- Survey Penjualan (PS)
            SELECT survey.NO_BUKTI, survey.TGL, surveyd.KD_BRG, surveyd.NA_BRG, 0 as awal,
                   surveyd.R_PJL AS MASUK, 0 AS KELUAR, 0 AS LAIN, survey.flag, 5 AS URT
            FROM {$cbgCode}.survey, {$cbgCode}.surveyd
            WHERE survey.NO_BUKTI = surveyd.AG_PJL
            AND survey.POSTED = 1
            AND surveyd.KDLAKU NOT IN ('0', '1')
            AND survey.flag = 'PS'
            AND survey.PER = :periode18
            AND survey.CBG = :cbg20
            AND surveyd.KD_BRG = :kdBrg19

        ) AS AA JOIN (SELECT @AKHIR:=0) AS BB ON 1=1
        ORDER BY KD_BRG, tgl, urt";

        try {
            return DB::select($query, [
                'bulan1' => $bulan,
                'year1' => $tahun,
                'year2' => $tahun,
                'kdBrg1' => $kdBrg,
                'cbg1' => $cbgCode,
                'cbg2' => $cbgCode,
                'cbg3' => $cbgCode,
                'periode1' => $periode,
                'kdBrg2' => $kdBrg,
                'periode2' => $periode,
                'cbg4' => $cbgCode,
                'kdBrg3' => $kdBrg,
                'periode3' => $periode,
                'kdBrg4' => $kdBrg,
                'cbg5' => $cbgCode,
                'periode4' => $periode,
                'kdBrg5' => $kdBrg,
                'cbg6' => $cbgCode,
                'cbg7' => $cbgCode,
                'periode5' => $periode,
                'kdBrg6' => $kdBrg,
                'periode6' => $periode,
                'kdBrg7' => $kdBrg,
                'cbg8' => $cbgCode,
                'cbg9' => $cbgCode,
                'periode7' => $periode,
                'kdBrg8' => $kdBrg,
                'periode8' => $periode,
                'kdBrg9' => $kdBrg,
                'cbg10' => $cbgCode,
                'periode9' => $periode,
                'kdBrg10' => $kdBrg,
                'cbg11' => $cbgCode,
                'periode10' => $periode,
                'kdBrg11' => $kdBrg,
                'cbg12' => $cbgCode,
                'cbg13' => $cbgCode,
                'periode11' => $periode,
                'kdBrg12' => $kdBrg,
                'periode12' => $periode,
                'kdBrg13' => $kdBrg,
                'cbg14' => $cbgCode,
                'periode13' => $periode,
                'kdBrg14' => $kdBrg,
                'cbg15' => $cbgCode,
                'periode14' => $periode,
                'kdBrg15' => $kdBrg,
                'cbg16' => $cbgCode,
                'cbg17' => $cbgCode,
                'periode15' => $periode,
                'kdBrg16' => $kdBrg,
                'cbg18' => $cbgCode,
                'periode16' => $periode,
                'kdBrg17' => $kdBrg,
                'periode17' => $periode,
                'cbg19' => $cbgCode,
                'kdBrg18' => $kdBrg,
                'periode18' => $periode,
                'cbg20' => $cbgCode,
                'kdBrg19' => $kdBrg,
            ]);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getKartuStockGudang($cbgCode, $periode, $kdBrg, $bulan, $tahun, $brgdTable)
    {
        $query = "SELECT * , @AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN AS SALDO FROM (
            SELECT 'Saldo Awal' as no_bukti, '' as tgl, kd_brg, NA_BRG,
                   IF(:bulan1=MONTH(NOW()), gAW00, gAW{$bulan}) as awal,
                   0 as masuk, 0 as keluar, 0 AS LAIN,
                   'AW' AS FLAG, 0 AS URT
            FROM {$brgdTable}
            WHERE yer = :year1
            AND KD_BRG = :kdBrg1
            AND cbg = :cbg1

            UNION ALL

            -- Pembelian Gudang (BL, BZ, BD)
            SELECT beliz.no_bukti, beliz.TGL, belizd.KD_BRG, belizd.NA_BRG, 0 as awal,
                   belizd.qty AS MASUK, 0 AS KELUAR, 0 AS LAIN, beliz.FLAG, 3 AS URT
            FROM {$cbgCode}.beliz, {$cbgCode}.belizd
            WHERE beliz.NO_BUKTI = belizd.NO_BUKTI
            AND beliz.CBG = :cbg2
            AND beliz.PER = :periode1
            AND beliz.flag IN ('BL', 'BZ', 'BD')
            AND belizd.kd_brg = :kdBrg2
            AND belizd.qty <> 0
            AND belizd.kdlaku IN ('0', '1')

            UNION ALL

            -- Order Toko dari Gudang (Keluar)
            SELECT stockaz.NO_PO as no_bukti, stockaz.tg_smp as TGL, stockazd.KD_BRG, stockazd.NA_BRG, 0 AS AWAL,
                   0 AS MASUK, stockazd.QTY AS KELUAR, 0 AS LAIN, stockaz.FLAG, 4 AS URT
            FROM {$cbgCode}.stockaz, {$cbgCode}.stockazd
            WHERE stockaz.NO_BUKTI = stockazd.NO_BUKTI
            AND stockaz.PER = :periode2
            AND stockaz.flag = 'OT'
            AND stockazd.KD_BRG = :kdBrg3
            AND stockaz.CBG = :cbg3
            AND stockazd.qty <> 0

            UNION ALL

            -- Pelayanan Outlet dari Gudang (Keluar)
            SELECT stockaz.no_bukti, stockaz.tg_smp as TGL, stockazd.KD_BRG, stockazd.NA_BRG, 0 AS AWAL,
                   0 AS MASUK, stockazd.QTY AS KELUAR, 0 AS LAIN, stockaz.FLAG, 5 AS URT
            FROM {$cbgCode}.stockaz, {$cbgCode}.stockazd
            WHERE stockaz.NO_BUKTI = stockazd.NO_BUKTI
            AND stockaz.PER = :periode3
            AND stockaz.flag = 'OO'
            AND stockazd.abl = 'GD'
            AND stockazd.KD_BRG = :kdBrg4
            AND stockaz.CBG = :cbg4
            AND stockazd.qty <> 0

            UNION ALL

            -- Penjualan ke Toko dari Gudang (Masuk)
            SELECT stockaz.no_bukti, stockaz.tg_smp as TGL, stockazd.KD_BRG, stockazd.NA_BRG, 0 AS AWAL,
                   stockazd.qty AS MASUK, 0 AS KELUAR, 0 AS LAIN, stockaz.FLAG, 6 AS URT
            FROM {$cbgCode}.stockaz, {$cbgCode}.stockazd
            WHERE stockaz.NO_BUKTI = stockazd.NO_BUKTI
            AND stockaz.CBG = :cbg5
            AND stockaz.PER = :periode4
            AND stockaz.flag = 'JT'
            AND stockazd.KD_BRG = :kdBrg5
            AND stockazd.qty <> 0
            AND stockazd.kdlaku IN ('0', '1')

            UNION ALL

            -- Order Produksi Gudang (Keluar)
            SELECT stockaz.no_bukti, stockaz.tg_smp as TGL, stockazd.KD_BRG, stockazd.NA_BRG, 0 AS AWAL,
                   0 AS MASUK, stockazd.QTY AS KELUAR, 0 AS LAIN, stockaz.FLAG, 7 AS URT
            FROM {$cbgCode}.stockaz, {$cbgCode}.stockazd
            WHERE stockaz.NO_BUKTI = stockazd.NO_BUKTI
            AND stockaz.PER = :periode5
            AND stockaz.flag = 'OP'
            AND stockazd.JNS = 'B'
            AND stockazd.TYP = 'G'
            AND stockazd.KD_BRG = :kdBrg6
            AND stockaz.CBG = :cbg6
            AND stockazd.qty <> 0

            UNION ALL

            -- Retur Outlet ke Gudang (Lain)
            SELECT retur.no_bukti, retur.tg_smp as TGL, returd.KD_BRG, returd.NA_BRG, 0 AS AWAL,
                   0 AS MASUK, 0 AS KELUAR, returd.qty AS LAIN, retur.FLAG, 8 AS URT
            FROM {$cbgCode}.retur, {$cbgCode}.returd
            WHERE retur.NO_BUKTI = returd.NO_BUKTI
            AND retur.CBG = :cbg7
            AND retur.posted = 1
            AND retur.PER = :periode6
            AND retur.flag IN ('RZ', 'OX')
            AND returd.KD_BRG = :kdBrg7
            AND returd.qty <> 0
            AND returd.kdlaku IN ('0', '1')

            UNION ALL

            -- Tukar Guling Gudang
            SELECT retur.no_bukti, retur.tg_smp as TGL, returd.KD_BRG, returd.NA_BRG, 0 AS AWAL,
                   0 AS MASUK, returd.qty * -1 AS KELUAR, 0 AS LAIN, retur.FLAG, 9 AS URT
            FROM {$cbgCode}.retur, {$cbgCode}.returd
            WHERE retur.NO_BUKTI = returd.NO_BUKTI
            AND retur.CBG = :cbg8
            AND retur.posted = 1
            AND retur.PER = :periode7
            AND retur.flag = 'RG'
            AND returd.KD_BRG = :kdBrg8
            AND returd.qty <> 0
            AND returd.kdlaku IN ('0', '1')

            UNION ALL

            -- Gudang Stock (GS, MG)
            SELECT stockbz.no_bukti, stockbz.tg_smp as TGL, stockbzd.KD_BRG, stockbzd.NA_BRG, 0 AS AWAL,
                   0 AS MASUK, 0 AS KELUAR, stockbzd.qty AS LAIN, stockbz.FLAG, 10 AS URT
            FROM {$cbgCode}.stockbz, {$cbgCode}.stockbzd
            WHERE stockbz.NO_BUKTI = stockbzd.NO_BUKTI
            AND stockbz.PER = :periode8
            AND stockbz.flag IN ('GS', 'MG')
            AND stockbzd.KD_BRG = :kdBrg9
            AND stockbz.CBG = :cbg9
            AND stockbzd.qty <> 0

            UNION ALL

            -- Survey Pembelian Gudang (BS)
            SELECT survey.NO_BUKTI, survey.tg_smp as TGL, surveyd.KD_BRG, surveyd.NA_BRG, 0 as awal,
                   surveyd.R_PBL AS MASUK, 0 AS KELUAR, 0 AS LAIN, survey.flag, 11 AS URT
            FROM {$cbgCode}.survey, {$cbgCode}.surveyd
            WHERE survey.NO_BUKTI = surveyd.AG_PBL
            AND survey.posted = 1
            AND surveyd.KDLAKU IN ('0', '1')
            AND survey.flag = 'BS'
            AND survey.PER = :periode9
            AND survey.CBG = :cbg10
            AND surveyd.KD_BRG = :kdBrg10

            UNION ALL

            -- Survey Penjualan Gudang (PS)
            SELECT survey.NO_BUKTI, survey.tg_smp as TGL, surveyd.KD_BRG, surveyd.NA_BRG, 0 as awal,
                   surveyd.R_PJL AS MASUK, 0 AS KELUAR, 0 AS LAIN, survey.flag, 12 AS URT
            FROM {$cbgCode}.survey, {$cbgCode}.surveyd
            WHERE survey.NO_BUKTI = surveyd.AG_PJL
            AND survey.posted = 1
            AND surveyd.KDLAKU IN ('0', '1')
            AND survey.flag = 'PS'
            AND survey.PER = :periode10
            AND survey.CBG = :cbg11
            AND surveyd.KD_BRG = :kdBrg11

        ) AS AA JOIN (SELECT @AKHIR:=0) AS BB ON 1=1
        ORDER BY KD_BRG, tgl, urt";

        try {
            return DB::select($query, [
                'bulan1' => $bulan,
                'year1' => $tahun,
                'kdBrg1' => $kdBrg,
                'cbg1' => $cbgCode,
                'cbg2' => $cbgCode,
                'periode1' => $periode,
                'kdBrg2' => $kdBrg,
                'periode2' => $periode,
                'kdBrg3' => $kdBrg,
                'cbg3' => $cbgCode,
                'periode3' => $periode,
                'kdBrg4' => $kdBrg,
                'cbg4' => $cbgCode,
                'cbg5' => $cbgCode,
                'periode4' => $periode,
                'kdBrg5' => $kdBrg,
                'periode5' => $periode,
                'kdBrg6' => $kdBrg,
                'cbg6' => $cbgCode,
                'cbg7' => $cbgCode,
                'periode6' => $periode,
                'kdBrg7' => $kdBrg,
                'cbg8' => $cbgCode,
                'periode7' => $periode,
                'kdBrg8' => $kdBrg,
                'periode8' => $periode,
                'kdBrg9' => $kdBrg,
                'cbg9' => $cbgCode,
                'periode9' => $periode,
                'cbg10' => $cbgCode,
                'kdBrg10' => $kdBrg,
                'periode10' => $periode,
                'cbg11' => $cbgCode,
                'kdBrg11' => $kdBrg,
            ]);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getKartuStockRetur($cbgCode, $periode, $kdBrg, $bulan, $tahun, $brgdtTable)
    {
        $query = "SELECT * , @AKHIR:=@AKHIR+AWAL+MASUK-KELUAR+LAIN AS SALDO FROM (
            SELECT 'Saldo Awal' as no_bukti, '' as tgl, kd_brg, NA_BRG,
                   IF(:bulan1=MONTH(NOW()), rAW00, rAW{$bulan}) as awal,
                   0 as masuk, 0 as keluar, 0 AS LAIN,
                   'AW' AS FLAG, 0 AS URT
            FROM {$brgdtTable}
            WHERE yer = :year1
            AND KD_BRG = :kdBrg1
            AND cbg = :cbg1

            UNION ALL

            -- Retur VR (Masuk ke Retur)
            SELECT retur.no_bukti, retur.TGL, returd.KD_BRG, returd.NA_BRG, 0 as awal,
                   returd.qty AS MASUK, 0 AS KELUAR, 0 AS LAIN, retur.FLAG, 1 AS URT
            FROM {$cbgCode}.retur, {$cbgCode}.returd
            WHERE retur.NO_BUKTI = returd.NO_BUKTI
            AND retur.PER = :periode1
            AND retur.flag = 'VR'
            AND returd.KD_BRG = :kdBrg2
            AND returd.kdlaku = '5'
            AND retur.POSTED = 1
            AND retur.cbg = :cbg2
            AND returd.qty <> 0

            UNION ALL

            -- Transfer Stock TS (Masuk ke Retur dari Toko)
            SELECT stockbz.no_bukti, stockbz.tgl_posted as TGL, stockbzd.KD_BRG, stockbzd.NA_BRG, 0 as awal,
                   stockbzd.qty AS MASUK, 0 AS KELUAR, 0 AS LAIN, stockbz.FLAG, 2 AS URT
            FROM {$cbgCode}.stockbz, {$cbgCode}.stockbzd
            WHERE stockbz.NO_BUKTI = stockbzd.NO_BUKTI
            AND stockbz.CBG = :cbg3
            AND stockbz.PER = :periode2
            AND stockbz.flag = 'TS'
            AND stockbzd.KD_BRG = :kdBrg3
            AND stockbzd.qty <> 0

            UNION ALL

            -- Retur GR (Keluar dari Retur ke Supplier)
            SELECT retur.no_bukti, retur.TGL, returd.KD_BRG, returd.NA_BRG, 0 as awal,
                   0 AS MASUK, returd.qty AS KELUAR, 0 AS LAIN, retur.FLAG, 3 AS URT
            FROM {$cbgCode}.retur, {$cbgCode}.returd
            WHERE retur.NO_BUKTI = returd.NO_BUKTI
            AND retur.PER = :periode3
            AND retur.flag = 'GR'
            AND returd.KD_BRG = :kdBrg4
            AND retur.cbg = :cbg4
            AND returd.qty <> 0
            AND retur.POSTED = 1

            UNION ALL

            -- Penyesuaian Retur (MT, AO, AK)
            SELECT stockbz.no_bukti, stockbz.TGL, stockbzd.KD_BRG, stockbzd.NA_BRG, 0 as awal,
                   0 AS MASUK, 0 AS KELUAR, stockbzd.qty AS LAIN, stockbz.FLAG, 4 AS URT
            FROM {$cbgCode}.stockbz, {$cbgCode}.stockbzd
            WHERE stockbz.NO_BUKTI = stockbzd.NO_BUKTI
            AND stockbz.PER = :periode4
            AND stockbz.flag IN ('MT', 'AO', 'AK')
            AND stockbzd.KD_BRG = :kdBrg5
            AND stockbz.cbg = :cbg5
            AND stockbzd.qty <> 0

        ) AS AA JOIN (SELECT @AKHIR:=0) AS BB ON 1=1
        ORDER BY KD_BRG, tgl, urt";

        try {
            return DB::select($query, [
                'bulan1' => $bulan,
                'year1' => $tahun,
                'kdBrg1' => $kdBrg,
                'cbg1' => $cbgCode,
                'periode1' => $periode,
                'kdBrg2' => $kdBrg,
                'cbg2' => $cbgCode,
                'cbg3' => $cbgCode,
                'periode2' => $periode,
                'kdBrg3' => $kdBrg,
                'periode3' => $periode,
                'kdBrg4' => $kdBrg,
                'cbg4' => $cbgCode,
                'periode4' => $periode,
                'kdBrg5' => $kdBrg,
                'cbg5' => $cbgCode,
            ]);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function jasperKartuStokReport(Request $request)
    {
        $file = 'rkartustok';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        $cbgCode = trim($request->cbg);
        $periode = trim($request->periode);
        $kdBrg = trim($request->kd_brg);
        $jenis = $request->jenis ?? 'toko';

        $bulan = substr($periode, 0, 2);
        $tahun = substr($periode, 3, 4);

        $brgdtTable = $cbgCode . '.brgdt';
        $brgdTable = $cbgCode . '.brgd';

        if ($tahun != date('Y')) {
            $brgdtTable .= $tahun;
            $brgdTable .= $tahun;
        }

        $data = [];

        if ($jenis === 'toko') {
            $results = $this->getKartuStockToko($cbgCode, $periode, $kdBrg, $bulan, $tahun, $brgdtTable, $brgdTable);
        } elseif ($jenis === 'gudang') {
            $results = $this->getKartuStockGudang($cbgCode, $periode, $kdBrg, $bulan, $tahun, $brgdTable);
        } else {
            $results = $this->getKartuStockRetur($cbgCode, $periode, $kdBrg, $bulan, $tahun, $brgdtTable);
        }

        foreach ($results as $row) {
            $data[] = [
                'NO_BUKTI' => $row->no_bukti ?? '',
                'TGL' => $row->tgl ?? '',
                'KD_BRG' => $row->kd_brg ?? '',
                'NA_BRG' => $row->NA_BRG ?? '',
                'AWAL' => $row->awal ?? 0,
                'MASUK' => $row->masuk ?? 0,
                'KELUAR' => $row->keluar ?? 0,
                'LAIN' => $row->LAIN ?? 0,
                'SALDO' => $row->SALDO ?? 0,
                'FLAG' => $row->FLAG ?? '',
            ];
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    // API untuk browse barang
    public function getBarangList(Request $request)
    {
        try {
            $cbgCode = trim($request->cbg);

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $currentYear = date('Y');
            $brgdtTable = $cbgCode . '.brgdt';

            // Check if current year table exists
            $yearCheck = DB::select("SELECT :year as XX, YEAR(CURDATE()) as YER", [
                'year' => $currentYear
            ]);

            $query = "SELECT DISTINCT A.KD_BRG as kd_brg, A.NA_BRG as na_brg, A.sub, B.AK00, B.gAK00
                     FROM {$cbgCode}.brg A
                     LEFT JOIN {$brgdtTable} B ON A.KD_BRG = B.KD_BRG AND B.cbg = :cbg
                     WHERE A.KD_BRG IS NOT NULL
                     AND A.NA_BRG IS NOT NULL
                     ORDER BY A.KD_BRG
                     LIMIT 500";

            $results = DB::select($query, ['cbg' => $cbgCode]);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
