<?php

namespace App\Http\Controllers\OLain;

use App\Http\Controllers\Controller;

use App\Models\OLain\Jackh;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\Brg;
use App\Models\Master\Sup;
use DataTables;
use Auth;
use DB;

class DimensiController extends Controller
{

    public function index() {
        return view('olain_dimensi.index');
    }

    public function getDimensi(Request $request)
    {
        $susun = $request->susun;
        $dimensi = $request->dimensi;

        $dimensi = DB::SELECT("CALL sim_ambil_brg_rak('MASTER_DIMENSI','$susun','$dimensi')");
        
        return Datatables::of($dimensi)
                ->addIndexColumn()
                ->make(true);
    }

    public function importRak(Request $request)
    {
        DB::beginTransaction();
        try {

            // 1. CREATE TABLE (sama persis)
            DB::statement("
                CREATE TABLE IF NOT EXISTS excelnomrak (
                    KD_BRG varchar(20) DEFAULT '',
                    BARCODE varchar(20) DEFAULT '',
                    NO_RAK varchar(20) DEFAULT '',
                    TG_SMP datetime DEFAULT '2001-01-01',
                    USRNM varchar(50) DEFAULT '',
                    NAMAFILE varchar(50) DEFAULT '',
                    INDEX (KD_BRG, BARCODE)
                )
            ");

            // 2. TRUNCATE
            DB::statement("TRUNCATE TABLE excelnomrak");

            // 3. BACA EXCEL
            $file = $request->file('file');
            $namaFile = $file->getClientOriginalName();

            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $kolom = 'KD_BRG';
            if (strtoupper($sheet->getCell('A1')->getValue()) === 'BARCODE') {
                $kolom = 'BARCODE';
            }

            $row = 2;
            while ($sheet->getCell('A'.$row)->getValue()) {
                DB::table('excelnomrak')->insert([
                    $kolom     => $sheet->getCell('A'.$row)->getValue(),
                    'NO_RAK'   => $sheet->getCell('B'.$row)->getValue(),
                    'TG_SMP'   => now(),
                    'USRNM'    => Auth::user()->username,
                    'NAMAFILE' => $namaFile
                ]);
                $row++;
            }

            // 4. JIKA BARCODE → MAP KE KD_BRG
            if ($kolom === 'BARCODE') {
                DB::statement("
                    UPDATE excelnomrak a
                    JOIN brg b ON a.BARCODE=b.BARCODE
                    SET a.KD_BRG=b.KD_BRG
                ");
            }

            // 5. INSERT BARU
            DB::statement("
                INSERT INTO brg_dc_ts (KD_BRG,RAK_TOKO,SUSUN,USRNM,TG_SMP,BUKTI_USUL)
                SELECT KD_BRG, NO_RAK, 1, USRNM, NOW(), NAMAFILE
                FROM excelnomrak
                WHERE KD_BRG NOT IN (SELECT KD_BRG FROM brg_dc_ts)
                AND NAMAFILE=?
            ", [$namaFile]);

            // 6. UPDATE EXISTING
            $affected = DB::affectingStatement("
                UPDATE brg_dc_ts a
                JOIN excelnomrak b ON a.KD_BRG=b.KD_BRG
                SET a.RAK_TOKO=b.NO_RAK,
                    a.USRNM=?,
                    a.TG_SMP=NOW(),
                    a.BUKTI_USUL=?
            ", [Auth::user()->username, $namaFile]);

            // 7. CALL PROCEDURE
            DB::statement("CALL dcts_repair_importrak(?, ?)", [
                $namaFile,
                Auth::user()->username
            ]);

            DB::commit();

            return response()->json([
                'message' => "Berhasil ubah $affected barang"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}