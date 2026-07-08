<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>UNIMAID</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="description" content="Unimaid" />
        <meta name="keywords" content="">
        <meta name="author" content="AAA" />
        <!-- Favicon icon -->
        <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

        <!-- fontawesome icon -->
        <link rel="stylesheet" href="{{ asset('dashboard/fonts/fontawesome/css/fontawesome-all.min.css') }}">
        <!-- data tables css -->
        <link rel="stylesheet" href="{{ asset('dashboard/plugins/data-tables/css/datatables.min.css') }}">
        <!-- select2 css -->
        <link rel="stylesheet" href="{{ asset('dashboard/plugins/select2/css/select2.min.css') }}">
        <!-- material datetimepicker css -->
        <link rel="stylesheet" href="{{ asset('dashboard/plugins/material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}">
        <!-- minicolors css -->
        <link rel="stylesheet" href="{{ asset('dashboard/plugins/mini-color/css/jquery.minicolors.css') }}">
        <!-- toastr css -->
        <link rel="stylesheet" href="{{ asset('dashboard/plugins/toastr/css/toastr.min.css') }}">


        <!-- page css -->
        {{-- @yield('page_css') --}}


        <!-- vendor css -->
        <link rel="stylesheet" href="{{ asset('dashboard/css/style.css') }}" type="text/css" media="screen, print">


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

            @if(isset($setting))
            @if(is_file('uploads/setting/pic.jpg'))
            <a href="#" class="auth-logo">
                <img src="{{ asset('uploads/setting/pic.jpg') }}" alt="logo">
            </a>
            @endif
            @endif

            <div class="auth-content">
                <div class="auth-bg">
                    <span class="r"></span>
                    <span class="r s"></span>
                    <span class="r s"></span>
                    <span class="r"></span>
                </div>

                <!-- Start Content-->
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="feather icon-unlock auth-icon"></i>
                        </div>
                        <h3 class="mb-4">Reset Password</h3>
                        <!-- Form Start -->
                        <form method="POST" action="forgot">
                        @csrf
                            <div class="input-group mb-3">
                                <input id="email" type="text" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter Validated ID Number" autofocus>
                            </div>
                            <div class="input-group mb-4">
                                <input id="password" type="text" class="form-control" name="password" required autocomplete="current-password" placeholder="Enter Validated PIN">
                            </div>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="MALE">Male</option>
                                <option value="FEMALE">Female</option>
                            </select>
                            <br>
                            <hr>

                            <input type="submit" class="btn btn-primary shadow-2 mb-4" name="submit" value="Reset">
                        </form>
                        <!-- Form End -->

                        <p class="mb-0 text-muted">
                            Back to login
                            <a href="/">
                                here
                            </a>
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
    <script src="{{ asset('dashboard/plugins/material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}"></script>

    <!-- Input mask Js -->
    <script src="{{ asset('dashboard/plugins/inputmask/js/autoNumeric.js') }}"></script>

    <!-- minicolors Js -->
    <script src="{{ asset('dashboard/plugins/mini-color/js/jquery.minicolors.min.js') }}"></script>

    <!-- toastr Js -->
    <script src="{{ asset('dashboard/plugins/toastr/js/toastr.min.js') }}"></script>
    <!-- Toastr message display -->
    <script src="{{ url('assets/js/plugins/sweetalert.min.js') }}"></script>
    <script src="{{ url('assets/js/pages/ac-alert.js') }}"></script>

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
    </body>
</html>
