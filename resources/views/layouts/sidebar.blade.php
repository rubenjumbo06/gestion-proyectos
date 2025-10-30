<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MENÚ</li>

            <!-- Dashboard -->
            <li class="{{ Route::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" title="Dashboard">
                    <i class="fa fa-home"></i> <span>Dashboard</span>
                </a>
            </li>

            <!-- Proyectos -->
            <li class="{{ Route::is('proyectos.*') ? 'active' : '' }}">
                <a href="{{ route('proyectos.index') }}" title="Proyectos">
                    <i class="fa fa-folder"></i> <span>Proyectos</span>
                </a>
            </li>

             <!-- Reporte de Actividades
            <li class="{{ Route::is('reporte_actividades') ? 'active' : '' }}">
                <a href="{{ route('reporte_actividades') }}" title="Reporte de Actividades">
                    <i class="fa fa-list"></i> <span>Reporte de Actividades</span>
                </a>
            </li> -->

            <!-- Proveedores -->
            <li class="{{ Route::is('proveedores.*') ? 'active' : '' }}">
                <a href="{{ url('/admin/proveedores') }}" title="Proveedores">
                    <i class="fa fa-truck"></i> <span>Proveedores</span>
                </a>
            </li>

            <!-- Departamentos -->
            <li class="{{ Route::is('departamentos.*') ? 'active' : '' }}">
                <a href="{{ url('/admin/departamentos') }}" title="Departamentos">
                    <i class="fa fa-building"></i> <span>Departamentos</span>
                </a>
            </li>

            <!-- Trabajadores -->
            <li class="{{ Route::is('trabajadores.*') ? 'active' : '' }}">
                <a href="{{ url('/admin/trabajadores') }}" title="Trabajadores">
                    <i class="fa fa-users"></i> <span>Trabajadores</span>
                </a>
            </li>

            <!-- Exportación General -->
            <li class="{{ Route::is('exportacion.general') ? 'active' : '' }}">
                <a href="{{ route('exportacion.general') }}" title="Exportación General">
                    <i class="fa fa-file-pdf"></i> <span>Exportación General</span>
                </a>
            </li>

            <!-- Usuarios Autorizados (solo SuperAdmin) -->
            @if(auth()->check())
                @php
                    $allowedUser = \App\Models\AllowedUser::where('email', auth()->user()->email)->first();
                @endphp
                @if($allowedUser && $allowedUser->is_superadmin)
                    <li class="{{ Route::is('allowed_users.*') ? 'active' : '' }}">
                        <a href="{{ route('allowed_users.index') }}" title="Usuarios Autorizados"> 
                            <i class="fa fa-user-plus"></i> <span>Usuarios Autorizados</span>
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </section>
</aside>