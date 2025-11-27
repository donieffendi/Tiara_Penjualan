<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

include_once base_path() . "/vendor/simitgroup/phpjasperxml/version/1.1/PHPJasperXML.inc.php";

use PHPJasperXML;

class PHPembayaranPiutangController extends Controller
{
    /**
     * Display index page for pembayaran piutang
     */
    public function index()
    {
        return view('promo_hadiah_pembayaran_piutang.index');
    }

    /**
     * Get list of pembayaran piutang for datatable
     * Equivalent to Delphi's Tampil procedure
     */
    public function getPembayaranPiutang(Request $request)
    {
        $per = session('periode', date('m.Y'));
        $periode = $per['bulan'] . '/'.$per['tahun'];
        // Query matching Delphi: SELECT * FROM piu where flag='PC' and per=:per order by NO_BUKTI
        $query = DB::select(
            "SELECT no_bukti, tgl, jtempo, kodec, namac, alamat, kota,
                    tbayar, acno, total, bayar, lain, posted, notes
             FROM piu
             WHERE flag='PC' AND per=?
             ORDER BY no_bukti DESC",
            [$periode]
        );
      
        return Datatables::of(collect($query))
            ->addIndexColumn()
            ->editColumn('tgl', function ($row) {
                return $row->tgl ? date('d/m/Y', strtotime($row->tgl)) : '';
            })
            ->editColumn('jtempo', function ($row) {
                return $row->jtempo ? date('d/m/Y', strtotime($row->jtempo)) : '';
            })
            ->editColumn('total', function ($row) {
                return number_format($row->total, 2);
            })
            ->editColumn('bayar', function ($row) {
                return number_format($row->bayar, 2);
            })
            ->editColumn('lain', function ($row) {
                return number_format($row->lain, 2);
            })
            ->editColumn('posted', function ($row) {
                return $row->posted == 1 ? '<span class="badge badge-success">Posted</span>' : '<span class="badge badge-warning">Open</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row->posted == 0) {
                    $btnEdit = '<button onclick="editData(\'' . $row->no_bukti . '\')" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></button>';
                } else {
                    $btnEdit = '<button class="btn btn-sm btn-secondary" disabled title="Sudah Terposting"><i class="fas fa-lock"></i></button>';
                }
            $btnPrint = '<a target="_blank" href="phpembayaranpiutang/print/' . $row->no_bukti . '" class="btn btn-sm btn-info ml-1" title="Print"><i class="fas fa-print"></i></a>';
            return $btnEdit . ' ' . $btnPrint;
            })
            ->rawColumns(['action', 'posted'])
            ->make(true);
    }
  

    public function printPembayaran($no_bukti)
    {
        function terbilang($nilai)
        {
            $nilai = intval($nilai);

            $angka = array(
                "",
                "Satu",
                "Dua",
                "Tiga",
                "Empat",
                "Lima",
                "Enam",
                "Tujuh",
                "Delapan",
                "Sembilan",
                "Sepuluh",
                "Sebelas"
            );

            if ($nilai < 12) {
                return $angka[$nilai];
            } elseif ($nilai < 20) {
                return terbilang($nilai - 10) . " Belas";
            } elseif ($nilai < 100) {
                return terbilang(intval($nilai / 10)) . " Puluh " . terbilang($nilai % 10);
            } elseif ($nilai < 200) {
                return "Seratus " . terbilang($nilai - 100);
            } elseif ($nilai < 1000) {
                return terbilang(intval($nilai / 100)) . " Ratus " . terbilang($nilai % 100);
            } elseif ($nilai < 2000) {
                return "Seribu " . terbilang($nilai - 1000);
            } elseif ($nilai < 1000000) {
                return terbilang(intval($nilai / 1000)) . " Ribu " . terbilang($nilai % 1000);
            } elseif ($nilai < 1000000000) {
                return terbilang(intval($nilai / 1000000)) . " Juta " . terbilang($nilai % 1000000);
            } elseif ($nilai < 1000000000000) {
                return terbilang(intval($nilai / 1000000000)) . " Milyar " . terbilang($nilai % 1000000000);
            } elseif ($nilai < 1000000000000000) {
                return terbilang(intval($nilai / 1000000000000)) . " Triliun " . terbilang($nilai % 1000000000000);
            }

            return "";
        }

        function terbilangRupiah($nilai)
        {
            
            $hasil = trim(terbilang($nilai));
            return $hasil === "" ? "" : $hasil . " Rupiah";
        }
        $file     = 'PHPembayaranPiutang';
        $PHPJasperXML = new PHPJasperXML();
        $PHPJasperXML->load_xml_file(base_path() . ('/app/reportc01/phpjasperxml/' . $file . '.jrxml'));

        //po.GUDANG setelah po.NETT dihapus
        $query = DB::SELECT("SELECT piu.no_bukti, piu.tgl, piu.jtempo, piu.kodec, piu.namac, piu.alamat, piu.kota,
                    piu.tbayar, piu.acno, piu.total, piu.bayar, piu.lain, piu.posted, piu.notes, piu.jtempo
             FROM piu 
             WHERE piu.flag='PC' AND piu.no_bukti=?
             ",[$no_bukti]);
        

        $data = [];

        foreach ($query as $key => $value) {
            array_push($data, array(
                'NO_BUKTI' => $query[$key]->no_bukti,
                'TGL'      => date('d/m/Y', strtotime($query[$key]->tgl)),
                'KODEC'    => $query[$key]->kodec,
                'NAMAC'    => $query[$key]->namac,
                'TOTAL'    => $query[$key]->total,
                'TERBILANG'    => terbilangRupiah($query[$key]->total),
                'TBAYAR'    => $query[$key]->tbayar,
                'ACNO'       => $query[$key]->acno,
                'NOTES'    => $query[$key]->notes,
                'JTEMPO'      => date('d/m/Y', strtotime($query[$key]->jtempo)),
            ));
        }

        $PHPJasperXML->setData($data);
        ob_end_clean();
        $PHPJasperXML->outpage("I");

    //     DB::SELECT("UPDATE PO SET POSTED = 1 WHERE PO.NO_BUKTI='$no_po';");
    // echo $no_bukti;        
    }
    /**
     * Show form for create/edit pembayaran piutang
     * Equivalent to Delphi's FormShow and cxGrid1DBTableView1DblClick
     */
    public function edit(Request $request)
    {
        $no_bukti = $request->get('no_bukti');
        $status = $request->get('status', 'simpan');

        // Get periode as string
        $periode = session('periode', date('m.Y'));

        $data = [
            'no_bukti' => '+',
            'status' => $status,
            'header' => null,
            'detail' => [],
            'periode' => $periode  // Pastikan ini string
        ];

        if ($status == 'edit' && $no_bukti) {
            // Check if posted (matching Delphi logic)
            $check_posted = DB::select("SELECT posted FROM piu WHERE no_bukti = ?", [$no_bukti]);

            if (!empty($check_posted) && $check_posted[0]->posted == 1) {
                return redirect()->route('phpembayaranpiutang')->with('error', 'Nota Sudah Terposting !!');
            }

            // Get header data
            $header = DB::select(
                "SELECT no_bukti, tgl, jtempo, kodec, namac, alamat, kota,
                    tbayar, acno, total, bayar, lain, notes, posted
             FROM piu
             WHERE no_bukti = ?
             ORDER BY no_bukti",
                [$no_bukti]
            );

            if (!empty($header)) {
                // Get detail data
                $detail = DB::select(
                    "SELECT no_bukti, rec, no_faktur, tgl_faktur, total, bayar, lain, sisa, uraian, no_id
                 FROM piud
                 WHERE no_bukti = ?
                 ORDER BY rec",
                    [$no_bukti]
                );

                $data['header'] = $header[0];
                $data['detail'] = $detail;
                $data['no_bukti'] = $no_bukti;
            }
        }

        return view('promo_hadiah_pembayaran_piutang.edit', $data);
    }

    /**
     * Store/Update pembayaran piutang
     * Equivalent to Delphi's MSaveClick procedure
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tgl' => 'required|date',
            'kodec' => 'required',
            'tbayar' => 'required',
            'acno' => 'required',
            'details' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            $no_bukti = trim($request->no_bukti);
            $status = $request->status;
            $periode = session('periode', date('m.Y'));
            $cbg = session('cbg', '01');
            $username = Auth::user()->username ?? 'system';

            // Check if period is closed - matching Delphi's hero.sql check
            $check_period = DB::select("SELECT posted FROM perid WHERE kd_peri=?", [$periode]);
            if (!empty($check_period) && $check_period[0]->posted == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closed Period'
                ], 400);
            }

            // Validate month and year match periode (matching Delphi's checkx procedure)
            $tgl = Carbon::parse($request->tgl);
            $monthz = str_pad($tgl->month, 2, '0', STR_PAD_LEFT);
            $yearz = $tgl->year;

            $periode_month = substr($periode, 0, 2);
            $periode_year = substr($periode, -4);

            if ($monthz != $periode_month) {
                return response()->json([
                    'success' => false,
                    'message' => 'Month is not the same as Periode.'
                ], 400);
            }

            if ($yearz != $periode_year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Year is not the same as Periode.'
                ], 400);
            }

            // Calculate totals (matching Delphi's Hitung procedure)
            $total_amount = 0;
            $total_bayar = 0;
            $total_lain = 0;

            foreach ($request->details as $detail) {
                if (!empty($detail['no_faktur'])) {
                    $total_amount += floatval($detail['total'] ?? 0);
                    $total_bayar += floatval($detail['bayar'] ?? 0);
                    $total_lain += floatval($detail['lain'] ?? 0);
                }
            }

            if ($status == 'simpan') {
                // Generate no_bukti (matching Delphi logic)
                if ($no_bukti == '+') {
                    $no_bukti = $this->generateNoBukti($periode, $cbg);
                }

                // Insert header - matching Delphi's INSERT INTO PIU
                DB::statement(
                    "INSERT INTO piu (no_bukti, tbayar, acno, tgl, jtempo, per, flag, kodec, namac, alamat, kota, notes, total, lain, sisa, usrnm, tg_smp, bayar, cbg)
                     VALUES (?, ?, ?, ?, ?, ?, 'PC', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)",
                    [
                        $no_bukti,
                        $request->tbayar,
                        $request->acno,
                        $request->tgl,
                        $request->jtempo,
                        $periode,
                        $request->kodec,
                        $request->namac,
                        $request->alamat ?? '',
                        $request->kota ?? '',
                        $request->notes ?? '',
                        $total_amount,
                        $total_lain,
                        $total_amount, // sisa initially equals total
                        $username,
                        $total_bayar,
                        $cbg
                    ]
                );
            } else {
                // Update mode - call PIUDEL stored procedure first (matching Delphi)
                DB::statement("CALL PIUDEL(?)", [$no_bukti]);

                // Update header - matching Delphi's UPDATE PIU
                DB::statement(
                    "UPDATE piu
                     SET tbayar=?, acno=?, tgl=?, jtempo=?, kodec=?, namac=?, alamat=?, kota=?, notes=?, total=?, bayar=?, lain=?, sisa=?-?, usrnm=?, tg_smp=NOW()
                     WHERE no_bukti=?",
                    [
                        $request->tbayar,
                        $request->acno,
                        $request->tgl,
                        $request->jtempo,
                        $request->kodec,
                        $request->namac,
                        $request->alamat ?? '',
                        $request->kota ?? '',
                        $request->notes ?? '',
                        $total_amount,
                        $total_bayar,
                        $total_lain,
                        $total_amount,
                        $total_bayar,
                        $username,
                        $no_bukti
                    ]
                );
            }

            // Get header ID for detail records
            $header_id_result = DB::select("SELECT no_id FROM piu WHERE no_bukti=?", [$no_bukti]);
            $id = $header_id_result[0]->no_id ?? 0;

            // Handle detail updates (matching Delphi's complex update logic)
            if ($status == 'edit') {
                $existing_details = DB::select("SELECT no_id FROM piud WHERE no_bukti = ?", [$no_bukti]);

                foreach ($existing_details as $existing) {
                    $found = false;
                    foreach ($request->details as $detail) {
                        if (isset($detail['no_id']) && $detail['no_id'] == $existing->no_id) {
                            // Update existing record - matching Delphi's UPDATE PIUD
                            $sisa = floatval($detail['total'] ?? 0) - floatval($detail['bayar'] ?? 0) + floatval($detail['lain'] ?? 0);

                            DB::statement(
                                "UPDATE piud
                                 SET rec=?, no_faktur=?, tgl_faktur=?, total=?, bayar=?, lain=?, sisa=?, uraian=?
                                 WHERE no_id=?",
                                [
                                    intval($detail['rec'] ?? 1),
                                    trim($detail['no_faktur'] ?? ''),
                                    $detail['tgl_faktur'] ?? null,
                                    floatval($detail['total'] ?? 0),
                                    floatval($detail['bayar'] ?? 0),
                                    floatval($detail['lain'] ?? 0),
                                    $sisa,
                                    trim($detail['uraian'] ?? ''),
                                    $existing->no_id
                                ]
                            );
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        // Delete record not found in new data - matching Delphi's DELETE FROM PIUD
                        DB::statement("DELETE FROM piud WHERE no_id = ?", [$existing->no_id]);
                    }
                }
            }

            // Insert new detail records (matching Delphi's INSERT INTO PIUD)
            $rec = 1;
            foreach ($request->details as $detail) {
                if (!empty($detail['no_faktur'])) {
                    if (!isset($detail['no_id']) || $detail['no_id'] == 0) {
                        $sisa = floatval($detail['total'] ?? 0) - floatval($detail['bayar'] ?? 0) + floatval($detail['lain'] ?? 0);

                        DB::statement(
                            "INSERT INTO piud (no_bukti, rec, per, flag, no_faktur, tgl_faktur, total, bayar, lain, sisa, uraian, id)
                             VALUES (?, ?, ?, 'PC', ?, ?, ?, ?, ?, ?, ?, ?)",
                            [
                                $no_bukti,
                                $rec,
                                $periode,
                                trim($detail['no_faktur']),
                                $detail['tgl_faktur'] ?? null,
                                floatval($detail['total'] ?? 0),
                                floatval($detail['bayar'] ?? 0),
                                floatval($detail['lain'] ?? 0),
                                $sisa,
                                trim($detail['uraian'] ?? ''),
                                $id
                            ]
                        );
                    }
                    $rec++;
                }
            }

            // Call PIUINS stored procedure (matching Delphi)
            DB::statement("CALL PIUINS(?)", [$no_bukti]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Save Data Success'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Browse customer data
     * Equivalent to Delphi's txtkodecExit procedure
     */
    public function browse(Request $request)
    {
        $type = $request->get('type', 'customer');
        $q = $request->get('q', '');

        if ($type == 'customer') {
            // Browse customers - matching Delphi: SELECT * from cust where kodec=:b1
            if (!empty($q)) {
                $data = DB::select(
                    "SELECT kodec, namac, alamat, kota
                     FROM cust
                     WHERE kodec LIKE ? OR namac LIKE ?
                     ORDER BY kodec
                     LIMIT 50",
                    ["%$q%", "%$q%"]
                );
            } else {
                $data = DB::select(
                    "SELECT kodec, namac, alamat, kota
                     FROM cust
                     ORDER BY kodec
                     LIMIT 50"
                );
            }
        } elseif ($type == 'account') {
            // Browse accounts (account)
            if (!empty($q)) {
                $data = DB::select(
                    "SELECT acno, nama
                     FROM account
                     WHERE acno LIKE ? OR nama LIKE ?
                     ORDER BY acno
                     LIMIT 50",
                    ["%$q%", "%$q%"]
                );
            } else {
                $data = DB::select(
                    "SELECT acno, nama
                     FROM account
                     ORDER BY acno
                     LIMIT 50"
                );
            }
        } elseif ($type == 'faktur') {
            // Browse faktur/invoice - matching Delphi logic in cxGrid1DBTableView1EditKeyDown
            $kodec = $request->get('kodec', '');
            $mm = session('per', ''); // Historical period suffix

            if (!empty($q)) {
                // Check current and historical jual tables
                $data = DB::select(
                    "SELECT no_bukti, tgl, totala as total
                     FROM jual
                     WHERE no_bukti LIKE ? AND kodec=?
                     UNION ALL
                     SELECT no_bukti, tgl, totala as total
                     FROM jual{$mm}
                     WHERE no_bukti LIKE ? AND kodec=?
                     ORDER BY no_bukti DESC
                     LIMIT 50",
                    ["%$q%", $kodec, "%$q%", $kodec]
                );
            } else {
                $data = DB::select(
                    "SELECT no_bukti, tgl, totala as total
                     FROM jual
                     WHERE kodec=?
                     UNION ALL
                     SELECT no_bukti, tgl, totala as total
                     FROM jual{$mm}
                     WHERE kodec=?
                     ORDER BY no_bukti DESC
                     LIMIT 50",
                    [$kodec, $kodec]
                );
            }
        } else {
            $data = [];
        }

        return response()->json($data);
    }

    /**
     * Get customer detail by code
     * Equivalent to Delphi's txtkodecExit
     */
    public function getBarangDetail(Request $request)
    {
        $type = $request->get('type', 'customer');

        if ($type == 'customer') {
            $kodec = $request->get('kodec');

            $customer = DB::select(
                "SELECT kodec, namac, alamat, kota
                 FROM cust
                 WHERE kodec = ?",
                [$kodec]
            );

            if (!empty($customer)) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $customer[0]
                ]);
            }
        } elseif ($type == 'faktur') {
            // Check if faktur exists and not yet paid
            $no_faktur = $request->get('no_faktur');
            $mm = session('per', '');

            // Check in jual tables (matching Delphi logic)
            $faktur = DB::select(
                "SELECT no_bukti, tgl, totala as total
                 FROM jual
                 WHERE no_bukti = ?
                 UNION ALL
                 SELECT no_bukti, tgl, totala as total
                 FROM jual{$mm}
                 WHERE no_bukti = ?",
                [$no_faktur, $no_faktur]
            );

            if (!empty($faktur)) {
                // Check if already has payment instruction (matching Delphi's com00 check)
                $check_piud = DB::select(
                    "SELECT no_bukti
                     FROM piud
                     WHERE no_faktur = ?",
                    [$no_faktur]
                );

                if (!empty($check_piud)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nomor kitir sudah dibuatkan Instruksi Penagihan di nomor: ' . $check_piud[0]->no_bukti
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $faktur[0]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Nomor kitir tidak ditemukan'
            ]);
        } elseif ($type == 'account') {
            $acno = $request->get('acno');

            $account = DB::select(
                "SELECT acno, nama
                 FROM account
                 WHERE acno = ?",
                [$acno]
            );

            if (!empty($account)) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => $account[0]
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'exists' => false,
            'data' => null
        ]);
    }

    /**
     * Check if bukti exists
     */
    public function cekOrder(Request $request)
    {
        $no_bukti = $request->no_bukti;
        $result = DB::select("SELECT COUNT(*) as ada FROM piu WHERE no_bukti = ?", [$no_bukti]);

        return response()->json(['exists' => $result[0]->ada > 0]);
    }

    /**
     * Print pembayaran piutang
     * Equivalent to Delphi's Print1Click procedure
     */
    public function printOrder(Request $request)
    {
        $no_bukti = $request->no_bukti;

        // Get print data with terbilang - matching Delphi's Print1Click
        $data = DB::select(
            "SELECT piu.no_bukti, piu.tgl, piu.jtempo, piu.tbayar, piu.acno, piu.kodec, piu.namac,
                    piu.alamat, piu.kota, piu.notes, piud.no_faktur, piud.tgl_faktur,
                    piud.total, piud.bayar, piud.lain, piud.sisa, piud.uraian
             FROM piu, piud
             WHERE piu.no_bukti = piud.no_bukti
               AND TRIM(piu.no_bukti) = TRIM(?)
             ORDER BY piud.rec",
            [$no_bukti]
        );

        // Calculate total for terbilang
        $total = 0;
        if (!empty($data)) {
            foreach ($data as $row) {
                $total += $row->total;
            }
        }

        // Generate terbilang (Indonesian number to words)
        $terbilang = $this->terbilang(strval($total));

        return response()->json([
            'data' => $data,
            'terbilang' => $terbilang
        ]);
    }

    /**
     * Delete pembayaran piutang (currently disabled in Delphi, kept for reference)
     */
    public function destroy($no_bukti)
    {
        try {
            $check_posted = DB::select("SELECT posted FROM piu WHERE no_bukti = ?", [$no_bukti]);

            if (!empty($check_posted) && $check_posted[0]->posted == 1) {
                return redirect()->route('phpembayaranpiutang')->with('error', 'Data sudah terposting, tidak dapat dihapus');
            }

            // Note: Delete is commented out in Delphi code, so we keep this as reference only
            // In production, you might want to enable this or keep it disabled as per business rules

            return redirect()->route('phpembayaranpiutang')->with('info', 'Fungsi hapus tidak diaktifkan');
        } catch (\Exception $e) {
            return redirect()->route('phpembayaranpiutang')->with('error', 'Gagal menghapus data');
        }
    }

    /**
     * Generate no_bukti for new transaction
     * Equivalent to Delphi's TxtBukti.Text='+'  logic in MSaveClick
     */
    private function generateNoBukti($periode, $cbg)
    {
        $monthString = substr($periode, 0, 2);
        $year = substr($periode, -4);

        // Get toko type (matching Delphi logic)
        $toko = DB::select("SELECT type FROM toko WHERE kode = ?", [$cbg]);
        $kode2 = $toko[0]->type ?? '';

        $kode = 'PC' . substr($year, -2) . $monthString;

        // Get next number from notrans (matching Delphi's NOM query)
        $notrans = DB::select("SELECT NOM{$monthString} as no_bukti FROM notrans WHERE trans='PIU' AND per=?", [$year]);
        $r1 = ($notrans[0]->no_bukti ?? 0) + 1;

        // Update counter
        DB::statement("UPDATE notrans SET NOM{$monthString} = ? WHERE trans='PIU' AND per=?", [$r1, $year]);

        $bkt1 = str_pad($r1, 4, '0', STR_PAD_LEFT);
        return $kode . '-' . $bkt1 . $kode2;
    }

    /**
     * Convert number to Indonesian words
     * Equivalent to Delphi's terbilang function
     */
    private function terbilang($sValue)
    {
        $angka = [
            '',
            'Satu',
            'Dua',
            'Tiga',
            'Empat',
            'Lima',
            'Enam',
            'Tujuh',
            'Delapan',
            'Sembilan',
            'Sepuluh',
            'Sebelas',
            'Duabelas',
            'Tigabelas',
            'Empatbelas',
            'Limabelas',
            'Enambelas',
            'Tujuhbelas',
            'Delapanbelas',
            'Sembilanbelas'
        ];

        $sPattern = '000000000000000';
        $s = substr($sPattern, 0, strlen($sPattern) - strlen(trim($sValue))) . $sValue;

        $one = 4;
        $two = 5;
        $three = 6;
        $hitung = 1;
        $rupiah = '';

        while ($hitung < 5) {
            $satu = substr($s, $one - 1, 1);
            $dua = substr($s, $two - 1, 1);
            $tiga = substr($s, $three - 1, 1);
            $gabung = $satu . $dua . $tiga;

            if (intval($satu) == 1) {
                $rupiah .= 'Seratus ';
            } elseif (intval($satu) > 1) {
                $rupiah .= $angka[intval($satu)] . ' Ratus ';
            }

            if (intval($dua) == 1) {
                $belas = $dua . $tiga;
                $rupiah .= $angka[intval($belas)];
            } elseif (intval($dua) > 1) {
                $rupiah .= $angka[intval($dua)] . ' Puluh ' . $angka[intval($tiga)];
            } elseif (intval($dua) == 0 && intval($tiga) > 0) {
                if (($hitung == 3 && $gabung == '001') || ($hitung == 3 && $gabung == '  1')) {
                    $rupiah .= 'Seribu ';
                } else {
                    $rupiah .= $angka[intval($tiga)];
                }
            }

            if ($hitung == 1 && intval($gabung) > 0) {
                $rupiah .= ' Milyar ';
            } elseif ($hitung == 2 && intval($gabung) > 0) {
                $rupiah .= ' Juta ';
            } elseif ($hitung == 3 && intval($gabung) > 0) {
                if ($gabung == '001' || $gabung == '  1') {
                    $rupiah .= '';
                } else {
                    $rupiah .= ' Ribu ';
                }
            }

            $hitung++;
            $one += 3;
            $two += 3;
            $three += 3;
        }

        if (strlen($rupiah) > 1) {
            $rupiah .= ' Rupiah ';
        }

        return $rupiah;
    }
}
