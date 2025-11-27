<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TPanitiaAcaraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('otransaksi_PanitiaAcara.index');
    }

    /**
     * Browse supplier with specific code (from Delphi browse_hari)
     */
    public function browse_hari(Request $request)
    {
        $kodes = $request->kodes;

        $sup = DB::SELECT("SELECT NO_ID, kd as kodes, dep as namas,
                            CONCAT(kd,'-',dep) AS NAMAS2
                            FROM nddafdep WHERE kd = '$kodes'");

        return response()->json($sup);
    }

    /**
     * Browse departments with search functionality (from Delphi browse)
     */
    public function browse(Request $request)
    {
        if (!empty(request('q'))) {
            $sup = DB::SELECT("SELECT NO_ID, kd as kodes, dep as namas,
                                CONCAT(kd,'-',dep) AS NAMAS2
                                FROM nddafdep WHERE dep <> '' AND dep LIKE ('%$request->q%')
                                ORDER BY kd");
        } else {
            $sup = DB::SELECT("SELECT NO_ID, kd as kodes, dep as namas,
                                CONCAT(kd,'-',dep) AS NAMAS2
                                FROM nddafdep
                                WHERE dep <> ''
                                ORDER BY kd");
        }

        return response()->json($sup);
    }

    /**
     * Browse customers with search functionality (keeping for compatibility)
     */
    public function browsesupz(Request $request)
    {
        $data = DB::SELECT("SELECT kodec as kodes, CONCAT(namac,'-',KOTA) AS namas
                            FROM cust
                            WHERE namac LIKE ('%$request->q%')
                            ORDER BY namac LIMIT 30");
        return response()->json($data);
    }

    /**
     * Get Panitia Acara data for DataTables (from Delphi tampil procedure)
     */
    public function getPanitiaAcara()
    {
        $periode = session('periode');
        $flag = session('flag');

        // Based on Delphi tampil procedure - filter with KET = 'ACARA'
        $panitiaAcara = DB::SELECT("SELECT no_bukti, na_brg, qty, satuan, ukuran, harga, cbg, kd_dept, per,
                                    total, batas1, tg_pbl, akunt, ket, tgl, dept, usrnm, panitia, seksi, kepl,
                                    (SELECT CASE WHEN posted = 1 THEN 1 ELSE 0 END) as posted
                                    FROM tpondg
                                    WHERE PER = '$periode' AND KET = 'ACARA'
                                    GROUP BY no_bukti
                                    ORDER BY no_bukti ASC");

        return Datatables::of($panitiaAcara)
            ->addIndexColumn()
            ->editColumn('tgl', function ($row) {
                return date('d-m-Y', strtotime($row->tgl));
            })
            ->editColumn('batas1', function ($row) {
                return $row->batas1 ? date('d-m-Y', strtotime($row->batas1)) : '';
            })
            ->editColumn('total', function ($row) {
                return number_format($row->total, 2, '.', ',');
            })
            ->editColumn('harga', function ($row) {
                return number_format($row->harga, 2, '.', ',');
            })
            ->addColumn('action', function ($row) {
                if (
                    Auth::user()->divisi == "programmer" || Auth::user()->divisi == "owner" ||
                    Auth::user()->divisi == "assistant" || Auth::user()->divisi == "accounting"
                ) {

                    $btnEdit = '';
                    $btnDelete = '';

                    // Check if current period matches data period (from Delphi logic)
                    $currentPeriod = DB::SELECT("SELECT CONCAT(LPAD(MONTH(NOW()),2,0),'/',YEAR(NOW())) as periy")[0]->periy;

                    if ($row->posted == 0 && $currentPeriod == $row->per) {
                        $btnEdit = '<a class="dropdown-item" href="TPanitiaAcara/edit?idx=' . $row->no_bukti . '&tipx=edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>';

                        $url = "'" . url("TPanitiaAcara/delete/" . $row->no_bukti) . "'";
                        $btnDelete = '<a class="dropdown-item btn btn-danger" onclick="deleteRow(' . $url . ')">
                                        <i class="fa fa-trash" aria-hidden="true"></i> Delete
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
                                ' . ($btnEdit && $btnDelete ? '<hr>' : '') . '
                                ' . $btnDelete . '
                            </div>
                        </div>';

                    return $actionBtn;
                } else {
                    return '';
                }
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show the form for creating/editing a resource (from Delphi FormShow logic)
     */
    public function edit(Request $request)
    {
        $tipx = $request->tipx;
        $idx = $request->idx;
        $periode = session('periode');

        // Handle navigation logic (similar to Delphi navigation)
        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';
        }

        if ($tipx == 'search') {
            $kodex = $request->kodex;
            $bingco = DB::SELECT("SELECT no_bukti from tpondg
                                 WHERE no_bukti = '$kodex' AND KET = 'ACARA'
                                 GROUP BY no_bukti
                                 ORDER BY no_bukti ASC LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->no_bukti;
            } else {
                $idx = '';
            }
        }

        // Navigation logic for top, prev, next, bottom
        if ($tipx == 'top') {
            $bingco = DB::SELECT("SELECT no_bukti from tpondg
                                 WHERE PER = '$periode' AND KET = 'ACARA'
                                 GROUP BY no_bukti
                                 ORDER BY no_bukti ASC LIMIT 1");
            if (!empty($bingco)) {
                $idx = $bingco[0]->no_bukti;
            } else {
                $idx = '';
            }
        }

        if ($tipx == 'prev' && !empty($idx)) {
            $bingco = DB::SELECT("SELECT no_bukti from tpondg
                                 WHERE no_bukti < '$idx' AND PER = '$periode' AND KET = 'ACARA'
                                 GROUP BY no_bukti
                                 ORDER BY no_bukti DESC LIMIT 1");
            if (!empty($bingco)) {
                $idx = $bingco[0]->no_bukti;
            }
        }

        if ($tipx == 'next' && !empty($idx)) {
            $bingco = DB::SELECT("SELECT no_bukti from tpondg
                                 WHERE no_bukti > '$idx' AND PER = '$periode' AND KET = 'ACARA'
                                 GROUP BY no_bukti
                                 ORDER BY no_bukti ASC LIMIT 1");
            if (!empty($bingco)) {
                $idx = $bingco[0]->no_bukti;
            }
        }

        if ($tipx == 'bottom') {
            $bingco = DB::SELECT("SELECT no_bukti from tpondg
                                 WHERE PER = '$periode' AND KET = 'ACARA'
                                 GROUP BY no_bukti
                                 ORDER BY no_bukti DESC LIMIT 1");
            if (!empty($bingco)) {
                $idx = $bingco[0]->no_bukti;
            } else {
                $idx = '';
            }
        }

        if ($tipx == 'undo' || $tipx == 'search') {
            $tipx = 'edit';
        }

        // Get data for edit or create new (based on Delphi FormShow logic)
        if (!empty($idx)) {
            $header = DB::SELECT("SELECT no_bukti, tgl, notes, kd_dept, dept, usrnm, panitia, seksi, kepl,
                                    SUM(total) as total,
                                    (SELECT CASE WHEN posted = 1 THEN 1 ELSE 0 END) as posted
                                    FROM tpondg
                                    WHERE no_bukti = '$idx' AND KET = 'ACARA'
                                    GROUP BY no_bukti")[0];

            $detail = DB::SELECT("SELECT no_id, na_brg, ket, total, satuan, ukuran, akunt,
                                   qty, harga, batas1, batas2, tg_pbl, merk
                                   FROM tpondg
                                   WHERE no_bukti = '$idx' AND KET = 'ACARA'
                                   ORDER BY no_id");
        } else {
            $header = (object) [
                'no_bukti' => '+',
                'tgl' => Carbon::now()->format('Y-m-d'),
                'notes' => '',
                'kd_dept' => '',
                'dept' => '',
                'total' => 0,
                'usrnm' => '',
                'panitia' => '',
                'seksi' => '',
                'kepl' => '',
                'posted' => 0
            ];
            $detail = [];
        }

        $data = [
            'header' => $header,
            'detail' => $detail,
        ];

        return view('otransaksi_PanitiaAcara.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Validation logic (from Delphi checkx procedure)
     */
    private function validateData(Request $request)
    {
        $periode = session('periode');
        $tgl = $request['TGL'];

        // Check period validation (from Delphi checkx)
        $date = Carbon::parse($tgl);
        $monthz = str_pad($date->month, 2, '0', STR_PAD_LEFT);
        $yearz = $date->year;

        $periodeMonth = substr($periode, 0, 2);
        $periodeYear = substr($periode, -4);

        if ($monthz != $periodeMonth) {
            return ['error' => 'Bulan tidak sesuai dengan periode.'];
        }

        if ($yearz != $periodeYear) {
            return ['error' => 'Tahun tidak sesuai dengan periode.'];
        }

        if (empty($request['KD_DEPT'])) {
            return ['error' => 'Departemen Kosong.'];
        }

        return ['success' => true];
    }

    /**
     * Store a newly created resource (based on Delphi MSaveClick)
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'KD_DEPT' => 'required',
            'TGL' => 'required'
        ]);

        $periode = session('periode');
        $flag = session('flag');
        $userName = Auth::user()->name;

        // Validate data
        $validation = $this->validateData($request);
        if (isset($validation['error'])) {
            return response()->json(['error' => $validation['error']], 400);
        }

        DB::beginTransaction();

        try {
            // Check if period is closed (from Delphi logic)
            $perid = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = '$periode'")[0];
            if ($perid->posted == 1) {
                return response()->json(['error' => 'Periode sudah ditutup'], 400);
            }

            // Generate document number if new (from Delphi logic)
            if ($request['NO_BUKTI'] == '+' || empty($request['NO_BUKTI'])) {
                $month = substr($periode, 0, 2);
                $year = substr($periode, -2);

                // Get company type
                $toko = DB::SELECT("SELECT type FROM toko WHERE kode = '$flag'")[0];
                $kode2 = $toko->type;

                $kode = 'ND' . $year . $month;

                // Get next number
                $notrans = DB::SELECT("SELECT NOM$month as NO_BUKTI FROM notrans
                                     WHERE trans = 'TJASA' AND PER = '" . substr($periode, -4) . "'")[0];
                $r1 = $notrans->NO_BUKTI + 1;

                // Update number
                DB::statement("UPDATE notrans SET NOM$month = $r1
                              WHERE trans = 'TJASA' AND PER = '" . substr($periode, -4) . "'");

                $bkt1 = sprintf('%04d', $r1);
                $noBukti = $kode . '-' . $bkt1 . $kode2;
            } else {
                $noBukti = $request['NO_BUKTI'];

                // If editing, delete existing records first
                DB::table('tpondg')->where('no_bukti', $noBukti)->where('ket', 'ACARA')->delete();
            }

            // Insert detail records (from Delphi logic with PANITIA, SEKSI, KEPL fields)
            if (!empty($request['detail'])) {
                foreach ($request['detail'] as $index => $detail) {
                    if (!empty($detail['NA_BRG']) && ($detail['QTY'] ?? 0) != 0) {
                        // Calculate total (from Delphi hitung procedure)
                        $total = ($detail['QTY'] ?? 0) * ($detail['HARGA'] ?? 0);

                        DB::table('tpondg')->insert([
                            'NO_BUKTI' => $noBukti,
                            'TGL' => $request['TGL'],
                            'NOTES' => strtoupper($request['NOTES'] ?? ''),
                            'KD_DEPT' => $request['KD_DEPT'],
                            'DEPT' => $request['DEPT'] ?? '',
                            'PER' => $periode,
                            'CBG' => $flag,
                            'USRNM' => $userName,
                            'NA_BRG' => strtoupper($detail['NA_BRG'] ?? ''),
                            'QTY' => $detail['QTY'] ?? 0,
                            'SATUAN' => strtoupper($detail['SATUAN'] ?? ''),
                            'UKURAN' => strtoupper($detail['UKURAN'] ?? ''),
                            'MERK' => strtoupper($detail['MERK'] ?? ''),
                            'HARGA' => $detail['HARGA'] ?? 0,
                            'TOTAL' => $total,
                            'BATAS1' => !empty($detail['BATAS1']) ? $detail['BATAS1'] : null,
                            'BATAS2' => !empty($detail['BATAS2']) ? $detail['BATAS2'] : null,
                            'AKUNT' => strtoupper($detail['AKUNT'] ?? ''),
                            'KET' => 'ACARA', // Fixed value as per Delphi code
                            'PANITIA' => strtoupper($request['PANITIA'] ?? ''),
                            'SEKSI' => strtoupper($request['SEKSI'] ?? ''),
                            'KEPL' => strtoupper($request['KEPL'] ?? ''),
                            'TG_SMP' => Carbon::now(),
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

    /**
     * Update the specified resource (similar to store but for editing)
     */
    public function update(Request $request, $noBukti)
    {
        $request['NO_BUKTI'] = $noBukti;

        $periode = session('periode');
        $flag = session('flag');
        $userName = Auth::user()->name;

        // Validate data
        $validation = $this->validateData($request);
        if (isset($validation['error'])) {
            return response()->json(['error' => $validation['error']], 400);
        }

        DB::beginTransaction();

        try {
            // Check if period is closed
            $perid = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = '$periode'")[0];
            if ($perid->posted == 1) {
                return response()->json(['error' => 'Periode sudah ditutup'], 400);
            }

            // Get existing record IDs from database
            $existingRecords = DB::SELECT("SELECT no_id FROM tpondg WHERE no_bukti = ? AND KET = 'ACARA'", [$noBukti]);
            $existingIds = collect($existingRecords)->pluck('no_id')->toArray();

            // Get IDs from form data
            $formIds = [];
            if (!empty($request['detail'])) {
                foreach ($request['detail'] as $detail) {
                    if (!empty($detail['NO_ID']) && $detail['NO_ID'] != 0) {
                        $formIds[] = $detail['NO_ID'];
                    }
                }
            }

            // Delete records that exist in DB but not in form (from Delphi update logic)
            $idsToDelete = array_diff($existingIds, $formIds);
            if (!empty($idsToDelete)) {
                DB::table('tpondg')->whereIn('no_id', $idsToDelete)->delete();
            }

            // Update or insert detail records
            if (!empty($request['detail'])) {
                foreach ($request['detail'] as $index => $detail) {
                    if (!empty($detail['NA_BRG']) && ($detail['QTY'] ?? 0) != 0) {
                        $total = ($detail['QTY'] ?? 0) * ($detail['HARGA'] ?? 0);

                        $dataToSave = [
                            'TGL' => $request['TGL'],
                            'NOTES' => strtoupper($request['NOTES'] ?? ''),
                            'KD_DEPT' => $request['KD_DEPT'],
                            'DEPT' => $request['DEPT'] ?? '',
                            'USRNM' => $userName,
                            'NA_BRG' => strtoupper($detail['NA_BRG'] ?? ''),
                            'QTY' => $detail['QTY'] ?? 0,
                            'SATUAN' => strtoupper($detail['SATUAN'] ?? ''),
                            'UKURAN' => strtoupper($detail['UKURAN'] ?? ''),
                            'MERK' => strtoupper($detail['MERK'] ?? ''),
                            'HARGA' => $detail['HARGA'] ?? 0,
                            'TOTAL' => $total,
                            'BATAS1' => !empty($detail['BATAS1']) ? $detail['BATAS1'] : null,
                            'AKUNT' => strtoupper($detail['AKUNT'] ?? ''),
                            'PANITIA' => strtoupper($request['PANITIA'] ?? ''),
                            'SEKSI' => strtoupper($request['SEKSI'] ?? ''),
                            'KEPL' => strtoupper($request['KEPL'] ?? ''),
                            'TG_SMP' => Carbon::now(),
                        ];

                        if (!empty($detail['NO_ID']) && $detail['NO_ID'] != 0) {
                            // Update existing record
                            DB::table('tpondg')
                                ->where('no_id', $detail['NO_ID'])
                                ->update($dataToSave);
                        } else {
                            // Insert new record
                            $dataToSave = array_merge($dataToSave, [
                                'NO_BUKTI' => $noBukti,
                                'PER' => $periode,
                                'CBG' => $flag,
                                'KET' => 'ACARA',
                            ]);
                            DB::table('tpondg')->insert($dataToSave);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json(['success' => 'Data berhasil diupdate', 'no_bukti' => $noBukti]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal mengupdate data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage (from Delphi delete logic)
     */
    public function destroy($noBukti)
    {
        DB::beginTransaction();

        try {
            // Check if record exists and is not posted
            $record = DB::SELECT("SELECT COUNT(*) as count,
                                   MAX(CASE WHEN EXISTS(SELECT 1 FROM perid WHERE kd_peri = tpondg.per AND posted = 1)
                                       THEN 1 ELSE 0 END) as posted
                                   FROM tpondg WHERE no_bukti = ? AND KET = 'ACARA'", [$noBukti])[0];

            if ($record->count == 0) {
                return response()->json(['error' => 'Data tidak ditemukan'], 400);
            }

            if ($record->posted == 1) {
                return response()->json(['error' => 'Data sudah diposting, tidak dapat dihapus'], 400);
            }

            // Check current period matches (from Delphi period validation)
            $currentPeriod = DB::SELECT("SELECT CONCAT(LPAD(MONTH(NOW()),2,0),'/',YEAR(NOW())) as periy")[0]->periy;
            $dataPeriod = DB::SELECT("SELECT per FROM tpondg WHERE no_bukti = ? AND KET = 'ACARA' LIMIT 1", [$noBukti])[0]->per;

            if ($currentPeriod != $dataPeriod) {
                return response()->json(['error' => 'Hanya dapat menghapus data periode berjalan'], 400);
            }

            // Delete all records with this no_bukti and KET = 'ACARA'
            DB::table('tpondg')->where('no_bukti', $noBukti)->where('ket', 'ACARA')->delete();

            DB::commit();

            return redirect('/TPanitiaAcara')->with('status', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check if department exists (from Delphi txtkodesExit)
     */
    public function ceksup(Request $request)
    {
        $getItem = DB::SELECT('SELECT count(*) as ADA FROM nddafdep WHERE kd = "' . $request->kodes . '"');
        return $getItem;
    }

    /**
     * Get department data by code (from Delphi txtkodesExit logic)
     */
    public function getSelectKodes(Request $request)
    {
        $kodes = $request->kodes;

        $dept = DB::SELECT("SELECT kd as kodes, dep as namas
                           FROM nddafdep WHERE kd = '$kodes'");

        return response()->json($dept);
    }

    /**
     * Calculate totals (from Delphi hitung procedure) - for AJAX calls
     */
    public function calculateTotals(Request $request)
    {
        $details = $request->details ?? [];
        $grandTotal = 0;

        foreach ($details as &$detail) {
            if (isset($detail['QTY']) && isset($detail['HARGA'])) {
                $detail['TOTAL'] = $detail['QTY'] * $detail['HARGA'];
                $grandTotal += $detail['TOTAL'];
            }
        }

        return response()->json([
            'details' => $details,
            'grand_total' => $grandTotal
        ]);
    }
}
