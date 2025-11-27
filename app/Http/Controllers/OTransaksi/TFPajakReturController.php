<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TFPajakReturController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    var $judul = 'Faktur Pajak Retur';
    var $FLAGZ = 'FR';
    var $koderetur = 'SPM'; // Default SPM, bisa BSN
    var $tabelBrg = 'brg';
    var $tabelSup = 'sup';
    var $tabelRetur = 'beliz';
    var $fieldRef = 'REF';
    var $flagFaktur = 'FR';
    var $flagretur = 'RB';

    function setFlag(Request $request)
    {
        $koderetur = $request->get('koderetur', 'SPM');

        if ($koderetur == 'SPM') {
            $this->judul = "Faktur Pajak Retur Supermarket";
            $this->FLAGZ = 'FR';
            $this->koderetur = 'SPM';
            $this->tabelBrg = 'brg';
            $this->tabelSup = 'sup';
            $this->tabelRetur = 'beliz';
            $this->fieldRef = 'REF';
            $this->flagFaktur = 'FR';
            $this->flagretur = 'RB';
        } elseif ($koderetur == 'BSN') {
            $this->judul = "Faktur Pajak Retur Busana";
            $this->FLAGZ = 'FB';
            $this->koderetur = 'BSN';
            $this->tabelBrg = 'brgbsn';
            $this->tabelSup = 'supbsn';
            $this->tabelRetur = 'belibsnz';
            $this->fieldRef = 'REF_PJK';
            $this->flagFaktur = 'FB';
            $this->flagretur = 'RX';
        }
    }

    public function index(Request $request)
    {
        // Get koderetur from request, default to SPM
        $koderetur = $request->get('koderetur', 'SPM');

        // Set session for koderetur
        Session::put('koderetur', $koderetur);

        // Call setFlag to configure the appropriate settings
        $this->setFlag($request);

        // Set flagz based on koderetur
        if ($koderetur == 'SPM') {
            $flagz = 'SPM';
            $judul = 'Faktur Pajak Retur Supermarket';
        } else { // BSN
            $flagz = 'BSN';
            $judul = 'Faktur Pajak Retur Busana';
        }

        // Set session for flagz
        Session::put('flagz', $flagz);

        return view("otransaksi_tfpajakretur.index")->with([
            'judul' => $judul,
            'flagz' => $flagz,
            'koderetur' => $koderetur,
        ]);
    }

    /**
     * Get data for DataTables (main table)
     */
    public function getdata(Request $request)
    {
        // Get flagz from session or request
        $flagz = Session::get('flagz', 'SPM');
        $koderetur = Session::get('koderetur', 'SPM');

        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        // Set connection and tables based on flagz
        if ($flagz === 'BSN') {
            $connection = 'mysql2';
            $tableName = 'belibsnz';
        } else {
            $connection = 'mysql';
            $tableName = 'beliz';
        }

        try {
            $query = DB::connection($connection)->table($tableName . ' as b')
                ->select([
                    'b.no_bukti',
                    'b.no_fak01 as no_faktur',
                    'b.tgl',
                    'b.kodes as kodep',
                    'b.namas as namac',
                    'b.usrnm',
                    'b.total'
                ])
                ->whereRaw("LEFT(b.no_bukti, 2) = 'RT'")
                ->orderBy('b.tgl', 'desc')
                ->orderBy('b.no_bukti', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<a class="btn btn-primary btn-sm" href="' . url("tfpajakretur/edit?idx=" . $row->no_bukti . "&tipx=edit") . '">
                                <i class="fas fa-edit"></i>
                            </a>';
                })
                ->addColumn('delete', function ($row) {
                    return '<button class="btn btn-danger btn-sm" onclick="deleteData(\'' . $row->no_bukti . '\')">
                                <i class="fas fa-trash"></i>
                            </button>';
                })
                ->addColumn('check', function ($row) {
                    return '<input type="checkbox" class="check-item form-control" value="' . $row->no_bukti . '" style="width: 20px; margin: 0 auto;">';
                })
                ->editColumn('tgl', function ($row) {
                    return date('d/m/Y', strtotime($row->tgl));
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->total, 0, ',', '.');
                })
                ->rawColumns(['action', 'delete', 'check'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getdata: ' . $e->getMessage());
            return response()->json(['error' => 'Error retrieving data'], 500);
        }
    }

    /**
     * Display Faktur Pajak Retur data (equivalent to Tampil procedure in Delphi)
     */
    public function getFakturRetur(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $this->setFlag($request);

        try {
            $query = DB::table('notapjk')
                ->select([
                    'no_bukti',
                    'no_pjk',
                    'tgl',
                    'tgl_buat',
                    'kodes',
                    'namas',
                    'per',
                    'total',
                    'ppn',
                    'nett',
                    'prom',
                    'usrnm',
                    'flag',
                    'cbg'
                ])
                ->where('flag', $this->flagFaktur)
                ->where('per', $periode)
                ->orderBy('no_bukti', 'desc');

            $data = $query->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<a class="btn btn-primary btn-sm" href="' . url("tfpajakretur/edit?idx=" . $row->no_bukti . "&tipx=edit&flagz=" . $this->FLAGZ) . '">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-info btn-sm" onclick="printFaktur(\'' . $row->no_bukti . '\')">
                                <i class="fas fa-print"></i>
                            </button>';
                })
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="check-item form-control" value="' . $row->no_bukti . '" style="width: 20px; margin: 0 auto;">';
                })
                ->editColumn('tgl', function ($row) {
                    return date('d/m/Y', strtotime($row->tgl));
                })
                ->editColumn('tgl_buat', function ($row) {
                    return date('d/m/Y', strtotime($row->tgl_buat));
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->total, 2, ',', '.');
                })
                ->editColumn('ppn', function ($row) {
                    return number_format($row->ppn, 2, ',', '.');
                })
                ->editColumn('nett', function ($row) {
                    return number_format($row->nett, 2, ',', '.');
                })
                ->rawColumns(['action', 'checkbox'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getFakturRetur: ' . $e->getMessage());
            return response()->json(['error' => 'Error retrieving data'], 500);
        }
    }

    /**
     * Get Supplier Retur data based on supplier code (equivalent to Button1Click in Delphi)
     */
    public function getSupplierRetur(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $this->setFlag($request);
        $kodes = $request->kodes;

        if (empty($kodes)) {
            return response()->json([
                'success' => false,
                'message' => 'Isikan Supplier'
            ]);
        }

        try {
            $query = null;

            if ($this->koderetur == 'SPM') {
                $query = DB::table('beliz')
                    ->join('sup', 'beliz.kodes', '=', 'sup.kodes')
                    ->select([
                        'beliz.NO_BUKTI',
                        'beliz.no_tagi',
                        'beliz.kodes',
                        'sup.namas',
                        'beliz.tgl',
                        'beliz.total',
                        'beliz.prom',
                        'beliz.ppn',
                        'beliz.nett'
                    ])
                    ->where('beliz.flag', $this->flagretur)
                    ->where('beliz.REF', '')
                    ->where('beliz.kodes', $kodes)
                    ->where('sup.GOLONGAN', 'P1');
            } elseif ($this->koderetur == 'BSN') {
                $query = DB::table('belibsnz')
                    ->join('supbsn', 'belibsnz.kodes', '=', 'supbsn.kodes')
                    ->leftJoin('cntbsn', 'supbsn.kodes', '=', 'cntbsn.sup')
                    ->select([
                        'belibsnz.NO_BUKTI',
                        'belibsnz.no_tagi',
                        'belibsnz.kodes',
                        'supbsn.namas',
                        'belibsnz.tgl',
                        'belibsnz.total',
                        'belibsnz.prom',
                        'belibsnz.ppn',
                        'belibsnz.nett'
                    ])
                    ->where('belibsnz.flag', $this->flagretur)
                    ->where('belibsnz.ref_pjk', '')
                    ->where('belibsnz.kodes', $kodes)
                    ->where('cntbsn.st_pjk', 'P1');
            }

            $data = $query->get();

            // Get supplier info
            $supplierInfo = null;
            if ($data->count() > 0) {
                $supplierInfo = [
                    'kodes' => $data->first()->kodes,
                    'namas' => $data->first()->namas
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'supplier' => $supplierInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getSupplierRetur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving supplier data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if faktur already exists (equivalent to txtcekfakturKeyDown in Delphi)
     */
    public function checkFaktur(Request $request)
    {
        $this->setFlag($request);
        $noBukti = $request->no_bukti;

        try {
            $faktur = DB::table($this->tabelRetur)
                ->select($this->fieldRef . ' as REF')
                ->where('flag', $this->flagretur)
                ->where('NO_BUKTI', $noBukti)
                ->first();

            if ($faktur && !empty($faktur->REF)) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'faktur' => $faktur->REF,
                    'message' => 'Sudah ada faktur: ' . $faktur->REF
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'message' => 'Belum ada faktur'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in checkFaktur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking faktur: ' . $e->getMessage()
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
     * Store newly created faktur pajak retur (equivalent to txtpajakKeyPress in Delphi)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $this->setFlag($request);

        $noPajak = trim($request->no_pajak);
        $tglFaktur = $request->tgl_faktur;
        $selectedItems = $request->selected_items ?? [];
        $kodes = $request->kodes;
        $namas = $request->namas;

        if (empty($noPajak)) {
            return response()->json([
                'success' => false,
                'message' => 'Isikan Nomor Faktur Pajak!!!'
            ]);
        }

        if (empty($selectedItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal satu item untuk diproses'
            ]);
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $total = 0;
            $bprom = 0;
            $ppn = 0;
            $nett = 0;

            foreach ($selectedItems as $item) {
                $total += floatval($item['total']);
                $bprom += floatval($item['prom']);
                $ppn += floatval($item['ppn']);
                $nett += floatval($item['nett']);
            }

            // Get cabang info
            $cbg = DB::table('toko')
                ->select('kode')
                ->where('STA', 'MA')
                ->first();

            if (!$cbg) {
                throw new \Exception('Data cabang tidak ditemukan');
            }

            // Generate new bukti number using stored procedure
            $newBukti = DB::select('CALL tgz.adm_fakturretur("NEW_BUKTI", ?, "", "", ?, ?, "")', [
                session('user.cabang', $cbg->kode),
                $periode,
                Auth::user()->username ?? 'system'
            ]);

            if (empty($newBukti) || $newBukti[0]->no_bukti == 'kosong') {
                throw new \Exception('Nomor Urut habis! Silahkan tambahkan nomor baru..');
            }

            $bukti = $newBukti[0]->no_bukti;

            // Insert into notapjk
            DB::table('notapjk')->insert([
                'no_bukti' => $bukti,
                'no_pjk' => $noPajak,
                'tgl' => $tglFaktur,
                'tgl_buat' => now()->format('Y-m-d'),
                'kodes' => $kodes,
                'namas' => $namas,
                'per' => $periode,
                'total' => $total,
                'ppn' => $ppn,
                'nett' => $nett,
                'prom' => $bprom,
                'usrnm' => Auth::user()->username ?? 'system',
                'flag' => $this->flagFaktur,
                'cbg' => session('user.cabang', $cbg->kode)
            ]);

            // Update selected items with reference
            foreach ($selectedItems as $item) {
                DB::table($this->tabelRetur)
                    ->where('no_bukti', $item['no_bukti'])
                    ->update([
                        $this->fieldRef => $bukti,
                        'no_pjk' => $noPajak
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Faktur Pajak Retur berhasil dibuat',
                'no_bukti' => $bukti
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in store faktur retur: ' . $e->getMessage());
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
        $this->setFlag($request);

        return view("otransaksi_tfpajakretur.edit")->with([
            'judul' => $this->judul,
            'flagz' => $this->FLAGZ,
            'koderetur' => $this->koderetur,
            'tipx' => $request->get('tipx', 'edit'),
            'idx' => $request->get('idx', 0),
        ]);
    }

    /**
     * Update faktur pajak retur (equivalent to Button3Click in Delphi)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->setFlag($request);

        $noBukti = $request->no_bukti;
        $noPajak = trim($request->no_pjk);
        $tglFaktur = $request->tgl;
        $tglBuat = $request->tgl_buat;

        try {
            DB::beginTransaction();

            // Update notapjk
            DB::table('notapjk')
                ->where('no_bukti', $noBukti)
                ->update([
                    'no_pjk' => $noPajak,
                    'tgl' => $tglFaktur,
                    'tgl_buat' => $tglBuat
                ]);

            // Update related retur records
            DB::table($this->tabelRetur . ' as beliz')
                ->join('notapjk', 'beliz.' . $this->fieldRef, '=', 'notapjk.no_bukti')
                ->where('notapjk.no_bukti', $noBukti)
                ->update([
                    'beliz.no_pjk' => DB::raw('notapjk.no_pjk')
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in update faktur retur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error mengupdate data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print faktur pajak retur (equivalent to cetak procedure in Delphi)
     */
    public function printFaktur(Request $request)
    {
        $this->setFlag($request);
        $noBukti = $request->no_bukti;

        try {
            // Get company info (equivalent to frmmenu.tokox)
            $companyInfo = $this->getCompanyInfo();

            $query = null;

            if ($this->koderetur == 'SPM') {
                $query = DB::table('beliz')
                    ->join('belizd', 'beliz.NO_BUKTI', '=', 'belizd.no_bukti')
                    ->join('sup', 'beliz.kodes', '=', 'sup.kodes')
                    ->join('notapjk', 'beliz.ref', '=', 'notapjk.no_bukti')
                    ->select([
                        DB::raw("'" . $companyInfo['no_form'] . "' as no_form"),
                        DB::raw("'" . $companyInfo['na_toko'] . "' as na_toko"),
                        DB::raw("'" . $companyInfo['typ_pers'] . "' as typ_pers"),
                        DB::raw("'" . $companyInfo['typ_npwp'] . "' as typ_npwp"),
                        DB::raw("'" . $companyInfo['alamat_pers'] . "' as alamat_pers"),
                        'notapjk.tgl_buat as sekiring',
                        'beliz.ref',
                        'beliz.NO_PJK',
                        'sup.NM_NPWP',
                        'sup.AL_NPWP',
                        'sup.KOTA',
                        'sup.NO_NPWP',
                        DB::raw("concat(belizd.na_BRG, ' [', belizd.no_bukti, ']') as barang"),
                        'belizd.qty',
                        'belizd.harga',
                        DB::raw("concat(belizd.DISKON1, '/', belizd.DISKON2) as diskun"),
                        'belizd.total',
                        'notapjk.total as tot',
                        'notapjk.prom',
                        'notapjk.ppn',
                        'notapjk.nett',
                        'notapjk.tgl'
                    ])
                    ->where('beliz.NO_BUKTI', '=', DB::raw('belizd.no_bukti'))
                    ->where('beliz.kodes', '=', DB::raw('sup.kodes'))
                    ->where('beliz.ref', '=', DB::raw('notapjk.no_bukti'))
                    ->where('notapjk.no_bukti', $noBukti);
            } elseif ($this->koderetur == 'BSN') {
                $query = DB::table('belibsnz')
                    ->join('belibsnzd', 'belibsnz.NO_BUKTI', '=', 'belibsnzd.no_bukti')
                    ->join('supbsn', 'belibsnz.kodes', '=', 'supbsn.kodes')
                    ->join('notapjk', 'belibsnz.ref_pjk', '=', 'notapjk.no_bukti')
                    ->select([
                        DB::raw("'" . $companyInfo['no_form'] . "' as no_form"),
                        DB::raw("'" . $companyInfo['na_toko'] . "' as na_toko"),
                        DB::raw("'" . $companyInfo['typ_pers'] . "' as typ_pers"),
                        DB::raw("'" . $companyInfo['typ_npwp'] . "' as typ_npwp"),
                        DB::raw("'" . $companyInfo['alamat_pers'] . "' as alamat_pers"),
                        'notapjk.tgl_buat as sekiring',
                        'belibsnz.ref_pjk as ref',
                        'belibsnz.NO_PJK',
                        'supbsn.NM_NPWP',
                        'supbsn.AL_NPWP',
                        'supbsn.P_KOTA',
                        'supbsn.NPWP as no_npwp',
                        DB::raw("concat(belibsnzd.na_BRG, ' [', belibsnzd.no_bukti, ']') as barang"),
                        'belibsnzd.qty',
                        'belibsnzd.harga',
                        DB::raw("concat(belibsnzd.DISKON1, '/', belibsnzd.DISKON2) as diskun"),
                        'belibsnzd.total',
                        'notapjk.total as tot',
                        'notapjk.prom',
                        'notapjk.ppn',
                        'notapjk.nett',
                        'notapjk.tgl'
                    ])
                    ->where('belibsnz.NO_BUKTI', '=', DB::raw('belibsnzd.no_bukti'))
                    ->where('belibsnz.kodes', '=', DB::raw('supbsn.kodes'))
                    ->where('belibsnz.ref_pjk', '=', DB::raw('notapjk.no_bukti'))
                    ->where('notapjk.no_bukti', $noBukti);
            }

            $data = $query->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in printFaktur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating print data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print range faktur pajak retur (equivalent to Button4Click in Delphi)
     */
    public function printRange(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $this->setFlag($request);
        $bukti1 = $request->bukti1;
        $bukti2 = $request->bukti2;

        if (empty($bukti1) || empty($bukti2)) {
            return response()->json([
                'success' => false,
                'message' => 'Isikan Nomor Bukti !!!'
            ]);
        }

        try {
            $query = null;

            if ($this->koderetur == 'SPM') {
                $query = DB::table('beliz')
                    ->join('belizd', 'beliz.NO_BUKTI', '=', 'belizd.no_bukti')
                    ->join('sup', 'beliz.kodes', '=', 'sup.kodes')
                    ->join('notapjk', 'beliz.ref', '=', 'notapjk.no_bukti')
                    ->select([
                        DB::raw('""  as TYP_PERS'),
                        DB::raw('""  as NO_FORM'),
                        'notapjk.tgl_buat as sekiring',
                        'beliz.ref',
                        'beliz.NO_PJK',
                        'sup.NM_NPWP',
                        'sup.AL_NPWP',
                        'sup.KOTA',
                        'sup.NO_NPWP',
                        DB::raw("concat(belizd.na_BRG, ' [', belizd.no_bukti, ']') as barang"),
                        'belizd.qty',
                        'belizd.harga',
                        DB::raw("concat(belizd.DISKON1, '/', belizd.DISKON2) as diskun"),
                        'belizd.total',
                        'notapjk.total as tot',
                        'notapjk.prom',
                        'notapjk.ppn',
                        'notapjk.nett',
                        'notapjk.tgl'
                    ])
                    ->where('beliz.NO_BUKTI', '=', DB::raw('belizd.no_bukti'))
                    ->where('beliz.kodes', '=', DB::raw('sup.kodes'))
                    ->where('beliz.ref', '=', DB::raw('notapjk.no_bukti'))
                    ->whereBetween('notapjk.no_bukti', [$bukti1, $bukti2])
                    ->where('notapjk.PER', $periode)
                    ->orderBy('REF');
            } elseif ($this->koderetur == 'BSN') {
                $query = DB::table('belibsnz')
                    ->join('belibsnzd', 'belibsnz.NO_BUKTI', '=', 'belibsnzd.no_bukti')
                    ->join('supbsn', 'belibsnz.kodes', '=', 'supbsn.kodes')
                    ->join('notapjk', 'belibsnz.ref_pjk', '=', 'notapjk.no_bukti')
                    ->select([
                        DB::raw('""  as TYP_PERS'),
                        DB::raw('""  as NO_FORM'),
                        'notapjk.tgl_buat as sekiring',
                        'belibsnz.ref_pjk as ref',
                        'belibsnz.NO_PJK',
                        'supbsn.NM_NPWP',
                        'supbsn.AL_NPWP',
                        'supbsn.P_KOTA',
                        'supbsn.NPWP as no_npwp',
                        DB::raw("concat(belibsnzd.na_BRG, ' [', belibsnzd.no_bukti, ']') as barang"),
                        'belibsnzd.qty',
                        'belibsnzd.harga',
                        DB::raw("concat(belibsnzd.DISKON1, '/', belibsnzd.DISKON2) as diskun"),
                        'belibsnzd.total',
                        'notapjk.total as tot',
                        'notapjk.prom',
                        'notapjk.ppn',
                        'notapjk.nett',
                        'notapjk.tgl'
                    ])
                    ->where('belibsnz.NO_BUKTI', '=', DB::raw('belibsnzd.no_bukti'))
                    ->where('belibsnz.kodes', '=', DB::raw('supbsn.kodes'))
                    ->where('belibsnz.ref_pjk', '=', DB::raw('notapjk.no_bukti'))
                    ->whereBetween('notapjk.no_bukti', [$bukti1, $bukti2])
                    ->where('notapjk.PER', $periode)
                    ->orderBy('REF_pjk');
            }

            $data = $query->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in printRange: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating print range data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate CSV export for tax return (equivalent to buatcsvpajak in Delphi)
     */
    public function generateCsv(Request $request)
    {
        if ($request->session()->has('periode')) {
            $periode = $request->session()->get('periode')['bulan'] . '/' . $request->session()->get('periode')['tahun'];
        } else {
            $periode = '';
        }

        $this->setFlag($request);
        $bukti1 = $request->bukti1;
        $bukti2 = $request->bukti2;
        $namaFile = $request->nama_file ?? 'noname';

        if (empty($bukti1) || empty($bukti2)) {
            return response()->json([
                'success' => false,
                'message' => 'Isikan Nomor Bukti !!!'
            ]);
        }

        try {
            // Call stored procedure to get CSV data
            $data = DB::select('CALL tgz.adm_fakturretur("TAMPIL", ?, ?, ?, ?, "", "")', [
                session('user.cabang', ''),
                trim($bukti1),
                trim($bukti2),
                trim($periode)
            ]);

            // Create CSV content
            $csvData = [];
            $csvData[] = [
                'rm',
                'npwp',
                'nama',
                'kd_jenis_transaksi',
                'fg_pengganti',
                'no_fm',
                'tg_fm',
                'is_credit',
                'nomor_faktur',
                'tanggal_faktur',
                'masa_pajak',
                'tahun_pajak',
                'jumlah_dpp',
                'jumlah_ppn',
                'jumlah_ppnbm'
            ];

            foreach ($data as $row) {
                $csvData[] = [
                    $row->rm,
                    $row->npwp,
                    $row->nama,
                    $row->kd_jenis_transaksi,
                    $row->fg_pengganti,
                    $row->no_fm,
                    $row->tg_fm,
                    $row->is_credit,
                    $row->nomor_faktur,
                    $row->tanggal_faktur,
                    $row->masa_pajak,
                    $row->tahun_pajak,
                    $row->jumlah_dpp,
                    $row->jumlah_ppn,
                    $row->jumlah_ppnbm
                ];
            }

            // Create directory if not exists
            $monthYear = substr($periode, 0, 2) . '-' . substr($periode, -4);
            $dirPath = 'D:/tiara/file_pajak/' . $monthYear . '/coba/';

            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0777, true);
            }

            $fileName = $dirPath . $namaFile . '.csv';

            // Generate CSV file
            $fp = fopen($fileName, 'w');
            foreach ($csvData as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);

            return response()->json([
                'success' => true,
                'message' => 'CSV tersimpan di ' . $fileName,
                'file_path' => $fileName,
                'data_count' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in generateCsv: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company information for reports
     */
    private function getCompanyInfo()
    {
        // This should be implemented based on your company data structure
        // Equivalent to frmmenu.tokox and frmmenu.repx in Delphi
        return [
            'no_form' => '---',
            'na_toko' => session('company.name', 'PT. Default'),
            'typ_pers' => session('company.type', 'PT'),
            'typ_npwp' => session('company.npwp_type', ''),
            'alamat_pers' => session('company.address', '')
        ];
    }

    /**
     * Posting function for Faktur Pajak Retur
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
                DB::table('notapjk')
                    ->where('no_bukti', $noBukti)
                    ->where('flag', $this->flagFaktur)
                    ->update([
                        'posted' => 1,
                        'tg_smp' => now()
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
     * Unposting function for Faktur Pajak Retur
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
                DB::table('notapjk')
                    ->where('no_bukti', $noBukti)
                    ->where('flag', $this->flagFaktur)
                    ->update([
                        'posted' => 0,
                        'tg_smp' => now()
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

    /**
     * Get retur data for DataTables
     */
    public function getreturdata(Request $request)
    {
        try {
            // Get current flag values
            $flagz = Session::get('flagz') ?? 'SPM';
            $koderetur = Session::get('koderetur') ?? 'Y';

            // Set connection based on flag
            if ($flagz === 'BSN') {
                $connection = 'mysql2';
                $tableName = 'belibsnz';
                $supplierTable = 'supbsn';
            } else {
                $connection = 'mysql';
                $tableName = 'beliz';
                $supplierTable = 'sup';
            }

            $kodes = $request->kodes ?? '';

            $query = DB::connection($connection)->table($tableName . ' as b')
                ->leftJoin($supplierTable . ' as s', 'b.kodes', '=', 's.kodes')
                ->select([
                    'b.no_bukti',
                    'b.no_fak01',
                    'b.tgl',
                    'b.total',
                    DB::raw("CASE WHEN b.posted = 'Y' THEN 'POSTED' ELSE 'UNPOST' END as cek")
                ])
                ->whereRaw("LEFT(b.no_bukti, 2) = 'RT'")
                ->where('b.kodes', $kodes)
                ->orderBy('b.tgl', 'desc')
                ->orderBy('b.no_bukti', 'desc');

            return DataTables::of($query)
                ->editColumn('tgl', function ($row) {
                    return date('d/m/Y', strtotime($row->tgl));
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->total, 0, ',', '.');
                })
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getreturdata: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch retur data']);
        }
    }

    /**
     * Update faktur data for AJAX calls (different from the edit update method)
     */
    public function updateFaktur(Request $request)
    {
        try {
            $this->setFlag($request);

            $kodeSupplier = $request->kodes;
            $selectedRetur = $request->selected_retur;

            if (empty($kodeSupplier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode supplier is required'
                ]);
            }

            if (empty($selectedRetur) || !is_array($selectedRetur)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilih minimal satu transaksi retur'
                ]);
            }

            // Generate nomor bukti baru
            $noBukti = $this->generateNoBukti();

            return response()->json([
                'success' => true,
                'message' => 'Update berhasil',
                'no_bukti' => $noBukti,
                'total_retur' => count($selectedRetur)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in updateFaktur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating faktur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate nomor bukti untuk faktur baru
     */
    private function generateNoBukti()
    {
        $latest = DB::table('notapjk')
            ->where('flag', $this->flagFaktur)
            ->orderBy('no_bukti', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->no_bukti, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'FPR' . date('ym') . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
