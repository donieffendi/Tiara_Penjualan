<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TBeteBeteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Transaksi Bete Bete';

            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                return view("otransaksi_TBeteBete.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TBeteBete.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TBeteBete.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'username' => $username
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TBeteBete index: ' . $e->getMessage());
            return view("otransaksi_TBeteBete.index")->with([
                'judul' => 'Transaksi Bete Bete',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Ambil data untuk datatables
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';

            if (!$CBG) {
                Log::error('TBeteBete cari_data: User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                Log::error('TBeteBete cari_data: Periode belum diset');
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            Log::info('TBeteBete cari_data: CBG=' . $CBG . ', Periode=' . json_encode($periode));

            $cibing = $CBG; // TGZ, TMM, atau SOP

            // Check if table bete exists and has required columns
            try {
                $tableCheck = DB::select("SHOW TABLES LIKE 'bete'");
                if (empty($tableCheck)) {
                    Log::error('TBeteBete cari_data: Tabel bete tidak ditemukan');
                    return Datatables::of(collect([]))->make(true);
                }

                // Check columns exist
                $columnCheck = DB::select("SHOW COLUMNS FROM bete LIKE ?", [$cibing]);
                if (empty($columnCheck)) {
                    Log::warning('TBeteBete cari_data: Kolom ' . $cibing . ' tidak ada di tabel bete');
                    return Datatables::of(collect([]))->make(true);
                }
            } catch (\Exception $e) {
                Log::error('TBeteBete cari_data table check error: ' . $e->getMessage());
                return Datatables::of(collect([]))->make(true);
            }

            // Query untuk menampilkan data bete dengan flag cibing = 0
            $query = "
                SELECT 
                    b.rec,
                    b.SUB,
                    b.KD_BRG,
                    b.NA_BRG,
                    b.HJUSUL,
                    b.DISKON1 as D1,
                    b.DISKON2 as D2,
                    b.DISKON3 as D3,
                    b.PPN,
                    b.MARGIN as MRG,
                    b.KODES as SUPP,
                    CASE 
                        WHEN ? = 'TGZ' THEN b.HLTGZ
                        WHEN ? = 'TMM' THEN b.HLTMM
                        WHEN ? = 'SOP' THEN b.HLSOP
                        ELSE 0
                    END as HRG_LALU,
                    CASE 
                        WHEN ? = 'TGZ' THEN b.HJTGZ
                        WHEN ? = 'TMM' THEN b.HJTMM
                        WHEN ? = 'SOP' THEN b.HJSOP
                        ELSE 0
                    END as HRG_JUAL,
                    CASE 
                        WHEN ? = 'TGZ' THEN b.HBTGZ
                        WHEN ? = 'TMM' THEN b.HBTMM
                        WHEN ? = 'SOP' THEN b.HBSOP
                        ELSE 0
                    END as HRG_BELI,
                    COALESCE(b.KET1, '') as ALASAN,
                    COALESCE(b.KET1, '') as NOTES
                FROM bete b
                WHERE CASE 
                    WHEN ? = 'TGZ' THEN b.TGZ = 0
                    WHEN ? = 'TMM' THEN b.TMM = 0
                    WHEN ? = 'SOP' THEN b.SOP = 0
                    ELSE 1 = 0
                END
                ORDER BY b.KD_BRG ASC
            ";

            $data = DB::select($query, [
                $cibing,
                $cibing,
                $cibing,
                $cibing,
                $cibing,
                $cibing,
                $cibing,
                $cibing,
                $cibing,
                $cibing,
                $cibing,
                $cibing
            ]);

            Log::info('TBeteBete cari_data: Found ' . count($data) . ' records');

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('HJUSUL', function ($row) {
                    return number_format((float)$row->HJUSUL, 0, ',', '.');
                })
                ->editColumn('D1', function ($row) {
                    return number_format((float)$row->D1, 2, ',', '.');
                })
                ->editColumn('D2', function ($row) {
                    return number_format((float)$row->D2, 2, ',', '.');
                })
                ->editColumn('D3', function ($row) {
                    return number_format((float)$row->D3, 2, ',', '.');
                })
                ->editColumn('PPN', function ($row) {
                    return number_format((float)$row->PPN, 2, ',', '.');
                })
                ->editColumn('MRG', function ($row) {
                    return number_format((float)$row->MRG, 2, ',', '.');
                })
                ->editColumn('HRG_LALU', function ($row) {
                    return number_format((float)$row->HRG_LALU, 0, ',', '.');
                })
                ->editColumn('HRG_JUAL', function ($row) {
                    return number_format((float)$row->HRG_JUAL, 0, ',', '.');
                })
                ->editColumn('ALASAN', function ($row) {
                    return $row->ALASAN ?? '-';
                })
                ->editColumn('NOTES', function ($row) {
                    return $row->NOTES ?? '-';
                })
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Proses untuk berbagai action
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
                case 'tampilkan':
                    return $this->tampilkanData($request, $CBG);

                case 'proses':
                    return $this->prosesHitung($request, $CBG);

                case 'simpan':
                    return $this->simpanData($request, $CBG, $username);

                case 'proses_catatan':
                    return $this->prosesCatatan($request, $CBG);

                case 'export_excel':
                    return $this->exportExcel($request, $CBG);

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

    private function tampilkanData($request, $CBG)
    {
        // Update margin dari tabel brg ke bete
        DB::statement("
            UPDATE bete b, brg br
            SET b.margin = br.margin
            WHERE b.kd_brg = br.kd_brg
            AND CASE 
                WHEN ? = 'TGZ' THEN b.TGZ = 0
                WHEN ? = 'TMM' THEN b.TMM = 0
                WHEN ? = 'SOP' THEN b.SOP = 0
            END
        ", [$CBG, $CBG, $CBG]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditampilkan!'
        ]);
    }

    private function prosesHitung($request, $CBG)
    {
        $cibing = $CBG;

        // Ambil semua data yang perlu dihitung
        $dataList = DB::select("
            SELECT 
                b.rec,
                b.KD_BRG,
                b.SUB,
                b.HJUSUL,
                b.DISKON1,
                b.DISKON2,
                b.DISKON3,
                b.PPN,
                b.MARGIN,
                CASE 
                    WHEN ? = 'TGZ' THEN b.HJTGZ
                    WHEN ? = 'TMM' THEN b.HJTMM
                    WHEN ? = 'SOP' THEN b.HJSOP
                END as HJ_CURRENT
            FROM bete b
            WHERE CASE 
                WHEN ? = 'TGZ' THEN b.TGZ = 0
                WHEN ? = 'TMM' THEN b.TMM = 0
                WHEN ? = 'SOP' THEN b.SOP = 0
            END
        ", [$cibing, $cibing, $cibing, $cibing, $cibing, $cibing]);

        foreach ($dataList as $row) {
            // Hanya proses jika HJ_CURRENT = 0
            if ($row->HJ_CURRENT == 0) {
                $result = $this->hitungHarga(
                    $row->SUB,
                    $row->HJUSUL,
                    $row->DISKON1,
                    $row->DISKON2,
                    $row->DISKON3,
                    $row->PPN,
                    $row->MARGIN
                );

                // Update ke database
                DB::statement("
                    UPDATE bete b, brgdt bd
                    SET 
                        b.HB{$cibing} = ?,
                        b.HJ{$cibing} = ?,
                        b.HL{$cibing} = IF(bd.hj = 0, 1, bd.hj),
                        b.margin = ?
                    WHERE b.{$cibing} = 0 
                    AND b.kd_brg = ?
                    AND b.kd_brg = bd.kd_brg 
                    AND bd.cbg = ?
                    AND bd.td_od <> '*'
                ", [
                    $result['hb'],
                    $result['hj'],
                    $result['margin'],
                    $row->KD_BRG,
                    $CBG
                ]);
            }
        }

        // Update kode supplier dengan flag -U atau -P
        DB::statement("
            UPDATE bete b, brg br
            SET b.kodes = IF(
                b.kodes = br.supp,
                CONCAT(b.kodes, '-U'),
                CONCAT(b.kodes, '-P')
            )
            WHERE ((RIGHT(b.kodes, 2) <> '-P') AND (RIGHT(b.kodes, 2) <> '-U'))
            AND b.{$cibing} = 0
            AND b.kd_brg = br.kd_brg
        ", [$cibing]);

        // Set flag = 2 untuk data dengan HL = 0 atau supplier -P
        DB::statement("
            UPDATE bete
            SET {$cibing} = 2
            WHERE {$cibing} = 0
            AND (HL{$cibing} = 0 OR RIGHT(kodes, 2) = '-P')
        ", [$cibing, $cibing]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Proses hitung selesai!'
        ]);
    }

    private function hitungHarga($sub, $hjusul, $d1, $d2, $d3, $ppn, $margin)
    {
        // Ambil persentase margin dari aotprice
        $aotprice = DB::selectOne("
            SELECT persen FROM aotprice WHERE sub = ?
        ", [$sub]);

        if ($aotprice) {
            $mg = $aotprice->persen;
        } else {
            $mg = $margin;
        }

        // Ambil nilai PPN jika > 0
        if ($ppn > 0) {
            $ppnData = DB::selectOne("CALL xppn()");
            $pn = $ppnData->PN ?? 0;
        } else {
            $pn = 0;
        }

        $x = round($hjusul);
        $y = round($hjusul);

        // Logika perhitungan HJ (Harga Jual)
        if ($sub >= '086' && $sub <= '096') {
            $x = floor($x / 10) * 10;
        } elseif ($sub == '***') {
            // Tidak ada perubahan
        } else {
            $x = $x * (100 - $d1) / 100;
            $x = $x * (100 - $d2) / 100;
            $x = $x * (100 + $pn) / 100;
            $x = $x * (100 + $mg) / 100;

            $result = DB::selectOne("SELECT ROUND(?, -1) as xx", [$x]);
            $x = $result->xx;
        }

        // Logika perhitungan HB (Harga Beli)
        if ($sub != '***') {
            $y = $y * (100 - $d1) / 100;
            $y = $y * (100 - $d2) / 100;
            $y = $y * (100 + $pn) / 100;

            $result = DB::selectOne("SELECT ROUND(?, -1) as yy", [$y]);
            $y = $result->yy;
        }

        // Pembulatan khusus untuk harga jual
        $result = DB::selectOne("
            SELECT 
                IF(? >= 1000 AND ? < 10000, 
                    IF(SUBSTR(?, 2, 2) = '00', ROUND(? - RIGHT(?, 1) - 10), ?),
                IF(? >= 10000 AND ? < 100000,
                    IF(SUBSTR(?, 3, 1) = '0', ROUND(? - RIGHT(?, 2) - 20), ?),
                IF(? >= 100000 AND ? < 1000000,
                    IF(SUBSTR(?, 3, 1) = '0', ROUND(? - RIGHT(?, 3) - 150), ?),
                IF(? >= 1000000 AND ? < 10000000,
                    IF(SUBSTR(?, 3, 1) = '0', ROUND(? - RIGHT(?, 4) - 1500), ?), ?)))) as hrg
        ", [$x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x, $x]);

        $x = $result->hrg;

        // Final rounding
        if (!($sub >= '086' && $sub <= '096') && $sub != '***') {
            $result = DB::selectOne("SELECT ROUND(?, -1) as xx", [$x]);
            $x = $result->xx;
        }

        return [
            'hj' => $x,
            'hb' => $y,
            'margin' => $mg
        ];
    }

    private function simpanData($request, $CBG, $username)
    {
        $periode = $request->session()->get('periode');
        $monthString = substr($periode, 0, 2);
        $yearString = substr($periode, -4);

        $flag = 'UH';
        $cibing = $CBG;

        // Tentukan kode tipe toko
        $tokoData = DB::selectOne("SELECT type FROM toko WHERE kode = ?", [$CBG]);
        $kode2 = $tokoData->type ?? '';

        // Ambil nomor bukti terakhir
        $notrans = DB::selectOne("
            SELECT NOM{$monthString} as NO_BUKTI 
            FROM notrans 
            WHERE trans = ? AND PER = ?
        ", [$flag, $yearString]);

        $r1 = ($notrans->NO_BUKTI ?? 0) + 1;

        // Update nomor bukti
        DB::statement("
            UPDATE notrans 
            SET NOM{$monthString} = ?
            WHERE trans = ? AND PER = ?
        ", [$r1, $flag, $yearString]);

        // Generate nomor bukti
        $kode = $flag . substr($yearString, -2) . $monthString;
        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        $bukti = $kode . '-' . $bkt1 . $kode2;

        // Insert ke HISTO
        DB::statement("
            INSERT INTO HISTO(NO_BUKTI, TGL, FLAG, CBG, TYPE, USRNM, PER)
            VALUES (?, DATE(NOW()), ?, ?, 1, ?, ?)
        ", [$bukti, $flag, $CBG, $username, $periode]);

        // Ambil ID yang baru dibuat
        $id = DB::selectOne("
            SELECT no_id FROM HISTO WHERE no_bukti = ?
        ", [$bukti])->no_id;

        // Ambil data yang akan disimpan
        $dataList = DB::select("
            SELECT 
                b.KD_BRG,
                b.NA_BRG,
                CASE 
                    WHEN ? = 'TGZ' THEN b.HJTGZ
                    WHEN ? = 'TMM' THEN b.HJTMM
                    WHEN ? = 'SOP' THEN b.HJSOP
                END as HJ_BR
            FROM bete b
            WHERE CASE 
                WHEN ? = 'TGZ' THEN b.TGZ = 0 AND b.HLTGZ <> 0
                WHEN ? = 'TMM' THEN b.TMM = 0 AND b.HLTMM <> 0
                WHEN ? = 'SOP' THEN b.SOP = 0 AND b.HLSOP <> 0
            END
        ", [$cibing, $cibing, $cibing, $cibing, $cibing, $cibing]);

        foreach ($dataList as $row) {
            // Ambil harga dari brgdt
            $brgdt = DB::selectOne("
                SELECT HJ, HJ2 
                FROM brgdt 
                WHERE CBG = ? AND KD_BRG = ?
            ", [$CBG, $row->KD_BRG]);

            // Insert ke HISTOD
            DB::statement("
                INSERT INTO HISTOD(NO_BUKTI, TGL, id, kode, uraian, hj2, hj, hjbr, ket)
                VALUES (?, DATE(NOW()), ?, ?, ?, ?, ?, ?, 'DARI BETE')
            ", [
                $bukti,
                $id,
                $row->KD_BRG,
                $row->NA_BRG,
                $brgdt->HJ2 ?? 0,
                $brgdt->HJ ?? 0,
                $row->HJ_BR
            ]);
        }

        // Update flag bete menjadi 1
        DB::statement("
            UPDATE bete
            SET {$cibing} = 1
            WHERE {$cibing} = 0 AND HL{$cibing} <> 0
        ", [$cibing, $cibing]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan!',
            'bukti' => $bukti
        ]);
    }

    private function prosesCatatan($request, $CBG)
    {
        $cibing = $CBG;
        $cbgx = $CBG;

        // Tentukan kode untuk histod
        $pok1 = '';
        if ($CBG == 'TGZ') $pok1 = 'Z';
        elseif ($CBG == 'TMM') $pok1 = 'M';
        elseif ($CBG == 'SOP') $pok1 = 'S';

        // Ambil semua data bete yang perlu diproses
        $dataList = DB::select("
            SELECT KD_BRG
            FROM bete
            WHERE {$cibing} = 0
        ");

        foreach ($dataList as $row) {
            $kode = $row->KD_BRG;

            // Ambil ket1 dari histod terakhir
            $ket1Data = DB::selectOne("
                SELECT ket
                FROM histod
                WHERE RIGHT(no_bukti, 1) = ?
                AND LEFT(no_bukti, 2) = 'UH'
                AND kode = ?
                ORDER BY NO_ID DESC
                LIMIT 1
            ", [$pok1, $kode]);

            $ket1 = $ket1Data->ket ?? '';

            // Ambil ket2 dari diskon yang aktif
            $ket2Data = DB::selectOne("
                SELECT DIS.no_bukti
                FROM dis, disd
                WHERE DIS.no_bukti = disd.no_bukti
                AND DIS.TGL_MULAI <= DATE(NOW())
                AND DIS.TGL_SLS >= DATE(NOW())
                AND DIS.cbg = ?
                AND disd.kd_brg = ?
                ORDER BY DIS.NO_ID DESC
                LIMIT 1
            ", [$cbgx, $kode]);

            $ket2 = $ket2Data->no_bukti ?? '';

            // Update ket1 di bete
            DB::statement("
                UPDATE bete
                SET ket1 = CONCAT(?, ' ', ?)
                WHERE kd_brg = ?
                AND {$cibing} = 0
            ", [$ket1, $ket2, $kode]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Proses catatan selesai!'
        ]);
    }

    private function exportExcel($request, $CBG)
    {
        $cibing = $CBG;

        // Get data untuk export excel
        $data = DB::select("
            SELECT 
                b.SUB as 'Sub Item',
                b.NA_BRG as 'Nama Barang',
                b.HJUSUL as 'HRG Usul',
                b.DISKON1 as 'D1',
                b.DISKON2 as 'D2',
                b.DISKON3 as 'D3',
                b.PPN as 'PPN',
                b.MARGIN as 'MRG',
                b.KODES as 'Supplier',
                CASE 
                    WHEN ? = 'TGZ' THEN b.HLTGZ
                    WHEN ? = 'TMM' THEN b.HLTMM
                    WHEN ? = 'SOP' THEN b.HLSOP
                END as 'Harga Lalu',
                CASE 
                    WHEN ? = 'TGZ' THEN b.HJTGZ
                    WHEN ? = 'TMM' THEN b.HJTMM
                    WHEN ? = 'SOP' THEN b.HJSOP
                END as 'Harga Jual',
                b.KET1 as 'Alasan',
                b.KET1 as 'Notes'
            FROM bete b
            WHERE CASE 
                WHEN ? = 'TGZ' THEN b.TGZ = 0
                WHEN ? = 'TMM' THEN b.TMM = 0
                WHEN ? = 'SOP' THEN b.SOP = 0
            END
            ORDER BY b.KD_BRG ASC
        ", [$cibing, $cibing, $cibing, $cibing, $cibing, $cibing, $cibing, $cibing, $cibing]);

        if (empty($data)) {
            DB::rollBack();
            return response()->json(['error' => 'Tidak ada data untuk di-export'], 404);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
