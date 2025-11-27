<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Auth;
use DataTables;
use DB;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile');
    }

    public function update(Request $request, User $user)
    {
        $user = User::find(Auth::user()->id);
        $password = $request->password;
        $user->password = Hash::make($password);
        $user->save();
        return redirect('/profile')->with('status', 'Password selesai diubah..');
    }

}