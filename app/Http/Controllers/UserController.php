<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Auth;
use DataTables;
use DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $getUser = User::join('role_user', 'role_user.user_id' , '=' , 'users.id')
        //                 ->join('');

        // return $getUser;

        return view('manage_user.index');
    }

    public function getUser()
    {
        $user = User::query();

        return Datatables::of($user)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $a = 'Apakah anda yakin?';
                $actionBtn =
                    '
                    <div class="dropdown show" style="text-align: center">
                        <a class="btn btn-secondary dropdown-toggle btn-sm" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </a>
                        
                
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item" href="show/' . $row->id . '">
                                <i class="fas fa-eye"></i>
                                    Lihat
                                </a>

                                <a class="dropdown-item" href="edit/' . $row->id . '">
                                <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <hr></hr>
                                <a class="dropdown-item btn btn-danger" onclick="return confirm(&quot; Apakah anda yakin ingin hapus? &quot;)" href="delete/' . $row->id . '">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                    Delete
                                </a>
                        </div>
                    </div>
                    ';

                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Auth::user()->attachRole('superadministrator');
        // Auth::user()->detachRole('superadministrator');

        return view('manage_user.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:3',
        ]);

        $user = User::create([
            'username'  => $request->username,
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'divisi'    => $request->divisi,
            'privilege' => $request->privilege,
            'CBG'       => $request->CBG
        ]);

        $user->attachRole($request->divisi);
        $user->attachRole($request->privilege);
        $user->attachRole($request->CBG);

        return redirect('/user/manage')->with('status', 'User berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $userData = User::where('id', $user->id)->first();

        return view('manage_user.show', $userData);
        // return $userData;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $userData = User::where('id', $user->id)->first();

        return view('manage_user.edit', $userData);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {

        if (!$user->hasRole($request->divisi)) {
            $user->detachRole($user->divisi);
            $user->attachRole($request->divisi);
        }

        if (!$user->hasRole($request->privilege)) {
            $user->detachRole($user->privilege);
            $user->attachRole($request->privilege);
        }

        if (!$user->hasRole($request->CBG)) {
            $user->detachRole($user->CBG);
            $user->attachRole($request->CBG);
        }


        $user->update(
            [
                "divisi"    => $request->divisi,
                "privilege" => $request->privilege,
                "CBG"       => $request->CBG
            ]
        );
        return redirect('/user/manage')->with('status', 'User berhasil diedit!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $deleteUser = User::find($user->id);

        $deleteUser->delete();

        return redirect('/user/manage')->with('status', 'User berhasil dihapus!');
    }
}
