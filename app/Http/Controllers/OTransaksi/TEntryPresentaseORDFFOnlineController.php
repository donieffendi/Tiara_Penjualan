<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TEntryPresentaseORDFFOnlineController extends Controller
{
    /**
     * Halaman Index - List Persentase
     */
    public function index(Request $request)
    {
        try {
            $judul = 'Entry Persentase Order Fresh Food Online';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TEntryPresentaseORDFFOnline.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TEntryPresentaseORDFFOnline.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TEntryPresentaseORDFFOnline.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TEntryPresentaseORDFFOnline index: ' . $e->getMessage());
            return view("otransaksi_TEntryPresentaseORDFFOnline.index")->with([
                'judul' => 'Entry Persentase Order Fresh Food Online',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ambil data list persentase untuk datatables di index
     */
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Query sesuai Delphi: SELECT * FROM ord_persentase_ff ORDER BY TGL DESC
            $query = "
                SELECT 
                    NO_ID,
                    TGL,
                    PERSENTASE,
                    TGL_AW,
                    TGL_AK,
                    USRNM,
                    CBG
                FROM ord_persentase_ff 
                WHERE CBG = ? 
                ORDER BY TGL DESC
            ";

            $data = DB::select($query, [$CBG]);

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return date('d-m-Y H:i:s', strtotime($row->TGL));
                })
                ->editColumn('PERSENTASE', function ($row) {
                    return number_format($row->PERSENTASE, 2) . ' %';
                })
                ->editColumn('TGL_AW', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL_AW));
                })
                ->editColumn('TGL_AK', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL_AK));
                })
                ->addColumn('action', function ($row) {
                    $editBtn = '<button class="btn btn-sm btn-primary btn-edit" data-id="' . $row->NO_ID . '"><i class="fas fa-edit"></i> Edit</button> ';
                    $deleteBtn = '<button class="btn btn-sm btn-danger btn-delete" data-id="' . $row->NO_ID . '"><i class="fas fa-trash"></i> Hapus</button>';
                    return $editBtn . $deleteBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Edit/New - Form Entry Persentase
     */
    public function edit(Request $request, $no_id = null)
    {
        try {
            $judul = $no_id ? 'Edit Persentase Order Fresh Food' : 'Tambah Persentase Order Fresh Food';

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return redirect()->route('entrypresentaseordffonline')->with('error', 'User tidak memiliki akses cabang');
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return redirect()->route('entrypresentaseordffonline')->with('warning', 'Periode belum diset');
            }

            $data = null;

            // Jika edit, ambil data existing
            if ($no_id && $no_id !== 'new') {
                $query = "SELECT * FROM ord_persentase_ff WHERE NO_ID = ? AND CBG = ?";
                $result = DB::select($query, [$no_id, $CBG]);

                if (!empty($result)) {
                    $data = $result[0];
                } else {
                    return redirect()->route('entrypresentaseordffonline')->with('error', 'Data tidak ditemukan');
                }
            }

            return view("otransaksi_TEntryPresentaseORDFFOnline.edit")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'no_id' => $no_id,
                'data' => $data,
                'status' => $no_id && $no_id !== 'new' ? 'edit' : 'simpan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in edit: ' . $e->getMessage());
            return redirect()->route('entrypresentaseordffonline')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Proses Save/Update/Delete
     */
    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $action = $request->input('action', '');

            DB::beginTransaction();

            switch ($action) {
                case 'save':
                    return $this->saveData($request, $CBG, $username);

                case 'delete':
                    return $this->deleteData($request, $CBG);

                default:
                    DB::rollBack();
                    return response()->json(['error' => 'Action tidak valid'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save/Update Data
     */
    private function saveData($request, $CBG, $username)
    {
        $status = $request->input('status', 'simpan');
        $no_id = $request->input('no_id');
        $persentase = $request->input('persentase', 0);
        $tgl_aw = $request->input('tgl_aw');
        $tgl_ak = $request->input('tgl_ak');

        // Validasi
        if (empty($tgl_aw) || empty($tgl_ak)) {
            DB::rollBack();
            return response()->json(['error' => 'Tanggal mulai dan sampai harus diisi'], 400);
        }

        if ($persentase <= 0) {
            DB::rollBack();
            return response()->json(['error' => 'Persentase harus lebih dari 0'], 400);
        }

        if ($status === 'simpan') {
            // Validasi tanggal tidak overlap dengan data existing
            // Sesuai Delphi: SELECT * FROM ord_persentase_ff WHERE :TGL BETWEEN TGL_AW AND TGL_AK
            $checkQuery = "
                SELECT * FROM ord_persentase_ff 
                WHERE CBG = ? 
                AND (
                    (? BETWEEN TGL_AW AND TGL_AK) OR
                    (? BETWEEN TGL_AW AND TGL_AK) OR
                    (TGL_AW BETWEEN ? AND ?) OR
                    (TGL_AK BETWEEN ? AND ?)
                )
            ";

            $existing = DB::select($checkQuery, [
                $CBG,
                $tgl_aw,
                $tgl_ak,
                $tgl_aw,
                $tgl_ak,
                $tgl_aw,
                $tgl_ak
            ]);

            if (!empty($existing)) {
                DB::rollBack();
                return response()->json(['error' => 'Tanggal Sudah Ada Dalam Daftar Persentase!'], 400);
            }

            // Insert baru
            $insertQuery = "
                INSERT INTO ord_persentase_ff (
                    TGL, PERSENTASE, USRNM, CBG, TGL_AW, TGL_AK
                ) VALUES (
                    NOW(), ?, ?, ?, ?, ?
                )
            ";

            DB::statement($insertQuery, [
                $persentase,
                $username,
                $CBG,
                $tgl_aw,
                $tgl_ak
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan!'
            ]);
        } else {
            // Update existing
            // Validasi tanggal tidak overlap dengan data lain (exclude current record)
            $checkQuery = "
                SELECT * FROM ord_persentase_ff 
                WHERE CBG = ? 
                AND NO_ID != ?
                AND (
                    (? BETWEEN TGL_AW AND TGL_AK) OR
                    (? BETWEEN TGL_AW AND TGL_AK) OR
                    (TGL_AW BETWEEN ? AND ?) OR
                    (TGL_AK BETWEEN ? AND ?)
                )
            ";

            $existing = DB::select($checkQuery, [
                $CBG,
                $no_id,
                $tgl_aw,
                $tgl_ak,
                $tgl_aw,
                $tgl_ak,
                $tgl_aw,
                $tgl_ak
            ]);

            if (!empty($existing)) {
                DB::rollBack();
                return response()->json(['error' => 'Tanggal Sudah Ada Dalam Daftar Persentase!'], 400);
            }

            $updateQuery = "
                UPDATE ord_persentase_ff 
                SET TGL = NOW(),
                    USRNM = ?,
                    CBG = ?,
                    PERSENTASE = ?,
                    TGL_AW = ?,
                    TGL_AK = ?
                WHERE NO_ID = ?
            ";

            DB::statement($updateQuery, [
                $username,
                $CBG,
                $persentase,
                $tgl_aw,
                $tgl_ak,
                $no_id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate!'
            ]);
        }
    }

    /**
     * Delete Data
     */
    private function deleteData($request, $CBG)
    {
        $no_id = $request->input('no_id');

        if (empty($no_id)) {
            DB::rollBack();
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        // Sesuai Delphi: DELETE From ord_persentase_ff where NO_ID = :kode
        $deleteQuery = "DELETE FROM ord_persentase_ff WHERE NO_ID = ? AND CBG = ?";

        DB::statement($deleteQuery, [$no_id, $CBG]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!'
        ]);
    }
}
