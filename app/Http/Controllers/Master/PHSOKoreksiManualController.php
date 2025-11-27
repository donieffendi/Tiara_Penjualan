<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHSOKoreksiManualController extends Controller
{
    public function index(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status');

        if ($no_bukti && $status == 'edit') {
            return $this->edit($no_bukti);
        }

        return view('promo_hadiah_stok_opname_koreksi_manual.index');
    }

    private function edit($no_bukti)
    {
$per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];        $cbg = session('cbg', '01');

        $header = DB::select(
            "SELECT * FROM stockb WHERE no_bukti = ? AND per = ? AND flag = 'MH' AND cbg = ?",
            [$no_bukti, $periode, $cbg]
        );

        if (empty($header)) {
            $header = DB::select(
                "SELECT * FROM stockbz WHERE no_bukti = ? AND per = ? AND flag = 'MH' AND cbg = ?",
                [$no_bukti, $periode, $cbg]
            );
        }

        $detail = [];
        if (!empty($header)) {
            $posted = $header[0]->posted ?? 0;

            if ($posted == 0) {
                $detail = DB::select(
                    "SELECT * FROM stockbd WHERE no_bukti = ? ORDER BY rec",
                    [$no_bukti]
                );
            }
        }

        $data = [
            'no_bukti' => $no_bukti,
            'status' => 'edit',
            'header' => $header[0] ?? null,
            'detail' => $detail,
            'periode' => $periode,
            'cbg' => $cbg
        ];

        return view('promo_hadiah_stok_opname_koreksi_manual.form', $data);
    }

    public function create()
    {
        $data = [
            'no_bukti' => '+',
            'status' => 'simpan',
            'header' => null,
            'detail' => [],
            'periode' => session('periode', date('m.Y')),
            'cbg' => session('cbg', '01')
        ];

        return view('promo_hadiah_stok_opname_koreksi_manual.form', $data);
    }

    public function getData(Request $request)
    {
$per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];        $cbg = session('cbg', '01');

        $query = DB::select(
            "SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, TYPE, BKTK, POSTED
             FROM stockb
             WHERE per = ? AND flag = 'MH' AND cbg = ?
             UNION ALL
             SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, TYPE, BKTK, POSTED
             FROM stockbz
             WHERE per = ? AND flag = 'MH' AND cbg = ?
             ORDER BY NO_BUKTI DESC",
            [$periode, $cbg, $periode, $cbg]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('tgl', function ($row) {
                return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
            })
            ->editColumn('total_qty', function ($row) {
                return number_format($row->TOTAL_QTY, 2, ',', '.');
            })
            ->addColumn('action', function ($row) {
                if ($row->POSTED == 0) {
                    $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                    return $btnEdit;
                }
                return '<span class="badge badge-success">Posted</span>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'details' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
    $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];            $username = Auth::user()->username ?? 'system';
            $cbg = session('cbg', '01');
            $tgl = Carbon::parse($request->tgl);

            $this->validateDatePeriode($tgl, $periode);

            $details = collect($request->details)->filter(function ($detail) {
                return !empty($detail['kd_brg']);
            })->values();

            if ($details->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail tidak boleh kosong'
                ], 400);
            }

            if ($status == 'simpan') {
                foreach ($details as $detail) {
                    $bktk = $detail['bktk'] ?? '';
                    if (substr($bktk, 0, 2) == 'TS' || substr($bktk, 0, 2) == 'GG') {
                        DB::statement(
                            "UPDATE stockbd
                             SET bktk = ?, qty = qty - (? * -1)
                             WHERE no_bukti = ? AND kd_brg = ?",
                            [$bktk, $detail['qty'], $bktk, $detail['kd_brg']]
                        );
                    }
                }

                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                }

                DB::statement(
                    "INSERT INTO stockb (no_bukti, tgl, flag, per, total_qty, notes, usrnm, tg_smp, type, cbg, total)
                     VALUES (?, ?, 'MH', ?, ?, ?, ?, NOW(), 'MUSNAH', ?, ?)",
                    [
                        $no_bukti,
                        $tgl->format('Y-m-d'),
                        $periode,
                        $request->total_qty ?? 0,
                        trim($request->notes ?? ''),
                        $username,
                        $cbg,
                        $request->total ?? 0
                    ]
                );
            } else {
                DB::statement(
                    "UPDATE stockb
                     SET tgl = ?, notes = ?, total_qty = ?, usrnm = ?, tg_smp = NOW()
                     WHERE no_bukti = ?",
                    [
                        $tgl->format('Y-m-d'),
                        trim($request->notes ?? ''),
                        $request->total_qty ?? 0,
                        $username,
                        $no_bukti
                    ]
                );
            }

            $header_id_result = DB::select("SELECT no_id FROM stockb WHERE no_bukti = ?", [$no_bukti]);
            $id = $header_id_result[0]->no_id ?? 0;

            $existing_details = DB::select("SELECT no_id FROM stockbd WHERE no_bukti = ?", [$no_bukti]);

            foreach ($existing_details as $existing) {
                $found = false;
                foreach ($details as $detail) {
                    if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    $detail_to_update = collect($details)->firstWhere('no_id', $existing->no_id);
                    DB::statement(
                        "UPDATE stockbd
                         SET rec = ?, kd_brg = ?, na_brg = ?, flag = 'MH', qty = ?, ket = ?
                         WHERE no_id = ?",
                        [
                            $detail_to_update['rec'] ?? 0,
                            trim($detail_to_update['kd_brg']),
                            trim($detail_to_update['na_brg'] ?? ''),
                            $detail_to_update['qty'] ?? 0,
                            trim($detail_to_update['ket'] ?? ''),
                            $existing->no_id
                        ]
                    );
                } else {
                    DB::statement("DELETE FROM stockbd WHERE no_id = ?", [$existing->no_id]);
                }
            }

            $rec = 1;
            foreach ($details as $detail) {
                if (empty($detail['kd_brg'])) continue;

                $detail['rec'] = $rec;

                if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                    DB::statement(
                        "INSERT INTO stockbd (no_bukti, rec, per, flag, kd_brg, na_brg, qty, ket, id)
                         VALUES (?, ?, ?, 'MH', ?, ?, ?, ?, ?)",
                        [
                            $no_bukti,
                            $rec,
                            $periode,
                            trim($detail['kd_brg']),
                            trim($detail['na_brg'] ?? ''),
                            $detail['qty'] ?? 0,
                            trim($detail['ket'] ?? ''),
                            $id
                        ]
                    );
                }

                $rec++;
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

    public function browse(Request $request)
    {
        $q = $request->get('q', '');
        $cbg = session('cbg', '01');
$per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];        
        $bulan = $per['bulan'];

        $query = "SELECT kd_brgh, na_brgh, ak{$bulan} AS qty
                  FROM brghd
                  WHERE cbg = ?";

        if (!empty($q)) {
            $query .= " AND (kd_brgh LIKE ? OR na_brgh LIKE ?)";
            $data = DB::select($query . " ORDER BY kd_brgh LIMIT 50", [$cbg, "%$q%", "%$q%"]);
        } else {
            $data = DB::select($query . " ORDER BY kd_brgh LIMIT 50", [$cbg]);
        }

        return response()->json($data);
    }

    public function getDetail(Request $request)
    {
        $kd_brgh = $request->get('kd_brgh');
        $cbg = session('cbg', '01');
$per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];        
        $bulan = $per['bulan'];

        $product = DB::select(
            "SELECT kd_brgh, na_brgh, ak{$bulan} AS qty
             FROM brghd
             WHERE cbg = ? AND kd_brgh = ?",
            [$cbg, $kd_brgh]
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

    public function printStokOpnameKoreksiManual(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $cbg = session('cbg', '01');
$per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];        $mon = substr($periode, 0, 2);

        $toko = DB::select("SELECT na_toko FROM toko WHERE kode = ?", [$cbg]);
        $na_toko = $toko[0]->na_toko ?? '';

        $data = DB::select(
            "SELECT :toko AS nmtoko, B.NO_BUKTI, B.KD_BRG, B.NA_BRG, B.QTY, C.AK{$mon} AS SALDO
             FROM stockbz A, stockbzd B
             LEFT JOIN brghd C ON B.KD_BRG = C.KD_BRGH
             WHERE A.NO_BUKTI = B.NO_BUKTI AND A.NO_BUKTI = ?
             ORDER BY B.KD_BRG",
            [$na_toko, $no_bukti]
        );

        return response()->json([
            'success' => true,
            'data' => $data,
            'toko' => $na_toko
        ]);
    }

    private function validateDatePeriode($tgl, $periode)
    {
        $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
        $yearz = $tgl->year;

        $periode_month = substr($periode, 0, 2);
        $periode_year = substr($periode, -4);

        if ($monthz != $periode_month) {
            throw new \Exception('Month is not the same as Periode.');
        }

        if ($yearz != $periode_year) {
            throw new \Exception('Year is not the same as Periode.');
        }
    }

    private function generateNoBukti($periode, $cbg)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = $toko[0]->type ?? '';

        $kode = 'MH' . substr($year, -2) . $monthString;

        $notrans = DB::select(
            "SELECT NOM{$monthString} AS no_bukti FROM notrans WHERE trans = 'MANUALHDH' AND per = ?",
            [$year]
        );
        $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

        DB::statement(
            "UPDATE notrans SET NOM{$monthString} = ? WHERE trans = 'MANUALHDH' AND per = ?",
            [$r1, $year]
        );

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }
}
