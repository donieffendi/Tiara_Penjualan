<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TCetakLBKKLBTATController extends Controller
{
    var $judul = 'Buat Orderan Kue Basah';
    var $FLAGZ = 'ORDKB';

    public function index(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_cetaklbkk_lbtat.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            $cbgMa = DB::table('toko')->where('STA', 'MA')->value('KODE');

            return view("otransaksi_cetaklbkk_lbtat.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'cbgMa' => $cbgMa
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TBuatOrderanKueBasah index: ' . $e->getMessage());
            return view("otransaksi_cetaklbkk_lbtat.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function getTBuatOrderanKueBasahData(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;
            $orderKe = $request->order_ke;

            $cbgMa = DB::table('toko')->where('STA', 'MA')->value('KODE');

            if (!$orderKe) {
                return Datatables::of(collect([]))->make(true);
            }

            $query = DB::connection($cbgMa)
                ->select("CALL " . $cbgMa . ".pjl_ord_toko_kode8('TAMPIL', '', ?, ?, '')", [$orderKe, $CBG]);

            return Datatables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-danger btn-sm btn-hapus" data-kd="' . $row->KD_BRG . '" data-ke="' . $row->PESAN_KE . '"><i class="fas fa-trash"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getTBuatOrderanKueBasahData: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function browse(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;
            $cbgMa = DB::table('toko')->where('STA', 'MA')->value('KODE');

            if ($request->type == 'barang') {
                $kdBrg = $request->kd_brg;
                $orderKe = $request->order_ke;

                $query = DB::connection($cbgMa)
                    ->select("CALL " . $cbgMa . ".pjl_ord_toko_kode8('MASTER', ?, '', ?, '')", [$kdBrg, $CBG]);

                if (count($query) > 0) {
                    $cekSp = DB::connection($cbgMa)
                        ->select("CALL " . $cbgMa . ".pjl_ord_toko_kode8('CEKSP', ?, '', ?, '')", [$kdBrg, $CBG]);

                    $ada = $cekSp[0]->ADA ?? 0;

                    return response()->json([
                        'success' => true,
                        'data' => $query[0],
                        'ada_sp' => $ada
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada barang ini..'
                    ]);
                }
            }

            return response()->json([]);
        } catch (\Exception $e) {
            Log::error('Error in browse: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $CBG = Auth::user()->CBG;
            $USERNAME = Auth::user()->username;
            $cbgMa = DB::table('toko')->where('STA', 'MA')->value('KODE');

            $kdBrg = $request->kd_brg;
            $orderKe = $request->order_ke;

            DB::connection($cbgMa)->statement(
                "CALL " . $cbgMa . ".pjl_ord_toko_kode8('SIMPAN', ?, ?, ?, ?)",
                [$kdBrg, $orderKe, $CBG, $USERNAME]
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in store: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }

    public function proses(Request $request)
    {
        try {
            DB::beginTransaction();

            $CBG = Auth::user()->CBG;
            $USERNAME = Auth::user()->username;
            $cbgMa = DB::table('toko')->where('STA', 'MA')->value('KODE');

            $kdBrg = $request->kd_brg;
            $orderKe = $request->order_ke;

            DB::connection($cbgMa)->statement(
                "CALL " . $cbgMa . ".pjl_ord_toko_kode8('HAPUS', ?, ?, ?, ?)",
                [$kdBrg, $orderKe, $CBG, $USERNAME]
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
        }
    }

    public function jasper(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG;
            $USERNAME = Auth::user()->username;
            $cbgMa = DB::table('toko')->where('STA', 'MA')->value('KODE');
            $orderKe = $request->order_ke;

            if (!$orderKe) {
                return redirect()->back()->with('error', 'Order Ke tidak boleh kosong');
            }

            $query = DB::connection($cbgMa)
                ->select("CALL " . $cbgMa . ".pjl_ord_toko_kode8('CETAK', '', ?, ?, ?)", [$orderKe, $CBG, $USERNAME]);

            return response()->json(['success' => true, 'data' => $query]);
        } catch (\Exception $e) {
            Log::error('Error in jasper: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal generate laporan: ' . $e->getMessage()], 500);
        }
    }
}
