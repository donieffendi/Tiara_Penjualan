<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PHRincianPenagihanController extends Controller
{
    public function index()
    {
        try {
            $cbgMst = DB::selectOne("SELECT KODE FROM toko WHERE STA='MA'");
            $cbgMst = $cbgMst ? $cbgMst->KODE : '';
        } catch (\Illuminate\Database\QueryException $e) {
            $cbgMst = '';
        }

        try {
            $taunIni = DB::selectOne("SELECT YEAR(NOW()) as taunini");
            $taunIni = $taunIni ? $taunIni->taunini : date('Y');
        } catch (\Illuminate\Database\QueryException $e) {
            $taunIni = date('Y');
        }

        $periodeList = [];
        for ($j = 2024; $j <= $taunIni; $j++) {
            for ($i = 1; $i <= 12; $i++) {
                $periodeList[] = sprintf('%02d', $i) . '/' . $j;
            }
        }

        $currentPeriode = sprintf('%02d', date('m')) . '/' . date('Y');

        return view('promo_hadiah_rincian_penagihan.index', [
            'title' => 'Rincian Penagihan Sewa',
            'cbgMst' => $cbgMst,
            'periodeList' => $periodeList,
            'currentPeriode' => $currentPeriode
        ]);
    }

    public function getData(Request $request)
    {
        try {
            $periode = $request->get('periode');
            $cbg = $request->get('cbg', '');
            $statusPajak = $request->get('status_pajak', '');
            $tanggungPPN = $request->get('tanggung_ppn', 0);

            if (empty($periode)) {
                return response()->json(['success' => false, 'message' => 'Cek Periode!']);
            }

            if (empty($statusPajak)) {
                return response()->json(['success' => false, 'message' => 'Pilih Status Pajak!']);
            }

            $cbgMst = DB::selectOne("SELECT KODE FROM toko WHERE STA='MA'");
            $cbgMst = $cbgMst ? $cbgMst->KODE : '';

            $query = "CALL " . $cbgMst . ".pmsr_report_sewa(?, ?, '', ?, ?, ?)";
            $data = DB::select($query, ['RINCIAN_TAGIH', $cbg, $periode, $statusPajak, $tanggungPPN]);

            if (empty($data)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data..']);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $periode = $request->get('periode');
            $cbg = $request->get('cbg', '');
            $statusPajak = $request->get('status_pajak', '');
            $tanggungPPN = $request->get('tanggung_ppn', 0);

            if (empty($periode)) {
                return response()->json(['success' => false, 'message' => 'Cek Periode!']);
            }

            if (empty($statusPajak)) {
                return response()->json(['success' => false, 'message' => 'Pilih Status Pajak!']);
            }

            $cbgMst = DB::selectOne("SELECT KODE FROM toko WHERE STA='MA'");
            $cbgMst = $cbgMst ? $cbgMst->KODE : '';

            $query = "CALL " . $cbgMst . ".pmsr_report_sewa(?, ?, '', ?, ?, ?)";
            $data = DB::select($query, ['RINCIAN_TAGIH', $cbg, $periode, $statusPajak, $tanggungPPN]);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function cetakUlang(Request $request)
    {
        try {
            $noTagih = $request->get('no_tagih', '');
            $cbg = $request->get('cbg', '');

            if (empty($noTagih)) {
                return response()->json(['success' => false, 'message' => 'Nomor tagihan tidak boleh kosong!']);
            }

            $cbgMst = DB::selectOne("SELECT KODE FROM toko WHERE STA='MA'");
            $cbgMst = $cbgMst ? $cbgMst->KODE : '';

            $query = "CALL " . $cbgMst . ".pmsr_report_sewa(?, ?, ?, '', '', 0)";
            $data = DB::select($query, ['RINCIAN_TAGIH', $cbg, $noTagih]);

            if (empty($data)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data..']);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
