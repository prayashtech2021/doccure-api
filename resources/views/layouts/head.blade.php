<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Favicon -->
<link rel="apple-touch-icon" sizes="180x180" href="{{ URL::asset('public/img/fav/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ URL::asset('public/img/fav/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ URL::asset('public/img/fav/favicon-16x16.png') }}">
<link rel="manifest" href="{{ URL::asset('public/img/fav/site.webmanifest') }}">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">
<!-- Page Title -->
<title> @yield('browser_title') | Doctor Portal</title>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,400i,500,500i,600,700,900|Work+Sans:100,200,300,400,500,600,700,800,900" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Muli:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

<!-- Noty CSS -->
<link rel="stylesheet" type="text/css" href="{{ URL::asset('public/plugins/noty/css/noty.css') }}">

<!-- Theme CSS -->
<link rel="stylesheet" type="text/css" href="{{ URL::asset('public/less/theme.css') }}">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('public/less/responsive.css') }}">
<!-- Custom CSS -->
<link rel="stylesheet" type="text/css" href="{{ URL::asset('public/css/custom.css') }}">

<style type="text/css">
  .navbar {
  margin-bottom:0px !important;
}
.category_table{
    width:1000px !important;
}
.nav-item active{
  background:red !important;
}
</style>



