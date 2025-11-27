<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHStokOpnameKoreksiHadiahController extends Controller
{
    public function index(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status');

        if ($no_bukti && $status == 'edit') {
            return $this->edit($request);
        }

        if ($status == 'simpan') {
            return $this->create($request);
        }

        return view('promo_hadiah_stok_opname_koreksi_hadiah.index');
    }

    public function create(Request $request)
    {
        $data = [
            'no_bukti' => '+',
            'status' => 'simpan',
            'header' => null,
            'detail' => [],
            'periode' => session('periode', date('m.Y')),
            'cbg' => session('cbg', '01')
        ];

        return view('promo_hadiah_stok_opname_koreksi_hadiah.form', $data);
    }

    public function edit(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $cbg = session('cbg', '01');
        $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];

        $data = [
            'no_bukti' => $no_bukti,
            'status' => 'edit',
            'header' => null,
            'detail' => [],
            'periode' => $periode,
            'cbg' => $cbg
        ];

        $header = DB::select(
            "SELECT * FROM stockb WHERE no_bukti = ?",
            [$no_bukti]
        );

        if (!empty($header)) {
            $detail = DB::select(
                "SELECT * FROM stockbd WHERE no_bukti = ? ORDER BY rec",
                [$no_bukti]
            );

            $data['header'] = $header[0];
            $data['detail'] = $detail;
        }

        return view('promo_hadiah_stok_opname_koreksi_hadiah.form', $data);
    }

    public function getData(Request $request)
    {
        $cbg = session('cbg', '01');
        $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];
        $flag = $request->get('flag', 'HZ');

        $query = DB::select(
            "SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, TYPE, BKTK, POSTED
             FROM stockb
             WHERE per = ? AND flag = ? AND cbg = ?
             UNION ALL
             SELECT NO_BUKTI, TGL, TOTAL_QTY, NOTES, TYPE, BKTK, POSTED
             FROM stockbz
             WHERE per = ? AND flag = ? AND cbg = ?
             ORDER BY NO_BUKTI DESC",
            [$periode, $flag, $cbg, $periode, $flag, $cbg]
        );

        return DataTables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TGL', function ($row) {
                return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
            })
            ->editColumn('TOTAL_QTY', function ($row) {
                return number_format($row->TOTAL_QTY, 2, ',', '.');
            })
            ->addColumn('posted_status', function ($row) {
                return $row->POSTED == 1 ? '<span class="badge badge-success">Posted</span>' : '<span class="badge badge-warning">Unposted</span>';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '';
                if ($row->POSTED == 0) {
                    $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                } else {
                    $btnEdit = '<button class="btn btn-sm btn-secondary" disabled title="Sudah Posting"><i class="fas fa-lock"></i></button>';
                }
                $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                return $btnEdit . ' ' . $btnPrint;
            })
            ->rawColumns(['action', 'posted_status'])
            ->make(true);
    }

    public function checkPosted(Request $request)
    {
        $no_bukti = $request->get('no_bukti');

        $data = DB::select(
            "SELECT posted FROM stockb WHERE no_bukti = ?",
            [$no_bukti]
        );

        if (!empty($data)) {
            $posted = $data[0]->posted ?? 0;
            return response()->json([
                'success' => true,
                'posted' => $posted,
                'message' => $posted == 1 ? 'Transaksi sudah di Posting !!' : 'Transaksi belum di Posting'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan'
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'flag' => 'required',
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
            $flag = $request->flag;

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

            if ($no_bukti == '+' || empty($no_bukti)) {
                $no_bukti = $this->generateNoBukti($periode, $cbg, $flag);
            }

            $check = DB::select("SELECT no_bukti FROM stockb WHERE no_bukti = ?", [$no_bukti]);

            $total_qty = $details->sum(function ($detail) {
                return floatval($detail['qty'] ?? 0);
            });

            if (empty($check)) {
                DB::statement(
                    "INSERT INTO stockb (no_bukti, tgl, per, cbg, flag, type, bktk, total_qty, notes, posted, usrnm, tg_input)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())",
                    [
                        $no_bukti,
                        $request->tgl,
                        $periode,
                        $cbg,
                        $flag,
                        $request->type ?? '',
                        $request->bktk ?? '',
                        $total_qty,
                        $request->notes ?? '',
                        $username
                    ]
                );
            } else {
                DB::statement(
                    "UPDATE stockb SET tgl = ?, total_qty = ?, notes = ?, type = ?, bktk = ?, usrnm = ?, tg_input = NOW()
                     WHERE no_bukti = ?",
                    [
                        $request->tgl,
                        $total_qty,
                        $request->notes ?? '',
                        $request->type ?? '',
                        $request->bktk ?? '',
                        $username,
                        $no_bukti
                    ]
                );
            }

            DB::statement("DELETE FROM stockbd WHERE no_bukti = ?", [$no_bukti]);

            $rec = 1;
            foreach ($details as $detail) {
                if (!empty($detail['kd_brg'])) {
                    DB::statement(
                        "INSERT INTO stockbd (no_bukti, rec, kd_brg, na_brg, qty, ket)
                         VALUES (?, ?, ?, ?, ?, ?)",
                        [
                            $no_bukti,
                            $rec,
                            trim($detail['kd_brg']),
                            trim($detail['na_brg'] ?? ''),
                            floatval($detail['qty'] ?? 0),
                            trim($detail['ket'] ?? '')
                        ]
                    );
                    $rec++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
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
        $flag = $request->get('flag', 'HZ');

        if ($flag == 'HZ') {
            $query = "SELECT kd_brgh AS kd_brg, na_brgh AS na_brg, gak AS saldo
                      FROM brgh
                      WHERE gak <> 0";

            if (!empty($q)) {
                $query .= " AND (kd_brgh LIKE ? OR na_brgh LIKE ?)";
                $data = DB::select($query . " ORDER BY kd_brgh LIMIT 50", ["%$q%", "%$q%"]);
            } else {
                $data = DB::select($query . " ORDER BY kd_brgh LIMIT 50");
            }
        } else {
            $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];
            $bulan = $per['bulan'];

            $query = "SELECT kd_brgh AS kd_brg, na_brgh AS na_brg, ak{$bulan} AS saldo
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
        $kd_brg = $request->get('kd_brg');
        $cbg = session('cbg', '01');
        $flag = $request->get('flag', 'HZ');

        if ($flag == 'HZ') {
            $product = DB::select(
                "SELECT kd_brgh AS kd_brg, na_brgh AS na_brg, gak AS saldo
                 FROM brgh
                 WHERE kd_brgh = ?",
                [$kd_brg]
            );
        } else {
            $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];
            $bulan = $per['bulan'];

            $product = DB::select(
                "SELECT kd_brgh AS kd_brg, na_brgh AS na_brg, ak{$bulan} AS saldo
                 FROM brghd
                 WHERE cbg = ? AND kd_brgh = ?",
                [$cbg, $kd_brg]
            );
        }

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

    public function printStokOpnameKoreksiHadiah(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $cbg = session('cbg', '01');

        $toko = DB::select("SELECT na_toko FROM toko WHERE kode = ?", [$cbg]);
        $na_toko = $toko[0]->na_toko ?? '';

        $header = DB::select(
            "SELECT * FROM stockb WHERE no_bukti = ?",
            [$no_bukti]
        );

        $detail = DB::select(
            "SELECT * FROM stockbd WHERE no_bukti = ? ORDER BY rec",
            [$no_bukti]
        );

        return response()->json([
            'success' => true,
            'header' => $header[0] ?? null,
            'detail' => $detail,
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
            throw new \Exception('Bulan tidak sesuai dengan Periode.');
        }

        if ($yearz != $periode_year) {
            throw new \Exception('Tahun tidak sesuai dengan Periode.');
        }
    }

    private function generateNoBukti($periode, $cbg, $flag)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = $toko[0]->type ?? '';

        $prefix = $flag == 'HZ' ? 'HZ' : 'HS';
        $kode = $prefix . substr($year, -2) . $monthString;

        $trans_name = $flag == 'HZ' ? 'SOHADIAH' : 'SOHADIAHTOKO';

        $notrans = DB::select(
            "SELECT NOM{$monthString} AS no_bukti FROM notrans WHERE trans = ? AND per = ?",
            [$trans_name, $year]
        );
        $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

        DB::statement(
            "UPDATE notrans SET NOM{$monthString} = ? WHERE trans = ? AND per = ?",
            [$r1, $trans_name, $year]
        );

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }
}
