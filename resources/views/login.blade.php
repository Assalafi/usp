<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>UNIMAID PORTAL</title>
    <meta charset="utf-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description"
        content="Access the Unimaid Portal to manage student information, academic results, course registration, and more. Stay connected with the University of Maiduguri online.">
    <meta name="keywords"
        content="Unimaid Portal, University of Maiduguri, student portal, academic results, course registration">
    <meta name="author" content="University of Maiduguri">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Unimaid Portal - University of Maiduguri Online Access">
    <meta property="og:description"
        content="Welcome to the Unimaid Portal - your gateway to student information, academic results, and more at the University of Maiduguri.">
    <meta property="og:image" content="{{ asset('uploads/logo.png') }}">
    <meta property="og:url" content="https://www.umstad.online">
    <meta property="og:type" content="website">
    <link rel="icon" href="{{ asset('uploads/logo.png') }}" type="image/x-icon">
    <link rel="canonical" href="https://www.umstad.online">

    <!-- fontawesome icon -->
    <link rel="stylesheet" href="{{ asset('dashboard/fonts/fontawesome/css/fontawesome-all.min.css') }}">
    <!-- data tables css -->
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/data-tables/css/datatables.min.css') }}">
    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/select2/css/select2.min.css') }}">
    <!-- material datetimepicker css -->
    <link rel="stylesheet"
        href="{{ asset('dashboard/plugins/material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}">
    <!-- minicolors css -->
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/mini-color/css/jquery.minicolors.css') }}">
    <!-- toastr css -->
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/toastr/css/toastr.min.css') }}">

    <!-- page css -->
    {{-- @yield('page_css') --}}


    <!-- vendor css -->
    <link rel="stylesheet" href="{{ asset('dashboard/css/style.css') }}" type="text/css" media="screen, print">
    <link rel="stylesheet" href="{{ asset('countdown/css/style.min.css') }}">


    <style type="text/css" media="screen">
        h3 {
            font-size: 18px;
        }

        .auth-logo {
            position: absolute;
            left: 40px;
            top: 10px;
            overflow: hidden;
        }

        .auth-logo img {
            max-height: 100px;
            max-width: 100px;
        }

        @media screen and (max-width: 767px) {
            .auth-logo img {
                max-height: 70px;
            }
        }
    </style>

</head>

<body class="portal-login">



    <main class="portal-login-shell">
        <section class="portal-login-intro" aria-label="Portal introduction">
            <div class="portal-login-mark"><img src="{{ asset('uploads/logo.png') }}" alt="University of Maiduguri logo"></div>
            <p class="portal-login-overline">University of Maiduguri</p>
            <h1>UNIMAID<br>Portal</h1>
            <p class="portal-login-tagline">Secure access to your university services.</p>
            <div class="portal-login-line" aria-hidden="true"></div>
            <small>Use your registered username and password to continue.</small>
        </section>

        <section class="portal-login-panel">
            <div class="portal-login-panel-head">
                <span class="portal-login-mini-mark"><img src="{{ asset('uploads/logo.png') }}" alt=""></span>
                <span>UNIMAID Portal</span>
            </div>
            <div class="portal-login-form-head">
                <span class="portal-login-kicker">Welcome back</span>
                <h2>Sign in</h2>
                <p>Enter your details to continue.</p>
            </div>

            <form method="POST" action="auth" class="portal-login-form">
                @csrf
                <label for="email">Username</label>
                <div class="portal-input">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    <input id="email" type="text" class="form-control" name="email"
                        value="{{ old('email') }}" required autocomplete="username" placeholder="Enter your username"
                        autofocus>
                </div>
                <label for="password">Password</label>
                <div class="portal-input portal-password-input">
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    <input id="password" type="password" class="form-control" name="password" required
                        autocomplete="current-password" placeholder="Enter your password">
                    <button type="button" class="portal-password-toggle" aria-label="Show password" data-password-toggle="password"><i class="fas fa-eye"></i></button>
                </div>
                <button type="submit" class="btn btn-primary portal-submit" name="submit">
                    Sign in <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i>
                </button>
            </form>

            <div class="portal-login-help">
                <span>Need help signing in?</span>
                <a href="tel:+2347036982856">Contact support</a>
            </div>
            <a href="/inter-university-transfer/register" class="portal-transfer-link"><i class="fas fa-exchange-alt" aria-hidden="true"></i> Inter-University Transfer application</a>
        </section>
    </main>
    <!-- Required Js -->
    <script src="{{ asset('dashboard/plugins/jquery/js/jquery.min.js') }}"></script>
    <script src="{{ asset('dashboard/plugins/popper/js/popper.min.js') }}"></script>
    <script src="{{ asset('dashboard/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('dashboard/plugins/jquery-scrollbar/js/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/pcoded.min.js') }}"></script>

    <!-- datatable Js -->
    <script src="{{ asset('dashboard/plugins/data-tables/js/datatables.min.js') }}"></script>

    <!-- form-validation Js -->
    <script src="{{ asset('dashboard/js/pages/form-validation.js') }}"></script>

    <!-- select2 Js -->
    <script src="{{ asset('dashboard/plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- material datetimepicker Js -->
    <script src="{{ asset('dashboard/plugins/moment/js/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset('dashboard/plugins/material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}">
    </script>

    <!-- Input mask Js -->
    <script src="{{ asset('dashboard/plugins/inputmask/js/autoNumeric.js') }}"></script>

    <!-- minicolors Js -->
    <script src="{{ asset('dashboard/plugins/mini-color/js/jquery.minicolors.min.js') }}"></script>

    <!-- toastr Js -->
    <script src="{{ asset('dashboard/plugins/toastr/js/toastr.min.js') }}"></script>
    <!-- Toastr message display -->
    <script src="{{ url('assets/js/plugins/sweetalert.min.js') }}"></script>
    <script src="{{ url('assets/js/pages/ac-alert.js') }}"></script>
    <script src="{{ url('countdown/js/countdown.js') }}"></script>
    <script src="{{ url('countdown/js/init.js') }}"></script>

    @if (session('success'))
        <script>
            swal("", "{{ session('success') }}", "success");
        </script>
    @endif
    @if (session('error'))
        <script>
            swal("Oops!!!", "{{ session('error') }}", "error");
        </script>
    @endif
    @if (session('info'))
        <script>
            swal("", "{{ session('info') }}", "info");
        </script>
    @endif
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.passwordToggle);
                const icon = this.querySelector('i');
                if (!input) return;
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                this.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
                if (icon) icon.className = showing ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
        });

        document.querySelector('.portal-login-form')?.addEventListener('submit', function () {
            const button = this.querySelector('button[type="submit"]');
            if (!button || button.disabled) return;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Signing in...';
        });
    </script>
</body>

</html>
@php
    session()->pull('error');
@endphp
