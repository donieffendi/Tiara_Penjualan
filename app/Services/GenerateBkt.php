<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateBkt
{
   public static function get(string $flag, string $tabel = ""): string
   {
      $periode  = Carbon::now()->format('m/Y');
      $nomer    = DB::table('notrans')->where('kode', $flag)->where('periode', $periode)->value('nomer') ?? 0;
      $tahun    = Carbon::now()->format('y');
      $bulan    = Carbon::now()->format('m');
      $no       = str_pad(++$nomer, 4, '0', STR_PAD_LEFT);
      $no_bukti = "$flag$tahun$bulan-$no";

      if (!empty($tabel)){
         if (DB::table($tabel)->where('no_bukti', $no_bukti)->exists()) {
               DB::table('notrans')->where('kode', $flag)->where('periode', $periode)->increment('nomer');
               return static::get($flag, $tabel);
         }
      };

      DB::table('notrans')->updateOrInsert(
         [
            'kode'    => $flag,
            'periode' => $periode
         ],
         [
            'nomer' => $nomer,
            'updated_at' => Carbon::now(),
         ]
      );

      return $no_bukti;
   }
}
