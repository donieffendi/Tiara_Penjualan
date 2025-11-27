<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LMemberiTandaBintangController extends Controller
{
    /**
     * Display listing of barang logd (equivalent to frmlgbintg - main grid)
     */
    public function index()
    {
        return view('logistik_memberi_tanda_bintang.index');
    }

    /**
     * Get data for index page (equivalent to Tampil procedure in Delphi)
     */
    public function getMemberiTandaBintang(Request $request)
    {
        // Main query - equivalent to com query in Delphi Tampil procedure
        $query = DB::table('brglogd')
            ->select('kd_brg', 'na_brg', 'td_od', 'tgl_od', 'cat_od')
            ->orderBy('kd_brg', 'ASC');

        return Datatables::of($query)
            ->addIndexColumn()
            ->editColumn('tgl_od', function ($row) {
                return $row->tgl_od ? date('d/m/Y', strtotime($row->tgl_od)) : '';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button onclick="editData(\'' . $row->kd_brg . '\')" class="btn btn-sm btn-primary">Edit</button>';
                return $btnEdit;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show form for creating new entry or editing existing (equivalent to frmlgbintn)
     * Combined function for both new and edit based on status parameter
     */
    public function edit(Request $request)
    {
        $kd_brg = $request->get('kd_brg');
        $status = $request->get('status', 'simpan'); // 'simpan' for new, 'edit' for existing

        $data = [
            'kd_brg' => $kd_brg,
            'status' => $status,
            'barang' => null
        ];

        // If editing existing data (equivalent to cxGrid1DBTableView1DblClick in Delphi)
        if ($status == 'edit' && $kd_brg) {
            $barang = DB::select("
                SELECT kd_brg, na_brg, td_od, cat_od, tgl_od
                FROM brglogd
                WHERE kd_brg = ?
                ORDER BY kd_brg ASC", [$kd_brg]);

            $data['barang'] = !empty($barang) ? $barang[0] : null;
        }

        return view('logistik_memberi_tanda_bintang.edit', $data);
    }

    /**
     * Store new entry or update existing (equivalent to MSaveClick in Delphi frmlgbintn)
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'kd_brg' => 'required',
            'na_brg' => 'required',
            'tgl_od' => 'required|date'
        ]);

        DB::beginTransaction();

        try {
            $kd_brg = trim($request->kd_brg);
            $status = $request->status;

            // Validation equivalent to checkx procedure in Delphi
            if (empty($kd_brg)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode Barang Kosong.'
                ]);
            }

            if (empty(trim($request->na_brg))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nama Barang Kosong.'
                ]);
            }

            if ($status == 'simpan') {
                // Check if kd_brg already exists (equivalent to Delphi validation)
                $existing = DB::select("
                    SELECT kd_brg, na_brg, td_od, tgl_od, cat_od
                    FROM brglogd
                    WHERE kd_brg = ?", [$kd_brg]);

                if (!empty($existing)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode Barang sudah ada!'
                    ]);
                }

                // Insert new record (commented INSERT logic in Delphi suggests this might be implemented later)
                // For now, we'll assume INSERT functionality based on the form structure
                DB::statement("
                    INSERT INTO brglogd (kd_brg, na_brg, td_od, cat_od, tgl_od)
                    VALUES (?, ?, ?, ?, ?)", [
                    $kd_brg,
                    trim($request->na_brg),
                    trim($request->txttype ?? ''),
                    trim($request->txtalasan ?? ''),
                    $request->tgl_od
                ]);
            } else {
                // Update existing record (equivalent to UPDATE in Delphi)
                DB::statement("
                    UPDATE brglogd
                    SET td_od = ?, cat_od = ?, tgl_od = ?
                    WHERE kd_brg = ?", [
                    trim($request->txttype ?? ''),
                    trim($request->txtalasan ?? ''),
                    $request->tgl_od,
                    $kd_brg
                ]);
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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Browse barang logd (for kd_brg lookup if needed)
     */
    public function browse(Request $request)
    {
        $q = $request->get('q', '');

        if (!empty($q)) {
            $barang = DB::select("
                SELECT kd_brg, na_brg, td_od, cat_od, tgl_od
                FROM brglogd
                WHERE (na_brg LIKE ? OR kd_brg LIKE ?)
                ORDER BY kd_brg
                LIMIT 50", ["%$q%", "%$q%"]);
        } else {
            $barang = DB::select("
                SELECT kd_brg, na_brg, td_od, cat_od, tgl_od
                FROM brglogd
                ORDER BY kd_brg
                LIMIT 50");
        }

        return response()->json($barang);
    }

    /**
     * Get barang detail by code (equivalent to txtkdbrgKeyUp validation in Delphi)
     */
    public function getBarangDetail(Request $request)
    {
        $kd_brg = $request->kd_brg;

        $barang = DB::select("
            SELECT kd_brg, na_brg, td_od, cat_od, tgl_od
            FROM brglogd
            WHERE kd_brg = ?", [$kd_brg]);

        if (!empty($barang)) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'message' => 'Kode Barang sudah ada!',
                'data' => $barang[0]
            ]);
        }

        return response()->json([
            'success' => true,
            'exists' => false,
            'data' => null
        ]);
    }

    /**
     * Delete barang logd entry
     */
    public function destroy(Request $request)
    {
        $kd_brg = $request->route('lmemberitandabintang');

        try {
            DB::statement("DELETE FROM brglogd WHERE kd_brg = ?", [$kd_brg]);
            return redirect()->route('lmemberitandabintang')->with('status', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('lmemberitandabintang')->with('error', 'Gagal menghapus data');
        }
    }

    /**
     * Check if barang exists (equivalent to validation logic)
     */
    public function cekOrder(Request $request)
    {
        $kd_brg = $request->kd_brg;
        $result = DB::select("SELECT COUNT(*) as ADA FROM brglogd WHERE kd_brg = ?", [$kd_brg]);

        return response()->json(['exists' => $result[0]->ADA > 0]);
    }

    /**
     * Print functionality (if needed for reports)
     */
    public function printOrder(Request $request)
    {
        $kd_brg = $request->kd_brg;

        $data = DB::select("
            SELECT kd_brg, na_brg, td_od, cat_od, tgl_od
            FROM brglogd
            WHERE kd_brg = ?
            ORDER BY kd_brg", [$kd_brg]);

        return response()->json(['data' => $data]);
    }
}
