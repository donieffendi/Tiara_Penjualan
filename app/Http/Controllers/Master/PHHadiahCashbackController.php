<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;
class PHHadiahCashbackController extends Controller
{
    public function print(){
        $result = DB::select(
            "SELECT * FROM promo ORDER BY no_id DESC"
        );
        $file = 'hadiah_cashback';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        $PHPJasperXML->setData(array_map(function ($row) {
            $row->TGL = now()->format('d/m/Y');
            return (array)$row;
        }, $result));
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }
    public function index(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status');

        if ($status == 'simpan') {
            return $this->create();
        }

        if ($no_bukti && $status == 'edit') {
            return $this->edit($no_bukti);
        }

        if ($request->ajax()) {
            return $this->getData($request);
        }

        return view('promo_hadiah_cashback.index');
    }

    public function create()
    {
        $data = [
            'no_bukti' => '+',
            'status' => 'simpan',
            'header' => null,
            'periode' => session('periode', date('m.Y'))
        ];

        return view('promo_hadiah_cashback.form', $data);
    }

    private function edit($kd_prm)
    {
        $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];

        $header = DB::select("SELECT * FROM promo WHERE kd_prm = ? ORDER BY kd_prm", [$kd_prm]);

        if (empty($header)) {
            abort(404, 'Data tidak ditemukan');
        }

        $data = [
            'kd_prm' => $kd_prm,
            'status' => 'edit',
            'header' => $header[0],
            'periode' => $periode
        ];

        return view('promo_hadiah_cashback.form', $data);
    }

    public function getData(Request $request)
    {
        $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];

        $query = DB::select(
            "SELECT * FROM promo WHERE per = ? OR tg_akhir >= DATE(NOW()) ORDER BY no_id DESC",
            [$periode]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TGL', function ($row) {
                return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
            })
            ->editColumn('TG_MULAI', function ($row) {
                return $row->TG_MULAI ? date('d/m/Y', strtotime($row->TG_MULAI)) : '';
            })
            ->editColumn('TG_AKHIR', function ($row) {
                return $row->TG_AKHIR ? date('d/m/Y', strtotime($row->TG_AKHIR)) : '';
            })
            ->addColumn('NO_BUKTI', function ($row) {
                return $row->NO_BUKTI;
            })
            ->addColumn('KD_PRM', function ($row) {
                return $row->KD_PRM;
            })
            ->addColumn('JNS', function ($row) {
                return $row->JNS;
            })
            ->addColumn('JNS_DIS', function ($row) {
                return $row->JNS_DIS ?? '-';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button onclick="editData(\'' . $row->KD_PRM . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                $btnDelete = '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>';
                return $btnEdit . ' ' . $btnDelete;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveConfig(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'kd_prm' => 'required',
            'tg_mulai' => 'required|date',
            'tg_akhir' => 'required|date',
            'jm_mulai' => 'required',
            'jm_akhir' => 'required',
            'jns' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $status = $request->status;
            $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];
            $username = Auth::user()->username ?? 'system';
            $cbg = session('cbg', '01');
            $tgl = Carbon::parse($request->tgl);

            $this->validateDatePeriode($tgl, $periode);
            $this->validateJamSelesai($request->jm_mulai, $request->jm_akhir);
            $this->validateTanggalSelesai($request->tg_mulai, $request->tg_akhir);

            if ($status == 'simpan') {
                $this->checkKodePromosiExists($request->kd_prm);

                $no_bukti = $request->no_bukti;

                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                }

                DB::statement(
                    "INSERT INTO promo
                    (maxh, no_bukti, kd_prm, TGL, rp_beli, rp_disc_max, disc, jns, kasir, jns_dis,
                    tg_mulai, tg_akhir, jm_mulai, jm_akhir, tg_dis_akhir, brg, brg_disc, USRNM, PER, NKARTU, TG_SMP)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $request->maxh ?? 0,
                        $no_bukti,
                        trim($request->kd_prm),
                        $tgl->format('Y-m-d'),
                        $request->rp_beli ?? 0,
                        $request->rp_disc_max ?? 0,
                        $request->disc ?? 0,
                        trim($request->jns),
                        'SPM',
                        trim($request->jns_dis ?? ''),
                        Carbon::parse($request->tg_mulai)->format('Y-m-d'),
                        Carbon::parse($request->tg_akhir)->format('Y-m-d'),
                        $request->jm_mulai,
                        $request->jm_akhir,
                        Carbon::parse($request->tg_dis_akhir)->format('Y-m-d'),
                        trim($request->brg ?? ''),
                        trim($request->brg_disc ?? ''),
                        $username,
                        $periode,
                        trim($request->nkartu ?? '')
                    ]
                );

                $this->syncToCabang($no_bukti, $cbg);
            } else {
                DB::statement(
                    "UPDATE promo SET
                    jns_dis = ?, kd_prm = ?, TGL = ?, rp_beli = ?, rp_disc_max = ?, disc = ?, jns = ?, kasir = ?,
                    tg_mulai = ?, tg_akhir = ?, jm_mulai = ?, jm_akhir = ?, tg_dis_akhir = ?,
                    maxh = ?, brg = ?, brg_disc = ?, USRNM = ?, PER = ?, NKARTU = ?, TG_SMP = NOW()
                    WHERE no_bukti = ?",
                    [
                        trim($request->jns_dis ?? ''),
                        trim($request->kd_prm),
                        $tgl->format('Y-m-d'),
                        $request->rp_beli ?? 0,
                        $request->rp_disc_max ?? 0,
                        $request->disc ?? 0,
                        trim($request->jns),
                        'SPM',
                        Carbon::parse($request->tg_mulai)->format('Y-m-d'),
                        Carbon::parse($request->tg_akhir)->format('Y-m-d'),
                        $request->jm_mulai,
                        $request->jm_akhir,
                        Carbon::parse($request->tg_dis_akhir)->format('Y-m-d'),
                        $request->maxh ?? 0,
                        trim($request->brg ?? ''),
                        trim($request->brg_disc ?? ''),
                        $username,
                        $periode,
                        trim($request->nkartu ?? ''),
                        $request->no_bukti
                    ]
                );

                $this->syncToCabang($request->no_bukti, $cbg);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getConfig(Request $request)
    {
        $kd_brg = $request->get('kd_brg');
        $jns = $request->get('jns');
        $type = $request->get('type', 1);

        if ($jns == 'ITEM') {
            $product = DB::select(
                "SELECT kd_brg, na_brg FROM masks WHERE kd_brg = ?",
                [$kd_brg]
            );
        } else {
            $product = DB::select(
                "SELECT sub AS kd_brg, kelompok AS na_brg FROM aotprice WHERE sub = ?",
                [$kd_brg]
            );
        }

        if (empty($product)) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak Ditemukan'
            ]);
        }

        $column = $type == 1 ? 'BRG' : 'BRG_disc';

        $existing = DB::select(
            "SELECT KD_PRM FROM promo
            WHERE FIND_IN_SET(?, {$column}) > 0
            AND DATE(NOW()) BETWEEN TG_MULAI AND TG_AKHIR",
            [$kd_brg]
        );

        return response()->json([
            'success' => true,
            'data' => $product[0],
            'exists_in_promo' => !empty($existing),
            'kd_prm' => !empty($existing) ? $existing[0]->KD_PRM : null
        ]);
    }

    public function checkCustomer(Request $request)
    {
        $no_bukti = $request->get('no_bukti');

        if (empty($no_bukti)) {
            return response()->json([
                'success' => false,
                'message' => 'No Bukti tidak boleh kosong'
            ]);
        }

        $result = DB::select(
            "SELECT no_bukti FROM promo WHERE no_bukti = ?",
            [$no_bukti]
        );

        if (!empty($result)) {
            DB::statement("DELETE FROM promo WHERE no_bukti = ?", [$no_bukti]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan'
        ]);
    }

    private function checkKodePromosiExists($kd_prm)
    {
        $exists = DB::select("SELECT kd_prm FROM promo WHERE kd_prm = ?", [$kd_prm]);

        if (!empty($exists)) {
            throw new \Exception('Kode Promosi telah Di pakai');
        }
    }

    private function validateDatePeriode($tgl, $periode)
    {
        $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
        $periode_month = substr($periode, 0, 2);

        if ($monthz != $periode_month) {
            throw new \Exception('Periode tidak sama dengan tanggal!');
        }
    }

    private function validateJamSelesai($jam_mulai, $jam_selesai)
    {
        if ($jam_mulai == '00:00:00') {
            throw new \Exception('Filter Jam Mulai, tidak boleh kosong!!');
        }

        if ($jam_selesai == '00:00:00') {
            throw new \Exception('Filter Jam Selesai, tidak boleh kosong!!');
        }
    }

    private function validateTanggalSelesai($tg_mulai, $tg_akhir)
    {
        if (Carbon::parse($tg_akhir)->lt(Carbon::parse($tg_mulai))) {
            throw new \Exception('Tanggal Selesai Harus Lebih Tinggi Tanggal Mulai!!');
        }
    }

    private function syncToCabang($no_bukti, $cbg)
    {
        $cabang = DB::select(
            "SELECT TRIM(KODE) AS cbg FROM toko WHERE kode <> ? AND type2 <> ''",
            [$cbg]
        );

        foreach ($cabang as $cab) {
            $cabang_kode = $cab->cbg;

            DB::statement("DELETE FROM {$cabang_kode}.promo WHERE NO_BUKTI = ?", [$no_bukti]);

            DB::statement(
                "INSERT INTO {$cabang_kode}.promo
                (maxh, no_bukti, kd_prm, TGL, rp_beli, rp_disc_max, disc, jns, kasir, jns_dis,
                tg_mulai, tg_akhir, jm_mulai, jm_akhir, tg_dis_akhir, brg, brg_disc, USRNM, PER, NKARTU, TG_SMP)
                SELECT maxh, no_bukti, kd_prm, TGL, rp_beli, rp_disc_max, disc, jns, kasir, jns_dis,
                tg_mulai, tg_akhir, jm_mulai, jm_akhir, tg_dis_akhir, brg, brg_disc, USRNM, PER, NKARTU, TG_SMP
                FROM promo WHERE NO_BUKTI = ?",
                [$no_bukti]
            );
        }
    }

    private function generateNoBukti($periode, $cbg)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = $toko[0]->type ?? '';

        $kode = 'LH' . substr($year, -2) . $monthString;

        $notrans = DB::select(
            "SELECT NOM{$monthString} AS no_bukti FROM notrans WHERE trans = 'HIJAU' AND per = ?",
            [$year]
        );
        $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

        DB::statement(
            "UPDATE notrans SET NOM{$monthString} = ? WHERE trans = 'HIJAU' AND per = ?",
            [$r1, $year]
        );

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }
}
