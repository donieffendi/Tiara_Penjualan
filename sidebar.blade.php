<!-- Main Sidebar Container -->
<style>
/* General sidebar styling */
.vertical-menu {
    width: 50px;
    height: 100vh;
    background-color: #343a40;
    position: relative;
}

.content-box {
    flex-grow: 1;
    /* This makes the child content fill the height */
    background-color: lightgray;
    /* Just for demonstration */
    padding: 20px;
}

/* Main menu items */
.vertical-menu a {
    color: white;
    padding: 10px;
    text-decoration: none;
    display: block;
}

/*.vertical-menu a:hover {
      background-color: #495057;
      color: white;
    }*/

/* Mega menu container */
.mega-menu {
    position: absolute;
    /* top: 800; */
    /* top: 50; */
    left: 100px;
    width: 850px;

    background-color: white;
    display: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 5px;
    z-index: 9999 !important;
}

#a {
    top: 10;
}

#b {
    top: 40;
}

#c {
    top: 50;
}

#d {
    top: 100;
}

#e {
    top: 160;
}

#f {
    top: 180;
}

#g {
    top: 300;
}

#h {
    top: 310;
}

#i {
    top: 320;
}

#j {
    top: 400;
}

#k {
    top: 120;
}

/* Display mega menu on hover */
.vertical-menu a:hover+.mega-menu,
.mega-menu:hover {
    /* display: block; */
}

/* Sub-menu styling */
.mega-menu .row {
    padding: 5px;
}

.mega-menu h5 {
    color: #343a40;
}

.mega-menu ul {
    list-style: none;
    padding: 0;
}

.mega-menu ul li a {
    text-decoration: none;
    color: #343a40;
    padding: 5px 0;
    display: block;
}

.mega-menu ul li a:hover {
    color: #007bff;
}

.menu-card {

    text-align: center;
    padding: 5px;
    border-radius: 5px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease;
}

.menu-card:hover {
    background-color: #f8f9fa;
}

.menu-card h6 {
    margin-top: 12px;
    color: black;

}

.menu-card i {
    font-size: 30px;
    margin-bottom: 10px;
}

.font-size {
    font-size: large;
    /* or you can use a specific size like 16px, 1.5em, etc. */
}


/* icon bergerak */


@keyframes wiggle {

    0%,
    100% {
        transform: rotate(0deg);
    }

    25% {
        transform: rotate(-20deg);
    }

    50% {
        transform: rotate(20deg);
    }

    75% {
        transform: rotate(-20deg);
    }
}

.nav-item a:hover .nav-icon {
    animation: wiggle 0.6s ease-in-out infinite;
}

/* batas */
</style>

<!-- pengaturan lebar sidebar, block hitam, space kesamping, bayangan putih (all in)-->

<style>
/* untuk block hitam */
.main-sidebar,
.main-sidebar::before {
    width: 70px !important;
}

.main-sidebar,
.main-sidebar:hover {
    width: 70px !important;
}

/* bayangan putih yg ada panahnya di atur disini */
.sidebar-mini .main-sidebar .nav-link,
.sidebar-mini-md .main-sidebar .nav-link,
.sidebar-mini-xs .main-sidebar .nav-link {
    width: calc(70px - 0.5rem * 2);
    transition: width ease-in-out 0.3s;
}

/* batas */

/* untuk space ke samping setelahnya */
@media (min-width: 768px) {

    body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .content-wrapper,
    body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-footer,
    body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-header {
        transition: margin-left 0.3s ease-in-out;
        margin-left: 60px;
    }
}

/* batas */
</style>

<!-- tutupannya -->

<aside class="main-sidebar sidebar-dark-primary elevation-4" style="overflow-y: visible;">
    <!-- Brand Logo -->
    <a href="{{ url('/') }}" class="brand-link" style="text-align: center">
        <img src="{{ url('/img/logo1.png') }}" alt="LookmanDjaja Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light"></span>
    </a>

    <!-- Sidebar -->
    <div class="vertical-menu">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="{{ url('/') }}" class="brand-link" data-bs-toggle="tooltip" title="Home">
                        <i class="nav-icon fas fa-home"></i>
                        <p>
                        </p>
                    </a>
                </li>
                <li class="nav-header"></li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Master">
                        <i class="nav-icon fas fa-database icon-white fa-beat"></i>
                        <p>
                        </p>
                    </a>

                    <!------- penambahan tampilan baru ------->


                    <div class="mega-menu" id="a">

                        <!-- penambahan judul di menu -->
                        <div class="row">
                            <div class="col-md-12">
                                <h3>MASTER</h3>
                                <hr style=" height: 5px;
                                    background-color: #333;
                                    border: none;
                                    margin: 20px 0; " />
                            </div>
                        </div>

                        <!-- batas -->

                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Barang', '{{ url('brg') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Barang</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Barang Baru', '{{ url('brg-baru') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-file icon-blue"></i>
                                        <h6>Barang Baru</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Customer', '{{ url('cust') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Customer</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Dft. Bank Pembayaran', '{{ url('bank-byr') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Daftar Bank Pembayaran</h6>
                                    </a>
                                </div>
                            </div> 
                        </div>

                        <!--  -->
                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Dft. Hari Raya', '{{ url('hraya') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Daftar Hari Raya</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Daftar Komisi', '{{ url('komisi') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-file icon-blue"></i>
                                        <h6>Daftar Komisi</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Daftar Supplier', '{{ url('sup') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Daftar Supplier</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Data Barang', '{{ url('dbrg') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Data Barang</h6>
                                    </a>
                                </div>
                            </div> 
                        </div>

                        <!--  -->
                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Data Barang 2', '{{ url('dbrg2') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Data Barang 2</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Suplier Food Center', '{{ url('sup-food-center') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-file icon-blue"></i>
                                        <h6>Data Suplier Food Center</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('EDC', '{{ url('edc') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>EDC</h6>
                                    </a>
                                </div>
                            </div> 
                        </div>

                    </div>
                </li>
                <!-----------------batas ------------------------>

                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Master">
                        <i class="nav-icon fas fa-database icon-white fa-beat"></i>
                        <p>
                        </p>
                    </a>

                    <!------- penambahan tampilan baru ------->


                    <div class="mega-menu" id="a">

                        <!-- penambahan judul di menu -->
                        <div class="row">
                            <div class="col-md-12">
                                <h3>MASTER </h3>
                                <hr style=" height: 5px;
                                    background-color: #333;
                                    border: none;
                                    margin: 20px 0; " />
                            </div>
                        </div>

                        <!-- batas -->

                        <!--  -->
                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Ganti Sub Item', '{{ url('gsub') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Ganti Sub Item</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Hapus Barang', '{{ url('hbrg') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-trash icon-blue"></i>
                                        <h6>Hapus Barang</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Invoice Agenda', '{{ url('invoice') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Invoice Agenda</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Keperluan Barang & Jasa', '{{ url('brg-jasa') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Keperluan Barang & Jasa</h6>
                                    </a>
                                </div>
                            </div> 
                        </div>

                        <!--  -->
                        <div class="row d-flex"> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Keperluan Barang & Jasa PA', '{{ url('brg-jasa-pa') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Keperluan Barang & Jasa Panitia Acara</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Margin Kasir', '{{ url('margin-ksr') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Margin Kasir</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Master Rekanan', '{{ url('rekanan') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Master Rekanan</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Suplier Sewa', '{{ url('sup-sewa') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Suplier Sewa</h6>
                                    </a>
                                </div>
                            </div> 
                        </div>

                        <!--  -->
                        <div class="row d-flex"> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Peng. Perubahan Brg', '{{ url('perubahan_brg') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Pengajuan Perubahan Barang</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Peng. Perubahan Suplier', '{{ url('perubahan_sup') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Pengajuan Perubahan Suplier</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Import SQL', '{{ url('import_sql') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Pengesahan Import SQL</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Report Penjualan Rekanan', '{{ url('rjual-rekanan') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Report Penjualan Rekanan</h6>
                                    </a>
                                </div>
                            </div> 
                        </div>

                        
                    </div>
                </li>


                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Master">
                        <i class="nav-icon fas fa-database icon-white fa-beat"></i>
                        <p>
                        </p>
                    </a>

                    <!------- penambahan tampilan baru ------->


                    <div class="mega-menu" id="a">

                        <!-- penambahan judul di menu -->
                        <div class="row">
                            <div class="col-md-12">
                                <h3>MASTER</h3>
                                <hr style=" height: 5px;
                                    background-color: #333;
                                    border: none;
                                    margin: 20px 0; " />
                            </div>
                        </div>

                        <!-- batas -->
                        <!--  -->
                        <div class="row d-flex"> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Sub', '{{ url('sub') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Sub</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Update Harga Beli', '{{ url('update-hrg-beli') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Update Harga Beli</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Usl. Brg Kasir Rekanan', '{{ url('usl-brg-rekanan') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Usl. Barang Kasir Rekanan</h6>
                                    </a>
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Usl. Brg Kasir Td', '{{ url('usl-brg-td') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Usl. Barang Kasir Td</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Usl. Brg Kasir Hf', '{{ url('usl-brg-hf') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Usl. Barang Kasir Happy Fresh</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Greeting Kasir', '{{ url('greet') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Greeting Kasir</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Export Import SQL', '{{ url('expim') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-blue"></i>
                                        <h6>Export-Import SQL</h6>
                                    </a>
                                </div>
                            </div>
                        </div>

                        
                    </div>
                </li>


                <li class="nav-item">
                    @if (Auth::user()->divisi == 'pembelian' ||
                    Auth::user()->divisi == 'gudang' ||
                    Auth::user()->divisi == 'outlet' ||
                    Auth::user()->divisi == 'programmer')
                    <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Transaksi Point">
                        <i class="nav-icon fas fa-hand-holding-heart icon-pink"></i>
                        <p>
                        </p>
                    </a>
                    <div class="mega-menu" id="b">

                        <!-- penambahan judul di menu -->
                        <div class="row">
                            <div class="col-md-12">
                                <h3>TRANSAKSI</h3>
                                <hr style=" height: 5px;
                                        background-color: #333;
                                        border: none;
                                        margin: 20px 0; " />
                            </div>
                        </div>
                        <!-- batas -->
                        <div class="row">
                            @if ( Auth::user()->divisi == 'programmer')
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Psn Outlet', '{{ url('psn-outlet') }}')">
                                        <i style="margin-left:-15px;font-size: 40px;"
                                            class="nav-icon fas fa-store icon-orange"></i>
                                        <h6>Pesanan Khusu Outlet</h6>
                                    </a>
                                </div>
                            </div>
                            @endif

                            @if ( Auth::user()->divisi == 'programmer')
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Order PBL', '{{ url('orderpbl?flagz=JL&golz=DC') }}')">
                                        <i style="margin-left:-15px;font-size: 40px;"
                                            class="nav-icon fas fa-warehouse icon-orange"></i>
                                        <h6>Order Ke Pembelian</h6>
                                    </a>
                                </div>
                            </div>
                            @endif

                            @if ( Auth::user()->divisi == 'programmer')
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Order PBL Tanpa DC', '{{ url('orderpbl?flagz=JL&golz=TD') }}')">
                                        <i style="margin-left:-15px;font-size: 40px;"
                                            class="nav-icon fas fa-file icon-orange"></i>
                                        <h6>Order Ke Pembelian (Tanpa DC)</h6>
                                    </a>
                                </div>
                            </div>
                            @endif
                            
                            @if ( Auth::user()->divisi == 'programmer')
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Koreksi PBL', '{{ url('orderpjl?flagz=BL') }}')">
                                        <i style="margin-left:-15px;font-size: 40px;"
                                            class="nav-icon fas fa-file icon-orange"></i>
                                        <h6>Koreksi Pembelian</h6>
                                    </a>
                                </div>
                            </div>
                            @endif

                            @if ( Auth::user()->divisi == 'programmer')
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Order Cust', '{{ url('ordercust') }}')">
                                        <i style="margin-left:-15px;font-size: 40px;"
                                            class="nav-icon fas fa-file icon-orange"></i>
                                        <h6>Orderan Pelanggan</h6>
                                    </a>
                                </div>
                            </div>
                            @endif

                            @if ( Auth::user()->divisi == 'programmer')
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Order Manual', '{{ url('orderman') }}')">
                                        <i style="margin-left:-15px;font-size: 40px;"
                                            class="nav-icon fas fa-file icon-orange"></i>
                                        <h6>Orderan Manual (Sales)</h6>
                                    </a>
                                </div>
                            </div>
                            @endif

                            @if ( Auth::user()->divisi == 'programmer')
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Order Kue Basah', '{{ url('ordkode8') }}')">
                                        <i style="margin-left:-15px;font-size: 40px;"
                                            class="nav-icon fas fa-file icon-orange"></i>
                                        <h6>Order Kue Basah (Kode 8)</h6>
                                    </a>
                                </div>
                            </div>
                            @endif

                            @if ( Auth::user()->divisi == 'programmer')
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Ubah Harga VIP', '{{ url('ubahhrgvip?flagz=PV') }}')">
                                        <i style="margin-left:-15px;font-size: 40px;"
                                            class="nav-icon fas fa-file icon-orange"></i>
                                        <h6>Pelaksanaan Perubahan Harga VIP</h6>
                                    </a>
                                </div>
                            </div>
                            @endif

                            @if ( Auth::user()->divisi == 'programmer')
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Rkp Label Harian', '{{ url('rkplabel') }}')">
                                        <i style="margin-left:-15px;font-size: 40px;"
                                            class="nav-icon fas fa-file icon-orange"></i>
                                        <h6>Rekap Label Harian</h6>
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </li>

                <!----------------------------------------->

                

                <li class="nav-item">
                    @if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
                    <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Laporan Master">
                        <i class="nav-icon fas fa-book icon-yellow"></i>
                        <p>
                        </p>
                    </a>
                    <div class="mega-menu" id="d">
                        <div class="row">
                            <div class="col-md-12">
                                <h3>LAPORAN</h3>
                                <hr style=" height: 5px;
                                        background-color: #333;
                                        border: none;
                                        margin: 20px 0; " />
                            </div>
                        </div>

                        <!-- batas -->

                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
                                    <a href="javascript:addTab('Kartu Stock', '{{ url('rkarstk') }}')">

                                        <i style="margin-left:-5px;font-size: 40px;"
                                            class="nav-icon fas fa-receipt icon-red"></i>
                                        <h6>Kartu Stock</h6>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row d-flex">
                            
                        </div>
                        <div class="row d-flex"> 
                            
                        </div>
                    </div>
                    @endif
                </li>

                <li class="nav-item">
                    
                </li>
                <li class="nav-item">
                    
                </li>
                

                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="tooltip" title="UTILITY">
                        <i class="nav-icon fas fa-plus icon-yellow"></i>
                        <p>
                        </p>
                    </a>
                    <div class="mega-menu" id="j">
                        <!-- penambahan judul di menu -->
                        <div class="row">
                            <div class="col-md-12">
                                <h3>UTILITY</h3>
                                <hr style=" height: 5px;
                                    background-color: #333;
                                    border: none;
                                    margin: 20px 0; " />
                            </div>
                        </div>

                        <!-- batas -->

                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="menu-card" style="">
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#periodeModal"
                                        id="periode">
                                        <i style="margin-left:-5px;font-size: 40px;"
                                            class="nav-icon fas fa-calendar icon-purple"></i>
                                        <h6>Ganti Periode</h6>
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="menu-card" style="">
                                    <a href="javascript:addTab('Posted', '{{ url('posted/index-posting') }}')">
                                        <!-- <i class="nav-icon fas fa-crop icon-orange"></i> -->
                                        <i style="margin-left:-5px;font-size: 40px;"
                                            class="nav-icon fas fa-bezier-curve icon-pink"></i>
                                        <h6>Posted</h6>
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="menu-card" style="">
                                    <a href="javascript:addTab('Posted', '{{ url('ambildata') }}')">
                                        <!-- <i class="nav-icon fas fa-crop icon-orange"></i> -->
                                        <i style="margin-left:-5px;font-size: 40px;"
                                            class="nav-icon fas fa-bezier-curve icon-pink"></i>
                                        <h6>Ambil Data</h6>
                                    </a>
                                </div>
                            </div>

                            
                            <!----- batas ----->
                </li>
                @if (Auth::user()->hasRole('superadmin'))
                <li class="nav-header">User</li>
                <li class="nav-item" data-bs-toggle="tooltip" title="USER">
                    <!-- href di ganti dengan onclick -->
                    <a onclick="addTab('User', '{{ url('/user/manage') }}')" href="#" class="nav-link">
                        <i class="nav-icon fas fa-users icon-orange"></i>
                        <p>
                        </p>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
