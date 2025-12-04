<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Panel de Control')</title>
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- CSS de AdminLTE -->
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/skins/skin-blue.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css?v=2') }}"> <!-- Agrega ?v=2 para romper caché -->
    <link rel="stylesheet" href="{{ asset('css/modals.css') }}"> 
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body class="hold-transition skin-blue sidebar-mini">
{{-- Protección por pestaña: la ejecutamos LO ANTES POSIBLE para que la segunda pestaña no muestre contenido --}}
<script>
(function() {
    try {
        if (!window.localStorage || !window.sessionStorage) return;

        var isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        if (!isAuthenticated) return;

        var TAB_KEY = 'gp_active_tab_id';
        var myId = sessionStorage.getItem(TAB_KEY);

        if (!myId) {
            myId = Date.now().toString() + '_' + Math.random().toString(36).substr(2, 8);
            sessionStorage.setItem(TAB_KEY, myId);
        }

        var activeId = localStorage.getItem(TAB_KEY);

        if (activeId && activeId !== myId) {
            // Usamos replace para no dejar historial y que sea más rápido
            window.location.replace('{{ route('login.form') }}');
            return;
        }

        localStorage.setItem(TAB_KEY, myId);

        window.addEventListener('beforeunload', function() {
            var currentActive = localStorage.getItem(TAB_KEY);
            if (currentActive === myId) {
                localStorage.removeItem(TAB_KEY);
            }
        });
    } catch (e) {}
})();
</script>
<div class="wrapper">
    @include('layouts.header')
    @include('layouts.sidebar')

    <div class="content-wrapper">
        @yield('content')
    </div>

    @include('layouts.footer')

</div>

<!-- JS de AdminLTE -->
<script src="{{ asset('adminlte/bower_components/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/Chart.js/Chart.js') }}"></script>
<script>
window.addEventListener('pageshow', e => e.persisted && location.reload());
</script>
<script>
(function(){
    try {
        var token = '{{ session('tab_token_' . auth()->id()) }}';
        if (!token) return;
        // Fetch: adjuntar X-Tab-Token por defecto
        var origFetch = window.fetch;
        window.fetch = function(input, init){
            init = init || {};
            init.headers = init.headers || {};
            if (init.headers instanceof Headers) {
                if (!init.headers.has('X-Tab-Token')) init.headers.set('X-Tab-Token', token);
            } else if (Array.isArray(init.headers)) {
                var has = init.headers.some(function(h){ return (h[0]||'').toLowerCase() === 'x-tab-token'; });
                if (!has) init.headers.push(['X-Tab-Token', token]);
            } else {
                if (!('X-Tab-Token' in init.headers)) init.headers['X-Tab-Token'] = token;
            }
            return origFetch(input, init);
        };
        // jQuery AJAX: adjuntar X-Tab-Token por defecto
        if (window.$ && $.ajaxSetup) {
            $.ajaxSetup({ headers: { 'X-Tab-Token': token } });
        }
    } catch (e) { /* noop */ }
})();
</script>
@stack('scripts')
</body>
</html>