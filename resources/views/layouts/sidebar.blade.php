<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MENÚ</li>

            <li class="{{ Route::is('dashboard') ? 'active' : '' }}">
                <a href="{{ URL::withTabToken(route('dashboard')) }}">
                    <i class="fa fa-home"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="{{ Route::is('proyectos.*') ? 'active' : '' }}">
                <a href="{{ URL::withTabToken(route('proyectos.index')) }}">
                    <i class="fa fa-folder"></i> <span>Proyectos</span>
                </a>
            </li>

            <li class="{{ Route::is('financiadores.*') ? 'active' : '' }}">
                <a href="{{ URL::withTabToken(route('financiadores.index')) }}">
                    <i class="fa fa-truck"></i> <span>Financiadores</span>
                </a>
            </li>

            <li class="{{ Route::is('departamentos.*') ? 'active' : '' }}">
                <a href="{{ URL::withTabToken(route('departamentos.index')) }}">
                    <i class="fa fa-building"></i> <span>Departamentos</span>
                </a>
            </li>

            <li class="{{ Route::is('trabajadores.*') ? 'active' : '' }}">
                <a href="{{ URL::withTabToken(route('trabajadores.index')) }}">
                    <i class="fa fa-users"></i> <span>Trabajadores</span>
                </a>
            </li>

            @if(auth()->user()->puede_descargar ?? false)
            <li class="{{ Route::is('exportacion.general') ? 'active' : '' }}">
                <a href="{{ URL::withTabToken(route('exportacion.general')) }}">
                    <i class="fa fa-file-pdf"></i> <span>Exportación General</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->is_superadmin ?? false)
            <li class="{{ Route::is('allowed_users.*') ? 'active' : '' }}">
                <a href="{{ URL::withTabToken(route('allowed_users.index')) }}">
                    <i class="fa fa-user-plus"></i> <span>Usuarios Autorizados</span>
                </a>
            </li>
            @endif
        </ul>
    </section>
</aside>