<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPJasperXML;
use Yajra\DataTables\Facades\DataTables;

class TPengajuanPerubahanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Pengajuan Perubahan';
            $CBG = Auth::user()->CBG ?? null;

            if (!$CBG) {
                return view("otransaksi_TPengajuanPerubahan.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.',
                ]);
            }

            if (!$request->session()->has('periode')) {
                return view("otransaksi_TPengajuanPerubahan.index")->with([
                    'judul' => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.',
                ]);
            }

            $periode = $request->session()->get('periode');

            return view("otransaksi_TPengajuanPerubahan.index")->with([
                'judul' => $judul,
                'cbg' => $CBG,
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

    public function cari_data(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "
                SELECT NO_BUKTI, TGL, FLAG, POSTED, USRNM, PER
                FROM histo
                WHERE TYPE = '1' AND CBG = ?
                ORDER BY TGL DESC
            ";

            $data = DB::select($query, [$CBG]);

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('TGL', function ($row) {
                    return date('d-m-Y', strtotime($row->TGL));
                })
                ->addColumn('URAIAN', function ($row) {
                    $flag_desc = [
                        'UK' => 'Ubah Kartu',
                        'UH' => 'Ubah Harga',
                        'UD' => 'Ubah Data',
                        'UJ' => 'Ubah Jualan',
                    ];
                    return $flag_desc[$row->FLAG] ?? $row->FLAG;
                })
                ->editColumn('POSTED', function ($row) {
                    return $row->POSTED == 1
                        ? '<span class="badge badge-success">Posted</span>'
                        : '<span class="badge badge-warning">Open</span>';
                })
                ->addColumn('action', function ($row) {
                    $disabled = $row->POSTED == 1 ? 'disabled' : '';
                    return '
                        <button class="btn btn-sm btn-primary btn-edit" data-nobukti="' . $row->NO_BUKTI . '">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete" data-nobukti="' . $row->NO_BUKTI . '" ' . $disabled . '>
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                        <button class="btn btn-sm btn-info btn-detail" data-nobukti="' . $row->NO_BUKTI . '">
                            <i class="fas fa-eye"></i> Detail
                        </button>
                        <button class="btn btn-sm btn-secondary btn-print" data-nobukti="' . $row->NO_BUKTI . '">
                            <i class="fas fa-print"></i> Print
                        </button>
                    ';
                })
                ->rawColumns(['action', 'POSTED'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in cari_data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit(Request $request, $no_bukti = null)
    {
        try {
            $judul = $no_bukti && $no_bukti !== 'new' ? 'Edit Pengajuan Perubahan' : 'Tambah Pengajuan Perubahan';
            $CBG = Auth::user()->CBG ?? null;

            if (!$CBG) {
                return redirect()->route('pengajuanperubahan')->with('error', 'User tidak memiliki akses cabang');
            }

            $periode = $request->session()->get('periode');
            if (!$periode) {
                return redirect()->route('pengajuanperubahan')->with('warning', 'Periode belum diset');
            }

            // Convert periode format
            if (is_array($periode)) {
                $periode_str = str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT) . $periode['tahun'];
            } else {
                $periode_str = $periode;
            }

            // Check closed period
            $queryPeriod = "SELECT POSTED FROM perid WHERE kd_peri = ?";
            $periodCheck = DB::select($queryPeriod, [$periode_str]);
            $closedPeriod = (!empty($periodCheck) && $periodCheck[0]->POSTED == 1);

            $data = [];
            $detail = [];
            $no_bukti_display = '+';
            $tgl = date('Y-m-d');
            $flag = '';
            $posted = 0;

            // Load data for edit
            if ($no_bukti && $no_bukti !== 'new') {
                $query = "SELECT * FROM histo WHERE NO_BUKTI = ? AND CBG = ?";
                $result = DB::select($query, [$no_bukti, $CBG]);

                if (!empty($result)) {
                    $data = $result[0];
                    $no_bukti_display = $data->NO_BUKTI;
                    $tgl = date('Y-m-d', strtotime($data->TGL));
                    $flag = $data->FLAG;
                    $posted = $data->POSTED;

                    // Get detail items
                    $queryDetail = "SELECT * FROM histod WHERE NO_BUKTI = ? ORDER BY REC";
                    $detail = DB::select($queryDetail, [$no_bukti]);
                } else {
                    return redirect()->route('pengajuanperubahan')->with('error', 'Data tidak ditemukan');
                }
            }

            return view("otransaksi_TPengajuanPerubahan.edit")->with([
                'judul' => $judul,
                'cbg' => $CBG,
                'periode' => $periode,
                'no_bukti' => $no_bukti_display,
                'data' => $data,
                'detail' => $detail,
                'tgl' => $tgl,
                'flag' => $flag,
                'posted' => $posted,
                'closedPeriod' => $closedPeriod,
                'status' => $no_bukti && $no_bukti !== 'new' ? 'edit' : 'simpan',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in edit: ' . $e->getMessage());
            return redirect()->route('pengajuanperubahan')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function detail(Request $request, $no_bukti)
    {
        try {
            $query = "
                SELECT NO_ID, REC, KODE as KD_BRG, URAIAN as NA_BRG,
                       HJ2 as HJ_LAMA, HJ, HJBR as HJ_BARU,
                       LPH, LPHBR as LPH_BARU, DTR, DTRBR as DTR_BARU,
                       KK, KKBR as KK_BARU, KET as CATATAN,
                       MOOLM as MOO, MOO as MOO_BARU,
                       CIBING as CABANG, SPLBR as ORDR
                FROM histod
                WHERE NO_BUKTI = ?
                ORDER BY REC
            ";

            $data = DB::select($query, [$no_bukti]);

            return Datatables::of(collect($data))
                ->addIndexColumn()
                ->editColumn('HJ_LAMA', fn($row) => number_format($row->HJ_LAMA, 2))
                ->editColumn('HJ', fn($row) => number_format($row->HJ, 2))
                ->editColumn('HJ_BARU', fn($row) => number_format($row->HJ_BARU, 2))
                ->editColumn('LPH', fn($row) => number_format($row->LPH, 2))
                ->editColumn('LPH_BARU', fn($row) => number_format($row->LPH_BARU, 2))
                ->editColumn('DTR', fn($row) => number_format($row->DTR, 0))
                ->editColumn('DTR_BARU', fn($row) => number_format($row->DTR_BARU, 0))
                ->editColumn('MOO', fn($row) => number_format($row->MOO, 2))
                ->editColumn('MOO_BARU', fn($row) => number_format($row->MOO_BARU, 2))
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in detail: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }

    public function searchBarang(Request $request)
    {
        try {
            $kd_brg = $request->input('kd_brg');
            $cbg = Auth::user()->CBG ?? null;

            if (!$cbg) {
                return response()->json(['success' => false, 'message' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "
                SELECT brg.KD_BRG, brg.SUB, brg.KDBAR, brg.SP_L, brg.SP_LF,
                       brg.LPH_TM, brg.LPH_TF, brg.KET_KEM, brg.KET_UK,
                       brg.NA_BRG, brg.KK, brg.TYPE, brg.MARGIN,
                       brgdt.SRMIN, brgdt.SRMAX, brgdt.SMIN, brgdt.SMAX,
                       brgdt.DTR, brgdt.LPH, brgdt.KDLAKU, brgdt.KLK,
                       brgdt.HJ, brgdt.HJ2, brgdt.CAT_OD,
                       IF(brgdt.KLK < 'U', ASCII(brgdt.KLK) - 64,
                          (((ASCII(brgdt.KLK) - 64 - 20) * 5) + 20)) AS AGENG,
                       SUBSTR(TRIM(brg.KET_KEM), ((LOCATE('/', TRIM(brg.KET_KEM)) + 1))) AS KEMASAN
                FROM brg
                INNER JOIN brgdt ON brg.KD_BRG = brgdt.KD_BRG
                WHERE brg.KD_BRG = ? AND brgdt.CBG = ? AND brgdt.YER = YEAR(NOW())
            ";

            $result = DB::select($query, [$kd_brg, $cbg]);

            if (!empty($result)) {
                $barang = $result[0];

                // Check LPH H.Raya
                $queryHR = "SELECT POSTED FROM usul_hraya WHERE KD_BRG = ? AND POSTED = 1";
                $hrCheck = DB::select($queryHR, [$kd_brg]);
                $barang->LPH_HRAYA = !empty($hrCheck) ? 1 : 0;

                // Get supplier info for UH
                $querySupp = "
                    SELECT belid.DISKON1, belid.DISKON2, belid.DISKON3, belid.DISKON4,
                           beli.KODES, beli.NAMAS, beli.GOLONGAN, beli.FLAG as BL_FLAG,
                           belid.NO_BUKTI
                    FROM beli
                    INNER JOIN belid ON beli.NO_BUKTI = belid.NO_BUKTI
                    INNER JOIN (SELECT MAX(NO_ID) AS IDBRG FROM belid WHERE KD_BRG = ?) AS XX
                        ON belid.NO_ID = XX.IDBRG
                    WHERE beli.CBG = ?
                ";
                $suppInfo = DB::select($querySupp, [$kd_brg, $cbg]);

                if (!empty($suppInfo)) {
                    foreach (['DISKON1', 'DISKON2', 'DISKON3', 'DISKON4', 'KODES', 'NAMAS', 'GOLONGAN', 'BL_FLAG'] as $field) {
                        $barang->$field = $suppInfo[0]->$field ?? null;
                    }
                    $barang->NO_BUKTI_BL = $suppInfo[0]->NO_BUKTI ?? null;
                }

                return response()->json(['success' => true, 'data' => $barang]);
            }

            return response()->json(['success' => false, 'message' => 'Barang tidak ditemukan']);
        } catch (\Exception $e) {
            Log::error('Error in searchBarang: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
        }
    }

    public function tampilBarang(Request $request)
    {
        try {
            $cbg = Auth::user()->CBG ?? null;
            if (!$cbg) {
                return response()->json(['success' => false, 'message' => 'User tidak memiliki akses cabang'], 400);
            }

            $query = "
                SELECT brg.KD_BRG, brg.SUB, brg.KDBAR, brg.SP_L, brg.SP_LF,
                       brg.LPH_TM, brg.LPH_TF, brg.KET_KEM, brg.KET_UK,
                       brg.NA_BRG, brg.KK, brg.TYPE, brg.MARGIN,
                       brgdt.SRMIN, brgdt.SRMAX, brgdt.SMIN, brgdt.SMAX,
                       brgdt.DTR, brgdt.LPH, brgdt.KDLAKU, brgdt.KLK,
                       brgdt.HJ, brgdt.HJ2, brgdt.CAT_OD,
                       IF(brgdt.KLK < 'U', ASCII(brgdt.KLK) - 64,
                          (((ASCII(brgdt.KLK) - 64 - 20) * 5) + 20)) AS AGENG,
                       SUBSTR(TRIM(brg.KET_KEM), (LOCATE('/', TRIM(brg.KET_KEM)) + 1)) AS KEMASAN
                FROM brg
                INNER JOIN brgdt ON brg.KD_BRG = brgdt.KD_BRG
                WHERE brgdt.CBG = ? AND brgdt.YER = YEAR(NOW())
                ORDER BY brg.KD_BRG
            ";

            $result = DB::select($query, [$cbg]);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error tampilBarang: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
        }
    }

    public function cekSerupa(Request $request)
    {
        $kd_brg = $request->kd_brg;
        $barang = DB::table('brg')->where('KD_BRG', $kd_brg)->first();

        if (!$barang) {
            return response()->json([]);
        }

        $serupa = DB::table('brg')
            ->where('NA_BRG', $barang->NA_BRG)
            ->where('KET_UK', $barang->KET_UK)
            ->where('SUB', substr($kd_brg, 0, 3))
            ->where('KD_BRG', '!=', $kd_brg)
            ->limit(1)
            ->get();

        return response()->json($serupa);
    }

    public function proses(Request $request)
    {
        try {
            $CBG = Auth::user()->CBG ?? null;
            $username = Auth::user()->username ?? 'system';
            $periode = $request->session()->get('periode');

            if (!$CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $action = $request->input('action', '');
            DB::beginTransaction();

            // $result = match ($action) {
            //     'save' => $this->saveData($request, $CBG, $username, $periode),
            //     'delete' => $this->deleteData($request, $CBG),
            //     'delete_item' => $this->deleteItem($request, $CBG),
            //     'add_item' => $this->addItem($request, $CBG, $username),
            //     default => throw new \Exception('Action tidak valid')
            // };

            switch ($action) {
                case 'save':
                    $result = $this->saveData($request, $CBG, $username, $periode);
                    break;

                case 'delete':
                    $result = $this->deleteData($request, $CBG);
                    break;

                case 'delete_item':
                    $result = $this->deleteItem($request, $CBG);
                    break;

                case 'add_item':
                    $result = $this->addItem($request, $CBG, $username);
                    break;

                default:
                    throw new \Exception('Action tidak valid');
            }

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in proses: ' . $e->getMessage());
            return response()->json(['error' => 'Proses gagal: ' . $e->getMessage()], 500);
        }
    }

    private function saveData($request, $CBG, $username, $periode)
    {
        $status = $request->input('status', 'simpan');
        $no_bukti = $request->input('no_bukti');
        $tgl = $request->input('tgl');
        $flag = $request->input('flag');

        if (empty($flag) || empty($tgl)) {
            throw new \Exception('Jenis pengajuan dan tanggal harus diisi');
        }

        // Validate date against periode
        [$year, $month] = explode('-', $tgl);
        $periode_month = is_array($periode) ? $periode['bulan'] : substr($periode, 0, 2);
        $periode_year = is_array($periode) ? $periode['tahun'] : substr($periode, 2, 4);

        if ($month != $periode_month || $year != $periode_year) {
            throw new \Exception('Tanggal tidak sesuai dengan periode aktif');
        }

        $periode_str = $periode_month . '/' . $periode_year;

        if ($status === 'simpan' && $no_bukti === '+') {
            // Generate new NO_BUKTI
            $toko = DB::selectOne("SELECT TYPE FROM toko WHERE KODE = ?", [$CBG]);
            if (!$toko) {
                throw new \Exception('Data toko tidak ditemukan');
            }

            $kode = $flag . substr($year, 2, 2) . $month;

            // Get sequence number
            $nom = DB::selectOne(
                "SELECT NOM{$periode_month} as NO_BUKTI FROM notrans WHERE TRANS = ? AND PER = ?",
                [$flag, $periode_year]
            );
            $nomor = ($nom->NO_BUKTI ?? 0) + 1;

            // Update sequence
            DB::statement(
                "UPDATE notrans SET NOM{$periode_month} = ? WHERE TRANS = ? AND PER = ?",
                [$nomor, $flag, $periode_year]
            );

            $no_bukti = $kode . '-' . str_pad($nomor, 4, '0', STR_PAD_LEFT) . $toko->TYPE;

            // Insert header
            DB::statement("
                INSERT INTO histo (NO_BUKTI, TGL, FLAG, CBG, TYPE, USRNM, PER)
                VALUES (?, ?, ?, ?, '1', ?, ?)
            ", [$no_bukti, $tgl, $flag, $CBG, $username, $periode_str]);
        } else {
            // Update header
            DB::statement(
                "UPDATE histo SET TGL = ?, USRNM = ? WHERE NO_BUKTI = ?",
                [$tgl, $username, $no_bukti]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan!',
            'no_bukti' => $no_bukti
        ]);
    }

    private function addItem($request, $CBG, $username)
    {
        $no_bukti = $request->input('no_bukti');
        $kd_brg = $request->input('kd_brg');
        $flag = $request->input('flag');
        $tgl = $request->input('tgl');

        // Get barang data
        $barang = DB::selectOne("
            SELECT brg.KD_BRG, brg.NA_BRG, brg.KET_UK, brg.KET_KEM, brg.KK,
                   brgdt.HJ, brgdt.HJ2, brgdt.LPH, brgdt.DTR, brgdt.KDLAKU,
                   brgdt.SRMIN, brgdt.SRMAX, brgdt.SMIN, brgdt.SMAX, brgdt.KLK,
                   brgdt.CAT_OD
            FROM brg
            INNER JOIN brgdt ON brg.KD_BRG = brgdt.KD_BRG
            WHERE brg.KD_BRG = ? AND brgdt.CBG = ? AND brgdt.YER = YEAR(NOW())
        ", [$kd_brg, $CBG]);

        if (!$barang) {
            throw new \Exception('Barang tidak ditemukan');
        }

        // Get histo ID
        $histo = DB::selectOne("SELECT NO_ID FROM histo WHERE NO_BUKTI = ?", [$no_bukti]);
        if (!$histo) {
            throw new \Exception('Header tidak ditemukan');
        }

        // Get max REC
        $maxRec = DB::selectOne("SELECT COALESCE(MAX(REC), 0) as MAX_REC FROM histod WHERE NO_BUKTI = ?", [$no_bukti]);
        $rec = ($maxRec->MAX_REC ?? 0) + 1;

        // Prepare data based on flag
        $data = [
            'NO_BUKTI' => $no_bukti,
            'TGL' => $tgl,
            'ID' => $histo->NO_ID,
            'REC' => $rec,
            'KODE' => $barang->KD_BRG,
            'URAIAN' => $barang->NA_BRG,
            'HJ2' => $barang->HJ2,
            'HJ' => $barang->HJ,
            'HJBR' => $request->input('hjbr', $barang->HJ),
            'LPH' => $barang->LPH,
            'LPHBR' => $request->input('lphbr', $barang->LPH),
            'DTR' => $barang->DTR,
            'DTRBR' => $request->input('dtrbr', $barang->DTR),
            'KK' => $barang->KK,
            'KKBR' => $request->input('kkbr', ''),
            'KDLAKU' => $barang->KDLAKU,
            'KDLAKUBR' => $request->input('kdlakubr', $barang->KDLAKU),
            'SR_MIN' => $barang->SRMIN,
            'SR_MINBR' => $request->input('sr_minbr', $barang->SRMIN),
            'SMAX_TK' => $barang->SRMAX,
            'SMAX_TKBR' => $request->input('smax_tkbr', $barang->SRMAX),
            'SMIN' => $barang->SMIN,
            'SMINBR' => $request->input('sminbr', $barang->SMIN),
            'SMAX' => $barang->SMAX,
            'SMAXBR' => $request->input('smaxbr', $barang->SMAX),
            'SP_L' => $request->input('sp_l', ''),
            'LPH_TM' => $request->input('lph_tm', 0),
            'SP_LF' => $request->input('sp_lf', ''),
            'LPH_TF' => $request->input('lph_tf', 0),
            'KLK' => $barang->KLK,
            'KET' => $request->input('ket', $barang->CAT_OD),
            'MOO' => $request->input('moo', 0),
            'MOOLM' => $request->input('moo', 0),
            'CIBING' => $request->input('cibing', ''),
            'SPLBR' => $request->input('splbr', '')
        ];

        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        DB::statement("INSERT INTO histod ({$fields}) VALUES ({$placeholders})", array_values($data));

        // Get inserted NO_ID
        $inserted = DB::selectOne("SELECT NO_ID FROM histod WHERE NO_BUKTI = ? AND REC = ?", [$no_bukti, $rec]);

        $data['no_id'] = $inserted->NO_ID ?? null;
        $data['kode'] = $data['KODE'];
        $data['uraian'] = $data['URAIAN'];
        $data['hj'] = $data['HJ'];
        $data['hjbr'] = $data['HJBR'];
        $data['hj2'] = $data['HJ2'];
        $data['lph'] = $data['LPH'];
        $data['lphbr'] = $data['LPHBR'];
        $data['dtr'] = $data['DTR'];
        $data['dtrbr'] = $data['DTRBR'];
        $data['kk'] = $data['KK'];
        $data['kkbr'] = $data['KKBR'];
        $data['ket'] = $data['KET'];
        $data['cibing'] = $data['CIBING'];
        $data['splbr'] = $data['SPLBR'];
        $data['moobr'] = $data['MOO'];

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil ditambahkan!',
            'no_bukti' => $no_bukti,
            'item' => $data
        ]);
    }

    private function deleteItem($request, $CBG)
    {
        $no_id = $request->input('no_id');
        if (empty($no_id)) {
            throw new \Exception('ID tidak valid');
        }

        DB::statement("DELETE FROM histod WHERE NO_ID = ?", [$no_id]);
        return response()->json(['success' => true, 'message' => 'Item berhasil dihapus!']);
    }

    private function deleteData($request, $CBG)
    {
        $no_bukti = $request->input('no_bukti');
        if (empty($no_bukti)) {
            throw new \Exception('No Bukti tidak valid');
        }

        // Check if posted
        $check = DB::selectOne("SELECT POSTED FROM histo WHERE NO_BUKTI = ? AND CBG = ?", [$no_bukti, $CBG]);
        if ($check && $check->POSTED == 1) {
            throw new \Exception('Data sudah diposting, tidak dapat dihapus');
        }

        DB::statement("DELETE FROM histod WHERE NO_BUKTI = ?", [$no_bukti]);
        DB::statement("DELETE FROM histo WHERE NO_BUKTI = ? AND CBG = ?", [$no_bukti, $CBG]);

        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus!']);
    }

    public function print(Request $request)
    {
        $noBukti = $request->input('no_bukti');
        $cbg = Auth::user()->CBG;
        $TGL = Carbon::now()->format('d/m/Y');
        $JAM = Carbon::now()->addHour()->toTimeString();

        $toko = DB::table('toko')->where('KODE', $cbg)->value('NA_TOKO');
        $kode = substr($noBukti, 0, 2);

        $files = [
            'UH' => 'print_pengajuan_perubahan',
            'UK' => 'print_pengajuan_perubahan_UK',
            'UD' => 'print_pengajuan_perubahan_UD',
            'UJ' => 'print_pengajuan_perubahan_UJ'
        ];

        $file = $files[$kode] ?? null;
        if (!$file) {
            return response()->json(['error' => 'Jenis pengajuan tidak valid'], 400);
        }

        $queries = [
            'UH' => "
                SELECT '{$toko}' as nmtoko, histod.NO_BUKTI, histod.KODE as KD_BRG,
                       CONCAT(histod.KDLAKU, histod.KLK) as KD, histod.URAIAN as NA_BRG,
                       brg.KET_UK, brg.KET_KEM, histod.HJ2, histod.HJ, histod.HJBR,
histod.TGL, histod.ket
FROM histod
JOIN brg ON histod.KODE = brg.KD_BRG
JOIN histo ON histod.NO_BUKTI = histo.NO_BUKTI
WHERE histo.CBG = ? AND histo.NO_BUKTI = ?
",
            'UK' => "
SELECT '{$toko}' as nmtoko, b.NO_BUKTI, b.KODE, b.URAIAN,
c.KET_UK, c.KET_KEM, d.HJ, b.KDLAKU, b.KDLAKUbr,
CONCAT(b.KDLAKU, b.KLK) AS KD,
b.lph, b.lphbr, b.DTR, b.DTRBR,
b.SMIN, b.SMINbr, b.SMAX, b.SMAXbr,
b.SR_MIN, b.SR_MINbr, b.SMAX_tk, b.SMAX_tkbr,
b.kkbr, b.kk, c.supp as sup_1, c.mo
FROM histod b
JOIN brg c ON b.KODE = c.KD_BRG
JOIN histo a ON b.NO_BUKTI = a.NO_BUKTI
JOIN brgdt d ON b.KODE = d.kd_brg
WHERE a.CBG = ? AND a.NO_BUKTI = ?
",
            'UD' => "
SELECT '{$toko}' as nmtoko, histod.NO_BUKTI, histod.KODE, histod.URAIAN,
brg.KET_UK, brg.KET_KEM, CONCAT(histod.KDLAKU, histod.KLK) as KD,
IF(histod.ket='', 'JUAL KEMBALI', histod.ket) as ket
FROM histod
JOIN brg ON histod.KODE = brg.KD_BRG
JOIN histo ON histod.NO_BUKTI = histo.NO_BUKTI
WHERE histo.CBG = ? AND histo.NO_BUKTI = ?
",
            'UJ' => "
SELECT '{$toko}' as nmtoko, histod.NO_BUKTI, histod.KODE, histod.URAIAN,
brg.KET_UK, brg.KET_KEM, CONCAT(histod.KDLAKUBR, histod.KLK) as KD,
cibing, IF(cibing='TMM', brg.SP_L, brg.SP_LF) as SPLLM,
SPLBR, histod.MOO, MOOLM, lphbr
FROM histod
JOIN brg ON histod.KODE = brg.KD_BRG
JOIN histo ON histod.NO_BUKTI = histo.NO_BUKTI
WHERE histo.CBG = ? AND histo.NO_BUKTI = ?
"
        ];
        $data = DB::select($queries[$kode], [$cbg, $noBukti]);
        $cleanData = json_decode(json_encode($data), true);

        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));
        $PHPJasperXML->arrayParameter = [
            "na_toko" => $toko,
            "TGL_1" => $TGL,
            "JAM_1" => $JAM,
        ];
        $PHPJasperXML->setData($cleanData);

        ob_end_clean();
        $PHPJasperXML->outpage("I");
    }

    public function usulan(Request $request)
    {
        try {
            $cbg = Auth::user()->CBG ?? null;
            $user = Auth::user()->username ?? null;

            $result = DB::select("CALL tgz.pjl_usul_hj_margin(:jns, :cbg, :sub, :kdbar1, :kdbar2, :user)", [
                'jns' => 'PROSES',
                'cbg' => $cbg,
                'sub' => $request->SUB,
                'kdbar1' => $request->KDBAR1,
                'kdbar2' => $request->KDBAR2,
                'user' => $user,
            ]);

            $bukti = $result[0]->BUKTI ?? '';

            return $bukti
                ? response()->json(['success' => true, 'message' => "Berhasil Proses. No. Bukti: {$bukti}", 'bukti' => $bukti])
                : response()->json(['success' => false, 'message' => 'Tidak ada data yang diproses']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
