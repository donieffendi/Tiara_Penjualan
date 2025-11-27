<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PHDataUndianCustomerController extends Controller
{
    public function index()
    {
        return view('promo_hadiah_data_undian_customer.index', [
            'title' => 'Data Undian Customer'
        ]);
    }

    public function saveConfig(Request $request)
    {
        try {
            $pin = trim($request->get('pin'));
            $kodec = trim($request->get('kodec'));
            $namac = trim($request->get('namac'));

            $check = DB::selectOne("SELECT COUNT(*) as cnt FROM jualund WHERE pin=? AND kodec=''", [$pin]);

            if ($check->cnt > 0) {
                DB::update("UPDATE jualund SET kodec=?, namac=? WHERE pin=?", [$kodec, $namac, $pin]);
                return response()->json(['success' => true, 'message' => 'Masukkan data selesai']);
            } else {
                return response()->json(['success' => false, 'message' => 'Pin sudah terisi data atau pin tidak ada...']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa disimpan, terjadi kesalahan...']);
        }
    }

    public function getConfig(Request $request)
    {
        try {
            $pin = trim($request->get('pin'));

            $data = DB::selectOne("SELECT COUNT(*) as cnt FROM jualund WHERE pin=? AND kodec=''", [$pin]);

            if ($data->cnt > 0) {
                return response()->json(['success' => true, 'count' => $data->cnt]);
            } else {
                return response()->json(['success' => false, 'message' => 'Pin sudah terisi data atau pin tidak ada...']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function checkCustomer(Request $request)
    {
        try {
            $kodec = trim($request->get('kodec'));

            $now = DB::selectOne("SELECT CONCAT(
                RIGHT(DATE(NOW()),2),
                SUBSTR(DATE(NOW()),6,2),
                RIGHT(YEAR(NOW()),2),
                LEFT(TIME(NOW()),2),
                SUBSTR(TIME(NOW()),4,2)
            ) as xx");

            $timestamp = $now->xx;
            $parameter = $kodec . ' ' . $timestamp . ' D:\\CRM\\CHKOUT';

            exec('Z:\\CHK02.exe ' . escapeshellarg($parameter) . ' > NUL 2>&1');

            sleep(3);

            $filePath = 'D:\\CRM\\chkout.txt';

            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $parts = explode(';', trim($content));

                if (count($parts) > 0 && $parts[0] == '0') {
                    $namac = isset($parts[4]) ? $parts[4] : '';
                    return response()->json(['success' => true, 'namac' => $namac]);
                } else {
                    return response()->json(['success' => false, 'message' => 'Kode customer tidak valid']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Kesalahan Koneksi']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
