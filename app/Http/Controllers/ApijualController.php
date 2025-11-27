<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ApijualController extends Controller
{
	
    public function sinkron_jual(Request $request)
    {
		$stat = strtoupper($request->status); 	//NEW, EDIT, DELETE
		$tipe = strtoupper($request->type);		//JUAL
		// $dbcenter = "crm_dev";
		
		$nobukti = '';
		$tgl = '';
		$per = '';
		$kodec = '';
		$namac = '';
		$total = '';
		$poin = '';

		if(empty($stat) || empty($tipe)) {
				return response()->json([
						'success' => false,
						'message' => 'Ada isian yang kosong',
						'data'    => $request->all()
				],401);
				
		}else {
			$buktiakhir='';

			foreach ($request->data as $row) {
				$nobukti = $row['nobukti'];
				$tgl = date('Y-m-d', strtotime($row['tgl']));
				$per = $row['per'];
				$kodec = $row['kodec'];
				$namac = $row['namac'];
				$total = $row['total'];
				$poin = $row['poin'];
				
				try {
					DB::select(
						"INSERT into jual (no_bukti,tgl,per,kodec,namac,total,poin) VALUES ('$nobukti','$tgl','$per','$kodec','$namac',$total,$poin)"
					);
				} catch (Exception $e) {
					return response()->json([
						'success' => false,
						'message' => 'Kesalahan '.$tipe.' ('.$e.')',
					], 401);
				}

				$buktiakhir = $nobukti;
			}
			
			if ($buktiakhir!=''){
				return response()->json([
					'success' => true,
					'message' => $buktiakhir.' berhasil.',
					'data'		=> $buktiakhir
				], 200);
			}else{
				return response()->json([
					'success' => false,
					'message' => $buktiakhir.' gagal.',
					'data'		=> $buktiakhir
				], 401);
			}

			// $getResult = DB::connection('tdtrial')->select(
			// 	"SELECT na_brgh,namas from $dbcenter.brgh WHERE kd_brgh='$kdbrgh'"
			// );
			
			// $countResult = count($getResult);
			// if ($countResult>0)
			// {
			// 	return response()->json([
			// 		'success' => true,
			// 		'message' => $stat.' '.$tipe.' data hadiah berhasil.',
			// 		'data' => $getResult
			// 	], 200);
			// }
			// else
			// {
			// 	return response()->json([
			// 		'success' => false,
			// 		'message' => $stat.' '.$tipe.' data hadiah gagal.',
			// 	], 401);
			// }
    }
  }
}
