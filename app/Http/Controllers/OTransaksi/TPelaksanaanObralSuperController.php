<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TPelaksanaanObralSuperController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Deteksi jenis transaksi berdasarkan route
            $routeName = $request->route()->getName();

            if ($routeName === 'postingflashsale') {
                $judul = 'Posting Flash Sale';
                $flagz = 'FS';
            } else {
                $judul = 'Pelaksanaan Obral Supermarket';
                $flagz = 'OB';
            }

            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return view("otransaksi_TPelaksanaan.index")->with([
                    'judul' => $judul,
                    'flagz' => $flagz,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.'
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TPelaksanaan.index")->with([
                    'judul' => $judul,
                    'flagz' => $flagz,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.'
                ]);
            }

            return view("otransaksi_TPelaksanaan.index")->with([
                'judul' => $judul,
                'flagz' => $flagz,
                'cbg' => $CBG
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPelaksanaanObralSuper index: ' . $e->getMessage());
            return view("otransaksi_TPelaksanaan.index")->with([
                'judul' => $judul ?? 'Pelaksanaan Obral',
                'flagz' => $flagz ?? 'OB',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return response()->json(['error' => 'Periode belum diset'], 400);
            }

            // Deteksi flagz dari request
            $flagz = $request->input('flagz', 'OB');

            // Tentukan kolom posted berdasarkan CBG
            $postedColumn = $this->getPostedColumn($CBG);

            $sqlWhere = '';
            if ($flagz === 'FS') {
                $sqlWhere = " AND flag2 = 'FS' ";
            }

            $query = DB::SELECT("
                SELECT
                    NO_BUKTI,
                    TGL,
                    KODES,
                    NAMAS,
                    notes,
                    TGL_MULAI,
                    TGL_SLS,
                    ? as CBG,
                    {$postedColumn} as posted
                FROM dis
                WHERE flag = 'OB'
                {$sqlWhere}
                GROUP BY no_bukti
                ORDER BY NO_BUKTI DESC
            ", [$CBG]);

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL));
                })
                ->editColumn('TGL_MULAI', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL_MULAI));
                })
                ->editColumn('TGL_SLS', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL_SLS));
                })
                ->editColumn('posted', function ($row) {
                    if ($row->posted == 1) {
                        return '<span class="badge badge-success">Posted</span>';
                    } else {
                        return '<span class="badge badge-secondary">Belum Posted</span>';
                    }
                })
                ->addColumn('cek_checkbox', function ($row) {
                    $disabled = $row->posted == 1 ? 'disabled' : '';
                    return '<input type="checkbox" class="form-check-input cek-item" value="' . $row->NO_BUKTI . '" data-posted="' . $row->posted . '" ' . $disabled . '>';
                })
                ->rawColumns(['cek_checkbox', 'posted'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function detail(Request $request, $no_bukti)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $noBuktiList = explode(',', $no_bukti);
            $flagz = $request->input('flagz', 'OB');
            $action = $request->input('action', 'posting'); // posting atau batal

            if (empty($noBuktiList)) {
                return response()->json(['error' => 'Tidak ada data yang dipilih'], 400);
            }

            DB::beginTransaction();

            $successCount = 0;
            $errors = [];

            // Ambil list cabang yang akan diproses
            $cabangList = DB::SELECT("
                SELECT TRIM(KODE) as cbg
                FROM toko
                WHERE STA IN ('MA', 'CB')
                AND KODE = ?
                ORDER BY NO_ID ASC
            ", [$CBG]);

            foreach ($noBuktiList as $bukti) {
                try {
                    $bukti = trim($bukti);

                    if ($action === 'batal') {
                        // Proses pembatalan posting
                        $this->processBatalPosting($bukti, $flagz, $cabangList);
                    } else {
                        // Proses posting
                        $this->processPosting($bukti, $flagz, $cabangList);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error pada bukti {$bukti}: " . $e->getMessage();
                    Log::error("Error processing {$bukti}: " . $e->getMessage());
                }
            }

            DB::commit();

            $actionText = $action === 'batal' ? 'Pembatalan posting' : 'Posting';

            if ($successCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "{$actionText} berhasil untuk {$successCount} dokumen",
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'error' => "Semua {$actionText} gagal",
                    'details' => $errors
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processPosting($noBukti, $flagz, $cabangList)
    {
        foreach ($cabangList as $cabang) {
            $cbg = $cabang->cbg;

            // Tentukan kolom berdasarkan cabang
            $columnInfo = $this->getColumnInfo($cbg);
            $cibing = $columnInfo['tgl_column'];
            $trn = $columnInfo['dis_column'];
            $harga = $columnInfo['harga_column'];

            // Update masks untuk setiap cabang
            DB::statement("
                UPDATE {$cbg}.masks, (
                    SELECT
                        dis.JAM_MULAI,
                        dis.JAM_SLS,
                        dis.TGL_MULAI,
                        dis.TGL_SLS,
                        disd.KD_BRG,
                        disd.NA_BRG,
                        disd.dis
                    FROM dis, disd
                    WHERE dis.no_bukti = disd.no_bukti
                    AND dis.no_bukti = ?
                ) as ageng
                SET
                    masks.dis = ageng.dis,
                    masks.{$trn} = ageng.dis,
                    masks.JAM = ageng.jam_mulai,
                    masks.JAMSLS = ageng.jam_sls,
                    masks.TGDIS_M = ageng.tgl_mulai,
                    masks.TGDIS_A = ageng.tgl_sls
                WHERE masks.kd_brg = ageng.kd_brg
            ", [$noBukti]);

            // Update status posted di dis
            DB::statement("
                UPDATE dis
                SET {$cbg} = 1, {$cibing} = NOW()
                WHERE no_bukti = ?
            ", [$noBukti]);
        }

        return true;
    }

    private function processBatalPosting($noBukti, $flagz, $cabangList)
    {
        foreach ($cabangList as $cabang) {
            $cbg = $cabang->cbg;

            // Tentukan kolom berdasarkan cabang
            $columnInfo = $this->getColumnInfo($cbg);
            $cibing = $columnInfo['tgl_column'];
            $trn = $columnInfo['dis_column'];

            // Reset masks untuk setiap cabang
            DB::statement("
                UPDATE {$cbg}.masks, (
                    SELECT
                        dis.JAM_MULAI,
                        dis.JAM_SLS,
                        dis.TGL_MULAI,
                        dis.TGL_SLS,
                        disd.KD_BRG,
                        disd.NA_BRG,
                        disd.dis
                    FROM dis, disd
                    WHERE dis.no_bukti = disd.no_bukti
                    AND dis.no_bukti = ?
                ) as ageng
                SET
                    masks.dis = 0,
                    masks.{$trn} = 0,
                    masks.JAM = ageng.jam_mulai,
                    masks.JAMSLS = ageng.jam_sls,
                    masks.TGDIS_M = ageng.tgl_mulai,
                    masks.TGDIS_A = ageng.tgl_sls
                WHERE masks.kd_brg = ageng.kd_brg
            ", [$noBukti]);

            // Update status posted di dis menjadi 0
            DB::statement("
                UPDATE dis
                SET {$cbg} = 0, {$cibing} = NOW()
                WHERE no_bukti = ?
            ", [$noBukti]);
        }

        return true;
    }

    private function getColumnInfo($cbg)
    {
        switch ($cbg) {
            case 'TGZ':
                return [
                    'tgl_column' => 'TGL_GZ',
                    'dis_column' => 'DISGZ',
                    'harga_column' => 'HJGZ'
                ];
            case 'TMM':
                return [
                    'tgl_column' => 'TGL_MM',
                    'dis_column' => 'DISMM',
                    'harga_column' => 'HJMM'
                ];
            case 'SOP':
                return [
                    'tgl_column' => 'TGL_SP',
                    'dis_column' => 'DISSP',
                    'harga_column' => 'HJSP'
                ];
            default:
                return [
                    'tgl_column' => 'TGL_GZ',
                    'dis_column' => 'DISGZ',
                    'harga_column' => 'HJGZ'
                ];
        }
    }

    private function getPostedColumn($cbg)
    {
        switch ($cbg) {
            case 'TGZ':
                return 'TGZ';
            case 'TMM':
                return 'TMM';
            case 'SOP':
                return 'SOP';
            default:
                return 'TGZ';
        }
    }
}
