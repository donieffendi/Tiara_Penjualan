<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PostingKoreksiController extends Controller
{
    public function index(Request $request)
    {
        $flagg = $request->route()->getName();

        $flagMap = [
            'phkoreksitoko' => 'HS',
            'phkoreksigudang' => 'MH',
            'phpostingkoreksimanual' => 'HZ'
        ];

        $flag = $flagMap[$flagg] ?? 'HS';

        $titleMap = [
            'HS' => 'Posting Koreksi Toko',
            'MH' => 'Posting Koreksi Gudang',
            'HZ' => 'Posting Koreksi Manual'
        ];

        $data = [
            'flagg' => $flag,
            'title' => $titleMap[$flag] ?? 'Posting Koreksi'
        ];

        return view('promosi_hadiah_posting_koreksi.index', $data);
    }

    public function getData(Request $request)
    {
        $flagg = $request->get('flagg', 'HS');
        $cbg = session('user_cabang', 'CB');

        $query = DB::table('stockb')
            ->select(
                'no_bukti',
                'tgl',
                'notes',
                DB::raw('namas as supplier'),
                DB::raw('total_qty as total'),
                DB::raw('posted as cek')
            )
            ->where('flag', $flagg)
            ->where('posted', 0)
            ->where('cbg', $cbg)
            ->orderBy('no_bukti');

        $totalData = $query->count();

        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->get('search')['value'] ?? '';
        $orderColumn = $request->get('order')[0]['column'] ?? 1;
        $orderDir = $request->get('order')[0]['dir'] ?? 'asc';

        $columns = ['no_bukti', 'no_bukti', 'tgl', 'notes', 'namas', 'total_qty', 'posted'];

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_bukti', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('namas', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();

        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $query->orderBy($columns[$orderColumn], $orderDir);

        $data = $query->get();

        return response()->json([
            'draw' => intval($request->get('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        try {
            $selected_bukti = $request->get('selected_bukti', []);
            $flagg = $request->get('flagg', 'HS');
            $cbg = session('user_cabang', 'CB');

            if (empty($selected_bukti)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
            }

            $posted_count = 0;

            foreach ($selected_bukti as $bukti) {
                DB::beginTransaction();

                try {
                    if ($flagg == 'HS') {
                        $details = DB::select("SELECT STOCKBD.JNS, STOCKBD.NO_ID, stockbd.KD_BRG,
                                               stockbd.QTY, stockbd.FLAG, stockbd.per
                                               FROM stockbd WHERE stockbd.no_bukti = ?", [$bukti]);

                        if (!empty($details)) {
                            $monthstring = substr($details[0]->per, 0, 2);
                            $mon_hdh = $details[0]->JNS;

                            foreach ($details as $detail) {
                                DB::statement(
                                    "UPDATE brghd SET
                                              ln{$mon_hdh} = ln{$mon_hdh} + ?,
                                              ak{$mon_hdh} = aw{$mon_hdh} + ma{$mon_hdh} - ke{$mon_hdh} + ln{$mon_hdh}
                                              WHERE kd_brgh = ? AND cbg = ?",
                                    [$detail->QTY, $detail->KD_BRG, $cbg]
                                );

                                DB::statement(
                                    "UPDATE brghd SET
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
                                              WHERE kd_brgh = ? AND cbg = ?",
                                    [$detail->KD_BRG, $cbg]
                                );
                            }
                        }
                    } elseif ($flagg == 'MH') {
                        $details = DB::select("SELECT STOCKBD.JNS, STOCKBD.NO_ID, stockbd.KD_BRG,
                                               stockbd.QTY, stockbd.FLAG, stockbd.per
                                               FROM stockbd WHERE stockbd.no_bukti = ?", [$bukti]);

                        if (!empty($details)) {
                            $monthstring = substr($details[0]->per, 0, 2);

                            foreach ($details as $detail) {
                                DB::statement(
                                    "UPDATE brghd SET
                                              ln{$monthstring} = ln{$monthstring} + ?,
                                              ak{$monthstring} = aw{$monthstring} + ma{$monthstring} - ke{$monthstring} + ln{$monthstring}
                                              WHERE kd_brgh = ? AND cbg = ?",
                                    [$detail->QTY, $detail->KD_BRG, $cbg]
                                );

                                DB::statement(
                                    "UPDATE brghd SET
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
                                              WHERE kd_brgh = ? AND cbg = ?",
                                    [$detail->KD_BRG, $cbg]
                                );
                            }
                        }
                    } elseif ($flagg == 'HZ') {
                        $details = DB::select("SELECT STOCKBD.JNS, STOCKBD.NO_ID, stockbd.KD_BRG,
                                               stockbd.QTY, stockbd.FLAG, stockbd.per
                                               FROM stockbd WHERE stockbd.no_bukti = ?", [$bukti]);

                        if (!empty($details)) {
                            $mon_hdh = $details[0]->JNS;

                            foreach ($details as $detail) {
                                DB::statement(
                                    "UPDATE brghd SET
                                              gln{$mon_hdh} = gln{$mon_hdh} + ?,
                                              gak{$mon_hdh} = gaw{$mon_hdh} + gma{$mon_hdh} - gke{$mon_hdh} + gln{$mon_hdh}
                                              WHERE kd_brgh = ? AND cbg = ?",
                                    [$detail->QTY, $detail->KD_BRG, $cbg]
                                );

                                DB::statement(
                                    "UPDATE brghd SET
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
                                              WHERE kd_brgh = ? AND cbg = ?",
                                    [$detail->KD_BRG, $cbg]
                                );
                            }
                        }
                    }

                    DB::statement("CALL poststkb(?)", [$bukti]);

                    $posted_count++;
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['success' => false, 'message' => 'Error posting ' . $bukti . ': ' . $e->getMessage()]);
                }
            }

            return response()->json(['success' => true, 'message' => $posted_count . ' dokumen berhasil diposting']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
