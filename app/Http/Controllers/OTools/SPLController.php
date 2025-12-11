<?php

namespace App\Http\Controllers\OTools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SPLController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'status' => 'required|in:ON,OFF'
        ]);

        $loginUser = Auth::user()->username;
        $password  = $request->password;

        // Ambil user login dari database
        $user = DB::table('tgz.users')
            ->where('username', $loginUser)
            ->first();

        if (!$user) {
            return back()->withErrors("User tidak ditemukan!");
        }

        // Verifikasi password Laravel (bcrypt)
        if (!Hash::check($password, $user->password)) {
            return back()->withErrors("Verifikasi password gagal!");
        }

        // Konversi ON/OFF menjadi angka
        $aktif = ($request->status === "ON") ? 1 : 0;

        // Update sistem SPL
        DB::update("
            UPDATE tgz.sistem
            SET TG_EDIT = NOW(), 
                USRNM = ?, 
                AKTIF = ?
            WHERE FLAG = 'SP' AND TYPE = 'SP'
        ", [
            $loginUser,
            $aktif
        ]);

        return back()->with('success', "Berhasil {$request->status}!");
    }
}