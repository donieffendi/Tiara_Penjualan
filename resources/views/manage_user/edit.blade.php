@extends('layouts.plain')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Edit Wewenang User {{$username}}</h1>
          </div>
          <!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{url('/user/manage')}}">Kelola User</a></li>
                <li class="breadcrumb-item active">Edit {{$username}}</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <!-- Validation Errors -->
                <x-auth-validation-errors class="mb-4" :errors="$errors" />

                <form method="POST" action="{{url('/user/update/'.$id)}}">
                    @csrf

                    <!-- Username -->
                    <div class="form-group">
                        <x-label for="username" :value="__('Username')" />

                        <x-input id="username" type="text" name="username" :value="old('username')" value="{{$username}}" readonly autofocus />
                    </div>

                    <!-- Name -->
                    <div class="form-group">
                        <x-label for="name" :value="__('Name')" />

                        <x-input id="name" type="text" name="name" :value="old('name')" value="{{$name}}" disabled />
                    </div>

                    <!-- Email Address -->
                    <div class="form-group">
                        <x-label for="email" :value="__('Email')" />

                        <x-input id="email" type="email" name="email" :value="old('email')" value="{{$email}}" disabled />
                    </div>

                    <!-- Divisi -->
                    <div class="form-group">
                        <x-label for="divisi" :value="__('Divisi')" />
                        <select name="divisi" id="divisi" class="btn-group btn-block" style="padding: 10px" required>
                            <option value="accounting" <?php if($divisi=='accounting') echo 'selected="selected"' ?>>Accounting</option>
                            <option value="assistant" <?php if($divisi=='assistant') echo 'selected="selected"' ?>>Assistant</option>
                            <option value="penjualan" <?php if($divisi=='penjualan') echo 'selected="selected"' ?>>Penjualan</option>
                            <option value="pembelian" <?php if($divisi=='pembelian') echo 'selected="selected"' ?>>Pembelian</option>
                            <option value="owner" <?php if($divisi=='owner') echo 'selected="selected"' ?>>Owner</option>
                            <option value="programmer" <?php if($divisi=='programmer') echo 'selected="selected"' ?>>Programmer</option>
                        </select>
                    </div>

                    <!-- Privilege -->
                    <div class="form-group">
                        <x-label for="privilege" :value="__('Privilege')" />
                        <select name="privilege" id="privilege" class="btn-group btn-block" style="padding: 10px" required>
                            <option value="superadmin" <?php if($privilege=='superadmin') echo 'selected="selected"' ?>>Superadmin</option>
                            <option value="user" <?php if($privilege=='user') echo 'selected="selected"' ?>>User</option>
                        </select>
                    </div>

                    
                    <!-- Cabang -->
                    <div class="form-group">
                        <x-label for="CBG" :value="__('Cabang')" />
                        <select name="CBG" id="CBG" class="btn-group btn-block" style="padding: 10px" required>
                            <option value="Z" <?php if($CBG=='Z') echo 'selected="selected"' ?>>Gatzu</option>
                            <option value="M" <?php if($CBG=='M') echo 'selected="selected"' ?>>Monang Maning</option>
                            <option value="S" <?php if($CBG=='S') echo 'selected="selected"' ?>>Soputan</option>
                            <option value="F" <?php if($CBG=='F') echo 'selected="selected"' ?>>Freshindo</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <div class="d-flex justify-content-end align-items-baseline">
                            {{-- <a class="text-muted mr-3 text-decoration-none" href="{{ route('login') }}">
                                {{ __('Already registered?') }}
                            </a> --}}

                            <x-button>
                                {{ __('Edit User') }}
                            </x-button>
                        </div>
                    </div>
                </form>
              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
@endsection
