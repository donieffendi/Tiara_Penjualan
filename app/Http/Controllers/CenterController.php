<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CenterController extends Controller
{
     public function pengesahan_brg(Request $request)
    {
		$tipe 	= strtoupper($request->type);		//JENIS USULAN
		$jenis 	= strtoupper($request->jenis);		//JENIS BRG/SUP/HARGA
		$cbg  	= strtoupper($request->cbg);		//OUTLET
		$nafile	= strtoupper($request->na_file); 	//NAMA FILE
		$dbcenter = "tiara_pembelian";
		
		
        if(empty($tipe) || empty($cbg) || empty($nafile)) {
            return response()->json([
                'success' => false,
                'message' => 'Ada isian yang kosong',
                'data'    => $request->all()
            ],401);
        }
		else {
			if ($tipe=='EXP'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						// $na_brg = str_replace("'","''",$rowd['NA_BRG']);
						$na_brg = $rowd['NA_BRG'];
						$ket_uk = $rowd['KET_UK'] ?? '';
						$tarik = $rowd['TARIK'];
						$masa_exp = $rowd['MASA_EXP'];
						$exd = $rowd['EXD'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.brgchd (no_bukti, sub, kdbar, na_brg, ket_uk, kd_brg, tarik, dtb, id) VALUES 
								 ('$no_bukti', '$sub', '$kdbar', ?, ?, concat('$sub','$kdbar'), $tarik, '$masa_exp', $id)", [$na_brg, $ket_uk]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}
			
			if ($tipe=='BTL'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						// $na_brg = str_replace("'","''",$rowd['NA_BRG']);
						$na_brg = $rowd['NA_BRG'];
						$ket_uk = $rowd['KET_UK'] ?? '';
						$ket_kem = $rowd['KET_KEM'];
						$tg_mati = $rowd['TG_OD_G'];
						$ket = $rowd['CAT_OD_G'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.brgchd (no_bukti, sub, kdbar, na_brg, ket_uk, ket_kem, tg_od_g, kd_brg, cat_od_g, id) VALUES 
								 ('$no_bukti', '$sub', '$kdbar', ?, ?, '$ket_kem', '$tg_mati', concat('$sub','$kdbar'), '$ket', $id)", [$na_brg, $ket_uk]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='PPN'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						// $na_brg = str_replace("'","''",$rowd['NA_BRG']);
						$na_brg = $rowd['NA_BRG'];
						$ket_uk = $rowd['KET_UK'] ?? '';
						$tg_smp = $rowd['TG_SMP'];
						$ppn = $rowd['PPN'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.brgchd (no_bukti, sub, kdbar, na_brg, ket_uk, tg_smp, kd_brg, ppn, id) VALUES 
								 ('$no_bukti', '$sub', '$kdba	r', ?, ?, '$tg_smp', concat('$sub','$kdbar'), '$ppn', $id)", [$na_brg, $ket_uk] 
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='BCD'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						// $na_brg = str_replace("'","''",$rowd['NA_BRG']);
						$na_brg = $rowd['NA_BRG'];
						$ket_uk = $rowd['KET_UK'] ?? '';
						$ket_kem = $rowd['KET_KEM'];
						$tg_smp = $rowd['TG_SMP'];
						$barcode = $rowd['BARCODE'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.brgchd (no_bukti, sub, kdbar, na_brg, ket_uk, ket_kem, tg_smp, kd_brg, barcode, id) VALUES 
								 ('$no_bukti', '$sub', '$kdbar', ?, ?, '$ket_kem','$tg_smp', concat('$sub','$kdbar'), '$barcode', $id)", [$na_brg, $ket_uk]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='002'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						// $na_brg = str_replace("'","''",$rowd['NA_BRG']);
						$na_brg = $rowd['NA_BRG'];
						$ket_uk = $rowd['KET_UK'] ?? '';
						$ket_kem = $rowd['KET_KEM'];
						$tg_smp = $rowd['TG_SMP'];
						$retur = $rowd['RETUR'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.brgchd (no_bukti, sub, kdbar, na_brg, ket_uk, ket_kem, tg_smp, kd_brg, retur, id) VALUES 
								 ('$no_bukti', '$sub', '$kdbar', ?, ?, '$ket_kem','$tg_smp', concat('$sub','$kdbar'), '$retur', $id)", [$na_brg, $ket_uk]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='KMS'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						// $na_brg = str_replace("'","''",$rowd['NA_BRG']);
						$na_brg = $rowd['NA_BRG'];
						$ket_kem = $rowd['KET_KEM'];
						$tg_smp = $rowd['TG_SMP'];
						$klk = $rowd['KLK'];
						$mo = $rowd['MO'];
						$kem_p = $rowd['KEM_P'];						
						$supp = $rowd['SUPP'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.brgchd (no_bukti, sub, kdbar, na_brg, ket_kem, tg_smp, kd_brg, klk, mo, kem_p, supp, id) VALUES 
								 ('$no_bukti', '$sub', '$kdbar', ?, '$ket_kem','$tg_smp', concat('$sub','$kdbar'), '$klk', '$mo', '$kem_p', '$supp', $id)", [$na_brg]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='103'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						// $na_brg = str_replace("'","''",$rowd['NA_BRG']);
						$na_brg = $rowd['NA_BRG'];
						$ket_uk = $rowd['KET_UK'] ?? '';
						$ket_kem = $rowd['KET_KEM'];
						$tg_smp = $rowd['TG_SMP'];
						$item_uni = $rowd['ITEM_UNI'];						
						$supp = $rowd['SUPP'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.brgchd (no_bukti, sub, kdbar, na_brg, ket_uk, ket_kem, tg_smp, kd_brg, item_uni, supp, id) VALUES 
								 ('$no_bukti', '$sub', '$kdbar', ?, ?, '$ket_kem','$tg_smp', concat('$sub','$kdbar'), '$item_uni', '$supp', $id)", [$na_brg, $ket_uk]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='DBF'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						$na_brg = $rowd['NA_BRG'];
						$ket_uk = $rowd['KET_UK'] ?? '';
						$ket_kem = $rowd['KET_KEM'];
						$fungsi = $rowd['FUNGSI'];
						$import = $rowd['IMPORT'];
						$kmp = $rowd['KMP'];
						$kmp1 = $rowd['KMP1'];
						$kmp2 = $rowd['KMP2'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.brgchd (no_bukti, sub, kdbar, na_brg, ket_uk, ket_kem, fungsi, kd_brg, import, kmp, kmp1, kmp2, id) VALUES 
								 ('$no_bukti', '$sub', '$kdbar', ?, ?, '$ket_kem', '$fungsi', concat('$sub','$kdbar'), '$import', '$kmp', '$kmp1', '$kmp2', $id)", [$na_brg, $ket_uk]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='102'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						$na_brg = $rowd['NA_BRG'];
						$ket_uk = $rowd['KET_UK'] ?? '';
						$ket_kem = $rowd['KET_KEM'];
						$kosong = $rowd['KOSONG'];
						$al_kosong = $rowd['AL_KOSONG'];
						$tg_smp = $rowd['TG_SMP'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.brgchd (no_bukti, sub, kdbar, na_brg, ket_uk, ket_kem, kd_brg, kosong, al_kosong, tg_smp, id) VALUES 
								 ('$no_bukti', '$sub', '$kdbar', ?, ?, '$ket_kem', concat('$sub','$kdbar'), '$kosong', '$al_kosong', '$tg_smp',  $id)", [$na_brg, $ket_uk]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='001'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						$na_brg = $rowd['NA_BRG'];
						$ket_uk = $rowd['KET_UK'];
						$ket_kem = $rowd['KET_KEM'];
						$cat_od_g = $rowd['CAT_OD_G'];
						$ket = $rowd['KET'];
						$supp = $rowd['SUPP'];
						$tg_smp = $rowd['TG_SMP'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.brgchd (no_bukti, sub, kdbar, na_brg, ket_uk, ket_kem, cat_od_g, kd_brg, ket, supp, tg_smp, id) VALUES 
								 ('$no_bukti', '$sub', '$kdbar', ?, ?, '$ket_kem', '$cat_od_g', concat('$sub','$kdbar'), '$ket', '$supp', '$tg_smp',  $id)", [$na_brg, $ket_uk]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='BAR'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.supchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.supch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.supch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.supch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}

				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$kodes = $rowd['KODES'];
						$namas = $rowd['NAMAS'] ?? '';
						$s_bar = $rowd['S_BAR'];
						$tgl = $rowd['TG_SMP'];
						try {
							DB::SELECT(
								"INSERT into $dbcenter.supchd (no_bukti, kodes, namas, s_bar, tg_smp, id) VALUES 
								 ('$no_bukti', '$kodes', ?, '$s_bar', '$tgl', $id)", [$namas]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.supch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='BRL'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$dept 	= $rowh['DEPT'];
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						try {
							DB::SELECT(
								"DELETE from $dbcenter.brgchd WHERE no_bukti in (SELECT no_bukti from $dbcenter.brgch WHERE NA_FILE='$nafile')"
							);
							DB::SELECT(
								"DELETE from $dbcenter.brgch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.brgch (NA_FILE, TYPE, NO_BUKTI, TG_SMP) VALUES 
								 ('$nafile', '$dept', '$no_bukti', '$tgl')"
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				foreach ($request->data as $row) {
					foreach ($row['detail'] as $rowd) {
						$no_bukti = $rowd['NO_BUKTI'];
						$id = $rowd['ID'];
						$sub = $rowd['SUB'];
						$kdbar = $rowd['KDBAR'];
						$na_brg = $rowd['NA_BRG'];
						$ket_uk = $rowd['KET_UK'] ?? '';
						$ket_kem = $rowd['KET_KEM'];
						$lph_td = $rowd['LPH_TD'];
						$lph_tm = $rowd['LPH_TM'];
						$lph_gz = $rowd['LPH_GZ'];
						$kdlaku = $rowd['KDLAKU'];
						$klk = $rowd['KLK'];
						$ppn = $rowd['PPN'];
						$supp = $rowd['SUPP'];
						// $hrg_tawar = $rowd['HRG_TAWAR'];
						$barcode = $rowd['BARCODE'];
						$dtb = $rowd['DTB'];
						$dtr_td = $rowd['DTR_TD'];
						$dtr_tm = $rowd['DTR_TM'];
						$dtr_gz = $rowd['DTR_GZ'];
						$tg_smp = $rowd['TG_SMP'];
						$lph_kg = $rowd['LPH_KG'];
						$dtr_kg = $rowd['DTR_KG'];
						$tarik = $rowd['TARIK'];
						$dtb = $rowd['DTB'];
						$panjang = $rowd['PANJANG'];
						$lebar = $rowd['LEBAR'];
						$tinggi = $rowd['TINGGI'];
						try {

							DB::SELECT(
								"INSERT INTO $dbcenter.brgchd 
								(
									no_bukti, sub, kdbar, na_brg, ket_uk, ket_kem, lph_td, kd_brg, lph_tm, lph_gz, kdlaku, tg_smp, id,
									klk, ppn, supp, barcode, dtb, dtr_td, dtr_tm, dtr_gz, lph_kg, dtr_kg, tarik, panjang, lebar, tinggi
								) 
								VALUES 
								(
									'$no_bukti', $sub, '$kdbar', ?, ?, '$ket_kem', '$lph_td', concat('$sub','$kdbar'), $lph_tm, 
									'$lph_gz', '$kdlaku', '$tg_smp', $id,
									'$klk', '$ppn', '$supp', '$barcode', '$dtb', '$dtr_td', '$dtr_tm', '$dtr_gz', '$lph_kg', '$dtr_kg', 
									'$tarik', '$panjang', '$lebar', '$tinggi'
								)", [$na_brg, $ket_uk]
							);

						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan detail '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.brgch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='DSL'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						$kodes = $rowh['KODES'];
						$namas = $rowh['NAMAS'] ?? '';
						$flagsup = $rowh['FLAGSUP'];
						$almt_k = $rowh['ALMT_K'] ?? '';
						$kota = $rowh['KOTA'] ?? '';
						$tlp_k = $rowh['TLP_K'] ?? '';
						$no_fax = $rowh['NO_FAX'];
						$pemilik = $rowh['PEMILIK'] ?? '';
						$almt_r = $rowh['ALMT_R'] ?? '';
						$tlp_r = $rowh['TLP_R'] ?? '';
						$no_rek = $rowh['NO_REK'] ?? '';
						$nama_b = $rowh['NAMA_B'] ?? '';
						$kota_b = $rowh['KOTA_B'] ?? '';
						$an_b = $rowh['AN_B'] ?? '';
						$cabang_b = $rowh['CABANG_B'] ?? '';
						$gol_brg = $rowh['GOL_BRG'] ?? '';
						$cat1 = $rowh['CAT1'] ?? '';
						$cat2 = $rowh['CAT2'] ?? '';
						$cat3 = $rowh['CAT3'] ?? '';
						$jen_brg1 = $rowh['JEN_BRG1'] ?? '';
						$jen_brg2 = $rowh['JEN_BRG2'] ?? '';
						$stm_pembl = $rowh['STM_PEMBL'] ?? '';
						$cara = $rowh['CARA'] ?? '';
						$disc_ps = $rowh['DISC_PS'];
						$bg_pers = $rowh['BG_PERS'] ?? '';
						$stts = $rowh['STTS'] ?? '';
						$golongan = $rowh['GOLONGAN'] ?? '';
						$klb2 = $rowh['KLB2'];
						$fo_klb = $rowh['KLB2'];
						$ff_klb = $rowh['KLB2'];
						$nf_klb = $rowh['KLB2'];
						$st_klb = $rowh['KLB2'];
						$pb_klb = $rowh['KLB2'];
						$dis_p4 = $rowh['DIS_P4'];
						$email_td1 = $rowh['EMAIL_TD1'] ?? '';
						$email_tgz1 = $rowh['EMAIL_TGZ1'] ?? '';
						$email_td2 = $rowh['EMAIL_TD2'] ?? '';
						$email_td3 = $rowh['EMAIL_TD3'] ?? '';
						$email_tgz2 = $rowh['EMAIL_TGZ2'] ?? '';
						$email_tgz3 = $rowh['EMAIL_TGZ3'] ?? '';
						$email_tmm1 = $rowh['EMAIL_TMM1'] ?? '';
						$email_tmm2 = $rowh['EMAIL_TMM2'] ?? '';
						$email_tmm3 = $rowh['EMAIL_TMM3'] ?? '';
						$email_sop1 = $rowh['EMAIL_SOP1'] ?? '';
						$email_sop2 = $rowh['EMAIL_SOP2'] ?? '';
						$email_sop3 = $rowh['EMAIL_SOP3'] ?? '';
						$nm_npwp = $rowh['NM_NPWP'] ?? '';
						$no_npwp = $rowh['NO_NPWP'] ?? '';
						$al_npwp = $rowh['AL_NPWP'] ?? '';
						$order2 = $rowh['ORDER2'] ?? '';
						$jamin_ret = $rowh['JAMIN_RET'] ?? '';
						$va_td = $rowh['VA_TD'] ?? '';
						$va_gz = $rowh['VA_GZ'] ?? '';
						$va_fr = $rowh['VA_FR'] ?? '';
						$va_fc = $rowh['VA_FC'] ?? '';
						$email_dana = $rowh['EMAIL_DANA'] ?? '';
						$by_kr = $rowh['BY_KR'] ?? '';
						$no_wa = $rowh['NO_WA'] ?? '';
						$kode_dc = $rowh['KODE_DC'] ?? '';
						$type = $rowh['TYPE'] ?? '';
						$no_rek_tgz = $rowh['NO_REK_GZ'] ?? '';
						$an_b_tgz = $rowh['AN_B_GZ'] ?? '';
						try {
							DB::SELECT(
								"DELETE from $dbcenter.supch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.supch (NA_FILE, `TYPE`, NO_BUKTI, TG_SMP, KODES, NAMAS, FLAGSUP, ALMT_K, KOTA, TLP_K, NO_FAX, PEMILIK,
																ALMT_R, TLP_R, NO_REK_TD, NAMA_B, KOTA_B, AN_B_TD, CABANG_B, GOL_BRG, CAT1, CAT2, CAT3, JEN_BRG1,
																JEN_BRG2, STM_PEMBL, CARA, DISC_PS, BG_PERS, STTS, GOLONGAN, KLB2, FO_KLB, FF_KLB, NF_KLB, 
																ST_KLB, PB_KLB, DIS_P4, EMAIL_TD1, EMAIL_TGZ1, EMAIL_TD2, EMAIL_TD3, EMAIL_TGZ2, EMAIL_TGZ3, 
																EMAIL_TMM1, EMAIL_TMM2, EMAIL_TMM3, EMAIL_SOP1, EMAIL_SOP2, EMAIL_SOP3, NM_NPWP, NO_NPWP, AL_NPWP, 
																ORDER2, JAMIN_RET, VA_TD, VA_TGZ, VA_FR, VA_FC, EMAIL_DANA, BY_KR, NO_WA, KODE_DC, NO_REK_TGZ, AN_B_TGZ) 
								VALUES ('$nafile', '$type', '$no_bukti', '$tgl', '$kodes', ?, '$flagsup', ?, ?, '$tlp_k', '$no_fax', ?,
																?, '$tlp_r', '$no_rek', '$nama_b', ?, ?, '$cabang_b', '$gol_brg', ?, ?, ?, '$jen_brg1',
																'$jen_brg2', '$stm_pembl', '$cara', '$disc_ps', '$bg_pers', '$stts', '$golongan', '$klb2', '$fo_klb', '$ff_klb', '$nf_klb', 
																'$st_klb', '$pb_klb', '$dis_p4', '$email_td1', '$email_tgz1', '$email_td2', '$email_td3', '$email_tgz2', '$email_tgz3', '$email_tmm1', 
																'$email_tmm2', '$email_tmm3', '$email_sop1', '$email_sop2', '$email_sop3', '$nm_npwp', '$no_npwp', '$al_npwp', '$order2', '$jamin_ret',
																'$va_td', '$va_gz', '$va_fr', '$va_fc', '$email_dana', '$by_kr', '$no_wa', '$kode_dc', '$no_rek_tgz', ?)", [$namas, $almt_k, $kota, $pemilik, $almt_r, $kota_b, $an_b, $cat1, $cat2, $cat3, $an_b_tgz]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.supch WHERE na_file='$nafile'"
				);
			}

			if ($tipe=='DSB'){
				foreach ($request->data as $row) {
					foreach ($row['header'] as $rowh) {
						$no_bukti = $rowh['UR'];
						$tgl = $rowh['TGL'];
						$kodes = $rowh['KODES'];
						$namas = $rowh['NAMAS'] ?? '';
						$flagsup = $rowh['FLAGSUP'];
						$almt_k = $rowh['ALMT_K'] ?? '';
						$kota = $rowh['KOTA'] ?? '';
						$tlp_k = $rowh['TLP_K'] ?? '';
						$no_fax = $rowh['NO_FAX'];
						$pemilik = $rowh['PEMILIK'] ?? '';
						$almt_r = $rowh['ALMT_R'] ?? '';
						$tlp_r = $rowh['TLP_R'] ?? '';
						$no_rek = $rowh['NO_REK'] ?? '';
						$nama_b = $rowh['NAMA_B'] ?? '';
						$kota_b = $rowh['KOTA_B'] ?? '';
						$an_b = $rowh['AN_B'] ?? '';
						$cabang_b = $rowh['CABANG_B'] ?? '';
						$gol_brg = $rowh['GOL_BRG'] ?? '';
						$cat1 = $rowh['CAT1'] ?? '';
						$cat2 = $rowh['CAT2'] ?? '';
						$cat3 = $rowh['CAT3'] ?? '';
						$jen_brg1 = $rowh['JEN_BRG1'] ?? '';
						$jen_brg2 = $rowh['JEN_BRG2'] ?? '';
						$stm_pembl = $rowh['STM_PEMBL'] ?? '';
						$cara = $rowh['CARA'] ?? '';
						$disc_ps = $rowh['DISC_PS'];
						$bg_pers = $rowh['BG_PERS'] ?? '';
						$stts = $rowh['STTS'] ?? '';
						$golongan = $rowh['GOLONGAN'] ?? '';
						$klb2 = $rowh['KLB2'];
						$fo_klb = $rowh['KLB2'];
						$ff_klb = $rowh['KLB2'];
						$nf_klb = $rowh['KLB2'];
						$st_klb = $rowh['KLB2'];
						$pb_klb = $rowh['KLB2'];
						$dis_p4 = $rowh['DIS_P4'];
						$email_td1 = $rowh['EMAIL_TD1'] ?? '';
						$email_tgz1 = $rowh['EMAIL_TGZ1'] ?? '';
						$email_td2 = $rowh['EMAIL_TD2'] ?? '';
						$email_td3 = $rowh['EMAIL_TD3'] ?? '';
						$email_tgz2 = $rowh['EMAIL_TGZ2'] ?? '';
						$email_tgz3 = $rowh['EMAIL_TGZ3'] ?? '';
						$email_tmm1 = $rowh['EMAIL_TMM1'] ?? '';
						$email_tmm2 = $rowh['EMAIL_TMM2'] ?? '';
						$email_tmm3 = $rowh['EMAIL_TMM3'] ?? '';
						$email_sop1 = $rowh['EMAIL_SOP1'] ?? '';
						$email_sop2 = $rowh['EMAIL_SOP2'] ?? '';
						$email_sop3 = $rowh['EMAIL_SOP3'] ?? '';
						$nm_npwp = $rowh['NM_NPWP'] ?? '';
						$no_npwp = $rowh['NO_NPWP'] ?? '';
						$al_npwp = $rowh['AL_NPWP'] ?? '';
						$order2 = $rowh['ORDER2'] ?? '';
						$jamin_ret = $rowh['JAMIN_RET'] ?? '';
						$va_td = $rowh['VA_TD'] ?? '';
						$va_gz = $rowh['VA_GZ'] ?? '';
						$va_fr = $rowh['VA_FR'] ?? '';
						$va_fc = $rowh['VA_FC'] ?? '';
						$email_dana = $rowh['EMAIL_DANA'] ?? '';
						$by_kr = $rowh['BY_KR'] ?? '';
						$no_wa = $rowh['NO_WA'] ?? '';
						$kode_dc = $rowh['KODE_DC'] ?? '';
						$type = $rowh['TYPE'] ?? '';
						$no_rek_tgz = $rowh['NO_REK_GZ'] ?? '';
						$an_b_tgz = $rowh['AN_B_GZ'] ?? '';
						try {
							DB::SELECT(
								"DELETE from $dbcenter.supch WHERE na_file='$nafile'"
							);
							DB::SELECT(
								"INSERT into $dbcenter.supch (NA_FILE, `TYPE`, NO_BUKTI, TG_SMP, KODES, NAMAS, FLAGSUP, ALMT_K, KOTA, TLP_K, NO_FAX, PEMILIK,
																ALMT_R, TLP_R, NO_REK_TD, NAMA_B, KOTA_B, AN_B_TD, CABANG_B, GOL_BRG, CAT1, CAT2, CAT3, JEN_BRG1,
																JEN_BRG2, STM_PEMBL, CARA, DISC_PS, BG_PERS, STTS, GOLONGAN, KLB2, FO_KLB, FF_KLB, NF_KLB, 
																ST_KLB, PB_KLB, DIS_P4, EMAIL_TD1, EMAIL_TGZ1, EMAIL_TD2, EMAIL_TD3, EMAIL_TGZ2, EMAIL_TGZ3, 
																EMAIL_TMM1, EMAIL_TMM2, EMAIL_TMM3, EMAIL_SOP1, EMAIL_SOP2, EMAIL_SOP3, NM_NPWP, NO_NPWP, AL_NPWP, 
																ORDER2, JAMIN_RET, VA_TD, VA_TGZ, VA_FR, VA_FC, EMAIL_DANA, BY_KR, NO_WA, KODE_DC, NO_REK_TGZ, AN_B_TGZ) 
								VALUES ('$nafile', '$type', '$no_bukti', '$tgl', '$kodes', ?, '$flagsup', ?, ?, '$tlp_k', '$no_fax', ?,
																?, '$tlp_r', '$no_rek', '$nama_b', ?, ?, '$cabang_b', '$gol_brg', ?, ?, ?, '$jen_brg1',
																'$jen_brg2', '$stm_pembl', '$cara', '$disc_ps', '$bg_pers', '$stts', '$golongan', '$klb2', '$fo_klb', '$ff_klb', '$nf_klb', 
																'$st_klb', '$pb_klb', '$dis_p4', '$email_td1', '$email_tgz1', '$email_td2', '$email_td3', '$email_tgz2', '$email_tgz3', '$email_tmm1', 
																'$email_tmm2', '$email_tmm3', '$email_sop1', '$email_sop2', '$email_sop3', '$nm_npwp', '$no_npwp', '$al_npwp', '$order2', '$jamin_ret',
																'$va_td', '$va_gz', '$va_fr', '$va_fc', '$email_dana', '$by_kr', '$no_wa', '$kode_dc', '$no_rek_tgz', ?)", [$namas, $almt_k, $kota, $pemilik, $almt_r, $kota_b, $an_b, $cat1, $cat2, $cat3, $an_b_tgz]
							);
						} catch (Exception $e) {
							return response()->json([
								'success' => false,
								'message' => 'Kesalahan head '.$tipe.' ('.$e.')',
							], 401);
						}
					}
				}
				
				$getResult = DB::SELECT(
					"SELECT no_bukti,tg_smp from $dbcenter.supch WHERE na_file='$nafile'"
				);
			}

			$countResult = count($getResult);
			if ($countResult>0)
			{
				return response()->json([
					'success' => true,
					'message' => 'Ambil '.$tipe.' barang berhasil.',
					'data' => $getResult
				], 200);
			}
			else
			{
				return response()->json([
					'success' => false,
					'message' => 'Ambil '.$tipe.' barang gagal.',
				], 401);
			}
        }
    }
	
}