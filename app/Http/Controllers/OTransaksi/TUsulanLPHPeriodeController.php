<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TUsulanLPHPeriodeController extends Controller
{
    /**
     * Halaman Index - List Usulan LPH Periode
     */
    public function index(Request $request)
    {
        try {
            $judul = 'Usulan LPH Periode';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TUsulanLPHPeriode.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TUsulanLPHPeriode.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TUsulanLPHPeriode.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TUsulanLPHPeriode index: ' . $e->getMessage());
            return view("otransaksi_TUsulanLPHPeriode.index")->with([
                'judul' => 'Usulan LPH Periode',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ambil data list untuk datatables di index
     */
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::error('TUsulanLPHPeriode cari_data: User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                Log::error('TUsulanLPHPeriode cari_data: Periode belum diset');
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            // Convert periode to string if array
            if (is_array($periode)) {
                $periode = ($periode['bulan'] ?? '01') . ($periode['tahun'] ?? date('Y'));
            }

            Log::info('TUsulanLPHPeriode cari_data: CBG=' . $CBG . ', Periode=' . $periode);

            // Check if table oleolang exists
            try {
                $tableCheck = DB::select("SHOW TABLES LIKE 'oleolang'");
                if (empty($tableCheck)) {
                    Log::warning('TUsulanLPHPeriode cari_data: Tabel oleolang tidak ditemukan');
                    return Datatables::of(collect([]))->make(true);
                }
            } catch (\Exception $e) {
                Log::error('TUsulanLPHPeriode cari_data table check error: ' . $e->getMessage());
                return Datatables::of(collect([]))->make(true);
            }

            // Query sesuai Delphi
            $query = "
                SELECT sub, per, cbg, ket, COUNT(sub) as jml, 
                       DATE_FORMAT(MAX(tg_smp), '%d/%m/%Y %H:%i:%s') as tg_smp, 
                       MAX(post) as post 
                FROM oleolang 
                WHERE per = ?
                GROUP BY SUB, ket 
                UNION ALL 
                SELECT sub, per, cbg, ket, COUNT(sub) as jml, 
                       DATE_FORMAT(MAX(tg_smp), '%d/%m/%Y %H:%i:%s') as tg_smp, 
                       MAX(post) as post 
                FROM oleolang_his 
                WHERE per = ?
                GROUP BY SUB, ket 
                ORDER BY SUB, ket
            ";

            $data = DB::select($query, [$periode, $periode]);

            Log::info('TUsulanLPHPeriode cari_data: Found ' . count($data) . ' records');

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->addColumn('posted_label', function ($row) {
                    if ($row->post == 1) {
                        return '<span class="badge badge-success">Posted</span>';
                    } else {
                        return '<span class="badge badge-warning">Open</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $editBtn = '<button class="btn btn-sm btn-primary btn-edit" 
                                data-sub="' . $row->sub . '" 
                                data-per="' . $row->per . '" 
                                data-cbg="' . $row->cbg . '" 
                                data-ket="' . $row->ket . '">
                                <i class="fas fa-edit"></i> Edit</button> ';

                    $printBtn = '<button class="btn btn-sm btn-info btn-print" 
                                data-sub="' . $row->sub . '" 
                                data-per="' . $row->per . '" 
                                data-ket="' . $row->ket . '">
                                <i class="fas fa-print"></i> Print</button>';

                    return $editBtn . $printBtn;
                })
                ->rawColumns(['action', 'posted_label'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Edit - Form Entry Usulan LPH Periode
     */
    public function edit(Request $request, $sub = null)
    {
        try {
            $judul = 'Edit Usulan LPH Periode';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return redirect()->route('usulanlphperiode')->with('error', 'User tidak memiliki akses cabang');
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return redirect()->route('usulanlphperiode')->with('warning', 'Periode belum diset');
            }

            // Convert periode to string if array
            if (is_array($periode)) {
                $periode = ($periode['bulan'] ?? '01') . ($periode['tahun'] ?? date('Y'));
            }

            // Get parameters from query string
            $per = $request->get('per', $periode);
            $ket = $request->get('ket', '');
            $outlet = $request->get('cbg', $CBG);

            return view("otransaksi_TUsulanLPHPeriode.edit")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $per,
                'sub' => $sub,
                'ket' => $ket,
                'outlet' => $outlet
            ]);
        } catch (\Exception $e) {
            Log::error('Error in edit: ' . $e->getMessage());
            return redirect()->route('usulanlphperiode')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get detail items for specific sub/periode/ket
     */
    public function detail(Request $request, $sub)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $per = $request->input('per');
            $ket = $request->input('ket');
            $outlet = $request->input('cbg', $CBG);

            $outletConn = strtolower($outlet);

            Log::info('TUsulanLPHPeriode detail: Sub=' . $sub . ', Per=' . $per . ', Ket=' . $ket . ', Outlet=' . $outlet);

            $query = "
                SELECT a.*, b.ak00 + b.gak00 as stock 
                FROM oleolang a
                INNER JOIN brgdt b ON a.kd_brg = b.kd_brg
                WHERE a.per = ? AND a.cbg = ? AND a.sub = ? AND a.ket = ?
                ORDER BY a.kd_brg
            ";

            $data = DB::connection($outletConn)->select($query, [$per, $outlet, $sub, $ket]);

            Log::info('TUsulanLPHPeriode detail: Found ' . count($data) . ' records');

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('lph', function ($row) {
                    return number_format($row->lph, 2);
                })
                ->editColumn('lphlm', function ($row) {
                    return number_format($row->lphlm ?? 0, 2);
                })
                ->editColumn('lph_saran', function ($row) {
                    return number_format($row->lph_saran ?? 0, 2);
                })
                ->editColumn('dtr', function ($row) {
                    return number_format($row->dtr, 0);
                })
                ->editColumn('smin', function ($row) {
                    return number_format($row->smin ?? 0, 0);
                })
                ->editColumn('smax', function ($row) {
                    return number_format($row->smax ?? 0, 0);
                })
                ->editColumn('srmin', function ($row) {
                    return number_format($row->srmin ?? 0, 0);
                })
                ->editColumn('srmax', function ($row) {
                    return number_format($row->srmax ?? 0, 0);
                })
                ->editColumn('sminlm', function ($row) {
                    return number_format($row->sminlm ?? 0, 0);
                })
                ->editColumn('smaxlm', function ($row) {
                    return number_format($row->smaxlm ?? 0, 0);
                })
                ->editColumn('srminlm', function ($row) {
                    return number_format($row->srminlm ?? 0, 0);
                })
                ->editColumn('srmaxlm', function ($row) {
                    return number_format($row->srmaxlm ?? 0, 0);
                })
                ->editColumn('stock', function ($row) {
                    return number_format($row->stock ?? 0, 0);
                })
                ->editColumn('kosong', function ($row) {
                    return number_format($row->kosong ?? 0, 0);
                })
                ->rawColumns([])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Proses Save/Update/Delete/Posting
     */
    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $periode = $request->session()->get('periode');

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Convert periode to string if array
            if (is_array($periode)) {
                $periode = ($periode['bulan'] ?? '01') . ($periode['tahun'] ?? date('Y'));
            }

            $action = $request->input('action', '');

            DB::beginTransaction();

            switch ($action) {
                case 'new':
                    return $this->prosesNew($request, $CBG, $username, $periode);

                case 'posting':
                    return $this->prosesPosting($request, $CBG, $username, $periode);

                case 'update_lph':
                    return $this->updateLPH($request, $CBG, $username);

                default:
                    DB::rollBack();
                    return response()->json(['error' => 'Action tidak valid'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses New - Ambil Data LPH
     */
    private function prosesNew($request, $CBG, $username, $periode)
    {
        try {
            $connection = strtolower($CBG);

            Log::info('TUsulanLPHPeriode prosesNew: CBG=' . $CBG . ', Periode=' . $periode . ', User=' . $username);

            // Check if already processed - use TGZ connection for PERID table
            $queryCheck = "SELECT LPH_{$CBG} AS LPH FROM PERID WHERE KD_PERI = ?";
            Log::info('TUsulanLPHPeriode prosesNew check query: ' . $queryCheck . ', Periode: ' . $periode);

            $check = DB::connection('tgz')->select($queryCheck, [$periode]);

            if (!empty($check) && $check[0]->LPH == 1) {
                Log::warning('TUsulanLPHPeriode prosesNew: Data periode ' . $periode . ' sudah diproses');
                DB::rollBack();
                return response()->json(['error' => 'Data Bulan ini Sudah Di Proses'], 400);
            }

            // Call stored procedure 1 - use TGZ connection
            Log::info('TUsulanLPHPeriode prosesNew: Calling lph_1periode with params: ' . $CBG . ', ' . $periode . ', ' . $username);
            DB::connection('tgz')->statement("CALL lph_1periode(?, ?, ?)", [$CBG, $periode, $username]);

            // Call stored procedure 2 - use TGZ connection
            Log::info('TUsulanLPHPeriode prosesNew: Calling lph_2periode with params: ' . $CBG . ', ' . $periode . ', ' . $username);
            DB::connection('tgz')->statement("CALL lph_2periode(?, ?, ?)", [$CBG, $periode, $username]);

            DB::commit();

            Log::info('TUsulanLPHPeriode prosesNew: Success - Data berhasil diproses');

            return response()->json([
                'success' => true,
                'message' => 'Proses Ambil Data Berhasil'
            ]);
        } catch (\Exception $e) {
            Log::error('TUsulanLPHPeriode prosesNew error: ' . $e->getMessage());
            Log::error('TUsulanLPHPeriode prosesNew error trace: ' . $e->getTraceAsString());
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Proses Posting
     */
    private function prosesPosting($request, $CBG, $username, $periode)
    {
        $items = $request->input('items', []);

        if (empty($items)) {
            DB::rollBack();
            return response()->json(['error' => 'Tidak ada data untuk diproses'], 400);
        }

        foreach ($items as $item) {
            if (!isset($item['post']) || $item['post'] != '1') {
                continue;
            }

            $cbgx = $item['cbg'];
            $subx = $item['sub'];
            $perx = $item['per'];
            $ket = $item['ket'];

            $cbgxConn = strtolower($cbgx);

            Log::info('TUsulanLPHPeriode prosesPosting: Starting item - CBG=' . $cbgx . ', Sub=' . $subx . ', Per=' . $perx . ', Ket=' . $ket);

            // Check if data exists - use cbgx connection
            $queryCheck = "SELECT * FROM oleolang WHERE per = ? AND sub = ? AND ket = ?";
            $check = DB::connection($cbgxConn)->select($queryCheck, [$perx, $subx, $ket]);

            if (empty($check)) {
                Log::warning('TUsulanLPHPeriode prosesPosting: No data found for Sub=' . $subx . ', skipping');
                continue;
            }

            // Determine LPH field based on cbg
            $lphField = '';
            if ($cbgx == 'TGZ') {
                $lphField = 'a.lph';
            } elseif ($cbgx == 'TMM') {
                $lphField = 'a.lph_tm';
            } elseif ($cbgx == 'SOP') {
                $lphField = 'a.lph_tf';
            }

            if (empty($lphField)) {
                continue;
            }

            Log::info('TUsulanLPHPeriode prosesPosting: Processing CBG=' . $cbgx . ', Sub=' . $subx . ', Per=' . $perx . ', Ket=' . $ket . ', LPH Field=' . $lphField);

            // Get all cabang - use TGZ connection
            $queryCabang = "SELECT TRIM(KODE) as cbg FROM toko WHERE th <> ''";
            $cabangs = DB::connection('tgz')->select($queryCabang);

            Log::info('TUsulanLPHPeriode prosesPosting: Found ' . count($cabangs) . ' cabang to update');

            // Update each cabang
            foreach ($cabangs as $cab) {
                $cabang = $cab->cbg;
                $cabangConn = strtolower($cabang);

                $updateBrg = "
                    UPDATE brg a, {$cbgx}.oleolang b    
                    SET {$lphField} = b.LPH,                             
                        a.KETX = CONCAT('Ganti LPH', ' ', ?, {$lphField}),    
                        a.USERX = ?, 
                        a.TGLX = NOW()                         
                    WHERE a.KD_BRG = b.KD_BRG 
                        AND b.per = ? 
                        AND b.sub = ? 
                        AND b.ket = ?
                ";

                try {
                    DB::connection($cabangConn)->statement($updateBrg, [$cbgx, $username, $perx, $subx, $ket]);
                    Log::info('TUsulanLPHPeriode prosesPosting: Updated brg in ' . $cabang);
                } catch (\Exception $e) {
                    Log::warning('TUsulanLPHPeriode prosesPosting: Failed to update ' . $cabang . ' - ' . $e->getMessage());
                }
            }

            // Update brgdt - use cbgx connection (already defined above)
            $updateBrgdt = "
                UPDATE brgdt a, oleolang b                        
                SET a.lph_lalu = a.lph, 
                    a.lph = b.lph,   
                    a.srmin = b.srmin,               
                    a.srmax = b.srmax,               
                    a.smin = b.smin,                
                    a.smax = b.smax,                
                    a.kdlaku = b.kdlaku,              
                    a.ketx = CONCAT('Form LPH Penjualan = KDlaku ole olang ', ?),  
                    a.userx = ?, 
                    a.tglx = NOW()  
                WHERE a.kd_brg = b.kd_brg 
                    AND b.per = ? 
                    AND b.ket = ?         
                    AND b.sub = ?
            ";

            DB::connection($cbgxConn)->statement($updateBrgdt, [$cbgx, $username, $perx, $ket, $subx]);
            Log::info('TUsulanLPHPeriode prosesPosting: Updated brgdt in ' . $cbgx);

            // Call repair procedure - use cbgx connection
            DB::connection($cbgxConn)->statement("CALL dcts_repair_lph_periode(?, ?, ?, ?)", [$perx, $subx, $ket, $username]);
            Log::info('TUsulanLPHPeriode prosesPosting: Called repair procedure');

            // Insert to history - use cbgx connection
            $insertHistory = "
                INSERT INTO oleolang_his (
                    CBG, SUB, KD_BRG, NA_BRG, TOTAL_LK, HARI_LK, KET_KEM, KET_UK, 
                    SMINLM, SMAXLM, SRMINLM, SRMAXLM, 
                    DTR, DTRDC, LPHLM, LPH_SARAN, LPH, KLK, KDLAKULM, KDLAKU, KEM, 
                    KOSONG, KSG, SMIN, SMAX, SRMIN, SRMAX, USRNM, TG_SMP, PER, KET, POST
                ) 
                SELECT 
                    CBG, SUB, KD_BRG, NA_BRG, TOTAL_LK, HARI_LK, KET_KEM, KET_UK, 
                    SMINLM, SMAXLM, SRMINLM, SRMAXLM,  
                    DTR, DTRDC, LPHLM, LPH_SARAN, LPH, KLK, KDLAKULM, KDLAKU, KEM,                                                  
                    KOSONG, KSG, SMIN, SMAX, SRMIN, SRMAX, USRNM, TG_SMP, PER, KET, '1' 
                FROM oleolang 
                WHERE sub = ? 
                    AND cbg = ? 
                    AND per = ? 
                    AND ket = ?
            ";

            DB::connection($cbgxConn)->statement($insertHistory, [$subx, $cbgx, $perx, $ket]);
            Log::info('TUsulanLPHPeriode prosesPosting: Inserted to history');

            // Delete from oleolang - use cbgx connection
            $delete = "DELETE FROM oleolang WHERE sub = ? AND cbg = ? AND per = ? AND ket = ?";
            DB::connection($cbgxConn)->statement($delete, [$subx, $cbgx, $perx, $ket]);
            Log::info('TUsulanLPHPeriode prosesPosting: Deleted from oleolang');

            // Update PERID - use TGZ connection
            $updatePerid = "UPDATE PERID SET LPH_{$cbgx} = 1 WHERE KD_PERI = ?";
            DB::connection('tgz')->statement($updatePerid, [$perx]);
            Log::info('TUsulanLPHPeriode prosesPosting: Updated PERID for ' . $cbgx);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil di proses'
        ]);
    }

    /**
     * Update LPH from Edit page
     */
    private function updateLPH($request, $CBG, $username)
    {
        $kd_brg = $request->input('kd_brg');
        $lph = $request->input('lph');
        $dtr = $request->input('dtr');
        $sub = $request->input('sub');
        $per = $request->input('per');
        $ket = $request->input('ket');
        $outlet = $request->input('cbg', $CBG);

        $outletConn = strtolower($outlet);

        Log::info('TUsulanLPHPeriode updateLPH: KD_BRG=' . $kd_brg . ', LPH=' . $lph . ', DTR=' . $dtr . ', Outlet=' . $outlet);

        // Call calculation procedure - use outlet connection
        $queryCalc = "CALL pjl_lphch(?, '', ?, ?)";
        $result = DB::connection($outletConn)->select($queryCalc, [$kd_brg, $lph, $dtr]);

        if (empty($result)) {
            Log::error('TUsulanLPHPeriode updateLPH: Failed to calculate parameters');
            DB::rollBack();
            return response()->json(['error' => 'Gagal menghitung parameter'], 400);
        }

        $calc = $result[0];

        Log::info('TUsulanLPHPeriode updateLPH: Calculated - SMIN=' . ($calc->smin ?? 0) . ', SMAX=' . ($calc->smax ?? 0) . ', SRMIN=' . ($calc->srmin ?? 0) . ', SRMAX=' . ($calc->srmax ?? 0));

        // Update oleolang - use outlet connection
        $update = "
            UPDATE oleolang 
            SET lph = ?, 
                dtr = ?,
                smin = ?, 
                smax = ?, 
                srmin = ?, 
                srmax = ?, 
                kdlaku = ?,
                tg_smp = NOW(),
                usrnm = ?
            WHERE kd_brg = ? 
                AND per = ? 
                AND sub = ? 
                AND ket = ?
        ";

        DB::connection($outletConn)->statement($update, [
            $lph,
            $dtr,
            $calc->smin ?? 0,
            $calc->smax ?? 0,
            $calc->srmin ?? 0,
            $calc->srmax ?? 0,
            $calc->kdlaku ?? '',
            $username,
            $kd_brg,
            $per,
            $sub,
            $ket
        ]);

        Log::info('TUsulanLPHPeriode updateLPH: Successfully updated oleolang');

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $calc
        ]);
    }

    /**
     * Search Barang (if needed for future enhancement)
     */
    public function searchBarang(Request $request)
    {
        try {
            $kd_brg = $request->input('kd_brg');
            $cbg = Auth::user()->CBG ?? null;

            if (!$cbg) {
                return response()->json(['success' => false, 'message' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "
                SELECT 
                    brg.KD_BRG,
                    brg.NA_BRG,
                    brg.KET_UK,
                    brg.KET_KEM,
                    brgdt.LPH,
                    brgdt.DTR,
                    brgdt.SMIN,
                    brgdt.SMAX,
                    brgdt.SRMIN,
                    brgdt.SRMAX,
                    brgdt.KDLAKU
                FROM brg
                INNER JOIN brgdt ON brg.KD_BRG = brgdt.KD_BRG
                WHERE brg.KD_BRG = ? 
                AND brgdt.CBG = ?
                AND brgdt.YER = YEAR(NOW())
            ";

            $result = DB::select($query, [$kd_brg, $cbg]);

            if (!empty($result)) {
                return response()->json([
                    'success' => true,
                    'data' => $result[0]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchBarang: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
