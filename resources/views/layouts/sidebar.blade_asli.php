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
        <img src="{{ url('/img/company.jpg') }}" alt="LookmanDjaja Logo" class="brand-image img-circle elevation-3"
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
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Home">
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
                                <hr
                                    style=" height: 5px;
                                    background-color: #333;
                                    border: none;
                                    margin: 20px 0; " />
                            </div>
                        </div>

                        <!-- batas -->

                        <div class="row d-flex">
                            <div class="col-md-3">
                                <!-- kalau sub menu, di kasih warna pinggirannya. style:"border" -->
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <!-- batas -->
                                    <a href="javascript:addTab('Supplier', '{{ url('sup') }}')">
                                        <!-- <i class="nav-icon far fa-user fa-10x icon-purple"></i> -->
                                        <i style="margin-left:-5px;font-size: 40px;"
                                            class="nav-icon fas fa-user icon-blue"></i>
                                        <h6>Vendor</h6>
                                    </a>
                                </div>

                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                    <a href="javascript:addTab('Customer', '{{ url('cust') }}')">
                                        <!-- <i class="nav-icon fas fa-users icon-yellow" style="text-align: center;"></i> -->
                                        <i style="margin-left:-25px;font-size: 40px;"
                                            class="nav-icon fas fa-users icon-blue"></i>
                                        <h6>Customer</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Barang Hijau', '{{ url('brg') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-orange"></i>
                                        <h6>Barang Hijau</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                    <a href="javascript:addTab('Barang', '{{ url('vbrg') }}')">
                                        <i style="margin-left:-10px;font-size: 40px;"
                                            class="nav-icon fas fa-cube icon-orange"></i>
                                        <h6>Barang</h6>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                    <a href="javascript:addTab('Z Supplier', '{{ url('zsup') }}')">
                                        <!-- <i class="nav-icon fas fa-anchor icon-blue" style="text-align: center;"></i> -->
                                        <i style="margin-left:-10x;font-size: 40px;"
                                            class="nav-icon fas fa-user-tie icon-green"></i>
                                        <h6>Z-Supplier</h6>
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                    <a href="javascript:addTab('Member', '{{ url('member') }}')">
                                        <!-- <i class="nav-icon fas fa-anchor icon-blue" style="text-align: center;"></i> -->
                                        <i style="margin-left:-10x;font-size: 40px;"
                                            class="nav-icon fas fa-folder icon-green"></i>
                                        <h6>Member</h6>
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                    <a href="javascript:addTab('Gudang', '{{ url('gdg') }}')">
                                        <!-- <i class="nav-icon fas fa-anchor icon-blue" style="text-align: center;"></i> -->
                                        <i style="margin-left:-10x;font-size: 40px;"
                                            class="nav-icon fas fa-warehouse icon-green"></i>
                                        <h6>Gudang</h6>
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="menu-card" style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                    <a href="javascript:addTab('Compan', '{{ url('compan') }}')">
                                        <!-- <i class="nav-icon fas fa-anchor icon-blue" style="text-align: center;"></i> -->
                                        <i style="margin-left:-10x;font-size: 40px;"
                                            class="nav-icon fas fa-city icon-green"></i>
                                        <h6>Compan</h6>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-----------------batas ------------------------>


                <li class="nav-item">
                    @if (
                        (Auth::user()->divisi == 'pembelian') |
                            (Auth::user()->divisi == 'gudang') |
                            (Auth::user()->divisi == 'outlet') |
                            (Auth::user()->divisi == 'programmer'))
                        <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Transaksi Point">
                            <i class="nav-icon fas fa-hand-holding-heart icon-pink"></i>
                            <p>
                            </p>
                        </a>
                        <div class="mega-menu" id="b">

                            <!-- penambahan judul di menu -->
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>TRANSAKSI POINT</h3>
                                    <hr
                                        style=" height: 5px;
                                    background-color: #333;
                                    border: none;
                                    margin: 20px 0; " />
                                </div>
                            </div>
                            <!-- batas -->
                            <div class="row">
                                @if ((Auth::user()->divisi == 'gudang') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a
                                                href="javascript:addTab('Pre Purchase', '{{ url('pp?flagz=PP&golz=J') }}')">
                                                <i style="margin-left:-15px;font-size: 40px;"
                                                    class="nav-icon fas fa-store icon-orange"></i>
                                                <h6>Pre Purchase</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'pembelian') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a
                                                href="javascript:addTab('Purchase Order', '{{ url('po?flagz=PO&golz=J') }}')">
                                                <i style="margin-left:-15px;font-size: 40px;"
                                                    class="nav-icon fas fa-cart-plus icon-orange"></i>
                                                <h6>Purchase Order</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'pembelian') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a
                                                href="javascript:addTab('Purchase Order Customer', '{{ url('po?flagz=PO&golz=C') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-briefcase icon-orange"></i>
                                                <h6>PO Customer</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'gudang') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a href="javascript:addTab('Pembelian', '{{ url('beli?flagz=BL') }}')">
                                                <i style="margin-left:-15px;font-size: 40px;"
                                                    class="nav-icon fas fa-check icon-orange"></i>
                                                <h6>Penerimaan Barang</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'gudang') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a
                                                href="javascript:addTab('Retur Pembelian', '{{ url('beli?flagz=RB') }}')">
                                                <!-- <i class="nav-icon fas fa-store icon-white"></i> -->
                                                <i style="margin-left:-15px;font-size: 40px;"
                                                    class="nav-icon fas fa-retweet icon-orange"></i>
                                                <h6>Retur Pembelian</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'admin') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a
                                                href="javascript:addTab('Transaksi Hutang', '{{ url('utbeli?flagz=TH&golz=J') }}')">
                                                <!-- <i class="nav-icon fas fa-crop icon-orange"></i> -->
                                                <i style="margin-left: 5px;font-size: 40px;"
                                                    class="nav-icon fas fa-receipt icon-orange"></i>
                                                <h6>Transaksi Hutang</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'gudang') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a href="javascript:addTab('Stock Kirim', '{{ url('kirim?flagz=KM') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-truck icon-orange"></i>
                                                <h6>Stock Kirim</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'admin') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a
                                                href="javascript:addTab('Pembayaran Hutang', '{{ url('hut?flagz=B&golz=J') }}')">
                                                <!-- <i class="nav-icon fas fa-anchor icon-blue" style="text-align: center;"></i> -->
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-cash-register icon-orange"></i>
                                                <h6>Pembayaran Hutang</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'outlet') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a
                                                href="javascript:addTab('Tukar Barang', '{{ url('tukar?flagz=TK') }}')">
                                                <i style="margin-left:-15px;font-size: 40px;"
                                                    class="nav-icon fas fa-money-bill icon-orange"></i>
                                                <h6>Tukar Barang</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'outlet') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a href="javascript:addTab('Transaksi Manual', '{{ url('manual') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-file icon-orange"></i>
                                                <h6>Manual</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'outlet') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a href="javascript:addTab('Stock Minta', '{{ url('minta?flagz=MT') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-hand-holding icon-orange"></i>
                                                <h6>Stock Minta</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'outlet') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a
                                                href="javascript:addTab('Stock Terima', '{{ url('terima?flagz=TE') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-handshake icon-orange"></i>
                                                <h6>Stock Terima</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'superadmin') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a href="javascript:addTab('Sales Order', '{{ url('so') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-plus icon-orange"></i>
                                                <h6>Sales Order</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if (Auth::user()->divisi == 'programmer')
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                            <a href="javascript:addTab('Stock Poin', '{{ url('stockb?flagz=KZ') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-box-open icon-orange"></i>
                                                <h6>Stock Poin</h6>
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
                    @if ((Auth::user()->divisi == 'gudang') | (Auth::user()->divisi == 'outlet') | (Auth::user()->divisi == 'programmer'))
                        <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Koreksi Stock">
                            <i class="nav-icon fas fa-pen-nib icon-green"></i>
                            <p>
                            </p>
                        </a>
                        <div class="mega-menu" id="k">

                            <div class="row">
                                <div class="col-md-12">
                                    <h3>TRANSAKSI BARANG HIJAU</h3>
                                    <hr style=" height: 5px; background-color: #333; border: none; margin: 20px 0; " />
                                </div>
                            </div>

                            <div class="row d-flex">
                                @if ((Auth::user()->divisi == 'outlet') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                            <a
                                                href="javascript:addTab('Ambil Barang', '{{ url('ambil?flagz=AM') }}')">
                                                <i style="margin-left:-15px;font-size: 40px;"
                                                    class="nav-icon fas fa-hand-lizard icon-green"></i>
                                                <h6>Ambil Barang</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'outlet') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                            <a
                                                href="javascript:addTab('Kartu Stock Barang Hijau', '{{ url('rkarstk') }}')">

                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-cart-plus icon-green"></i>
                                                <h6>Kartu Stock Barang Hijau</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'gudang') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                            <a href="javascript:addTab('Hadiah Masuk', '{{ url('hdh?flagz=MA') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-download icon-green"></i>
                                                <h6>Hadiah Masuk</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'gudang') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                            <a
                                                href="javascript:addTab('Stock Label Hijau', '{{ url('stocka?flagz=KB') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-paste icon-green"></i>
                                                <h6>Stock Label Hijau</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if ((Auth::user()->divisi == 'gudang') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                            <a
                                                href="javascript:addTab('Stock Kirim Hijau', '{{ url('hkirim?flagz=KH') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-truck icon-green"></i>
                                                <h6>Stock Kirim Hijau</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if ((Auth::user()->divisi == 'superadmin') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                            <a
                                                href="javascript:addTab('Stock Terima Hijau', '{{ url('hterima?flagz=TA') }}')">
                                                <i style="margin-left:-5px;font-size: 40px;"
                                                    class="nav-icon fas fa-handshake icon-green"></i>
                                                <h6>Stock Terima Hijau</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if ((Auth::user()->divisi == 'gudang') | (Auth::user()->divisi == 'programmer'))
                                    <div class="col-md-3">
                                        <div class="menu-card"
                                            style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                            <a href="javascript:addTab('Promo', '{{ url('lbhijau?typez=H') }}')">
                                                <i style="margin-left:-5px; font-size: 40px;"
                                                    class="nav-icon fas fa-bell icon-green"></i>
                                                <h6>Promo</h6>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    @endif
                </li>

                <li class="nav-item">
                    @if ((Auth::user()->divisi == 'superadmin') | (Auth::user()->divisi == 'programmer'))
                        <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Laporan Master">
                            <i class="nav-icon fas fa-book icon-yellow"></i>
                            <p>
                            </p>
                        </a>
                        <div class="mega-menu" id="d">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>LAPORAN MASTER</h3>
                                    <hr
                                        style=" height: 5px;
                                        background-color: #333;
                                        border: none;
                                        margin: 20px 0; " />
                                </div>
                            </div>

                            <!-- batas -->

                            <div class="row d-flex">
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffd9d9;">
                                        <a href="javascript:addTab('Report Barang Hijau', '{{ url('rbrg') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-cubes icon-red"></i>
                                            <h6>Barang Hijau</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffd9d9;">
                                        <a href="javascript:addTab('Report Barang', '{{ url('rvbrg') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-cubes icon-red"></i>
                                            <h6>Barang</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffd9d9;">
                                        <a href="javascript:addTab('Report Suplier', '{{ url('rsup') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-user-tie icon-red"></i>
                                            <h6>Suplier</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffd9d9;">
                                        <a href="javascript:addTab('Report Customer', '{{ url('rcust') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-users icon-red"></i>
                                            <h6>Customer</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffd9d9;">
                                        <a href="javascript:addTab('Report VIP', '{{ url('rvip') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-envelope icon-red"></i>
                                            <h6>VIP</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </li>

                <li class="nav-item">
                    @if (Auth::user()->divisi == 'programmer')
                        <a href="#" class="nav-link" data-bs-toggle="tooltip"
                            title="Laporan Barang Tukar Point">
                            <i class="nav-icon fas fa-book icon-yellow"></i>
                            <p>
                            </p>
                        </a>
                        <div class="mega-menu" id="e">
                            <!-- penambahan judul di menu -->
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>LAPORAN BARANG TUKAR POINT</h3>
                                    <hr
                                        style=" height: 5px;
                      background-color: #333;
                      border: none;
                      margin: 20px 0; " />
                                </div>
                            </div>
                            <div class="row d-flex">
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffe6ff;">
                                        <a href="javascript:addTab('Report Purchase Order', '{{ url('rpo') }}')">
                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-cart-plus icon-pink"></i>
                                            <h6>Purchase Order</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffe6ff;">
                                        <a href="javascript:addTab('Report Pembelian', '{{ url('rbeli') }}')">
                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-store icon-pink"></i>
                                            <h6>Pembelian</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                        <a
                                            href="javascript:addTab('Report Pembayaran Hutang', '{{ url('rhut') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-industry icon-orange"></i>
                                            <h6>Pembayaran Hutang</h6>
                                        </a>
                                    </div>
                                </div>


                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                        <a href="javascript:addTab('Report Sisa Hutang', '{{ url('rsisahut') }}')">
                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-industry icon-orange"></i>
                                            <h6>Sisa Hutang</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="row d-flex">
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ebd9ff;">
                                        <a href="javascript:addTab('Report Tukar', '{{ url('rtukar') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-briefcase icon-purple"></i>
                                            <h6>Tukar</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </li>
                <li class="nav-item">
                    @if (Auth::user()->divisi == 'programmer')
                        <a href="#" class="nav-link" data-bs-toggle="tooltip"
                            title="Laporan Barang Tukar Struck">
                            <i class="nav-icon fas fa-book icon-yellow"></i>
                            <p>
                            </p>
                        </a>
                        <div class="mega-menu" id="f">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>LAPORAN BARANG TUKAR STRUCK</h3>
                                    <hr
                                        style=" height: 5px;
                        background-color: #333;
                        border: none;
                        margin: 20px 0; " />
                                </div>
                            </div>
                            <div class="row d-flex">

                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ebd9ff;">
                                        <a
                                            href="javascript:addTab('Report Hadiah Masuk', '{{ url('rHadiahMasuk') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-store icon-purple"></i>
                                            <h6>Lap Hadiah Masuk</h6>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ebd9ff;">
                                        <a href="javascript:addTab('Report Ambil', '{{ url('rambil') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-cash-register icon-purple"></i>
                                            <h6>Ambil</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                    @endif
                </li>
                <li class="nav-item">
                    @if (Auth::user()->divisi == 'programmer')
                        <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Kartu">
                            <i class="nav-icon fas fa-book icon-red"></i>
                            <p>
                            </p>
                        </a>
                        <div class="mega-menu" id="e">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>KARTU</h3>
                                    <hr
                                        style=" height: 5px;
                      background-color: #333;
                      border: none;
                      margin: 20px 0; " />
                                </div>
                            </div>
                            <div class="row d-flex">
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                        <a
                                            href="javascript:addTab('Kartu Stock Barang', '{{ url('rkarstk_vbrg') }}')">
                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-cart-plus icon-green"></i>
                                            <h6>Kartu Stock Barang</h6>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                        <a href="javascript:addTab('Kartu Hutang', '{{ url('rkartuh') }}')">
                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-store icon-green"></i>
                                            <h6>Kartu Hutang</h6>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ccffd2;">
                                        <a href="javascript:addTab('Kartu Poin', '{{ url('rkartu_poin') }}')">
                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-store icon-green"></i>
                                            <h6>Kartu Poin</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </li>
                @if (Auth::user()->divisi == 'programmer')
                    <li class="nav-header"></li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Account">
                            <i class="nav-icon fas fa-location-arrow icon-blue"></i>
                            <p>
                            </p>
                        </a>
                        <div class="mega-menu"id="g">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>MASTER FINANCIAL</h3>
                                    <hr
                                        style=" height: 5px;
                                        background-color: #333;
                                        border: none;
                                        margin: 20px 0; " />
                                </div>
                            </div>
                            <div class="row d-flex">
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffe0ba;">
                                        <a href="javascript:addTab('Account', '{{ url('account') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-user icon-orange"></i>
                                            <h6>Account</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                @endif
                </li>
                <li class="nav-item">
                    @if (Auth::user()->divisi == 'programmer')
                        <a href="#" class="nav-link" data-bs-toggle="tooltip" title="KAS, BANK, MEMO">
                            <i class="nav-icon fas fa-university icon-green"></i>
                            <p>
                            </p>
                        </a>
                        <div class="mega-menu" id="h">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>TRANSAKSI FINANCIAL</h3>
                                    <hr
                                        style=" height: 5px;
                                    background-color: #333;
                                    border: none;
                                    margin: 20px 0; " />
                                </div>
                            </div>
                            <div class="row d-flex">
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffd9d9;">
                                        <a href="javascript:addTab('Kas Masuk','{{ url('kas?flagz=BKM') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-stamp icon-purple"></i>
                                            <h6>Kas Masuk</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffd9d9;">
                                        <a href="javascript:addTab('Kas Keluar','{{ url('kas?flagz=BKK') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-credit-card icon-purple"></i>
                                            <h6>Kas Keluar</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#ffd9d9;">
                                        <a href="javascript:addTab('Bank Masuk','{{ url('bank?flagz=BBM') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-money-check icon-purple"></i>
                                            <h6>Bank Masuk</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                        <a href="javascript:addTab('Bank Keluar','{{ url('bank?flagz=BBK') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-money-bill icon-blue"></i>
                                            <h6>Bank Keluar</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="menu-card"
                                        style="border:1px solid #aabbcc; background-color:#e3f1fc;">
                                        <a href="javascript:addTab('Penyesuaian','{{ url('memo?flagz=M') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-receipt icon-blue"></i>
                                            <h6>Penyesuaian</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                    @endif
                </li>

                <li class="nav-item">
                    @if (Auth::user()->divisi == 'programmer')
                        <a href="#" class="nav-link" data-bs-toggle="tooltip" title="Laporan">
                            <i class="nav-icon fas fa-print icon-purple"></i>
                            <p>
                            </p>
                        </a>
                        <div class="mega-menu" id="i">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>LAPORAN FINANCIAL</h3>
                                    <hr
                                        style=" height: 5px;
                      background-color: #333;
                      border: none;
                      margin: 20px 0; " />
                                </div>
                            </div>
                            <div class="row d-flex">
                                <div class="col-md-3">
                                    <div class="menu-card" style="">
                                        <a href="javascript:addTab('Journal Kas', '{{ url('rkas') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-newspaper icon-purple"></i>
                                            <h6>Journal Kas</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="menu-card" style="">
                                        <a href="javascript:addTab('Journal Bank', '{{ url('rbank') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-bookmark icon-blue"></i>
                                            <h6>Journal Bank</h6>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="menu-card" style="">
                                        <a href="javascript:addTab('Journal Penyesuaian', '{{ url('rmemo') }}')">

                                            <i style="margin-left:-5px;font-size: 40px;"
                                                class="nav-icon fas fa-receipt icon-red"></i>
                                            <h6>Journal Penyesuaian</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
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
                                <hr
                                    style=" height: 5px;
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

                            <div class="col-md-3">
                                <div class="menu-card" style="">
                                    <a href="javascript:addTab('Posted', '{{ url('ambildatabiru') }}')">
                                        <!-- <i class="nav-icon fas fa-crop icon-orange"></i> -->
                                        <i style="margin-left:-5px;font-size: 40px;"
                                            class="nav-icon fas fa-bezier-curve icon-pink"></i>
                                        <h6>Ambil Data Biru</h6>
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
