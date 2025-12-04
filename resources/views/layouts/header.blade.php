<head>
    <meta charset="UTF-8">
    <title>Zpend Dashboard</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"> <!-- fuentes nuevas -->
    @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.js'])
</head>
<body class="hold-transition sidebar-mini">
    <!-- Encabezado principal -->
    <header class="main-header">
        <a href="#" class="logo">
            <span class="logo-mini"><b>Z</b>ND</span>
            <span class="logo-lg"><b>ZPEND</b></span>
        </a>
        <nav class="navbar navbar-static-top">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
           <!-- Menú de cuenta de usuario -->
            @if(auth()->check())
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                @if(auth()->user()->img)
                                    <img src="{{ auth()->user()->img }}" class="user-image" alt="Imagen de usuario">
                                @else
                                    <i class="fa fa-user user-image-icon" style="color: #4285f4; width: 35px; height: 35px; line-height: 35px; text-align: center;"></i>
                                @endif
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="user-menu-content">
                                        <div class="user-menu-avatar">
                                            @if(auth()->user()->img)
                                                <img src="{{ auth()->user()->img }}" class="user-image-dropdown" alt="Imagen de usuario">
                                            @else
                                                <i class="fa fa-user user-image-icon" style="color: #4285f4; width: 50px; height: 50px; line-height: 50px; text-align: center;"></i>
                                            @endif
                                        </div>
                                        <div class="user-menu-info">
                                            <p class="user-name">¡Hola, {{ auth()->user()->name ?? 'Usuario' }}!</p>
                                            <p class="user-email">{{ auth()->user()->email ?? 'correo@ejemplo.com' }}</p>
                                        </div>
                                    </div>
                                    <div class="user-menu-actions">
                                        <a href="{{ secure_url(route('profile.show')) }}" class="btn btn-default btn-flat">Ver Perfil</a>
                                        <a href="{{ route('logout') }}" class="btn btn-default btn-flat" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar sesión</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            @endif
        </nav>
    </header>
    <!-- Formulario oculto de cierre de sesión -->
    @if(auth()->check())
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    @endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.main-sidebar');

    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });
});
</script>
</body>