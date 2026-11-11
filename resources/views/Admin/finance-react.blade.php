@php
    $isAdmin = session('accType') === 'Admin';
    $financeBoot = [
        'mode' => $page === 'remita finance live' ? 'live' : 'records',
        'session' => $selectedFinanceSession ?? $fees_session,
        'sessions' => ($financeSessions ?? $session)->pluck('title')->values(),
        'faculties' => $faculty ?? [],
        'admin' => $isAdmin,
        'upload' => $isAdmin && in_array(strtoupper((string) session('username')), ['SP11913', 'SU'], true),
        'csrf' => csrf_token(),
    ];
@endphp
<link rel="stylesheet" href="{{ asset('css/finance-react.css') }}?v={{ filemtime(public_path('css/finance-react.css')) }}">
<script type="application/json" id="finance-config">{!! json_encode($financeBoot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<div id="finance-react-root"><div class="fr-boot"><span></span>Opening finance overview…</div></div>
<noscript>Please enable JavaScript to view collections and payment records.</noscript>
@if($isAdmin && $page === 'fees due')
<div class="fr-legacy">@include('Admin.finance-payment-tools')</div>
@endif
<script src="{{ asset('js/finance-react.js') }}?v={{ filemtime(public_path('js/finance-react.js')) }}" defer></script>
