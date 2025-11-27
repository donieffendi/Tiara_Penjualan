@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
            <h1 class="m-0">Lihat Journal Kas Keluar</h1>
            </div>
            <!-- /.col -->
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{url('/kask')}}">Journal Kas Keluar</a></li>
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
                                    <label for="NO_BUKTI" class="form-label">Nomor Bukti</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI"
                                    placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" readonly>
                                </div>
						
						<!-- <div class="col-md-2">
									<label for="AKT" class="form-label">Aktif</label>
								</div> -->
									
								<div class="col-md-6">
									<input type="checkbox" class="form-check-input" id="POSTED" name="POSTED" value="1">
									<label for="POSTED">Posted</label>
								</div>  								
							</div>
        
		
							<div class="form-group row">
                                <div class="col-md-2">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
								<div class="col-md-2">
 
								  <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}" readonly >
								
								</div>
                            </div>       
		
                            <div class="form-group row">
                                <div class="col-md-2">
                                    <label for="BACNO" class="form-label">Kas</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control BACNO" id="BACNO" name="BACNO"
                                    placeholder="Masukkan Account" value="{{$header->BACNO}}" readonly>
                                </div>
							</div>	
        
							<div class="form-group row">
                                <div class="col-md-2">
                                    <label for="BNAMA" class="form-label">-</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control BNAMA" id="BNAMA "name="BNAMA"
                                    placeholder="Masukkan -" value="{{$header->BNAMA}}" readonly>
                                </div>
                            </div>
        

        
                            <hr style="margin-top: 30px; margin-buttom: 30px">
                            
                            <table id="datatable" class="table table-striped table-border">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">No.</th>
                                        <th style="text-align: center;">Account#</th>
                                        <th style="text-align: center;">-</th>
                                        <th style="text-align: center;">Uraian</th>
                                        <th style="text-align: center;">Jumlah</th>
                                    </tr>
                                </thead>
        
                                <tbody>
								<?php $no=0 ?>
								@foreach ($detail as $kasd)
                                    <tr>
                                         <td>
                                            <input type="hidden" name="NO_ID[]" id="NO_ID0" type="text" value="{{$kasd->NO_ID}}" 
                                            class="form-control NO_ID" onkeypress="return tabE(this,event)" readonly>
                                            
                                            <input name="REC[]" id="REC0" type="text" value="{{$kasd->REC}}" 
                                            class="form-control REC" onkeypress="return tabE(this,event)" readonly>
                                        </td>
                                        <td>
                                            <input name="ACNO[]" id="ACNO0" type="text" value="{{$kasd->ACNO}}"
                                            class="form-control ACNO" readonly>
                                        </td>
										
                                        <td>
                                            <input name="NACNO[]" id="NACNO0" type="text" value="{{$kasd->NACNO}}"
                                            class="form-control NACNO" readonly>
                                        </td>
										
										<td>
                                            <input name="URAIAN[]" id="URAIAN0" type="text" value="{{$kasd->URAIAN}}"
                                            class="form-control URAIAN" readonly>
                                        </td>
										<td>
                                            <input name="JUMLAH[]" onclick="select()" onkeyup="hitung()" id="JUMLAH0" type="text" style="text-align: right" value="{{$kasd->JUMLAH}}"
                                            class="form-control JUMLAH" readonly>
                                        </td>
                                        
                                        
                                    </tr>
								@endforeach
									
									
                                </tbody>
								<tfoot>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><input class="form-control TJUMLAH  text-primary font-weight-bold" style="text-align: right"  id="TJUMLAH" name="TJUMLAH" value="{{$header->JUMLAH}}" readonly></td>
                                    <td></td>
                                </tfoot>
								
                            </table>
        				
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
@endsection

@section('footer-scripts')
<script>
    var target;
	var idrow = 1;
    
	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	
	$(document).ready(function() {

		$("#TJUMLAH").autoNumeric('init', {aSign: '<?php echo ''; ?>',vMin: '-999999999.99'});

		jumlahdata = 100;
		for (i = 0; i <= jumlahdata; i++) {
			$("#JUMLAH" + i.toString()).autoNumeric('init', {aSign: '<?php echo ''; ?>', vMin: '-999999999.99'});
		}
		
		$(".date").datepicker({
			'dateFormat': 'dd-mm-yy',
		})
			
///////////////////////////////////////////////////////////////////////


	});

    
</script>
@endsection

