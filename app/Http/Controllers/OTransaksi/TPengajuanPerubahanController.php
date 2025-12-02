<?php
namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPJasperXML;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

class TPengajuanPerubahanController extends Controller
{
    /**
     * Halaman Index - List Pengajuan Perubahan
     */
    public function index(Request $request)
    {
        try {
            $judul = 'Pengajuan Perubahan';

            $CBG = Auth::user()->CBG ?? null;
            if (! $CBG) {
                return view("otransaksi_TPengajuanPerubahan.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.',
                ]);
            }

            if (! $request->session()->has('periode')) {
                return view("otransaksi_TPengajuanPerubahan.index")->with([
                    'judul'   => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.',
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TPengajuanPerubahan.index")->with([
                'judul'   => $judul,
                'cbg'     => $CBG,
                'periode' => $periode,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TPengajuanPerubahan index: ' . $e->getMessage());
            return view("otransaksi_TPengajuanPerubahan.index")->with([
                'judul' => 'Pengajuan Perubahan',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Ambil data list pengajuan untuk datatables di index
     */
    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (! $CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            // Query sesuai Delphi: SELECT * FROM HISTO WHERE TYPE='1' and cbg=:cbg order by tgl desc
            $query = "
                SELECT
                    NO_BUKTI,
                    TGL,
                    FLAG,
                    POSTED,
                    USRNM,
                    PER
                FROM histo
                WHERE TYPE = '1'
                AND CBG = ?
                ORDER BY TGL DESC
            ";

            $data = DB::select($query, [$CBG]);

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL));
                })
                ->addColumn('URAIAN', function ($row) {
                    // Get flag description
                    $flag_desc = '';
                    switch ($row->FLAG) {
                        case 'UK':
                            $flag_desc = 'Ubah Kartu';
                            break;
                        case 'UH':
                            $flag_desc = 'Ubah Harga';
                            break;
                        case 'UD':
                            $flag_desc = 'Ubah Data';
                            break;
                        case 'UJ':
                            $flag_desc = 'Ubah Jualan';
                            break;
                        default:
                            $flag_desc = $row->FLAG;
                    }
                    return $flag_desc;
                })
                ->editColumn('POSTED', function ($row) {
                    if ($row->POSTED == 1) {
                        return '<span class="badge badge-success">Posted</span>';
                    } else {
                        return '<span class="badge badge-warning">Open</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $editBtn   = '<button class="btn btn-sm btn-primary btn-edit" data-nobukti="' . $row->NO_BUKTI . '"><i class="fas fa-edit"></i> Edit</button> ';
                    $deleteBtn = '<button class="btn btn-sm btn-danger btn-delete" data-nobukti="' . $row->NO_BUKTI . '" ' . ($row->POSTED == 1 ? 'disabled' : '') . '><i class="fas fa-trash"></i> Hapus</button> ';
                    $detailBtn = '<button class="btn btn-sm btn-info btn-detail" data-nobukti="' . $row->NO_BUKTI . '"><i class="fas fa-eye"></i> Detail</button> ';
                    $printBtn  = '<button class="btn btn-sm btn-info btn-print" data-nobukti="' . $row->NO_BUKTI . '"><i class="fas fa-eye"></i> Print</button>';

                    return $editBtn . $deleteBtn . $detailBtn . $printBtn;
                })
                ->rawColumns(['action', 'POSTED'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Edit/New - Form Entry Pengajuan Perubahan
     */
    public function edit(Request $request, $no_bukti = null)
    {
        try {
            Log::info('TPengajuanPerubahan edit called with no_bukti: ' . $no_bukti);

            $judul = $no_bukti && $no_bukti !== 'new' ? 'Edit Pengajuan Perubahan' : 'Tambah Pengajuan Perubahan';

            $CBG = Auth::user()->CBG ?? null;
            if (! $CBG) {
                Log::error('User tidak memiliki CBG');
                return redirect()->route('pengajuanperubahan')->with('error', 'User tidak memiliki akses cabang');
            }

            $periode = $request->session()->get('periode');
            if (! $periode) {
                Log::error('Periode belum diset');
                return redirect()->route('pengajuanperubahan')->with('warning', 'Periode belum diset');
            }

            // Convert periode to string if it's an array (tahun + bulan)
            if (is_array($periode)) {
                $periode = ($periode['bulan'] ?? '01') . ($periode['tahun'] ?? date('Y'));
            }

            Log::info('Periode: ' . $periode . ', CBG: ' . $CBG);

            // Check closed period
            $queryPeriod = "SELECT POSTED FROM perid WHERE kd_peri = ?";
            $periodCheck = DB::select($queryPeriod, [$periode]);

            $closedPeriod = false;
            if (! empty($periodCheck) && $periodCheck[0]->POSTED == 1) {
                $closedPeriod = true;
            }

            $data             = [];
            $detail           = [];
            $no_bukti_display = '+';
            $tgl              = date('Y-m-d');
            $flag             = '';
            $posted           = 0;

            // Jika edit, ambil data existing
            if ($no_bukti && $no_bukti !== 'new') {
                $query = "
                    SELECT * FROM histo
                    WHERE NO_BUKTI = ? AND CBG = ?
                ";
                $result = DB::select($query, [$no_bukti, $CBG]);

                if (! empty($result)) {
                    $data             = $result[0];
                    $no_bukti_display = $data->NO_BUKTI;
                    $tgl              = date('Y-m-d', strtotime($data->TGL));
                    $flag             = $data->FLAG;
                    $posted           = $data->POSTED;

                    // Get detail
                    $queryDetail = "
                        SELECT * FROM histod
                        WHERE NO_BUKTI = ?
                        ORDER BY REC
                    ";
                    $detail = DB::select($queryDetail, [$no_bukti]);
                } else {
                    return redirect()->route('pengajuanperubahan')->with('error', 'Data tidak ditemukan');
                }
            }

            return view("otransaksi_TPengajuanPerubahan.edit")->with([
                'judul'        => $judul,
                'cbg'          => $CBG,
                'periode'      => $periode,
                'no_bukti'     => $no_bukti_display,
                'data'         => $data,
                'detail'       => $detail,
                'tgl'          => $tgl,
                'flag'         => $flag,
                'posted'       => $posted,
                'closedPeriod' => $closedPeriod,
                'status'       => $no_bukti && $no_bukti !== 'new' ? 'edit' : 'simpan',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in edit: ' . $e->getMessage());
            return redirect()->route('pengajuanperubahan')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get detail items for specific no_bukti (for AJAX)
     */
    public function detail(Request $request, $no_bukti)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (! $CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "
                SELECT
                    NO_ID,
                    REC,
                    KODE as KD_BRG,
                    URAIAN as NA_BRG,
                    HJ2 as HJ_LAMA,
                    HJ,
                    HJBR as HJ_BARU,
                    LPH,
                    LPHBR as LPH_BARU,
                    DTR,
                    DTRBR as DTR_BARU,
                    KK,
                    KKBR as KK_BARU,
                    KET as CATATAN,
                    MOO,
                    CIBING as CABANG,
                    SPLBR as ORDR
                FROM histod
                WHERE NO_BUKTI = ?
                ORDER BY REC
            ";

            $data = DB::select($query, [$no_bukti]);

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('HJ_LAMA', function ($row) {
                    return number_format($row->HJ_LAMA, 2);
                })
                ->editColumn('HJ', function ($row) {
                    return number_format($row->HJ, 2);
                })
                ->editColumn('HJ_BARU', function ($row) {
                    return number_format($row->HJ_BARU, 2);
                })
                ->editColumn('LPH', function ($row) {
                    return number_format($row->LPH, 2);
                })
                ->editColumn('LPH_BARU', function ($row) {
                    return number_format($row->LPH_BARU, 2);
                })
                ->editColumn('DTR', function ($row) {
                    return number_format($row->DTR, 0);
                })
                ->editColumn('DTR_BARU', function ($row) {
                    return number_format($row->DTR_BARU, 0);
                })
                ->editColumn('MOO', function ($row) {
                    return number_format($row->MOO, 2);
                })
                ->editColumn('MOO_BARU', function ($row) {
                    return number_format($row->MOO_BARU, 2);
                })
                ->addColumn('action', function ($row) {
                    $deleteBtn = '<button class="btn btn-xs btn-danger btn-delete-item" data-id="' . $row->NO_ID . '"><i class="fas fa-trash"></i></button>';
                    return $deleteBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Search barang
     */
    public function searchBarang(Request $request)
    {
        try {
            $kd_brg = $request->input('kd_brg');
            $cbg    = Auth::user()->CBG ?? null;

            if (! $cbg) {
                return response()->json(['success' => false, 'message' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "
                SELECT
                    brg.KD_BRG,
                    brg.SUB,
                    brg.KDBAR,
                    brg.SP_L,
                    brg.SP_LF,
                    brg.LPH_TM,
                    brg.LPH_TF,
                    brg.KET_KEM,
                    brg.KET_UK,
                    brg.NA_BRG,
                    brg.KK,
                    brg.TYPE,
                    brg.MARGIN,
                    brgdt.SRMIN,
                    brgdt.SRMAX,
                    brgdt.SMIN,
                    brgdt.SMAX,
                    brgdt.DTR,
                    brgdt.LPH,
                    brgdt.KDLAKU,
                    brgdt.KLK,
                    brgdt.HJ,
                    brgdt.HJ2,
                    brgdt.CAT_OD,
                    IF(brgdt.KLK < 'U', ASCII(brgdt.KLK) - 64, (((ASCII(brgdt.KLK) - 64 - 20) * 5) + 20)) AS AGENG,
                    SUBSTR(TRIM(brg.KET_KEM), ((LOCATE('/', TRIM(brg.KET_KEM)) + 1))) AS KEMASAN
                FROM brg
                INNER JOIN brgdt ON brg.KD_BRG = brgdt.KD_BRG
                WHERE brg.KD_BRG = ?
                AND brgdt.CBG = ?
                AND brgdt.YER = YEAR(NOW())
                ORDER BY brg.KD_BRG
            ";

            $result = DB::select($query, [$kd_brg, $cbg]);

            if (! empty($result)) {
                $barang = $result[0];

                // Check if item in Hari Raya list
                $queryHR           = "SELECT POSTED FROM usul_hraya WHERE KD_BRG = ? AND POSTED = 1";
                $hrCheck           = DB::select($queryHR, [$kd_brg]);
                $barang->LPH_HRAYA = ! empty($hrCheck) ? 1 : 0;

                // Get latest supplier info for UH flag
                $querySupp = "
                    SELECT
                        belid.DISKON1,
                        belid.DISKON2,
                        belid.DISKON3,
                        beli.KODES,
                        beli.NAMAS,
                        beli.GOLONGAN,
                        beli.FLAG as BL_FLAG,
                        belid.NO_BUKTI
                    FROM beli
                    INNER JOIN belid ON beli.NO_BUKTI = belid.NO_BUKTI
                    INNER JOIN (
                        SELECT MAX(NO_ID) AS IDBRG
                        FROM belid
                        WHERE KD_BRG = ?
                    ) AS XX ON belid.NO_ID = XX.IDBRG
                    WHERE beli.CBG = ?
                ";
                $suppInfo = DB::select($querySupp, [$kd_brg, $cbg]);

                if (! empty($suppInfo)) {
                    $barang->DISKON1     = $suppInfo[0]->DISKON1;
                    $barang->DISKON2     = $suppInfo[0]->DISKON2;
                    $barang->DISKON3     = $suppInfo[0]->DISKON3;
                    $barang->KODES       = $suppInfo[0]->KODES;
                    $barang->NAMAS       = $suppInfo[0]->NAMAS;
                    $barang->GOLONGAN    = $suppInfo[0]->GOLONGAN;
                    $barang->BL_FLAG     = $suppInfo[0]->BL_FLAG;
                    $barang->NO_BUKTI_BL = $suppInfo[0]->NO_BUKTI;
                }

                return response()->json([
                    'success' => true,
                    'data'    => $barang,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchBarang: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function tampilBarang(Request $request)
    {
        try {
            $cbg = Auth::user()->CBG ?? null;

            if (! $cbg) {
                return response()->json(['success' => false, 'message' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "
            SELECT
                brg.KD_BRG,
                brg.SUB,
                brg.KDBAR,
                brg.SP_L,
                brg.SP_LF,
                brg.LPH_TM,
                brg.LPH_TF,
                brg.KET_KEM,
                brg.KET_UK,
                brg.NA_BRG,
                brg.KK,
                brg.TYPE,
                brg.MARGIN,
                brgdt.SRMIN,
                brgdt.SRMAX,
                brgdt.SMIN,
                brgdt.SMAX,
                brgdt.DTR,
                brgdt.LPH,
                brgdt.KDLAKU,
                brgdt.KLK,
                brgdt.HJ,
                brgdt.HJ2,
                brgdt.CAT_OD,
                IF(brgdt.KLK < 'U', ASCII(brgdt.KLK) - 64,
                    (((ASCII(brgdt.KLK) - 64 - 20) * 5) + 20)) AS AGENG,
                SUBSTR(TRIM(brg.KET_KEM),
                    (LOCATE('/', TRIM(brg.KET_KEM)) + 1)
                ) AS KEMASAN
            FROM brg
            INNER JOIN brgdt ON brg.KD_BRG = brgdt.KD_BRG
            WHERE brgdt.CBG = ?
            AND brgdt.YER = YEAR(NOW())
            ORDER BY brg.KD_BRG
        ";

            $result = DB::select($query, [$cbg]);

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Error tampilBarang: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proses Save/Update/Delete
     */
    public function proses(Request $request)
    {
        try {
            $CBG      = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $periode  = $request->session()->get('periode');

            // if (! $CBG) {
            //     return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            // }

            $action = $request->input('action', '');
            // dd($CBG, $username, $periode, $action);

            DB::beginTransaction();

            switch ($action) {
                case 'save':
                    return $this->saveData($request, $CBG, $username, $periode);

                case 'delete':
                    return $this->deleteData($request, $CBG);

                case 'delete_item':
                    return $this->deleteItem($request, $CBG);

                case 'add_item':
                    return $this->addItem($request, $CBG, $username, $periode);

                default:
                    DB::rollBack();
                    return response()->json(['error' => 'Action tidak valid'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save/Update Data Header
     */
    // private function saveData($request, $CBG, $username, $periode)
    // {
    //     $status   = $request->input('status', 'simpan');
    //     $no_bukti = $request->input('no_bukti');
    //     $tgl      = $request->input('tgl');
    //     $flag     = $request->input('flag');

    //     // Validasi
    //     if (empty($flag)) {
    //         DB::rollBack();
    //         return response()->json(['error' => 'Jenis pengajuan harus dipilih'], 400);
    //     }

    //     if (empty($tgl)) {
    //         DB::rollBack();
    //         return response()->json(['error' => 'Tanggal harus diisi'], 400);
    //     }

    //     // Check month/year vs periode
    //     $tgl_parts = explode('-', $tgl);
    //     $month     = $tgl_parts[1];
    //     $year      = $tgl_parts[0];

    //     // $periode_month = substr($periode, 0, 2);
    //     // $periode_year  = substr($periode, 2, 4);
    //     $periode_month = $periode['bulan'];
    //     $periode_year  = $periode['tahun'];
    //     $periode2      = $periode_month . '/' . $periode_year;

    //     if ($month != $periode_month || $year != $periode_year) {
    //         DB::rollBack();
    //         return response()->json(['error' => 'Tanggal tidak sesuai dengan periode aktif'], 400);
    //     }

    //     if ($status === 'simpan') {
    //         // Generate NO_BUKTI baru
    //         // Get toko type
    //         $toko = DB::SELECT("SELECT TYPE FROM toko WHERE KODE = '$CBG'");

    //         if (empty($toko)) {
    //             DB::rollBack();
    //             return response()->json(['error' => 'Data toko tidak ditemukan'], 400);
    //         }

    //         $type = $toko[0]->TYPE;

    //         // Format: {FLAG}{YY}{MM}-{NNNN}{TYPE}
    //         $kode = $flag . substr($year, 2, 2) . $month;

    //         // Get nomor urut
    //         $queryNom = "SELECT NOM" . $periode_month . " as NO_BUKTI FROM notrans WHERE TRANS = ? AND PER = ?";
    //         $nom      = DB::select($queryNom, [$flag, $periode_year]);

    //         $nomor = 1;
    //         if (! empty($nom)) {
    //             $nomor = $nom[0]->NO_BUKTI + 1;
    //         }

    //         // Update notrans
    //         $updateNom = "UPDATE notrans SET NOM" . $periode_month . " = ? WHERE TRANS = ? AND PER = ?";
    //         DB::statement($updateNom, [$nomor, $flag, $periode_year]);

    //         $no_bukti = $kode . '-' . str_pad($nomor, 4, '0', STR_PAD_LEFT) . $type;

    //         // Insert header
    //         $insertQuery = "
    //             INSERT INTO histo (NO_BUKTI, TGL, FLAG, CBG, TYPE, USRNM, PER)
    //             VALUES (?, ?, ?, ?, '1', ?, ?)
    //         ";

    //         DB::statement($insertQuery, [
    //             $no_bukti,
    //             $tgl,
    //             $flag,
    //             $CBG,
    //             $username,
    //             $periode2,
    //         ]);
    //     } else {
    //         // Update header
    //         $updateQuery = "
    //             UPDATE histo
    //             SET TGL = ?,
    //                 USRNM = ?
    //             WHERE NO_BUKTI = ?
    //         ";

    //         DB::statement($updateQuery, [
    //             $tgl,
    //             $username,
    //             $no_bukti,
    //         ]);
    //     }

    //     DB::commit();

    //     return response()->json([
    //         'success'  => true,
    //         'message'  => 'Data berhasil disimpan!',
    //         'no_bukti' => $no_bukti,
    //     ]);
    // }
    private function saveData($request, $CBG, $username, $periode)
{
    DB::beginTransaction();

    try {
        $status   = $request->input('status', 'simpan');
        $no_bukti = $request->input('no_bukti');
        $tgl      = $request->input('tgl');
        $flag     = $request->input('flag');

        // Validasi
        if (empty($flag)) {
            return response()->json(['error' => 'Jenis pengajuan harus dipilih'], 400);
        }

        if (empty($tgl)) {
            return response()->json(['error' => 'Tanggal harus diisi'], 400);
        }

        // Validasi tanggal sesuai periode
        $tgl_parts = explode('-', $tgl);
        $month     = $tgl_parts[1];
        $year      = $tgl_parts[0];

        $periode_month = $periode['bulan'];
        $periode_year  = $periode['tahun'];
        $periode2      = $periode_month . '/' . $periode_year;

        if ($month != $periode_month || $year != $periode_year) {
            return response()->json(['error' => 'Tanggal tidak sesuai dengan periode aktif'], 400);
        }

        // Cek apakah no_bukti sudah ada
        $histo = DB::table('histo')->where('NO_BUKTI', $no_bukti)->first();
        // dd($histo);

        if (!$histo) {
            // Kalau belum ada, insert header baru
            if ($status === 'simpan') {
                // Ambil type toko
                $toko = DB::table('toko')->where('KODE', $CBG)->first();
                if (!$toko) {
                    return response()->json(['error' => 'Data toko tidak ditemukan'], 400);
                }

                $type = $toko->TYPE;

                // Generate NO_BUKTI: {FLAG}{YY}{MM}-{NNNN}{TYPE}
                $kode = $flag . substr($year, 2, 2) . $month;

                // Ambil nomor urut dari notrans
                $nom = DB::select("SELECT NOM{$periode_month} as NO_BUKTI FROM notrans WHERE TRANS = ? AND PER = ?", [$flag, $periode_year]);
                $nomor = !empty($nom) ? ($nom[0]->NO_BUKTI + 1) : 1;

                // Update notrans
                DB::statement("UPDATE notrans SET NOM{$periode_month} = ? WHERE TRANS = ? AND PER = ?", [$nomor, $flag, $periode_year]);

                $no_bukti = $kode . '-' . str_pad($nomor, 4, '0', STR_PAD_LEFT) . $type;

                // Insert header
                DB::table('histo')->insert([
                    'NO_BUKTI' => $no_bukti,
                    'TGL'      => $tgl,
                    'FLAG'     => $flag,
                    'CBG'      => $CBG,
                    'TYPE'     => '1',
                    'USRNM'    => $username,
                    'PER'      => $periode2
                ]);
            }
        } else {
            // Kalau sudah ada, update header
            DB::table('histo')->where('NO_BUKTI', $no_bukti)->update([
                'TGL'   => $tgl,
                'USRNM' => $username
            ]);
        }

        DB::commit();

        return response()->json([
            'success'  => true,
            'message'  => 'Data berhasil disimpan!',
            'no_bukti' => $no_bukti,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Proses gagal: ' . $e->getMessage()], 500);
    }
}


    /**
     * Add Item to Pengajuan
     */

// private function addItem($request, $CBG, $username)
// {
//     DB::beginTransaction();

//     try {
//         $no_bukti = $request->input('no_bukti');
//         $kd_brg   = $request->input('kd_brg');
//         $CBG = Auth::user()->CBG;

//         // Format tanggal aman
//         $tgl_input = $request->input('tgl');
//         $tgl = \Carbon\Carbon::parse($tgl_input)->format('Y-m-d');

//         // Data barang pakai query SQL mentah
//         $barang = DB::selectOne("
//             SELECT brg.KD_BRG, brg.NA_BRG, brg.KET_UK, brg.KET_KEM, brg.KK,
//                    brgdt.HJ, brgdt.HJ2, brgdt.LPH, brgdt.DTR, brgdt.KDLAKU,
//                    brgdt.SRMIN, brgdt.SRMAX, brgdt.SMIN, brgdt.SMAX,
//                    brgdt.KLK, brgdt.CAT_OD
//             FROM brg
//             INNER JOIN brgdt ON brg.KD_BRG = brgdt.KD_BRG
//             WHERE brg.KD_BRG = ?
//               AND brgdt.CBG = ?
//               AND brgdt.YER = ?
//         ", [$kd_brg, $CBG, date('Y')]);

//         if (!$barang) {
//             DB::rollBack();
//             return response()->json(['error' => 'Barang tidak ditemukan'], 400);
//         }

//         // Header histo
//         $histo = DB::table('histo')->where('NO_BUKTI', $no_bukti)->first();
//         if (!$histo) {
//             DB::rollBack();
//             return response()->json(['error' => 'Header tidak ditemukan'], 400);
//         }

//         $rec = DB::table('histod')->where('NO_BUKTI', $no_bukti)->max('REC') + 1;

//         // Siapkan data insert, numeric = 0 kalau kosong
//         $data = [
//             'NO_BUKTI'   => $no_bukti,
//             'TGL'        => $tgl,
//             'ID'         => $histo->NO_ID,
//             'REC'        => $rec,
//             'KODE'       => $barang->KD_BRG,
//             'URAIAN'     => $barang->NA_BRG,
//             'HJ2'        => $barang->HJ2 ?? 0,
//             'HJ'         => $barang->HJ ?? 0,
//             'HJBR'       => $request->input('hjbr', $barang->HJ ?? 0),
//             'LPH'        => $barang->LPH ?? 0,
//             'LPHBR'      => $request->input('lphbr', $barang->LPH ?? 0),
//             'DTR'        => $barang->DTR ?? 0,
//             'DTRBR'      => $request->input('dtrbr', $barang->DTR ?? 0),
//             'KK'         => $barang->KK ?? '0',
//             'KKBR'       => '!',
//             'KDLAKU'     => $barang->KDLAKU ?? 0,
//             'KDLAKUBR'   => $request->input('kdlakubr', $barang->KDLAKU ?? 0),
//             'SR_MIN'     => $barang->SRMIN ?? 0,
//             'SR_MINBR'   => $request->input('sr_minbr', $barang->SRMIN ?? 0),
//             'SMAX_TK'    => $barang->SRMAX ?? 0,
//             'SMAX_TKBR'  => $request->input('smax_tkbr', $barang->SRMAX ?? 0),
//             'SMIN'       => $barang->SMIN ?? 0,
//             'SMINBR'     => $request->input('sminbr', $barang->SMIN ?? 0),
//             'SMAX'       => $barang->SMAX ?? 0,
//             'SMAXBR'     => $request->input('smaxbr', $barang->SMAX ?? 0),
//             'SP_L'       => $request->input('sp_l', 0),
//             'LPH_TM'     => $request->input('lph_tm', 0),
//             'SP_LF'      => $request->input('sp_lf', 0),
//             'LPH_TF'     => $request->input('lph_tf', 0),
//             'KLK'        => $barang->KLK ?? 0,
//             'KET'        => $request->input('ket', $barang->CAT_OD ?? ''),
//             'MOO'        => $request->input('moo', 0),
//             'MOOLM'      => $request->input('moobr', 0),
//             'CIBING'     => $request->input('cibing', 0),
//             'SPLBR'      => $request->input('splbr', 0)
//         ];

//         // Insert
//         DB::table('histod')->insert($data);

//         DB::commit();

//        return response()->json([
//     'success' => true,
//     'message' => 'Item berhasil ditambahkan!',
//     'item' => [
//         'rec'       => $rec,
//         'kd_brg'    => $data['KODE'],
//         'na_brg'    => $data['URAIAN'],
//         'hj'        => $data['HJ'],
//         'hjbr'      => $data['HJBR'],
//         'lph'       => $data['LPH'],
//         'lphbr'     => $data['LPHBR'],
//         'dtr'       => $data['DTR'],
//         'dtrbr'     => $data['DTRBR'],
//         'kk'        => $data['KK'],
//         'kkbr'      => $data['KKBR'],
//         'kdlaku'    => $data['KDLAKU'],
//         'kdlakubr'  => $data['KDLAKUBR'],
//         'sr_min'    => $data['SR_MIN'],
//         'sr_minbr'  => $data['SR_MINBR'],
//         'smax_tk'   => $data['SMAX_TK'],
//         'smax_tkbr' => $data['SMAX_TKBR'],
//         'smin'      => $data['SMIN'],
//         'sminbr'    => $data['SMINBR'],
//         'smax'      => $data['SMAX'],
//         'smaxbr'    => $data['SMAXBR'],
//         'sp_l'      => $data['SP_L'],
//         'lph_tm'    => $data['LPH_TM'],
//         'sp_lf'     => $data['SP_LF'],
//         'lph_tf'    => $data['LPH_TF'],
//         'klk'       => $data['KLK'],
//         'ket'       => $data['KET'],
//         'moo'       => $data['MOO'],
//         'moolm'     => $data['MOOLM'],
//         'cibing'    => $data['CIBING'],
//         'splbr'     => $data['SPLBR']
//     ]
// ]);
private function addItem($request, $CBG, $username, $periode)
{
    DB::beginTransaction();

    try {
        $kd_brg   = $request->input('kd_brg');
        $flag     = $request->input('flag');
        $tgl_input = $request->input('tgl');
        $tgl = Carbon::parse($tgl_input)->format('Y-m-d');

        if (!$kd_brg) {
            return response()->json(['error' => 'Kode barang harus diisi'], 400);
        }

        if (!$flag) {
            return response()->json(['error' => 'Flag harus diisi'], 400);
        }

        // --- 1. Header ---
        $no_bukti = $request->input('no_bukti');
        $no_bukti2 = $no_bukti;

        if (!$no_bukti || $no_bukti === '+') {
            $toko = DB::table('toko')->where('KODE', $CBG)->first();
            if (!$toko) {
                return response()->json(['error' => 'Data toko tidak ditemukan'], 400);
            }

            $type = $toko->TYPE;
            $month = $periode['bulan'];
            $year  = $periode['tahun'];
            $periode2 = $month.'/'.$year;

            $kode = $flag . substr($year, 2, 2) . $month;
            $nom = DB::select("SELECT NOM{$month} as NO_BUKTI FROM notrans WHERE TRANS = ? AND PER = ?", [$flag, $year]);
            $nomor = !empty($nom) ? ($nom[0]->NO_BUKTI + 1) : 1;
            DB::statement("UPDATE notrans SET NOM{$month} = ? WHERE TRANS = ? AND PER = ?", [$nomor, $flag, $year]);

            $no_bukti2 = $kode . '-' . str_pad($nomor, 4, '0', STR_PAD_LEFT) . $type;

            $headerData = [
                'NO_BUKTI'   => $no_bukti2,
                'TGL'        => $tgl,
                'FLAG'       => $flag,
                'CBG'        => $CBG,
                'TYPE'       => 1,
                'USRNM'      => $username,
                'PER'        => $periode2,
            ];

            try {
                DB::table('histo')->insert($headerData);
                
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Gagal insert header: ' . $e->getMessage(),
                    'debug' => $headerData
                ], 500);
            }
        }

        // --- 2. Ambil header ---
        $histo = DB::table('histo')->where('NO_BUKTI', $no_bukti2)->first();
        if (!$histo) {
            DB::rollBack();
            return response()->json([
                'error' => 'Header tidak ditemukan setelah insert',
                'debug' => ['no_bukti2' => $no_bukti2]
            ], 500);
        }

        // --- 3. Ambil data barang ---
        $barang = DB::selectOne("
            SELECT brg.KD_BRG, brg.NA_BRG, brg.KK, brgdt.HJ, brgdt.HJ2, brgdt.LPH, brgdt.DTR, brgdt.KDLAKU,
                   brgdt.SRMIN, brgdt.SRMAX, brgdt.SMIN, brgdt.SMAX, brgdt.KLK, brgdt.CAT_OD
            FROM brg
            INNER JOIN brgdt ON brg.KD_BRG = brgdt.KD_BRG
            WHERE brg.KD_BRG = ? AND brgdt.CBG = ? AND brgdt.YER = ?
        ", [$kd_brg, $CBG, date('Y')]);

        if (!$barang) {
            DB::rollBack();
            return response()->json(['error' => 'Data barang tidak ditemukan'], 400);
        }

        // --- 4. Insert detail ---
        $rec = DB::table('histod')->where('NO_BUKTI', $no_bukti2)->max('REC') + 1;

        $detailData = [
            'NO_BUKTI'   => $no_bukti2,
            'TGL'        => $tgl,
            'ID'         => $histo->NO_ID,
            'REC'        => $rec,
            'KODE'       => $barang->KD_BRG,
            'URAIAN'     => $barang->NA_BRG,
            'HJ2'        => $barang->HJ2 ?? 0,
            'HJ'         => $barang->HJ ?? 0,
            'HJBR'       => $request->input('hjbr', $barang->HJ ?? 0),
            'LPH'        => $barang->LPH ?? 0,
            'LPHBR'      => $request->input('lphbr', $barang->LPH ?? 0),
            'DTR'        => $barang->DTR ?? 0,
            'DTRBR'      => $request->input('dtrbr', $barang->DTR ?? 0),
            'KK'         => $barang->KK ?? '0',
            'KKBR'       => $request->input('kkbr', '!'),
            'KDLAKU'     => $barang->KDLAKU ?? 0,
            'KDLAKUBR'   => $request->input('kdlakubr', $barang->KDLAKU ?? 0),
            'SR_MIN'     => $barang->SRMIN ?? 0,
            'SR_MINBR'   => $request->input('sr_minbr', $barang->SRMIN ?? 0),
            'SMAX_TK'    => $barang->SRMAX ?? 0,
            'SMAX_TKBR'  => $request->input('smax_tkbr', $barang->SRMAX ?? 0),
            'SMIN'       => $barang->SMIN ?? 0,
            'SMINBR'     => $request->input('sminbr', $barang->SMIN ?? 0),
            'SMAX'       => $barang->SMAX ?? 0,
            'SMAXBR'     => $request->input('smaxbr', $barang->SMAX ?? 0),
            'KET'        => $request->input('ket', $barang->CAT_OD ?? ''),
               'LPH_TM'     => $request->input('lph_tm', 0),
            'SP_LF'      => $request->input('sp_lf', 0),
            'LPH_TF'     => $request->input('lph_tf', 0),
            'KLK'        => $barang->KLK ?? 0,
            'KET'        => $request->input('ket', $barang->CAT_OD ?? ''),
            'MOO'        => $request->input('moo', 0),
            'MOOLM'      => $request->input('moobr', 0),
            'CIBING'     => $request->input('cibing', 0),
            'SPLBR'      => $request->input('splbr', 0)

        ];

        DB::table('histod')->insert($detailData);

        DB::commit();

        return response()->json([
            'success'  => true,
            'message'  => 'Item berhasil ditambahkan!',
            'no_bukti' => $no_bukti2,
             'item' => [
        'rec'       => $rec,
        'kd_brg'    => $detailData['KODE'],
        'na_brg'    => $detailData['URAIAN'],
        'hj'        => $detailData['HJ'],
        'hjbr'      => $detailData['HJBR'],
        'lph'       => $detailData['LPH'],
        'lphbr'     => $detailData['LPHBR'],
        'dtr'       => $detailData['DTR'],
        'dtrbr'     => $detailData['DTRBR'],
        'kk'        => $detailData['KK'],
        'kkbr'      => $detailData['KKBR'],
        'kdlaku'    => $detailData['KDLAKU'],
        'kdlakubr'  => $detailData['KDLAKUBR'],
        'sr_min'    => $detailData['SR_MIN'],
        'sr_minbr'  => $detailData['SR_MINBR'],
        'smax_tk'   => $detailData['SMAX_TK'],
        'smax_tkbr' => $detailData['SMAX_TKBR'],
        'smin'      => $detailData['SMIN'],
        'sminbr'    => $detailData['SMINBR'],
        'smax'      => $detailData['SMAX'],
        'smaxbr'    => $detailData['SMAXBR'],
        'lph_tm'    => $detailData['LPH_TM'],
        'sp_lf'     => $detailData['SP_LF'],
        'klk'       => $detailData['KLK'],
        'ket'       => $detailData['KET'],
        'moo'       => $detailData['MOO'],
        'moolm'     => $detailData['MOOLM'],
        'cibing'    => $detailData['CIBING'],
        'splbr'     => $detailData['SPLBR']
    ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Proses gagal: ' . $e->getMessage()
        ], 500);
    }
}


    /**
     * Delete Data (Header and all items)
     */
    private function deleteData($request, $CBG)
    {
        $no_bukti = $request->input('no_bukti');

        if (empty($no_bukti)) {
            DB::rollBack();
            return response()->json(['error' => 'No Bukti tidak valid'], 400);
        }

        // Check if posted
        $queryCheck = "SELECT POSTED FROM histo WHERE NO_BUKTI = ? AND CBG = ?";
        $check      = DB::select($queryCheck, [$no_bukti, $CBG]);

        if (! empty($check) && $check[0]->POSTED == 1) {
            DB::rollBack();
            return response()->json(['error' => 'Data sudah diposting, tidak dapat dihapus'], 400);
        }

        // Delete detail first
        $deleteDetail = "DELETE FROM histod WHERE NO_BUKTI = ?";
        DB::statement($deleteDetail, [$no_bukti]);

        // Delete header
        $deleteHeader = "DELETE FROM histo WHERE NO_BUKTI = ? AND CBG = ?";
        DB::statement($deleteHeader, [$no_bukti, $CBG]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!',
        ]);
    }

    public function print(Request $request)
    {
        $noBukti = $request->input('no_bukti');
        $cbg     = Auth::user()->CBG;

        $toko = DB::table('toko')
            ->where('KODE', $cbg)
            ->value('NA_TOKO');

        $kode = substr($noBukti, 0, 2);
        // dd($noBukti, $kode);

        if ($kode === 'UH') {

            $data = DB::table('histod')
                ->selectRaw("
                '$toko' as nmtoko,
                histod.NO_BUKTI,
                histod.KODE as KD_BRG,
                CONCAT(histod.KDLAKU, histod.KLK) as KD,
                histod.URAIAN as NA_BRG,
                brg.KET_UK,
                brg.KET_KEM,
                histod.HJ2,
                histod.HJ,
                histod.HJBR,
                histod.TGL,
                histod.ket")
                ->join('brg', 'histod.KODE', '=', 'brg.KD_BRG')
                ->join('histo', 'histod.NO_BUKTI', '=', 'histo.NO_BUKTI')
                ->where('histo.CBG', $cbg)
                ->where('histo.NO_BUKTI', $noBukti)
                ->get();
        }

        // =============== CASE UK =============== //
        if ($kode === 'UK') {

            $data = DB::table('histod as b')
                ->selectRaw("
                '$toko' as nmtoko,
                b.NO_BUKTI, b.KODE, b.URAIAN,
                c.KET_UK, c.KET_KEM,
                d.HJ, b.KDLAKU, b.KDLAKUbr,
                CONCAT(b.KDLAKU, b.KLK) AS KD,
                b.lph, b.lphbr, b.DTR, b.DTRBR,
                b.SMIN, b.SMINbr, b.SMAX, b.SMAXbr,
                b.SR_MIN, b.SR_MINbr, b.SMAX_tk, b.SMAX_tkbr,
                b.kkbr, b.kk, c.supp as sup_1, c.mo")
                ->join('brg as c', 'b.KODE', '=', 'c.KD_BRG')
                ->join('histo as a', 'b.NO_BUKTI', '=', 'a.NO_BUKTI')
                ->join('brgdt as d', 'b.KODE', '=', 'd.kd_brg')
                ->where('a.CBG', $cbg)
                ->where('a.NO_BUKTI', $noBukti)
                ->get();
        }

        // =============== CASE UD =============== //
        if ($kode === 'UD') {

            $data = DB::table('histod')
                ->selectRaw("
                '$toko' as nmtoko,
                histod.NO_BUKTI,
                histod.KODE,
                histod.URAIAN,
                brg.KET_UK,
                brg.KET_KEM,
                CONCAT(histod.KDLAKU, histod.KLK) as KD,
                IF(histod.ket='', 'JUAL KEMBALI', histod.ket) as ket")
                ->join('brg', 'histod.KODE', '=', 'brg.KD_BRG')
                ->join('histo', 'histod.NO_BUKTI', '=', 'histo.NO_BUKTI')
                ->where('histo.CBG', $cbg)
                ->where('histo.NO_BUKTI', $noBukti)
                ->get();
        }

        // =============== CASE UJ =============== //
        if ($kode === 'UJ') {

            $data = DB::table('histod')
                ->selectRaw("
                '$toko' as nmtoko,
                histod.NO_BUKTI,
                histod.KODE,
                histod.URAIAN,
                brg.KET_UK,
                brg.KET_KEM,
                CONCAT(histod.KDLAKUBR, histod.KLK) as KD,
                cibing,
                IF(cibing='TMM', brg.SP_L, brg.SP_LF) as SPLLM,
                SPLBR,
                histod.MOO,
                MOOLM,
                lphbr")
                ->join('brg', 'histod.KODE', '=', 'brg.KD_BRG')
                ->join('histo', 'histod.NO_BUKTI', '=', 'histo.NO_BUKTI')
                ->where('histo.CBG', $cbg)
                ->where('histo.NO_BUKTI', $noBukti)
                ->get();
        }
        // dd($data);

        $file         = 'print_pengajuan_perubahan';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

        // $PHPJasperXML->setData($data);
        $cleanData                    = json_decode(json_encode($data), true);
        $PHPJasperXML->arrayParameter = [
            "na_toko"   => $na_toko,
            "alamatini" => $alamatini,
            "tipe"      => $tipe,
        ];

        $PHPJasperXML->setData($cleanData);

        // dd($cleanData);

        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function usulan(Request $request)
    {
        try {
            $cbg  = Auth::user()->CBG ?? null;
            $user = Auth::user()->username ?? null;
            // dd($cbg, $user);

            $result = DB::select("
            call tgz.pjl_usul_hj_margin(:jns, :cbg, :sub, :kdbar1, :kdbar2, :user)
        ", [
                'jns'    => 'PROSES',
                'cbg'    => $cbg,
                'sub'    => $request->SUB,
                'kdbar1' => $request->KDBAR1,
                'kdbar2' => $request->KDBAR2,
                'user'   => $user,
            ]);

            $bukti = $result[0]->BUKTI ?? '';

            if (! empty($bukti)) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil Proses. No. Bukti: {$bukti}",
                    'bukti' => $bukti,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang diproses',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

}