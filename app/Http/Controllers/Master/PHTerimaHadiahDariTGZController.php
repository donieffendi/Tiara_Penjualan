<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHTerimaHadiahDariTGZController extends Controller
{
    /**
     * Display index and edit form
     * Matching Delphi: frmTrmHTg.pas (list) dan frmTrmHTN.pas (entry form)
     */
    public function index(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status', 'simpan');

        // Jika ada parameter no_bukti atau status=edit, tampilkan form edit
        if ($no_bukti || $status == 'edit') {
            return $this->showEditForm($request);
        }

        // Tampilkan halaman list
        return view('promo_hadiah_dari_tgz.index');
    }

    /**
     * Show form for create/edit
     * Matching Delphi: FormShow procedure in frmTrmHTN.pas
     */
    private function showEditForm(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status', 'simpan');

        $data = [
            'no_bukti' => '+',
            'status' => $status,
            'header' => null,
            'detail' => [],
            'periode' => session('periode', date('m.Y'))
        ];

        // Edit mode - matching: po.sql.text:='SELECT * FROM hdh where FLAG='FT' AND no_bukti = ...'
        if ($status == 'edit' && $no_bukti) {
            $header = DB::select(
                "SELECT * FROM hdh WHERE FLAG = 'FT' AND no_bukti = ?",
                [$no_bukti]
            );

            if (!empty($header)) {
                $headerData = $header[0];

                // Check if posted - matching Delphi mati procedure call
                if ($headerData->posted == 1) {
                    return redirect()->route('phterimahadiahdaritgz')
                        ->with('error', 'Data sudah terposting, tidak dapat diedit!');
                }

                // Matching: com.SQL.Add(' SELECT NO_BUKTI, REC,KD_BRGH,NA_BRGH,QTY,HARGA,PER ,NO_ID FROM HDHD ...')
                $detail = DB::select(
                    "SELECT NO_BUKTI, REC, KD_BRGH, NA_BRGH, QTY, HARGA, PER, NO_ID
                     FROM HDHD
                     WHERE NO_BUKTI = ?
                     ORDER BY REC",
                    [$no_bukti]
                );

                $data['header'] = $headerData;
                $data['detail'] = $detail;
                $data['no_bukti'] = $no_bukti;
            }
        }

        return view('promo_hadiah_dari_tgz.edit', $data);
    }

    /**
     * Get list for datatable
     * Matching Delphi Tampil procedure: SELECT * FROM hdh where per=:per AND FLAG='FT' order by NO_BUKTI
     */
    public function getData(Request $request)
    {
        $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];

        $query = DB::select(
            "SELECT NO_BUKTI, TGL, NO_PO, TOTAL_QTY, TOTAL, posted, CBG, NOTES
             FROM hdh
             WHERE per = ? AND FLAG = 'FT'
             ORDER BY NO_BUKTI DESC",
            [$periode]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('TGL', function ($row) {
                return $row->TGL ? date('d/m/Y', strtotime($row->TGL)) : '';
            })
            ->editColumn('TOTAL_QTY', function ($row) {
                return number_format($row->TOTAL_QTY, 2, ',', '.');
            })
            ->editColumn('TOTAL', function ($row) {
                return number_format($row->TOTAL, 0, ',', '.');
            })
            ->editColumn('posted', function ($row) {
                return $row->posted == 1
                    ? '<span class="badge badge-success">Posted</span>'
                    : '<span class="badge badge-warning">Open</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row->posted == 0) {
                    $btnEdit = '<button onclick="editData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                } else {
                    $btnEdit = '<button class="btn btn-sm btn-secondary" disabled title="Sudah Terposting"><i class="fas fa-lock"></i></button>';
                }
                $btnPrint = '<button onclick="printData(\'' . $row->NO_BUKTI . '\')" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></button>';
                return $btnEdit . ' ' . $btnPrint;
            })
            ->rawColumns(['action', 'posted'])
            ->make(true);
    }

    /**
     * Store/Update data
     * Matching Delphi: MSaveClick procedure in frmTrmHTN.pas
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'agenda' => 'required',
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

            // Check if period is closed - matching: hero.sql.Clear; hero.SQL.add(abc); ...
            $check_period = DB::select("SELECT posted FROM perid WHERE kd_peri = ?", [$periode]);
            if (!empty($check_period) && $check_period[0]->posted == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closed Period'
                ], 400);
            }

            // Validate date vs periode - matching checkx procedure
            $this->validateDatePeriode($request->tgl, $periode);

            // Validate agenda
            if (empty($request->agenda)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Resi tidak boleh kosong'
                ], 400);
            }

            if ($status == 'simpan') {
                // Generate no_bukti - matching Delphi logic
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                }

                // INSERT INTO HDH - matching Delphi insert statement
                // com.SQL.Add('INSERT INTO HDH (NOTES,NO_PO,CBG,NO_BUKTI,TGL, TOTAL_QTY, FLAG, USRNM, PER, TG_SMP) VALUES ...')
                DB::statement(
                    "INSERT INTO HDH (NO_BUKTI, TGL, CBG, NO_PO, TOTAL_QTY, FLAG, USRNM, PER, TG_SMP, NOTES)
                     VALUES (?, ?, ?, ?, ?, 'FT', ?, ?, NOW(), ?)",
                    [
                        $no_bukti,
                        $request->tgl,
                        $cbg,
                        trim($request->agenda),
                        floatval($request->total_qty ?? 0),
                        $username,
                        $periode,
                        trim($request->notes ?? '')
                    ]
                );
            } else {
                // Edit mode - matching: com.SQL.Add('UPDATE HDH SET TGL=:TGL, TOTAL_QTY=:TOTAL_QTY, ...')
                DB::statement(
                    "UPDATE HDH
                     SET TGL = ?, TOTAL_QTY = ?, NO_PO = ?, NOTES = ?, USRNM = ?, TG_SMP = NOW()
                     WHERE NO_BUKTI = ?",
                    [
                        $request->tgl,
                        floatval($request->total_qty ?? 0),
                        trim($request->agenda),
                        trim($request->notes ?? ''),
                        $username,
                        $no_bukti
                    ]
                );
            }

            // Get header ID - matching: com.SQL.Text:='select no_id from HDH where no_bukti=...'
            $header_id_result = DB::select("SELECT no_id FROM HDH WHERE no_bukti = ?", [$no_bukti]);
            $id = $header_id_result[0]->no_id ?? 0;

            // Handle detail updates - matching Delphi logic for edit mode
            if ($status == 'edit') {
                // com.SQL.Text:='select no_id from HDHD where no_bukti=...'
                $existing_details = DB::select("SELECT no_id FROM HDHD WHERE no_bukti = ?", [$no_bukti]);

                // Call THDHDEL procedure - matching: com.SQL.Add('CALL THDHDEL(:BUKTI)')
                DB::statement("CALL THDHDEL(?)", [$no_bukti]);

                foreach ($existing_details as $existing) {
                    $found = false;
                    foreach ($request->details as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
                            // Update existing record - matching: com00.SQL.Add('UPDATE HDHD SET ...')
                            DB::statement(
                                "UPDATE HDHD
                                 SET REC = ?, KD_BRGH = ?, NA_BRGH = ?, QTY = ?, HARGA = ?
                                 WHERE NO_ID = ?",
                                [
                                    intval($detail['rec'] ?? 1),
                                    trim($detail['kd_brgh'] ?? ''),
                                    trim($detail['na_brgh'] ?? ''),
                                    floatval($detail['qty'] ?? 0),
                                    floatval($detail['harga'] ?? 0),
                                    $existing->no_id
                                ]
                            );
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        // Delete record - matching: com00.SQL.Add('DELETE FROM HDHD WHERE NO_ID=:NO_ID')
                        DB::statement("DELETE FROM HDHD WHERE NO_ID = ?", [$existing->no_id]);
                    }
                }
            }

            // Insert new detail records - matching Delphi insert logic
            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['kd_brgh'])) {
                    if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                        // INSERT INTO HDHD - matching: com.SQL.Add('INSERT INTO HDHD ...')
                        DB::statement(
                            "INSERT INTO HDHD (NO_BUKTI, REC, PER, FLAG, CBG, KD_BRGH, NA_BRGH, QTY, HARGA, ID)
                             VALUES (?, ?, ?, 'FT', ?, ?, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $rec,
                                $periode,
                                $cbg,
                                trim($detail['kd_brgh']),
                                trim($detail['na_brgh'] ?? ''),
                                floatval($detail['qty'] ?? 0),
                                floatval($detail['harga'] ?? 0),
                                $id
                            ]
                        );

                        // Update stock - matching: update brghd set ma00 = ma00 + :qty, ak00 = aw00 + ma00 - ke00 + ln00
                        DB::statement(
                            "UPDATE brghd
                             SET ma00 = ma00 + ?, ak00 = aw00 + ma00 - ke00 + ln00
                             WHERE kd_brgh = ?",
                            [
                                floatval($detail['qty'] ?? 0),
                                trim($detail['kd_brgh'])
                            ]
                        );
                    }
                    $rec++;
                }
            }

            // Call THDHINS procedure - matching: com.SQL.Add('CALL THDHINS(:BUKTI)')
            DB::statement("CALL THDHINS(?)", [$no_bukti]);

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
     * Browse product data
     * Matching Delphi: com.sql.text:='select * from BrgH where kd_brgh=:brgh'
     */
    public function browse(Request $request)
    {
        $q = $request->get('q', '');

        if (!empty($q)) {
            $data = DB::select(
                "SELECT kd_brgh, na_brgh
                 FROM brgh
                 WHERE kd_brgh LIKE ? OR na_brgh LIKE ?
                 ORDER BY kd_brgh
                 LIMIT 50",
                ["%$q%", "%$q%"]
            );
        } else {
            $data = DB::select(
                "SELECT kd_brgh, na_brgh
                 FROM brgh
                 ORDER BY kd_brgh
                 LIMIT 50"
            );
        }

        return response()->json($data);
    }

    /**
     * Get product detail
     * Matching Delphi: com.sql.text:='select * from BrgH where kd_brgh=:brgh'
     */
    public function getDetail(Request $request)
    {
        $kd_brgh = $request->get('kd_brgh');

        $product = DB::select(
            "SELECT kd_brgh, na_brgh FROM brgh WHERE kd_brgh = ? LIMIT 1",
            [$kd_brgh]
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

    /**
     * Load data from agenda/resi
     * Matching Delphi: txtagendaExit procedure - SELECT no_bukti,KD_BRGH,NA_BRGH,qty,harga FROM hdhd WHERE no_bukti=:bukti
     */
    public function loadFromAgenda(Request $request)
    {
        $agenda = trim($request->get('agenda', ''));
        $per = session('periode', date('m.Y'));

        $periode = $per['bulan'] . '/' . $per['tahun'];

        if (empty($agenda)) {
            return response()->json([
                'success' => false,
                'message' => 'No resi kosong'
            ]);
        }

        // Check if already entered - matching: com.SQL.Text:='SELECT NO_PO FROM HDH WHERE NO_PO=:po'
        $check = DB::select(
            "SELECT NO_PO FROM hdh WHERE NO_PO = ? AND FLAG = 'FT' AND per = ?",
            [$agenda, $periode]
        );

        if (!empty($check)) {
            return response()->json([
                'success' => false,
                'message' => 'Hadiah sudah di entri!'
            ]);
        }

        // Get detail from agenda - matching: SELECT no_bukti,KD_BRGH,NA_BRGH,qty,harga FROM hdhd WHERE no_bukti=:bukti
        $details = DB::select(
            "SELECT no_bukti, KD_BRGH, NA_BRGH, qty, harga
             FROM hdhd
             WHERE no_bukti = ?",
            [$agenda]
        );

        if (empty($details)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor Resi Salah!'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $details
        ]);
    }

    /**
     * Print report
     * Matching Delphi: frxreport1.ShowReport()
     */
    public function printTerimaHadiahDariTGZ(Request $request)
    {
        $no_bukti = $request->no_bukti;

        $data = DB::select(
            "SELECT h.NO_BUKTI, h.TGL, h.NO_PO, h.NOTES, h.TOTAL_QTY,
                    d.REC, d.KD_BRGH, d.NA_BRGH, d.QTY, d.HARGA
             FROM hdh h
             LEFT JOIN hdhd d ON h.NO_BUKTI = d.no_bukti
             WHERE h.no_bukti = ? AND h.FLAG = 'FT'
             ORDER BY d.REC",
            [$no_bukti]
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Validate date vs periode
     * Matching Delphi: checkx procedure
     */
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

    /**
     * Generate no_bukti
     * Matching Delphi: kode:='FT'+ RightStr(periode, 2) + LeftStr(periode, 2); ...
     */
    private function generateNoBukti($periode, $cbg)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        // Get toko type - matching: com.SQL.Text:='select type from toko where kode=:cbg'
        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = $toko[0]->type ?? '';

        $kode = 'FT' . substr($year, -2) . $monthString;

        // Get next number - matching: SELECT NOM'+monthstring+' as NO_BUKTI FROM notrans WHERE trans='FTHDH'
        $notrans = DB::select(
            "SELECT NOM{$monthString} as NO_BUKTI FROM notrans WHERE trans = 'FTHDH' AND PER = ?",
            [$year]
        );
        $r1 = ($notrans[0]->NO_BUKTI ?? 0) + 1;

        // Update counter - matching: UPDATE notrans SET NOM'+monthstring+' =:r1 WHERE trans='FTHDH'
        DB::statement(
            "UPDATE notrans SET NOM{$monthString} = ? WHERE trans = 'FTHDH' AND PER = ?",
            [$r1, $year]
        );

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }
}
