<?php

namespace App\Http\Controllers\OReport;

use App\Http\Controllers\Controller;
use App\Models\Master\Cbg;
use App\Models\Master\Perid;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class RbrgdtgController extends Controller
{
    public function report()
    {
        $cbg = Cbg::groupBy('CBG')->get();
        session()->put('filter_cbg', '');
        session()->put('filter_tglDari', date("d-m-Y"));
        session()->put('filter_tglSampai', date("d-m-Y"));

        $per = Perid::query()->get();

        return view('oreport_brgdtg.report')->with([
            'cbg' => $cbg,
            'periode' => $per,
            'hasil' => [],
            'per' => $per,
            'no_form' => '',
            'na_toko' => '',
            'typ_pers' => '',
            'toko' => '',
            'selectedCbg' => '',
            'selectedPeriode' => '',
            'tglDr' => date('Y-m-d'),
            'tglSmp' => date('Y-m-d'),
            'sub' => '',
            'kdbrg' => '',
            'min' => '',
            'max' => '',
            'sub_item' => '',
            'time' => '07:00',
            'lexe' => 'ADMINISTRASI',
            'kdlaku' => '',
        ]);
    }

    public function jasperBrgdtgReport(Request $request)
    {
        // Check if this is a filter request (for DataTable)
        if ($request->has('filter') && !$request->has('cetak')) {
            $data = $this->getBrgdtgData($request);
            return view('oreport_brgdtg.report')->with($data);
        }

        $file = 'brgdtgreport';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        // Get data first
        $data = $this->getBrgdtgData($request);
        $hasil = $data['hasil'];
        $na_toko = $data['na_toko'];
        $no_form = $data['no_form'];
        $typ_pers = $data['typ_pers'];
        $lexe = $data['lexe'];
        $tgl = $data['tgl'];
        $pertgl = $data['pertgl'];

        // Handle report generation based on lexe (like Delphi logic)
        if (!empty($hasil)) {
            if ($lexe === 'GUDANG') {
                // Generate Gudang report
                $PHPJasperXML->arrayParameter = array(
                    "myTitle" => "Laporan Barang Datang Gudang",
                    "mySubTitle" => "Periode: " . $tgl,
                    "myHeader" => "Gudang: " . $na_toko
                );
            } elseif ($lexe === 'ADMINISTRASI') {
                // Generate Administrasi report
                $PHPJasperXML->arrayParameter = array(
                    "myTitle" => "Laporan Barang Datang Administrasi",
                    "mySubTitle" => "Periode: " . $pertgl,
                    "myHeader" => "Administrasi: " . $na_toko
                );
            }

            $PHPJasperXML->arrayParameter["results"] = $hasil;
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } else {
            return redirect()->back()->with('error', 'Tidak ada data untuk periode yang dipilih');
        }
    }

    private function getBrgdtgData(Request $request)
    {
        // Set session variables
        session()->put('filter_cbg', $request->cbcbg);

        // Determine table prefix based on CBG
        $cbgTable = '';
        if (!empty($request->cbcbg)) {
            $cbgTable = $request->cbcbg . '.';
        }

        // Step 1: Get store and form information (first Delphi query)
        $tokoQuery = "SELECT A.KODE, A.TYP, A.NA_TOKO, A.NA_PERS, A.TYP_PERS, A.TYP_NPWP, A.ALAMAT, B.NO_BUKTI
                     FROM {$cbgTable}toko A, {$cbgTable}tokoform B
                     WHERE A.TYP = B.TYP
                           AND A.KODE = ?
                           AND B.KD_PRNT = ?";

        $tokoData = DB::select($tokoQuery, [$request->cbcbg, 'BRGDTG_GD']);

        $no_form = '';
        $na_toko = '';
        $typ_pers = '';

        if (!empty($tokoData)) {
            $no_form = $tokoData[0]->NO_BUKTI ?? '';
            $typ_pers = $tokoData[0]->TYP_PERS ?? '';
            $na_toko = $tokoData[0]->NA_TOKO ?? '';
        }

        // Prepare parameters similar to Delphi
        $sub = $request->sub ?? '';
        $kdbrg = $request->kdbrg ?? '';
        $min = $request->min ?? '';
        $max = $request->max ?? '';
        $sub_item = $request->sub_item ?? '';
        $tgl = $request->tglDr ?? date('Y-m-d');
        $pertgl = date('m/Y', strtotime($tgl));
        $time = $request->time ?? '07:00:00';
        $lexe = strtoupper($request->lexe ?? 'ADMINISTRASI');

        // Build additional filters
        $filterkdlaku = '';
        if (!empty($request->kdlaku)) {
            $filterkdlaku = " AND brgdt.KDLAKU = '{$request->kdlaku}' ";
        }

        $filterSub = '';
        if (!empty($sub)) {
            $filterSub = " AND brg.SUB = '{$sub}' ";
        }

        $filterSubItem = '';
        if (!empty($sub_item)) {
            $filterSubItem = " AND brg.KDBAR LIKE '{$sub_item}%' ";
        }

        // Step 2: Main query (second Delphi query) - adjusted for DataTable columns
        $mainQuery = "SELECT DATE(beliz.TGL) as tanggal,
                            TIME(beliz.tgl_posted) as jam,
                            brg.SUB as sub,
                            brg.KDBAR as kode,
                            CONCAT(brg.NA_BRG, ' ', brg.KET_UK) as nama_barang,
                            brg.KET_KEM as kemasan,
                            CONCAT(brgdt.KDLAKU, brgdt.KLK) as kd,
                            brgdt.DTR as dtr,
                            brgdt.HJ as harga_pcs,
                            Belizd.qty as qty,
                            beliz.NO_PJK as no_faktur,
                            beliz.no_bukti as no_bukti,
                            CONCAT(beliz.KODES, '/ ', beliz.NO_PO) as keterangan,
                            beliz.usrnm as operator,
                            beliz.NO_PO as no_po
                     FROM {$cbgTable}beliz, {$cbgTable}Belizd, {$cbgTable}brg, {$cbgTable}brgdt
                     WHERE Belizd.KD_BRG = brg.KD_BRG
                           AND brg.KD_BRG = brgdt.KD_BRG
                           AND beliz.POSTED = 1
                           AND IF(? = 'ADMINISTRASI', beliz.PER = ?, DATE(beliz.tgl_posted) = ? AND TIME(beliz.tgl_posted) > ?)
                           AND (beliz.flag = 'BL' OR beliz.flag = 'BZ')
                           AND beliz.NO_BUKTI = Belizd.no_bukti
                           {$filterkdlaku}
                           {$filterSub}
                           {$filterSubItem}
                           AND IF(? = '', brg.sub >= ? AND brg.sub <= ?, belizd.KD_BRG = ?)
                     ORDER BY beliz.TGL DESC, belizd.KD_BRG ASC";

        $hasil = DB::select($mainQuery, [
            $lexe,         // :lexe
            $pertgl,       // :pertgl
            $tgl,          // :tgl
            $time,         // :time
            $kdbrg,        // for brg.sub check
            $min,          // :min for brg.sub
            $max,          // :max for brg.sub
            $kdbrg         // :kdbrg for exact match
        ]);

        // Return data for both filter and jasper use
        return [
            'hasil' => $hasil,
            'na_toko' => $na_toko,
            'no_form' => $no_form,
            'typ_pers' => $typ_pers,
            'tgl' => $tgl,
            'pertgl' => $pertgl,
            'lexe' => $lexe,
            'cbg' => Cbg::groupBy('CBG')->get(),
            'periode' => Perid::query()->get(),
            'per' => Perid::query()->get(),
            'toko' => $na_toko,
            'selectedCbg' => $request->cbcbg,
            'selectedPeriode' => $request->periode,
            'tglDr' => $request->tglDr,
            'tglSmp' => $request->tglSmp,
            'sub' => $sub,
            'kdbrg' => $kdbrg,
            'min' => $min,
            'max' => $max,
            'sub_item' => $sub_item,
            'time' => $time,
            'kdlaku' => $request->kdlaku ?? '',
        ];
    }
}
