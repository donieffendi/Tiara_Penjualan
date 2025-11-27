<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TOrderKepembelianController extends Controller
{
    private function getJnsTransType($jns_trans)
    {
        return strtoupper($jns_trans) === 'TANPA_DC' ? 'TANPA_DC' : 'BIASA';
    }

    private function getPageTitle($jns_trans)
    {
        $type = $this->getJnsTransType($jns_trans);
        return $type === 'TANPA_DC' ? 'Transaksi Order Kepembelian ( Tanpa DC )' : 'Transaksi Order Kepembelian';
    }

    public function index($jns_trans = 'BIASA')
    {
        // Set flag dari CBG user jika belum ada di session
        if (!session('flag')) {
            $flag = Auth::user()->CBG ?? null;
            if ($flag) {
                session(['flag' => $flag]);
            }
        }

        $type = $this->getJnsTransType($jns_trans);
        $title = $this->getPageTitle($jns_trans);

        return view('otransaksi_OrderKepembelian.index', compact('type', 'title', 'jns_trans'));
    }

    public function browsePage($jns_trans = 'BIASA')
    {
        return view('otransaksi_OrderKepembelian.browse');
    }

    public function getData($jns_trans = 'BIASA', Request $request)
    {
        try {
            $periodeSession = session('periode');
            $flag = session('flag');

            // Jika flag tidak ada di session, gunakan CBG dari user yang login
            if (!$flag) {
                $flag = Auth::user()->CBG ?? null;
                if ($flag) {
                    session(['flag' => $flag]);
                }
            }

            if (!$periodeSession) {
                return response()->json([
                    'error' => 'Periode belum diset',
                    'draw' => intval($request->input('draw', 0)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], 200);
            }

            if (!$flag) {
                return response()->json([
                    'error' => 'Cabang (flag) belum diset',
                    'draw' => intval($request->input('draw', 0)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], 200);
            }

            // Convert periode array to string format MM/YYYY
            $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];

            $type = $this->getJnsTransType($jns_trans);

            $query = "SELECT NO_BUKTI, TGL, PER, KODES, NAMAS, TOTAL_QTY, FLAG, TG_SMP, CBG,
                      EXP, OPERATOR, USRNM, NOTES, TOTAL, LPH1, LPH2, HARI, SUB1, SUB2, POSTED
                      FROM khusus
                      WHERE PER = ? AND FLAG = 'JL' AND CBG = ?
                      GROUP BY NO_BUKTI
                      ORDER BY NO_BUKTI DESC";

            $data = DB::select($query, [$periode, $flag]);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL));
                })
                ->editColumn('TG_SMP', function ($row) {
                    return date('d-m-Y H:i', strtotime($row->TG_SMP));
                })
                ->editColumn('TOTAL_QTY', function ($row) {
                    return number_format($row->TOTAL_QTY, 0, ',', '.');
                })
                ->editColumn('TOTAL', function ($row) {
                    return number_format($row->TOTAL, 2, ',', '.');
                })
                ->addColumn('action', function ($row) use ($jns_trans) {
                    if (
                        Auth::user()->divisi == "programmer" || Auth::user()->divisi == "owner" ||
                        Auth::user()->divisi == "assistant" || Auth::user()->divisi == "accounting"
                    ) {

                        $btnEdit = '';
                        $btnDelete = '';
                        $btnPost = '';

                        if ($row->POSTED == 0) {
                            $btnEdit = '<a class="dropdown-item" href="' . route('TOrderKepembelian.edit', ['jns_trans' => $jns_trans, 'idx' => $row->NO_BUKTI]) . '">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>';

                            $btnDelete = '<a class="dropdown-item btn-delete" data-id="' . $row->NO_BUKTI . '" data-jns="' . $jns_trans . '">
                                        <i class="fa fa-trash"></i> Delete
                                      </a>';

                            $btnPost = '<a class="dropdown-item btn-posting" data-id="' . $row->NO_BUKTI . '" data-jns="' . $jns_trans . '">
                                        <i class="fa fa-check"></i> Posting
                                      </a>';
                        }

                        $actionBtn = '
                        <div class="dropdown show" style="text-align: center">
                            <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button"
                               id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bars"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                ' . $btnEdit . '
                                ' . $btnPost . '
                                ' . (($btnEdit || $btnPost) && $btnDelete ? '<hr>' : '') . '
                                ' . $btnDelete . '
                            </div>
                        </div>';

                        return $actionBtn;
                    }
                    return '';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getData TOrderKepembelian: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'draw' => intval($request->input('draw', 0)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 200);
        }
    }

    public function edit($jns_trans = 'BIASA', Request $request)
    {
        $idx = $request->idx ?? '';
        $tipx = $request->tipx ?? 'edit';
        $periodeSession = session('periode');

        // Set flag dari CBG user jika belum ada di session
        if (!session('flag')) {
            $flag = Auth::user()->CBG ?? null;
            if ($flag) {
                session(['flag' => $flag]);
            }
        }

        $type = $this->getJnsTransType($jns_trans);
        $title = $this->getPageTitle($jns_trans);

        // Convert periode array to string format MM/YYYY
        $periode = '';
        if ($periodeSession) {
            $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
        }

        if ($tipx == 'search') {
            $kodex = $request->kodex;
            $bingco = DB::select("SELECT NO_BUKTI FROM khusus
                                  WHERE NO_BUKTI = ? AND FLAG = 'JL'
                                  ORDER BY NO_BUKTI ASC LIMIT 1", [$kodex]);
            $idx = !empty($bingco) ? $bingco[0]->NO_BUKTI : '';
        }

        if ($tipx == 'top') {
            $bingco = DB::select("SELECT NO_BUKTI FROM khusus
                                  WHERE FLAG = 'JL' AND PER = ?
                                  ORDER BY NO_BUKTI ASC LIMIT 1", [$periode]);
            $idx = !empty($bingco) ? $bingco[0]->NO_BUKTI : '';
        }

        if ($tipx == 'prev') {
            $kodex = $request->kodex;
            $bingco = DB::select("SELECT NO_BUKTI FROM khusus
                                  WHERE NO_BUKTI < ? AND FLAG = 'JL' AND PER = ?
                                  ORDER BY NO_BUKTI DESC LIMIT 1", [$kodex, $periode]);
            $idx = !empty($bingco) ? $bingco[0]->NO_BUKTI : $kodex;
        }

        if ($tipx == 'next') {
            $kodex = $request->kodex;
            $bingco = DB::select("SELECT NO_BUKTI FROM khusus
                                  WHERE NO_BUKTI > ? AND FLAG = 'JL' AND PER = ?
                                  ORDER BY NO_BUKTI ASC LIMIT 1", [$kodex, $periode]);
            $idx = !empty($bingco) ? $bingco[0]->NO_BUKTI : $kodex;
        }

        if ($tipx == 'bottom') {
            $bingco = DB::select("SELECT NO_BUKTI FROM khusus
                                  WHERE FLAG = 'JL' AND PER = ?
                                  ORDER BY NO_BUKTI DESC LIMIT 1", [$periode]);
            $idx = !empty($bingco) ? $bingco[0]->NO_BUKTI : '';
        }

        if ($tipx == 'undo' || $tipx == 'search') {
            $tipx = 'edit';
        }

        if (!empty($idx)) {
            $header = DB::select("SELECT * FROM khusus WHERE NO_BUKTI = ? AND FLAG = 'JL'", [$idx]);
            if (!empty($header)) {
                $header = $header[0];
                $detail = DB::select("SELECT A.*, B.SP_L, B.SP_LF, B.SP_LZ,
                                      (SELECT KODE_DC FROM sup WHERE KODES = A.KODES LIMIT 1) AS KODE_DC
                                      FROM khususd A
                                      LEFT JOIN brg B ON A.KD_BRG = B.KD_BRG
                                      WHERE A.NO_BUKTI = ?
                                      ORDER BY A.REC", [$idx]);
            } else {
                $header = $this->getEmptyHeader();
                $detail = [];
            }
        } else {
            $header = $this->getEmptyHeader();
            $detail = [];
        }

        $data = [
            'header' => $header,
            'detail' => $detail,
            'type' => $type,
            'title' => $title,
            'jns_trans' => $jns_trans
        ];

        return view('otransaksi_OrderKepembelian.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    private function getEmptyHeader()
    {
        return (object) [
            'NO_BUKTI' => '+',
            'TGL' => Carbon::now()->format('Y-m-d'),
            'KODES' => '',
            'NAMAS' => '',
            'NOTES' => '',
            'TOTAL_QTY' => 0,
            'TOTAL' => 0,
            'LPH1' => 0,
            'LPH2' => 0,
            'HARI' => 0,
            'SUB1' => '',
            'SUB2' => '',
            'POSTED' => 0
        ];
    }

    public function store($jns_trans = 'BIASA', Request $request)
    {
        $this->validate($request, [
            'TGL' => 'required',
            'KODES' => 'required'
        ]);

        $periodeSession = session('periode');
        $flag = session('flag') ?? Auth::user()->CBG;
        $userName = Auth::user()->name;
        $type = $this->getJnsTransType($jns_trans);

        // Convert periode array to string format MM/YYYY
        $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];

        DB::beginTransaction();

        try {
            $perid = DB::select("SELECT POSTED FROM perid WHERE KD_PERI = ?", [$periode]);
            if (!empty($perid) && $perid[0]->POSTED == 1) {
                return response()->json(['error' => 'Periode sudah ditutup'], 400);
            }

            $month = $periodeSession['bulan'];
            $year = $periodeSession['tahun'];
            $yearShort = substr($year, -2);

            $tglParts = explode('-', $request['TGL']);
            $tglMonth = $tglParts[1];
            $tglYear = $tglParts[0];

            if ($tglMonth != $month || $tglYear != $year) {
                return response()->json(['error' => 'Tanggal tidak sesuai dengan periode'], 400);
            }

            if ($request['NO_BUKTI'] == '+' || empty($request['NO_BUKTI'])) {
                $toko = DB::select("SELECT TYPE FROM toko WHERE KODE = ?", [$flag]);
                $kode2 = !empty($toko) ? $toko[0]->TYPE : '';

                $kode = 'JL' . $yearShort . $month;

                $notrans = DB::select("SELECT NOM$month AS NO_BUKTI FROM notrans
                                       WHERE TRANS = 'KHU' AND PER = ?", [$year]);
                $r1 = !empty($notrans) ? $notrans[0]->NO_BUKTI + 1 : 1;

                DB::statement("UPDATE notrans SET NOM$month = ?
                               WHERE TRANS = 'KHU' AND PER = ?", [$r1, $year]);

                $bkt1 = sprintf('%04d', $r1);
                $noBukti = $kode . '-' . $bkt1 . $kode2;
            } else {
                $noBukti = $request['NO_BUKTI'];
            }

            $headerData = [
                'NO_BUKTI' => $noBukti,
                'TGL' => $request['TGL'],
                'PER' => $periode,
                'KODES' => $request['KODES'],
                'NAMAS' => $request['NAMAS'],
                'TOTAL_QTY' => $request['TOTAL_QTY'] ?? 0,
                'FLAG' => 'JL',
                'TG_SMP' => Carbon::now(),
                'CBG' => $flag,
                'EXP' => '',
                'OPERATOR' => 'C',
                'USRNM' => $userName,
                'NOTES' => $request['NOTES'] ?? '',
                'TOTAL' => $request['TOTAL'] ?? 0,
                'LPH1' => $request['LPH1'] ?? 0,
                'LPH2' => $request['LPH2'] ?? 0,
                'HARI' => $request['HARI'] ?? 0,
                'SUB1' => $request['SUB1'] ?? '',
                'SUB2' => $request['SUB2'] ?? '',
                'POSTED' => 0
            ];

            if (empty($request['edit'])) {
                $columns = implode(', ', array_keys($headerData));
                $placeholders = implode(', ', array_fill(0, count($headerData), '?'));
                DB::statement("INSERT INTO khusus ($columns) VALUES ($placeholders)", array_values($headerData));
            } else {
                $existing = DB::select("SELECT NO_ID FROM khusus WHERE NO_BUKTI = ?", [$noBukti]);
                if (!empty($existing)) {
                    $headerId = $existing[0]->NO_ID;
                    DB::statement("DELETE FROM khususd WHERE ID = ?", [$headerId]);

                    $sets = [];
                    foreach ($headerData as $key => $value) {
                        if ($key != 'NO_BUKTI') {
                            $sets[] = "$key = ?";
                        }
                    }
                    $setClause = implode(', ', $sets);
                    $values = array_values(array_filter($headerData, function ($key) {
                        return $key != 'NO_BUKTI';
                    }, ARRAY_FILTER_USE_KEY));
                    $values[] = $noBukti;

                    DB::statement("UPDATE khusus SET $setClause WHERE NO_BUKTI = ?", $values);
                }
            }

            $headerId = DB::select("SELECT NO_ID FROM khusus WHERE NO_BUKTI = ?", [$noBukti]);
            $headerId = !empty($headerId) ? $headerId[0]->NO_ID : 0;

            if (!empty($request['detail'])) {
                foreach ($request['detail'] as $index => $detail) {
                    if (!empty($detail['KD_BRG'])) {
                        DB::statement("INSERT INTO khususd
                                       (NO_BUKTI, REC, PER, FLAG, KODES, KD_BRG, NA_BRG, KET_KEM, KET_UK,
                                        KEMASAN, QTYPO, QTYBRG, LPH, QTY, HARGA, TOTAL, NOTES, ID, CBG, SMIN)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                            $noBukti,
                            $detail['REC'] ?? ($index + 1),
                            $periode,
                            'JL',
                            $detail['KODES'] ?? '',
                            $detail['KD_BRG'],
                            $detail['NA_BRG'] ?? '',
                            $detail['KET_KEM'] ?? '',
                            $detail['KET_UK'] ?? '',
                            $detail['KEMASAN'] ?? 0,
                            $detail['QTYPO'] ?? 0,
                            $detail['QTYBRG'] ?? 0,
                            $detail['LPH'] ?? 0,
                            $detail['QTY'] ?? 0,
                            $detail['HARGA'] ?? 0,
                            $detail['TOTAL'] ?? 0,
                            $detail['NOTES'] ?? '',
                            $headerId,
                            $flag,
                            $detail['SMIN'] ?? 0
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => 'Data berhasil disimpan', 'no_bukti' => $noBukti]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }

    public function update($jns_trans = 'BIASA', Request $request, $id)
    {
        $request['edit'] = true;
        $request['NO_BUKTI'] = $id;
        return $this->store($jns_trans, $request);
    }

    public function destroy($jns_trans = 'BIASA', $id)
    {
        DB::beginTransaction();

        try {
            $khusus = DB::select("SELECT POSTED FROM khusus WHERE NO_BUKTI = ?", [$id]);
            if (!empty($khusus) && $khusus[0]->POSTED == 1) {
                return response()->json(['error' => 'Data sudah diposting'], 400);
            }

            $headerId = DB::select("SELECT NO_ID FROM khusus WHERE NO_BUKTI = ?", [$id]);
            if (!empty($headerId)) {
                DB::statement("DELETE FROM khususd WHERE ID = ?", [$headerId[0]->NO_ID]);
            }

            DB::statement("DELETE FROM khusus WHERE NO_BUKTI = ?", [$id]);

            DB::commit();
            return redirect()->route('TOrderKepembelian', ['jns_trans' => $jns_trans])
                ->with('status', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
        }
    }

    public function browseHari(Request $request)
    {
        $kodes = $request->kodes;
        $sup = DB::select("SELECT NO_ID, KODES, NAMAS, ALMT_K, KOTA, TLP_K, N_AKTIF,
                           KEL_PAJAK, HARI FROM sup WHERE KODES = ?", [$kodes]);
        return response()->json($sup);
    }

    public function browse(Request $request)
    {
        $q = $request->q ?? '';

        if (!empty($q)) {
            $sup = DB::select("SELECT NO_ID, KODES, NAMAS, CONCAT(KODES, '-', NAMAS) AS NAMAS2,
                               ALMT_K, KOTA, TLP_K, N_AKTIF, KEL_PAJAK, HARI FROM sup
                               WHERE NAMAS <> '' AND NAMAS LIKE ?
                               ORDER BY KODES", ['%' . $q . '%']);
        } else {
            $sup = DB::select("SELECT NO_ID, KODES, NAMAS, CONCAT(KODES, '-', NAMAS) AS NAMAS2,
                               ALMT_K, KOTA, TLP_K, N_AKTIF, KEL_PAJAK, HARI FROM sup
                               WHERE NAMAS <> ''
                               ORDER BY KODES");
        }

        return response()->json($sup);
    }

    public function browsesupz(Request $request)
    {
        $q = $request->q ?? '';
        $data = DB::select("SELECT KODES, CONCAT(NAMAS, '-', KOTA) AS NAMAS FROM sup
                            WHERE NAMAS LIKE ?
                            ORDER BY NAMAS LIMIT 30", ['%' . $q . '%']);
        return response()->json($data);
    }

    public function ceksup(Request $request)
    {
        $kodes = $request->kodes ?? '';
        $result = DB::select("SELECT COUNT(*) AS ADA FROM sup WHERE KODES = ?", [$kodes]);
        return response()->json($result);
    }

    public function getSelectKodes(Request $request)
    {
        $kodes = $request->kodes ?? '';
        $sup = DB::select("SELECT KODES, NAMAS, ALMT_K, KOTA, HARI FROM sup WHERE KODES = ?", [$kodes]);
        return response()->json($sup);
    }

    public function proses($jns_trans = 'BIASA', Request $request)
    {
        $periodeSession = session('periode');
        $flag = session('flag') ?? Auth::user()->CBG;
        $type = $this->getJnsTransType($jns_trans);

        // Convert periode array to string format MM/YYYY (if needed)
        $periode = '';
        if ($periodeSession) {
            $periode = str_pad($periodeSession['bulan'], 2, '0', STR_PAD_LEFT) . '/' . $periodeSession['tahun'];
        }

        $cbg = $flag;
        $spl = ' A.SUPP IN (SELECT KODES FROM SUP_DC_TS) AND ';
        $filter_srmin = '';
        $filter_jo = '';

        if ($type === 'TANPA_DC') {
            $filter_srmin = ' (TOTALTK + TOTALGD) <= SRMIN AND COALESCE(TOTALPO, 0) = 0 AND QTY_ORD = 0 AND ';
            $spl = ' A.ON_DC = 0 AND ';
        }

        $kodes1 = $request->kodes1 ?? '';
        $kodes2 = $request->kodes2 ?? '';
        $lph1 = $request->lph1 ?? 0;
        $lph2 = $request->lph2 ?? 0;
        $sub1 = $request->sub1 ?? '';
        $sub2 = $request->sub2 ?? '';
        $hari = $request->hari ?? 0;

        try {
            $query = "
                SET @cbg   := ?;
                SET @kodes1:= ?;
                SET @kodes2:= ?;
                SET @lph1  := ?;
                SET @lph2  := ?;
                SET @sub1  := ?;
                SET @sub2  := ?;

                SELECT INI.*, TOTALTK + TOTALGD AS SALDO
                FROM (
                    SELECT A.SP_L, A.SUB, A.KD_BRG, A.NA_BRG, A.KET_UK, A.KET_KEM,
                        A.SUPP AS KODES, A.TYPE, C.HB, C.KDLAKU, C.KLK, C.LPH, A.MO,
                        COALESCE(E.QTY, 0) AS QTY_ORD,
                        A.SP_LF, A.SP_LZ, A.ON_DC,
                        GREATEST(ROUND(1.5 * C.LPH * TGZ.XX_HITKLK(C.KLK)), ROUND(0.5 * (D.DTR + D.DTR2))) AS SRMIN,
                        D.DTR, D.DTR2,
                        RIGHT(TRIM(A.KET_KEM), LENGTH(TRIM(A.KET_KEM)) - LOCATE('/', TRIM(A.KET_KEM))) AS KEM,
                        IF(KK.TOTALPO IS NULL, 0, KK.TOTALPO) AS TOTALPO,
                        C.AK00 AS TOTALTK, B.AK00 AS TOTALGD,
                        (SELECT KODE_DC FROM SUP WHERE KODES = A.SUPP LIMIT 1) AS KODE_DC
                    FROM BRG A, BRGD B, BRGDT C
                    LEFT JOIN (
                        SELECT POD.KD_BRG, POD.NA_BRG, SUM(POD.QTY) AS TOTALPO
                        FROM POD, PO
                        WHERE PO.NO_BUKTI = POD.NO_BUKTI AND PO.CBG = @cbg
                        AND IF(PO.TKK3 <> '2001-01-01', PO.TKK3,
                            IF(PO.TKK2 <> '2001-01-01', PO.TKK2, PO.TKK1)) >= DATE(NOW())
                        GROUP BY KD_BRG
                    ) AS KK ON KK.KD_BRG = C.KD_BRG
                    LEFT JOIN SPO E ON C.KD_BRG = E.KD_BRG AND E.GOL = '1' AND E.FLAG = 'KS'
                    LEFT JOIN BRG_DC_TS D ON C.KD_BRG = D.KD_BRG
                    WHERE A.KD_BRG = B.KD_BRG
                    AND " . $spl . "
                    B.KD_BRG = C.KD_BRG
                    AND B.CBG = @cbg AND C.CBG = @cbg AND C.YER = YEAR(NOW())
                    AND (C.KDLAKU = '0' OR C.KDLAKU = '1' OR C.KDLAKU = '4')
                    AND LEFT(A.NA_BRG, 1) NOT IN ('3', '5', '6')
                    AND TRIM(C.TD_OD) = '' AND A.SUPP BETWEEN @kodes1 AND @kodes2
                ) AS INI
                WHERE " . $filter_srmin . " LPH BETWEEN @lph1 AND @lph2
                AND SUB BETWEEN @sub1 AND @sub2
                ORDER BY KODES, KD_BRG";

            DB::statement($query, [$cbg, $kodes1, $kodes2, $lph1, $lph2, $sub1, $sub2]);

            $result = DB::select("SELECT * FROM INI");

            $processedData = [];
            $sup_libur = '';

            foreach ($result as $row) {
                if ($type === 'TANPA_DC') {
                    $cekLibur = DB::select(
                        "SELECT COUNT(*) AS CEK FROM SUP_LIBUR
                                            WHERE KODES = ? AND CURDATE() BETWEEN TGL_AWAL AND TGL_AKHIR",
                        [$row->KODES]
                    );

                    if (!empty($cekLibur) && $cekLibur[0]->CEK > 0) {
                        $sup_libur .= $row->KODES . ', ';
                        continue;
                    }
                }

                $z1 = ($row->LPH * $hari) - $row->TOTALPO - $row->SALDO;
                $z4 = floor($z1);
                $z2 = $z4 / $row->KEM;

                $ceilResult = DB::select("SELECT CEILING(?) AS ZZ", [$z2]);
                $z3 = $ceilResult[0]->ZZ;
                $z1 = $z3 * $row->KEM;

                if ($type === 'TANPA_DC') {
                    $klkResult = DB::select("SELECT TGZ.XX_HITKLK(?) AS XKLK", [$row->KLK]);
                    $xklk = $klkResult[0]->XKLK;

                    $z1 = 2.5 * ($row->LPH * $hari) * $xklk;
                    $z1 = floor($z1 - $row->TOTALPO - $row->SALDO);

                    $z4 = $row->DTR + $row->DTR2;
                    if ($z1 <= $z4) {
                        $z1 = $z4 - $row->SALDO;
                    }

                    $ceilResult = DB::select("SELECT CEILING(? / ?) * ? AS ZZ", [$z1, $row->KEM, $row->KEM]);
                    $z1 = $ceilResult[0]->ZZ;
                }

                if ($z1 > 0) {
                    $processedData[] = [
                        'KD_BRG' => $row->KD_BRG,
                        'NA_BRG' => $row->NA_BRG,
                        'KODES' => $row->KODES,
                        'KET_KEM' => $row->KET_KEM,
                        'KET_UK' => $row->KET_UK,
                        'KEMASAN' => $row->KEM,
                        'QTY' => $z1,
                        'HARGA' => $row->HB,
                        'TOTAL' => $z1 * $row->HB,
                        'MO' => $row->MO,
                        'LPH' => $row->LPH,
                        'QTYBRG' => $row->SALDO,
                        'QTYPO' => $row->TOTALPO,
                        'KDLAKU' => $row->KDLAKU,
                        'TYPE' => $row->TYPE,
                        'SP_L' => $row->SP_L,
                        'SP_LF' => $row->SP_LF,
                        'SP_LZ' => $row->SP_LZ,
                        'SRMIN' => $row->SRMIN,
                        'KODE_DC' => $row->KODE_DC,
                        'NOTES' => strval($z1)
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $processedData,
                'sup_libur' => rtrim($sup_libur, ', ')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memproses data: ' . $e->getMessage()], 500);
        }
    }

    public function posting($jns_trans = 'BIASA', Request $request)
    {
        $noBukti = $request->no_bukti ?? '';

        if (empty($noBukti)) {
            return response()->json(['error' => 'Nomor bukti tidak boleh kosong'], 400);
        }

        DB::beginTransaction();

        try {
            $posted = DB::select("SELECT POSTED FROM khusus WHERE NO_BUKTI = ?", [$noBukti]);

            if (!empty($posted) && $posted[0]->POSTED == 0) {
                DB::statement("
                    INSERT INTO SPO (
                        NO_BUKTI, TGL, PER, KODES, NAMAS, NOTES, FLAG, TOTAL, NETT, TYPE,
                        TG_SMP, CBG, KET_KEM, KEMASAN, NA_BRG, KD_BRG, QTY, HARGA, SUB,
                        KDBAR, KET, TGO, TGL_MULAI
                    )
                    SELECT
                        KHUSUS.NO_BUKTI, DATE(NOW()), KHUSUS.PER, KHUSUSD.KODES,
                        (SELECT NAMAS FROM SUP WHERE KODES = KHUSUSD.KODES LIMIT 1) AS NAMAS,
                        KHUSUS.NOTES, KHUSUS.FLAG, KHUSUSD.TOTAL, KHUSUSD.TOTAL, KHUSUS.TYPE,
                        NOW(), KHUSUS.CBG, KHUSUSD.KET_KEM, KHUSUSD.KEMASAN, KHUSUSD.NA_BRG,
                        KHUSUSD.KD_BRG, KHUSUSD.QTY, KHUSUSD.HARGA,
                        LEFT(TRIM(KHUSUSD.KD_BRG), 3) AS SUB,
                        RIGHT(TRIM(KHUSUSD.KD_BRG), 4) AS KDBAR,
                        KHUSUS.KET, DATE(NOW()), DATE(NOW())
                    FROM KHUSUS, KHUSUSD
                    WHERE KHUSUS.NO_BUKTI = KHUSUSD.NO_BUKTI
                    AND KHUSUS.NO_BUKTI = ?
                ", [$noBukti]);

                DB::statement("UPDATE khusus SET POSTED = 1 WHERE NO_BUKTI = ?", [$noBukti]);

                DB::commit();
                return response()->json(['success' => 'Posting berhasil']);
            } else {
                return response()->json(['error' => 'Data sudah diposting atau tidak ditemukan'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal posting: ' . $e->getMessage()], 500);
        }
    }

    public function validateBarang(Request $request)
    {
        $kdBrg = $request->kd_brg ?? '';
        $cbg = session('flag') ?? Auth::user()->CBG;

        if (empty($kdBrg)) {
            return response()->json(['error' => 'Kode barang kosong'], 400);
        }

        try {
            DB::statement("
                SET @KD_BRG := ?;
                SET @CBG    := ?;
                SET @NAMAS  := '';
                SET @TPO    := 0;
            ", [$kdBrg, $cbg]);

            DB::statement("
                SELECT SUM(QTY), NAMAS INTO @TPO, @NAMAS FROM (
                    SELECT SUM(POD.QTY) AS QTY, PO.NAMAS
                    FROM PO, POD
                    WHERE PO.NO_BUKTI = POD.NO_BUKTI AND KD_BRG = @KD_BRG
                    AND IF(PO.TKK3 <> '2001-01-01', PO.TKK3,
                        IF(PO.TKK2 <> '2001-01-01', PO.TKK2, PO.TKK1)) >= DATE(NOW())
                    GROUP BY KD_BRG
                ) A
            ");

            $barang = DB::select("
                SELECT A.KD_BRG, A.NA_BRG, A.TYPE, A.KET_UK, A.KET_KEM, B.LPH, A.SUPP, B.HB,
                    A.MO, B.HB, B.KDLAKU, A.SUB, A.KDBAR, B.AK00 + B.GAK00 AS SALDO,
                    A.SP_LZ, A.ON_DC,
                    SUBSTR(TRIM(A.KET_KEM), ((LOCATE('/', TRIM(A.KET_KEM)) + 1))) AS KEMASAN,
                    @TPO AS TOTALPO, @NAMAS AS NAMAS, A.SP_L, A.SP_LF, B.TD_OD, B.CAT_OD,
                    IF(B.CAT_OD LIKE '%TP%' AND B.TD_OD <> '', CONCAT('Barang : ', B.TD_OD, B.CAT_OD), '') AS XX
                FROM BRG A, BRGDT B
                WHERE A.KD_BRG = B.KD_BRG AND B.YER = YEAR(NOW()) AND B.CBG = @CBG AND A.KD_BRG = @KD_BRG
                LIMIT 1
            ");

            if (empty($barang)) {
                return response()->json(['error' => 'Barang tidak ditemukan'], 404);
            }

            $brg = $barang[0];

            if (!empty($brg->TD_OD) && !empty($brg->XX)) {
                return response()->json([
                    'error' => $brg->XX,
                    'confirm' => false
                ], 400);
            }

            if (!empty($brg->TD_OD)) {
                return response()->json([
                    'confirm' => true,
                    'message' => 'Barang : ' . $brg->TD_OD . $brg->CAT_OD . '. Lanjutkan?',
                    'data' => $brg
                ]);
            }

            if ($brg->ON_DC == '1' && $cbg != 'DCK') {
                return response()->json(['error' => 'Barang milik DC!'], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $brg
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal validasi barang: ' . $e->getMessage()], 500);
        }
    }
}
