<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PHStopProgramHadiahController extends Controller
{
    /**
     * Display index page (list of program hadiah)
     * Matching Delphi: frmStshijaug.pas - Tampil procedure
     */
    public function index(Request $request)
    {
        return view('promo_hadiah_stop_program.index');
    }

    /**
     * Get list data for datatable
     * Matching Delphi: Tampil procedure
     * hij.sql.Text := ' SELECT * from lbhijau order by no_id desc ';
     */
    public function getData(Request $request)
    {
        $query = DB::select("SELECT * FROM lbhijau ORDER BY no_id DESC");

        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('tg_mulai', function ($row) {
                return $row->tg_mulai ? date('d/m/Y', strtotime($row->tg_mulai)) : '';
            })
            ->editColumn('tg_akhir', function ($row) {
                return $row->tg_akhir ? date('d/m/Y', strtotime($row->tg_akhir)) : '';
            })
            ->editColumn('TGZ', function ($row) {
                return $row->TGZ == 1
                    ? '<span class="badge badge-success">Ya</span>'
                    : '<span class="badge badge-secondary">Tidak</span>';
            })
            ->editColumn('TMM', function ($row) {
                return $row->TMM == 1
                    ? '<span class="badge badge-success">Ya</span>'
                    : '<span class="badge badge-secondary">Tidak</span>';
            })
            ->editColumn('SOP', function ($row) {
                return $row->SOP == 1
                    ? '<span class="badge badge-success">Ya</span>'
                    : '<span class="badge badge-secondary">Tidak</span>';
            })
            ->addColumn('action', function ($row) {
                $btnStop = '<button onclick="stopProgram(\'' . $row->kd_prm . '\')" class="btn btn-sm btn-danger" title="Stop Program"><i class="fas fa-stop"></i> Stop</button>';
                $btnView = '<button onclick="viewDetail(\'' . $row->kd_prm . '\')" class="btn btn-sm btn-info ml-1" title="Lihat Detail"><i class="fas fa-eye"></i></button>';
                return $btnStop . ' ' . $btnView;
            })
            ->rawColumns(['action', 'TGZ', 'TMM', 'SOP'])
            ->make(true);
    }

    /**
     * Browse product by code
     * Matching Delphi: txtkd_brgKeyDown procedure
     * SELECT lbhijau.no_bukti, lbhijau.kd_prm, ... FROM lbhijau,lbhijaud WHERE lbhijaud.KD_BRGH=:kd
     */
    public function browse(Request $request)
    {
        $kd_brgh = trim($request->get('kd_brgh', ''));

        if (empty($kd_brgh)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode barang kosong'
            ]);
        }

        $data = DB::select(
            "SELECT lbhijau.no_bukti, lbhijau.kd_prm, lbhijau.ket, lbhijau.kodes,
                    lbhijau.namas, lbhijau.tg_mulai, lbhijau.tg_akhir, lbhijau.type,
                    lbhijaud.KD_BRGH, lbhijaud.NA_BRGH
             FROM lbhijau, lbhijaud
             WHERE lbhijaud.NO_BUKTI = lbhijau.NO_BUKTI
               AND lbhijaud.KD_BRGH = ?
             ORDER BY lbhijau.NO_BUKTI ASC, lbhijaud.KD_BRGH ASC",
            [$kd_brgh]
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get detail program hadiah
     * Matching Delphi: cxGrid1DBTableView1DblClick
     * SELECT * FROM lbhijau where kd_prm = ...
     */
    public function getDetail(Request $request)
    {
        $kd_prm = trim($request->get('kd_prm', ''));

        if (empty($kd_prm)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode program kosong'
            ]);
        }

        // Get header data
        $header = DB::select(
            "SELECT * FROM lbhijau WHERE kd_prm = ? ORDER BY kd_prm",
            [$kd_prm]
        );

        if (empty($header)) {
            return response()->json([
                'success' => false,
                'message' => 'Data program tidak ditemukan'
            ]);
        }

        // Get detail barang (masks table)
        $detail = DB::select(
            "SELECT * FROM masks WHERE hS = ?",
            [$kd_prm]
        );

        return response()->json([
            'success' => true,
            'header' => $header[0],
            'detail' => $detail
        ]);
    }

    /**
     * Stop program hadiah - update all branches
     * Matching Delphi: cxButton1Click procedure
     * UPDATE [cab].LBHIJAU SET [flag]=0 WHERE KD_PRM=:KD
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'kd_prm' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $kd_prm = trim($request->kd_prm);

            // Get flag from session/user - matching: cibing:=frmmenu.FrMenu.Flag.Caption;
            $flag_field = session('flag_field', 'TGZ'); // Default TGZ, bisa TMM atau SOP

            // Get all branch codes - matching: SELECT trim(KODE) as cbg from toko
            $branches = DB::select("SELECT TRIM(KODE) as cbg FROM toko");

            if (empty($branches)) {
                throw new \Exception('Tidak ada data cabang ditemukan');
            }

            // Update each branch - matching Delphi while loop
            foreach ($branches as $branch) {
                $cab = $branch->cbg;

                // Update LBHIJAU set flag=0 - matching: UPDATE [cab].LBHIJAU SET [flag]=0 WHERE KD_PRM=:KD
                DB::statement(
                    "UPDATE {$cab}.LBHIJAU SET {$flag_field} = 0 WHERE KD_PRM = ?",
                    [$kd_prm]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stop Hadiah, Sukses!'
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
     * Delete program hadiah
     * Matching Delphi: cxGrid1DBTableView1KeyUp - Key=46 (Delete)
     * DELETE From lbhijau where no_bukti = :kode
     * DELETE From lbhijaud where no_bukti = :kode
     */
    public function destroy(Request $request)
    {
        $no_bukti = trim($request->get('no_bukti', ''));

        if (empty($no_bukti)) {
            return response()->json([
                'success' => false,
                'message' => 'No bukti kosong'
            ]);
        }

        DB::beginTransaction();

        try {
            // Delete from lbhijau (header)
            DB::statement("DELETE FROM lbhijau WHERE no_bukti = ?", [$no_bukti]);

            // Delete from lbhijaud (detail)
            DB::statement("DELETE FROM lbhijaud WHERE no_bukti = ?", [$no_bukti]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get supplier data
     * Matching Delphi: txtkodesExit procedure
     * SELECT * from sup where kodes=:b1
     */
    public function getSupplier(Request $request)
    {
        $kodes = trim($request->get('kodes', ''));

        if (empty($kodes)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode supplier kosong'
            ]);
        }

        $supplier = DB::select(
            "SELECT * FROM sup WHERE kodes = ? ORDER BY kodes",
            [$kodes]
        );

        if (empty($supplier)) {
            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Supplier tidak ditemukan'
            ]);
        }

        return response()->json([
            'success' => true,
            'exists' => true,
            'data' => $supplier[0]
        ]);
    }

    /**
     * Print report
     * Matching Delphi: p1Click - frxReport1.ShowReport()
     */
    public function printStopProgramHadiah(Request $request)
    {
        $kd_prm = $request->get('kd_prm');

        if (empty($kd_prm)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode program kosong'
            ]);
        }

        // Get header
        $header = DB::select(
            "SELECT * FROM lbhijau WHERE kd_prm = ?",
            [$kd_prm]
        );

        // Get detail
        $detail = DB::select(
            "SELECT d.*, b.na_brgh
             FROM lbhijaud d
             LEFT JOIN brgh b ON d.KD_BRGH = b.kd_brgh
             WHERE d.NO_BUKTI = ?
             ORDER BY d.KD_BRGH",
            [$header[0]->no_bukti ?? '']
        );

        return response()->json([
            'success' => true,
            'header' => $header[0] ?? null,
            'detail' => $detail
        ]);
    }
}
