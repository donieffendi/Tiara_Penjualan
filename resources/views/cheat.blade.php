@extends('layouts.main')
<style>
    .tab-content > .tab-pane:not(.active),
    .pill-content > .pill-pane:not(.active) {
        display: block;
        height: 0;
        overflow-y: hidden;
    } 
    .owl-item img {
        width: 100%;
        height: 90% !important;
        object-fit: cover;
    }
</style>

@section('content')
<div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6 animate__animated animate__lightSpeedInRight animate__slower">
            <h1 class="m-0">Cheatsheet</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Cheatsheet</li>
            </ol>
          </div>
        </div>
        <div class="row">
            <div class="owl-carousel owl-theme owl-loaded">
                <div class="owl-stage-outer">
                    <div class="owl-stage">
                        <div class="owl-item img-fluid"><img src="{{ asset('images/gallery/3.jpg') }}" alt="slider-image"></div>
                        <div class="owl-item img-fluid"><img src="{{ asset('images/gallery/5.jpg') }}" alt="slider-image"></div>
                        <div class="owl-item img-fluid"><img src="{{ asset('images/gallery/7.jpg') }}" alt="slider-image"></div>
                        <div class="owl-item img-fluid"><img src="{{ asset('images/gallery/9.jpg') }}" alt="slider-image"></div>
                        <div class="owl-item img-fluid"><img src="{{ asset('images/gallery/10.jpg') }}" alt="slider-image"></div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>

    <!-- Status -->
    @if (session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
    @endif

    <div class="content">
      <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <ul class="nav nav-tabs">
                    <li class="nav-item active">
                        <a class="nav-link active" href="#jasper" id="jasper_tab" data-toggle="tab">Jasper</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kool" id="kool_tab" data-toggle="tab">Koolreport</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#josh" id="josh_tab" data-toggle="tab">Josh Admin</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content mt-3">
                <div id="jasper" class="tab-pane active">
                    <div class="col-md-12">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="card card-success">
                                    <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                        <h3 class="card-title"><strong>Grup</strong></h3>
                                    </div>
                                    <div class="card-body">
                                        <p>Aktifkan Page Footer.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-success">
                                    <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                        <h3 class="card-title"><strong>Parameter +/-</strong></h3>
                                    </div>
                                    <div class="card-body">
                                        <p>( $P{SAWAL} + $V{var_masuk} ) - $V{var_keluar}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="card card-success">
                                    <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                        <h3 class="card-title"><strong>Gambar/Icon</strong></h3>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>(Laravel)</strong> Lokasi ada di public/img_jasper. 
                                            <br>
                                                Setting image di jasper dari expression. Kalo image gak ketemu sesuai link (misal: img_jasper/avatar-1.png) maka jasper akan error.
                                            <br>
                                            <img class="img-fluid" src="img_cheatsheet/jasper_image_field.jpg">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="kool" class="tab-pane">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                    <h3 class="card-title"><strong>ChartCard</strong></h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>(Laravel)</strong> Card berbentuk chart line untuk koolreport.
                                    <br>
                                    <div class="report-content col-md-8">
                                        <?php
                                        use \koolreport\amazing\ChartCard;

                                        if ($hasil)
                                        {
                                            ChartCard::create(array(
                                                "title"=>"Pembelian Tahun ".session()->get('periode')['tahun'],
                                                "value"=>$nilaimax,
                                                // "format"=>array(
                                                //     "value"=>array(
                                                //         "decimals"=>2,
                                                //         "prefix"=>$bulanmax." : ",
                                                //     ),
                                                // ),
                                                "preset"=>"primary",
                                                "chart"=>array(
                                                    "type"=>"line",
                                                    "dataSource"=>$hasil,
                                                    "columns"=>array(
                                                        "BULAN",
                                                        "KG"=>array(
                                                            "type"=>"number",
                                                            "decimals"=>2,
                                                            // "decimalPoint"=>".",
                                                            // "thousandSeparator"=>",",
                                                            // "prefix"=>"$",
                                                            // "suffix"=>"USD",
                                                        )
                                                    )
                                                ),
                                                "cssClass"=>array(
                                                    "icon"=>"icon-people"
                                                ),
                                                // "href"=>"function(){
                                                //     window.open('http://google.com/');
                                                // }",
                                            ));
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-danger">
                                <div class="card-header">
                                    <h3 class="card-title">Total Rp</h3>
                    
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="josh" class="tab-pane">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                    <h3 class="card-title"><strong>Manage User</strong></h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>(Laravel)</strong>
                                        <br>
                                            Menambahkan menu untuk mengatur User
                                            <span class="badge badge-info">level</span>
                                        <br>
                                        <img class="img-fluid" src="img_cheatsheet/user_manage.jpg">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                    <h3 class="card-title"><strong>Sidebar Terpilih</strong></h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>(Laravel)</strong>
                                        <br>
                                            Memberi tanda untuk menu sidebar yang sedang digunakan
                                        <br>
                                        <img class="img-fluid" src="img_cheatsheet/sidebar_selected1.jpg">
                                        <br>
                                        <br>
                                        <img class="img-fluid" src="img_cheatsheet/sidebar_selected2.jpg">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                    <h3 class="card-title"><strong>User Level</strong></h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>(Laravel)</strong>
                                        <br>
                                            Mengatur akses menu berbeda tiap User Level. 
                                            <ul>
                                                <li>
                                                    Tambahkan CheckDivisi.php di folder Middleware.
                                                    <img class="img-fluid" src="img_cheatsheet/user_level1.jpg">
                                                </li>
                                                <br>
                                                <li>
                                                    Lakukan penyesuaian di route dan menu.
                                                    <img class="img-fluid" src="img_cheatsheet/user_level2.jpg">
                                                    <br>
                                                    <br>
                                                    <img class="img-fluid" src="img_cheatsheet/user_level3.jpg">
                                                </li>
                                            </ul>
                                        <br>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                    <h3 class="card-title"><strong>Serverside Datatable</strong></h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>(Laravel)</strong>
                                        <br>
                                            Tambahkan serverSide: true , pada option datatable.
                                        <br>
                                        <img class="img-fluid" src="img_cheatsheet/serverSide_datatable.jpg">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                    <h3 class="card-title"><strong>Liv Icons</strong></h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>(Laravel)</strong>
                                        <br>
                                            Tambahkan js berikut ini untuk Liv Icons :
                                        <ul>
                                            <li>jquery-1.11.1.min.js (optional)
                                                <i class="livicon" data-name="apple" data-size="32" data-loop="true" data-color="red" data-hovercolor="#000"></i>
                                            </li>
                                            <li>raphael-min.js
                                                <i class="livicon" data-name="alarm" data-size="32" data-loop="true" data-color="blue" data-hovercolor="#000"></i>
                                            </li>
                                            <li>livicons-1.4.min.js
                                                <i class="livicon" data-name="bank" data-size="32" data-loop="true" data-color="green" data-hovercolor="#000"></i>
                                            </li>
                                            <li>json2.min.js
                                                <i class="livicon" data-name="user" data-size="32" data-loop="true" data-color="purple" data-hovercolor="#000"></i>
                                            </li>
                                        </ul>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                    <h3 class="card-title"><strong>Animasi</strong></h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>(Laravel)</strong>
                                        <br>
                                            Contoh animasi di judul halaman ini, bisa dilihat juga di dashboard. Untuk dokumentasi dan css ada di <a href="https://animate.style/" target="_blank">Animate CSS</a>
                                        <br>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                    <h3 class="card-title"><strong>Carousel</strong></h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>(Laravel)</strong>
                                        <br>
                                            Contoh Carousel di halaman ini menggunakan <a href="https://owlcarousel2.github.io/OwlCarousel2/" target="_blank">Owl Carousel</a>.
                                        <br>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                    <h3 class="card-title"><strong>Toastr</strong></h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>(Laravel)</strong>
                                        <br>
                                            Toastr untuk memberikan informasi berupa notifikasi sementara ke pengguna. Plugin ini dapat diunduh di <a href="https://codeseven.github.io/toastr/" target="_blank">Toastr</a>.
                                        <br>
                                            <button class="btn btn-info" type="button" onclick="tampilToast()">Tampilkan Toastr</button>
                                        <br>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header" data-card-widget="collapse" style="cursor: pointer;">
                                    <h3 class="card-title"><strong>Sweet Alert</strong></h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>(Laravel)</strong>
                                        <br>
                                            Tambahkan file" berikut: 
                                        <ul>
                                            <li>sweetalert2.css
                                            </li>
                                            <li>sweetalert2.js
                                            </li>
                                            <li>custom_sweetalert.js (untuk daftar function)
                                            </li>
                                        </ul>
                                        <br>
                                            Contoh Sweet Alert dengan Timer. 
                                        <br>
                                            <button class="btn btn-info" type="button" id="autoclose">Notifikasi</button>
                                        <br>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('javascripts')
<link href="{{asset('foxie_js_css/josh/animate.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('foxie_js_css/josh/owl_carousel/css/owl.carousel.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('foxie_js_css/josh/owl_carousel/css/owl.theme.default.min.css') }}" rel="stylesheet" type="text/css">
<script src="{{ asset('foxie_js_css/josh/owl_carousel/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('foxie_js_css/josh/toastr/toastr.js') }}" ></script>
<link href="{{ asset('foxie_js_css/josh/toastr/toastr.css') }}" rel="stylesheet" type="text/css">
<script src="{{ asset('foxie_js_css/sweetalert/js/sweetalert2.js') }}" type="text/javascript"></script>
<link href="{{ asset('foxie_js_css/sweetalert/css/sweetalert2.css') }}" rel="stylesheet" type="text/css" />
<script>
    $(document).ready(function(){
        var owl = $('.owl-carousel');
        owl.owlCarousel({
            items:3,
            loop:true,
            margin:10,
            autoplay:true,
            autoplayTimeout:3000,
            autoplayHoverPause:true
        });

        const swalWithBootstrapButtons = swal.mixin({
            confirmButtonClass: 'btn btn-success mr-3',
            cancelButtonClass: 'btn btn-custom-light',
            buttonsStyling: false,
        });
        $("#autoclose").on('click',function()
        {
            let timerInterval
            swalWithBootstrapButtons.fire({
                title: 'Auto close Sweet Alert!',
                html: 'Notifikasi tertutup otomatis pada <strong></strong> miliseconds.',
                timer: 3000,
                onBeforeOpen: () => {
                        swalWithBootstrapButtons.showLoading()
                        timerInterval = setInterval(() => {
                        swalWithBootstrapButtons.getContent().querySelector('strong')
                            .textContent = Swal.getTimerLeft()
                        }, 100)
                    },
                onClose: () => {
                        clearInterval(timerInterval)
                    }
            }).then((result) => {
                if (
                    // Read more about handling dismissals
                    result.dismiss === Swal.DismissReason.timer
                ) {
                    console.log('I was closed by the timer')
                }
            })
        });
    });
    
	function tampilToast()
    {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut",
        };
        toastr.info('This is info toastr');
	};
</script>
@endsection