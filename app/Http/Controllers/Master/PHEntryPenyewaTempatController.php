<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;class PHEntryPenyewaTempatController extends Controller
{
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

        return view('promo_hadiah_entry_penyewa_tempat.index');
    }

    public function create()
    {
        $periode = session('periode', date('m.Y'));

        try {
            $areal = DB::select("SELECT KODE, NAMA_TOKO FROM toko WHERE SEWA_AREAL <> ''");
        } catch (\Illuminate\Database\QueryException $e) {
            $areal = [];
        }

        try {
            $sarana = DB::select("SELECT KODE, SARANA FROM accountsewa");
        } catch (\Illuminate\Database\QueryException $e) {
            $sarana = [];
        }

        $cbg = session('cbg', '01');
        try {
            $rekening = DB::select("SELECT NO_REK, NAMA_REK, CABANG_REK FROM toko_bank WHERE OUTLET = ?", [$cbg]);
        } catch (\Illuminate\Database\QueryException $e) {
            $rekening = [];
        }

        try {
            $ppn = DB::select("CALL xppn()");
        } catch (\Illuminate\Database\QueryException $e) {
            $ppn = [];
        }
        $ppn_label = 'PPN ' . ($ppn[0]->PN ?? 11) . '%';

        $data = [
            'no_bukti' => '+',
            'status' => 'simpan',
            'header' => null,
            'periode' => $periode,
            'areal' => $areal,
            'sarana' => $sarana,
            'rekening' => $rekening,
            'ppn_label' => $ppn_label
        ];

        return view('promo_hadiah_entry_penyewa_tempat.form', $data);
    }

    private function edit($no_bukti)
    {
        $periode = session('periode', date('m.Y'));

        $header = DB::select("SELECT * FROM kontrak WHERE NO_BUKTI = ?", [$no_bukti]);

        if (empty($header)) {
            abort(404, 'Data tidak ditemukan');
        }

        $kontrak = $header[0];

        $supplier = DB::select(
            "SELECT KTP, NAMAS, Al_prsh, Al_prsh2, KOTA, NO_TELP, S_PJK, NPWP,
             CARA_BYR, CARA_BYR2, KET, EMAIL, KD_DISTRIBUTOR
             FROM supstand WHERE KODES = ?",
            [$kontrak->KODES]
        );

        $distributor = [];
        if (!empty($supplier) && $supplier[0]->KD_DISTRIBUTOR) {
            $distributor = DB::select(
                "SELECT KODES, NM_NPWP FROM sup WHERE kodes = ? LIMIT 1",
                [$supplier[0]->KD_DISTRIBUTOR]
            );
        }

        $areal = DB::select("SELECT KODE, NAMA_TOKO FROM toko WHERE SEWA_AREAL <> ''");

        $sarana = DB::select("SELECT KODE, SARANA FROM accountsewa");

        $rekening = DB::select(
            "SELECT NO_REK, NAMA_REK, CABANG_REK FROM toko_bank WHERE OUTLET = ?",
            [$kontrak->AREAL]
        );

        $rekening_info = DB::select(
            "SELECT NO_REK, NAMA_REK, CABANG_REK FROM toko_bank
             WHERE OUTLET = ? AND NO_REK = ?",
            [$kontrak->AREAL, $kontrak->NO_REK]
        );

        $ppn = DB::select("CALL xppn()");
        $ppn_label = 'PPN ' . ($ppn[0]->PN ?? 11) . '%';

        $data = [
            'no_bukti' => $no_bukti,
            'status' => 'edit',
            'header' => $kontrak,
            'supplier' => !empty($supplier) ? $supplier[0] : null,
            'distributor' => !empty($distributor) ? $distributor[0] : null,
            'periode' => $periode,
            'areal' => $areal,
            'sarana' => $sarana,
            'rekening' => $rekening,
            'rekening_info' => !empty($rekening_info) ? $rekening_info[0] : null,
            'ppn_label' => $ppn_label
        ];

        return view('promo_hadiah_entry_penyewa_tempat.form', $data);
    }

    public function getData(Request $request)
    {
        $query = DB::select("SELECT * FROM kontrak ORDER BY NO_BUKTI");

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TG_MULAI', function ($row) {
                return $row->TG_MULAI ? date('d/m/Y', strtotime($row->TG_MULAI)) : '';
            })
            ->editColumn('TG_SELESAI', function ($row) {
                return $row->TG_SELESAI ? date('d/m/Y', strtotime($row->TG_SELESAI)) : '';
            })
            ->editColumn('TARIF', function ($row) {
                return number_format($row->TARIF, 0, ',', '.');
            })
            ->editColumn('BAYAR', function ($row) {
                return number_format($row->BAYAR, 0, ',', '.');
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                $btnDelete = '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\', \'' . $row->AREAL . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>';
                $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                $btnExport = '<button onclick="exportData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-success ml-1" title="Export"><i class="fas fa-file-export"></i></button>';
                return $btnEdit . ' ' . $btnDelete . ' ' . $btnPrint . ' ' . $btnExport;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveConfig(Request $request)
    {
        $this->validate($request, [
            'no_bukti' => 'required',
            'kodes' => 'required',
            'kd_sarana' => 'required',
            'areal' => 'required',
            'no_rek' => 'required',
            'tg_mulai' => 'required|date',
            'tg_selesai' => 'required|date'
        ]);

        DB::beginTransaction();

        try {
            $status = $request->status;
            $periode = session('periode', date('m.Y'));
            $username = Auth::user()->username ?? 'system';
            $cbg = session('cbg', '01');

            $tg_mulai = Carbon::parse($request->tg_mulai);
            $tg_selesai = Carbon::parse($request->tg_selesai);
            $masa = $request->masa ? Carbon::parse($request->masa) : null;

            if ($tg_mulai->format('Ymd') > $tg_selesai->format('Ymd')) {
                throw new \Exception('Periode mulai salah!');
            }

            if ($status == 'simpan') {
                $no_bukti = trim($request->no_bukti);

                $check = DB::select("SELECT NO_BUKTI FROM kontrak WHERE NO_BUKTI = ?", [$no_bukti]);
                if (!empty($check)) {
                    throw new \Exception('No. Penyewa sudah terpakai (' . $request->areal . ')');
                }

                $cabang = DB::select("SELECT KODE FROM toko WHERE SEWA_AREAL <> ''");

                foreach ($cabang as $cab) {
                    $prosesToko = trim($cab->KODE);

                    DB::statement(
                        "INSERT INTO {$prosesToko}.kontrak
                        (NO_BUKTI, PER, KODES, NM_MOHON, JAB, EMAIL, AREAL, NO_REK, CATATAN, KD_SARANA,
                         LOKASI, LUAS, TG_MULAI, TG_SELESAI, MEREK, KEGIATAN, DAYA, TARIF,
                         PPN, PPH, BAYAR, DEPOSIT, DP1, DP2, MASA, JUM_KWI,
                         usrnm, tg_smp, tanggung, jns_produk)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)",
                        [
                            $no_bukti,
                            $periode,
                            trim($request->kodes),
                            trim($request->nm_mohon ?? ''),
                            trim($request->jab ?? ''),
                            trim($request->email ?? ''),
                            trim($request->areal),
                            trim($request->no_rek),
                            trim($request->catatan ?? ''),
                            trim($request->kd_sarana),
                            trim($request->lokasi ?? ''),
                            trim($request->luas ?? ''),
                            $tg_mulai->format('Y-m-d'),
                            $tg_selesai->format('Y-m-d'),
                            trim($request->merek ?? ''),
                            trim($request->kegiatan ?? ''),
                            trim($request->daya ?? ''),
                            $request->tarif ?? 0,
                            $request->ppn ?? 0,
                            $request->pph ?? 0,
                            $request->bayar ?? 0,
                            $request->deposit ?? 0,
                            $request->dp1 ?? 0,
                            $request->dp2 ?? 0,
                            $masa ? $masa->format('Y-m-d') : null,
                            $request->jum_kwi ?? 0,
                            $username,
                            trim($request->tanggung ?? ''),
                            trim($request->jns_produk ?? '')
                        ]
                    );
                }
            } else {
                $no_bukti = trim($request->no_bukti);

                $cabang = DB::select("SELECT KODE FROM toko WHERE SEWA_AREAL <> ''");

                foreach ($cabang as $cab) {
                    $prosesToko = trim($cab->KODE);

                    DB::statement(
                        "UPDATE {$prosesToko}.kontrak SET
                        KODES = ?, NM_MOHON = ?, JAB = ?, EMAIL = ?,
                        NO_REK = ?, CATATAN = ?, KD_SARANA = ?, LOKASI = ?,
                        LUAS = ?, TG_MULAI = ?, TG_SELESAI = ?, MEREK = ?,
                        KEGIATAN = ?, DAYA = ?, TARIF = ?, PPN = ?, PPH = ?,
                        BAYAR = ?, DEPOSIT = ?, DP1 = ?, DP2 = ?, MASA = ?,
                        JUM_KWI = ?, USRNM = ?, TG_SMP = NOW(), TANGGUNG = ?,
                        jns_produk = ?
                        WHERE NO_BUKTI = ?",
                        [
                            trim($request->kodes),
                            trim($request->nm_mohon ?? ''),
                            trim($request->jab ?? ''),
                            trim($request->email ?? ''),
                            trim($request->no_rek),
                            trim($request->catatan ?? ''),
                            trim($request->kd_sarana),
                            trim($request->lokasi ?? ''),
                            trim($request->luas ?? ''),
                            $tg_mulai->format('Y-m-d'),
                            $tg_selesai->format('Y-m-d'),
                            trim($request->merek ?? ''),
                            trim($request->kegiatan ?? ''),
                            trim($request->daya ?? ''),
                            $request->tarif ?? 0,
                            $request->ppn ?? 0,
                            $request->pph ?? 0,
                            $request->bayar ?? 0,
                            $request->deposit ?? 0,
                            $request->dp1 ?? 0,
                            $request->dp2 ?? 0,
                            $masa ? $masa->format('Y-m-d') : null,
                            $request->jum_kwi ?? 0,
                            $username,
                            trim($request->tanggung ?? ''),
                            trim($request->jns_produk ?? ''),
                            $no_bukti
                        ]
                    );
                }
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
        $kodes = $request->get('kodes');

        if (empty($kodes)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode supplier tidak boleh kosong'
            ]);
        }

        $supplier = DB::select(
            "SELECT KTP, NAMAS, Al_prsh, Al_prsh2, KOTA, NO_TELP, S_PJK, NPWP,
             CARA_BYR, CARA_BYR2, KET, EMAIL, KD_DISTRIBUTOR
             FROM supstand WHERE KODES = ?",
            [$kodes]
        );

        if (empty($supplier)) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier tidak ditemukan'
            ]);
        }

        $distributor = null;
        if ($supplier[0]->KD_DISTRIBUTOR) {
            $dist = DB::select(
                "SELECT KODES, NM_NPWP FROM sup WHERE kodes = ? LIMIT 1",
                [$supplier[0]->KD_DISTRIBUTOR]
            );
            $distributor = !empty($dist) ? $dist[0] : null;
        }

        return response()->json([
            'success' => true,
            'data' => $supplier[0],
            'distributor' => $distributor
        ]);
    }

    public function checkCustomer(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $areal = $request->get('areal');

        if (empty($no_bukti)) {
            return response()->json([
                'success' => false,
                'message' => 'No Bukti tidak boleh kosong'
            ]);
        }

        if (empty($areal)) {
            return response()->json([
                'success' => false,
                'message' => 'Areal tidak boleh kosong'
            ]);
        }

        $check = DB::select(
            "SELECT COUNT(*) as CEK FROM {$areal}.bayarkontrak WHERE NO_KONTRAK = ?",
            [$no_bukti]
        );

        if ($check[0]->CEK > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Kontrak tidak bisa dihapus karena sudah dibuatkan kwitansi.'
            ]);
        }

        $cabang = DB::select("SELECT KODE FROM toko WHERE SEWA_AREAL <> ''");

        foreach ($cabang as $cab) {
            $prosesToko = trim($cab->KODE);
            DB::statement("DELETE FROM {$prosesToko}.kontrak WHERE NO_BUKTI = ?", [$no_bukti]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function getRekening(Request $request)
    {
        $areal = $request->get('areal');

        if (empty($areal)) {
            return response()->json([
                'success' => false,
                'message' => 'Areal tidak boleh kosong'
            ]);
        }

        $rekening = DB::select(
            "SELECT NO_REK, NAMA_REK, CABANG_REK FROM toko_bank WHERE OUTLET = ?",
            [$areal]
        );

        $prior = DB::select(
            "SELECT NO_REK, NAMA_REK, CABANG_REK FROM toko_bank WHERE OUTLET = ? AND PRIOR = 1",
            [$areal]
        );

        return response()->json([
            'success' => true,
            'rekening' => $rekening,
            'prior' => !empty($prior) ? $prior[0] : null
        ]);
    }

    public function printKontrak(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $tipe = $request->get('tipe');
        if (empty($no_bukti)) {
            return response()->json([
                'success' => false,
                'message' => 'No Bukti tidak boleh kosong'
            ]);
        }

        $data = DB::select(
            "SELECT k.NO_BUKTI, st.S_PJK, st.NAMAS, k.EMAIL, st.KTP,
             CONCAT(k.KD_SARANA, '.', k.LOKASI) as LOKASI, k.MEREK, k.LUAS,
             k.TG_MULAI, k.TG_SELESAI, k.TARIF, k.PPN, k.PPH, k.BAYAR, k.DEPOSIT, k.DP1, k.DP2,
             k.JUM_KWI, k.AREAL, k.JNS_PRODUK, CONCAT('PPN ', xx_ppn(1), '%') as KET_PPN
             FROM kontrak k, supstand st
             WHERE k.KODES = st.KODES AND k.no_bukti = ?",
            [$no_bukti]
        );

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
        if($tipe == 'persetujuan'){
            $file = 'persetujuanSewa';
        }else{

            $file = 'tandaTerimaSewa';
        }
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));
        $PHPJasperXML->setData(array_map(function ($row) {
            $row->TGL = now()->format('d/m/Y');
            $row->TG_MULAI = Carbon::parse($row->TG_MULAI)->format('d/m/Y');
            $row->TG_SELESAI = Carbon::parse($row->TG_SELESAI)->format('d/m/Y');
            return (array)$row;
        }, $data));
        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function exportData(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $db = session('cbg', '01');

        if (empty($no_bukti)) {
            return response()->json([
                'success' => false,
                'message' => 'No Bukti tidak boleh kosong'
            ]);
        }

        try {
            $url = "http://10.10.30.132:8080/export-dbf-app/public/export-kontrak?db={$db}&bkt={$no_bukti}";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 500 || $response == '500') {
                throw new \Exception('File Gagal Di Export.');
            }

            return response()->json([
                'success' => true,
                'message' => 'Export berhasil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
