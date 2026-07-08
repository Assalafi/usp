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

<body>



    <div class="auth-wrapper">
        @if (isset($setting))
            @if (is_file('uploads/setting/pic.jpg'))
                <a href="#" class="auth-logo">
                    <img src="{{ asset('uploads/setting/pic.jpg') }}" alt="logo">
                </a>
            @endif
        @endif

        <div class="auth-content">
            <!-- Start Content-->
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-4">
                        {{-- <i class="feather icon-unlock auth-icon"></i> --}}
                        <img src="{{ asset('uploads/logo.png') }}" alt="logo" width="100">
                        <br>
                        <h4 style="font-family: algerian; color: #008ed6">UNIVERSITY OF MAIDUGURI</h4>
                        <h5 style="font-family: algerian">(UNIMAID PORTAL)</h5>
                    </div>
                    <h3 class="mb-4">Login</h3>

                    <!-- Form Start -->
                    <form method="POST" action="auth">
                        @csrf
                        <div class="input-group mb-3">
                            <input id="email" type="text" class="form-control" name="email"
                                value="{{ old('email') }}" required autocomplete="email" placeholder="Username"
                                autofocus>
                        </div>
                        <div class="input-group mb-4">
                            <input id="password" type="password" class="form-control" name="password" required
                                autocomplete="current-password" placeholder="Password">
                        </div>
                        <input type="submit" class="btn btn-primary shadow-2 mb-4" name="submit" value="Login">
                    </form>
                    <!-- Form End -->

                    <div class="alert alert-success shadow-sm"
                        style="border-left: 5px solid #006400; text-align: left; font-size: 14px;">
                        <strong><i class="fas fa-exchange-alt mr-1"></i> Inter-University Transfer:</strong>
                        Seeking transfer to the University of Maiduguri?
                        <a href="/inter-university-transfer/register" class="font-weight-bold">Click here to apply</a>.
                    </div>

                    {{-- <p class="mb-0 text-muted">
                            Validate your PIN
                            <a href="validate H-Pin">
                                here
                            </a>
                        </p> --}}
                    <br>
                    <p class="mb-0 text-muted">
                        Forgot Password? click
                        <a href="#">
                            here
                        </a>
                    </p>
                    <br>
                    <p class="mb-0 text-muted">
                        For any inquiries or assistance, contact us via WhatsApp <a
                            href="tel:+2347036982856"><strong>07036982856</strong></a>.
                    </p>
                </div>
            </div>
            <!-- End Content-->

        </div>
    </div>
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
</body>

</html>
@php
    session()->pull('error');
@endphp
