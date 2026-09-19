<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>UNIMAID PORTAL</title>
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

<link rel="stylesheet" href="{{ url('dashboard/fonts/fontawesome/css/fontawesome-all.min.css') }}">
<link rel="stylesheet" href="{{ url('dashboard/plugins/data-tables/css/datatables.min.css') }}">
<link rel="stylesheet"
    href="{{ url('dashboard/plugins/material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}">
<link rel="stylesheet" href="{{ url('dashboard/plugins/toastr/css/toastr.min.css') }}">
<link rel="stylesheet" href="{{ url('dashboard/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ url('dashboard/css/style.css') }}">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
{{--
<link href="{{ asset('assets/sdata2/src/selectstyle.css') }}" rel="stylesheet">
<script src="{{ asset('assets/sdata2/src/selectstyle.js') }}"></script> --}}
<!-- Required Js -->
<script src="{{ asset('dashboard/plugins/jquery/js/jquery.min.js') }}"></script>
<script src="{{ asset('dashboard/plugins/popper/js/popper.min.js') }}"></script>
<script src="{{ asset('dashboard/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('dashboard/plugins/jquery-scrollbar/js/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('dashboard/js/pcoded.min.js') }}"></script>

<style>
    .btn-info {
        background-color: #474311;
    }

    @media print {
        .noprint {
            display: none;
        }
    }

    /* Shared UG portal shell. Scoped to the authenticated portal so legacy
       pages can be refreshed without changing the single-login behaviour. */
    .portal-shell {
        --portal-primary: #3ea1e4;
        --portal-primary-dark: #247fb9;
        --portal-ink: #17243b;
        --portal-muted: #6e7d92;
        --portal-border: #e5ebf3;
        background: #f4f8fc;
        color: var(--portal-ink);
    }
    .portal-shell .pcoded-navbar {
        background: #111827 !important;
        box-shadow: 8px 0 22px rgba(15, 23, 42, .08);
        z-index: 1031;
    }
    .portal-shell .navbar-content { border-top: 0 !important; }
    .portal-shell .pcoded-navbar .navbar-brand,
    .portal-shell .pcoded-navbar .navbar-wrapper {
        background: #111827 !important;
    }
    .portal-shell .pcoded-navbar .pcoded-inner-navbar > li > a,
    .portal-shell .pcoded-navbar .pcoded-inner-navbar > li > a .pcoded-mtext,
    .portal-shell .pcoded-navbar .pcoded-inner-navbar > li > a .pcoded-micon {
        color: #dbe7f2 !important;
    }
    .portal-shell .pcoded-navbar .pcoded-inner-navbar > li > a:hover,
    .portal-shell .pcoded-navbar .pcoded-inner-navbar > li.active > a,
    .portal-shell .pcoded-navbar .pcoded-inner-navbar > li.pcoded-trigger > a {
        background: rgba(62, 161, 228, .16) !important;
        color: #fff !important;
    }
    .portal-shell .pcoded-header {
        background: var(--portal-primary) !important;
        box-shadow: 0 3px 15px rgba(28, 87, 130, .16);
        color: #fff;
        z-index: 1030;
    }
    .portal-shell .pcoded-header .topbar-title,
    .portal-shell .pcoded-header .navbar-nav > li > a,
    .portal-shell .pcoded-header .navbar-nav > li > a i {
        color: #fff !important;
    }
    .portal-shell .pcoded-main-container {
        background: #f4f8fc;
    }
    .portal-shell .pcoded-content {
        padding: 22px 20px;
    }
    .portal-shell .card {
        border: 1px solid var(--portal-border);
        border-radius: 14px;
        box-shadow: 0 6px 20px rgba(28, 53, 84, .05);
    }
    .portal-shell .card-header {
        border-bottom-color: var(--portal-border);
        background: #fff;
    }
    .portal-shell .form-control,
    .portal-shell .form-select,
    .portal-shell .select2-container--default .select2-selection--single {
        min-height: 42px;
        border-color: #d7e1ec;
        border-radius: 9px;
    }
    .portal-shell .form-control:focus,
    .portal-shell .form-select:focus {
        border-color: var(--portal-primary);
        box-shadow: 0 0 0 .2rem rgba(62, 161, 228, .16);
    }
    .portal-shell .btn-primary,
    .portal-login .btn-primary {
        border-color: var(--portal-primary);
        background: var(--portal-primary);
    }
    .portal-shell .btn-primary:hover,
    .portal-login .btn-primary:hover {
        border-color: var(--portal-primary-dark);
        background: var(--portal-primary-dark);
    }
    .portal-topbar-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        padding: 0 16px 0 0;
        list-style: none;
    }
    .portal-role-badge {
        padding: 5px 10px;
        border: 1px solid rgba(255, 255, 255, .42);
        border-radius: 999px;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .03em;
    }
    .portal-user-menu {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 5px 9px !important;
        border-radius: 9px;
        background: rgba(255, 255, 255, .12);
    }
    .portal-user-menu:hover { background: rgba(255, 255, 255, .2); }
    .portal-user-menu .portal-user-name {
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 12px;
        font-weight: 600;
    }
    .portal-shell .dropdown-menu {
        border: 1px solid var(--portal-border);
        border-radius: 11px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .14);
    }
    .portal-shell .dropdown-item { padding: 9px 13px; font-size: 13px; }
    .portal-shell .dropdown-item i { width: 18px; margin-right: 5px; }
    @media (max-width: 991px) {
        .portal-shell .pcoded-content { padding: 16px 13px; }
        .portal-role-badge { display: none; }
        .portal-user-menu .portal-user-name { display: none; }
    }
    @media (max-width: 575px) {
        .portal-shell .pcoded-content { padding: 12px 9px; }
        .portal-shell .card { border-radius: 11px; }
        .portal-shell .pcoded-header .m-header .b-brand img { max-width: 34px; max-height: 34px; }
    }

    /* Login is shared by every account type; no role selector is introduced. */
    .portal-login {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 28px 16px;
        background: radial-gradient(circle at 10% 0%, #e9f7ff 0, transparent 38%), #f4f8fc;
    }
    .portal-login .auth-content { width: min(100%, 440px); }
    .portal-login .card {
        overflow: hidden;
        border: 1px solid #e0eaf3;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 18px 50px rgba(28, 68, 104, .12);
    }
    .portal-login .card-body { padding: 34px 34px 28px; }
    .portal-login .portal-login-brand img { width: 76px; height: 76px; object-fit: contain; }
    .portal-login .portal-login-brand h4,
    .portal-login .portal-login-brand h5 { margin: 4px 0 0; font-family: inherit !important; color: var(--portal-ink) !important; }
    .portal-login .portal-login-brand h4 { font-size: 18px; font-weight: 800; }
    .portal-login .portal-login-brand h5 { color: var(--portal-primary-dark) !important; font-size: 12px; font-weight: 700; letter-spacing: .08em; }
    .portal-login .portal-login-title { margin: 24px 0 17px; color: var(--portal-ink); font-size: 21px; font-weight: 800; }
    .portal-login .portal-input { position: relative; margin-bottom: 13px; }
    .portal-login .portal-input i { position: absolute; top: 13px; left: 13px; color: #8191a7; }
    .portal-login .portal-input .form-control { min-height: 46px; padding-left: 40px; }
    .portal-login .portal-submit { width: 100%; min-height: 46px; border-radius: 9px; font-weight: 700; }
    .portal-login .portal-support { margin: 19px 0 0; color: var(--portal-muted); font-size: 12px; line-height: 1.55; }
    @media (max-width: 480px) {
        .portal-login { padding: 16px 10px; }
        .portal-login .card-body { padding: 27px 20px 22px; }
    }
    .portal-login-shell {
        display: grid;
        grid-template-columns: minmax(240px, .88fr) minmax(320px, 1fr);
        width: min(100%, 930px);
        min-height: 560px;
        overflow: hidden;
        border: 1px solid #dce8f2;
        border-radius: 26px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(34, 80, 118, .14);
    }
    .portal-login-intro {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 48px 42px;
        overflow: hidden;
        background: linear-gradient(145deg, #3ea1e4 0%, #2582be 100%);
        color: #fff;
    }
    .portal-login-intro::after {
        position: absolute;
        right: -100px;
        bottom: -125px;
        width: 300px;
        height: 300px;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 50%;
        box-shadow: 0 0 0 30px rgba(255, 255, 255, .04), 0 0 0 62px rgba(255, 255, 255, .03);
        content: '';
    }
    .portal-login-mark {
        display: grid;
        place-items: center;
        width: 72px;
        height: 72px;
        margin-bottom: 28px;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 10px 22px rgba(20, 76, 113, .2);
    }
    .portal-login-mark img { width: 55px; height: 55px; object-fit: contain; }
    .portal-login-overline { margin: 0 0 10px; color: rgba(255, 255, 255, .78); font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .portal-login-intro h1 { margin: 0; color: #fff; font-size: clamp(2.3rem, 5vw, 3.7rem); font-weight: 800; letter-spacing: -.04em; line-height: .98; }
    .portal-login-tagline { max-width: 230px; margin: 20px 0 0; color: rgba(255, 255, 255, .86); font-size: 14px; line-height: 1.55; }
    .portal-login-line { width: 54px; height: 3px; margin: 30px 0 16px; border-radius: 999px; background: rgba(255, 255, 255, .72); }
    .portal-login-intro small { position: relative; z-index: 1; max-width: 230px; color: rgba(255, 255, 255, .72); font-size: 11px; line-height: 1.5; }
    .portal-login-panel { display: flex; flex-direction: column; justify-content: center; padding: 45px 58px; }
    .portal-login-panel-head { display: flex; align-items: center; gap: 9px; color: #38526b; font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .portal-login-mini-mark { display: grid; place-items: center; width: 29px; height: 29px; border: 1px solid #dce8f2; border-radius: 8px; background: #f8fbfd; }
    .portal-login-mini-mark img { width: 22px; height: 22px; object-fit: contain; }
    .portal-login-form-head { margin: 49px 0 26px; }
    .portal-login-kicker { color: var(--portal-primary-dark); font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .portal-login-form-head h2 { margin: 6px 0 6px; color: var(--portal-ink); font-size: 31px; font-weight: 800; letter-spacing: -.035em; }
    .portal-login-form-head p { margin: 0; color: var(--portal-muted); font-size: 13px; }
    .portal-login-form label { display: block; margin: 0 0 7px; color: #40546b; font-size: 12px; font-weight: 700; }
    .portal-login-form .portal-input { margin-bottom: 17px; }
    .portal-login-form .portal-input .form-control { min-height: 48px; border-radius: 10px; background: #fbfdff; }
    .portal-login-form .portal-input .form-control::placeholder { color: #a0adbb; }
    .portal-password-input .form-control { padding-right: 45px; }
    .portal-password-toggle { position: absolute; top: 8px; right: 8px; display: grid; place-items: center; width: 32px; height: 32px; border: 0; border-radius: 7px; background: transparent; color: #8b9bad; }
    .portal-password-toggle:hover { background: #edf5fa; color: var(--portal-primary-dark); }
    .portal-login-form .portal-submit { min-height: 48px; border-radius: 10px; box-shadow: 0 8px 17px rgba(62, 161, 228, .2); }
    .portal-login-help { display: flex; justify-content: space-between; gap: 12px; margin-top: 23px; color: #8997a7; font-size: 11px; }
    .portal-login-help a { color: var(--portal-primary-dark); font-weight: 700; text-decoration: none; }
    .portal-transfer-link { display: inline-flex; align-items: center; gap: 7px; align-self: flex-start; margin-top: 36px; color: #6e7d92; font-size: 11px; font-weight: 700; text-decoration: none; }
    .portal-transfer-link:hover { color: var(--portal-primary-dark); }
    @media (max-width: 700px) {
        .portal-login-shell { display: block; min-height: 0; border-radius: 20px; }
        .portal-login-intro { min-height: 225px; padding: 27px 25px 32px; }
        .portal-login-mark { width: 53px; height: 53px; margin-bottom: 18px; border-radius: 15px; }
        .portal-login-mark img { width: 40px; height: 40px; }
        .portal-login-overline { margin-bottom: 6px; font-size: 9px; }
        .portal-login-intro h1 { font-size: 2.25rem; }
        .portal-login-tagline { margin-top: 10px; font-size: 12px; }
        .portal-login-line, .portal-login-intro small { display: none; }
        .portal-login-panel { padding: 28px 23px 25px; }
        .portal-login-form-head { margin: 30px 0 22px; }
        .portal-login-form-head h2 { font-size: 26px; }
        .portal-transfer-link { margin-top: 27px; }
    }
</style>
