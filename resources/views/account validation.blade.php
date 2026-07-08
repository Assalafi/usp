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
                        <h3 class="mb-4">Update Password</h3>
                        <!-- Form Start -->
                        <form action="account-validation" method="POST" enctype="multipart/form-data">
                        @csrf
                            <div class="form-group">
                                <center>

                                <img src="{{ asset('storage/picture/default.jpg') }}" id="pickImg" class="img-circle" width="70" height="70"/>
                                <br>
                                 <label class="col-sm-12">Passport* (Size <= 200kb)</label>
                                 <div class="col-sm-12">
                                 <input oninput="getImg(event)" type='file' class="form-control" name="picture" id="imgPath" required>
                                </div>
                                <div style="padding: 0;" id="displayStatus" class="alert alert-info">Image Size: <span id="displaySize"></span></div>
                                </center>

                                <div class="alert alert-info" style="font-size: 12px; text-align: left;">
                                    <b>
                                        Note: When uploading a photo, please ensure it meets the following criteria:
                                    <hr>
                                    - Avoid fashion or artistic photos.
                                    <hr>
                                    - The background should be plain and uniform, preferably passport size.
                                    </b>

                                </div>
                            </div>
                            <div class="input-group mb-3">
                                <input id="p2" type="password" class="form-control" name="p1" required placeholder="New Password">
                            </div>
                            <div class="input-group mb-4">
                                <input id="p2" type="password" class="form-control" name="p2" required placeholder="Confirm Password">
                            </div>
                            <input type="submit" id="submitButton" class="btn btn-primary shadow-2 mb-4" name="submit" value="Update">
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

    <script>
    var getImg = function(event) {
        var output = document.getElementById('pickImg');
        output.src = URL.createObjectURL(event.target.files[0]);
        output.onload = function() {
          URL.revokeObjectURL(output.src) // free memory
        }
            var imgPath=document.getElementById('imgPath');
          if (!imgPath.value==""){
            var img=imgPath.files[0].size;
            var imgsize=parseInt(img/1024);
            //alert(imgPath.files[0].value);
            if(imgsize>200){
                document.getElementById('displaySize').innerHTML=imgsize+"kb Rejected";
                document.getElementById('displayStatus').style.backgroundColor="red";
                document.getElementById('displayStatus').style.color="white";
                //document.getElementById('pickImg').src=imgPath.value;
                document.getElementById('submitButton').disabled=true;

            }else{
                document.getElementById('displaySize').innerHTML=imgsize+"kb Accepted";
                document.getElementById('displayStatus').style.backgroundColor="green";
                document.getElementById('displayStatus').style.color="white";
                document.getElementById('submitButton').disabled=false;
            }
          }
      };
    </script>
    @if (session('success'))
        <script>
            swal("", "{{ session('success') }}", "success");
        </script>
    @endif
    @if (session('info'))
        <script>
            swal("", "{{ session('info') }}", "info");
        </script>
    @endif
    @if (session('error'))
        <script>
            swal("Oops!!!", "{{ session('error') }}", "error");
        </script>
    @endif
    </body>
</html>
