@extends('layouts.app')

@section('title', 'Gestionar Usuarios Autorizados')

@section('content')
<section class="content-header">
    <h1>Gestionar Usuarios Autorizados <small>Controla el acceso al sistema</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ URL::withTabToken(route('dashboard')) }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Usuarios Autorizados</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Lista de Usuarios Autorizados</h3>
            <a href="#" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addUserModal">Agregar Usuario</a>
        </div>
        <div class="box-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tabla -->
            <table id="allowedUsersTable" class="table table-bordered table-hover text-center">
                <thead>
                    <tr>
                        <th style="width:40%">Email</th>
                        <th>Superadmin</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allowedUsers as $user)
                        <tr>
                            <td>
                                <a href="#"
                                   class="open-permissions-modal"
                                   data-id="{{ $user->id }}"
                                   data-email="{{ $user->email }}"
                                   data-superadmin="{{ $user->is_superadmin }}"
                                   data-activo="{{ $user->is_active }}"
                                   data-permisos='@json($user->permisos)'>
                                   {{ $user->email }}
                                </a>
                            </td>
                            <td>
                                @if($user->is_superadmin)
                                    <span class="label label-success">Sí</span>
                                @else
                                    <span class="label label-default">No</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="label label-success">Sí</span>
                                @else
                                    <span class="label label-danger">No</span>
                                @endif
                            </td>
                            <td>
                                <button type="button"
                                        class="btn btn-danger btn-sm delete-user-btn"
                                        data-user-id="{{ $user->id }}"
                                        data-user-email="{{ $user->email }}">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal Agregar Usuario -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form method="POST" action="{{ route('allowed_users.store') }}">
        @csrf
        <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
        <div class="modal-content custom-modal rounded-2xl">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title">Agregar Usuario</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="permission-switches text-left">
                    <div class="permission-item">
                        <span>Crear</span>
                        <label class="switch small-switch">
                            <input type="checkbox" name="permissions[puede_agregar]">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="permission-item">
                        <span>Editar</span>
                        <label class="switch small-switch">
                            <input type="checkbox" name="permissions[puede_editar]">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="permission-item">
                        <span>Descargar</span>
                        <label class="switch small-switch">
                            <input type="checkbox" name="permissions[puede_descargar]">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </form>
  </div>
</div>

<!-- Modal Editar Permisos -->
<div class="modal fade" id="permissionsModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="permissionsForm" method="POST">
        @csrf
        <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
        @method('PUT')
        <div class="modal-content custom-modal rounded-2xl">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title">Editar Permisos</h4>
            </div>
            <div class="modal-body text-left">
                <p id="modalUserEmail"></p>
                <div class="permission-switches">
                    <div class="permission-item">
                        <span>Crear</span>
                        <label class="switch small-switch">
                            <input type="checkbox" name="permissions[puede_agregar]" id="permAgregar">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="permission-item">
                        <span>Editar</span>
                        <label class="switch small-switch">
                            <input type="checkbox" name="permissions[puede_editar]" id="permEditar">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="permission-item">
                        <span>Descargar</span>
                        <label class="switch small-switch">
                            <input type="checkbox" name="permissions[puede_descargar]" id="permDescargar">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="permission-item">
                        <span>Superadmin</span>
                        <label class="switch small-switch">
                            <input type="checkbox" name="is_superadmin" id="permSuperadmin">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="permission-item">
                        <span>Activo</span>
                        <label class="switch small-switch">
                            <input type="checkbox" name="is_active" id="permActivo">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </div>
    </form>
  </div>
</div>

<!-- Modal Confirmar Eliminar -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content custom-modal rounded-2xl">
            <div class="modal-header bg-red text-white">
                <h4 class="modal-title">Confirmar Eliminación</h4>
            </div>
            <div class="modal-body">
                <p id="confirmDeleteMessage"></p>
            </div>
            <div class="modal-footer">
                <form id="deleteUserForm" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .modal-content.custom-modal { padding: 15px; border-radius: 15px; }
    .permission-switches { display: flex; flex-wrap: wrap; gap: 25px; }
    .permission-item { display: flex; flex-direction: column; align-items: flex-start; font-weight: 500; }
    .switch { position: relative; display: inline-block; width: 50px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #4a90e2; }
    input:checked + .slider:before { transform: translateX(26px); }
   #confirmDeleteModal .modal-header {
    background-color: #dc3545 !important; /* rojo intenso */
    color: #fff !important;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

#addUserModal .modal-header,
#permissionsModal .modal-header {
    background-color: #007bff !important; /* azul */
    color: #fff !important;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

#addUserModal .modal-dialog,
#permissionsModal .modal-dialog {
    max-width: 400px; /* más delgado */
    margin: 1.75rem auto;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = $('#permissionsModal');
    const form = document.getElementById('permissionsForm');
    const emailSpan = document.getElementById('modalUserEmail');

    document.querySelectorAll('.open-permissions-modal').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const userId = this.dataset.id;
            const email = this.dataset.email;
            const superadmin = this.dataset.superadmin == 1;
            const activo = this.dataset.activo == 1;
            const permisos = this.dataset.permisos ? JSON.parse(this.dataset.permisos) : {};

            emailSpan.textContent = "Permisos de: " + email;
            form.action = `/allowed_users/${userId}/permissions`;

            // Reset
            document.querySelectorAll('#permissionsModal input[type="checkbox"]').forEach(cb => cb.checked = false);

            // Prefill permisos
            document.getElementById('permSuperadmin').checked = superadmin;
            document.getElementById('permActivo').checked = activo;

            if (permisos) {
                document.getElementById('permAgregar').checked = permisos.puede_agregar == 1;
                document.getElementById('permEditar').checked = permisos.puede_editar == 1;
                document.getElementById('permDescargar').checked = permisos.puede_descargar == 1;
            }

            modal.modal('show');
        });
    });

    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function () {
            const userId = this.dataset.userId;
            const email = this.dataset.userEmail;
            document.getElementById('confirmDeleteMessage').innerHTML =
                `¿Seguro que deseas eliminar al usuario <strong>${email}</strong>?`;
            const form = document.getElementById('deleteUserForm');
            form.action = `/allowed_users/${userId}`;
            $('#confirmDeleteModal').modal('show');
        });
    });
});
</script>
@endpush
