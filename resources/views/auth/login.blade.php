<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('sufee-admin/vendors/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('sufee-admin/vendors/font-awesome/css/font-awesome.min.css') }}">

    <link rel="stylesheet" href="{{ asset('sufee-admin/assets/css/style.css') }}">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

</head>

<body class="bg-white">

    <div class="sufee-login d-flex align-content-center flex-wrap">
        <div class="container">
            <div class="login-content">
                
                <div class="login-form">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            {{ session('error') }}
                            <button class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif
                    <form action="{{ route('login') }}" method="post">
                        @csrf
                        <div class="form-group">
                        <div class="login-logo">
                    <a href="{{ route('login') }}">
                        <h1 class="text-white font-weight-bold">{{ Site('nama_toko') }}</h1>
                       
                    </a>
                </div>
                
                            <label>Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" placeholder="Username" value="{{ old('username') }}" autofocus>

                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Password">

                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="remember"> Remember Me
                            </label>
                        </div>
                        <button type="submit" class="btn btnlogin btn-flat m-b-30 m-t-25 ">Sign in</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="{{ asset('sufee-admin/vendors/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('sufee-admin/vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>

</body>

</html>
