<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <!-- Meta data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta content="Pioneers" name="description">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="{{ asset('/favicons/browserconfig.xml') }}">
    <meta name="theme-color" content="#ffffff">

    <!-- Title -->
    <title>Pioneers SA - Password Reset</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/favicons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('/favicons/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ asset('/favicons/safari-pinned-tab.svg') }}" color="#5bbad5">
    <link rel="shortcut icon" href="{{ asset('/favicons/favicon.ico') }}">

    <!-- Bootstrap css -->
    <link id="style" href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />

    <!-- Style css -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" />

    <!-- Plugin css -->
    <link href="{{ asset('css/plugin.css') }}" rel="stylesheet" />

    <!-- Animate css -->
    <link href="{{ asset('css/animated.css') }}" rel="stylesheet" />

    <!-- Icons css -->
    <link href="{{ asset('plugins/web-fonts/icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('plugins/web-fonts/font-awesome/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/web-fonts/plugin.css') }}" rel="stylesheet" />

    <!-- Custom css -->
    <style>
        .reset-container {
            display: flex;
            min-height: 100vh;
        }

        .reset-form {
            width: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background-color: #f8f9fa;
            position: relative;
            z-index: 1;
        }

        .reset-info {
            background-color: #505151; /* Dark background color */
            width: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .reset-info div {
            position: relative;
            z-index: 1;
        }

        .reset-info img {
            max-width: 80%;
        }

        .reset-form form {
            width: 100%;
            max-width: 400px;
        }

        .reset-form .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .reset-form .btn-link {
            color: #007bff;
        }

        .reset-form .form-control {
            border-radius: 0.25rem;
        }

        .reset-form .back-link {
            margin-top: 20px;
            display: block;
            text-align: center;
        }
    </style>
</head>

<body class="main-body light-mode ltr page-style1 error-page bg4">
    <div class="reset-container">
        <div class="reset-form">
            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @elseif(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password-confirm">Confirm Password</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                </div>
                <div class="form-group mb-3">
                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </div>
                <div class="back-link">
                    <a href="{{ route('login') }}" class="btn btn-link">Back to Login</a>
                </div>
            </form>
        </div>
        <div class="reset-info">
            <div>
                <img src="{{ asset('images/logo/logo.png') }}" alt="Logo" style="width: 200px; margin-bottom: 20px;">
                <h1>Reset Your Password</h1>
                <p>Please enter and confirm your new password.</p>
            </div>
        </div>
    </div>

    <!-- jQuery js -->
    <script src="{{ asset('js/vendors/jquery.min.js') }}"></script>

    <!-- Bootstrap5 js -->
    <script src="{{ asset('plugins/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.min.js') }}"></script>

    <!-- Othercharts js -->
    <script src="{{ asset('plugins/othercharts/jquery.sparkline.min.js') }}"></script>

    <!-- Circle-progress js -->
    <script src="{{ asset('js/vendors/circle-progress.min.js') }}"></script>

    <!-- jQuery-rating js -->
    <script src="{{ asset('plugins/rating/jquery.rating-stars.js') }}"></script>

    <!-- P-scroll js -->
    <script src="{{ asset('plugins/p-scrollbar/p-scrollbar.js') }}"></script>

    <!-- Color Theme js -->
    <script src="{{ asset('js/themeColors.js') }}"></script>

    <!-- Switcher-Styles js -->
    <script src="{{ asset('js/switcher-styles.js') }}"></script>

    <!-- Custom js -->
    <script src="{{ asset('js/custom.js') }}"></script>
</body>

</html>
