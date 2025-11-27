@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
            <h1 class="m-0">Data User {{$username}}</h1>
            </div>
            <!-- /.col -->
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{url('/user/manage')}}">Kelola User</a></li>
                <li class="breadcrumb-item active">{{$username}}</li>
            </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <div class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                    <!-- Validation Errors -->
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />

                    <form method="POST" action="">
                        @csrf

                        <!-- Username -->
                        <div class="form-group">
                            <x-label for="username" :value="__('Username')" />

                            <x-input id="username" type="text" name="username" :value="old('username')" value="{{$username}}" disabled autofocus />
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
                            <select name="divisi" id="divisi" class="btn-group btn-block" style="padding: 10px" disabled>
                                <option value="accounting" <?php if($divisi=='accounting') echo 'selected="selected"' ?>>Accounting</option>
                                <option value="humansupport" <?php if($divisi=='humansupport') echo 'selected="selected"' ?>>Human Support</option>
                                <option value="operational" <?php if($divisi=='operational') echo 'selected="selected"' ?>>Operational</option>
                                <option value="programmer" <?php if($divisi=='programmer') echo 'selected="selected"' ?>>Programmer</option>
                                <option value="vehiclesupport" <?php if($divisi=='vehiclesupport') echo 'selected="selected"' ?>>Vehicle Support</option>
                            </select>
                        </div>

                        <!-- Privilege -->
                        <div class="form-group">
                            <x-label for="privilege" :value="__('Privilege')" />
                            <select name="privilege" id="privilege" class="btn-group btn-block" style="padding: 10px" disabled>
                                <option value="superadmin" <?php if($privilege=='superadmin') echo 'selected="selected"' ?>>Superadmin</option>
                                <option value="user" <?php if($privilege=='user') echo 'selected="selected"' ?>>User</option>
                            </select>
                        </div>

                        <div class="mb-0">
                            <div class="d-flex justify-content-end align-items-baseline">
                                {{-- <a class="text-muted mr-3 text-decoration-none" href="{{ route('login') }}">
                                    {{ __('Already registered?') }}
                                </a> --}}

                                {{-- <x-button>
                                    {{ __('Tambah User') }}
                                </x-button> --}}
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
</div>
@endsection