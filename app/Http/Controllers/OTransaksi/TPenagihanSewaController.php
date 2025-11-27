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

class TPenagihanSewaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('otransaksi_PenagihanSewa.index');
    }

    /**
     * Browse supplier with specific code
     */
    public function browse_hari(Request $request)
    {
        $kodes = $request->kodes;

        $sup = DB::SELECT("SELECT NO_ID, kodes, namas, ALAMAT, KOTA, NOTBAY, KONTAK, AKTIF,
                            CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                            PKP, HARI
                            FROM sup WHERE kodes = '$kodes'");

        return response()->json($sup);
    }

    /**
     * Browse suppliers with search functionality
     */
    public function browse(Request $request)
    {
        if (!empty(request('q'))) {
            $sup = DB::SELECT("SELECT NO_ID, kodes, namas, CONCAT(kodes,'-',namas) AS NAMAS2,
                                ALAMAT, KOTA, NOTBAY, KONTAK, AKTIF,
                                CASE WHEN PKP = '1' THEN '(PKP)' ELSE '(NON PKP)' END AS PKP2,
                                PKP, HARI
                                FROM sup WHERE namas <> '' AND namas LIKE ('%$request->q%')
                                ORDER BY kodes");
        } else {
            $sup = DB::SELECT("SELECT NO_ID, kodes, namas, CONCAT(kodes,'-',namas) AS NAMAS2,
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
        $data = DB::SELECT("SELECT kodec as kodes, CONCAT(namac,'-',KOTA) AS namas
                            FROM cust
                            WHERE namac LIKE ('%$request->q%')
                            ORDER BY namac LIMIT 30");
        return response()->json($data);
    }

    /**
     * Get Penagihan Sewa data for DataTables
     */
    public function getPenagihanSewa()
    {
        $periode = session('periode');
        $flag = session('flag');

        $penagihanSewa = DB::SELECT("SELECT * FROM piu
                                    WHERE PER = '$periode'
                                    AND CBG = '$flag'
                                    AND FLAG = 'PS'
                                    ORDER BY NO_BUKTI");

        return Datatables::of($penagihanSewa)
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
                        $btnEdit = '<a class="dropdown-item" href="tpenagihansewa/edit?idx=' . $row->NO_ID . '&tipx=edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>';

                        $url = "'" . url("tpenagihansewa/delete/" . $row->NO_ID) . "'";
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

        if ($tipx == 'search') {
            $kodex = $request->kodex;
            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from piu
                                 WHERE NO_BUKTI = '$kodex' AND FLAG = 'PS'
                                 ORDER BY NO_BUKTI ASC LIMIT 1");

            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }

        // Navigation logic for top, prev, next, bottom
        if ($tipx == 'top') {
            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from piu
                                 WHERE FLAG = 'PS' AND PER = '$periode'
                                 ORDER BY NO_BUKTI ASC LIMIT 1");
            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            } else {
                $idx = 0;
            }
        }

        if ($tipx == 'prev') {
            $kodex = $request->kodex;
            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from piu
                                 WHERE NO_BUKTI < '$kodex' AND FLAG = 'PS' AND PER = '$periode'
                                 ORDER BY NO_BUKTI DESC LIMIT 1");
            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            }
        }

        if ($tipx == 'next') {
            $kodex = $request->kodex;
            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from piu
                                 WHERE NO_BUKTI > '$kodex' AND FLAG = 'PS' AND PER = '$periode'
                                 ORDER BY NO_BUKTI ASC LIMIT 1");
            if (!empty($bingco)) {
                $idx = $bingco[0]->NO_ID;
            }
        }

        if ($tipx == 'bottom') {
            $bingco = DB::SELECT("SELECT NO_ID, NO_BUKTI from piu
                                 WHERE FLAG = 'PS' AND PER = '$periode'
                                 ORDER BY NO_BUKTI DESC LIMIT 1");
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
            $header = DB::SELECT("SELECT * FROM piu WHERE NO_ID = $idx")[0];
            $detail = DB::SELECT("SELECT * FROM piud WHERE ID = $idx ORDER BY REC ASC");
        } else {
            $header = (object) [
                'NO_BUKTI' => '',
                'TGL' => Carbon::now()->format('Y-m-d'),
                'JTEMPO' => Carbon::now()->format('Y-m-d'),
                'KODEC' => '',
                'NAMAC' => '',
                'ALAMAT' => '',
                'KOTA' => '',
                'GOL' => '',
                'TYP' => '',
                'NOTES' => '',
                'DPP' => 0,
                'PPN' => 0,
                'PPH1' => 0,
                'PPH2' => 0,
                'TOTAL' => 0,
                'PRODUK' => '',
                'TBAYAR' => ''
            ];
            $detail = [];
        }

        $data = [
            'header' => $header,
            'detail' => $detail,
        ];

        return view('otransaksi_PenagihanSewa.edit', $data)->with(['tipx' => $tipx, 'idx' => $idx]);
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

        DB::beginTransaction();

        try {
            // Check if period is closed
            $perid = DB::SELECT("SELECT posted FROM perid WHERE kd_peri = '$periode'")[0];
            if ($perid->posted == 1) {
                return response()->json(['error' => 'Period is closed'], 400);
            }

            // Generate document number if new
            if ($request['NO_BUKTI'] == '+' || empty($request['NO_BUKTI'])) {
                $month = substr($periode, 0, 2);
                $year = substr($periode, -2);

                // Get company type
                $toko = DB::SELECT("SELECT type FROM toko WHERE kode = '$flag'")[0];
                $kode2 = $toko->type;

                $kode = 'PS' . $year . $month;

                // Get next number
                $notrans = DB::SELECT("SELECT NOM$month as NO_BUKTI FROM notrans
                                     WHERE trans = 'PIUSEWA' AND PER = '" . substr($periode, -4) . "'")[0];
                $r1 = $notrans->NO_BUKTI + 1;

                // Update number
                DB::statement("UPDATE notrans SET NOM$month = $r1
                              WHERE trans = 'PIUSEWA' AND PER = '" . substr($periode, -4) . "'");

                $bkt1 = sprintf('%04d', $r1);
                $noBukti = $kode . '-' . $bkt1 . $kode2;
            } else {
                $noBukti = $request['NO_BUKTI'];
            }

            // Insert/Update header
            $headerData = [
                'NO_BUKTI' => $noBukti,
                'TGL' => $request['TGL'],
                'JTEMPO' => $request['JTEMPO'],
                'PER' => $periode,
                'FLAG' => 'PS',
                'CBG' => $flag,
                'KODEC' => $request['KODEC'],
                'NAMAC' => $request['NAMAC'],
                'ALAMAT' => $request['ALAMAT'] ?? '',
                'KOTA' => $request['KOTA'] ?? '',
                'GOL' => $request['GOL'] ?? '',
                'TYP' => $request['TYP'] ?? '',
                'NOTES' => $request['NOTES'] ?? '',
                'DPP' => $request['DPP'] ?? 0,
                'PPN' => $request['PPN'] ?? 0,
                'PPH1' => $request['PPH1'] ?? 0,
                'PPH2' => $request['PPH2'] ?? 0,
                'TOTAL' => $request['TOTAL'] ?? 0,
                'SISA' => $request['TOTAL'] ?? 0,
                'PRODUK' => $request['PRODUK'] ?? '',
                'TBAYAR' => $request['TBAYAR'] ?? '',
                'USRNM' => $userName,
                'TG_SMP' => Carbon::now(),
                'POSTED' => 0
            ];

            if (empty($request['edit_id'])) {
                // Insert new record
                DB::table('piu')->insert($headerData);
            } else {
                // Update existing record
                DB::table('piu')->where('NO_ID', $request['edit_id'])->update($headerData);
            }

            // Get header ID
            $headerId = DB::SELECT("SELECT NO_ID FROM piu WHERE NO_BUKTI = '$noBukti'")[0]->NO_ID;

            // Handle detail records
            if (!empty($request['edit_id'])) {
                // Delete existing detail records
                DB::table('piud')->where('ID', $headerId)->delete();
            }

            // Insert detail records
            if (!empty($request['detail'])) {
                foreach ($request['detail'] as $index => $detail) {
                    if (!empty($detail['URAIAN']) && !empty($detail['TOTAL'])) {
                        DB::table('piud')->insert([
                            'NO_BUKTI' => $noBukti,
                            'REC' => $index + 1,
                            'PER' => $periode,
                            'FLAG' => 'PS',
                            'URAIAN' => $detail['URAIAN'],
                            'TOTAL' => $detail['TOTAL'],
                            'ID' => $headerId
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json(['success' => 'Data saved successfully', 'no_bukti' => $noBukti]);
        } catch (\Exception $e) {
            DB::rollBack();
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
        DB::beginTransaction();

        try {
            // Check if record is posted
            $piu = DB::SELECT("SELECT POSTED FROM piu WHERE NO_ID = $id")[0];
            if ($piu->POSTED == 1) {
                return response()->json(['error' => 'Cannot delete posted record'], 400);
            }

            // Delete detail records
            DB::table('piud')->where('ID', $id)->delete();

            // Delete header record
            DB::table('piu')->where('NO_ID', $id)->delete();

            DB::commit();

            return redirect('/tpenagihansewa')->with('status', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check if customer exists
     */
    public function ceksup(Request $request)
    {
        $getItem = DB::SELECT('SELECT count(*) as ADA FROM cust WHERE kodec = "' . $request->kodec . '"');
        return $getItem;
    }

    /**
     * Get customer data by code
     */
    public function getSelectKodes(Request $request)
    {
        $kodec = $request->kodec;

        $cust = DB::SELECT("SELECT kodec, namac, golongan, jenispjk, alamat, kota
                           FROM cust WHERE kodec = '$kodec'");

        return response()->json($cust);
    }

    /**
     * Get PPN rate
     */
    public function getPPNRate()
    {
        $ppn = DB::SELECT("SELECT * FROM ppn WHERE aktif = 1 LIMIT 1");
        return response()->json($ppn);
    }
}
