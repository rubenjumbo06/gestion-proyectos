<header class="main-header zpend-header">

    <!-- LOGO -->
    <a href="#" class="logo zpend-logo">
        <span class="logo-mini">ZND</span>
        <span class="logo-lg">ZPEND</span>
    </a>

    <!-- NAVBAR -->
    <nav class="navbar navbar-static-top">

        <!-- BOTÓN SIDEBAR -->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                @if(auth()->check())
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <img src="{{ auth()->user()->img }}" 
                                 class="user-image rounded-circle" 
                                 alt="User Image"
                                 onerror="this.src='https://cdn-icons-png.flaticon.com/512/847/847969.png'">
                            <span class="hidden-xs">{{ auth()->user()->name }}</span>
                        </a>

                        <!-- DROPDOWN MODERNO 2025 -->
                        <ul class="dropdown-menu dropdown-user-modern">
                            <!-- Header del dropdown -->
                            <li class="user-header bg-primary">
                                <img src="{{ auth()->user()->img }}" 
                                     class="img-circle elevation-2" 
                                     alt="User Image"
                                     onerror="this.src='https://cdn-icons-png.flaticon.com/512/847/847969.png'">
                                <p class="text-white mt-2">
                                    {{ auth()->user()->name }}
                                    <small>{{ auth()->user()->email }}</small>
                                </p>
                            </li>

                            <!-- Botones -->
                            <li class="user-footer">
                                <div class="d-flex justify-content-between px-3">
                                    <a href="{{ secure_url(route('profile.show')) }}" 
                                       class="btn btn-outline-light btn-sm">
                                        <i class="fa fa-user mr-1"></i> Perfil
                                    </a>
                                    <a href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                       class="btn btn-danger btn-sm">
                                        <i class="fa fa-sign-out-alt mr-1"></i> Salir
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
</header>
