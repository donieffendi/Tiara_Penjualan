@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
            <h1 class="m-0">Lihat Data Account</h1>
            </div>
            <!-- /.col -->
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{url('account')}}">Master Account</a></li>
                <li class="breadcrumb-item active">{{$ACNO}}</li>
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
                    <form action="store" method="POST">
                        @csrf
                        {{-- <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" href="#data" data-toggle="tab">Data</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#dokumen" data-toggle="tab">Dokumen</a>
                            </li>
                        </ul> --}}
        
                        <div class="tab-content mt-3">
        
                            <div class="form-group row">
                                <div class="col-md-2">
                                    <label for="ACNO" class="form-label">Account#</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control ACNO" id="ACNO" name="ACNO"
                                    placeholder="Masukkan Account" value="{{$ACNO}}" readonly>
                                </div>      
        
                                <div class="col-md-2">
                                    <label for="BNK" class="form-label">Jenis</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control BNK" id="BNK" name="BNK"
                                    placeholder="Masukkan Type" value="{{$BNK}}" readonly>
                                </div>     
                            </div>
							
							<div class="form-group row">
									<div class="col-md-2">
										<label for="NAMA" class="form-label">Nama</label>
									</div>
									<div class="col-md-4">
										<input type="text" class="form-control NAMA" id="NAMA" name="NAMA" placeholder="Masukkan Nama" value="{{$NAMA}}" readonly>
									</div>                             
								</div>
								
							<div class="form-group row">
									<div class="col-md-2">
										<label for="GRUP" class="form-label">Group</label>
									</div>
									<div class="col-md-4">
										<input type="text" class="form-control GRUP" id="GRUP" name="GRUP" placeholder="Masukkan Group" value="{{$GRUP}}" readonly>
									</div>                             
								</div>
        
							<div class="form-group row">
									<div class="col-md-2">
										<label for="POS2" class="form-label">Type</label>
									</div>
									<div class="col-md-4">
										<input type="text" class="form-control POS2" id="POS2" name="POS2" placeholder="Masukkan Type" value="{{$POS2}}" readonly>
									</div>                             
								</div>
        
                            <hr style="margin-top: 30px; margin-buttom: 30px">
                            
                        </div>
        
                        {{-- <div class="mt-3">
                            <button type="submit"  class="btn btn-success"><i class="fa fa-save"></i> Save</button>										
                            <a type="button" href="javascript:javascript:history.go(-1)" class="btn btn-danger">Cancel</a>
                        </div> --}}
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
@endsection

@section('footer-scripts')
<script>
    var target;
	var idrow = 1;

    $(document).ready(function () {
        $('body').on('click', '.btn-delete', function() {
			var val = $(this).parents("tr").remove();
			idrow--;
			nomor();
		});
    });

    function nomor() {

	}

    function tambah() {

     }
</script>
@endsection

