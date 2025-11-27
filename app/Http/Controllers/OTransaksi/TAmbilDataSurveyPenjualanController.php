<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TAmbilDataSurveyPenjualanController extends Controller
{
    var $judul = 'Ambil Data Survey Penjualan';
    var $FLAGZ = 'SURVEY';

    public function index(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TAmbilDataSurveyPenjualan.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            // Get today's date
            $today = date('Y-m-d');

            // Get the last survey number for today
            $day_today = date('d');
            $no_survey = $this->getNoSurvey($today, $CBG);

            return view("otransaksi_TAmbilDataSurveyPenjualan.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'cbg' => $CBG,
                'today' => $today,
                'no_survey' => $no_survey
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TAmbilDataSurveyPenjualan index: ' . $e->getMessage());
            return view("otransaksi_TAmbilDataSurveyPenjualan.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    private function getNoSurvey($tanggal, $cbg)
    {
        try {
            // Check if the date is today
            $result = DB::select("
                SELECT IF(RIGHT(DATE(NOW()), 2) = RIGHT(?, 2), 'YES', 'NO') AS day
            ", [$tanggal]);

            $is_today = $result[0]->day ?? 'NO';

            if ($is_today === 'YES') {
                // Get the latest survey number for today
                $survey = DB::select("
                    SELECT DISTINCT NO_BUKTI
                    FROM surveyd
                    WHERE RIGHT(NO_BUKTI, 2) = DAY(?)
                    ORDER BY NO_BUKTI DESC
                    LIMIT 1
                ", [$tanggal]);

                return $survey[0]->NO_BUKTI ?? '';
            } else {
                // Get survey agenda for specific date
                $survey = DB::select("
                    SELECT DISTINCT NO_AGENDA
                    FROM survey
                    WHERE TGL = ?
                ", [$tanggal]);

                return $survey[0]->NO_AGENDA ?? '';
            }
        } catch (\Exception $e) {
            Log::error('Error in getNoSurvey: ' . $e->getMessage());
            return '';
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $tanggal = $request->input('tanggal', '');
            $no_survey = trim($request->input('no_survey', ''));

            // Validasi input
            if (empty($tanggal)) {
                return response()->json(['error' => 'Tanggal survey harus diisi'], 400);
            }

            if (empty($no_survey)) {
                return response()->json(['error' => 'No survey harus diisi'], 400);
            }

            // Query sesuai dengan logika Delphi (Button1Click)
            // Mengambil data dari surveyd dengan join ke brgdt dan brg
            $data_survey = DB::select("
                SELECT
                    surveyd.KD_BRG,
                    surveyd.no_id,
                    CONCAT(surveyd.NA_BRG, ' ', brg.ket_uk) AS NA_BRG,
                    surveyd.IMPORT,
                    surveyd.KET_KEM,
                    surveyd.R_PBL AS jml_ord,
                    surveyd.HJ_MAX,
                    surveyd.profit AS sale,
                    surveyd.barcode,
                    surveyd.HJ,
                    brgdt.kdlaku,
                    brgdt.lph,
                    brgdt.hb
                FROM surveyd
                INNER JOIN brgdt ON surveyd.kd_brg = brgdt.kd_brg
                INNER JOIN brg ON surveyd.kd_brg = brg.kd_brg
                WHERE surveyd.NO_bukti = ?
                AND brgdt.cbg = ?
                AND surveyd.SPTKK1 = 'Y'
                ORDER BY surveyd.kd_brg
            ", [$no_survey, $CBG]);

            if (empty($data_survey)) {
                return response()->json(['error' => 'Data survey tidak ditemukan'], 404);
            }

            // Format data untuk response
            $formatted_data = [];
            $no = 1;
            $total_qty = 0;
            $total_amount = 0;

            foreach ($data_survey as $row) {
                $total = $row->jml_ord * $row->hb;
                $total_qty += $row->jml_ord;
                $total_amount += $total;

                $formatted_data[] = [
                    'rec' => $no++,
                    'kd_brg' => $row->KD_BRG,
                    'na_brg' => $row->NA_BRG,
                    'no_id' => $row->no_id,
                    'kdlaku' => $row->kdlaku,
                    'lph' => $row->lph,
                    'sale' => $row->sale,
                    'barcode' => $row->barcode,
                    'ket_kem' => $row->KET_KEM,
                    'hb' => $row->hb,
                    'hj' => $row->HJ,
                    'import' => $row->IMPORT,
                    'hj_max' => $row->HJ_MAX,
                    'jml_ord' => $row->jml_ord,
                    'total' => $total
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Data survey berhasil dimuat',
                'data' => $formatted_data,
                'summary' => [
                    'total_qty' => $total_qty,
                    'total_amount' => $total_amount,
                    'jumlah_item' => count($formatted_data)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request, $id)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Ambil detail item survey berdasarkan no_id
            $detail = DB::select("
                SELECT
                    surveyd.KD_BRG,
                    surveyd.no_id,
                    CONCAT(surveyd.NA_BRG, ' ', brg.ket_uk) AS NA_BRG,
                    surveyd.IMPORT,
                    surveyd.KET_KEM,
                    surveyd.R_PBL AS jml_ord,
                    surveyd.HJ_MAX,
                    surveyd.profit AS sale,
                    surveyd.barcode,
                    surveyd.HJ,
                    brgdt.kdlaku,
                    brgdt.lph,
                    brgdt.hb
                FROM surveyd
                INNER JOIN brgdt ON surveyd.kd_brg = brgdt.kd_brg
                INNER JOIN brg ON surveyd.kd_brg = brg.kd_brg
                WHERE surveyd.no_id = ?
                AND brgdt.cbg = ?
                LIMIT 1
            ", [$id, $CBG]);

            if (empty($detail)) {
                return response()->json(['error' => 'Detail tidak ditemukan'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $detail[0]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
