<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHStokOpnameHadiahController extends Controller
{
    public function index(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status', 'simpan');

        if ($no_bukti || $status == 'edit') {
            return $this->showEditForm($request);
        }

        return view('promo_hadiah_stok_opname_hadiah.index');
    }

    private function showEditForm(Request $request)
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
            $header = DB::select(
                "SELECT * FROM lapbh WHERE flag = 'SH' AND no_bukti = ?",
                [$no_bukti]
            );

            if (!empty($header)) {
                $detail = DB::select(
                    "SELECT * FROM lapbhd WHERE no_bukti = ? ORDER BY rec",
                    [$no_bukti]
                );

                $data['header'] = $header[0];
                $data['detail'] = $detail;
                $data['no_bukti'] = $no_bukti;
            }
        }

        return view('promo_hadiah_stok_opname_hadiah.edit', $data);
    }

    public function getData(Request $request)
    {
        $cbg = session('cbg', '01');

        $query = DB::select(
            "SELECT * FROM lapbh WHERE flag = 'SH' AND cbg = ? ORDER BY no_bukti DESC",
            [$cbg]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('tgl', function ($row) {
                return $row->tgl ? date('d/m/Y', strtotime($row->tgl)) : '';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button onclick="editData(\'' . $row->no_bukti . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                $btnPrint = '<button onclick="printData(\'' . $row->no_bukti . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                return $btnEdit . ' ' . $btnPrint;
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

        $periode = $per['bulan'] . '/' . $per['tahun'];
            $username = Auth::user()->username ?? 'system';
            $cbg = session('cbg', '01');

            $this->validateDatePeriode($request->tgl, $periode);

            $details = collect($request->details)->filter(function ($detail) {
                return !empty($detail['kd_brg']);
            })->values();

            if ($details->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail tidak boleh kosong'
                ], 400);
            }

            if ($no_bukti == '+') {
                $no_bukti = $this->generateNoBukti($periode, $cbg);
            }

            $check = DB::select("SELECT no_bukti FROM lapbh WHERE no_bukti = ?", [$no_bukti]);

            if (empty($check)) {
                DB::statement(
                    "INSERT INTO lapbh (tgl, no_bukti, tg_smp, usrnm, cbg, flag, gol, sub)
                     VALUES (DATE(NOW()), ?, NOW(), ?, ?, 'SH', ?, ?)",
                    [
                        $no_bukti,
                        $username,
                        $cbg,
                        intval($request->gol ?? 0),
                        trim($request->sub ?? '')
                    ]
                );
            }

            $header_id_result = DB::select("SELECT no_id FROM lapbh WHERE no_bukti = ?", [$no_bukti]);
            $id = $header_id_result[0]->no_id ?? 0;

            DB::statement("DELETE FROM lapbhd WHERE no_bukti = ?", [$no_bukti]);

            $rec = 1;
            foreach ($details as $detail) {
                if (!empty($detail['kd_brg']) && isset($detail['cek']) && $detail['cek'] == 1) {
                    DB::statement(
                        "INSERT INTO lapbhd (id, rec, no_bukti, kd_brg, itemsub, na_brg, ket_uk, ket_kem, kd, hj, saldo, flag, lph)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'SH', ?)",
                        [
                            $id,
                            $rec,
                            $no_bukti,
                            trim($detail['kd_brg']),
                            trim($detail['itemsub'] ?? ''),
                            trim($detail['na_brg'] ?? ''),
                            trim($detail['ket_uk'] ?? ''),
                            trim($detail['ket_kem'] ?? ''),
                            trim($detail['kd'] ?? ''),
                            floatval($detail['hj'] ?? 0),
                            floatval($detail['saldo'] ?? 0),
                            floatval($detail['lph'] ?? 0)
                        ]
                    );
                    $rec++;
                }
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
        $bulan = $request->get('bulan', '02');
        $tipe = $request->get('tipe', 'TOKO');

        if ($tipe == 'GUDANG') {
            $query = "SELECT kd_brgh, na_brgh, 'TGZ' AS cbg, gak AS saldo
                      FROM brgh
                      WHERE gak <> 0";

            if (!empty($q)) {
                $query .= " AND (kd_brgh LIKE ? OR na_brgh LIKE ?)";
                $data = DB::select($query . " ORDER BY kd_brgh LIMIT 50", ["%$q%", "%$q%"]);
            } else {
                $data = DB::select($query . " ORDER BY kd_brgh LIMIT 50");
            }
        } else {
            $query = "SELECT kd_brgh, na_brgh, cbg, ak{$bulan} AS saldo
                      FROM brghd
                      WHERE cbg = ? AND ak{$bulan} <> 0";

            if (!empty($q)) {
                $query .= " AND (kd_brgh LIKE ? OR na_brgh LIKE ?)";
                $data = DB::select($query . " ORDER BY kd_brgh LIMIT 50", [$cbg, "%$q%", "%$q%"]);
            } else {
                $data = DB::select($query . " ORDER BY kd_brgh LIMIT 50", [$cbg]);
            }
        }

        return response()->json($data);
    }

    public function getDetail(Request $request)
    {
        $kd_brgh = $request->get('kd_brgh');
        $cbg = session('cbg', '01');

        $product = DB::select(
            "SELECT brgh.kd_brgh AS kd_brg, brgh.na_brgh AS na_brg, brghd.ak02 AS saldo
             FROM brgh, brghd
             WHERE brgh.kd_brgh = brghd.kd_brgh AND brghd.cbg = ? AND brgh.kd_brgh = ?
             ORDER BY brgh.kd_brgh",
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

    public function printStokOpnameHadiah(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $cbg = session('cbg', '01');

        $toko = DB::select("SELECT na_toko FROM toko WHERE kode = ?", [$cbg]);
        $na_toko = $toko[0]->na_toko ?? '';

        $data = DB::select(
            "SELECT *, CONCAT(LEFT(lapbh.no_bukti, 2), RIGHT(lapbh.no_bukti, 5)) AS bukt
             FROM lapbh, lapbhd
             WHERE lapbh.no_bukti = lapbhd.no_bukti AND lapbh.no_bukti = ?",
            [$no_bukti]
        );

        return response()->json([
            'success' => true,
            'data' => $data,
            'toko' => $na_toko
        ]);
    }

    private function validateDatePeriode($tgl, $periode)
    {
        $tgl = Carbon::parse($tgl);
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

        $kode = 'SH' . substr($year, -2) . $monthString;

        $notrans = DB::select(
            "SELECT NOM{$monthString} AS no_bukti FROM notrans WHERE trans = 'SOHADIAH' AND per = ?",
            [$year]
        );
        $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

        DB::statement(
            "UPDATE notrans SET NOM{$monthString} = ? WHERE trans = 'SOHADIAH' AND per = ?",
            [$r1, $year]
        );

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }
}
