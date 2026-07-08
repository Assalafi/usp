@php
    use Illuminate\Support\Facades\DB;
    $data = DB::table('students')->where('user_id', session('id'))->select('faculty', 'department', 'program', 'level_of_entry', 'jamb_no')->get();
    foreach ($data as $row) {
        // code...
    }
    $invs = DB::table('invoices')->where(['username' => session('id'), 'description' => 'UNIVERSITY OF MAIDUGURI-1000127 FEES', 'status' => 'Pending'])->orderBy('id', 'ASC')->get();
    $amount = DB::table('school_fees')->where(['program' => $row -> program, 'level' => $row -> level_of_entry, 'type' => 'NEW'])->select('amount')->value('amount');
@endphp
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
                            <i class="fas fa-money-bill-wave auth-icon"></i>
                        </div>
                        <h3 class="mb-4">Schools Fees</h3>
                        <!-- Form Start -->
                        <div class="card-body">
                            <p>COURSE: {{ DB::table('program')->where('code', $row->program)->value('title') }}</p>
                            <p>Amount: N{{ number_format($amount,2) }}</p>
                            <p>Minimum Amount to Pay: N{{ number_format($amount*0.5,2) }}</p>
                        </div>
                        @forelse ($invs as $inv)
                            <div class="card-footer bg-transparent border-success text-center">
                                <?php
                                $rrr = $inv -> rrr;
                                $merchantId = env('REMITA_MERCHANT_ID');
                                $apiKey = env('REMITA_API_KEY');
                                $hash = hash('sha512', $merchantId.''.$rrr.''.$apiKey);
                            ?>
                            <form action="{{ env('REMITA_CURLOPT_URL') }}finalize.reg" method="POST">
                                <input name="merchantId" value="{{ env('REMITA_MERCHANT_ID') }}" type="hidden">
                                <input name="hash" value="<?php echo $hash?>" type="hidden">
                                <input name="rrr"  value="<?php echo $rrr;?>" type="hidden">
                                <input name="responseurl" value="{{ env('REMITA_RESPONSE') }}response" type="hidden">
                                <input type="submit"value="Pay Now Via Remita" class="btn btn-danger btn-lg">
                            </form>
                            </div>
                        @empty
                            @if ($amount > 0)
                                <form action="invoices/school-fees" method="get" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="page" value="school-fees">
                                    <input type="hidden" name="try" value="1">
                                    <label>Amount to Pay</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-addon">
                                            <i class="fas fa-money-check f-40"></i>
                                        </span>
                                        <select name="amount" class="form-control" id="p2" required>
                                            <option value="">Select Amount*</option>
                                            <option value="{{ $amount }}">Full Payment (100%) {{ 'N'.number_format($amount,2) }}</option>
                                            <option value="{{ $amount*0.5 }}">Half Payment (50%) {{ 'N'.number_format($amount*0.5,2) }}</option>
                                        </select>
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-addon">
                                            <i class="fas fa-user f-40"></i>
                                        </span>
                                        <input id="email" type="email" class="form-control" name="email" required placeholder="Email Address">
                                    </div>
                                    <input type="submit" id="submitButton" class="btn btn-primary shadow-2 mb-4" name="submit" value="Generate Invoice">
                                </form>
                            @else
                                <div class="text-center">
                                    <h3>Your Department Fees is not Ready. Try Later</h3>
                                </div>
                            @endif
                        @endforelse
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create"><i class="fas fa-money-check"></i> {{ ('Verify Invoice') }}</a>

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

        <!-- Show modal content -->
        <div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel">Verify Invoice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="card">
                        <form class="form-group needs-validation" novalidate action="verify" method="GET" enctype="multipart/form-data">
                            <div class="card-body">
                                <!-- Details View Start -->
                                @csrf
                                <div class="form-group">
                                    <label for="rrr"></label>
                                    <input type="text" name="rrr" id="rrr" placeholder="Enter RRR" class="form-control" required>
                                    <div class="invalid-feedback"> You must enter RRR number </div>
                                </div>
                                <!-- Details View End -->
                            <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Verify</button>
                            </div>
                        </form>
                    </div>

                </div>
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
