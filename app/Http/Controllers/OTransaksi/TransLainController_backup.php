<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TransLainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = 'Transaksi Lain-lain';
    var $FLAGZ = 'TL';

    function setFlag(Request $request)
    {
        $this->judul = "Transaksi Lain-lain";
        $this->FLAGZ = 'TL';
    }

    public function index(Request $request)
    {
        $this->setFlag($request);
        return view("otransaksi_tlain.index")->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Display Transaksi Lain-lain data (equivalent to Tampil procedure in Delphi)
     */
    public function getTlain(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $flagz = $request->flagz ?? 'TL';

        try {
            $query = DB::table('beliz as h')
                ->select([
                    'h.NO_BUKTI',
                    'h.TGL',
                    'h.KODEC',
                    'h.NAMAC',
                    'h.NODOK',
                    'h.KET',
                    'h.TTOTAL',
                    'h.USRNM',
                    'h.TG_SMP',
                    'h.FLAG',
                    'h.POSTED'
                ])
                ->where('h.FLAG', $flagz)
                ->where('h.PER', $periode)
                ->orderBy('h.NO_BUKTI', 'asc');

            $data = $query->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<a class="btn btn-primary btn-sm" href="' . url("translain/edit?idx=" . $row->NO_BUKTI . "&tipx=edit&flagz=TL") . '">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-danger btn-sm" onclick="deleteTransLain(\'' . $row->NO_BUKTI . '\')">
                                <i class="fas fa-trash"></i>
                            </button>';
                })
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="check-item form-control" value="' . $row->NO_BUKTI . '" style="width: 20px; margin: 0 auto;">';
                })
                ->editColumn('TGL', function ($row) {
                    return date('d/m/Y', strtotime($row->TGL));
                })
                ->editColumn('TTOTAL', function ($row) {
                    return number_format($row->TTOTAL, 2, ',', '.');
                })
                ->rawColumns(['action', 'checkbox'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getTlain: ' . $e->getMessage());
            return response()->json(['error' => 'Error retrieving data'], 500);
        }
    }

    /**
     * Get detail data for Transaksi Lain-lain
     */
    public function getDetailTlain(Request $request)
    {
        try {
            $noBukti = $request->NO_BUKTI;

            $details = DB::table('belizd')
                ->select([
                    'REC',
                    'ACNO',
                    'NACNO',
                    'DEBET',
                    'KREDIT',
                    'KET'
                ])
                ->where('NO_BUKTI', $noBukti)
                ->orderBy('REC', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDetailTlain: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving detail data'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Implementation for storing new TransLain
        // Based on Delphi MSaveClick logic

        try {
            DB::beginTransaction();

            // Validation and save logic here
            // Will be implemented based on specific requirements

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $no_id = null)
    {
        // Implementation for edit form
        // Based on Delphi FormShow logic

        $this->setFlag($request);

        return view("otransaksi_tlain.edit")->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Posting function for TransLain
     */
    public function posting(Request $request)
    {
        try {
            $items = $request->items;
            $tglPosting = $request->tgl_posting;

            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items selected for posting'
                ]);
            }

            DB::beginTransaction();

            foreach ($items as $noBukti) {
                DB::table('beliz')
                    ->where('NO_BUKTI', $noBukti)
                    ->where('FLAG', 'TL')
                    ->update([
                        'POSTED' => 1,
                        'TG_SMP' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diposting'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in posting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error posting data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unposting function for TransLain
     */
    public function unposting(Request $request)
    {
        try {
            $items = $request->items;

            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items selected for unposting'
                ]);
            }

            DB::beginTransaction();

            foreach ($items as $noBukti) {
                DB::table('beliz')
                    ->where('NO_BUKTI', $noBukti)
                    ->where('FLAG', 'TL')
                    ->update([
                        'POSTED' => 0,
                        'TG_SMP' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil di-unpost'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in unposting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error unposting data: ' . $e->getMessage()
            ], 500);
        }
    }
}
