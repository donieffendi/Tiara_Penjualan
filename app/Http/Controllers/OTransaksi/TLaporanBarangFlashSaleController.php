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

class TLaporanBarangFlashSaleController extends Controller
{
    public function index(Request $request)
    {
        try {
            $judul = 'Laporan Barang Flash Sale';

            $CBG = Auth::user()->CBG ?? null;
            if (! $CBG) {
                Log::error('User tidak memiliki CBG');
                return view("otransaksi_TLaporanBarangFlashSale.index")->with([
                    'judul' => $judul,
                    'error' => 'User tidak memiliki akses cabang (CBG). Hubungi administrator.',
                ]);
            }

            if (! $request->session()->has('periode')) {
                Log::warning('Periode belum diset');
                return view("otransaksi_TLaporanBarangFlashSale.index")->with([
                    'judul'   => $judul,
                    'warning' => 'Periode belum diset. Silakan set periode terlebih dahulu.',
                ]);
            }

            Log::info('Halaman index dimuat sukses', ['cbg' => $CBG]);

            return view("otransaksi_TLaporanBarangFlashSale.index")->with([
                'judul' => $judul,
                'cbg'   => $CBG,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TLaporanBarangFlashSale index: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return view("otransaksi_TLaporanBarangFlashSale.index")->with([
                'judul' => 'Laporan Barang Flash Sale',
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function cari_data(Request $request)
    {
        try {
            $CBG      = Auth::user()->CBG ?? null;
            $USERNAME = Auth::user()->username ?? 'unknown';

            if (! $CBG) {
                Log::error('User tidak memiliki CBG');
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $periode = $request->session()->get('periode', date('m.Y'));

            $query = DB::table('laporan_brg_macet');

            $count = $query->count();

            return Datatables::of($query)
                ->addIndexColumn()
            // ->editColumn('dis_sebelumnya', function ($row) {
            //     return number_format($row->dis_sebelumnya, 2);
            // })
            // ->editColumn('dis_baru', function ($row) {
            //     return number_format($row->dis_baru, 2);
            // })
            // ->editColumn('stok', function ($row) {
            //     return number_format($row->stok, 0);
            // })
            // ->editColumn('tgl_jual', function ($row) {
            //     return $row->tgl_jual ? date('d-m-Y', strtotime($row->tgl_jual)) : '-';
            // })
            // ->editColumn('ukuran', function ($row) {
            //     return $row->ukuran ?? '-';
            // })
            // ->editColumn('kemasan', function ($row) {
            //     return $row->kemasan ?? '-';
            // })
            // ->editColumn('kd_program', function ($row) {
            //     return $row->kd_program ?? '-';
            // })
                ->make(true);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function detail(Request $request, $id)
    {
        try {
            $CBG      = Auth::user()->CBG ?? null;
            $USERNAME = Auth::user()->username ?? 'unknown';

            if (! $CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            try {
                $result = DB::select("CALL pjl_lap_flashsale(?)", [$CBG]);
            } catch (\Exception $procError) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Stored procedure gagal: ' . $procError->getMessage(),
                    'message' => 'Proses data gagal, namun Anda masih dapat melihat data yang tersedia.',
                ], 500);
            }

            $data = DB::select("SELECT * FROM laporan_brg_macet");

            return response()->json([
                'success' => true,
                'message' => 'Proses data berhasil! Total ' . count($data) . ' data telah diproses.',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function print(Request $request)
    {
        try {
            $CBG      = Auth::user()->CBG ?? null;
            $USERNAME = Auth::user()->username ?? 'unknown';
            $TGL      = Carbon::now()->format('d/m/Y');
            $JAM      = Carbon::now()->addHour()->toTimeString();

            if (! $CBG) {
                return response()->json(['error' => 'User tidak memiliki akses cabang'], 400);
            }

            $result = DB::SELECT("SELECT * FROM laporan_brg_macet");

            $file         = 'print_barang_flashsale';
            $PHPJasperXML = new PHPJasperXML();
            $PHPJasperXML->load_xml_file(base_path("/app/reportc01/phpjasperxml/{$file}.jrxml"));

            $cleanData                    = json_decode(json_encode($result), true);
            $PHPJasperXML->arrayParameter = [
                "TGL" => $TGL,
                "JAM" => $JAM,
            ];

            $PHPJasperXML->setData($cleanData);
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $PHPJasperXML->outpage("I");
            return;

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Proses gagal: ' . $e->getMessage(),
            ], 500);
        }
    }
}
