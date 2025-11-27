<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PHUndianSupplierController extends Controller
{
    public function index()
    {
        try {
            $tokos = DB::select("SELECT TRIM(KODE) as cbg FROM toko");
        } catch (\Exception $e) {
            $tokos = [];
        }

        try {
            $progundi = DB::selectOne("SELECT * FROM progundi");
        } catch (\Exception $e) {
            $progundi = [];
        }



        $kode = $progundi->kode ?? '';
        $aktif = isset($progundi->aktv) && $progundi->aktv == 1 ? 1 : 0;

        try {
            $masks = DB::select("SELECT kd_brg, na_brg FROM masks WHERE flag=1 ORDER BY kd_brg");
        } catch (\Exception $e) {
            $masks = [];
        }

        return view('promo_hadiah_undian_supplier.index', [
            'tokos' => $tokos,
            'kode' => $kode,
            'aktif' => $aktif,
            'masks' => $masks,
            'title' => 'Undian Supplier'
        ]);
    }

    public function saveConfig(Request $request)
    {
        try {
            $action = $request->get('action');

            if ($action == 'aktif') {
                $kode = $request->get('kode');
                $aktif = $request->get('aktif') == 1 ? 1 : 0;

                $tokos = DB::select("SELECT TRIM(KODE) as cbg FROM toko");

                foreach ($tokos as $toko) {
                    $cab = $toko->cbg;
                    DB::statement("UPDATE {$cab}.progundi SET aktv=?, kode=?", [$aktif, $kode]);
                }

                return response()->json(['success' => true, 'message' => 'Status berhasil diubah']);
            }

            if ($action == 'clear_nomor') {
                $tokos = DB::select("SELECT TRIM(KODE) as cbg FROM toko");

                foreach ($tokos as $toko) {
                    $cab = $toko->cbg;
                    DB::statement("UPDATE {$cab}.noks SET nound=0");
                }

                return response()->json(['success' => true, 'message' => 'Penomoran hadiah berhasil dibersihkan']);
            }

            if ($action == 'tambah') {
                $sub1 = $request->get('sub1');
                $sub2 = $request->get('sub2');
                $sup1 = $request->get('sup1');
                $sup2 = $request->get('sup2');
                $noitem = $request->get('noitem', '');

                $tokos = DB::select("SELECT TRIM(KODE) as cbg FROM toko");

                foreach ($tokos as $toko) {
                    $cab = $toko->cbg;

                    if (empty($noitem)) {
                        DB::statement(
                            "UPDATE {$cab}.masks, (SELECT masks.kd_brg FROM {$cab}.masks, {$cab}.brg
                            WHERE masks.kd_brg=brg.KD_BRG AND masks.sub>=? AND masks.sub<=? AND brg.SUPP>=? AND brg.SUPP<=?) as OTE
                            SET {$cab}.masks.flag=1 WHERE {$cab}.masks.kd_brg=ote.kd_brg",
                            [$sub1, $sub2, $sup1, $sup2]
                        );
                    } else {
                        DB::statement(
                            "UPDATE {$cab}.masks, (SELECT masks.kd_brg FROM {$cab}.masks, {$cab}.brg
                            WHERE masks.kd_brg=brg.KD_BRG AND masks.sub>=? AND masks.sub<=? AND brg.SUPP>=? AND brg.SUPP<=? AND masks.kdbar=?) as OTE
                            SET {$cab}.masks.flag=1 WHERE {$cab}.masks.kd_brg=ote.kd_brg",
                            [$sub1, $sub2, $sup1, $sup2, $noitem]
                        );
                    }
                }

                return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan']);
            }

            if ($action == 'hapus_semua') {
                $tokos = DB::select("SELECT TRIM(KODE) as cbg FROM toko");

                foreach ($tokos as $toko) {
                    $cab = $toko->cbg;
                    DB::statement("UPDATE {$cab}.masks SET flag=0 WHERE flag=1");
                }

                return response()->json(['success' => true, 'message' => 'Semua data berhasil dihapus']);
            }

            return response()->json(['success' => false, 'message' => 'Action tidak valid']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getConfig(Request $request)
    {
        try {
            $masks = DB::select("SELECT kd_brg, na_brg FROM masks WHERE flag=1 ORDER BY kd_brg");

            return response()->json(['success' => true, 'data' => $masks]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
