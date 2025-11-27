<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class PHPengesahanFileController extends Controller
{
    public function index()
    {
        try {
            $cbg = DB::select("SELECT kode FROM toko WHERE STA = 'MA'");
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika terjadi error (misal tabel tidak ada), return array kosong
            $cbg = [];
        }
        $cbgMa = !empty($cbg) ? $cbg[0]->kode : '';

        try {
            $cbgList = DB::select("SELECT kode, nama FROM toko WHERE STA IN ('MA','CB') ORDER BY kode ASC");
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika terjadi error (misal tabel tidak ada), return array kosong
            $cbgList = [];
        }

        $title = 'Pengesahan FIle Promo Hadiah GAYAN';

        return view('promo_hadiah_pengesahan_file.index', [
            'cbgList' => $cbgList,
            'cbgMa' => $cbgMa,
            'title' => $title
        ]);
    }

    public function cekFile(Request $request)
    {
        try {
            $namafile = $request->get('namafile');
            $exten = $request->get('exten');
            $cbgMa = $request->get('cbgMa');
            $cbg = session('user_cbg', '');
            $username = session('user_name', '');

            $fullname = $namafile . '.' . $exten;

            $result = DB::select("CALL {$cbgMa}.pjl_promogayan('CEK', ?, ?, ?)", [$cbg, $fullname, $username]);

            return response()->json([
                'success' => true,
                'jumcek' => !empty($result) ? $result[0]->jumcek : 0
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function prosesFile(Request $request)
    {
        try {
            $namafile = $request->get('namafile');
            $exten = $request->get('exten');
            $cbgMa = $request->get('cbgMa');
            $cbg = session('user_cbg', '');
            $username = session('user_name', '');

            $fullname = $namafile . '.' . $exten;

            DB::beginTransaction();

            $cbgList = DB::select("SELECT KODE from toko WHERE STA in ('MA','CB')");

            foreach ($cbgList as $item) {
                DB::statement("CALL {$cbgMa}.pjl_promogayan('HAPUS', ?, ?, '')", [$item->KODE, $fullname]);
            }

            $pathDCTS = $this->getPathDCTS($cbg);
            $folderOutlet = $this->getFolderDCTS($cbg);

            $localPath = storage_path("app/promo_hadiah_pengesahan_file/{$folderOutlet}/{$fullname}");

            if (!file_exists($localPath)) {
                throw new Exception('File tidak ditemukan di: ' . $localPath);
            }

            $dbfPath = storage_path("app/promo_hadiah_pengesahan_file/BACA/" . pathinfo($fullname, PATHINFO_FILENAME) . ".DBF");

            if (!copy($localPath, $dbfPath)) {
                throw new Exception('Gagal menyalin file untuk dibaca');
            }

            $data = $this->readDBF($dbfPath, $exten);

            $datadiproses = 0;
            foreach ($data as $row) {
                $this->insertPromoGayan($row, $exten, $fullname, $cbgMa, $username);
                $datadiproses++;
            }

            foreach ($cbgList as $item) {
                DB::statement("CALL {$cbgMa}.pjl_promogayan('PROSES', ?, ?, '')", [$item->KODE, $fullname]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "({$fullname}) Diproses : {$datadiproses} baris data.",
                'datadiproses' => $datadiproses
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getCetak(Request $request)
    {
        try {
            $namafile = $request->get('namafile');
            $exten = $request->get('exten');
            $cbgMa = $request->get('cbgMa');
            $cbg = session('user_cbg', '');

            $fullname = $namafile . '.' . $exten;

            $data = DB::select("CALL {$cbgMa}.pjl_promogayan('CETAK', ?, ?, '')", [$cbg, $fullname]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createTabel(Request $request)
    {
        try {
            $cbgMa = $request->get('cbgMa');

            $cbgList = DB::select("SELECT KODE from toko WHERE STA in ('MA','CB')");

            foreach ($cbgList as $item) {
                DB::statement("CALL {$cbgMa}.pjl_promogayan('TABEL', ?, '', '')", [$item->KODE]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tabel berhasil dibuat'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function getPathDCTS($cbg)
    {
        $result = DB::select("SELECT PATH_DCTS FROM {$cbg}.toko WHERE KODE = ?", [$cbg]);
        return !empty($result) ? $result[0]->PATH_DCTS : '';
    }

    private function getFolderDCTS($cbg)
    {
        $result = DB::select("SELECT FOLDER_DCTS FROM {$cbg}.toko WHERE KODE = ?", [$cbg]);
        return !empty($result) ? $result[0]->FOLDER_DCTS : '';
    }

    private function readDBF($filepath, $exten)
    {
        if (!file_exists($filepath)) {
            throw new Exception('File DBF tidak ditemukan: ' . $filepath);
        }

        $data = [];
        $handle = fopen($filepath, 'rb');

        if (!$handle) {
            throw new Exception('Gagal membuka file DBF');
        }

        try {
            $header = fread($handle, 32);
            $unpacked = unpack('Cversion/C3date/Lrecords/SheaderSize/SrecordSize', $header);

            $recordCount = $unpacked['records'];
            $headerSize = $unpacked['headerSize'];
            $recordSize = $unpacked['recordSize'];

            fseek($handle, 32);
            $fieldCount = ($headerSize - 33) / 32;

            $fields = [];
            for ($i = 0; $i < $fieldCount; $i++) {
                $fieldInfo = fread($handle, 32);
                $field = unpack('a11name/Ctype/Loffset/Clength/Cdecimals', $fieldInfo);
                $field['name'] = trim(str_replace("\x00", '', $field['name']));
                $fields[] = $field;
            }

            fseek($handle, $headerSize);

            for ($i = 0; $i < $recordCount; $i++) {
                $record = fread($handle, $recordSize);

                if (ord($record[0]) == 0x2A) {
                    continue;
                }

                $row = [];
                $offset = 1;

                foreach ($fields as $field) {
                    $value = substr($record, $offset, $field['length']);
                    $value = trim($value);

                    if ($field['type'] == 78) {
                        $value = floatval($value);
                    } elseif ($field['type'] == 76) {
                        $value = ($value == 'T' || $value == 't' || $value == 'Y' || $value == 'y');
                    } elseif ($field['type'] == 68) {
                        if (strlen($value) == 8 && is_numeric($value)) {
                            $year = substr($value, 0, 4);
                            $month = substr($value, 4, 2);
                            $day = substr($value, 6, 2);
                            $value = "$year-$month-$day";
                        }
                    }

                    $row[$field['name']] = $value;
                    $offset += $field['length'];
                }

                $data[] = $row;
            }

            fclose($handle);

            return $data;
        } catch (Exception $e) {
            @fclose($handle);
            throw new Exception('Error membaca DBF: ' . $e->getMessage());
        }
    }

    private function insertPromoGayan($row, $exten, $namafile, $cbgMa, $username)
    {
        $noBukti = $exten . '-' . trim($row['NOMER']);
        $kdBrg = trim($row['SUB']) . trim($row['NOITEM']);

        if ($exten == 'PGH') {
            DB::statement(
                "INSERT INTO {$cbgMa}.promo_hadiah_pengesahan_file_import
                (NO_BUKTI, TGL, KD_BRG, NA_BRG, KET_UK, SUPP, TGL_DARI, TGL_SAMPAI, QTY_MAX,
                PART_SUP, PART_SUP_V, PART_TIARA, PART_TIARA_V, PPH23, PPH23_PERSEN, PPH23_KET,
                NAMAFILE, USRNM, TG_SMP)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $noBukti,
                    $row['TANGGAL'],
                    $kdBrg,
                    trim($row['NMBAR']),
                    trim($row['KET_UK']),
                    trim($row['SUPP']),
                    $row['PGH_DARI'],
                    $row['PGH_SAMPAI'],
                    $row['PGH_MAK'],
                    $row['PGH_SUP'],
                    $row['PGH_SUP_V'],
                    $row['PGH_TD'],
                    $row['PGH_TD_V'],
                    trim($row['PPH_23']),
                    $row['PPH_PER'],
                    trim($row['KET']),
                    $namafile,
                    $username
                ]
            );
        } elseif ($exten == 'PGC') {
            DB::statement(
                "INSERT INTO {$cbgMa}.promo_hadiah_pengesahan_file_import
                (NO_BUKTI, TGL, KD_BRG, NA_BRG, KET_UK, SUPP, TGL_DARI, TGL_SAMPAI, QTY_MAX,
                RP_CASHBACK, RP_CASHBACK_V, NAMAFILE, USRNM, TG_SMP)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $noBukti,
                    $row['TANGGAL'],
                    $kdBrg,
                    trim($row['NMBAR']),
                    trim($row['KET_UK']),
                    trim($row['SUPP']),
                    $row['PGC_DARI'],
                    $row['PGC_SAMPAI'],
                    $row['PGC_MAK'],
                    $row['PGC_CB'],
                    $row['PGC_CB_V'],
                    $namafile,
                    $username
                ]
            );
        } elseif ($exten == 'PGP') {
            DB::statement(
                "INSERT INTO {$cbgMa}.promo_hadiah_pengesahan_file_import
                (NO_BUKTI, TGL, KD_BRG, NA_BRG, KET_UK, SUPP, TGL_DARI, TGL_SAMPAI, QTY_MAX,
                GET_POIN, GET_POIN_V, NAMAFILE, USRNM, TG_SMP)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $noBukti,
                    $row['TANGGAL'],
                    $kdBrg,
                    trim($row['NMBAR']),
                    trim($row['KET_UK']),
                    trim($row['SUPP']),
                    $row['PGP_DARI'],
                    $row['PGP_SAMPAI'],
                    $row['PGP_MAK'],
                    $row['PGP_POIN'],
                    $row['PGP_POIN_V'],
                    $namafile,
                    $username
                ]
            );
        }
    }
}
