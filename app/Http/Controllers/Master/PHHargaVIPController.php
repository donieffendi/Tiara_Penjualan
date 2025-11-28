<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PHPJasperXML;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

class PHHargaVIPController extends Controller
{
    /**
     * Display index page for harga VIP
     */
    public function index()
    {
        return view('promo_hadiah_harga_vip.index');
    }

    /**
     * Get list of harga VIP for datatable
     */
    public function getData(Request $request)
    {
        $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];
        $cbg = session('cbg', '01');

        // Query matching Delphi: SELECT NO_BUKTI,TGL,notes, posted FROM DIS where per=:per and flag='PV'
        $query = DB::select(
            "SELECT NO_BUKTI, TGL, TGL_MULAI, TGL_SLS, notes, POSTED
             FROM DIS
             WHERE per=? AND flag='PV'
             GROUP BY no_bukti
             ORDER BY NO_BUKTI DESC",
            [$periode]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TGL', function ($row) {
                return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
            })
            ->editColumn('TGL_MULAI', function ($row) {
                return $row->TGL_MULAI ? date('d/m/Y', strtotime($row->TGL_MULAI)) : '';
            })
            ->editColumn('TGL_SLS', function ($row) {
                return $row->TGL_SLS ? date('d/m/Y', strtotime($row->TGL_SLS)) : '';
            })
            ->editColumn('POSTED', function ($row) {
                return $row->POSTED == 1 ? '<span class="badge badge-success">Posted</span>' : '<span class="badge badge-warning">Open</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row->POSTED == 0) {
                    $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                } else {
                    $btnEdit = '<button class="btn btn-sm btn-secondary" disabled title="Sudah Terposting"><i class="fas fa-lock"></i></button>';
                }
                $btnDelete = '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>';
                $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                return $btnEdit . ' ' . $btnDelete . ' ' . $btnPrint;
            })
            ->rawColumns(['action', 'POSTED'])
            ->make(true);
    }

    /**
     * Show form for create/edit harga VIP
     */
    public function edit(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status', 'simpan');

        $data = [
            'no_bukti' => '+',
            'status' => $status,
            'header' => null,
            'detail' => [],
            'periode' => session('periode', date('m.Y')),
            'cbg' => session('cbg', '01')
        ];

        if ($status == 'edit' && $no_bukti) {
            // Check if posted
            $check_posted = DB::select("SELECT posted FROM DIS WHERE no_bukti = ? AND flag='PV'", [$no_bukti]);

            if (!empty($check_posted) && $check_posted[0]->posted == 1) {
                return redirect()->route('phhargavip')->with('error', 'Data Sudah Terposting !!');
            }

            // Get header data
            $header = DB::select(
                "SELECT NO_BUKTI, TGL, TGL_MULAI, TGL_SLS, notes, posted
                 FROM DIS
                 WHERE no_bukti = ? AND flag='PV'
                 LIMIT 1",
                [$no_bukti]
            );

            if (!empty($header)) {
                // Get detail data - matching Delphi query structure
                $detail = DB::select(
                    "SELECT NO_BUKTI, REC, KD_BRG, NA_BRG, KET_UK, KET_KEM,
                            HJVIP, KET, PER, NO_ID
                     FROM DISD
                     WHERE no_bukti = ?
                     ORDER BY REC",
                    [$no_bukti]
                );

                // Get HJ from brgdt for each item
                foreach ($detail as $item) {
                    $hj_result = DB::select(
                        "SELECT hj FROM brgdt WHERE cbg=? AND kd_brg=?",
                        [$data['cbg'], $item->KD_BRG]
                    );
                    $item->HJ = !empty($hj_result) ? $hj_result[0]->hj : 0;
                }

                $data['header'] = $header[0];
                $data['detail'] = $detail;
                $data['no_bukti'] = $no_bukti;
            }
        }

        return view('promo_hadiah_harga_vip.edit', $data);
    }

    /**
     * Store/Update harga VIP
     */
    public function store(Request $request)
    {
        // dd($request->details);
        // dd($request->all());
        // $this->validate($request, [
        //     'tgl' => 'required|date',
        //     'tgl_mulai' => 'required|date',
        //     'tgl_sls' => 'required|date',
        //     'details' => 'required|array|min:1'
        // ]);

        DB::beginTransaction();

        try {
            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $per = session('periode', date('m.Y'));

            $periode = $per['bulan'] . '/' . $per['tahun'];        $cbg = session('cbg', '01');

            $cbg = Auth::user()->CBG ?? 'TGZ';
            $username = Auth::user()->username ?? 'system';

            // Check if period is closed - matching Delphi check
            $check_period = DB::select("SELECT posted FROM perid WHERE kd_peri=?", [$periode]);
            if (!empty($check_period) && $check_period[0]->posted == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closed Period'
                ], 400);
            }

            // Validate dates match periode
            $tgl = Carbon::parse($request->tgl);
            $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
            $yearz = $tgl->year;

            $periode_month = substr($periode, 0, 2);
            $periode_year = substr($periode, -4);

            if ($monthz != $periode_month) {
                return response()->json([
                    'success' => false,
                    'message' => 'Month is not the same as Periode.'
                ], 400);
            }

            if ($yearz != $periode_year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Year is not the same as Periode.'
                ], 400);
            }
            // dd($status);

            if ($status == 'simpan') {
                // Generate no_bukti
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                }

                // Insert header - matching Delphi structure
                DB::statement(
                    "INSERT INTO DIS (TGL, CBG, NO_BUKTI, TGL_MULAI, TGL_SLS, FLAG, USRNM, PER, TG_SMP, notes)
                     VALUES (?, ?, ?, ?, ?, 'PV', ?, ?, NOW(), ?)",
                    [
                        $request->tgl,
                        $cbg,
                        $no_bukti,
                        $request->tgl_mulai,
                        $request->tgl_sls,
                        $username,
                        $periode,
                        $request->notes ?? ''
                    ]
                );
            } else {
                // Update mode
                DB::statement(
                    "UPDATE DIS
                     SET NO_BUKTI=?, TGL=?, TGL_MULAI=?, TGL_SLS=?, USRNM=?, TG_SMP=NOW(), notes=?
                     WHERE NO_BUKTI=?",
                    [
                        $no_bukti,
                        $request->tgl,
                        $request->tgl_mulai,
                        $request->tgl_sls,
                        $username,
                        $request->notes ?? '',
                        $no_bukti
                    ]
                );
            }

            // Get header ID
            $header_id_result = DB::select("SELECT NO_ID FROM DIS WHERE no_bukti=?", [$no_bukti]);
            $id = $header_id_result[0]->NO_ID ?? 0;

            // Handle detail updates
            if ($status == 'edit') {
                $existing_details = DB::select("SELECT no_id FROM DISD WHERE no_bukti = ?", [$no_bukti]);

                foreach ($existing_details as $existing) {
                    $found = false;
                    foreach ($request->details as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
                            // Update existing record
                            DB::statement(
                                "UPDATE DISD
                                 SET REC=?, KD_BRG=?, NA_BRG=?, KET_UK=?, KET_KEM=?,
                                     HJVIP=?, KET=?
                                 WHERE NO_ID=?",
                                [
                                    intval($detail['rec'] ?? 1),
                                    trim($detail['kd_brg'] ?? ''),
                                    trim($detail['na_brg'] ?? ''),
                                    trim($detail['ket_uk'] ?? ''),
                                    trim($detail['ket_kem'] ?? ''),
                                    floatval($detail['hjvip'] ?? 0),
                                    trim($detail['ket'] ?? ''),
                                    $existing->no_id
                                ]
                            );
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        // Delete record
                        DB::statement("DELETE FROM DISD WHERE NO_ID = ?", [$existing->no_id]);
                    }
                }
            }

            // Insert new detail records
            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                        DB::statement(
                            "INSERT INTO DISD (NO_BUKTI, REC, PER, KD_BRG, NA_BRG,
                                               KET_UK, KET_KEM, HJVIP, ket, ID)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $rec,
                                $periode,
                                trim($detail['kd_brg']),
                                trim($detail['na_brg'] ?? ''),
                                trim($detail['ket_uk'] ?? ''),
                                trim($detail['ket_kem'] ?? ''),
                                floatval($detail['hjvip'] ?? 0),
                                trim($detail['ket'] ?? ''),
                                $id
                            ]
                        );
                    }
                    $rec++;
                }
            }

            // Distribute data to other outlets - matching Delphi logic
            $outlets = DB::select(
                "SELECT TRIM(KODE) as cbg FROM toko WHERE kode <> ? AND STA IN ('MA','CB') ORDER BY NO_ID ASC",
                [$cbg]
            );

            foreach ($outlets as $outlet) {
                $cab = $outlet->cbg;

                // Delete existing data
                DB::statement("DELETE FROM {$cab}.dis WHERE no_bukti=?", [$no_bukti]);
                DB::statement("DELETE FROM {$cab}.disd WHERE no_bukti=?", [$no_bukti]);

                // Insert header
                DB::statement(
                    "INSERT INTO {$cab}.dis (TGL, CBG, NO_BUKTI, TGL_MULAI, TGL_SLS, FLAG, USRNM, PER, TG_SMP, notes)
                     SELECT TGL, CBG, NO_BUKTI, TGL_MULAI, TGL_SLS, FLAG, USRNM, PER, TG_SMP, notes
                     FROM dis WHERE no_bukti=?",
                    [$no_bukti]
                );

                // Insert detail
                DB::statement(
                    "INSERT INTO {$cab}.disd (NO_BUKTI, REC, PER, KD_BRG, NA_BRG,
                                              KET_UK, KET_KEM, HJVIP, ket, ID)
                     SELECT NO_BUKTI, REC, PER, KD_BRG, NA_BRG,
                            KET_UK, KET_KEM, HJVIP, ket, ID
                     FROM disd WHERE no_bukti=?",
                    [$no_bukti]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success',
                'no_bukti' => $no_bukti
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Browse data (product, etc.)
     */
    public function browse(Request $request)
    {
        $type = $request->get('type', 'product');
        $q = $request->get('q', '');
       

        if ($type == 'product') {
            $cbg = Auth::user()->CBG;

            if (!empty($q)) {
                $data = DB::select(
                    "SELECT B.KD_BRG, B.NA_BRG, B.KET_UK, B.KET_KEM, A.HJ
                     FROM brgdt A, brg B
                     WHERE A.KD_BRG = B.KD_BRG
                       AND A.CBG = ?
                       AND (B.KD_BRG LIKE ? OR B.NA_BRG LIKE ?)
                     ORDER BY B.KD_BRG
                     LIMIT 50",
                    [$cbg, "%$q%", "%$q%"]
                );
            } else {
                $data = DB::select(
                    "SELECT B.KD_BRG, B.NA_BRG, B.KET_UK, B.KET_KEM, A.HJ
                     FROM brgdt A, brg B
                     WHERE A.KD_BRG = B.KD_BRG AND A.CBG = ?
                     ORDER BY B.KD_BRG
                     LIMIT 50",
                    [$cbg]
                );
            }
        } else {
            $data = [];
        }

        return response()->json($data);
    }

    /**
     * Get detail (validate product)
     */
    public function getDetail(Request $request)
    {
        $type = $request->get('type', 'product');

        if ($type == 'product') {
            $kd_brg = $request->get('kd_brg');
            $cbg = Auth::user()->CBG ?? null;

            $product = DB::select(
                "SELECT B.KD_BRG, B.NA_BRG, B.KET_UK, B.KET_KEM, A.HJ
                 FROM brgdt A, brg B
                 WHERE A.KD_BRG = B.KD_BRG
                   AND A.CBG = ?
                   AND A.KD_BRG = ?
                 LIMIT 1",
                [$cbg, $kd_brg]
            );

            if (!empty($product)) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $product[0]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ]);
        }

        return response()->json([
            'success' => true,
            'exists' => false,
            'data' => null
        ]);
    }

    /**
     * Print harga VIP
     */
    public function printHargaVIP(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $per = session('periode', date('m.Y'));
        $TGL = Carbon::now()->format('d-m-Y');

        $periode = $per['bulan'] . '/' . $per['tahun'];     

        $cbg = Auth::user()->CBG;

        $data = DB::select(
            "SELECT dis.NO_BUKTI, dis.TGL, disd.KD_BRG,
                    disd.NA_BRG, disd.ket_uk, disd.ket_kem, disd.HJVIP, brgdt.hj, disd.ket
             FROM dis, disd, brgdt
             WHERE disd.KD_BRG = brgdt.KD_BRG
               AND brgdt.CBG = DIS.CBG
               AND dis.flag = 'PV'
               AND dis.no_bukti = disd.no_bukti
               AND dis.no_bukti = ?
               AND dis.per = ?
               AND dis.cbg = ?
             ORDER BY dis.no_bukti",
            [$no_bukti, $periode, $cbg]
        );

        // return response()->json([
        //     'data' => $data
        // ]);
        $file         = 'print_harga_vip';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

        // $PHPJasperXML->setData($data);
        $cleanData                    = json_decode(json_encode($data), true);
        $PHPJasperXML->arrayParameter = [
            "TGL"   => $TGL,
        ];

        $PHPJasperXML->setData($cleanData);

        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    /**
     * Delete harga VIP
     */
    public function destroy($no_bukti)
    {
        try {
            $check_posted = DB::select("SELECT posted FROM DIS WHERE no_bukti = ? AND flag='PV'", [$no_bukti]);

            if (!empty($check_posted) && $check_posted[0]->posted == 1) {
                return redirect()->route('phhargavip')->with('error', 'Data sudah terposting, tidak dapat dihapus');
            }

            DB::beginTransaction();

            // Delete from all outlets - matching Delphi logic
            $outlets = DB::select("SELECT TRIM(KODE) as cbg FROM toko WHERE STA IN ('MA','CB') ORDER BY NO_ID ASC");

            foreach ($outlets as $outlet) {
                DB::statement("DELETE FROM {$outlet->cbg}.disd WHERE NO_BUKTI=?", [$no_bukti]);
                DB::statement("DELETE FROM {$outlet->cbg}.dis WHERE NO_BUKTI=?", [$no_bukti]);
            }

            DB::commit();

            return redirect()->route('phhargavip')->with('success', 'Harga VIP ' . $no_bukti . ' telah terhapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('phhargavip')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Generate no_bukti for new transaction - matching Delphi logic
     */
    private function generateNoBukti($periode, $cbg)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        // Get toko type
        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = $toko[0]->type ?? '';

        $kode = 'PV' . substr($year, -2) . $monthString;

        // Get next number from notrans
        $notrans = DB::select("SELECT NOM{$monthString} as no_bukti FROM notrans WHERE trans='HJVIP' AND per=?", [$year]);
        $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

        // Update counter
        DB::statement("UPDATE notrans SET NOM{$monthString} = ? WHERE trans='HJVIP' AND per=?", [$r1, $year]);

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }
}