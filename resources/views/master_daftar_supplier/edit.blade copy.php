@extends('layouts.plain')

@section('content')

<style>
    .card {

    }

    .form-control:focus {
        background-color: #E0FFFF !important;
    }

	/* perubahan tab warna di form edit  */
	.nav-item .nav-link.active {
		background-color: red !important; /* Use !important to ensure it overrides */
		color: white !important;
        /* border-radius: 10; */
	}

    /* menghilangkan padding */
    .content-header {
        padding: 0 !important;
    }


</style>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- /.content-header -->

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">


                            <form action="{{($tipx=='new')? url('/sup/store/') : url('/sup/update/'.$header->NO_ID ) }}" method="POST" name ="entri" id="entri" >

                                 @csrf

                                <div class="tab-content mt-3">
                                    <style>
                                        /* Ensure specificity with class targeting */
                                        .form-group.special-input-label {
                                            position: relative;
                                            margin-left: 5px ;
                                        }
                                
                                        /* Ensure only bottom border for input */
                                        .form-group.special-input-label input {
                                            width: 100%;
                                            padding: 10px 0;
                                            border: none !important;
                                            border-bottom: 2px solid #ccc !important;
                                            outline: none !important;
                                            font-size: 16px !important;
                                            background: transparent !important; /* Remove any background color */
                                        }
                                
                                        /* Bottom border color change on focus */
                                        .form-group.special-input-label input:focus {
                                            border-bottom: 2px solid #007BFF !important; /* Change color on focus */
                                        }
                                
                                        /* Style the label with a higher specificity */
                                        .form-group.special-input-label label {
                                            position: absolute;
                                            top: 12px;
                                            color: #888 !important;
                                            font-size: 16px !important;
                                            transition: 0.3s ease all;
                                            pointer-events: none;
                                        }
                                
                                        /* Move label above input when focused or has content */
                                        .form-group.special-input-label input:focus + label,
                                        .form-group.special-input-label input:not(:placeholder-shown) + label {
                                            top: -10px !important;
                                            font-size: 12px !important;
                                            color: #007BFF !important;
                                        }
                                    </style>

                                    <div class="form-group row">

                                            <input name="tipx" class="form-control tipx" id="tipx" value="{{$tipx}}" hidden>

                                            <input type="text" class="form-control NO_ID" id="NO_ID" name="NO_ID"
                                            placeholder="Masukkan NO_ID" value="{{$header->NO_ID ?? ''}}" hidden readonly>

                                        <!-- code text box baru -->
                                        <div class="col-md-3 form-group row special-input-label">
                                            <input type="text" class="KODES" id="KODES" name="KODES" 
                                                value="{{$header->KODES}}" placeholder=" ">
                                            <label for="KODES">Kode</label>
                                        </div>
                                        <!-- tutupannya -->

                                        <div class="col-md-3"></div>

                                        <div class="col-md-1">
                                            <label for="TYPE" class="form-label">Type</label>
                                        </div>
                                        <div class="col-md-1">
                                            {{-- <input type="text" class="form-control TYPE" id="TYPE" name="TYPE" value="{{$header->TYPE}}" placeholder="Masukkan Bank" > --}}
                                            <select id="TYPE" class="form-control"  name="TYPE">
                                                <option value="A" {{ ( $header->TYPE== 'A') ? 'selected' : '' }}>A</option>
                                                <option value="N" {{ ( $header->TYPE== 'N') ? 'selected' : '' }}>N</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- <div class="form-group row">
                                        <!-- code text box baru -->
                                        <div class="col-md-3 form-group row special-input-label">
                                            <input type="text" class="SUP_BARU" id="SUP_BARU" name="SUP_BARU" 
                                                value="{{$header->SUP_BARU}}" placeholder=" ">
                                            <label for="SUP_BARU">Sup Baru</label>
                                        </div>
                                        <!-- tutupannya -->
                                    </div> --}}

                                    <div class="form-group row">
                                        <!-- code text box baru -->
                                        <div class="col-md-5 form-group row special-input-label">
                                            <input type="text" class="NAMAS" id="NAMAS" name="NAMAS" 
                                                value="{{$header->NAMAS}}" placeholder=" ">
                                            <label for="NAMAS">Nama</label>
                                        </div>
                                        <!-- tutupannya -->

                                        <div class="col-md-1"></div>

                                        <!-- code text box baru -->
                                        <div class="col-md-2 form-group row special-input-label">
                                            <input type="text" class="GOLONGAN" id="GOLONGAN" name="GOLONGAN" 
                                                value="{{$header->GOLONGAN}}" placeholder=" ">
                                            <label for="GOLONGAN">Golongan</label>
                                        </div>
                                        <!-- tutupannya -->
                                    </div>
                                </div>

                                <!-- ------------------------------------------------------------------------------- -->
                                
                                <div class="tab-content">
                                    <ul class="nav nav-tabs">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#page1">Page 1</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#page2">Page 2</a>
                                        </li>
                                    </ul>
                                    <!-- ---------------------------------------------------------------------------- -->

                                    <div class="tab-content mt-3">
                                        <div class="tab-pane show active" id="page1">
                                            <div class="tab-content">
                                                <ul class="nav nav-tabs">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#main">MAIN</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-toggle="tab" href="#bankinfo">BANK INFO</a>
                                                    </li>
                                                    {{-- <li class="nav-item">
                                                        <a class="nav-link" data-toggle="tab" href="#bayar">KODE LAMA BAYAR</a>
                                                    </li> --}}
                                                </ul>
                                                <!-- ------------------------------------------------------------------- -->
                                                <div class="tab-content mt-3">
                                                    <!---------------------------- MAIN TAB ------------------------------->
                                                    <div class="tab-pane show active" id="main">
                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="PEMILIK" id="PEMILIK" name="PEMILIK" 
                                                                    value="{{$header->PEMILIK}}" placeholder=" ">
                                                                <label for="PEMILIK">Pemilik</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="TLP_R" id="TLP_R" name="TLP_R" 
                                                                    value="{{$header->TLP_R}}" placeholder=" ">
                                                                <label for="TLP_R">Telp</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="ALMT_R" id="ALMT_R" name="ALMT_R" 
                                                                    value="{{$header->ALMT_R}}" placeholder=" ">
                                                                <label for="ALMT_R">Alamat Rumah</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-3 form-group row special-input-label">
                                                                <input type="text" class="ALMT_K" id="ALMT_K" name="ALMT_K" 
                                                                    value="{{$header->ALMT_K}}" placeholder=" ">
                                                                <label for="ALMT_K">Alamat Kantor</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                            
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="KOTA" id="KOTA" name="KOTA" 
                                                                    value="{{$header->KOTA}}" placeholder=" ">
                                                                <label for="KOTA">Kota</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="ALMT_GD" id="ALMT_GD" name="ALMT_GD" 
                                                                    value="{{$header->ALMT_GD}}" placeholder=" ">
                                                                <label for="ALMT_GD">Alamat Gudang</label>
                                                            </div>
                                                            <!-- tutupannya -->                                                            
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="TLP_K" id="TLP_K" name="TLP_K" 
                                                                    value="{{$header->TLP_K}}" placeholder=" ">
                                                                <label for="TLP_K">Telp. Kantor</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1"></div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="NO_FAX" id="NO_FAX" name="NO_FAX" 
                                                                    value="{{$header->NO_FAX}}" placeholder=" ">
                                                                <label for="NO_FAX">FAX</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="NO_HP" id="NO_HP" name="NO_HP" 
                                                                    value="{{$header->NO_HP}}" placeholder=" ">
                                                                <label for="NO_HP">No.HP</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1"></div>
                                                            
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="NO_TELEX" id="NO_TELEX" name="NO_TELEX" 
                                                                    value="{{$header->NO_TELEX}}" placeholder=" ">
                                                                <label for="NO_TELEX">No. Telex</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="EMAIL" id="EMAIL" name="EMAIL" 
                                                                    value="{{$header->EMAIL}}" placeholder=" ">
                                                                <label for="EMAIL">Email1</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="EMAIL2" id="EMAIL2" name="EMAIL2" 
                                                                    value="{{$header->EMAIL2}}" placeholder=" ">
                                                                <label for="EMAIL2">Email2</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>
                                                            
                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="EMAIL3" id="EMAIL3" name="EMAIL3" 
                                                                    value="{{$header->EMAIL3}}" placeholder=" ">
                                                                <label for="EMAIL3">Email3</label>
                                                            </div>
                                                            <!-- tutupannya -->                                                            
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-1 form-group row special-input-label">
                                                                <input type="text" class="GOL_BRG" id="GOL_BRG" name="GOL_BRG" 
                                                                    value="{{$header->GOL_BRG}}" placeholder=" ">
                                                                <label for="GOL_BRG">Gol. Brg</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <!-- code text box baru -->
                                                            <div class="col-md-1 form-group row special-input-label">
                                                                <input type="text" class="KD_PEMBY" id="KD_PEMBY" name="KD_PEMBY" 
                                                                    value="{{$header->KD_PEMBY}}" placeholder=" ">
                                                                <label for="KD_PEMBY">KD. Pemb</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="STM_PEMBL" id="STM_PEMBL" name="STM_PEMBL" 
                                                                    value="{{$header->STM_PEMBL}}" placeholder=" ">
                                                                <label for="STM_PEMBL">Stm Pemb</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <!-- code text box baru -->
                                                            <div class="col-md-1 form-group row special-input-label">
                                                                <input type="text" class="DISC_PS" id="DISC_PS" name="DISC_PS" 
                                                                    value="{{$header->DISC_PS}}" placeholder=" ">
                                                                <label for="DISC_PS">Dis PS</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="JEN_BRG1" id="JEN_BRG1" name="JEN_BRG1" 
                                                                    value="{{$header->JEN_BRG1}}" placeholder=" ">
                                                                <label for="JEN_BRG1">Jenis Barang</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="CARA" id="CARA" name="CARA" 
                                                                    value="{{$header->CARA}}" placeholder=" ">
                                                                <label for="CARA">Cara</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <!-- code text box baru -->
                                                            <div class="col-md-1 form-group row special-input-label">
                                                                <input type="text" class="BG_PERS" id="BG_PERS" name="BG_PERS" 
                                                                    value="{{$header->BG_PERS}}" placeholder=" ">
                                                                <label for="BG_PERS">BG Pers</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <div class="col-md-2">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="STTS" id="STTS" name="STTS" 
                                                                    value="{{$header->STTS}}" placeholder=" ">
                                                                <label for="STTS">Status</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <!-- code text box baru -->
                                                            <div class="col-md-1 form-group row special-input-label">
                                                                <input type="text" class="SUB" id="SUB" name="SUB" 
                                                                    value="{{$header->SUB}}" placeholder=" ">
                                                                <label for="SUB">Sub</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>
                                                    </div>

                                                    <!-------- batas antara main dan bank info ----------------------------->

                                                    <!------------------------- BANK INFO TAB ----------------------------->
                                                    <div class="tab-pane show" id="bankinfo">
                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="KD_BANK" id="KD_BANK" name="KD_BANK" 
                                                                    value="{{$header->KD_BANK}}" placeholder=" ">
                                                                <label for="KD_BANK">Kode Bank</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="NPWP" id="NPWP" name="NPWP" 
                                                                    value="{{$header->NPWP}}" placeholder=" ">
                                                                <label for="NPWP">NPWP</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="NPPKP" id="NPPKP" name="NPPKP" 
                                                                    value="{{$header->NPPKP}}" placeholder=" ">
                                                                <label for="NPPKP">NPPKP</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="NAMA_B" id="NAMA_B" name="NAMA_B" 
                                                                    value="{{$header->NAMA_B}}" placeholder=" ">
                                                                <label for="NAMA_B">Bank</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                            
                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="NM_NPWP" id="NM_NPWP" name="NM_NPWP" 
                                                                    value="{{$header->NM_NPWP}}" placeholder=" ">
                                                                <label for="NM_NPWP">Nama NPWP</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="CABANG_B" id="CABANG_B" name="CABANG_B" 
                                                                    value="{{$header->CABANG_B}}" placeholder=" ">
                                                                <label for="CABANG_B">Cabang</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                            
                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="NO_NPWP" id="NO_NPWP" name="NO_NPWP" 
                                                                    value="{{$header->NO_NPWP}}" placeholder=" ">
                                                                <label for="NO_NPWP">No. NPWP</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="KOTA_B" id="KOTA_B" name="KOTA_B" 
                                                                    value="{{$header->KOTA_B}}" placeholder=" ">
                                                                <label for="KOTA_B">Kota</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                            
                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="AL_NPWP" id="AL_NPWP" name="AL_NPWP" 
                                                                    value="{{$header->AL_NPWP}}" placeholder=" ">
                                                                <label for="AL_NPWP">Alamat NPWP</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="AN_B" id="AN_B" name="AN_B" 
                                                                    value="{{$header->AN_B}}" placeholder=" ">
                                                                <label for="AN_B">A / N</label>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="NOREK" id="NOREK" name="NOREK" 
                                                                    value="{{$header->NOREK}}" placeholder=" ">
                                                                <label for="NOREK">Rekening</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                            
                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input class="date" id="TG_NPWP" name="TG_NPWP" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TG_NPWP))}}">
                                                                <label for="TG_NPWP">Tanggal NPWP</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="SERI" id="SERI" name="SERI" 
                                                                    value="{{$header->SERI}}" placeholder=" ">
                                                                <label for="SERI">Seri</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>
                                                    </div>
                                                    <!------------------- Batas antara bank info dan bayar ----------------------->

                                                    <!---------------------------------- Lama bayar ------------------------------>
                                                    {{-- <div class="tab-pane show" id="bayar">
                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="FO_KLB" id="FO_KLB" name="FO_KLB" 
                                                                    value="{{$header->FO_KLB}}" placeholder=" ">
                                                                <label for="FO_KLB">FO</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="NF_KLB" id="NF_KLB" name="NF_KLB" 
                                                                    value="{{$header->NF_KLB}}" placeholder=" ">
                                                                <label for="NF_KLB">NF</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                            
                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="PB_KLB" id="PB_KLB" name="PB_KLB" 
                                                                    value="{{$header->PB_KLB}}" placeholder=" ">
                                                                <label for="PB_KLB">PB</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="ST_KLB" id="ST_KLB" name="ST_KLB" 
                                                                    value="{{$header->ST_KLB}}" placeholder=" ">
                                                                <label for="ST_KLB">ST</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="FF_KLB" id="FF_KLB" name="FF_KLB" 
                                                                    value="{{$header->FF_KLB}}" placeholder=" ">
                                                                <label for="FF_KLB">FF</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                            
                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="BS_KLB" id="BS_KLB" name="BS_KLB" 
                                                                    value="{{$header->BS_KLB}}" placeholder=" ">
                                                                <label for="BS_KLB">BS</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-3 form-group row special-input-label">
                                                                <input type="text" class="MATERAI" id="MATERAI" name="MATERAI" 
                                                                    value="{{$header->MATERAI}}" placeholder=" ">
                                                                <label for="MATERAI">Materai</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-3 form-group row special-input-label">
                                                                <input type="text" class="ZONE" id="ZONE" name="ZONE" 
                                                                    value="{{$header->ZONE}}" placeholder=" ">
                                                                <label for="ZONE">Zone</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-3 form-group row special-input-label">
                                                                <input type="text" class="CETAK_SBY" id="CETAK_SBY" name="CETAK_SBY" 
                                                                    value="{{$header->CETAK_SBY}}" placeholder=" ">
                                                                <label for="CETAK_SBY">Cetak SBY</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-3 form-group row special-input-label">
                                                                <input type="text" class="ACC_PPN" id="ACC_PPN" name="ACC_PPN" 
                                                                    value="{{$header->ACC_PPN}}" placeholder=" ">
                                                                <label for="ACC_PPN">Acc PPN</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-7 form-group row special-input-label">
                                                                <input type="text" class="CAT_LO" id="CAT_LO" name="CAT_LO" 
                                                                    value="{{$header->CAT_LO}}" placeholder=" ">
                                                                <label for="CAT_LO">Cat LO</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="DIS_P4" id="DIS_P4" name="DIS_P4" 
                                                                    value="{{$header->DIS_P4}}" placeholder=" ">
                                                                <label for="DIS_P4">Dis P4</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="SP" id="SP" name="SP" 
                                                                    value="{{$header->SP}}" placeholder=" ">
                                                                <label for="SP">SP %</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="RETUR" id="RETUR" name="RETUR" 
                                                                    value="{{$header->RETUR}}" placeholder=" ">
                                                                <label for="RETUR">Retur</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>
                                                    </div> --}}
                                                    <!-- batas lama bayar -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-------------------------------- batas antara page 1 dan page 2 ------------------------->
                                        
                                        <div class="tab-pane show" id="page2">
                                            <div class="tab-content">
                                                <ul class="nav nav-tabs">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#main2">MAIN</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-toggle="tab" href="#uraian">URAIAN</a>
                                                    </li>
                                                </ul>
                                                <!-- ------------------------------------------------------------------------ -->
                                                <div class="tab-content mt-3">
                                                    <!--------------------------------------- TAB MAIN ------------------------->
                                                    <div class="tab-pane show active" id="main2">
                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="KETHAP" id="KET_HAPUS" name="KET_HAPUS" 
                                                                    value="{{$header->KET_HAPUS}}" placeholder=" ">
                                                                <label for="KET_HAPUS">Ket Hapus</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="JMN_RETUR" id="JMN_RETUR" name="JMN_RETUR" 
                                                                    value="{{$header->JMN_RETUR}}" placeholder=" ">
                                                                <label for="JMN_RETUR">JMN Retur</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="HARI" id="HARI" name="HARI" 
                                                                    value="{{$header->HARI}}" placeholder=" ">
                                                                <label for="HARI">Hari</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="TND_SPL" id="TND_SPL" name="TND_SPL" 
                                                                    value="{{$header->TND_SPL}}" placeholder=" ">
                                                                <label for="TND_SPL">Tanda Suplier</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="B_CODE" id="B_CODE" name="B_CODE" 
                                                                    value="{{$header->B_CODE}}" placeholder=" ">
                                                                <label for="B_CODE">Barcode</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="JAMIN_RET" id="JAMIN_RET" name="JAMIN_RET" 
                                                                    value="{{$header->JAMIN_RET}}" placeholder=" ">
                                                                <label for="JAMIN_RET">Jamin retur</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input class="date" id="TGL" name="TGL" data-date-format="dd-mm-yyyy" type="text" autocomplete="off" value="{{date('d-m-Y',strtotime($header->TGL))}}">
                                                                <label for="TGL">Tanggal</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="VA_GZ" id="VA_GZ" name="VA_GZ" 
                                                                    value="{{$header->VA_GZ}}" placeholder=" ">
                                                                <label for="VA_GZ">Virtual Account</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="S_BAR" id="S_BAR" name="S_BAR" 
                                                                    value="{{$header->S_BAR}}" placeholder=" ">
                                                                <label for="S_BAR">S Bar</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="NOREK_GZ" id="NOREK_GZ" name="NOREK_GZ" 
                                                                    value="{{$header->NOREK_GZ}}" placeholder=" ">
                                                                <label for="NOREK_GZ">Nomor Rekening</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="AN_B_GZ" id="AN_B_GZ" name="AN_B_GZ" 
                                                                    value="{{$header->AN_B_GZ}}" placeholder=" ">
                                                                <label for="AN_B_GZ">AN Bank</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="ANB_VA_GZ" id="ANB_VA_GZ" name="ANB_VA_GZ" 
                                                                    value="{{$header->ANB_VA_GZ}}" placeholder=" ">
                                                                <label for="ANB_VA_GZ">ANB Virtual Account</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="EMAIL_GZ" id="EMAIL_GZ" name="EMAIL_GZ" 
                                                                    value="{{$header->EMAIL_GZ}}" placeholder=" ">
                                                                <label for="EMAIL_GZ">Email</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>
                                                    </div>
                                                    <!------------------------------------- BATAS TAB MAIN ------------------------------->
                                                    
                                                    <!--------------------------------------- TAB URAIAN --------------------------------->
                                                    <div class="tab-pane show" id="uraian">
                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="D_BUTOR" id="D_BUTOR" name="D_BUTOR" 
                                                                    value="{{$header->D_BUTOR}}" placeholder=" ">
                                                                <label for="D_BUTOR">Distributor</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="CAT_RET" id="CAT_RET" name="CAT_RET" 
                                                                    value="{{$header->CAT_RET}}" placeholder=" ">
                                                                <label for="CAT_RET">Cat Ret</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="CAT_PRM" id="CAT_PRM" name="CAT_PRM" 
                                                                    value="{{$header->CAT_PRM}}" placeholder=" ">
                                                                <label for="CAT_PRM">Cat PRM</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="SR_TERBIT" id="SR_TERBIT" name="SR_TERBIT" 
                                                                    value="{{$header->SR_TERBIT}}" placeholder=" ">
                                                                <label for="SR_TERBIT">SR Terbit</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="BONAFIT" id="BONAFIT" name="BONAFIT" 
                                                                    value="{{$header->BONAFIT}}" placeholder=" ">
                                                                <label for="BONAFIT">Bonafit</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="KEL_PAJAK" id="KEL_PAJAK" name="KEL_PAJAK" 
                                                                    value="{{$header->KEL_PAJAK}}" placeholder=" ">
                                                                <label for="KEL_PAJAK">Kel Pajak</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="N_AKTIF" id="N_AKTIF" name="N_AKTIF" 
                                                                    value="{{$header->N_AKTIF}}" placeholder=" ">
                                                                <label for="N_AKTIF">N Aktif</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="KETNAKTIF" id="KETNAKTIF" name="KETNAKTIF" 
                                                                    value="{{$header->KETNAKTIF}}" placeholder=" ">
                                                                <label for="KETNAKTIF">Ket N Aktif</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="LAIN1" id="LAIN1" name="LAIN1" 
                                                                    value="{{$header->LAIN1}}" placeholder=" ">
                                                                <label for="LAIN1">Lain - Lain</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="KD_MIN" id="KOD_MIN" name="KOD_MIN" 
                                                                    value="{{$header->KOD_MIN}}" placeholder=" ">
                                                                <label for="KOD_MIN">KD Min</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="KLB2" id="KLB2" name="KLB2" 
                                                                    value="{{$header->KLB2}}" placeholder=" ">
                                                                <label for="KLB2">KLB2</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="LAIN2" id="LAIN2" name="LAIN2" 
                                                                    value="{{$header->LAIN2}}" placeholder=" ">
                                                                <label for="LAIN2"></label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="ORDR" id="ORDR" name="ORDR" 
                                                                    value="{{$header->ORDR}}" placeholder=" ">
                                                                <label for="ORDR">Order</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="BY_KR" id="BY_KR" name="BY_KR" 
                                                                    value="{{$header->BY_KR}}" placeholder=" ">
                                                                <label for="BY_KR">BY_KR</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="URAIAN1" id="URAIAN1" name="URAIAN1" 
                                                                    value="{{$header->URAIAN1}}" placeholder=" ">
                                                                <label for="URAIAN1">Uraian</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="SUP_BARU" id="SUP_BARU" name="SUP_BARU" 
                                                                    value="{{$header->SUP_BARU}}" placeholder=" ">
                                                                <label for="SUP_BARU">Sup Baru</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-1">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="JAM" id="JAM" name="JAM" 
                                                                    value="{{$header->JAM}}" placeholder=" ">
                                                                <label for="JAM">Jam</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="URAIAN2" id="URAIAN2" name="URAIAN2" 
                                                                    value="{{$header->URAIAN2}}" placeholder=" ">
                                                                <label for="URAIAN2">Uraian</label>
                                                            </div>
                                                            <!-- tutupannya -->

                                                            <div class="col-md-4">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-2 form-group row special-input-label">
                                                                <input type="text" class="KLK" id="KLK" name="KLK" 
                                                                    value="{{$header->KLK}}" placeholder=" ">
                                                                <label for="KLK">KLK</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>

                                                        <div class="form-group row">
                                                            <div class="col-md-3">
                                                            </div>

                                                            <!-- code text box baru -->
                                                            <div class="col-md-5 form-group row special-input-label">
                                                                <input type="text" class="CAT_SP" id="CAT_SP" name="CAT_SP" 
                                                                    value="{{$header->CAT_SP}}" placeholder=" ">
                                                                <label for="CAT_SP">Cat SP</label>
                                                            </div>
                                                            <!-- tutupannya -->
                                                        </div>
                                                    </div>
                                                    <!------------------------------------- BATAS TAB URAIAN ------------------------------->
                                                </div>
                                            </div>
                                        </div>
                                        <!--------------------------------------------- BATAS TAB PAGE 2 --------------------------------------->
                                    </div>
                                </div>

                                <div class="mt-3 col-md-12 form-group row">
                                    <div class="col-md-4">
                                        <button hidden type="button" id='TOPX'  onclick="location.href='{{url('/sup/edit/?idx=' .$idx. '&tipx=top' )}}'" class="btn btn-outline-primary">Top</button>
                                        <button hidden type="button" id='PREVX' onclick="location.href='{{url('/sup/edit/?idx='.$header->NO_ID.'&tipx=prev&kodex='.$header->KODES )}}'" class="btn btn-outline-primary">Prev</button>
								        <button hidden type="button" id='NEXTX' onclick="location.href='{{url('/sup/edit/?idx='.$header->NO_ID.'&tipx=next&kodex='.$header->KODES )}}'" class="btn btn-outline-primary">Next</button>
                                        <button hidden type="button" id='BOTTOMX' onclick="location.href='{{url('/sup/edit/?idx=' .$idx. '&tipx=bottom' )}}'" class="btn btn-outline-primary">Bottom</button>
                                    </div>
                                    <div class="col-md-5">
                                        <button hidden type="button" id='NEWX' onclick="location.href='{{url('/sup/edit/?idx=0&tipx=new' )}}'" class="btn btn-warning">New</button>
                                        <button hidden type="button" id='EDITX' onclick='hidup()' class="btn btn-secondary">Edit</button>
                                        <button hidden type="button" id='UNDOX' onclick="location.href='{{url('/sup/edit/?idx=' .$idx. '&tipx=undo' )}}'" class="btn btn-info">Undo</button>
                                        <button type="button" id='SAVEX' onclick='simpan()'   class="btn btn-success" class="fa fa-save"></i>Save</button>

                                    </div>
                                    <div class="col-md-3">
                                        <button hidden type="button" id='HAPUSX' hidden onclick="hapusTrans()" class="btn btn-outline-danger">Hapus</button>
                                        <!-- <button type="button" id='CLOSEX'  onclick="location.href='{{url('/sup' )}}'" class="btn btn-outline-secondary">Close</button> -->
                                        <!-- tombol close sweet alert -->
								        <button type="button" id='CLOSEX' onclick="closeTrans()" class="btn btn-outline-secondary">Close</button></div>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </div>
    <!-- /.content -->



@endsection

@section('footer-scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- tambahan untuk sweetalert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- tutupannya -->
<script>
    var target;
	var idrow = 1;

    $(document).ready(function () {

        $('body').on('keydown', 'input, select', function(e) {
            if (e.key === "Enter") {
                var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
                focusable = form.find('input,select,textarea').filter(':visible');
                next = focusable.eq(focusable.index(this)+1);
                console.log(next);
                if (next.length) {
                    next.focus().select();
                } else {
                    // tambah();
                    // var nomer = idrow-1;
                    // console.log("REC"+nomor);
                    // document.getElementById("REC"+nomor).focus();
                    // form.submit();
                }
                return false;
            }
        });


		$tipx = $('#tipx').val();


        if ( $tipx == 'new' )
		{

			baru();
			//tambah();
		}

        if ( $tipx != 'new' )

		{
			 //alert('ganti');
			 ganti();
		}



        $('.date').datepicker({  
            dateFormat: 'dd-mm-yy'
		});

    });




	function baru() {

		 kosong();
		 hidup();

	}

	function ganti() {

		//  mati();
        hidup();

	}

	function batal() {

		// alert($header[0]->NO_BUKTI);

		 //$('#NO_BUKTI').val($header[0]->NO_BUKTI);
		 mati();

	}





	function hidup() {


		$("#TOPX").attr("disabled", true);
	    $("#PREVX").attr("disabled", true);
	    $("#NEXTX").attr("disabled", true);
	    $("#BOTTOMX").attr("disabled", true);

	    $("#NEWX").attr("disabled", true);
	    $("#EDITX").attr("disabled", true);
	    $("#UNDOX").attr("disabled", false);
	    $("#SAVEX").attr("disabled", false);

	    $("#HAPUSX").attr("disabled", true);
	    $("#CLOSEX").attr("disabled", false);


		$("#CARI").attr("readonly", true);
	    $("#SEARCHX").attr("disabled", true);

	    $("#PLUSX").attr("hidden", false)

		$("#KODES").attr("readonly", false);
		$("#NAMAS").attr("readonly", false);



	}


	function mati() {


	    $("#TOPX").attr("disabled", false);
	    $("#PREVX").attr("disabled", false);
	    $("#NEXTX").attr("disabled", false);
	    $("#BOTTOMX").attr("disabled", false);


	    $("#NEWX").attr("disabled", false);
	    $("#EDITX").attr("disabled", false);
	    $("#UNDOX").attr("disabled", true);
	    $("#SAVEX").attr("disabled", true);
	    $("#HAPUSX").attr("disabled", false);
	    $("#CLOSEX").attr("disabled", false);

		$("#CARI").attr("readonly", false);
	    $("#SEARCHX").attr("disabled", false);

	    $("#PLUSX").attr("hidden", true)

	    $("#KODES").attr("readonly", true);
		$("#NAMAS").attr("readonly", true);



	}


	function kosong() {

		 $('#KODES').val("");
		 $('#NAMAS').val("");


		var html = '';
		$('#detailx').html(html);

	}

	// function hapusTrans() {
	// 	let text = "Hapus Transaksi "+$('#KODES').val()+"?";
	// 	if (confirm(text) == true)
	// 	{
	// 		window.location ="{{url('/sup/delete/'.$header->NO_ID )}}'";

	// 	}
	// 	return false;
	// }

    // sweetalert untuk tombol hapus dan close
	
	function hapusTrans() {
		let text = "Hapus Transaksi "+$('#NO_BUKTI').val()+"?";

		var loc ='';
		
		Swal.fire({
			title: 'Are you sure?',
			text: text,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!',
			cancelButtonText: 'Cancel'
		}).then((result) => {
			if (result.isConfirmed) {
				// Show a success message before redirecting to delete the data
				Swal.fire({
					title: 'Deleted!',
					text: 'Data has been deleted.',
					icon: 'success',
					confirmButtonText: 'OK'
				}).then(() => {
					// Redirect to delete the data after user confirms the success message
	            	loc = "{{ url('/sup/delete/'.$header->NO_ID) }}"  ;

		            // alert(loc);
	            	window.location = loc;
		
				});
			}
		});
	}
	
	function closeTrans() {
		console.log("masuk");
		var loc ='';
		
		Swal.fire({
			title: 'Are you sure?',
			text: 'Do you really want to close this page? Unsaved changes will be lost.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, close it',
			cancelButtonText: 'No, stay here'
		}).then((result) => {
			if (result.isConfirmed) {
	        	loc = "{{ url('/sup/') }}" ;
				window.location = loc ;
			} else {
				Swal.fire({
					icon: 'info',
					title: 'Cancelled',
					text: 'You stayed on the page'
				});
			}
		});
	}

	// tutupannya

	function CariBukti() {


		var cari = $("#CARI").val();
		var loc = "{{ url('/sup/edit/') }}" + '?idx={{ $header->NO_ID}}&tipx=search&kodex=' +encodeURIComponent(cari);
		window.location = loc;

	}


	// function simpan() {
        //cekAcc($('#ACNO').val());
        //(hasilCek==0) ? document.getElementById("entri").submit() : alert('Account '+$('#ACNO').val()+' sudah ada!');
        // document.getElementById("entri").submit()
	// }

    var hasilCek;
	function cekSup(kodes) {
		$.ajax({
			type: "GET",
			url: "{{url('sup/ceksup')}}",
            async: false,
			data: ({ KODES: kodes, }),
			success: function(data) {
                if (data.length > 0) {
                    $.each(data, function(i, item) {
                        hasilCek=data[i].ADA;
                    });
                }
			},
			error: function() {
				alert('Error cekSup occured');
			}
		});
		return hasilCek;
	}
    
	function simpan() {
        hasilCek=0;
		$tipx = $('#tipx').val();
		
        if ( $('#KODES').val()=='' ) 
		{				
			hasilCek = '1';
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Kode# Harus Diisi.'
			});
			return; // Stop function execution
		}

		if ( $('#NAMAS').val()=='' ) 
		{				
			hasilCek = '1';
			Swal.fire({
				icon: 'warning',
				title: 'Warning',
				text: 'Nama# Harus Diisi.'
			});
			return; // Stop function execution
		}

        if ( $tipx == 'new' )
		{
			cekSup($('#KODES').val());		
		}
		

        (hasilCek==0) ? document.getElementById("entri").submit() : alert('Suplier '+$('#KODES').val()+' sudah ada!');
	}
</script>
</script>
@endsection

