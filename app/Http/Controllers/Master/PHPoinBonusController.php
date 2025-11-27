<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PHPoinBonusController extends Controller
{
    public function index()
    {
        return view('promosi_hadiah_poin_bonus.index');
    }

    public function getPoinBonus(Request $request)
    {
        $query = "
            SELECT NO_ID, TRIM(TYPE) AS TYPE, KET, MIN_BELANJA, MAX_POIN, PERSEN,
                   TGL1, TGL2, TIME(TG_EDIT) AS JAM, DATE(TG_EDIT) AS TGL_EDIT,
                   TG_EDIT, USRNM,
                   IF((CURDATE() BETWEEN TGL1 AND TGL2), 1, 0) AS CEK
            FROM poin
            WHERE flag = 'AA'
            ORDER BY TYPE
        ";

        $data = DB::select($query);

        return DataTables::of(collect($data))
            ->addIndexColumn()
            ->editColumn('MIN_BELANJA', function ($row) {
                return number_format($row->MIN_BELANJA, 0);
            })
            ->editColumn('MAX_POIN', function ($row) {
                return number_format($row->MAX_POIN, 0);
            })
            ->editColumn('PERSEN', function ($row) {
                return $row->PERSEN . '%';
            })
            ->editColumn('TGL1', function ($row) {
                return Carbon::parse($row->TGL1)->format('d/m/Y');
            })
            ->editColumn('TGL2', function ($row) {
                return Carbon::parse($row->TGL2)->format('d/m/Y');
            })
            ->editColumn('CEK', function ($row) {
                return $row->CEK == 1 ? 'Aktif' : 'Tidak Aktif';
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-primary btn-edit" data-id="' . $row->NO_ID . '">
                            <i class="fas fa-edit"></i>
                        </button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function edit(Request $request)
    {
        $id = $request->get('id');

        $data = DB::selectOne("
            SELECT NO_ID, TRIM(TYPE) AS TYPE, KET, MIN_BELANJA, MAX_POIN, PERSEN,
                   TGL1, TGL2, USRNM
            FROM poin
            WHERE NO_ID = ? AND flag = 'AA'
        ", [$id]);

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'NO_ID' => $data->NO_ID,
                'TYPE' => $data->TYPE,
                'KET' => $data->KET,
                'MIN_BELANJA' => $data->MIN_BELANJA,
                'MAX_POIN' => $data->MAX_POIN,
                'PERSEN' => $data->PERSEN,
                'TGL1' => Carbon::parse($data->TGL1)->format('Y-m-d'),
                'TGL2' => Carbon::parse($data->TGL2)->format('Y-m-d')
            ]
        ]);
    }

    public function store(Request $request)
    {
        try {
            $id = $request->get('NO_ID');
            $minBelanja = $request->get('MIN_BELANJA');
            $maxPoin = $request->get('MAX_POIN');
            $persen = $request->get('PERSEN');
            $tgl1 = $request->get('TGL1');
            $tgl2 = $request->get('TGL2');
            $user = Auth::user()->name ?? 'SYSTEM';

            if (empty($id) || empty($minBelanja) || empty($maxPoin) || empty($persen) || empty($tgl1) || empty($tgl2)) {
                return response()->json(['success' => false, 'message' => 'Data tidak lengkap']);
            }

            $cabangList = DB::select("
                SELECT TRIM(kode) AS CBG
                FROM toko
                WHERE STA IN ('MA','CB')
                ORDER BY NO_ID ASC
            ");

            DB::beginTransaction();

            foreach ($cabangList as $cabang) {
                $cbg = $cabang->CBG;

                DB::statement("
                    UPDATE {$cbg}.poin
                    SET MIN_BELANJA = ?,
                        MAX_POIN = ?,
                        PERSEN = ?,
                        TGL1 = ?,
                        TGL2 = ?,
                        TG_EDIT = NOW(),
                        USRNM = ?
                    WHERE NO_ID = ?
                ", [$minBelanja, $maxPoin, $persen, $tgl1, $tgl2, $user, $id]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan ke semua cabang'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}