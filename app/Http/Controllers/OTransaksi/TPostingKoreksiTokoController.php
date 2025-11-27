<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class TPostingKoreksiTokoController extends Controller
{
    var $judul = 'Posting Koreksi Toko';
    var $FLAGZ = 'PKT';

    public function index(Request $request)
    {
        try {
            Log::info('=== TPostingKoreksiToko INDEX ===', [
                'user' => Auth::user()->username ?? 'unknown',
                'cbg' => Auth::user()->CBG ?? null
            ]);

            if (!$request->session()->has('periode')) {
                Log::warning('Periode belum diset');
                return view("otransaksi_TPostingKoreksiToko.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                Log::error('User tidak memiliki CBG');
                return view("otransaksi_TPostingKoreksiToko.index")->with([
                    'judul' => $this->judul,
                    'flagz' => $this->FLAGZ,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            Log::info('Halaman index dimuat sukses', ['cbg' => $CBG]);

            return view("otransaksi_TPostingKoreksiToko.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPostingKoreksiToko index: ' . $e->getMessage());
            return view("otransaksi_TPostingKoreksiToko.index")->with([
                'judul' => $this->judul,
                'flagz' => $this->FLAGZ,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function gettpostingkoreksitoko_posting(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;

            Log::info('=== REQUEST getData ===', [
                'all_params' => $request->all(),
                'cbg' => $CBG,
                'user' => Auth::user()->username ?? 'unknown'
            ]);

            if (!$CBG) {
                Log::error('User tidak memiliki CBG di method getData');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $flagz = $request->input('flagz', 'MT');
            if (empty($flagz)) {
                $flagz = 'MT'; // Pastikan ada default
            }

            Log::info('Get data posting koreksi toko', [
                'cbg' => $CBG,
                'flagz' => $flagz
            ]);

            // Gunakan database connection sesuai CBG
            $connection = strtolower($CBG);

            $sql = "
                SELECT
                    no_bukti,
                    tgl,
                    notes,
                    namas,
                    total_qty as total,
                    posted as cek
                FROM stockb
                WHERE flag = '{$flagz}'
                AND posted = 0
                ORDER BY no_bukti
            ";

            Log::info('QUERY - Get Data Posting', [
                'connection' => $connection,
                'sql' => $sql,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sql))
            ]);

            $query = DB::connection($connection)->select("
                SELECT
                    no_bukti,
                    tgl,
                    notes,
                    namas,
                    total_qty as total,
                    posted as cek
                FROM stockb
                WHERE flag = ?
                AND posted = 0
                ORDER BY no_bukti
            ", [$flagz]);

            $recordCount = is_array($query) ? count($query) : (is_object($query) ? count((array)$query) : 0);
            Log::info('Data ditemukan: ' . $recordCount . ' record');

            if ($recordCount > 0) {
                Log::info('Sample data pertama:', [
                    'data' => json_encode($query[0] ?? null)
                ]);
            } else {
                Log::warning('TIDAK ADA DATA ditemukan untuk flagz: ' . $flagz . ' dengan posted = 0');
            }

            return Datatables::of(collect($query))
                ->addIndexColumn()
                ->editColumn('tgl', function ($row) {
                    return date('d-m-Y', strtotime($row->tgl));
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->total, 0, ',', '.');
                })
                ->addColumn('cek_checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check-input cek-item" value="' . $row->no_bukti . '" data-cek="' . $row->cek . '">';
                })
                ->addColumn('status', function ($row) {
                    if ($row->cek == 1) {
                        return '<span class="badge badge-success">Posted</span>';
                    } else {
                        return '<span class="badge badge-danger">Belum</span>';
                    }
                })
                ->rawColumns(['cek_checkbox', 'status'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in gettpostingkoreksitoko_posting', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function posting_bulk(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $USERNAME = Auth::user()->username ?? 'unknown';

            if (!$CBG) {
                Log::error('Posting bulk gagal: User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $noBuktiList = $request->input('no_bukti_list', []);
            $flagz = $request->input('flagz', 'MT');

            Log::info('=== MULAI POSTING BULK ===', [
                'cbg' => $CBG,
                'username' => $USERNAME,
                'flagz' => $flagz,
                'jumlah_dokumen' => count($noBuktiList),
                'no_bukti_list' => $noBuktiList
            ]);

            if (empty($noBuktiList)) {
                Log::warning('Tidak ada data yang dipilih');
                return response()->json(['error' => 'Tidak ada data yang dipilih untuk diposting'], 400);
            }

            // Gunakan connection sesuai CBG
            $connection = strtolower($CBG);
            DB::connection($connection)->beginTransaction();
            Log::info('Database transaction dimulai pada connection: ' . $connection);

            foreach ($noBuktiList as $noBukti) {
                Log::info('Memproses posting untuk no_bukti: ' . $noBukti);
                $this->processPosting($noBukti, $flagz, $CBG);
                Log::info('Posting berhasil untuk no_bukti: ' . $noBukti);
            }

            DB::connection($connection)->commit();
            Log::info('=== POSTING BULK SELESAI SUKSES ===', [
                'jumlah_dokumen' => count($noBuktiList)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Posting berhasil untuk ' . count($noBuktiList) . ' dokumen'
            ]);
        } catch (\Exception $e) {
            $connection = strtolower($CBG ?? 'mysql');
            DB::connection($connection)->rollBack();

            Log::error('=== POSTING BULK GAGAL ===', [
                'flagz' => $flagz ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Posting gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processPosting($noBukti, $flagz, $CBG)
    {
        try {
            $connection = strtolower($CBG);

            Log::info('Memulai processPosting', [
                'no_bukti' => $noBukti,
                'flagz' => $flagz,
                'cbg' => $CBG,
                'connection' => $connection
            ]);

            // Ambil detail transaksi dari stockbd
            if ($flagz == 'MT') {
                Log::info('Query detail untuk Material Toko (MT)');

                $sqlDetailMT = "
                    SELECT
                        stockbd.NO_ID,
                        stockbd.KD_BRG,
                        stockbd.QTY,
                        stockbd.FLAG,
                        brgdt.KDLAKU
                    FROM stockbd
                    INNER JOIN brgdt ON stockbd.kd_brg = brgdt.kd_brg
                    WHERE stockbd.no_bukti = '{$noBukti}'
                ";

                Log::info('QUERY - Detail MT', [
                    'connection' => $connection,
                    'sql' => $sqlDetailMT,
                    'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlDetailMT))
                ]);

                $details = DB::connection($connection)->select("
                    SELECT
                        stockbd.NO_ID,
                        stockbd.KD_BRG,
                        stockbd.QTY,
                        stockbd.FLAG,
                        brgdt.KDLAKU
                    FROM stockbd
                    INNER JOIN brgdt ON stockbd.kd_brg = brgdt.kd_brg
                    WHERE stockbd.no_bukti = ?
                ", [$noBukti]);
            } else {
                Log::info('Query detail untuk ' . $flagz);

                $sqlDetailOther = "
                    SELECT
                        stockbd.JNS,
                        stockbd.NO_ID,
                        stockbd.KD_BRG,
                        stockbd.QTY,
                        stockbd.FLAG,
                        stockbd.per
                    FROM stockbd
                    WHERE stockbd.no_bukti = '{$noBukti}'
                ";

                Log::info('QUERY - Detail ' . $flagz, [
                    'connection' => $connection,
                    'sql' => $sqlDetailOther,
                    'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlDetailOther))
                ]);

                $details = DB::connection($connection)->select("
                    SELECT
                        stockbd.JNS,
                        stockbd.NO_ID,
                        stockbd.KD_BRG,
                        stockbd.QTY,
                        stockbd.FLAG,
                        stockbd.per
                    FROM stockbd
                    WHERE stockbd.no_bukti = ?
                ", [$noBukti]);
            }

            Log::info('Detail data ditemukan: ' . count($details) . ' item');

            $monthstring = '';
            $mon_hdh = '';

            if (!empty($details) && $flagz != 'MT') {
                $monthstring = substr($details[0]->per, 0, 2);
                $mon_hdh = $details[0]->JNS ?? '';
            }

            // Process setiap detail
            foreach ($details as $detail) {
                $kdBrg = $detail->KD_BRG;
                $qty = $detail->QTY;

                Log::info('Proses item', [
                    'kd_brg' => $kdBrg,
                    'qty' => $qty,
                    'flagz' => $flagz
                ]);

                if ($flagz == 'MT') {
                    // Update brgdt untuk Material Toko
                    Log::info('Update brgdt untuk kd_brg: ' . $kdBrg);

                    $sqlUpdateBrgdt = "UPDATE brgdt SET tk = '', ln00 = ln00 + {$qty}, ak00 = aw00 + ma00 - ke00 + ln00 WHERE kd_brg = '{$kdBrg}'";
                    Log::info('QUERY - Update brgdt (MT)', [
                        'connection' => $connection,
                        'kd_brg' => $kdBrg,
                        'qty' => $qty,
                        'raw_query_untuk_navicat' => $sqlUpdateBrgdt
                    ]);

                    DB::connection($connection)->statement("
                        UPDATE brgdt
                        SET
                            tk = '',
                            ln00 = ln00 + ?,
                            ak00 = aw00 + ma00 - ke00 + ln00
                        WHERE kd_brg = ?
                    ", [$qty, $kdBrg]);

                    // Delete from tpo
                    Log::info('Delete from tpo untuk kd_brg: ' . $kdBrg);

                    $sqlDeleteTpo = "DELETE FROM tpo WHERE kd_brg = '{$kdBrg}'";
                    Log::info('QUERY - Delete tpo', [
                        'connection' => $connection,
                        'kd_brg' => $kdBrg,
                        'raw_query_untuk_navicat' => $sqlDeleteTpo
                    ]);

                    DB::connection($connection)->statement("
                        DELETE FROM tpo
                        WHERE kd_brg = ?
                    ", [$kdBrg]);
                } elseif ($flagz == 'MH') {
                    // Update brghd untuk Material Hadiah (bulanan)
                    Log::info('Update brghd (MH) untuk kd_brgh: ' . $kdBrg . ', bulan: ' . $monthstring);

                    $sqlUpdateBrghdMH = "UPDATE brghd SET ln{$monthstring} = ln{$monthstring} + {$qty}, ak{$monthstring} = aw{$monthstring} + ma{$monthstring} - ke{$monthstring} + ln{$monthstring} WHERE kd_brgh = '{$kdBrg}'";
                    Log::info('QUERY - Update brghd (MH)', [
                        'connection' => $connection,
                        'kd_brgh' => $kdBrg,
                        'qty' => $qty,
                        'bulan' => $monthstring,
                        'raw_query_untuk_navicat' => $sqlUpdateBrghdMH
                    ]);

                    DB::connection($connection)->statement("
                        UPDATE brghd
                        SET
                            ln{$monthstring} = ln{$monthstring} + ?,
                            ak{$monthstring} = aw{$monthstring} + ma{$monthstring} - ke{$monthstring} + ln{$monthstring}
                        WHERE kd_brgh = ?
                    ", [$qty, $kdBrg]);

                    // Update cascade untuk semua bulan
                    $this->updateBrghdCascade($kdBrg, $CBG, $connection);
                } elseif ($flagz == 'HS') {
                    // Update brghd untuk Hadiah Standar
                    Log::info('Update brghd (HS) untuk kd_brgh: ' . $kdBrg . ', jns: ' . $mon_hdh);

                    $sqlUpdateBrghdHS = "UPDATE brghd SET ln{$mon_hdh} = ln{$mon_hdh} + {$qty}, ak{$mon_hdh} = aw{$mon_hdh} + ma{$mon_hdh} - ke{$mon_hdh} + ln{$mon_hdh} WHERE kd_brgh = '{$kdBrg}'";
                    Log::info('QUERY - Update brghd (HS)', [
                        'connection' => $connection,
                        'kd_brgh' => $kdBrg,
                        'qty' => $qty,
                        'jns' => $mon_hdh,
                        'raw_query_untuk_navicat' => $sqlUpdateBrghdHS
                    ]);

                    DB::connection($connection)->statement("
                        UPDATE brghd
                        SET
                            ln{$mon_hdh} = ln{$mon_hdh} + ?,
                            ak{$mon_hdh} = aw{$mon_hdh} + ma{$mon_hdh} - ke{$mon_hdh} + ln{$mon_hdh}
                        WHERE kd_brgh = ?
                    ", [$qty, $kdBrg]);

                    // Update cascade untuk semua bulan
                    $this->updateBrghdCascade($kdBrg, $CBG, $connection);
                } elseif ($flagz == 'HZ') {
                    // Update brghd untuk Hadiah (dengan prefix 'g')
                    Log::info('Update brghd (HZ) dengan prefix g untuk kd_brgh: ' . $kdBrg . ', jns: ' . $mon_hdh);

                    $sqlUpdateBrghdHZ = "UPDATE brghd SET gln{$mon_hdh} = gln{$mon_hdh} + {$qty}, gak{$mon_hdh} = gaw{$mon_hdh} + gma{$mon_hdh} - gke{$mon_hdh} + gln{$mon_hdh} WHERE kd_brgh = '{$kdBrg}'";
                    Log::info('QUERY - Update brghd (HZ) dengan G-prefix', [
                        'connection' => $connection,
                        'kd_brgh' => $kdBrg,
                        'qty' => $qty,
                        'jns' => $mon_hdh,
                        'raw_query_untuk_navicat' => $sqlUpdateBrghdHZ
                    ]);

                    DB::connection($connection)->statement("
                        UPDATE brghd
                        SET
                            gln{$mon_hdh} = gln{$mon_hdh} + ?,
                            gak{$mon_hdh} = gaw{$mon_hdh} + gma{$mon_hdh} - gke{$mon_hdh} + gln{$mon_hdh}
                        WHERE kd_brgh = ?
                    ", [$qty, $kdBrg]);

                    // Update cascade untuk semua bulan (dengan prefix 'g')
                    $this->updateBrghdCascadeG($kdBrg, $CBG, $connection);
                }
            }

            // Call stored procedure untuk post
            Log::info('Memanggil stored procedure poststkb untuk no_bukti: ' . $noBukti);

            $sqlCallSP = "CALL poststkb('{$noBukti}')";
            Log::info('QUERY - Call Stored Procedure', [
                'connection' => $connection,
                'no_bukti' => $noBukti,
                'raw_query_untuk_navicat' => $sqlCallSP
            ]);

            DB::connection($connection)->statement("CALL poststkb(?)", [$noBukti]);
            Log::info('Stored procedure poststkb selesai');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function updateBrghdCascade($kdBrg, $CBG, $connection)
    {
        Log::info('Update cascade brghd untuk kd_brgh: ' . $kdBrg);

        $sqlCascade = "UPDATE brghd SET AK01 = AW01 + MA01 - KE01 + LN01, AW02 = AK01, AK02 = AW02 + MA02 - KE02 + LN02, AW03 = AK02, AK03 = AW03 + MA03 - KE03 + LN03, AW04 = AK03, AK04 = AW04 + MA04 - KE04 + LN04, AW05 = AK04, AK05 = AW05 + MA05 - KE05 + LN05, AW06 = AK05, AK06 = AW06 + MA06 - KE06 + LN06, AW07 = AK06, AK07 = AW07 + MA07 - KE07 + LN07, AW08 = AK07, AK08 = AW08 + MA08 - KE08 + LN08, AW09 = AK08, AK09 = AW09 + MA09 - KE09 + LN09, AW10 = AK09, AK10 = AW10 + MA10 - KE10 + LN10, AW11 = AK10, AK11 = AW11 + MA11 - KE11 + LN11, AW12 = AK11, AK12 = AW12 + MA12 - KE12 + LN12, AW00 = AK12, AK00 = AW00 + MA00 - KE00 + LN00 WHERE kd_brgh = '{$kdBrg}'";

        Log::info('QUERY - Update Cascade brghd', [
            'connection' => $connection,
            'kd_brgh' => $kdBrg,
            'raw_query_untuk_navicat' => $sqlCascade
        ]);

        DB::connection($connection)->statement("
            UPDATE brghd SET
                AK01 = AW01 + MA01 - KE01 + LN01, AW02 = AK01,
                AK02 = AW02 + MA02 - KE02 + LN02, AW03 = AK02,
                AK03 = AW03 + MA03 - KE03 + LN03, AW04 = AK03,
                AK04 = AW04 + MA04 - KE04 + LN04, AW05 = AK04,
                AK05 = AW05 + MA05 - KE05 + LN05, AW06 = AK05,
                AK06 = AW06 + MA06 - KE06 + LN06, AW07 = AK06,
                AK07 = AW07 + MA07 - KE07 + LN07, AW08 = AK07,
                AK08 = AW08 + MA08 - KE08 + LN08, AW09 = AK08,
                AK09 = AW09 + MA09 - KE09 + LN09, AW10 = AK09,
                AK10 = AW10 + MA10 - KE10 + LN10, AW11 = AK10,
                AK11 = AW11 + MA11 - KE11 + LN11, AW12 = AK11,
                AK12 = AW12 + MA12 - KE12 + LN12, AW00 = AK12,
                AK00 = AW00 + MA00 - KE00 + LN00
            WHERE kd_brgh = ?
        ", [$kdBrg]);
    }

    private function updateBrghdCascadeG($kdBrg, $CBG, $connection)
    {
        Log::info('Update cascade brghd (G-prefix) untuk kd_brgh: ' . $kdBrg);

        $sqlCascadeG = "UPDATE brghd SET GAK01 = GAW01 + GMA01 - GKE01 + GLN01, GAW02 = GAK01, GAK02 = GAW02 + GMA02 - GKE02 + GLN02, GAW03 = GAK02, GAK03 = GAW03 + GMA03 - GKE03 + GLN03, GAW04 = GAK03, GAK04 = GAW04 + GMA04 - GKE04 + GLN04, GAW05 = GAK04, GAK05 = GAW05 + GMA05 - GKE05 + GLN05, GAW06 = GAK05, GAK06 = GAW06 + GMA06 - GKE06 + GLN06, GAW07 = GAK06, GAK07 = GAW07 + GMA07 - GKE07 + GLN07, GAW08 = GAK07, GAK08 = GAW08 + GMA08 - GKE08 + GLN08, GAW09 = GAK08, GAK09 = GAW09 + GMA09 - GKE09 + GLN09, GAW10 = GAK09, GAK10 = GAW10 + GMA10 - GKE10 + GLN10, GAW11 = GAK10, GAK11 = GAW11 + GMA11 - GKE11 + GLN11, GAW12 = GAK11, GAK12 = GAW12 + GMA12 - GKE12 + GLN12 WHERE kd_brgh = '{$kdBrg}'";

        Log::info('QUERY - Update Cascade brghd (G-prefix)', [
            'connection' => $connection,
            'kd_brgh' => $kdBrg,
            'raw_query_untuk_navicat' => $sqlCascadeG
        ]);

        DB::connection($connection)->statement("
            UPDATE brghd SET
                GAK01 = GAW01 + GMA01 - GKE01 + GLN01, GAW02 = GAK01,
                GAK02 = GAW02 + GMA02 - GKE02 + GLN02, GAW03 = GAK02,
                GAK03 = GAW03 + GMA03 - GKE03 + GLN03, GAW04 = GAK03,
                GAK04 = GAW04 + GMA04 - GKE04 + GLN04, GAW05 = GAK04,
                GAK05 = GAW05 + GMA05 - GKE05 + GLN05, GAW06 = GAK05,
                GAK06 = GAW06 + GMA06 - GKE06 + GLN06, GAW07 = GAK06,
                GAK07 = GAW07 + GMA07 - GKE07 + GLN07, GAW08 = GAK07,
                GAK08 = GAW08 + GMA08 - GKE08 + GLN08, GAW09 = GAK08,
                GAK09 = GAW09 + GMA09 - GKE09 + GLN09, GAW10 = GAK09,
                GAK10 = GAW10 + GMA10 - GKE10 + GLN10, GAW11 = GAK10,
                GAK11 = GAW11 + GMA11 - GKE11 + GLN11, GAW12 = GAK11,
                GAK12 = GAW12 + GMA12 - GKE12 + GLN12
            WHERE kd_brgh = ?
        ", [$kdBrg]);
    }

    public function jasper(Request $request)
    {
        try {
            $judul = $this->judul;
            $CBG = Auth::user()->CBG ?? null;
            $flagz = $request->input('flagz', 'MT');

            Log::info('Generate laporan jasper', [
                'cbg' => $CBG,
                'flagz' => $flagz
            ]);

            if (!$CBG) {
                Log::error('Jasper error: User tidak memiliki CBG');
                return redirect()->back()->with('error', 'User tidak memiliki akses cabang');
            }

            $connection = strtolower($CBG);

            $sqlJasper = "
                SELECT
                    no_bukti,
                    tgl,
                    notes,
                    namas,
                    total_qty as total,
                    IF(posted = 1, 'Sudah Posting', 'Belum Posting') as status_text
                FROM stockb
                WHERE flag = '{$flagz}'
                ORDER BY no_bukti
            ";

            Log::info('QUERY - Jasper Report', [
                'connection' => $connection,
                'flagz' => $flagz,
                'sql' => $sqlJasper,
                'raw_query_untuk_navicat' => trim(preg_replace('/\s+/', ' ', $sqlJasper))
            ]);

            $query = DB::connection($connection)->select("
                SELECT
                    no_bukti,
                    tgl,
                    notes,
                    namas,
                    total_qty as total,
                    IF(posted = 1, 'Sudah Posting', 'Belum Posting') as status_text
                FROM stockb
                WHERE flag = ?
                ORDER BY no_bukti
            ", [$flagz]);

            $data = [];
            foreach ($query as $value) {
                array_push($data, array(
                    'NO_BUKTI' => $value->no_bukti,
                    'TANGGAL' => date('d-m-Y', strtotime($value->tgl)),
                    'NOTES' => $value->notes,
                    'SUPPLIER' => $value->namas,
                    'TOTAL' => number_format($value->total, 0, ',', '.'),
                    'STATUS' => $value->status_text,
                    'JUDUL' => $judul
                ));
            }

            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path() . '/app/reportc01/phpjasperxml/posting_koreksi_toko.jrxml');
            $PHPJasperXML->setData($data);
            ob_end_clean();
            $PHPJasperXML->outpage("I");
        } catch (\Exception $e) {
            Log::error('Error in jasper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal generate laporan: ' . $e->getMessage());
        }
    }
}
