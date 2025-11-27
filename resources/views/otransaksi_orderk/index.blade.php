@extends('layouts.plain')
@section('styles')
<!-- <link rel="stylesheet" href="{{url('http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}"> -->
<link rel="stylesheet" href="{{asset('foxie_js_css/jquery.dataTables.min.css')}}" />

@endsection

<style>
    .bg-card {
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        background-image: url("img/kedele01.jpg");
		height : 300px;
    }

    .card-header {
        background-color: rgb(244 221 179 / 67%) !important;
    }

     .logo2 {
        max-width: 25%;
    }

    .logo2 img {
        width: 100%;
        height: 100%;
        background-color: rgb(244 221 179 / 67%);
        border-radius: 50%;
        box-shadow: 0px 0px 3px #5f5f5f,
            0px 0px 0px 5px #ecf0f3,
            8px 8px 15px #a7aaa7,
            -8px -8px 15px #f4ddb3;
    } 

    /* menghilangkan padding */
    .content-header {
        padding: 0 !important;
    }
</style>

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
		<!--
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1> 
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">Home</li>
                        </ol>
                    </div>
                </div>
            </div>
		-->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card bg-card">
						<!--
                            <h5 class="card-title p-3 mb-0">Anda login sebagai : <b> {{ Auth::user()->name }}</b></h5>
                            <div class="card-header">
                                Periode: {{ session()->get('periode')['bulan'] }}/{{ session()->get('periode')['tahun'] }} -
                                <b>Divisi {{ Auth::user()->divisi }}</b>
                                <br>
                                Wewenang Anda : {{ Auth::user()->privilege }}
                            </div>
							
                            <div class="card-body">
        						<div class="logo2 mx-auto">
                                    <img class="img-fluid" src="img/logo.png">
                                </div>
                            </div>
						  
						  
						-->
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

