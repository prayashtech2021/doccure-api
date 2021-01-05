<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Doctor Portal- Login</title>

  <link href="{{URL::asset('public/vendor/fontawesome-free/css/all.min.css')}}" rel="stylesheet" type="text/css">
    <!-- Custom styles for this template-->
    <link href="{{URL::asset('public/css/sb-admin.css')}}" rel="stylesheet">

</head>

<body class="bg-dark">

  <div class="container">
    <div class="card card-login mx-auto mt-5">
      <div class="card-header">Time Doctor Portal Login</div>
      <div class="card-body">
        <form method="POST" action="{{ route('login') }}">
            {{ csrf_field() }}
          <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
            <div class="form-label-group">
              <input type="email" id="email" class="form-control" placeholder="Email address" required="required" autofocus="autofocus" name="email" autocomplete="off">
               @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
              <label for="email">Email address</label>
            </div>
          </div>
          <div class="form-group">
            <div class="form-label-group">
              <input type="password" id="password" class="form-control" placeholder="Password" required="required" name="password" autocomplete="off">
              <label for="password">Password</label>
            </div>
                @if ($errors->has('password'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
          </div>
          <button type="submit" class="btn btn-primary">
                                        Login
                                    </button>
        </form>
        <div class="text-center">
          <a class="d-block small mt-3" href="{{ route('register') }}">Register an Account</a>
            </a>
        </div>
      </div>
    </div>
  </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{URL::asset('public/vendor/jquery/jquery.min.js')}}"></script>
    <script src="{{URL::asset('public/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <!-- Core plugin JavaScript-->
    <script src="{{URL::asset('public/vendor/jquery-easing/jquery.easing.min.js')}}"></script>

</body>

</html>
