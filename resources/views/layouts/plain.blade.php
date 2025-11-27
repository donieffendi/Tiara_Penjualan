<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Tiara Dewata Penjualan</title>
       
<!-- Font Awesome 5 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/fontawesome.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" />

  <!-- Bootstrap -->

  <!-- Date Picker -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  
  <!-- Data Table -->
  <link rel="stylesheet" type="text/css" href="{{url('https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css') }}"> 
  
  <!-- Data Table Button -->
  <link rel="stylesheet" href="{{url('https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css') }}">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <!-- Local Asset -->
  <link rel="stylesheet" href="{{asset('css/app.css')}}">
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>

<!-- Date Picker -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  @yield('styles')
  
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar ( Menu Atas ) -->


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
		<div class="col-md-12">
  <div id="tabs">
  <ul></ul>
  </div></div></div></div></div>
  
  @yield('content')
</div>
  
  <!-- Control Sidebar -->
  
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- Local Asset -->
<script src="{{asset('js/app.js')}}"></script>

<!-- jQuery -->

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.bootstrap4.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.print.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.25/dataRender/datetime.js"></script>
 <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
@yield('javascripts')
@yield('footer-scripts')
<script>





</script>
</body>

</html>
