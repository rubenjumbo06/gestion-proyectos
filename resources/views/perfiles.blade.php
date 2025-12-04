@extends('layouts.app')

@section('title', 'Perfil del Usuario')

@section('content')
<section class="content-header">
    <h1>Perfil del Usuario <small>Detalles de tu cuenta</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ URL::withTabToken(route('dashboard')) }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Perfil</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-profile">
                    @if(auth()->user()->img)
                        <img src="{{ auth()->user()->img }}" class="profile-user-img img-responsive img-circle" alt="User Image">
                    @else
                        <div class="profile-user-img img-responsive img-circle" style="background-color: #f0f0f0;">
                            <i class="fa fa-user" style="color: #4285f4; font-size: 60px; text-align: center; display: block; margin: 20px auto;"></i>
                        </div>
                    @endif
                    <h3 class="profile-username text-center">{{ auth()->user()->name }}</h3>
                    <p class="text-muted text-center">Usuario</p>
                    <a href="#" class="btn btn-primary btn-block" data-toggle="modal" data-target="#editProfileModal"><b>Actualizar Perfil</b></a>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Información Personal</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr><td><strong>Nombre completo</strong></td><td>{{ auth()->user()->name }}</td></tr>
                        <tr><td><strong>Correo electrónico</strong></td><td>{{ auth()->user()->email }}</td></tr>
                        <tr><td><strong>Número Telefónico</strong></td><td>{{ auth()->user()->telefono ?? 'No especificado' }}</td></tr>
                        <tr><td><strong>Fecha de Nacimiento</strong></td><td>{{ auth()->user()->fecha_nacimiento ?? 'No especificado' }}</td></tr>
                        <tr><td><strong>Fecha de Registro</strong></td><td>{{ auth()->user()->created_at->format('d/m/Y') }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Actividad Reciente</h3>
                </div>
                <div class="box-body" id="activity-container" style="max-height: 200px; overflow-y: auto;">
                    <ul class="activity-list">
                        @forelse(auth()->user()->activities()->latest()->take(1)->get() as $activity)
                            <li>{{ $activity->description }} - {{ $activity->created_at->format('d/m/Y H:i') }}</li>
                        @empty
                            <li>No hay actividades recientes.</li>
                        @endforelse
                    </ul>
                    @if(auth()->user()->activities()->count() > 4)
                        <button id="load-more" class="btn btn-default btn-block mt-2">Ver más</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Editar Perfil -->
<div class="modal fade" id="editProfileModal">
    <div class="modal-dialog">
        <div class="modal-content custom-modal">
            <div class="modal-header bg-primary rounded-t-lg">
                <h4 class="modal-title text-white">Actualizar Perfil</h4>
            </div>
            <div class="modal-body">
                <form id="editProfileForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
                    @method('PUT')
                    <div class="form-group mb-4">
                        <label for="name" class="font-semibold">Nombre completo</label>
                        <input type="text" class="form-control rounded-lg" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required placeholder="Ingresa tu nombre completo">
                    </div>
                    <div class="form-group mb-4">
                        <label for="telefono" class="font-semibold">Número Telefónico</label>
                        <input type="text" class="form-control rounded-lg" id="telefono" name="telefono" value="{{ old('telefono', auth()->user()->telefono) }}" placeholder="Ingresa tu número telefónico">
                    </div>
                    <div class="flex flex-wrap -mx-2 mb-4">
                        <div class="w-full md:w-1/2 px-2">
                            <label for="fecha_nacimiento" class="font-semibold">Fecha de Nacimiento</label>
                            <input type="date" class="form-control rounded-lg w-full" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', auth()->user()->fecha_nacimiento) }}" placeholder="Selecciona tu fecha de nacimiento">
                        </div>
                        <div class="w-full md:w-1/2 px-2">
                            <label for="img" class="font-semibold">Foto de Perfil</label>
                            <div class="relative custom-file-input">
                                <input type="file" class="form-control rounded-lg w-full hidden-input" id="img" name="img" accept=".jpg,.jpeg,.png">
                                <div class="file-input-display rounded-lg w-full text-center flex items-center justify-between" data-placeholder="Elige tu foto de perfil">
                                    <span id="fileNameDisplay">Elige tu foto de perfil</span>
                                    <button type="button" id="clearFile" class="text-red-500 hover:text-red-700 focus:outline-none hidden" style="display: none;"></button>
                                </div>
                                <span class="absolute right-6 top-1/2 transform -translate-y-1/2 text-gray-500">
                                    <i class="fa fa-image"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Campos de contraseña condicionales -->
                    @if(auth()->user()->password)
                        <div class="form-group mb-4 relative">
                            <label for="current_password" class="font-semibold">Contraseña Actual (dejar en blanco si no cambia)</label>
                            <input type="password" class="form-control rounded-lg" id="current_password" name="current_password" placeholder="Ingresa tu contraseña actual">
                            <button type="button" class="show-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none" data-target="#current_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-group mb-4 relative">
                            <label for="new_password" class="font-semibold">Nueva Contraseña</label>
                            <input type="password" class="form-control rounded-lg" id="new_password" name="new_password" placeholder="Ingresa una nueva contraseña">
                            <button type="button" class="show-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none" data-target="#new_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-group mb-4 relative">
                            <label for="new_password_confirmation" class="font-semibold">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control rounded-lg" id="new_password_confirmation" name="new_password_confirmation" placeholder="Confirma tu nueva contraseña">
                            <button type="button" class="show-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none" data-target="#new_password_confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    @else
                        <div class="form-group mb-4 relative">
                            <label for="new_password" class="font-semibold">Nueva Contraseña</label>
                            <input type="password" class="form-control rounded-lg" id="new_password" name="new_password" placeholder="Ingresa una nueva contraseña">
                            <button type="button" class="show-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none" data-target="#new_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-group mb-4 relative">
                            <label for="new_password_confirmation" class="font-semibold">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control rounded-lg" id="new_password_confirmation" name="new_password_confirmation" placeholder="Confirma tu nueva contraseña">
                            <button type="button" class="show-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none" data-target="#new_password_confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    @endif
                    <div class="modal-footer border-t-0">
                        <button type="button" class="btn btn-default rounded-lg" data-dismiss="modal">Cerrar</button>
                        <button type="button" id="submitProfile" class="btn btn-primary rounded-lg">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

$('#editProfileForm').on('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);

    // Validación de coincidencia de contraseñas en el cliente
    const newPassword = $('#new_password').val();
    const confirmPassword = $('#new_password_confirmation').val();
    const currentPassword = $('#current_password').val();

    if (newPassword && confirmPassword && newPassword !== confirmPassword) {
        alert('La nueva contraseña y la confirmación no coinciden.');
        return;
    }

    // Eliminar campos de contraseña si están vacíos, a menos que se desee cambiar
    if (!newPassword && !currentPassword) {
        formData.delete('current_password');
        formData.delete('new_password');
        formData.delete('new_password_confirmation');
    }

    // Depuración: Verificar qué se envía
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]));
    }

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert(response.message);
                // Actualiza la imagen si está en la respuesta
                if (response.img) {
                    $('.profile-user-img img').attr('src', response.img).show();
                    $('.profile-user-img .fa-user').hide();
                }
                location.reload(); // Recarga la página
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            console.log('Error:', xhr.responseText);
            let errorMessage = xhr.responseJSON?.message || 'Actualización fallida';
            if (xhr.responseJSON?.errors) {
                errorMessage += '\nDetalles: ' + JSON.stringify(xhr.responseJSON.errors);
            }
            alert('Error: ' + errorMessage);
        }
    });
});

// Manejo del input de archivo personalizado
document.querySelector('#img').addEventListener('click', function(e) {
    e.stopPropagation();
});

document.querySelector('#img').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Elige tu foto de perfil';
    const display = document.querySelector('#fileNameDisplay');
    const clearButton = document.querySelector('#clearFile');
    display.textContent = fileName;
    clearButton.classList.remove('hidden');
});

// Botón para limpiar la foto seleccionada
document.querySelector('#clearFile').addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const input = document.querySelector('#img');
    const display = document.querySelector('#fileNameDisplay');
    const clearButton = document.querySelector('#clearFile');
    input.value = '';
    display.textContent = 'Elige tu foto de perfil';
    clearButton.classList.add('hidden');
});

// Toggle para ver/ocultar contraseña
document.querySelectorAll('.show-password').forEach(button => {
    button.addEventListener('click', function() {
        const target = document.querySelector(this.getAttribute('data-target'));
        const icon = this.querySelector('i');
        if (target.type === 'password') {
            target.type = 'text';
            icon.classList.remove('fas', 'fa-eye');
            icon.classList.add('fas', 'fa-eye-slash');
        } else {
            target.type = 'password';
            icon.classList.remove('fas', 'fa-eye-slash');
            icon.classList.add('fas', 'fa-eye');
        }
    });
});

// Desactivar el ojo predeterminado del navegador
document.addEventListener('DOMContentLoaded', function() {
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.style.webkitAppearance = 'none';
        input.style.mozAppearance = 'none';
        input.style.appearance = 'none';

        input.addEventListener('mousedown', function(e) {
            if (e.offsetX > input.clientWidth - 30) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
        input.addEventListener('mouseup', function(e) {
            if (e.offsetX > input.clientWidth - 30) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
});

// Limpiar campos al cerrar el modal
$('#editProfileModal').on('hidden.bs.modal', function() {
    const passwordFields = ['#current_password', '#new_password', '#new_password_confirmation'];
    passwordFields.forEach(selector => {
        $(selector).val('');
    });
    const imgInput = document.querySelector('#img');
    const display = document.querySelector('#fileNameDisplay');
    const clearButton = document.querySelector('#clearFile');
    imgInput.value = '';
    display.textContent = 'Elige tu foto de perfil';
    clearButton.classList.add('hidden');
});

// Asegurar que el botón de submit active el formulario
document.getElementById('submitProfile').addEventListener('click', function() {
    $('#editProfileForm').submit();
});

// Función para cargar más actividades
document.getElementById('load-more')?.addEventListener('click', function() {
    const container = document.getElementById('activity-container');
    const ul = container.querySelector('.activity-list');
    const currentCount = ul.children.length;

    $.get('/user-activities?offset=' + currentCount, function(data) {
        if (data.activities && data.activities.length > 0) {
            data.activities.forEach(activity => {
                const li = document.createElement('li');
                li.textContent = `${activity.description} - ${activity.created_at}`;
                ul.appendChild(li);
            });
            if (data.activities.length < 4) {
                document.getElementById('load-more').remove();
            }
        } else {
            alert('No hay más actividades para cargar.');
            document.getElementById('load-more').remove();
        }
    }).fail(function(xhr) {
        console.log('Error detallado:', xhr.responseText);
        alert('Error al cargar más actividades. Revisa la consola para detalles.');
    });
});
</script>
@endpush