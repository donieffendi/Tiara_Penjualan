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

	/* Icon animations */
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

	/* Custom color classes */
	.text-purple {
		color: #6f42c1 !important;
	}

	.icon-white {
		color: #ffffff !important;
	}

	.icon-blue {
		color: #007bff !important;
	}

	.icon-orange {
		color: #fd7e14 !important;
	}

	.icon-red {
		color: #dc3545 !important;
	}

	.icon-pink {
		color: #e83e8c !important;
	}

	.icon-yellow {
		color: #ffc107 !important;
	}

	.icon-purple {
		color: #6f42c1 !important;
	}
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
		<img src="{{ url('/img/logo1.png') }}" alt="LookmanDjaja Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
		<span class="brand-text font-weight-light"></span>
	</a>

	<!-- Sidebar -->
	<div class="vertical-menu">
		<!-- Sidebar user panel (optional) -->
		<div class="user-panel d-flex mb-3 mt-3 pb-3">
		</div>

		<!-- Sidebar Menu -->
		<nav class="mt-2">
			<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
				<!-- Add icons to the links using the .nav-icon class
															with font-awesome or any other icon font library -->
				<li class="nav-item">
					<a href="{{ url('/') }}" class="brand-link" data-bs-toggle="tooltip" title="Home">
						<i class="nav-icon fas fa-home text-white"></i>
						<p></p>
					</a>
				</li>
				<li class="nav-header"></li>
				<!-- MASTER MENU -->
				<li class="nav-item">
					<a href="#" class="nav-link" data-bs-toggle="tooltip" title="Master">
						<i class="nav-icon fas fa-database text-info fa-beat"></i>
						<p></p>
					</a>
					<div class="mega-menu" id="a">

						<div class="row">
							<div class="col-md-12">
								<h3 style="color: #17a2b8;">MASTER</h3>
								<hr style="height: 3px; background-color: #17a2b8; border: none; margin: 15px 0;" />
							</div>
						</div>
						<div class="row d-flex">
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Barang', '{{ url('brg') }}')">
										<i style="font-size: 40px;" class="fas fa-boxes text-info"></i>
										<h6>Barang</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Barang Baru', '{{ url('brg-baru') }}')">
										<i style="font-size: 40px;" class="fas fa-box-open text-info"></i>
										<h6>Barang Baru</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Customer', '{{ url('cust') }}')">
										<i style="font-size: 40px;" class="fas fa-users text-info"></i>
										<h6>Customer</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Dft. Bank Pembayaran', '{{ url('bank-byr') }}')">
										<i style="font-size: 40px;" class="fas fa-university text-info"></i>
										<h6>Daftar Bank Pembayaran</h6>
									</a>
								</div>
							</div>
						</div>

						<div class="row d-flex">
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Dft. Hari Raya', '{{ url('hraya') }}')">
										<i style="font-size: 40px;" class="fas fa-calendar-alt text-info"></i>
										<h6>Daftar Hari Raya</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Daftar Komisi', '{{ url('komisi') }}')">
										<i style="font-size: 40px;" class="fas fa-percentage text-info"></i>
										<h6>Daftar Komisi</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Daftar Supplier', '{{ url('sup') }}')">
										<i style="font-size: 40px;" class="fas fa-truck text-info"></i>
										<h6>Daftar Supplier</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Data Barang FC', '{{ url('dbrg') }}')">
										<i style="font-size: 40px;" class="fas fa-database text-info"></i>
										<h6>Data Barang Food Center</h6>
									</a>
								</div>
							</div>
						</div>
						<div class="row d-flex">
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Data Barang Kasir', '{{ url('dbrg2') }}')">
										<i style="font-size: 40px;" class="fas fa-database text-info"></i>
										<h6>Data Kasir</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Suplier Food Center', '{{ url('sup-food-center') }}')">
										<i style="font-size: 40px;" class="fas fa-utensils text-info"></i>
										<h6>Suplier Food Center</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('EDC', '{{ url('edc') }}')">
										<i style="font-size: 40px;" class="fas fa-credit-card text-info"></i>
										<h6>EDC</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Ganti Sub Item', '{{ url('gsub') }}')">
										<i style="font-size: 40px;" class="fas fa-exchange-alt text-info"></i>
										<h6>Ganti Sub Item</h6>
									</a>
								</div>
							</div>
						</div>
						<div class="row d-flex">
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Hapus Barang LK', '{{ url('hbrg') }}')">
										<i style="font-size: 40px;" class="fas fa-trash-alt text-info"></i>
										<h6>Hapus Barang -Lama Kosong</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Hapus Barang', '{{ url('hbrg2') }}')">
										<i style="font-size: 40px;" class="fas fa-trash-alt text-info"></i>
										<h6>Hapus Barang</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Invoice Agenda', '{{ url('invoice') }}')">
										<i style="font-size: 40px;" class="fas fa-file-invoice text-info"></i>
										<h6>Invoice Agenda</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Keperluan Barang & Jasa', '{{ url('brg-jasa') }}')">
										<i style="font-size: 40px;" class="fas fa-clipboard-list text-info"></i>
										<h6>Keperluan Barang & Jasa</h6>
									</a>
								</div>
							</div>
						</div>
						<div class="row d-flex">
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Keperluan Barang & Jasa PA', '{{ url('brg-jasa-pa') }}')">
										<i style="font-size: 40px;" class="fas fa-tasks text-info"></i>
										<h6>Keperluan Barang & Jasa PA</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Margin Kasir', '{{ url('margin-ksr') }}')">
										<i style="font-size: 40px;" class="fas fa-chart-line text-info"></i>
										<h6>Margin Kasir</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Master Rekanan', '{{ url('rekanan') }}')">
										<i style="font-size: 40px;" class="fas fa-handshake text-info"></i>
										<h6>Master Rekanan</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Suplier Sewa', '{{ url('sup-sewa') }}')">
										<i style="font-size: 40px;" class="fas fa-building text-info"></i>
										<h6>Suplier Sewa</h6>
									</a>
								</div>
							</div>
						</div>
						<div class="row d-flex">
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Peng. Perubahan Brg KMS', '{{ url('perkem') }}')">
										<i style="font-size: 40px;" class="fas fa-edit text-info"></i>
										<h6>Pengajuan Perubahan Barang KMS</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Peng. Perubahan Suplier', '{{ url('perubahan_sup') }}')">
										<i style="font-size: 40px;" class="fas fa-user-edit text-info"></i>
										<h6>Pengajuan Perubahan Suplier</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Import SQL', '{{ url('import_sql') }}')">
										<i style="font-size: 40px;" class="fas fa-file-import text-info"></i>
										<h6>Pengesahan Import SQL</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Report Penjualan Rekanan', '{{ url('rjual-rekanan') }}')">
										<i style="font-size: 40px;" class="fas fa-chart-bar text-info"></i>
										<h6>Report Penjualan Rekanan</h6>
									</a>
								</div>
							</div>
						</div>
						<div class="row d-flex">
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Sub', '{{ url('sub') }}')">
										<i style="font-size: 40px;" class="fas fa-layer-group text-info"></i>
										<h6>Sub</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Update Harga Beli', '{{ url('update-hrg-beli') }}')">
										<i style="font-size: 40px;" class="fas fa-tags text-info"></i>
										<h6>Update Harga Beli</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Usl. Brg Kasir Rekanan', '{{ url('usl-brg-rekanan') }}')">
										<i style="font-size: 40px;" class="fas fa-cart-plus text-info"></i>
										<h6>Usl. Barang Kasir Rekanan</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Usl. Brg Kasir Td', '{{ url('usl-brg-td') }}')">
										<i style="font-size: 40px;" class="fas fa-shopping-cart text-info"></i>
										<h6>Usl. Barang Kasir Td</h6>
									</a>
								</div>
							</div>
						</div>
						<div class="row d-flex">
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Usl. Brg Kasir Hf', '{{ url('usl-brg-hf') }}')">
										<i style="font-size: 40px;" class="fas fa-smile text-info"></i>
										<h6>Usl. Barang Kasir Happy Fresh</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Greeting Kasir', '{{ url('greet') }}')">
										<i style="font-size: 40px;" class="fas fa-hand-peace text-info"></i>
										<h6>Greeting Kasir</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Export Import SQL', '{{ url('expim') }}')">
										<i style="font-size: 40px;" class="fas fa-exchange-alt text-info"></i>
										<h6>Export-Import SQL</h6>
									</a>
								</div>
							</div>
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Ubah Tanggal SO', '{{ url('sog') }}')">
										<i style="font-size: 40px;" class="fas fa-exchange-alt text-info"></i>
										<h6>Ubah Tanggal SO</h6>
									</a>
								</div>
							</div>
						</div>
						<div class="row d-flex">
							<div class="col-md-3">
								<div class="menu-card" style="border:1px solid #17a2b8; background-color:#d1ecf1;">
									<a href="javascript:addTab('Export Manual SO', '{{ url('exso') }}')">
										<i style="font-size: 40px;" class="fas fa-exchange-alt text-info"></i>
										<h6>Export Manual SO</h6>
									</a>
								</div>
							</div>
						</div>
					</div>
				</li>

				<!-- TRANSAKSI MENU -->
				<li class="nav-item">
					@if (Auth::user()->divisi == 'pembelian' ||
							Auth::user()->divisi == 'gudang' ||
							Auth::user()->divisi == 'outlet' ||
							Auth::user()->divisi == 'programmer')
						<a href="#" class="nav-link" data-bs-toggle="tooltip" title="Transaksi">
							<i class="nav-icon fas fa-exchange-alt text-warning fa-beat"></i>
							<p></p>
						</a>
						<div class="mega-menu" id="b">
							<div class="row">
								<div class="col-md-12">
									<h3 style="color: #ffc107;">TRANSAKSI</h3>
									<hr style="height: 3px; background-color: #ffc107; border: none; margin: 15px 0;" />
								</div>
							</div>
							<div class="row d-flex">
								@if (Auth::user()->divisi == 'pembelian' ||
										Auth::user()->divisi == 'gudang' ||
										Auth::user()->divisi == 'outlet' ||
										Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:addTab('Posting Kasir', '{{ url('tpostingkasir') }}')">
												<i style="font-size: 40px;" class="fas fa-cash-register text-warning"></i>
												<h6>Posting Kasir</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'pembelian' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:addTab('Posting Report', '{{ url('tpostingreport') }}')">
												<i style="font-size: 40px;" class="fas fa-file-alt text-warning"></i>
												<h6>Posting Report</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'pembelian' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:addTab('Posting Akhir Bulan', '{{ url('tpostingakhirbulan') }}')">
												<i style="font-size: 40px;" class="fas fa-calendar-check text-warning"></i>
												<h6>Posting Akhir Bulan</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'pembelian' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:openOrderKepembelian('Rencana Order Ke Pembelian', '{{ url('TOrderKepembelian/BIASA') }}')">
												<i style="font-size: 40px;" class="fas fa-shopping-basket text-warning"></i>
												<h6>Rencana Order Ke Pembelian</h6>
											</a>
										</div>
									</div>
								@endif
							</div>
							<div class="row d-flex">
								@if (Auth::user()->divisi == 'pembelian' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:openOrderKepembelian('Rencana Order Ke Pembelian Tanpa DC', '{{ url('TOrderKepembelian/TANPA_DC') }}')">
												<i style="font-size: 40px;" class="fas fa-truck-loading text-warning"></i>
												<h6>Rencana Order Ke Pembelian Tanpa DC</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'pembelian' ||
										Auth::user()->divisi == 'gudang' ||
										Auth::user()->divisi == 'outlet' ||
										Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:addTab('Order Koreksi Pembelian', '{{ url('torderkoreksipembelian') }}')">
												<i style="font-size: 40px;" class="fas fa-undo-alt text-warning"></i>
												<h6>Order Koreksi Pembelian</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'admin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:addTab('Cetak SP Kode 5', '{{ url('tcetakspkode5') }}')">
												<i style="font-size: 40px;" class="fas fa-receipt text-warning"></i>
												<h6>Cetak SP Kode 5</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'admin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:addTab('Buat Order Kue Basah', '{{ url('tbuatorderankuebasah') }}')">
												<i style="font-size: 40px;" class="fas fa-birthday-cake text-warning"></i>
												<h6>Buat Order Kue Basah</h6>
											</a>
										</div>
									</div>
								@endif
							</div>
							<div class="row d-flex">
								@if (Auth::user()->divisi == 'outlet' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:addTab('SPKO Ke TGZ', '{{ url('tspkoketgz') }}')">
												<i style="font-size: 40px;" class="fas fa-file-invoice text-warning"></i>
												<h6>SPKO Ke TGZ</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'outlet' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:addTab('SPKO Ke DC Tunjungsari', '{{ url('tspkokedctunjungsari') }}')">
												<i style="font-size: 40px;" class="fas fa-file-invoice-dollar text-warning"></i>
												<h6>SPKO Ke DC Tunjungsari</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'outlet' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #ffc107; background-color:#fff3cd;">
											<a href="javascript:addTab('Orderan Pelanggan', '{{ url('torderanpelanggan') }}')">
												<i style="font-size: 40px;" class="fas fa-user-tag text-warning"></i>
												<h6>Orderan Pelanggan</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'pembelian' ||
										Auth::user()->divisi == 'gudang' ||
										Auth::user()->divisi == 'outlet' ||
										Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Orderan Manual', '{{ url('torderanmanual') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-clipboard icon-orange"></i>
												<h6>Orderan Manual</h6>
											</a>
										</div>
									</div>
								@endif
							</div>
							<div class="row d-flex">
								@if (Auth::user()->divisi == 'outlet' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Orderan Toko GD Transit', '{{ url('torderantokogdtransit') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-handshake icon-orange"></i>
												<h6>Orderan Toko GD Transit</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Cetak LBKK / LBTAT', '{{ url('tcetaklbkklbtat') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-print icon-orange"></i>
												<h6>Cetak LBKK / LBTAT</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Cetak LBKK / LBTAT Baru', '{{ url('tcetaklbkklbtatbaru') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-file-pdf icon-orange"></i>
												<h6>Cetak LBKK / LBTAT Baru</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Proses Stock Opname', '{{ url('tprosesstockopname') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tasks icon-orange"></i>
												<h6>Proses Stock Opname</h6>
											</a>
										</div>
									</div>
								@endif
							</div>
							<div class="row d-flex">
								@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Penanganan LB-TAT', '{{ url('tpenangananlbtat') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tools icon-orange"></i>
												<h6>Penanganan LB-TAT</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Koreksi Toko Manual', '{{ url('tkoreksitokomanual') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-edit icon-orange"></i>
												<h6>Koreksi Toko Manual</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Posting Koreksi Toko', '{{ url('tpostingkoreksitoko') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-upload icon-orange"></i>
												<h6>Posting Koreksi Toko</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Pengembalian Umum', '{{ url('tpengembaliankegudang/gudangumum') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-undo icon-orange"></i>
												<h6>Pengembalian Umum</h6>
											</a>
										</div>
									</div>
								@endif
							</div>
							<div class="row d-flex">
								@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Pengembalian DCTanjungsari', '{{ url('tpengembaliankegudang/dctanjungsari') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-reply icon-orange"></i>
												<h6>Pengembalian DCTanjungsari</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Koreksi Stock Food Center', '{{ url('tkoreksistokopname') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-pen-square icon-orange"></i>
												<h6>Koreksi Stock Food Center</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Posting Stok Opname', '{{ url('tpostingstokopname') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-check-square icon-orange"></i>
												<h6>Posting Stok Opname</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Ubah Harga VIP', '{{ url('ubahhrgvip?flagz=PV') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tags icon-orange"></i>
												<h6>Pelaksanaan Perubahan Harga VIP</h6>
											</a>
										</div>
									</div>
								@endif
							</div>
							<div class="row d-flex">
								@if (Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Rkp Label Harian', '{{ url('rkplabel') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tag icon-orange"></i>
												<h6>Rekap Label Harian</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Data Barang 6C', '{{ url('tdatabarang6c') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-cubes icon-orange"></i>
												<h6>Data Barang 6C</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Data Barang 1-1', '{{ url('tdatabarang1-1') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-cube icon-orange"></i>
												<h6>Data Barang 1-1</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Ambil Data Survey Penjualan', '{{ url('tambildatasurveypenjualan') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-download icon-orange"></i>
												<h6>Ambil Data Survey Penjualan</h6>
											</a>
										</div>
									</div>
								@endif
							</div>
							<div class="row d-flex">
								@if (Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Input Survey Penjualan', '{{ url('tinputsurveypenjualan') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-edit icon-orange"></i>
												<h6>Input Survey Penjualan</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Posting Survey Penjualan', '{{ url('tpostingsurveypenjualan') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-paper-plane icon-orange"></i>
												<h6>Posting Survey Penjualan</h6>
											</a>
										</div>
									</div>
								@endif

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Obral Super Market', '{{ url('tobralsupermarket') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tags icon-orange"></i>
											<h6>Obral Super Market</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Entry Flash Sale', '{{ url('tentryflashsale') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-bolt icon-orange"></i>
											<h6>Entry Flash Sale</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Pelaksanaan Obral', '{{ url('tpelaksanaanobralsuper') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tags icon-orange"></i>
											<h6>Pelaksanaan Obral</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Posting Flash Sale', '{{ url('postingflashsale') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-bolt icon-orange"></i>
											<h6>Posting Flash Sale</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Obral Food Centre', '{{ url('tobralfoodcentre') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-utensils icon-orange"></i>
											<h6>Obral Food Centre</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Laporan Barang Flashsale', '{{ url('laporanbarangflashsale') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-file-alt icon-orange"></i>
											<h6>Laporan Barang Flashsale</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Pembelian Beda Harga', '{{ url('tpembelianbedaharga') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-shopping-cart icon-orange"></i>
											<h6>Pembelian Beda Harga</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Pengajuan Harga Fresh Food', '{{ url('tpengajuanhargafreshfood') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-file-invoice-dollar icon-orange"></i>
											<h6>Pengajuan Harga Fresh Food</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Tidak Order Fresh Food', '{{ url('ttidakorderfreshfood') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-times-circle icon-orange"></i>
											<h6>Tidak Order Fresh Food</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Order Lebih Fresh Food Online', '{{ url('torderlebihfreshfoodonline') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-cart-plus icon-orange"></i>
											<h6>Order Lebih Fresh Food Online</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Entry Presentase Order Fresh Food Online', '{{ url('tentrypresentaseordffonline') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-percent icon-orange"></i>
											<h6>Entry Presentase Order Fresh Food Online</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Order Lebih Hari Raya Online', '{{ url('torderlebihharirayaonline') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gifts icon-orange"></i>
											<h6>Order Lebih Hari Raya Online</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Transaksi LPHFF Mingguan', '{{ url('tlphffmingguan') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-calendar-week icon-orange"></i>
											<h6>Transaksi LPHFF Mingguan</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Transaksi Kirim LPH K3', '{{ url('tkirimlphk3') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-paper-plane icon-orange"></i>
											<h6>Transaksi Kirim LPH K3</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Update Master Barang', '{{ url('tupdatemasterbarang') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-sync-alt icon-orange"></i>
											<h6>Update Master Barang</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:hsl(33, 100%, 86%);">
										<a href="javascript:addTab('Update DTB', '{{ url('tupdatedtb') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-database icon-orange"></i>
											<h6>Update DTB</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Ambil Order Kode 3', '{{ url('tambilorderkode3') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-download icon-orange"></i>
											<h6>Ambil Order Kode 3</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Kirim Data Timbangan', '{{ url('tkirimdatatimbangan') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-weight icon-orange"></i>
											<h6>Kirim Data Timbangan</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Barang Prioritas', '{{ url('tbarangprioritas') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-star icon-orange"></i>
											<h6>Barang Prioritas</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Order Lebih Fresh Food', '{{ url('torderlebihfreshfood') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-edit icon-orange"></i>
											<h6>Order Lebih Fresh Food</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Pengajuan Perubahan', '{{ url('tpengajuanperubahan') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-bolt icon-orange"></i>
											<h6>Pengajuan Perubahan</h6>
										</a>
									</div>
								</div>
								{{-- <div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
										<a href="javascript:addTab('Masa Tarik', '{{ url('tmasatarik') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-bolt icon-orange"></i>
											<h6>Masa Tarik</h6>
										</a>
									</div>
								</div> --}}
								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Perubahan Masa Tarik', '{{ url('permat') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-upload icon-orange"></i>
												<h6>Perubahan masa Tarik</h6>
											</a>
										</div>
									</div>
								@endif

								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Usulan Perubahan Harga', '{{ url('perharga') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tasks icon-orange"></i>
												<h6>Proses Barang Baru</h6>
											</a>
										</div>
									</div>
								@endif

								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Perubahan Barcode', '{{ url('perbar') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-qrcode icon-orange"></i>
												<h6>Perubahan Barcode</h6>
											</a>
										</div>
									</div>
								@endif

								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Proses Posting Perubahan', '{{ url('posthisto') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-check-square icon-orange"></i>
												<h6>Posting Pengajuan</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Bete', '{{ url('tbetebete') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-cube icon-orange"></i>
												<h6>Bete</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('LPH Hari Raya', '{{ url('tlphhariraya') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-calendar-alt icon-orange"></i>
												<h6>LPH Hari Raya</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Usulan LPH Periode', '{{ url('tusulanlphperiode') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-calendar-alt icon-orange"></i>
												<h6>Usulan LPH Periode</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Usulan Hapus Barang', '{{ url('tusulanhapusbarang') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-trash icon-orange"></i>
												<h6>Usulan Hapus Barang</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Penambahan barang Baru', '{{ url('tpenambahanbarangbaru') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-plus icon-orange"></i>
												<h6>Penambahan barang Baru</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Pelaksanaan Turun Harga', '{{ url('tpelaksanaanturunharga') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-arrow-down icon-orange"></i>
												<h6>Pelaksanaan Turun Harga</h6>
											</a>
										</div>
									</div>
								@endif
								@if (Auth::user()->divisi == 'PENJUALAN' || Auth::user()->divisi == 'PRG' || Auth::user()->divisi == 'programmer')
									<div class="col-md-3 mb-3">
										<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe0ba;">
											<a href="javascript:addTab('Cetak Label Harga', '{{ url('tcetaklabelharga') }}')">
												<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tag icon-orange"></i>
												<h6>Cetak Label Harga</h6>
											</a>
										</div>
									</div>
								@endif

							</div>
					@endif
				</li>

				<!-- REPORT MENU -->
				<li class="nav-item">
					@if (Auth::user()->divisi == 'superadmin' || Auth::user()->divisi == 'programmer')
						<a href="#" class="nav-link" data-bs-toggle="tooltip" title="Report">
							<i class="nav-icon fas fa-chart-bar text-danger fa-beat"></i>
							<p></p>
						</a>
						<div class="mega-menu" id="d">
							<div class="row">
								<div class="col-md-12">
									<h3 style="color: #dc3545;">REPORT</h3>
									<hr style="height: 3px; background-color: #dc3545; border: none; margin: 15px 0;" />
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #dc3545; background-color:#f8d7da;">
										<a href="javascript:addTab('Kartu Stok', '{{ url('rkartustok') }}')">
											<i style="font-size: 40px;" class="fas fa-clipboard-list text-danger"></i>
											<h6>Kartu Stok</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #dc3545; background-color:#f8d7da;">
										<a href="javascript:addTab('Barang SPM', '{{ url('rbarangspm') }}')">
											<i style="font-size: 40px;" class="fas fa-boxes text-danger"></i>
											<h6>Barang SPM</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #dc3545; background-color:#f8d7da;">
										<a href="javascript:addTab('Barang FC', '{{ url('rbarangfc') }}')">
											<i style="font-size: 40px;" class="fas fa-box text-danger"></i>
											<h6>Barang FC</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #dc3545; background-color:#f8d7da;">
										<a href="javascript:addTab('Hadiah', '{{ url('rhadiah') }}')">
											<i style="font-size: 40px;" class="fas fa-gift text-danger"></i>
											<h6>Hadiah</h6>
										</a>
									</div>
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #dc3545; background-color:#f8d7da;">
										<a href="javascript:addTab('Macet - Kosong', '{{ url('rbarangmacetkosong') }}')">
											<i style="font-size: 40px;" class="fas fa-exclamation-triangle text-danger"></i>
											<h6>Macet - Kosong</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #dc3545; background-color:#f8d7da;">
										<a href="javascript:addTab('Cek Perubahan LPH', '{{ url('rcekperubahanlph') }}')">
											<i style="font-size: 40px;" class="fas fa-chart-line text-danger"></i>
											<h6>Cek Perubahan LPH</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Sinkron DC', '{{ url('rsinkrondc') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-sync icon-red"></i>
											<h6>Sinkron DC</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Perubahan DTR2', '{{ url('rperubahandtr2') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-sync icon-red"></i>
											<h6>Perubahan DTR2</h6>
										</a>
									</div>
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Pemantauan DTR', '{{ url('rpemantauandtrkhusus') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-eye icon-red"></i>
											<h6>Pemantauan DTR </h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Stok Nol', '{{ url('rstoknol') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-euro-sign icon-red"></i>
											<h6>Stok Nol</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Barang Supplier', '{{ url('rbarangsupplier') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-box icon-red"></i>
											<h6>Barang Supplier</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Stock Kosong', '{{ url('rstockkosong') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-box icon-red"></i>
											<h6>Stock Kosong</h6>
										</a>
									</div>
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Pemantauan Barang', '{{ url('rpemantauanbarang') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-warehouse icon-red"></i>
											<h6>Pemantauan Barang </h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Laku Per Hari', '{{ url('rlakuperhari') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-chart-line icon-red"></i>
											<h6>Laku Per Hari</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Order DC Belum Dilayani', '{{ url('rodcbelum') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-box icon-red"></i>
											<h6>Order DC Belum Dilayani</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Order Non Kode 3', '{{ url('roordernonkode3') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-file-invoice icon-red"></i>
											<h6>Order Non Kode 3</h6>
										</a>
									</div>
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('IP Belum Transfer', '{{ url('rrencanaorderkode8') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-clock icon-red"></i>
											<h6>IP Belum Transfer </h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Kasir Bantu', '{{ url('rkasirbantu') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-cash-register icon-red"></i>
											<h6>Kasir Bantu </h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Report Penjualan', '{{ url('rpenjualanbaru') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-redo icon-red"></i>
											<h6>Report Penjualan </h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Penjualan PH', '{{ url('rpenjualanph') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-redo icon-red"></i>
											<h6>Penjualan PH </h6>
										</a>
									</div>
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Hdh Poin Over/Macet', '{{ url('rhdhovermacet') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-redo icon-red"></i>
											<h6>Hadiah Poin Overstok/Macet</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Stok KD', '{{ url('rstokkd') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-redo icon-red"></i>
											<h6>Stok KD</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Selisih SO', '{{ url('rselisihso') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-redo icon-red"></i>
											<h6>Selisih SO</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('SO R/L', '{{ url('rsorl') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-redo icon-red"></i>
											<h6>SO R/L</h6>
										</a>
									</div>
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Belum SO', '{{ url('rbelumso') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-redo icon-red"></i>
											<h6>Belum SO</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Kealpaan SO', '{{ url('rkealpaanso') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-redo icon-red"></i>
											<h6>Kealpaan SO</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffd9d9;">
										<a href="javascript:addTab('Rencana Order 8', '{{ url('rrcnorder8') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-redo icon-red"></i>
											<h6>Rencana Order Kode 8</h6>
										</a>
									</div>
								</div>
							</div>
						</div>
					@endif
				</li>

				<li class="nav-item">
					@if (Auth::user()->divisi == 'programmer')
						<a href="#" class="nav-link" data-bs-toggle="tooltip" title="Laporan Barang Tukar Point">
							<i class="nav-icon fas fa-book icon-yellow"></i>
							<p>
							</p>
						</a>
						<div class="mega-menu" id="e">
							<!-- penambahan judul di menu -->
							<div class="row">
								<div class="col-md-12">
									<h3>LAPORAN</h3>
									<hr
										style=" height: 5px;
                                        background-color: #333;
                                        border: none;
                                        margin: 20px 0; " />
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Cetak Ulang Struk', '{{ url('rcetakulangstruk') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-print icon-pink"></i>
											<h6>Cetak Ulang Struk</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Diskon Hadiah Berjalan', '{{ url('rdiskonhadiahberjalan') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gift icon-pink"></i>
											<h6>Diskon Hadiah Berjalan</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Report Jackpot & Point', '{{ url('rjackpopoint') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gift icon-pink"></i>
											<h6>Report Jackpot & Point</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Cetak Ulang Cashback', '{{ url('rcetakulangcashback') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-redo icon-pink"></i>
											<h6>Cetak Ulang Cashback</h6>
										</a>
									</div>
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Report Barcode', '{{ url('rbarcode') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-barcode icon-pink"></i>
											<h6>Report Barcode</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Sales Penjualan SPM', '{{ url('rsalespenjualanspm') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-shopping-cart icon-pink"></i>
											<h6>Sales Penjualan SPM</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Sales Penjualan EDC', '{{ url('rsalespenjualanedc') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-shopping-cart icon-pink"></i>
											<h6>Sales Penjualan EDC</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Sales Manager', '{{ url('rsalesmanager') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-shopping-cart icon-pink"></i>
											<h6>Sales Manager</h6>
										</a>
									</div>
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Barang Obral Vip', '{{ url('rbarangobralvip') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tag icon-pink"></i>
											<h6>Barang Obral Vip</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Report Kasir Grab', '{{ url('rkasirgrab') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-shopping-cart icon-pink"></i>
											<h6>Report Kasir Grab</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Penerimaan Gudang', '{{ url('rpenerimaangudang') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-shopping-cart icon-pink"></i>
											<h6>Penerimaan Gudang</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Barang Grab Mart', '{{ url('rbaranggrabmart') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-box icon-pink"></i>
											<h6>Barang Grab Mart</h6>
										</a>
									</div>
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Barang HappyFresh', '{{ url('rbaranghappyfresh') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-box icon-pink"></i>
											<h6>Barang HappyFresh</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Barang Baru Belum Datang', '{{ url('rbarangbarubelumdatang') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-box icon-pink"></i>
											<h6>Barang Baru Belum Datang</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Harga Jual Expired', '{{ url('rsurveihargajualexpired') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-clock icon-pink"></i>
											<h6>Harga Jual Expired</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ffe6ff;">
										<a href="javascript:addTab('Barang Grab Mart', '{{ url('rbaranggrabmart') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-box icon-pink"></i>
											<h6>Barang Grab Mart</h6>
										</a>
									</div>
								</div>
							</div>
						</div>
					@endif
				</li>
				<!-- LOGISTIK MENU -->
				{{-- <li class="nav-item">
					@if (Auth::user()->divisi == 'programmer')
						<a href="#" class="nav-link" data-bs-toggle="tooltip" title="Logistik">
							<i class="nav-icon fas fa-truck text-success fa-beat"></i>
							<p></p>
						</a>
						<div class="mega-menu" id="f">
							<div class="row">
								<div class="col-md-12">
									<h3 style="color: #28a745;">LOGISTIK</h3>
									<hr style="height: 3px; background-color: #28a745; border: none; margin: 15px 0;" />
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #28a745; background-color:#d4edda;">
										<a href="javascript:addTab('Order Logistik', '{{ url('lorderlogistik') }}')">
											<i style="font-size: 40px;" class="fas fa-clipboard-list text-success"></i>
											<h6>Order Logistik</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #28a745; background-color:#d4edda;">
										<a href="javascript:addTab('Entry Realisasi', '{{ url('lentryrealisasi') }}')">
											<i style="font-size: 40px;" class="fas fa-check-circle text-success"></i>
											<h6>Entry Realisasi</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #28a745; background-color:#d4edda;">
										<a href="javascript:addTab('Entry Transaksi', '{{ url('lentrytransaksi') }}')">
											<i style="font-size: 40px;" class="fas fa-edit text-success"></i>
											<h6>Entry Transaksi</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Kartu Stock', '{{ url('lkartustock') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-box icon-purple"></i>
											<h6>Kartu Stock</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Posting Order Logistik', '{{ url('lpostingorderlogistik') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-cash-register icon-purple"></i>
											<h6>Posting Order Logistik</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Posting Transaksi Logistik', '{{ url('lpostingtransaksilogistik') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-clipboard-check icon-purple"></i>
											<h6>Posting Transaksi Logistik</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Memberi Tanda Bintang', '{{ url('lmemberitandabintang') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-star icon-purple"></i>
											<h6>Memberi Tanda Bintang</h6>
										</a>
									</div>
								</div>
							</div>
					@endif
				</li> --}}

				<!-- HADIAH & PROMOSI MENU -->
				<li class="nav-item">
					@if (Auth::user()->divisi == 'programmer')
						<a href="#" class="nav-link" data-bs-toggle="tooltip" title="Hadiah & Promosi">
							<i class="nav-icon fas fa-gift text-purple fa-beat"></i>
							<p></p>
						</a>
						<div class="mega-menu" id="f">
							<div class="row">
								<div class="col-md-12">
									<h3 style="color: #6f42c1;">HADIAH & PROMOSI</h3>
									<hr style="height: 3px; background-color: #6f42c1; border: none; margin: 15px 0;" />
								</div>
							</div>
							<div class="row d-flex">
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #6f42c1; background-color:#e7d9f5;">
										<a href="javascript:addTab('Pembayaran Piutang', '{{ url('phpembayaranpiutang') }}')">
											<i style="font-size: 40px;" class="fas fa-money-bill-wave text-purple"></i>
											<h6>Pembayaran Piutang</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #6f42c1; background-color:#e7d9f5;">
										<a href="javascript:addTab('Poin Kresek', '{{ url('phpoinkresek') }}')">
											<i style="font-size: 40px;" class="fas fa-shopping-bag text-purple"></i>
											<h6>Poin Kresek</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #6f42c1; background-color:#e7d9f5;">
										<a href="javascript:addTab('Poin EDC', '{{ url('phpoinfc') }}')">
											<i style="font-size: 40px;" class="fas fa-credit-card text-purple"></i>
											<h6>Poin EDC</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #6f42c1; background-color:#e7d9f5;">
										<a href="javascript:addTab('Poin Bonus', '{{ url('phpoinbonus') }}')">
											<i style="font-size: 40px;" class="fas fa-star text-purple"></i>
											<h6>Poin Bonus</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Program Promosi Hadiah', '{{ url('phprogrampromosihadiah') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gift icon-purple"></i>
											<h6>Program Promosi Hadiah</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Pengesahan File', '{{ url('phpengesahanfile') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-file-upload icon-purple"></i>
											<h6>Pengesahan File</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Partisipasi Supplier', '{{ url('rpromoGayan/penjualan') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-star icon-purple"></i>
											<h6>Partisipasi Supplier</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Laporan Evaluasi', '{{ url('rpromoGayan/peritem') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-star icon-purple"></i>
											<h6>Laporan Evaluasi</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Turun Harga', '{{ url('phturanharga') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tag icon-purple"></i>
											<h6>Turun Harga</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Harga Vip', '{{ url('phhargavip') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-tag icon-purple"></i>
											<h6>Harga Vip</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Penukaran Hadiah', '{{ url('phpenukaranhadiah') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gift icon-purple"></i>
											<h6>Penukaran Hadiah</h6>
										</a>
									</div>
								</div>

								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Daftar Hadiah', '{{ url('phdaftarbaranghadiah') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gift icon-purple"></i>
											<h6>Daftar Hadiah</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Terima Hadiah Supplier', '{{ url('phterimahadiahsupplier') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gift icon-purple"></i>
											<h6>Terima Hadiah Supplier</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Transfer Hadiah', '{{ url('phtransaksitransferhadiah') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gift icon-purple"></i>
											<h6>Transfer Hadiah</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Hadiah TGZ', '{{ url('phterimahadiahdaritgz') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gift icon-purple"></i>
											<h6>Hadiah TGZ</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Stop Program Hadiah', '{{ url('phstopprogramhadiah') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-stop-circle icon-purple"></i>
											<h6>Stop Program Hadiah</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Stock Opname Hadiah', '{{ url('phstokopnamehadiah') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-stop-circle icon-purple"></i>
											<h6>Stock Opname Hadiah</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Stock Opname Koreksi', '{{ url('phstokopnamekoreksihadiah') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-undo icon-purple"></i>
											<h6>Stock Opname Koreksi</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Stock Opname Koreksi Manual', '{{ url('phsokoreksimanual') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-edit icon-purple"></i>
											<h6>Stock Opname Koreksi Manual</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Koreksi Toko', '{{ url('phkoreksitoko') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-store icon-purple"></i>
											<h6>Koreksi Toko</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Koreksi Gudang', '{{ url('phkoreksigudang') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-warehouse icon-purple"></i>
											<h6>Koreksi Gudang</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Posting Koreksi Manual', '{{ url('phpostingkoreksimanual') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-edit icon-purple"></i>
											<h6>Posting Koreksi Manual</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Undian Supplier', '{{ url('phundiansupplier') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gift icon-purple"></i>
											<h6>Undian Supplier</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Data Undian Customer', '{{ url('phdataundiancustomer') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-gift icon-purple"></i>
											<h6>Data Undian Customer</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Kasir 13', '{{ url('phkasir13') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-cash-register icon-purple"></i>
											<h6>Kasir 13</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Hadiah Cashback', '{{ url('phhadiahcashback') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-cash-register icon-purple"></i>
											<h6>Hadiah Cashback</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Entry Penyewa Tempat', '{{ url('phentrypenyewatempat') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-warehouse icon-purple"></i>
											<h6>Entry Penyewa Tempat</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Laporan Persetujuan', '{{ url('phlaporanpersetujuan') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-warehouse icon-purple"></i>
											<h6>Laporan Persetujuan</h6>
										</a>
									</div>
								</div>
								<div class="col-md-3 mb-3">
									<div class="menu-card" style="border:1px solid #aabbcc; background-color:#ebd9ff;">
										<a href="javascript:addTab('Rincian Penagihan', '{{ url('promo-hadiah/rincian-penagihan') }}')">
											<i style="margin-left:-5px;font-size: 40px;" class="nav-icon fas fa-warehouse icon-purple"></i>
											<h6>Rincian Penagihan</h6>
										</a>
									</div>
								</div>
							</div>
						</div>
					@endif
				</li>
				<!-- USER MENU -->
				@if (Auth::user()->hasRole('superadmin'))
					<li class="nav-header"></li>
					<li class="nav-item" data-bs-toggle="tooltip" title="User Management">
						<a onclick="addTab('User', '{{ url('/user/manage') }}')" href="#" class="nav-link">
							<i class="nav-icon fas fa-users-cog text-secondary"></i>
							<p></p>
						</a>
					</li>
				@endif
			</ul>
		</nav>
		<!-- /.sidebar-menu -->
	</div>
	<!-- /.sidebar -->
</aside>

<script>
	// Function to open Order Kepembelian with flag setting
	function openOrderKepembelian(title, url) {
		// Extract CBG from Auth user and set flag session
		var cbg = '{{ Auth::user()->CBG ?? '' }}';

		if (cbg) {
			// Make AJAX request to set flag session
			fetch('{{ url('/set-flag-session') }}', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': '{{ csrf_token() }}'
					},
					body: JSON.stringify({
						flag: cbg
					})
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						// Open tab after flag is set
						addTab(title, url);
					} else {
						console.error('Failed to set flag session');
						// Still try to open tab
						addTab(title, url);
					}
				})
				.catch(error => {
					console.error('Error setting flag:', error);
					// Still try to open tab
					addTab(title, url);
				});
		} else {
			// If no CBG, just open the tab
			addTab(title, url);
		}
	}
</script>
