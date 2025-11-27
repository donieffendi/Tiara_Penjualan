@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
            <h1 class="m-0">Lihat Pembelian</h1>
            </div>
            <!-- /.col -->
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{url('/beli')}}">Transaksi Pembelian</a></li>
                <li class="breadcrumb-item active">{{$header->NO_BUKTI}}</li>
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
                                    <label for="NO_BUKTI" class="form-label">Bukti#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="no_bukti"
                                    placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" disabled>
                                </div>
							
							</div>
							
                            <div class="form-group row">        
                                <div class="col-md-2">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control TGL" id="TGL "name="TGL"
                                    placeholder="Masukkan tgl" value="{{date('d-m-Y',strtotime($header->TGL))}}" disabled>
                                </div>
                            </div>
       					
                            <div class="form-group row">
                                <div class="col-md-2">
                                    <label for="NO_PO" class="form-label">PO#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_PO" id="NO_PO" name="NO_PO" placeholder="Masukkan PO#" value="{{$header->NO_PO}}"  disabled >
                                </div>
							</div>
							
                            <div class="form-group row">
                                <div class="col-md-2">
                                    <label for="KODES" class="form-label">Suplier#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control KODES" id="KODES" name="KODES" placeholder="Masukkan Suplier" value="{{$header->KODES}}"  disabled >
                                </div>
							</div>
							
                            <div class="form-group row">        
                                <div class="col-md-2">
                                    <label for="NAMAS" class="form-label">-</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NAMAS" id="NAMAS "name="NAMAS" placeholder="-" value="{{$header->NAMAS}}" disabled>
                                </div>
							</div>
							
                            <div class="form-group row">   
                                <div class="col-md-2">
                                    <label for="KD_BRG" class="form-label">Barang#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control KD_BRG" id="KD_BRG" name="KD_BRG" placeholder="Masukkan Barang" value="{{$header->KD_BRG}}" disabled>
                                </div>
							</div>
							
							
                            <div class="form-group row">        
                                <div class="col-md-2">
                                    <label for="NA_BRG" class="form-label">-</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NA_BRG" id="NA_BRG "name="NA_BRG" placeholder="-" value="{{$header->NA_BRG}}" disabled>
                                </div>
							</div>	

							<div class="form-group row">
                                <div class="col-md-2">
                                    <label for="KG" class="form-label">Kg#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control KG" id="KG" name="KG"
                                    placeholder="Masukkan kg" value="{{ number_format($header->KG, 0, '.', ',') }}" style="text-align: right" disabled>
                                </div>
        
                            </div>	
		
							<div class="form-group row">
                                <div class="col-md-2">
                                    <label for="HARGA" class="form-label">Harga#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control HARGA" id="HARGA" name="HARGA"
                                    placeholder="Masukkan harga" value="{{ number_format($header->HARGA, 5, '.', ',') }}" style="text-align: right" disabled>
                                </div>
        
                            </div>			
							<div class="form-group row">
                                <div class="col-md-2">
                                    <label for="TOTAL" class="form-label">Total#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control TOTAL" id="TOTAL" name="TOTAL"
                                    placeholder="Masukkan total " value="{{ number_format($header->TOTAL, 2, '.', ',') }}" style="text-align: right" disabled>
                                </div>
        
                            </div>			

							<div class="form-group row">        
                                <div class="col-md-2">
                                    <label for="NOTES" class="form-label">Notes</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NOTES" id="NOTES "name="NOTES" placeholder="-" value="{{$header->NOTES}}" disabled>
                                </div>
							</div>	
							
							<div class="form-group row">
                                <div class="col-md-2">
                                    <label for="RPRATE" class="form-label">RpRate#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control RPRATE" id="RPRATE" name="RPRATE"
                                    placeholder="Masukkan Rprate" value="{{ number_format($header->RPRATE, 0, '.', ',') }}" style="text-align: right" disabled>
                                </div>
        
                            </div>			
							
							<div class="form-group row">
                                <div class="col-md-2">
                                    <label for="RPTOTAL" class="form-label">RpTotal#</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control RPTOTAL" id="RPTOTAL" name="RPTOTAL"
                                    placeholder="Masukkan RpTotal" value="{{ number_format($header->RPTOTAL, 0, '.', ',') }}" style="text-align: right" disabled>
                                </div>
        
                            </div>	
		
                            
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

