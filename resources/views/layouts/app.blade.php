<!DOCTYPE html>
<html lang="en">
 @include('layouts/head')
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Doctor Portal - Dashboard</title>

<!-- Custom fonts for this template-->
  <link href="{{URL::asset('public/vendor/fontawesome-free/css/all.min.css')}}" rel="stylesheet" type="text/css">

  <!-- Page level plugin CSS-->
  <link href="{{URL::asset('public/vendor/datatables/dataTables.bootstrap4.css')}}" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="{{URL::asset('public/css/sb-admin.css')}}" rel="stylesheet">


  <style type="text/css">

.nav-item active{
  background:red !important;
}
</style>
</head>

<body id="page-top">

  <nav class="navbar navbar-expand navbar-dark bg-dark static-top">

    <a class="navbar-brand mr-1" href="{{url('/')}}">Doctor Portal</a>

    <button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" href="#">
      <i class="fas fa-bars"></i>
    </button>
    <!-- Navbar -->
    <ul class="navbar-nav ml-auto ml-md-0">
      <li class="nav-item dropdown no-arrow">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-user-circle fa-fw">{{Auth::user()->name ?  Auth::user()->name : ''}}</i>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="{{route('logout')}}" >Logout</a>
        </div>
      </li>
    </ul>

  </nav>

  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="sidebar navbar-nav">
      <li class="nav-item active">
        <a class="nav-link" href="{{url('/')}}">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span style="font-size:20px;">Dashboard</span>
        </a>
      </li>
      <li class="nav-item  {{ Request::is('appointment/list') ? 'active' : (Request::is('appointment/add') ? 'active' :'') }}">
        <a class="nav-link" href="#" >
          <i class="fas fa-fw"></i>
          <span style="font-size:16px;">Menu1</span>
        </a>
      </li>
      <li class="nav-item {{ Request::is('appointment/app-request-list') ? 'active' : '' }}">
        <a class="nav-link" href="#" >
          <i class="fas fa-fw"></i>
          <span style="font-size:16px;">Meu2</span>
        </a>
      </li>
      <li class="nav-item {{ Request::is('appointment/app-reject-list') ? 'active' : '' }}">
        <a class="nav-link" href="#" >
          <i class="fas fa-fw"></i>
          <span style="font-size:16px;">Menu3</span>
        </a>
      </li>
    </ul>
@yield('content')
 </div>
  <!-- /#wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <a class="btn btn-primary" href="{{url('/logout')}}">Logout</a>
        </div>
      </div>
    </div>
  </div>
 @include('layouts/footer')
 @yield('footer_scripts')
 @if(Session::has('success'))
      <script type="text/javascript">
        $(document).ready(function(){
            new Noty({
              type: 'success',
              layout: 'topRight',
              text: '<?php echo Session::get('success') ?>',
              timeout:3000
          }).show();
        })
      </script>
      @endif
    

</body>

</html>
