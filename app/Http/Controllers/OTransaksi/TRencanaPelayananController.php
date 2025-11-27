<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TRencanaPelayananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('otransaksi_RencanaPelayanan.index');
    }

    /**
     * Get Customer Status Information (Based on Delphi logic)
     * Implements the core logic from Delphi Button1Click procedure
     */
    public function getCustomerStatus(Request $request)
    {
        $kodec = $request->kodec;

        if (empty($kodec)) {
            return response()->json([
                'plafon' => 'Plafon :',
                'terhutang' => 'Terhutang :',
                'sisa' => 'Sisa :'
            ]);
        }

        try {
            // Exact Delphi SQL query implementation:
            // select cust.lim,custd.ak12 as hutang,cust.lim-custd.ak12 as saldo
            // from cust,custd where cust.kodec=custd.kodec and cust.kodec=:kodec
            $result = DB::SELECT("
                SELECT cust.lim, custd.ak12 as hutang, cust.lim - custd.ak12 as saldo
                FROM cust, custd
                WHERE cust.kodec = custd.kodec
                AND cust.kodec = ?
            ", [$kodec]);

            if (!empty($result)) {
                $data = $result[0];

                // Format currency exactly like Delphi's formatcurr('#,##0.00', value)
                $plafon = number_format($data->lim, 2, '.', ',');
                $hutang = number_format($data->hutang, 2, '.', ',');
                $saldo = number_format($data->saldo, 2, '.', ',');

                return response()->json([
                    'success' => true,
                    'plafon' => 'Plafon : ' . $plafon,
                    'terhutang' => 'Terhutang : ' . $hutang,
                    'sisa' => 'Sisa : ' . $saldo
                ]);
            } else {
                // No records found - return empty labels like Delphi would
                return response()->json([
                    'success' => false,
                    'plafon' => 'Plafon :',
                    'terhutang' => 'Terhutang :',
                    'sisa' => 'Sisa :'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving customer status: ' . $e->getMessage(),
                'plafon' => 'Plafon :',
                'terhutang' => 'Terhutang :',
                'sisa' => 'Sisa :'
            ], 500);
        }
    }

    /**
     * Reset Customer Status Display (Based on Delphi FormShow logic)
     * Implements the FormShow procedure from Delphi
     */
    public function resetCustomerStatus()
    {
        return response()->json([
            'kodec' => '',
            'plafon' => 'Plafon :',
            'terhutang' => 'Terhutang :',
            'sisa' => 'Sisa :'
        ]);
    }

    /**
     * Browse customers with search functionality (minimal implementation)
     */
    public function browseCustomers(Request $request)
    {
        $search = $request->q ?? '';

        if (!empty($search)) {
            $customers = DB::SELECT("
                SELECT kodec, namac, CONCAT(kodec, '-', namac) AS display_name
                FROM cust
                WHERE (namac LIKE '%$search%' OR kodec LIKE '%$search%')
                AND namac <> ''
                ORDER BY kodec
                LIMIT 30
            ");
        } else {
            $customers = DB::SELECT("
                SELECT kodec, namac, CONCAT(kodec, '-', namac) AS display_name
                FROM cust
                WHERE namac <> ''
                ORDER BY kodec
                LIMIT 30
            ");
        }

        return response()->json($customers);
    }
}
