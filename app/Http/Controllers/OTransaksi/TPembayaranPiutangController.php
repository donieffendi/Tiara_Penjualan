<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use App\Models\OTransaksi\Piu;
use App\Models\OTransaksi\Piud;
use App\Models\Master\Sup;
use App\Models\Master\Cust;
use App\Models\Master\Perid;
use App\Models\Master\Notrans;
use App\Models\Master\Toko;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TPembayaranPiutangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('otransaksi_PembayaranPiutang.index');
    }

    /**
     * Browse supplier with specific code
     */
    public function browse_hari(Request $request)
    {
        $kodes = $request->kodes;

        $sup = DB::connection('tgz')->SELECT("SELECT NO_ID, kodes, namas, ALAMAT, KOTA, NOTBAY, KONTAK, AKTIF,
                            CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                            PKP, HARI
                            FROM sup WHERE kodes = ?", [$kodes]);

        return response()->json($sup);
    }

    /**
     * Browse suppliers with search functionality
     */
    public function browse(Request $request)
    {
        $query = $request->q ?? '';

        if (!empty($query)) {
            $sup = DB::connection('tgz')->SELECT("SELECT NO_ID, kodes, namas, CONCAT(kodes,'-',namas) AS NAMAS2,
                                ALAMAT, KOTA, NOTBAY, KONTAK, AKTIF,
                                CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                                PKP, HARI
                                FROM sup WHERE namas <> '' AND namas LIKE ?
                                ORDER BY kodes", ["%{$query}%"]);
        } else {
            $sup = DB::connection('tgz')->SELECT("SELECT NO_ID, kodes, namas, CONCAT(kodes,'-',namas) AS NAMAS2,
                                ALAMAT, KOTA, NOTBAY, KONTAK, AKTIF,
                                CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                                PKP, HARI
                                FROM sup
                                WHERE namas <> ''
                                ORDER BY kodes");
        }

        return response()->json($sup);
    }

    /**
     * Browse customers with search functionality
     */
    public function browsesupz(Request $request)
    {
        $query = $request->q ?? '';

        $data = DB::connection('tgz')->SELECT("SELECT kodec as kodes, CONCAT(namac,'-',KOTA) AS namas
                            FROM cust
                            WHERE namac LIKE ?
                            ORDER BY namac LIMIT 30", ["%{$query}%"]);
        return response()->json($data);
    }

    /**
     * Browse accounts with search functionality
     */
    public function browseAccount(Request $request)
    {
        $search = $request->search ?? '';

        $accounts = DB::connection('tgz')->SELECT("
            SELECT acno, nama
            FROM account
            WHERE acno LIKE ? OR nama LIKE ?
            ORDER BY acno
            LIMIT 50
        ", ["%{$search}%", "%{$search}%"]);

        return response()->json($accounts);
    }

    /**
     * Get Pembayaran Piutang data for DataTables
     */
    public function getPembayaranPiutang()
    {
        $periode = session('periode');
        $flag = session('flag');
        $connection = strtolower($flag);

        $pembayaranPiutang = DB::connection($connection)->SELECT("SELECT * FROM piu
                                        WHERE PER = ?
                                        AND CBG = ?
                                        AND FLAG = 'PC'
                                        ORDER BY NO_BUKTI", [$periode, $flag]);

        return Datatables::of($pembayaranPiutang)
            ->addIndexColumn()
            ->editColumn('TGL', function ($row) {
                return date('d-m-Y', strtotime($row->TGL));
            })
            ->editColumn('JTEMPO', function ($row) {
                return date('d-m-Y', strtotime($row->JTEMPO));
            })
            ->editColumn('TOTAL', function ($row) {
                return number_format($row->TOTAL, 2, '.', ',');
            })
            ->editColumn('BAYAR', function ($row) {
                return number_format($row->BAYAR, 2, '.', ',');
            })
            ->editColumn('LAIN', function ($row) {
                return number_format($row->LAIN, 2, '.', ',');
            })
            ->editColumn('SISA', function ($row) {
                return number_format($row->SISA, 2, '.', ',');
            })
            ->addColumn('action', function ($row) {
                if (
                    Auth::user()->divisi == "programmer" || Auth::user()->divisi == "owner" ||
                    Auth::user()->divisi == "assistant" || Auth::user()->divisi == "accounting"
                ) {

                    $btnEdit = '';
                    $btnDelete = '';

                    if ($row->POSTED == 0) {
                        $btnEdit = '<a class="dropdown-item" href="TPembayaranPiutang/edit?idx=' . $row->NO_ID . '&tipx=edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>';

                        $url = "'" . url("TPembayaranPiutang/delete/" . $row->NO_ID) . "'";
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
     * Show the form for creating/editing a resource
     */
    public function edit(Request $request)
    {
        $tipx = $request->tipx;
        $idx = $request->idx;
        $periode = session('periode');

        // Handle navigation logic (top, prev, next, bottom, search)
        if ($idx == '0' && $tipx == 'undo') {
            $tipx = 'top';
        }

        $flag = session('flag');
        $connection = strtolower($flag);

        if ($tipx == 'search') {
            $kodex = $request->kodex;
            $bingco = DB::connection($connection)->SELECT("SELECT NO_ID, NO_BUKTI from piu
                                 WHERE NO_BUKTI = ? AND FLAG = 'PC'
                                 ORDER BY NO_BUKTI ASC LIMIT 1", [$kodex]);

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }

        // Navigation logic for top, prev, next, bottom
        if ($tipx == 'top') {
            $bingco = DB::connection($connection)->SELECT("SELECT NO_ID, NO_BUKTI from piu
                                 WHERE FLAG = 'PC' AND PER = ?
                                 ORDER BY NO_BUKTI ASC LIMIT 1", [$periode]);
            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }

        if ($tipx == 'prev') {
            $kodex = $request->kodex;
            $bingco = DB::connection($connection)->SELECT("SELECT NO_ID, NO_BUKTI from piu
                                 WHERE NO_BUKTI < ? AND FLAG = 'PC' AND PER = ?
                                 ORDER BY NO_BUKTI DESC LIMIT 1", [$kodex, $periode]);
            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            }
        }

        if ($tipx == 'next') {
            $kodex = $request->kodex;
            $bingco = DB::connection($connection)->SELECT("SELECT NO_ID, NO_BUKTI from piu
                                 WHERE NO_BUKTI > ? AND FLAG = 'PC' AND PER = ?
                                 ORDER BY NO_BUKTI ASC LIMIT 1", [$kodex, $periode]);
            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            }
        }

        if ($tipx == 'bottom') {
            $bingco = DB::connection($connection)->SELECT("SELECT NO_ID, NO_BUKTI from piu
                                 WHERE FLAG = 'PC' AND PER = ?
                                 ORDER BY NO_BUKTI DESC LIMIT 1", [$periode]);
            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }

        if ($tipx == 'undo' || $tipx == 'search') {
            $tipx = 'edit';
        }

        // Get data for edit or create new
        if ($idx != 0) {
            $header = DB::connection($connection)->SELECT("SELECT * FROM piu WHERE NO_ID = ?", [$idx])[0];
            $detail = DB::connection($connection)->SELECT("SELECT * FROM piud WHERE ID = ? ORDER BY REC ASC", [$idx]);
        } else {
            $header = (object) [
                'NO_BUKTI' => '',
                'TGL' => Carbon::now()->format('Y-m-d'),
                'JTEMPO' => Carbon::now()->format('Y-m-d'),
                'KODEC' => '',
                'NAMAC' => '',
                'ALAMAT' => '',
                'KOTA' => '',
                'ACNO' => '',
                'TBAYAR' => '',
                'NOTES' => '',
                'TOTAL' => 0,
                'BAYAR' => 0,
                'LAIN' => 0,
                'SISA' => 0
            ];
            $detail = [];
        }

        $data = [
            'header' => $header,
            'detail' => $detail,
        ];

        return view('otransaksi_PembayaranPiutang.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
    }

    /**
     * Store a newly created resource
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'KODEC' => 'required',
            'TGL' => 'required',
            'JTEMPO' => 'required'
        ]);

        $periode = session('periode');
        $flag = session('flag');
        $userName = Auth::user()->name;
        $connection = strtolower($flag);

        DB::connection($connection)->beginTransaction();

        try {
            // Check if period is closed
            $perid = DB::connection('tgz')->SELECT("SELECT posted FROM perid WHERE kd_peri = ?", [$periode])[0];
            if ($perid->posted == 1) {
                return response()->json(['error' => 'Period is closed'], 400);
            }

            // Generate document number if new
            if ($request['NO_BUKTI'] == '+' || empty($request['NO_BUKTI'])) {
                $month = substr($periode, 0, 2);
                $year = substr($periode, -2);

                // Get company type
                $toko = DB::connection('tgz')->SELECT("SELECT type FROM toko WHERE kode = ?", [$flag])[0];
                $kode2 = $toko->type;

                $kode = 'PC' . $year . $month;

                // Get next number
                $yearPart = substr($periode, -4);
                $notrans = DB::connection('tgz')->SELECT("SELECT NOM$month as NO_BUKTI FROM notrans
                                     WHERE trans = 'PIU' AND PER = ?", [$yearPart])[0];
                $r1 = $notrans->NO_BUKTI + 1;

                // Update number
                DB::connection('tgz')->statement("UPDATE notrans SET NOM$month = ?
                              WHERE trans = 'PIU' AND PER = ?", [$r1, $yearPart]);

                $bkt1 = sprintf('%04d', $r1);
                $noBukti = $kode . '-' . $bkt1 . $kode2;
            } else {
                $noBukti = $request['NO_BUKTI'];
            }

            // Calculate SISA based on logic: SISA = TOTAL - BAYAR + LAIN
            $sisa = ($request['TOTAL'] ?? 0) - ($request['BAYAR'] ?? 0) + ($request['LAIN'] ?? 0);

            // Insert/Update header
            $headerData = [
                'NO_BUKTI' => $noBukti,
                'TGL' => $request['TGL'],
                'JTEMPO' => $request['JTEMPO'],
                'PER' => $periode,
                'FLAG' => 'PC',
                'CBG' => $flag,
                'KODEC' => $request['KODEC'],
                'NAMAC' => $request['NAMAC'],
                'ALAMAT' => $request['ALAMAT'] ?? '',
                'KOTA' => $request['KOTA'] ?? '',
                'ACNO' => $request['ACNO'] ?? '',
                'TBAYAR' => $request['TBAYAR'] ?? '',
                'NOTES' => $request['NOTES'] ?? '',
                'TOTAL' => $request['TOTAL'] ?? 0,
                'BAYAR' => $request['BAYAR'] ?? 0,
                'LAIN' => $request['LAIN'] ?? 0,
                'SISA' => $sisa,
                'USRNM' => $userName,
                'TG_SMP' => Carbon::now(),
                'POSTED' => 0
            ];

            if (empty($request['edit_id'])) {
                // Insert new record
                DB::connection($connection)->table('piu')->insert($headerData);
            } else {
                // Update existing record - call PIUDEL first to clean up details
                DB::connection($connection)->statement("CALL PIUDEL(?)", [$noBukti]);
                DB::connection($connection)->table('piu')->where('NO_ID', $request['edit_id'])->update($headerData);
            }

            // Get header ID
            $headerId = DB::connection($connection)->SELECT("SELECT NO_ID FROM piu WHERE NO_BUKTI = ?", [$noBukti])[0]->NO_ID;

            // Handle detail records - delete existing if updating
            if (!empty($request['edit_id'])) {
                DB::connection($connection)->table('piud')->where('ID', $headerId)->delete();
            }

            // Insert detail records
            if (!empty($request['detail'])) {
                foreach ($request['detail'] as $index => $detail) {
                    if (!empty($detail['NO_FAKTUR']) || !empty($detail['URAIAN'])) {
                        // Calculate SISA for detail: SISA = TOTAL - BAYAR + LAIN
                        $detailSisa = ($detail['TOTAL'] ?? 0) - ($detail['BAYAR'] ?? 0) + ($detail['LAIN'] ?? 0);

                        DB::connection($connection)->table('piud')->insert([
                            'NO_BUKTI' => $noBukti,
                            'REC' => $index + 1,
                            'PER' => $periode,
                            'FLAG' => 'PC',
                            'NO_FAKTUR' => $detail['NO_FAKTUR'] ?? '',
                            'TGL_FAKTUR' => !empty($detail['TGL_FAKTUR']) ? $detail['TGL_FAKTUR'] : null,
                            'TOTAL' => $detail['TOTAL'] ?? 0,
                            'BAYAR' => $detail['BAYAR'] ?? 0,
                            'LAIN' => $detail['LAIN'] ?? 0,
                            'SISA' => $detailSisa,
                            'URAIAN' => $detail['URAIAN'] ?? '',
                            'ID' => $headerId
                        ]);
                    }
                }
            }

            // Call PIUINS stored procedure (equivalent to Delphi logic)
            DB::connection($connection)->statement("CALL PIUINS(?)", [$noBukti]);

            DB::connection($connection)->commit();

            return response()->json(['success' => 'Data saved successfully', 'no_bukti' => $noBukti]);
        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();
            return response()->json(['error' => 'Failed to save data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource
     */
    public function update(Request $request, $id)
    {
        $request['edit_id'] = $id;
        return $this->store($request);
    }

    /**
     * Remove the specified resource from storage
     */
    public function destroy($id)
    {
        $flag = session('flag');
        $connection = strtolower($flag);

        DB::connection($connection)->beginTransaction();

        try {
            // Check if record is posted
            $piu = DB::connection($connection)->SELECT("SELECT POSTED, NO_BUKTI FROM piu WHERE NO_ID = ?", [$id])[0];
            if ($piu->POSTED == 1) {
                return response()->json(['error' => 'Cannot delete posted record'], 400);
            }

            // Call PIUDEL stored procedure (equivalent to Delphi logic)
            DB::connection($connection)->statement("CALL PIUDEL(?)", [$piu->NO_BUKTI]);

            // Delete detail records
            DB::connection($connection)->table('piud')->where('ID', $id)->delete();

            // Delete header record
            DB::connection($connection)->table('piu')->where('NO_ID', $id)->delete();

            DB::connection($connection)->commit();

            return redirect('/TPembayaranPiutang')->with('status', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();
            return response()->json(['error' => 'Failed to delete data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check if customer exists
     */
    public function ceksup(Request $request)
    {
        $kodec = $request->kodec;
        $getItem = DB::connection('tgz')->SELECT('SELECT count(*) as ADA FROM cust WHERE kodec = ?', [$kodec]);
        return $getItem;
    }

    /**
     * Get customer data by code
     */
    public function getSelectKodes(Request $request)
    {
        $kodec = $request->kodec;

        $cust = DB::connection('tgz')->SELECT("SELECT kodec, namac, golongan, jenispjk, alamat, kota
                           FROM cust WHERE kodec = ?", [$kodec]);

        return response()->json($cust);
    }

    /**
     * Validate invoice number and get invoice data (from Delphi logic)
     */
    public function validateInvoice(Request $request)
    {
        $noFaktur = $request->no_faktur;
        $flag = session('flag');
        $connection = strtolower($flag);

        // Check invoice exists in jual tables (similar to Delphi union query)
        $invoice = DB::connection($connection)->SELECT(
            "SELECT * FROM jual WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual01 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual02 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual03 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual04 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual05 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual06 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual07 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual08 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual09 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual10 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual11 WHERE no_bukti = ?
                              UNION ALL SELECT * FROM jual12 WHERE no_bukti = ?",
            array_fill(0, 13, $noFaktur)
        );

        if (empty($invoice)) {
            return response()->json(['error' => 'Nomor invoice tidak ditemukan'], 400);
        }

        // Check if invoice already used in piud
        $existing = DB::connection($connection)->SELECT("SELECT no_bukti FROM piud WHERE no_faktur = ?", [$noFaktur]);

        if (!empty($existing)) {
            return response()->json(['error' => 'Nomor invoice sudah dibuatkan pembayaran di nomor: ' . $existing[0]->no_bukti], 400);
        }

        // Return invoice data
        $invoiceData = $invoice[0];
        return response()->json([
            'success' => true,
            'data' => [
                'no_faktur' => $invoiceData->no_bukti,
                'tgl_faktur' => $invoiceData->tgl,
                'total' => $invoiceData->totala,
                'sisa' => $invoiceData->totala
            ]
        ]);
    }
}
