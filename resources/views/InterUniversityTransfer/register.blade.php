<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Inter-University Transfer - Registration | UNIMAID PORTAL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Register for Inter-University Transfer to the University of Maiduguri">
    <link rel="icon" href="{{ asset('uploads/logo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('dashboard/fonts/fontawesome/css/fontawesome-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/css/style.css') }}" type="text/css" media="screen, print">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #006400 0%, #008ed6 100%);
        }
        .register-card {
            max-width: 550px;
            width: 100%;
            margin: 20px;
        }
        .register-card .card {
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .form-group label {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .btn-register {
            background: linear-gradient(135deg, #006400, #008ed6);
            border: none;
            padding: 10px 30px;
            font-weight: 600;
        }
        .btn-register:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="register-card">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ asset('uploads/logo.png') }}" alt="logo" width="80">
                        <h4 style="font-family: algerian; color: #008ed6" class="mt-2">UNIVERSITY OF MAIDUGURI</h4>
                        <h6 class="text-muted">Inter-University Transfer Application</h6>
                        <p class="text-muted small">Create an account to begin your transfer application</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('inter-transfer.register.post') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Surname <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                        name="surname" value="{{ old('surname') }}" required placeholder="Surname">
                                    @error('surname') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                        name="first_name" value="{{ old('first_name') }}" required placeholder="First Name">
                                    @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" value="{{ old('middle_name') }}"
                                placeholder="Middle Name (Optional)">
                        </div>

                        <div class="form-group mb-3">
                            <label>Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required placeholder="your@email.com">
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            <small class="text-muted">This will be your login username</small>
                        </div>

                        <div class="form-group mb-3">
                            <label>Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                name="phone" value="{{ old('phone') }}" required placeholder="08012345678">
                            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Gender</label>
                            <select class="form-control" name="gender">
                                <option value="MALE">Male</option>
                                <option value="FEMALE">Female</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        name="password" required placeholder="Min 6 characters">
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password_confirmation" required
                                        placeholder="Confirm Password">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-register btn-block mb-3 w-100">
                            <i class="fas fa-user-plus mr-2"></i> Create Account
                        </button>

                        <div class="text-center">
                            <p class="mb-0 text-muted">Already have an account? <a href="/">Login here</a></p>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <p class="text-white small">
                    <strong>Transfer Fees:</strong><br>
                    Within Nigeria: &#8358;150,000.00 | Abroad: &#8358;250,000.00
                </p>
            </div>
        </div>
    </div>

    <script src="{{ asset('dashboard/plugins/jquery/js/jquery.min.js') }}"></script>
    <script src="{{ asset('dashboard/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ url('assets/js/plugins/sweetalert.min.js') }}"></script>

    @if (session('success'))
        <script>swal("", "{{ session('success') }}", "success");</script>
    @endif
    @if (session('error'))
        <script>swal("Oops!", "{{ session('error') }}", "error");</script>
    @endif
</body>

</html>
