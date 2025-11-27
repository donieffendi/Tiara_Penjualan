<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHDaftarBarangHadiahController extends Controller
{
    /**
     * Display index page for Daftar Barang Hadiah
     * Matching Delphi: frmbrghdhg - list/browse form
     */
    public function index()
    {
        return view('promo_hadiah_daftar_barang_hadiah.index');
    }

    /**
     * Get list of Barang Hadiah for datatable
     * Query matching Delphi: SELECT * from brghd order by no_id
     */
    public function getData(Request $request)
    {
        $cbg = session('cbg', 'TGZ');

        // Matching Delphi query from Tampil procedure
        $query = DB::select(
            "SELECT brghd.no_id, brghd.kd_brgh, brghd.na_brgh, brgh.kodes, brgh.namas, brghd.aw00, brghd.gak, brghd.ma00, brghd.ke00, brghd.ak00, brghd.cbg, brghd.yer
             FROM brghd JOIN brgh ON brghd.kd_brgh = brgh.kd_brgh
             WHERE cbg = ?
             ORDER BY no_id",
            [$cbg]
        );

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('aw00', function ($row) {
                return number_format($row->aw00 ?? 0, 0, ',', '.');
            })
            ->editColumn('ma00', function ($row) {
                return number_format($row->ma00 ?? 0, 0, ',', '.');
            })
            ->editColumn('ke00', function ($row) {
                return number_format($row->ke00 ?? 0, 0, ',', '.');
            })
            ->editColumn('ak00', function ($row) {
                return number_format($row->ak00 ?? 0, 0, ',', '.');
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button onclick="editData(\'' . $row->kd_brgh . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                $btnDelete = '<button onclick="deleteData(\'' . $row->kd_brgh . '\')" class="btn btn-sm btn-danger ml-1" title="Hapus"><i class="fas fa-trash"></i></button>';
                return $btnEdit . ' ' . $btnDelete;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show form for create/edit Barang Hadiah
     * Matching Delphi: frmbrghdhn - new/edit form
     */
    public function edit(Request $request)
    {
        $kd_brgh = $request->get('kd_brgh');
        $status = $request->get('status', 'simpan');

        $data = [
            'kd_brgh' => '',
            'status' => $status,
            'barang' => null,
        ];

        if ($status == 'edit' && $kd_brgh) {
            // Get barang data from brgh table
            // Matching Delphi: hdh.sql.text:='SELECT * FROM brgh where kd_brgh = ...'
            $barang = DB::select(
                "SELECT kd_brgh, na_brgh, kodes, namas
                 FROM brgh
                 WHERE kd_brgh = ?
                 LIMIT 1",
                [$kd_brgh]
            );

            if (!empty($barang)) {
                $data['barang'] = $barang[0];
                $data['kd_brgh'] = $kd_brgh;
            } else {
                return redirect()->route('phdaftarbaranghadiah')->with('error', 'Data tidak ditemukan!');
            }
        }

        return view('promo_hadiah_daftar_barang_hadiah.edit', $data);
    }

    /**
     * Store/Update Barang Hadiah
     * Matching Delphi: MSaveClick procedure
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'kd_brgh' => 'required|max:7',
            'na_brgh' => 'required',
        ]);

        DB::beginTransaction();

        try {
            $kd_brgh = trim($request->kd_brgh);
            $na_brgh = trim($request->na_brgh);
            $kodes = trim($request->kodes ?? '');
            $namas = trim($request->namas ?? '');
            $status = $request->status;
            $username = Auth::user()->username ?? 'system';

            // Validate kode barang length (matching Delphi validation)
            if (strlen($kd_brgh) != 7) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode Barang harus 7 digit!'
                ], 400);
            }

            if ($status == 'simpan') {
                // Check if kode already exists
                $check = DB::select("SELECT kd_brgh FROM brgh WHERE kd_brgh = ?", [$kd_brgh]);
                if (!empty($check)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode ini sudah dipakai!'
                    ], 400);
                }

                // Get all branches (matching Delphi: select kode from toko WHERE STA='MA' OR STA='CB')
                $branches = DB::select("SELECT kode FROM toko WHERE STA IN ('MA', 'CB')");
                DB::statement(
                    "INSERT INTO brgh (kd_brgh, na_brgh, kodes, namas, usrnm, tg_smp)
                         VALUES (?, ?, ?, ?, ?, NOW())",
                    [$kd_brgh, $na_brgh, $kodes, $namas, $username]
                );
                foreach ($branches as $branch) {
                    $cab = $branch->kode;

                    // Insert into brgh table for each branch
                   

                    // Insert into brghd table for each branch
                    DB::statement(
                        "INSERT INTO brghd (kd_brgh, na_brgh, cbg, yer)
                         VALUES (?, ?, ?, YEAR(NOW()))",
                        [$kd_brgh, $na_brgh, $cab]
                    );
                }
            } else {
                // Update mode
                // Get all branches
                $branches = DB::select("SELECT kode FROM toko WHERE STA IN ('MA', 'CB')");

                foreach ($branches as $branch) {
                    $cab = $branch->kode;

                    // Update brgh table
                    DB::statement(
                        "UPDATE brgh
                         SET na_brgh = ?, kodes = ?, namas = ?, usrnm = ?, tg_smp = NOW()
                         WHERE kd_brgh = ?",
                        [$na_brgh, $kodes, $namas, $username, $kd_brgh]
                    );

                    // Update brghd table
                    DB::statement(
                        "UPDATE brghd
                         SET na_brgh = ?
                         WHERE kd_brgh = ?",
                        [$na_brgh, $kd_brgh]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success',
                'kd_brgh' => $kd_brgh
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
     * Delete Barang Hadiah
     * Matching Delphi: cxGrid1DBTableView1KeyUp - Key=46 (Delete key)
     */
    public function delete(Request $request)
    {
        $kd_brgh = $request->get('kd_brgh');

        if (empty($kd_brgh)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode barang tidak valid'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Get all branches
            $branches = DB::select("SELECT kode FROM toko WHERE STA IN ('MA', 'CB')");

            foreach ($branches as $branch) {
                $cab = $branch->kode;

                // Delete from brgh table
                DB::statement("DELETE FROM {$cab}.brgh WHERE kd_brgh = ?", [$kd_brgh]);

                // Delete from brghd table
                DB::statement("DELETE FROM {$cab}.brghd WHERE kd_brgh = ?", [$kd_brgh]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
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
     * Browse supplier data
     * Matching Delphi: txtkodesExit - lookup supplier
     */
    public function browseSupplier(Request $request)
    {
        $kodes = $request->get('kodes', '');

        if (!empty($kodes)) {
            $supplier = DB::select(
                "SELECT kodes, namas
                 FROM sup
                 WHERE kodes = ?
                 LIMIT 50",
                [$kodes]
            );

            if (!empty($supplier)) {
                return response()->json([
                    'success' => true,
                    'data' => $supplier[0]
                ]);
            }
        }else {
            return$supplier = DB::select(
                "SELECT kodes, namas
                 FROM sup
                 LIMIT 50",
            );}

        return response()->json([
            'success' => false,
            'message' => 'Supplier tidak ditemukan'
        ]);
    }

    /**
     * Browse product data for lookup
     * Matching Delphi: product lookup functionality
     */
    public function browse(Request $request)
    {
        $q = $request->get('q', '');
        $cbg = session('cbg', '01');

        if (!empty($q)) {
            $data = DB::select(
                "SELECT kd_brgh, na_brgh, kodes, namas
                 FROM brgh
                 WHERE (kd_brgh LIKE ? OR na_brgh LIKE ?)
                 ORDER BY kd_brgh
                 LIMIT 50",
                ["%$q%", "%$q%"]
            );
        } else {
            $data = DB::select(
                "SELECT kd_brgh, na_brgh, kodes, namas
                 FROM brgh
                 ORDER BY kd_brgh
                 LIMIT 50"
            );
        }

        return response()->json($data);
    }

    /**
     * Get product detail
     * Matching Delphi: product validation
     */
    public function getDetail(Request $request)
    {
        $kd_brgh = $request->get('kd_brgh');

        if (empty($kd_brgh)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode barang tidak boleh kosong'
            ]);
        }

        $product = DB::select(
            "SELECT kd_brgh, na_brgh, kodes, namas
             FROM brgh
             WHERE kd_brgh = ?
             LIMIT 1",
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
            'exists' => false,
            'message' => 'Produk tidak ditemukan'
        ]);
    }

    /**
     * Print Daftar Barang Hadiah
     * Matching Delphi: p1Click - print report
     */
    public function printDaftarBarangHadiah(Request $request)
    {
        $cbg = session('cbg', '01');

        $data = DB::select(
            "SELECT brghd.no_id, brghd.kd_brgh, brghd.na_brgh,
                    brghd.kodes, brghd.namas,
                    brghd.aw00, brghd.ma00, brghd.ke00, brghd.ak00,
                    brghd.cbg, brghd.yer
             FROM brghd
             WHERE brghd.cbg = ?
             ORDER BY brghd.no_id",
            [$cbg]
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}