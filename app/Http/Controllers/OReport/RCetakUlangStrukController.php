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

class RCetakUlangStrukController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Initialize session variables sesuai dengan logika Delphi
        session()->put('filter_cbg', '');
        session()->put('filter_per', date("m-Y"));
        session()->put('filter_mm', ''); // Untuk menyimpan bulan dari periode

        return view('oreport_cetak_ulang_struk.report')->with([
            'cbg' => $cbg,
            'hasilTransaksi' => [],
            'per' => $per,
        ]);
    }

    public function getCetakUlangStrukReport(Request $request)
    {
        $cbg = Cbg::groupBy('CBG')->get();
        $per = Perid::query()->get();

        // Get filter values
        $cbgCode = $request->cbg;
        $periode = $request->periode; // Format MM-YYYY
        $tanggal = $request->tanggal;
        $noBukti = $request->no_bukti;
        $namaKasir = $request->nama_kasir;
        $kodKasir = $request->kod_kasir;

        // Set MM variable seperti di Delphi
        $MM = '';
        if (!empty($periode)) {
            $MM = substr($periode, 0, 2);
        }

        // Store in session
        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_per', $periode);
        session()->put('filter_mm', $MM);

        $hasilTransaksi = [];

        if (!empty($cbgCode)) {
            $hasilTransaksi = $this->getTransaksiData($cbgCode, $MM, $tanggal, $noBukti, $namaKasir, $kodKasir);
        }

        return view('oreport_cetak_ulang_struk.report')->with([
            'cbg' => $cbg,
            'hasilTransaksi' => $hasilTransaksi,
            'per' => $per,
        ]);
    }

    private function getTransaksiData($cbgCode, $MM, $tanggal = null, $noBukti = null, $namaKasir = null, $kodKasir = null)
    {
        // Build filter condition
        $whereConditions = [];
        $bindings = ['cbg' => $cbgCode];

        // Base condition
        $whereConditions[] = "A.cbg = :cbg AND A.flag = 'JL'";

        // Periode condition
        if (!empty($MM)) {
            $whereConditions[] = "A.per = :per";
            $bindings['per'] = session('filter_per');
        }

        // Tanggal filter
        if (!empty($tanggal)) {
            $whereConditions[] = "A.TGL = :tgl";
            $bindings['tgl'] = $tanggal;
        }

        // No Bukti filter
        if (!empty($noBukti)) {
            $whereConditions[] = "A.no_bukti LIKE :no_bukti";
            $bindings['no_bukti'] = '%' . $noBukti . '%';
        }

        // Nama Kasir filter
        if (!empty($namaKasir)) {
            $whereConditions[] = "A.usrnm LIKE :usrnm";
            $bindings['usrnm'] = '%' . $namaKasir . '%';
        }

        // Kod Kasir filter
        if (!empty($kodKasir)) {
            $whereConditions[] = "A.KSR LIKE :ksr";
            $bindings['ksr'] = '%' . $kodKasir . '%';
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Query sesuai dengan logika Delphi
        $tableName = 'jual' . $MM;
        if (empty($MM)) {
            $tableName = 'jual'; // Untuk data hari ini
        }

        $query = "SELECT
            TIME(A.tg_smp) as Waktu,
            A.TGL,
            A.namaC,
            A.SHIFT,
            A.USRNM,
            A.no_bukti,
            A.total as totals,
            :tgl_param as tglx
            FROM {$tableName} A
            WHERE {$whereClause}
            ORDER BY A.tgl, A.SHIFT, A.usrnm, A.no_bukti";

        $bindings['tgl_param'] = $tanggal ?? date('Y-m-d');

        return DB::select($query, $bindings);
    }

    // Method untuk mendapatkan detail transaksi untuk cetak (sesuai Print1Click di Delphi)
    public function getDetailTransaksi($noBukti, $MM = '')
    {
        $details = [];

        // Header dan Detail Barang (com1 query pertama di Delphi)
        $headerQuery = "SELECT jual{$MM}.USRNM, jual{$MM}.no_bukti
                       FROM jual{$MM}
                       WHERE jual{$MM}.NO_BUKTI = :bukti";

        $details['header'] = DB::select($headerQuery, ['bukti' => $noBukti]);

        // Detail Barang (com1 query kedua di Delphi)
        $barangQuery = "SELECT
            jual{$MM}.USRNM,
            jual{$MM}.no_bukti,
            juald{$MM}.NA_BRG,
            juald{$MM}.KD_BRG,
            juald{$MM}.harga,
            juald{$MM}.qty,
            juald{$MM}.diskon,
            juald{$MM}.total,
            jual{$MM}.total as totals,
            jual{$MM}.bulat,
            jual{$MM}.totala,
            jual{$MM}.bayar,
            jual{$MM}.kembali,
            jual{$MM}.dpp,
            jual{$MM}.ppn,
            juald{$MM}.rec
            FROM jual{$MM}, juald{$MM}
            WHERE jual{$MM}.no_bukti = juald{$MM}.no_bukti
            AND jual{$MM}.NO_BUKTI = :bukti1
            ORDER BY rec";

        $details['barang'] = DB::select($barangQuery, ['bukti1' => $noBukti]);

        // Cek pembayaran non-cash (doot query di Delphi)
        $nonCashQuery = "SELECT jual{$MM}.no_bukti, jualby{$MM}.TYPE
                        FROM jual{$MM}, jualby{$MM}
                        WHERE jual{$MM}.no_bukti = jualby{$MM}.NO_BUKTI
                        AND jual{$MM}.no_bukti = :bukti
                        AND jualby{$MM}.type <> 'CASH'";

        $nonCashResult = DB::select($nonCashQuery, ['bukti' => $noBukti]);

        if (count($nonCashResult) > 0) {
            // Ada pembayaran non-cash (com2 query pertama di Delphi)
            $bayarQuery = "SELECT
                jual{$MM}.USRNM,
                jual{$MM}.no_bukti,
                jual{$MM}.tkp,
                jualby{$MM}.JUMLAH + jualby{$MM}.badm as jum,
                jualby{$MM}.TYPE,
                jualby{$MM}.badm,
                UI.adm,
                jual{$MM}.total as totals,
                jual{$MM}.bulat,
                jual{$MM}.totala,
                jual{$MM}.bayar,
                jual{$MM}.kembali,
                jual{$MM}.dpp,
                jual{$MM}.ppn
                FROM jual{$MM}, jualby{$MM},
                (SELECT SUM(jualby{$MM}.badm) as adm
                 FROM jualby{$MM}
                 WHERE no_bukti = :bukti) as UI
                WHERE jual{$MM}.no_bukti = jualby{$MM}.NO_BUKTI
                AND jual{$MM}.NO_BUKTI = :bukti";

            $details['bayar'] = DB::select($bayarQuery, ['bukti' => $noBukti]);
            $details['payment_type'] = 'non_cash';
        } else {
            // Hanya cash (com2 query kedua di Delphi)
            $cashQuery = "SELECT
                jual{$MM}.USRNM,
                jual{$MM}.no_bukti,
                jual{$MM}.total as totals,
                jual{$MM}.bulat,
                jual{$MM}.totala,
                jual{$MM}.bayar,
                jual{$MM}.kembali,
                jual{$MM}.dpp,
                jual{$MM}.ppn,
                jual{$MM}.tkp
                FROM jual{$MM}
                WHERE jual{$MM}.NO_BUKTI = :bukti";

            $details['bayar'] = DB::select($cashQuery, ['bukti' => $noBukti]);
            $details['payment_type'] = 'cash';
        }

        // Stiker (doot query kedua dan com1 query terakhir di Delphi)
        $stikerQuery = "SELECT stiker FROM jual{$MM} WHERE no_bukti = :bukti";
        $stikerResult = DB::select($stikerQuery, ['bukti' => $noBukti]);

        if (count($stikerResult) > 0) {
            $poin = $stikerResult[0]->stiker ?? '';

            $greetQuery = "SELECT
                kata,
                DATE_SUB(awbln, INTERVAL selisi MONTH) as perm,
                DATE_ADD(akbln, INTERVAL 5-selisi MONTH) as pera,
                :POIN AS POIN,
                :POINAW AS POINAW,
                :PERSTIKER AS PERSTIKER,
                :namac as namac,
                :kodec as kodec,
                TIME(NOW()) as waktu,
                :noks as noks
                FROM greet,
                (SELECT
                    DATE(NOW()) as tgl,
                    DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 1 month)), INTERVAL 1 day) as awbln,
                    LAST_DAY(NOW()) as akbln,
                    MOD(MONTH(NOW()), 6) as selisi
                ) as xx
                ORDER BY BARIS";

            $details['greet'] = DB::select($greetQuery, [
                'POIN' => $poin,
                'POINAW' => '',
                'PERSTIKER' => '',
                'namac' => '',
                'kodec' => '',
                'noks' => ''
            ]);
        }

        return $details;
    }

    // Method untuk mendapatkan data thermal print (sesuai dengan btnPrintClick di Delphi)
    public function getThermalPrintData($noBukti, $MM, $cbgCode)
    {
        $thermalData = [];

        try {
            // Cek subtotal
            $subQuery = "SELECT MAX(SUBTOTAL) as SUBX FROM juald{$MM} WHERE NO_BUKTI = :bukti";
            $subResult = DB::select($subQuery, ['bukti' => $noBukti]);
            $sub = $subResult[0]->SUBX ?? 0;

            // Header dan Detail Barang (ter1 query di Delphi)
            $mainQuery = "SELECT NAMA_TOKO, ALAMAT INTO @NATOKO, @ALMT FROM TOKO WHERE KODE = :cbg;
                SELECT *, @NATOKO NAMA_TOKO, @ALMT ALAMAT FROM
                (SELECT MIN(juald{$MM}.NO_ID) as ID,
                jual{$MM}.USRNM, jual{$MM}.no_bukti, jual{$MM}.total as totals,
                jual{$MM}.bulat, jual{$MM}.totala, jual{$MM}.bayar,
                jual{$MM}.kembali, jual{$MM}.dpp, jual{$MM}.ppn,
                juald{$MM}.KD_BRG, juald{$MM}.NA_BRG, juald{$MM}.harga,
                SUM(juald{$MM}.qty) AS qty, SUM(juald{$MM}.diskon) AS diskon,
                SUM(juald{$MM}.total) as total, juald{$MM}.subtotal,
                COUNT(juald{$MM}.subtotal) as jumsub,
                jual{$MM}.namac, jual{$MM}.kodec, jual{$MM}.ksr as noks,
                jual{$MM}.tg_smp as waktu, jual{$MM}.stiker as poin
                FROM JUAL{$MM}, juald{$MM}
                WHERE jual{$MM}.no_bukti = juald{$MM}.no_bukti
                AND jual{$MM}.NO_BUKTI = :bukti
                GROUP BY juald{$MM}.subtotal ASC, juald{$MM}.KD_BRG ASC) as nda
                WHERE total <> 0
                ORDER BY ID ASC";

            $thermalData['main'] = DB::select($mainQuery, ['bukti' => $noBukti, 'cbg' => $cbgCode]);

            if (empty($thermalData['main'])) {
                throw new \Exception('Data tidak ditemukan...');
            }

            $kodec = $thermalData['main'][0]->kodec ?? '';

            // Bayar (ter2 query di Delphi)
            $bayarQuery = "SELECT
                jual{$MM}.no_bukti,
                jual{$MM}.tkp, jual{$MM}.total as totals,
                jual{$MM}.bulat, jual{$MM}.totala,
                jual{$MM}.bayar, jual{$MM}.kembali,
                jual{$MM}.dpp, jual{$MM}.ppn,
                jual{$MM}.disc,
                jualby{$MM}.JUMLAH + jualby{$MM}.badm as jum,
                jualby{$MM}.TYPE, jualby{$MM}.badm, UI.adm
                FROM JUAL{$MM}, jualby{$MM},
                (SELECT SUM(jualby{$MM}.badm) as adm
                 FROM jualby{$MM} WHERE no_bukti = :bukti) as UI
                WHERE jual{$MM}.no_bukti = jualby{$MM}.NO_BUKTI
                AND jual{$MM}.NO_BUKTI = :bukti";

            $thermalData['bayar'] = DB::select($bayarQuery, ['bukti' => $noBukti]);

            if (empty($thermalData['bayar'])) {
                throw new \Exception('ter2 error');
            }

            // Poin Stiker (ter3 query di Delphi)
            $greetQuery = "SELECT kata, :noks as noks, :waktu as waktu,
                :poin as poin, :kodec as kodec, :namac as namac
                FROM greet
                WHERE greet.KATA <> ''
                ORDER BY BARIS";

            $mainData = $thermalData['main'][0];
            $thermalData['greet'] = DB::select($greetQuery, [
                'noks' => $mainData->noks ?? '',
                'waktu' => $mainData->waktu ?? '',
                'poin' => $mainData->poin ?? '',
                'kodec' => $mainData->kodec ?? '',
                'namac' => $mainData->namac ?? ''
            ]);

            if (empty($thermalData['greet'])) {
                throw new \Exception('ter3 error');
            }

            $thermalData['subtotal'] = $sub;
            $thermalData['kodec'] = $kodec;
        } catch (\Exception $e) {
            throw $e;
        }

        return $thermalData;
    }

    public function jasperCetakUlangStrukReport(Request $request)
    {
        $file = 'rcetak_ulang_struk';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Store filter values in session
        $cbgCode = $request->cbg;
        $periode = $request->periode;
        $noBukti = $request->no_bukti;

        $MM = '';
        if (!empty($periode)) {
            $MM = substr($periode, 0, 2);
        }

        session()->put('filter_cbg', $cbgCode);
        session()->put('filter_per', $periode);
        session()->put('filter_mm', $MM);

        // Get data based on filters
        $data = [];
        if (!empty($cbgCode)) {
            $results = $this->getTransaksiData($cbgCode, $MM, $request->tanggal, $noBukti, $request->nama_kasir, $request->kod_kasir);

            foreach ($results as $row) {
                $data[] = [
                    'WAKTU' => $row->Waktu ?? '',
                    'TGL' => $row->TGL ?? '',
                    'NAMA_C' => $row->namaC ?? '',
                    'SHIFT' => $row->SHIFT ?? '',
                    'USRNM' => $row->USRNM ?? '',
                    'NO_BUKTI' => $row->no_bukti ?? '',
                    'TOTALS' => $row->totals ?? 0,
                    'TGLX' => $row->tglx ?? '',
                ];
            }
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    // API endpoints untuk mendukung AJAX calls dari frontend
    public function apiGetTransaksi(Request $request)
    {
        try {
            $cbgCode = $request->cbg;
            $MM = $request->mm ?? '';
            $tanggal = $request->tanggal;
            $noBukti = $request->no_bukti;
            $namaKasir = $request->nama_kasir;
            $kodKasir = $request->kod_kasir;

            if (empty($cbgCode)) {
                return response()->json(['error' => 'Cabang harus dipilih'], 400);
            }

            $hasil = $this->getTransaksiData($cbgCode, $MM, $tanggal, $noBukti, $namaKasir, $kodKasir);

            return response()->json([
                'success' => true,
                'data' => $hasil
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function apiGetDetailTransaksi(Request $request)
    {
        try {
            $noBukti = $request->no_bukti;
            $MM = $request->mm ?? '';

            if (empty($noBukti)) {
                return response()->json(['error' => 'No Bukti harus diisi'], 400);
            }

            $detail = $this->getDetailTransaksi($noBukti, $MM);

            return response()->json([
                'success' => true,
                'data' => $detail
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function apiGetThermalPrint(Request $request)
    {
        try {
            $noBukti = $request->no_bukti;
            $MM = $request->mm ?? '';
            $cbgCode = $request->cbg;

            if (empty($noBukti) || empty($cbgCode)) {
                return response()->json(['error' => 'No Bukti dan Cabang harus diisi'], 400);
            }

            $thermalData = $this->getThermalPrintData($noBukti, $MM, $cbgCode);

            return response()->json([
                'success' => true,
                'data' => $thermalData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
