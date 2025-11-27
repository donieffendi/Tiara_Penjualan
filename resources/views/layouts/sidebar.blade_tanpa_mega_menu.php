<!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{url('/')}}" class="brand-link" style="text-align: center">
      <img src="{{url('/img/company.jpg')}}" alt="LookmanDjaja Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">UD. ASRI RAYA</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        {{-- <div class="image">
          <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="test">
        </div> --}}
        <div class="info">
          <a href="#" class="d-block">{{ Auth::user()->name }}</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      {{-- <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div> --}}

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-home"></i>
              <p>
                Home
                {{-- <span class="right badge badge-danger">New</span> --}}
              </p>
            </a>
          </li>
          

        
          
          <li class="nav-header">Operational</li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-database icon-white"></i>
              <p>
                Master
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>

            <ul class="nav nav-treeview">
              <!-- IF check privilege & divisi -->

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="purchase"))
              <li class="nav-item {{ (Request::is('sup*')) ? 'active' : '' }}">
                <a href="{{url('sup')}}" class="nav-link">
                   <i class="nav-icon far fa-user icon-purple "></i> 
                  <p>Vendor</p>
                </a>
              </li>
              @endif

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('cust')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-users icon-yellow"></i> -->
                  <p>Customer</p>
                </a>
              </li>
              @endif
              

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('pegawai')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-users icon-pink"></i> -->
                  <p>Pegawai</p>
                </a>
              </li>
              @endif

              <!-- @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="purchase"))
              <li class="nav-item">
                <a href="{{url('bhn')}}" class="nav-link">
                  <i class="nav-icon fas fa-layer-group icon-gree"></i>
                  <p>Bahan</p>
                </a>
              </li>
              @endif -->

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('brg')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-anchor icon-blue"></i> -->
                  <p>Barang</p>
                </a>
              </li>
              @endif

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('grup')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-anchor icon-red"></i> -->
                  <p>Group</p>
                </a>
              </li>
              @endif

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('kota')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-anchor icon-red"></i> -->
                  <p>Kota</p>
                </a>
              </li>
              @endif

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('gdg')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-anchor icon-red"></i> -->
                  <p>Gudang</p>
                </a>
              </li>
              @endif

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('truck')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-anchor icon-red"></i> -->
                  <p>Truck</p>
                </a>
              </li>
              @endif

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('lokasi')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-anchor icon-red"></i> -->
                  <p>Lokasi</p>
                </a>
              </li>
              @endif

            </ul>			 
          </li>
          
          <li class="nav-item">
          @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="purchase"))
			      <a href="#" class="nav-link">
              <i class="nav-icon fas fa-hand-holding-heart icon-pink"></i>
              <p>
                Transaksi Pembelian
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
			
			      <ul class="nav nav-treeview">

              <li class="nav-item {{ (Request::is('po*')) ? 'active' : '' }}">
                <a href="{{url('po?flagz=PO&golz=J')}}" class="nav-link">
                   <!-- <i class="nav-icon fas fa-cart-plus icon-yellow"></i>  -->
                  <p>PO</p>
                </a>
              </li>
              

              <li class="nav-item">
                <a href="{{url('beli?flagz=BL&golz=J')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-store icon-white"></i> -->
                  <p>Pembelian</p>
                </a>
              </li>
              

              <li class="nav-item">
                <a href="{{url('beli?flagz=RB&golz=J')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-store icon-white"></i> -->
                  <p>Retur Pembelian</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('utbeli?flagz=UM')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-comments-dollar icon-green"></i> -->
                  <p>Uang Muka Pembelian</p>
                </a>
              </li>
              

              <li class="nav-item">
                <a href="{{url('utbeli?flagz=TH')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-credit-card icon-purple"></i> -->
                  <p>Transaksi Hutang</p>
                </a>
              </li>
              
			  
              <li class="nav-item">
                <a href="{{url('hut?flagz=B')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-money-bill icon-blue"></i> -->
                  <p>Pembayaran Hutang</p>
                </a>
              </li>
              
            </ul>
           @endif			 		
          </li>

          <!-- <li class="nav-item">
          @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="purchase"))
			      <a href="#" class="nav-link">
              <i class="nav-icon fas fa-hand-holding-heart icon-ocean"></i>
              <p>
                Transaksi Pembelian Non
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
			
			      <ul class="nav nav-treeview">
              <li class="nav-item {{ (Request::is('po*')) ? 'active' : '' }}">
                <a href="{{url('po?flagz=PO&golz=N')}}" class="nav-link">
                  <p>PO Non</p>
                </a>
              </li>
			  
              <li class="nav-item">
                <a href="{{url('beli?flagz=BL&golz=N')}}" class="nav-link">
                  <p>Pembelian Non</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('beli?flagz=RB&golz=N')}}" class="nav-link">
                  <p>Retur Pembelian Non</p>
                </a>
              </li>
              
            </ul>
           @endif			 		
          </li> -->
			
          <!-- <li class="nav-item">
          @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="purchase"))
			      <a href="#" class="nav-link">
              <i class="nav-icon fas fa-hand-holding-heart icon-red"></i>
              <p>
                Transaksi Pembelian Bahan
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
			
			      <ul class="nav nav-treeview">
              <li class="nav-item {{ (Request::is('po*')) ? 'active' : '' }}">
                <a href="{{url('po?flagz=PO&golz=B')}}" class="nav-link">
                  <p>PO Bahan</p>
                </a>
              </li>
			  
              <li class="nav-item">
                <a href="{{url('beli?flagz=BL&golz=B')}}" class="nav-link">
                  <p>Pembelian Bahan</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('beli?flagz=RB&golz=B')}}" class="nav-link">
                  <p>Retur Pembelian Bahan</p>
                </a>
              </li>
              
            </ul>
           @endif			 		
          </li> -->

          <li class="nav-item">
          @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
			      <a href="#" class="nav-link">
              <i class="nav-icon fas fa-cash-register icon-orange"></i>
              <p>
                Transaksi Penjualan
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
			
			      <ul class="nav nav-treeview">

              <li class="nav-item">
                <a href="{{url('so?flagz=SO&golz=J')}}" class="nav-link">
                   <!-- <i class="nav-icon fas fa-car icon-white"></i> -->
                  <p>SO</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('deli?flagz=DO&golz=J')}}" class="nav-link">
                   <!-- <i class="nav-icon fas fa-car icon-white"></i> -->
                  <p>DO</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('surats?flagz=JL&golz=J')}}" class="nav-link">
                  <p>Surat Jalan </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('jual?flagz=JL&golz=J')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-crop icon-orange"></i> -->
                  <p>Penjualan</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('jual?flagz=AJ&golz=J')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-crop icon-orange"></i> -->
                  <p>Retur Penjualan</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('surats?flagz=AJ&golz=J')}}" class="nav-link">
                  <p> Retur Surat Jalan </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('utjual?flagz=UM')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-crop icon-orange"></i> -->
                  <p>Uang Muka Penjualan</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('utjual?flagz=TP')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-crop icon-orange"></i> -->
                  <p>Transaksi Piutang</p>
                </a>
              </li>


              <li class="nav-item">
                <a href="{{url('piu?flagz=B')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-desktop icon-aqua"></i> -->
                  <p>Pembayaran Piutang</p>
                </a>
              </li>
			 
            </ul>
          @endif			 		
          </li> 

          <!-- <li class="nav-item">
          @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
			      <a href="#" class="nav-link">
              <i class="nav-icon fas fa-cash-register icon-purple"></i>
              <p>
                Transaksi Penjualan Bahan
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
			
			      <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{url('so?flagz=SO&golz=B')}}" class="nav-link">
                  <p>SO Bahan</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('surats?flagz=JL&golz=B')}}" class="nav-link">
                  <p>Surat Jalan Bahan </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('jual?flagz=JL&golz=B')}}" class="nav-link">
                  <p>Penjualan Bahan</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('jual?flagz=AJ&golz=B')}}" class="nav-link">
                  <p>Retur Penjualan Bahan</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('surats?flagz=AJ&golz=B')}}" class="nav-link">
                  <p>Retur Surat Jalan Bahan </p>
                </a>
              </li>
			 
            </ul>
          @endif			 		
          </li>  -->

          

          <li class="nav-item">
          @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="production"))
			      <a href="#" class="nav-link">
              <i class="nav-icon fas fa-seedling icon-green"></i>
              <p>
                Koreksi Stok
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
			
			      <ul class="nav nav-treeview">

              <!-- <li class="nav-item {{ (Request::is('po*')) ? 'active' : '' }}">
                <a href="{{url('pp?flagz=PP&golz=J')}}" class="nav-link">
                  <p>Purchase Request</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('orderk?flagz=OK&golz=J')}}" class="nav-link">
                  <p>Order Kerja </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('kik/index-posting')}}" class="nav-link">
                  <p>Kartu Instruksi Kerja</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('pakai?flagz=PK&golz=J')}}" class="nav-link">
                  <p>Pemakaian</p>
                </a>
              </li>

			  
              <li class="nav-item">
                <a href="{{url('terima?flagz=HP&golz=J')}}" class="nav-link">
                  <p>Hasil Produksi </p>
                </a>
              </li>		
			  
              <li class="nav-item">
                <a href="{{url('stocka?flagz=KB')}}" class="nav-link">
                  <p>Koreksi Stock Bahan </p>
                </a>
              </li> -->
			  
              <li class="nav-item">
                <a href="{{url('stockb?flagz=KZ')}}" class="nav-link">
                  <p>Koreksi Stock Barang </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('stockb?flagz=MT')}}" class="nav-link">
                  <p>Koreksi Stock Mutasi </p>
                </a>
              </li>
			 
            </ul>
          @endif				 		
          </li> 


          <li class="nav-item">          
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-archive icon-yellow"></i>
              <p>
                Laporan Master
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>

            <ul class="nav nav-treeview">
              <!-- IF check privilege & divisi -->

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('rbrg')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-flask icon-red"></i> -->
                  <p>Barang </p>
                </a>
              </li>
              @endif 
			  
              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="purchase"))
              <li class="nav-item">
                <a href="{{url('rsup')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-flask icon-red"></i> -->
                  <p>Suplier</p>
                </a>
              </li>
              @endif 

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('rcust')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-flask icon-red"></i> -->
                  <p>Customer</p>
                </a>
              </li>
              @endif 

            </ul>
         </li>

<!---------------------------------------------------------------------------------->


          <li class="nav-item">          
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-archive icon-yellow"></i>
              <p>
                Laporan Pembelian
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>

            <ul class="nav nav-treeview">
              <!-- IF check privilege & divisi -->	
			  
              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="purchase"))
              <li class="nav-item">
                <a href="{{url('rpo')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-flask icon-red"></i> -->
                  <p>Purchase Order</p>
                </a>
              </li>
              @endif
			  
			  
              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="purchase"))
              <li class="nav-item">
                <a href="{{url('rbeli')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-folder icon-brown"></i> -->
                  <p>Pembelian</p>
                </a>
              </li>
              @endif 

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="purchase"))
              <li class="nav-item">
                <a href="{{url('rbeli_gdg')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-folder icon-brown"></i> -->
                  <p>Pembelian Gudang</p>
                </a>
              </li>
              @endif 

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('rthut')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-inbox icon-green"></i> -->
                  <p>Transaksi Hutang</p>
                </a>
              </li>
              @endif 

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('rum')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-key icon-blue"></i> -->
                  <p>Uang Muka Pembelian</p>
                </a>
              </li>
              @endif 


              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="purchase"))
              <li class="nav-item">
                <a href="{{url('rhut')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-gamepad icon-white"></i> -->
                  <p>Pembayaran Hutang</p>
                </a>
              </li>
              @endif 	
			  

            </ul>
         </li>

<!---------------------------------------------------------------------------------->

<!---------------------------------------------------------------------------------->


          <li class="nav-item">          
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-archive icon-yellow"></i>
              <p>
                Laporan Penjualan
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>

            <ul class="nav nav-treeview">
              <!-- IF check privilege & divisi -->	
			  
              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('rso')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-bug icon-pink"></i> -->
                  <p>Sales Order</p>
                </a>
              </li>
              @endif 

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('rsurats')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-heart icon-red"></i> -->
                  <p>Surat Jalan</p>
                </a>
              </li>
              @endif 
			  
              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('rjual')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-heart icon-red"></i> -->
                  <p>Penjualan</p>
                </a>
              </li>
              @endif 

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('rtpiu')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-inbox icon-green"></i> -->
                  <p>Transaksi Piutang</p>
                </a>
              </li>
              @endif 

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('ruj')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-key icon-blue"></i> -->
                  <p>Uang Muka Penjualan</p>
                </a>
              </li>
              @endif 
			  

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="sales"))
              <li class="nav-item">
                <a href="{{url('rpiu')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-laptop icon-pink"></i> -->
                  <p>Pembayaran Piutang</p>
                </a>
              </li>
              @endif 

              @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="production"))
              <li class="nav-item">
                <a href="{{url('rstockb')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-lock icon-white"></i> -->
                  <p>Koreksi Stock Barang</p>
                </a>
              </li>
              @endif 

            </ul>
         </li>

<!---------------------------------------------------------------------------------->

<!-- ...................................................................................... -->

      @if ( (Auth::user()->divisi=="programmer") || (Auth::user()->divisi=="owner") || (Auth::user()->divisi=="accounting"))
        <li class="nav-header">Financial</li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-location-arrow icon-blue"></i>
              <p>
                Master
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>

            <ul class="nav nav-treeview">						  
              
              <li class="nav-item">
                <a href="{{url('account')}}" class="nav-link">
                  <i class="nav-icon fas fa-map icon-yellow"></i>
                  <p>Account </p>
                </a>
              </li>
			  
            </ul>								
          </li>

	  
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-university icon-green"></i>
              <p>
                Transaksi
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
			
			      <ul class="nav nav-treeview">
              <!-- IF check privilege & divisi -->

              <li class="nav-item {{ (Request::is('kas')) ? 'active' : '' }}">
                <a href="{{url('kas?flagz=BKM')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-lock icon-yellow"></i> -->
                  <p>Sumber Dana Masuk</p>
                </a>
              </li>

              <li class="nav-item {{ (Request::is('kask*')) ? 'active' : '' }}">
                <a href="{{url('kas?flagz=BKK')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-magic icon-blue"></i> -->
                  <p>Sumber Dana Keluar</p>
                </a>
              </li>

              <li class="nav-item {{ (Request::is('bank')) ? 'active' : '' }}">
                <a href="{{url('bank?flagz=BBM')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-magnet icon-red"></i> -->
                  <p>Bank Masuk</p>
                </a>
              </li>

              <li class="nav-item {{ (Request::is('bankk*')) ? 'active' : '' }}">
                <a href="{{url('bank?flagz=BBK')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-male icon-purple"></i> -->
                  <p>Bank Keluar</p>
                </a>
              </li>

              <li class="nav-item {{ (Request::is('memo*')) ? 'active' : '' }}">
                <a href="{{url('memo?flagz=M')}}" class="nav-link">
                  <!-- <i class="nav-icon fas fa-bug icon-orange"></i> -->
                  <p>Penyesuaian</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('cbin')}}" class="nav-link">
                  <i class="nav-icon fas fa-microphone icon-white"></i>
                  <p>Kas - Bank </p>
                </a>
              </li>
			  
			  
            </ul>									
          </li>


          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-print icon-purple"></i>
              <p>
                Laporan
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
			
			      <ul class="nav nav-treeview">
              <!-- IF check privilege & divisi -->

              <li class="nav-item">
                <a href="{{url('rkas')}}" class="nav-link">
                  <i class="nav-icon fas fa-car icon-green"></i>
                  <p>Journal Kas</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('rbank')}}" class="nav-link">
                  <i class="nav-icon fas fa-plus icon-purple"></i>
                  <p>Journal Bank</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('rmemo')}}" class="nav-link">
                  <i class="nav-icon fas fa-beer icon-red"></i>
                  <p>Journal Penyesuaian</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('rbuku')}}" class="nav-link">
                  <i class="nav-icon fas fa-eraser icon-blue"></i>
                  <p>Buku Besar</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('raccount')}}" class="nav-link">
                  <i class="nav-icon fas fa-gift icon-aqua"></i>
                  <p>Neraca Percobaan</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{url('rrl')}}" class="nav-link">
                  <i class="nav-icon fas fa-random icon-white"></i>
                  <p>Laba Rugi</p>
                </a>
              </li>
			  
              <li class="nav-item">
                <a href="{{url('rnera')}}" class="nav-link">
                  <i class="nav-icon fas fa-road icon-pink"></i>
                  <p>Neraca</p>
                </a>
              </li>
			  
            </ul>									
          </li>
        @endif	









          <li class="nav-header">Utility</li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-plus icon-yellow"></i>
              <p>
                Utillty
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>

            <ul class="nav nav-treeview">
              <!-- IF check privilege & divisi -->
              @if (Auth::user()->hasRole('superadmin|operational'))
              <li class="nav-item">
                <a href="{{url('periode')}}" class="nav-link">
                  <i class="nav-icon fas fa-truck icon-blue"></i>
                  <p>Ganti Periode</p>
                </a>
              </li>
              @endif 
			  
            </ul>
          </li>

          @if (Auth::user()->hasRole('superadmin'))
          <li class="nav-header">User Management</li>
          <li class="nav-item">
            <a href="{{url('/user/manage')}}" class="nav-link">
              <i class="nav-icon fas fa-users icon-orange"></i>
              <p>
                User
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
