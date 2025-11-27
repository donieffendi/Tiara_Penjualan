@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
            <h1 class="m-0">Lihat Penjualan</h1>
            </div>
            <!-- /.col -->
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{url('/jual')}}">Transaksi Penjualan</a></li>
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
								<div class="col-md-2" align="left">
                                    <label for="NO_BUKTI" class="form-label">No Bukti</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control NO_BUKTI" id="NO_BUKTI" name="NO_BUKTI" placeholder="Masukkan Bukti#" value="{{$header->NO_BUKTI}}" disabled>
                                </div>
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-2" align="right">
									<label style="color:red;font-size:20px">* </label>	
                                    <label for="KODEC" class="form-label">Customer</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control KODEC" id="KODEC" name="KODEC" placeholder="Kode Customer" value="{{$header->KODEC}}" disabled>
                                </div>
                            </div>
							
							<div class="form-group row">
                                <div class="col-md-2" align="left">
                                    <label for="TGL" class="form-label">Tgl</label>
                                </div>
                                <div class="col-md-2">
                                   <input class="form-control date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}" disabled>
                                </div>
                                <div class="col-md-4">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control NAMAC" id="NAMAC" name="NAMAC" placeholder="Nama Customer" value="{{$header->NAMAC}}" disabled>
                                </div>
                            </div>
							<div class="form-group row">
                                <div class="col-md-2" align="left">
									<label style="color:red;font-size:20px">* </label>	
                                    <label for="TRUCK" class="form-label">Truck</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control TRUCK" id="TRUCK" name="TRUCK" placeholder="Masukkan Truck" value="{{$header->TRUCK}}" disabled>
                                </div>
                                <div class="col-md-1" align="right">
									<label style="color:red;font-size:20px">* </label>	
                                    <label for="SOPIR" class="form-label">Sopir</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control SOPIR" id="SOPIR" name="SOPIR" placeholder="Sopir" value="{{$header->SOPIR}}" disabled>
                                </div>
                                <div class="col-md-1">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control ALAMAT" id="ALAMAT" name="ALAMAT" placeholder="Alamat Customer" value="{{$header->ALAMAT}}" disabled>
                                </div>
                            </div>
                            
							<div class="form-group row">
                                <div class="col-md-2" align="left">
                                    <label class="form-label">Via</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control VIA" id="VIA" name="VIA" placeholder="Via" value="{{$header->VIA}}" disabled>
                                </div>
                                <div class="col-md-4">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control KOTA" id="KOTA" name="KOTA" placeholder="Kota Customer" value="{{$header->KOTA}}" disabled>
                                </div>
                            </div>

							<div class="form-group row">
                                <div class="col-md-2" align="left">
                                    <label for="NOTES" class="form-label">Notes</label>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control NOTES" id="NOTES" name="NOTES" placeholder="Notes" value="{{$header->NOTES}}" disabled>
                                </div>
                            </div>
                           
							<table id="datatable" class="table table-striped table-border">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">No.</th>
                                        <th style="text-align: center;">
									       <label style="color:red;font-size:20px">* </label>									
                                           <label for="KD_BRG" class="form-label">SO#</label></th>
                                        <th style="text-align: center;">Kode</th>
                                        <th style="text-align: center;">Uraian</th>
                                        <th style="text-align: center;">Satuan</th>
                                        <th style="text-align: center;">Qty</th>
										<th style="text-align: center;">Harga</th>
										<th style="text-align: center;">Total</th>
										<th style="text-align: center;">Ket</th>
										<th style="text-align: center;"></th>
                                    </tr>
									
                                </thead>
                                <tbody>
								<?php $no=0 ?>
								@foreach ($detail as $suratsd)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="NO_ID[]" id="NO_ID{{$no}}" type="text" value="{{$suratsd->NO_ID}}" 
                                            class="form-control NO_ID" onkeypress="return tabE(this,event)" disabled>
                                            <input name="REC[]" id="REC{{$no}}" type="text" value="1" class="form-control REC" onkeypress="return tabE(this,event)" value="{{$suratsd->REC}}" disabled>
											<input name="NO_TERIMA[]" id="NO_TERIMA{{$no}}" type="text" class="form-control NO_TERIMA" value="{{$suratsd->NO_TERIMA}}" disabled hidden>
											<input name="MERK[]" id="MERK{{$no}}" type="text" class="form-control MERK" value="{{$suratsd->MERK}}" disabled hidden>
											<input name="NO_SERI[]" id="NO_SERI{{$no}}" type="text" class="form-control NO_SERI" value="{{$suratsd->NO_SERI}}" disabled hidden>
											<input name="TYP[]" id="TYP{{$no}}" type="text" class="form-control TYP" disabled hidden>
											<input name="ID_SOD[]" id="ID_SOD{{$no}}" type="text" class="form-control ID_SOD" value="{{$suratsd->ID_SOD}}" disabled hidden>
                                        </td>
                                        <td>
                                            <input name="NO_SO[]" id="NO_SO{{$no}}" type="text" class="form-control NO_SO" placeholder="No SO" value="{{$suratsd->NO_SO}}" disabled required>
                                        </td>
                                        <td>
                                            <input name="KD_BRG[]" id="KD_BRG{{$no}}" type="text" class="form-control KD_BRG" placeholder="Barang#" value="{{$suratsd->KD_BRG}}" disabled required>
                                        </td>
                                        <td>
                                            <input name="NA_BRG[]" id="NA_BRG{{$no}}" type="text" class="form-control NA_BRG" placeholder="Nama" value="{{$suratsd->NA_BRG}}" disabled required>
                                        </td>
                                        <td>
                                            <input name="SATUAN[]" id="SATUAN{{$no}}" type="text" class="form-control SATUAN" placeholder="Satuan" value="{{$suratsd->SATUAN}}" disabled required>
                                        </td>
										<td>
											<input name="QTY[]" onkeyup="hitung()" id="QTY{{$no}}" type="text" style="text-align: right"  class="form-control QTY text-primary" value="{{$suratsd->QTY}}" disabled>
										</td>                         
										<td>
											<input name="HARGA[]" onkeyup="hitung()" id="HARGA{{$no}}" type="text" style="text-align: right"  class="form-control HARGA text-primary" value="{{$suratsd->HARGA}}" disabled>
										</td>
										<td>
											<input name="TOTAL[]" onkeyup="hitung()" id="TOTAL{{$no}}" type="text" style="text-align: right"  class="form-control TOTAL text-primary" value="{{$suratsd->TOTAL}}" disabled>
										</td>
										<td>
                                            <input name="KET[]" id="KET{{$no}}" type="text" class="form-control KET" placeholder="Ket" value="{{$suratsd->KET}}" disabled required>
                                        </td>
										
                                    </tr>
								<?php $no++; ?>
								@endforeach
                                </tbody>
								<tfoot>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><input class="form-control TQTY  text-primary font-weight-bold" style="text-align: right"  id="TQTY" name="TQTY" value="{{$header->TOTAL_QTY}}" disabled></td>
                                    <td></td>
                                    <td><input class="form-control TTOTAL  text-primary font-weight-bold" style="text-align: right"  id="TTOTAL" name="TTOTAL" value="{{$header->TOTAL}}" disabled></td>
                                    <td></td>
                                </tfoot>
                            </table>
                            
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

