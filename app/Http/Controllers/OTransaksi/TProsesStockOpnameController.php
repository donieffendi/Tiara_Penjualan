<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPJasperXML;
use Yajra\DataTables\Facades\DataTables;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

class TProsesStockOpnameController extends Controller
{
    private function getValidCbg()
    {
        $cbg = session('flag');
        if (empty($cbg)) {
            $cbg = Auth::user()->CBG ?? 'TGZ';
        }
        if (! in_array($cbg, ['TGZ', 'TMM', 'SOP'])) {
            $cbg = 'TGZ';
        }
        return $cbg;
    }

    private function getCbgMaster()
    {
        $result = DB::select("SELECT kode FROM toko WHERE STA='MA' LIMIT 1");
        return $result[0]->kode ?? 'TGZ';
    }

    public function index()
    {
        $periode = session('periode', date('m.Y'));
        if (is_array($periode)) {
            $periode = $periode['bulan'] . '.' . $periode['tahun'];
        }
        $cbg = $this->getValidCbg();

        $cbgmaster = $this->getCbgMaster();
        DB::statement("CALL {$cbgmaster}.pjl_expimp_so('TABEL_SOPJL_TEXT', ?, '', '')", [$cbg]);

        return view('otranskasi_proses_stok_opname.index', compact('periode', 'cbg'));
    }

    public function getProsesStockOpname(Request $request, $tab)
    {
        try {
            $periode = session('periode', date('m.Y'));
            if (is_array($periode)) {
                $periode = $periode['bulan'] . '/' . $periode['tahun'];
            }
            $cbg = $this->getValidCbg();

            if ($tab === 'SO1') {
                $query = DB::select(
                    "SELECT NO_BUKTI, TGL, SUB, USRNM, POSTED
                     FROM lapbh
                     WHERE flag='SO' AND cbg=?
                     ORDER BY NO_BUKTI DESC",
                    [$cbg]
                );
            } elseif ($tab === 'SO2') {
                $query = DB::select(
                    "SELECT *, CONCAT(LEFT(nolap,2), RIGHT(nolap,5)) as bukti
                     FROM stockb
                     WHERE per = ? AND flag = 'AO'
                     ORDER BY no_bukti",
                    [$periode]
                );
            }

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
                })
                ->editColumn('POSTED', function ($row) {
                    return $row->POSTED == 1
                        ? '<span class="badge badge-success">Posted</span>'
                        : '<span class="badge badge-warning">Open</span>';
                })
                ->addColumn('action', function ($row) {
                    $btnEdit = $row->POSTED == 0
                        ? '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>'
                        : '<button class="btn btn-sm btn-secondary" disabled title="Sudah Posted"><i class="fas fa-lock"></i></button>';
                    $btnDelete = $row->POSTED == 0
                        ? '<button onclick="deleteData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fas fa-trash"></i></button>'
                        : '';
                    return $btnEdit . '' . $btnDelete;
                })
                ->rawColumns(['action', 'POSTED'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('TProsesStockOpname getProsesStockOpname error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit(Request $request)
    {
        try {
            $no_bukti = $request->get('no_bukti', '+');
            $status   = $request->get('status', 'simpan');
            $periode  = session('periode', date('m.Y'));

            if (is_array($periode)) {
                $periode = $periode['bulan'] . '.' . $periode['tahun'];
            }
            $cbg = $this->getValidCbg();

            $periodeCheck = DB::select("SELECT posted FROM perid WHERE kd_peri=?", [$periode]);
            if (! empty($periodeCheck) && $periodeCheck[0]->posted == 1) {
                return view('otranskasi_proses_stok_opname.edit', [
                    'error'    => 'Closed Period',
                    'periode'  => $periode,
                    'cbg'      => $cbg,
                    'status'   => $status,
                    'no_bukti' => '+',
                    'header'   => (object) ['no_bukti' => '+', 'tgl' => date('Y-m-d'), 'sub' => '', 'notes' => ''],
                    'detail'   => [],
                ]);
            }

            $data = [
                'no_bukti' => '+',
                'status'   => $status,
                'header'   => (object) ['no_bukti' => '+', 'tgl' => date('Y-m-d'), 'sub' => '', 'notes' => ''],
                'detail'   => [],
                'periode'  => $periode,
                'cbg'      => $cbg,
                'error'    => null,
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                $header = DB::select(
                    "SELECT no_bukti, tgl, sub, posted FROM lapbh WHERE no_bukti=? AND flag='SO'",
                    [$no_bukti]
                );

                if (! empty($header)) {
                    $headerData = $header[0];
                    if ($headerData->posted == 1) {
                        return view('otranskasi_proses_stok_opname.edit', [
                            'error'    => 'Transaksi sudah di Posting !!',
                            'periode'  => $periode,
                            'cbg'      => $cbg,
                            'status'   => $status,
                            'no_bukti' => $no_bukti,
                            'header'   => $headerData,
                            'detail'   => [],
                        ]);
                    }

                    $detail = DB::select(
                        "SELECT lapbhd.*, brg.barcode
                         FROM lapbhd
                         LEFT JOIN brg ON lapbhd.kd_brg = brg.kd_brg
                         WHERE lapbhd.no_bukti=? AND lapbhd.flag='SO'
                         ORDER BY lapbhd.rec",
                        [$no_bukti]
                    );

                    $data['header']   = $headerData;
                    $data['detail']   = $detail;
                    $data['no_bukti'] = $no_bukti;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_proses_stok_opname.edit', $data);
        } catch (\Exception $e) {
            return view('otranskasi_proses_stok_opname.edit', [
                'no_bukti' => '+',
                'status'   => 'simpan',
                'header'   => (object) ['no_bukti' => '+', 'tgl' => date('Y-m-d'), 'sub' => '', 'notes' => ''],
                'detail'   => [],
                'periode'  => session('periode', date('m.Y')),
                'cbg'      => $this->getValidCbg(),
                'error'    => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $no_bukti = trim($request->no_bukti);
            $status   = $request->status;
            $periode  = session('periode', date('m.Y'));

            if (is_array($periode)) {
                $periode = $periode['bulan'] . '.' . $periode['tahun'];
            }

            $cbg      = $this->getValidCbg();
            $username = Auth::user()->username ?? 'system';

            $tgl    = Carbon::parse($request->tgl);
            $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
            $yearz  = $tgl->year;

            $periode_month = substr($periode, 0, 2);
            $periode_year  = substr($periode, -4);

            if ($monthz != $periode_month) {
                return response()->json(['success' => false, 'message' => 'Month is not the same as Periode.'], 400);
            }
            if ($yearz != $periode_year) {
                return response()->json(['success' => false, 'message' => 'Year is not the same as Periode.'], 400);
            }

            $details = $request->input('details', $request->input('detail', []));
            if (empty($details) || ! is_array($details)) {
                return response()->json(['success' => false, 'message' => 'Detail barang harus diisi'], 400);
            }

            if ($status == 'simpan' && $no_bukti == '+') {
                $tokoInfo = DB::select("SELECT type FROM toko WHERE kode=?", [$cbg]);
                $kode2 = ! empty($tokoInfo) ? $tokoInfo[0]->type : '';
                $kode = 'SO' . substr($periode, -2) . substr($periode, 0, 2);

                $lastNo = DB::select(
                    "SELECT NOM" . $periode_month . " as no_bukti FROM notrans WHERE trans='SO' AND PER=?",
                    [$periode_year]
                );
                $r1 = ! empty($lastNo) ? intval($lastNo[0]->no_bukti) : 0;
                $r1 = $r1 + 1;

                DB::statement(
                    "UPDATE notrans SET NOM" . $periode_month . "=? WHERE trans='SO' AND PER=?",
                    [$r1, $periode_year]
                );

                $bkt1     = str_pad($r1, 4, '0', STR_PAD_LEFT);
                $no_bukti = $kode . '-' . $bkt1 . $kode2;
            }

            if ($status == 'simpan') {
                DB::statement(
                    "INSERT INTO lapbh (NO_BUKTI, TGL, SUB, USRNM, TG_SMP, CBG, FLAG)
                     VALUES (?, ?, ?, ?, NOW(), ?, 'SO')",
                    [$no_bukti, $request->tgl, trim($request->sub), $username, $cbg]
                );
            } else {
                DB::statement(
                    "UPDATE lapbh SET TGL=?, SUB=?, USRNM=?, TG_SMP=NOW() WHERE NO_BUKTI=?",
                    [$request->tgl, trim($request->sub), $username, $no_bukti]
                );
            }

            $headerId = DB::select("SELECT no_id FROM lapbh WHERE no_bukti=?", [$no_bukti]);
            $id = ! empty($headerId) ? $headerId[0]->no_id : 0;

            if ($status == 'edit') {
                DB::statement("DELETE FROM lapbhd WHERE no_bukti=?", [$no_bukti]);
            }

            $rec = 1;
            foreach ($details as $detail) {
                $kd_brg = is_array($detail) ? ($detail['kd_brg'] ?? '') : ($detail->kd_brg ?? '');
                $cek    = is_array($detail) ? ($detail['cek'] ?? 0) : ($detail->cek ?? 0);

                if (! empty($kd_brg) && $cek == 1) {
                    $na_brg   = is_array($detail) ? ($detail['na_brg'] ?? '') : ($detail->na_brg ?? '');
                    $itemsub  = is_array($detail) ? ($detail['itemsub'] ?? '') : ($detail->itemsub ?? '');
                    $ket_uk   = is_array($detail) ? ($detail['ket_uk'] ?? '') : ($detail->ket_uk ?? '');
                    $ket_kem  = is_array($detail) ? ($detail['ket_kem'] ?? '') : ($detail->ket_kem ?? '');
                    $kd       = is_array($detail) ? ($detail['kd'] ?? '') : ($detail->kd ?? '');
                    $hj       = is_array($detail) ? ($detail['hj'] ?? 0) : ($detail->hj ?? 0);
                    $saldo    = is_array($detail) ? ($detail['saldo'] ?? 0) : ($detail->saldo ?? 0);
                    $lph      = is_array($detail) ? ($detail['lph'] ?? 0) : ($detail->lph ?? 0);

                    DB::statement(
                        "INSERT INTO lapbhd (NO_BUKTI, REC, KD_BRG, ITEMSUB, NA_BRG, KET_UK, KET_KEM, KD, HJ, SALDO, LPH, FLAG, ID)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'SO', ?)",
                        [
                            $no_bukti,
                            $rec,
                            trim($kd_brg),
                            trim($itemsub),
                            trim($na_brg),
                            trim($ket_uk),
                            trim($ket_kem),
                            trim($kd),
                            floatval($hj),
                            floatval($saldo),
                            floatval($lph),
                            $id
                        ]
                    );
                    $rec++;
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Save Data Success', 'no_bukti' => $no_bukti]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function browse(Request $request)
    {
        try {
            $cbg   = $this->getValidCbg();
            $sub   = $request->get('sub', '');
            $item1 = $request->get('item1', '');
            $item2 = $request->get('item2', '');
            $supp  = $request->get('supp', '');
            $tat   = $request->get('tat', null);
            $lph1  = $request->get('lph1', null);
            $lph2  = $request->get('lph2', null);
            $cbkdlaku = trim($request->get('cbkdlaku', 'ALL'));
            $dataRL = $request->get('dataRL', 0);

            $query = null;

            if ($dataRL == 1) {
                $periode = session('periode', date('m.Y'));
                if (is_array($periode)) {
                    $bul = str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT);
                    $tah = $periode['tahun'];
                } else {
                    list($bul, $tah) = explode('.', $periode);
                }

                $bullalu = intval($bul) - 1;
                $tahlalu = intval($tah);
                if ($bullalu == 0) {
                    $bullalu = 12;
                    $tahlalu = $tahlalu - 1;
                }
                $perrl = str_pad($bullalu, 2, '0', STR_PAD_LEFT) . '/' . $tahlalu;

                $sql = "SELECT a.KD_BRG, a.NA_BRG, a.KET_KEM, a.KET_UK,
                        CONCAT(RIGHT(a.kd_brg,4), '-', LEFT(a.kd_brg,3)) as itemsub,
                        CONCAT(b.KDLAKU, b.KLK) as kd, b.HJ, b.AK00 as saldo, b.lph
                        FROM sorl a, brgdt b, brg c
                        WHERE a.KD_BRG=b.KD_BRG AND a.kd_brg=c.kd_brg AND b.cbg=?
                        AND b.yer=YEAR(NOW())";

                $params = [$cbg];

                if (!empty($supp)) {
                    $sql .= " AND c.supp=?";
                    $params[] = $supp;
                }

                if ($cbkdlaku !== 'ALL') {
                    if ($cbkdlaku === '3') {
                        $sql .= " AND LEFT(b.na_brg,1)='3'";
                    } else {
                        $sql .= " AND b.kdlaku=?";
                        $params[] = intval($cbkdlaku);
                    }
                }

                if ($tat !== null) {
                    $sql .= " AND DATEDIFF(DATE(NOW()), DATE(b.tgl_at))>=?";
                    $params[] = $tat;
                }

                if ($lph1 !== null && $lph2 !== null) {
                    $sql .= " AND b.lph BETWEEN ? AND ?";
                    $params[] = $lph1;
                    $params[] = $lph2;
                }

                if (!empty($sub)) {
                    $sql .= " AND LEFT(a.kd_brg,3)=?";
                    $params[] = $sub;
                }

                if (!empty($item1)) {
                    $sql .= " AND RIGHT(a.kd_brg,4)>=?";
                    $params[] = $item1;
                }

                if (!empty($item2)) {
                    $sql .= " AND RIGHT(a.kd_brg,4)<=?";
                    $params[] = $item2;
                }

                $sql .= " AND a.per=? AND a.st_rl_akt='R' ORDER BY a.kd_brg";
                $params[] = $perrl;

                $query = DB::select($sql, $params);
            } else {
                $sql = "SELECT brg.KD_BRG, brg.NA_BRG, brg.KET_KEM, brg.KET_UK,
                        CONCAT(brg.kdbar, '-', brg.SUB) as itemsub,
                        CONCAT(brgdt.KDLAKU, brgdt.KLK) as kd, brgdt.HJ,
                        brgdt.AK00 as saldo, brgdt.lph
                        FROM brg, brgdt
                        WHERE brg.KD_BRG=brgdt.KD_BRG AND brgdt.cbg=?
                        AND brgdt.yer=YEAR(NOW())";

                $params = [$cbg];

                if (!empty($supp)) {
                    $sql .= " AND brg.supp=?";
                    $params[] = $supp;
                }

                if ($cbkdlaku !== 'ALL') {
                    if ($cbkdlaku === '3') {
                        $sql .= " AND LEFT(brgdt.na_brg,1)='3'";
                    } else {
                        $sql .= " AND brgdt.kdlaku=?";
                        $params[] = intval($cbkdlaku);
                    }
                }

                if ($tat !== null) {
                    $sql .= " AND DATEDIFF(DATE(NOW()), DATE(brgdt.tgl_at))>=?";
                    $params[] = $tat;
                }

                if ($lph1 !== null && $lph2 !== null) {
                    $sql .= " AND brgdt.lph BETWEEN ? AND ?";
                    $params[] = $lph1;
                    $params[] = $lph2;
                }

                if (!empty($sub)) {
                    $sql .= " AND brg.sub=?";
                    $params[] = $sub;
                }

                if (!empty($item1)) {
                    $sql .= " AND brg.kdbar>=?";
                    $params[] = $item1;
                }

                if (!empty($item2)) {
                    $sql .= " AND brg.kdbar<=?";
                    $params[] = $item2;
                }

                $sql .= " ORDER BY brg.kd_brg";

                $query = DB::select($sql, $params);
            }

            return response()->json($query);
        } catch (\Exception $e) {
            Log::error('TProsesStockOpname browse error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getDetail(Request $request)
    {
        try {
            $kd_brg = $request->get('kd_brg');
            $sub    = $request->get('sub');
            $cbg    = $this->getValidCbg();

            $barang = DB::select(
                "SELECT brg.kd_brg as KD_BRG, TRIM(CONCAT(brg.na_brg, ' ', brg.ket_uk)) as NA_BRG,
                        brg.sub as SUB, brg.kdbar as KDBAR, brg.ket_uk as KET_UK, brg.ket_kem as KET_KEM,
                        CONCAT(brg.kdbar, '-', brg.SUB) as itemsub,
                        CONCAT(brgdt.KDLAKU, brgdt.KLK) as kd,
                        brgdt.hj as HJ, brgdt.hb as HB, brgdt.AK00 as saldo,
                        brg.supp as SUPP, brg.barcode as BARCODE, brgdt.lph as lph
                 FROM brg
                 INNER JOIN brgdt ON brg.kd_brg=brgdt.kd_brg
                 WHERE brgdt.cbg=? AND brgdt.yer=YEAR(NOW())
                 AND (brg.kd_brg=? OR brg.kd_brg=CONCAT(?, ?)) AND brg.sub=?",
                [$cbg, $kd_brg, $sub, $kd_brg, $sub]
            );

            if (!empty($barang)) {
                return response()->json(['success' => true, 'exists' => true, 'data' => $barang[0]]);
            }

            return response()->json(['success' => false, 'exists' => false, 'message' => 'Barang tidak ditemukan']);
        } catch (\Exception $e) {
            Log::error('TProsesStockOpname getDetail error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($no_bukti)
    {
        try {
            DB::beginTransaction();

            $check = DB::select("SELECT posted FROM lapbh WHERE no_bukti=? AND flag='SO'", [$no_bukti]);
            if (empty($check)) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }
            if ($check[0]->posted == 1) {
                return response()->json(['success' => false, 'message' => 'Data sudah di posting, tidak dapat dihapus'], 400);
            }

            DB::statement("DELETE FROM lapbhd WHERE no_bukti=?", [$no_bukti]);
            DB::statement("DELETE FROM lapbh WHERE no_bukti=? AND flag='SO'", [$no_bukti]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function printProsesStockOpname(Request $request)
    {
        try {
            $no_bukti = $request->get('nobukti');
            $cbg      = $this->getValidCbg();

            $TGL = Carbon::now()->format('d/m/Y');
            $JAM = Carbon::now()->addHour()->toTimeString();

            $tokoInfo = DB::select("SELECT na_toko FROM toko WHERE kode=?", [$cbg]);
            $toko = ! empty($tokoInfo) ? $tokoInfo[0]->na_toko : '';

            $data = DB::select(
                "SELECT ? AS NA_TOKO, lapbh.*, lapbhd.*,
                 CONCAT(LEFT(lapbh.no_bukti, 2), RIGHT(lapbh.no_bukti, 5)) AS bukt
                 FROM lapbh
                 JOIN lapbhd ON lapbh.no_bukti = lapbhd.no_bukti
                 WHERE TRIM(lapbh.no_bukti) = TRIM(?)
                 ORDER BY lapbhd.kd_brg",
                [$toko, $no_bukti]
            );

            $file         = 'print_proses_stock_opname';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            $cleanData                    = json_decode(json_encode($data), true);
            $PHPJasperXML->arrayParameter = ["TGL" => $TGL, "JAM" => $JAM];
            $PHPJasperXML->setData($cleanData);

            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function buatSO2(Request $request)
    {
        try {
            $no_bukti = $request->no_bukti;
            $cbg      = $this->getValidCbg();
            $cbgmaster = $this->getCbgMaster();
            $user     = auth()->user()->username ?? 'SYSTEM';

            $prefix = substr($no_bukti, 0, 2);
            if ($prefix !== 'XO' && $prefix !== 'XG') {
                return response()->json(['success' => false, 'message' => 'Hanya bukti XO atau XG yang dapat diproses.']);
            }

            $result = DB::select("CALL {$cbgmaster}.pjl_buatso_scan('PROSES_BUKTI', ?, ?, ?)", [$cbg, $no_bukti, $user]);
            $buktiBaru = $result[0]->BUKTI ?? '';

            if ($buktiBaru !== '') {
                return response()->json(['success' => true, 'message' => 'SO2 berhasil dibuat', 'bukti_baru' => $buktiBaru]);
            } else {
                return response()->json(['success' => false, 'message' => 'SO baru tidak dapat dibuat.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function exportSO(Request $request)
    {
        try {
            $no_bukti = $request->no_bukti;
            $cbg      = $this->getValidCbg();
            $cbgmaster = $this->getCbgMaster();
            $periode  = session('periode', date('m.Y'));

            if (is_array($periode)) {
                $bul = str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT);
                $tah = $periode['tahun'];
                $periode_str = $bul . '-' . $tah;
            } else {
                $periode_str = str_replace('.', '-', $periode);
            }

            $dirLokal = 'D:\\tiara\\TOKO_EXPORT_SO\\' . $periode_str;
            if (!file_exists($dirLokal)) {
                mkdir($dirLokal, 0777, true);
            }

            $data = DB::select("CALL {$cbgmaster}.pjl_expimp_so('EXPORT_DAT_COLL', ?, ?, '')", [$cbg, $no_bukti]);

            $content = '';
            foreach ($data as $row) {
                $kdbrg  = substr($row->SUB, 0, 3) . substr($row->KDBAR, 0, 4);
                $nabrg  = str_pad(substr($row->NA_BRG, 0, 30), 30, ' ');
                $barco  = str_pad(substr($row->BARCODE, 0, 13), 13, ' ');
                $ketuk  = str_pad(substr($row->KET_UK, 0, 7), 7, ' ');
                $ketkem = str_pad(substr($row->KET_KEM, 0, 18), 18, ' ');
                $stoktk = str_pad($row->SALDO, 10, ' ', STR_PAD_LEFT);
                $hj = str_pad($row->HJ, 12, ' ', STR_PAD_LEFT);
                $lph = str_pad($row->LPH, 10, ' ', STR_PAD_LEFT);
                $dtr = str_pad($row->DTR, 10, ' ', STR_PAD_LEFT);
                $content .= $kdbrg . $barco . $nabrg . $ketuk . $ketkem . $stoktk . $hj . $lph . $dtr . "\r\n";
            }

            $filePath = $dirLokal . '\\' . $no_bukti . '.txt';
            file_put_contents($filePath, $content);

            return response()->json(['success' => true, 'message' => 'Export SO selesai: ' . $filePath]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function importSO(Request $request)
    {
        try {
            $namafile = $request->namafile;
            $cbg      = $this->getValidCbg();
            $cbgmaster = $this->getCbgMaster();
            $user     = Auth::user()->username ?? 'SYSTEM';
            $periode  = session('periode', date('m.Y'));

            if (is_array($periode)) {
                $bul = str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT);
                $tah = $periode['tahun'];
                $periode_str = $bul . '-' . $tah;
            } else {
                $periode_str = str_replace('.', '-', $periode);
            }

            $dirLokal = 'D:\\tiara\\PJL_IMPORT_SO\\' . $periode_str;
            $filePath = $dirLokal . '\\' . $namafile . '.txt';

            if (!file_exists($filePath)) {
                return response()->json(['success' => false, 'message' => 'File tidak ada.']);
            }

            $tokoType = DB::select("SELECT TYPE FROM toko WHERE KODE=?", [$cbg]);
            $tipecbg = !empty($tokoType) ? $tokoType[0]->TYPE : '';

            if (strtoupper(substr($namafile, -1)) !== $tipecbg) {
                return response()->json(['success' => false, 'message' => 'File bukan milik ' . $cbg]);
            }

            $cekImport = DB::select("CALL {$cbgmaster}.pjl_expimp_so('CEK_IMPORT', ?, ?, '')", [$cbg, $namafile]);

            if (count($cekImport) > 0) {
                return response()->json([
                    'success' => false,
                    'confirm' => true,
                    'message' => 'Import SO ' . $namafile . ' sudah diproses pada ' .
                        $cekImport[0]->HARI . ' ' . $cekImport[0]->JAM .
                        ' (' . $cekImport[0]->USRNM . '). Timpa data lama?'
                ]);
            }

            DB::statement("CALL {$cbgmaster}.pjl_expimp_so('UPDATE_SO_IMPORT', ?, ?, ?)", [$cbg, $namafile, $user]);

            DB::statement("DROP TABLE IF EXISTS sopjl_outlet_txt{$cbg}");
            DB::statement("CREATE TABLE sopjl_outlet_txt{$cbg} SELECT * FROM sopjl_outlet_txt");
            DB::statement("ALTER TABLE sopjl_outlet_txt{$cbg}
                      MODIFY COLUMN NO_ID int(11) NOT NULL AUTO_INCREMENT FIRST,
                      ADD PRIMARY KEY (NO_ID),
                      ADD INDEX `cari` (`NO_BUKTI`,`KD_BRG`)");

            return response()->json(['success' => true, 'message' => $namafile . ' berhasil import.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function koreksi(Request $request)
    {
        try {
            $no_bukti = $request->get('no_bukti', '+');
            $status   = $request->get('status', 'simpan');
            $periode  = session('periode', date('m.Y'));

            if (is_array($periode)) {
                $periode = $periode['bulan'] . '.' . $periode['tahun'];
            }
            $cbg = $this->getValidCbg();

            $periodeCheck = DB::select("SELECT posted FROM perid WHERE kd_peri=?", [$periode]);
            if (! empty($periodeCheck) && $periodeCheck[0]->posted == 1) {
                return view('otranskasi_proses_stok_opname.koreksi', [
                    'error'    => 'Closed Period',
                    'periode'  => $periode,
                    'cbg'      => $cbg,
                    'status'   => $status,
                    'no_bukti' => '+',
                    'header'   => (object) ['no_bukti' => '+', 'tgl' => date('Y-m-d'), 'sub' => '', 'notes' => ''],
                    'detail'   => [],
                ]);
            }

            $data = [
                'no_bukti' => '+',
                'status'   => $status,
                'header'   => (object) ['no_bukti' => '+', 'tgl' => date('Y-m-d'), 'sub' => '', 'notes' => '', 'nolap' => ''],
                'detail'   => [],
                'periode'  => $periode,
                'cbg'      => $cbg,
                'error'    => null,
            ];

            if ($status == 'edit' && $no_bukti && $no_bukti != '+') {
                $header = DB::select(
                    "SELECT no_bukti, tgl, sub, posted, nolap, type FROM stockb WHERE no_bukti=? AND flag='AO'",
                    [$no_bukti]
                );

                if (! empty($header)) {
                    $headerData = $header[0];
                    if ($headerData->posted == 1) {
                        return view('otranskasi_proses_stok_opname.koreksi', [
                            'error'    => 'Transaksi sudah di Posting !!',
                            'periode'  => $periode,
                            'cbg'      => $cbg,
                            'status'   => $status,
                            'no_bukti' => $no_bukti,
                            'header'   => $headerData,
                            'detail'   => [],
                        ]);
                    }

                    $detail = DB::select(
                        "SELECT stockbd.no_id, stockbd.rec, stockbd.kd_brg, stockbd.na_brg,
                            stockbd.hj, stockbd.saldo, brg.supp as SUPP,
                            IFNULL(stockbd.qty, 0) as qty,
                            IFNULL(stockbd.riil, 0) as riil,
                            IFNULL(stockbd.total, 0) as total,
                            IFNULL(stockbd.ket, '') as ket,
                            brg.sub as SUB, '' as STAND
                     FROM stockbd
                     LEFT JOIN brg ON stockbd.kd_brg = brg.kd_brg
                     WHERE stockbd.no_bukti=?
                     ORDER BY stockbd.rec",
                        [$no_bukti]
                    );

                    foreach ($detail as $item) {
                        $brgInfo = DB::select("SELECT barcode FROM brg WHERE kd_brg=?", [$item->kd_brg]);
                        $item->barcode = ! empty($brgInfo) ? $brgInfo[0]->barcode : '';
                    }

                    $data['header']   = $headerData;
                    $data['detail']   = $detail;
                    $data['no_bukti'] = $no_bukti;
                    $data['status']   = $status;
                } else {
                    $data['error'] = 'Data tidak ditemukan';
                }
            }

            return view('otranskasi_proses_stok_opname.koreksi', $data);
        } catch (\Exception $e) {
            return view('otranskasi_proses_stok_opname.koreksi', [
                'no_bukti' => '+',
                'status'   => 'simpan',
                'header'   => (object) ['no_bukti' => '+', 'tgl' => date('Y-m-d'), 'sub' => '', 'notes' => '', 'nolap' => ''],
                'detail'   => [],
                'periode'  => session('periode', date('m.Y')),
                'cbg'      => $this->getValidCbg(),
                'error'    => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function storeKoreksiSo(Request $request)
    {
        DB::beginTransaction();
        try {
            $flag    = $request->type === 'BSO' ? 'AO' : 'AK';
            $periode = session('periode');

            if (is_array($periode)) {
                $bulan   = str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT);
                $tahun   = $periode['tahun'];
                $periode = $bulan . '/' . $tahun;
            } else {
                list($bulan, $tahun) = explode('.', $periode);
                $periode = $bulan . '/' . $tahun;
            }

            $cbg      = $this->getValidCbg();
            $username = Auth::user()->username;
            $noBukti  = $request->no_bukti;

            if ($noBukti === '+') {
                $kode2 = DB::table('toko')->where('KODE', $cbg)->value('TYPE');
                $nomor = DB::table('notrans')->where('TRANS', 'KASISTEN')->where('PER', $tahun)->value("NOM{$bulan}");
                $nomor++;

                DB::table('notrans')->where('TRANS', 'KASISTEN')->where('PER', $tahun)->update(["NOM{$bulan}" => $nomor]);

                $noBukti = 'AS' . substr($periode, -2) . $bulan . '-' . str_pad($nomor, 4, '0', STR_PAD_LEFT) . $kode2;
            }

            if ($request->status === 'simpan') {
                DB::table('stockb')->insert([
                    'no_bukti'  => $noBukti,
                    'tgl'       => $request->tgl,
                    'flag'      => $flag,
                    'per'       => $periode,
                    'notes'     => '',
                    'usrnm'     => $username,
                    'tg_smp'    => now(),
                    'type'      => $request->type,
                    'cbg'       => $cbg,
                    'sub'       => $request->sub,
                    'nolap'     => $request->no_so,
                    'total_qty' => 0,
                    'total'     => 0,
                ]);
            } else {
                DB::statement('CALL STOCKBDEL(?)', [$noBukti]);
                DB::table('stockb')->where('no_bukti', $noBukti)->update([
                    'tgl'       => $request->tgl,
                    'notes'     => '',
                    'total_qty' => 0,
                    'total'     => 0,
                    'usrnm'     => $username,
                    'tg_smp'    => now(),
                ]);
            }

            $headerId = DB::table('stockb')->where('no_bukti', $noBukti)->value('no_id');

            foreach ($request->detail as $i => $row) {
                if ($row['no_id'] == 0) {
                    DB::table('stockbd')->insert([
                        'no_bukti' => $noBukti,
                        'rec'      => $i + 1,
                        'per'      => $periode,
                        'flag'     => $flag,
                        'kd_brg'   => $row['kd_brg'],
                        'na_brg'   => $row['na_brg'],
                        'qty'      => $row['qty'],
                        'riil'     => $row['riil'],
                        'total'    => $row['total'],
                        'ket'      => $row['ket'] ?? '',
                        'id'       => $headerId,
                        'saldo2'   => $row['qty_trans'] ?? 0,
                    ]);
                } else {
                    DB::table('stockbd')->where('no_id', $row['no_id'])->update([
                        'rec'   => $i + 1,
                        'qty'   => $row['qty'],
                        'riil'  => $row['riil'],
                        'total' => $row['total'],
                        'ket'   => $row['ket'] ?? '',
                    ]);
                }
            }

            DB::statement('CALL STOCKBINS(?)', [$noBukti]);
            DB::table('lapbh')->where('no_bukti', $request->no_so)->update(['posted' => 1]);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Save Data Success', 'no_bukti' => $noBukti]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function browseKoreksiSo(Request $request)
    {
        DB::beginTransaction();
        try {
            $nolap   = strtoupper(trim($request->no_so));
            $cbg     = $this->getValidCbg();
            $periode = session('periode', date('m.Y'));

            if (is_array($periode)) {
                $periode = $periode['bulan'] . '/' . $periode['tahun'];
            } else {
                $periode = str_replace('.', '/', $periode);
            }

            $flag = substr($nolap, 0, 2);
            if ($flag === 'SZ') {
                $flagDb = 'BZ';
            } elseif ($flag === 'XO') {
                $flagDb = 'SO';
            } else {
                $flagDb = $flag;
            }

            $bukti = DB::table('lapbh')
                ->whereRaw("CONCAT(LEFT(no_bukti,2), RIGHT(no_bukti,5)) = ?", [$nolap])
                ->where('flag', $flagDb)
                ->max('no_bukti');

            if (!$bukti) {
                throw new \Exception('Bukti tidak ditemukan...!');
            }

            $header = DB::table('lapbh')->where('no_bukti', $bukti)->where('cbg', $cbg)->first();
            if (!$header) {
                throw new \Exception('Bukti tidak ditemukan...!');
            }
            if ($header->posted == 1) {
                throw new \Exception('Sudah ada Koreksi dengan nomor SO ini...!');
            }

            $type        = '';
            $showQtyApps = false;

            if (in_array($header->flag, ['BZ', '3Z'])) {
                $type = 'LBK';
            } elseif ($header->flag === 'BT') {
                $type = 'TAT';
            } elseif ($header->flag === 'SO') {
                $type        = 'BSO';
                $showQtyApps = true;
            }

            $details = DB::table('lapbhd')->where('no_bukti', $bukti)->orderBy('kd_brg')->get();
            $rows = [];

            foreach ($details as $brg) {
                $brgdt = DB::table('brgdt')
                    ->where('KD_BRG', $brg->kd_brg)
                    ->where('CBG', $cbg)
                    ->where('YER', now()->year)
                    ->first();

                $saldo     = $brgdt->AK00 ?? 0;
                $harga     = $brgdt->HB ?? 0;
                $riil      = $saldo;
                $qty_apps  = $brg->QTY_APPS ?? 0;
                $qty_trans = $brg->QTY_TRANS ?? 0;

                if ($type === 'BSO') {
                    $riil = $qty_apps == 0 ? $saldo : $qty_apps;
                    if (substr($nolap, 0, 2) === 'XO') {
                        $riil      = $brg->QTY_APPS;
                        $saldo     = $brg->SALDO;
                        $qty_trans = $brg->QTY_TRANS;
                    }
                }

                $rekap = DB::table("synchron.rekap_stok_{$cbg}")
                    ->where('per', $periode)
                    ->where('kd_brg', $brg->kd_brg)
                    ->first();

                $qty_indi = $rekap->akhir_tk ?? 0;

                $rows[] = [
                    'kd_brg'    => $brg->kd_brg,
                    'itemsub'   => $brg->itemsub,
                    'na_brg'    => $brg->na_brg,
                    'ket_uk'    => $brg->ket_uk,
                    'ket_kem'   => $brg->ket_kem,
                    'saldo'     => $saldo,
                    'harga'     => $harga,
                    'kd'        => $brg->kd,
                    'hj'        => $brg->hj,
                    'qty'       => $qty_indi,
                    'qty_trans' => $qty_trans,
                    'riil'      => $riil,
                    'qty_apps'  => $qty_apps,
                    'qty_indi'  => $qty_indi,
                ];
            }

            DB::commit();
            return response()->json([
                'status'        => true,
                'no_bukti'      => $header->no_bukti,
                'sub'           => $header->sub,
                'type'          => $type,
                'show_qty_apps' => $showQtyApps,
                'data'          => $rows,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'msg' => $e->getMessage()], 400);
        }
    }
}
