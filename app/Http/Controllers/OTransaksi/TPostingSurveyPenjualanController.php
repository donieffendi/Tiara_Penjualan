<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TPostingSurveyPenjualanController extends Controller
{
    var $judul = 'Posting Survey Penjualan';
    var $FLAGZ = 'PS';

    public function index(Request $request)
    {
        try {
            if (!$request->session()->has('periode')) {
                return view("otransaksi_TPostingSurveyPenjualan.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TPostingSurveyPenjualan.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            return view("otransaksi_TPostingSurveyPenjualan.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPostingSurveyPenjualan index: ' . $e->getMessage());
            return view("otransaksi_TPostingSurveyPenjualan.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            $flagz = $request->input('flagz', 'PS');

            $query = DB::SELECT("
                SELECT
                    no_bukti,
                    tgl,
                    notes,
                    total_qty as qty,
                    total_qty as total,
                    posted as cek
                FROM survey
                WHERE per = ?
                AND posted = 0
                AND flag = ?
                ORDER BY no_bukti
            ", [$periode, $flagz]);

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('tgl', function ($row) {
                    return date('d-m-Y', strtotime($row->tgl));
                })
                ->editColumn('qty', function ($row) {
                    return number_format($row->qty, 2, ',', '.');
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->total, 2, ',', '.');
                })
                ->addColumn('cek_checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check-input cek-item" value="' . $row->no_bukti . '" data-cek="' . $row->cek . '">';
                })
                ->rawColumns(['cek_checkbox'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function detail(Request $request, $no_bukti)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $noBuktiList = explode(',', $no_bukti);
            $flagz = $request->input('flagz', 'PS');

            if (empty($noBuktiList)) {
                return response()->json(['error' => 'Tidak ada data yang dipilih untuk diposting'], 400);
            }

            DB::beginTransaction();

            $successCount = 0;
            $errors = [];

            foreach ($noBuktiList as $bukti) {
                try {
                    $this->processPosting(trim($bukti), $flagz, $CBG);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error pada bukti {$bukti}: " . $e->getMessage();
                    Log::error("Error posting {$bukti}: " . $e->getMessage());
                }
            }

            DB::commit();

            if ($successCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Posting berhasil untuk {$successCount} dokumen",
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'error' => 'Semua posting gagal',
                    'details' => $errors
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in detail (posting): ' . $e->getMessage());
            return response()->json([
                'error' => 'Posting gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processPosting($noBukti, $flagz, $CBG)
    {
        try {
            // Ambil detail transaksi dari surveyd berdasarkan flagz
            if ($flagz == 'BS') {
                // Survey Pembelian
                $details = DB::SELECT("
                    SELECT
                        surveyd.NO_ID,
                        surveyd.R_PBL,
                        surveyd.HB_PBL as HB,
                        surveyd.KD_BRG,
                        brgdt.KDLAKU
                    FROM surveyd
                    INNER JOIN brgdt ON brgdt.KD_BRG = surveyd.KD_BRG
                    WHERE surveyd.AG_PBL = ?
                    AND brgdt.CBG = ?
                ", [$noBukti, $CBG]);

                $qtyField = 'R_PBL';
            } else {
                // Survey Penjualan (PS)
                $details = DB::SELECT("
                    SELECT
                        surveyd.NO_ID,
                        surveyd.R_PJL as R_PBL,
                        surveyd.HB_PJL as HB,
                        surveyd.KD_BRG,
                        brgdt.KDLAKU
                    FROM surveyd
                    INNER JOIN brgdt ON brgdt.KD_BRG = surveyd.KD_BRG
                    WHERE surveyd.AG_PJL = ?
                    AND brgdt.CBG = ?
                ", [$noBukti, $CBG]);

                $qtyField = 'R_PBL';
            }

            // Process setiap detail - update stok
            foreach ($details as $detail) {
                $kdBrg = $detail->KD_BRG;
                $qty = $detail->R_PBL;
                $hb = $detail->HB;
                $kdLaku = $detail->KDLAKU;

                // Cek apakah barang untuk gudang (kdlaku = 0 atau 1) atau toko
                if ($kdLaku == '0' || $kdLaku == '1') {
                    // Update brgd (Gudang)
                    DB::statement("
                        UPDATE brgd
                        SET MA00 = MA00 + ?,
                            ak00 = aw00 + ma00 - ke00 + ln00
                        WHERE kd_brg = ?
                        AND cbg = ?
                    ", [$qty, $kdBrg, $CBG]);

                    // Update brgdt (Detail Gudang)
                    DB::statement("
                        UPDATE brgdt
                        SET gMA00 = gMA00 + ?,
                            HBX = ROUND(?),
                            HBX2 = ROUND(HBX),
                            gak00 = gaw00 + gma00 - gke00 + gln00
                        WHERE kd_brg = ?
                        AND cbg = ?
                    ", [$qty, $hb, $kdBrg, $CBG]);
                } else {
                    // Update brgdt (Toko)
                    DB::statement("
                        UPDATE brgdt
                        SET HBX = ROUND(?),
                            HBX2 = ROUND(HBX),
                            MA00 = MA00 + ?,
                            ak00 = aw00 + ma00 - ke00 + ln00
                        WHERE kd_brg = ?
                        AND cbg = ?
                    ", [$hb, $qty, $kdBrg, $CBG]);
                }
            }

            // Update status posted di survey
            DB::statement("
                UPDATE survey
                SET posted = 1,
                    tgl_posted = NOW()
                WHERE no_bukti = ?
            ", [$noBukti]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error in processPosting for {$noBukti}: " . $e->getMessage());
            throw $e;
        }
    }
}
