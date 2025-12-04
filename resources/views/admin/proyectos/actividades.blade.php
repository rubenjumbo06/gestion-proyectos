@extends('layouts.app')

@section('title', 'Actividades del Proyecto')

@section('content')
<section class="content-header">
    <h1>Actividades del Proyecto <small>{{ $proyecto->nombre }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ URL::withTabToken(route('dashboard')) }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ URL::withTabToken(route('proyectos.index')) }}">Proyectos</a></li>
        <li class="active">Actividades</li>
    </ol>
</section>

@php
    $isFinalized = !empty($proyecto->fechapr->fecha_fin_true);
@endphp

@if($isFinalized)
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong><i class="fa fa-exclamation-triangle"></i> Proyecto Finalizado</strong>
        <p>Este proyecto ha sido finalizado el {{ \Carbon\Carbon::parse($proyecto->fechapr->fecha_fin_true)->format('d/m/Y') }}. No se pueden agregar nuevas actividades.</p>
    </div>
@endif

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border d-flex justify-content-between align-items-center">
            <h3 class="box-title">
                <a href="{{ URL::withTabToken(route('proyectos.show', $proyecto->id_proyecto)) }}" class="btn btn-default btn-sm mr-2" style="margin-right: 10px;" title="Volver al Proyecto">
                    <i class="fa fa-arrow-left"></i>
                </a>
                Listado de Actividades
            </h3>
            <div class="flex justify-end space-x-1" style="margin-top: -10px;">
                <a href="#" 
                   class="btn btn-primary {{ $isFinalized ? 'disabled' : '' }}" 
                   data-toggle="modal" 
                   data-target="{{ $isFinalized ? '' : '#modalAgregarActividad' }}"
                   {{ $isFinalized ? 'disabled' : '' }}
                   onclick="{{ $isFinalized ? 'return false;' : '' }}">
                    <i class="fa fa-plus"></i> Agregar Actividad
                </a>
            </div>
        </div>

        <div class="box-body">
            {{-- Mensajes de sesión --}}
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

            @if($actividades->isEmpty())
                <p class="text-center text-muted">No hay actividades registradas para este proyecto.</p>
            @else
                <div class="row">
                    @foreach($actividades as $actividad)
                      <div class="col-md-4 mb-4">
                            <!-- TARJETA CON group y position-relative -->
                            <div class="box box-solid box-default position-relative group bg-white rounded-lg shadow hover:shadow-2xl transition-all duration-300 overflow-hidden" style="min-height: 300px;">
                                
                                <!-- Contenido -->
                                <div class="p-4">
                                    <div class="flex items-center mb-3">
                                        <i class="fa fa-check text-success mr-3 text-lg"></i>
                                        <h5 class="font-bold text-lg text-gray-800 mb-0">{{ $actividad->nombre }}</h5>
                                    </div>

                                    <p class="text-muted text-sm mb-3">
                                        <i class="fa fa-calendar mr-2"></i>
                                        {{ \Carbon\Carbon::parse($actividad->fecha_actividad)->format('d/m/Y') }}
                                    </p>

                                    @if($actividad->imagen_url)
                                        <div class="text-center mb-4">
                                            <img src="{{ $actividad->imagen_url }}" 
                                                 class="img-thumbnail rounded mx-auto d-block cursor-pointer"
                                                 style="max-height: 180px; object-fit: cover;"
                                                 onclick="window.open('{{ $actividad->imagen_url }}', '_blank')">
                                        </div>
                                    @endif

                                    <p class="text-gray-700 text-justify text-sm leading-relaxed">
                                        {{ $actividad->descripcion }}
                                    </p>
                                </div>
                                <!-- BOTONES EDITAR / ELIMINAR MOSTRADOS POR HOVER -->
<div class="absolute top-2 right-3 z-30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex space-x-2">

    <button class="btn btn-warning btn-sm"
            onclick='abrirModalEditar({!! $actividad->toJson() !!});'>
        <i class="fa fa-edit"></i>
    </button>

    <form action="{{ route('proyectos.actividades.destroy', [$proyecto->id_proyecto, $actividad->id_actividad]) }}"
          method="POST">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger btn-sm"
                onclick="return confirm('¿Eliminar actividad?')">
            <i class="fa fa-trash"></i>
        </button>
    </form>

</div>

                                <!-- FIN 3 PUNTOS -->
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Modal para agregar actividad -->
<div class="modal fade" id="modalAgregarActividad" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="formAgregarActividad" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content custom-modal">
                <div class="modal-header bg-primary text-white rounded-t-lg">
                    <h4 class="modal-title">Agregar Actividad</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="actividad_nombre" class="font-semibold">Título de la Actividad</label>
                        <input type="text" class="form-control rounded-lg" id="actividad_nombre" name="nombre" placeholder="Nombre de la actividad" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="actividad_descripcion" class="font-semibold">Descripción</label>
                        <textarea class="form-control rounded-lg" id="actividad_descripcion" name="descripcion" rows="3" placeholder="Describe la actividad" required></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="fecha_actividad" class="font-semibold">Fecha de Actividad</label>
                        <input type="date" class="form-control rounded-lg" id="fecha_actividad" name="fecha_actividad" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="actividad_img" class="font-semibold">Imagen de la Actividad</label>
                        <div class="relative custom-file-input">
                            <input type="file" class="hidden-input" id="actividad_img" name="imagen" accept=".jpg,.jpeg,.png">
                            <div class="file-input-display rounded-lg w-full text-center flex items-center justify-between" data-placeholder="Elige imagen">
                                <span id="fileNameActividad">Elige imagen</span>
                                <button type="button" id="clearActividadFile" class="text-red-500 hover:text-red-700 focus:outline-none hidden">✕</button>
                            </div>
                            <span class="absolute right-6 top-1/2 transform -translate-y-1/2 text-gray-500">
                                <i class="fa fa-image"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t-0">
                    <button type="button" class="btn btn-default rounded-lg" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary rounded-lg">Guardar Actividad</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Modal EDITAR Actividad (estilo igual al modal azul de proyecto) -->
<div class="modal fade" id="modalEditarActividad" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content project-modal">

            <form id="formEditarActividad" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- HEADER AZUL -->
                <div class="project-modal-header">
                    <h4 class="project-modal-title w-100 text-center">
                        Editar Actividad
                    </h4>
                </div>

                <!-- CUERPO -->
                <div class="project-modal-body">

                    <!-- Título -->
                    <div class="project-form-group">
                        <label class="project-label">Título de la Actividad</label>
                        <input type="text" class="project-input" id="edit_nombre" name="nombre" required>
                    </div>

                    <!-- Descripción -->
                    <div class="project-form-group">
                        <label class="project-label">Descripción</label>
                        <textarea class="project-input" id="edit_descripcion" name="descripcion"
                                  rows="4" required></textarea>
                    </div>

                    <!-- Fecha -->
                    <div class="project-form-group">
                        <label class="project-label">Fecha de Actividad</label>
                        <input type="date" class="project-input" id="edit_fecha_actividad"
                               name="fecha_actividad" required>
                    </div>
                    <!-- Nueva imagen -->
                    <div class="project-form-group">
                        <label class="project-label">Cambiar imagen (opcional)</label>

                        <div class="relative custom-file-input">
                            <input type="file" id="edit_imagen" name="imagen"
                                   accept=".jpg,.jpeg,.png" class="hidden-input">

                            <div class="file-input-display project-input flex items-center justify-between"
                                 data-placeholder="Elige nueva imagen">
                                <span id="editFileName">Elige nueva imagen</span>
                                <button type="button" id="clearEditFile"
                                        class="text-red-500 hover:text-red-700 hidden">✕</button>
                            </div>

                            <span class="absolute right-6 top-1/2 transform -translate-y-1/2 text-gray-500">
                                <i class="fa fa-image"></i>
                            </span>
                        </div>
                    </div>

                </div>

                <!-- FOOTER PEGADO ABAJO -->
                <div class="project-modal-footer" style="position: sticky; bottom: 0; background: white; padding: 10px; border-top: 1px solid #ddd;">
                    <button type="submit" class="project-btn project-btn-primary">
                        Actualizar Actividad
                    </button>
                    <button type="button" class="project-btn project-btn-default" data-dismiss="modal">
                        Cancelar
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
    /* Styling for form controls */
    .form-control {
        border-radius: 2.5rem !important;
        border: 2px solid #ced4da !important;
        padding: 0.9rem 1.8rem !important;
        transition: border-color 0.3s ease, box-shadow 0.3s ease !important;
        background-color: #f8f9fa !important;
        color: #333 !important;
        font-size: 1.05rem !important;
        line-height: 1.6 !important;
        width: 100% !important;
        outline: none !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    }

    .form-control:focus {
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.5) !important;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 1rem;
    }

    .form-group label {
        font-weight: 600;
        color: #444;
        text-align: center;
        margin-bottom: 0.5rem;
    }

    .modal-content.custom-modal {
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .error-message {
        font-size: 0.9rem !important;
        margin-top: 0.3rem !important;
        color: #dc3545;
        text-align: center;
    }

    /* Dropdown styling */
    .dropdown-toggle {
        background: rgba(255, 255, 255, 0.8) !important;
        border: 1px solid #ccc !important;
        font-size: 1.3rem;
        line-height: 1;
        padding: 2px 8px;
        border-radius: 4px;
        z-index: 10;
    }

    .dropdown-menu {
        min-width: 120px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        z-index: 15;
    }

    .dropdown-menu-right {
        right: 0;
        left: auto;
    }

    .dropdown:hover .dropdown-menu {
        display: block;
    }
    .group .btn { opacity: 0; }
.group:hover .btn { opacity: 1; }


    .dropdown.show .dropdown-menu {
        display: block;
    }
       .project-modal-body {
        max-height: 70px !important;   /* Altura máxima del contenido */
        overflow-y: auto !important;   /* Activa scroll vertical */
        padding-right: 12px !important;
        margin-right: -6px !important; /* Evita que se corte el scroll */
        scrollbar-width: thin;         /* Scroll delgado (Firefox) */
    }

    /* Scroll bonito para Chrome y Edge */
    .project-modal-body::-webkit-scrollbar {
        width: 6px;
    }
    .project-modal-body::-webkit-scrollbar-thumb {
        background: #b5b5b5;
        border-radius: 4px;
    }
    /* Forzar que el modal tenga una altura limitada */
#modalEditarActividad .modal-dialog {
    width: 900px !important;
    max-width: 900px !important;
}


#modalEditarActividad .project-modal-body {
    max-height: 70vh !important;
    overflow-y: auto !important;
    padding-right: 12px !important;
    margin-right: -6px !important;
}


</style>
@endpush

@push('scripts')
<script>
    // Inicializar dropdowns de Bootstrap
    $(document).ready(function() {
        $('.dropdown-toggle').dropdown();
        console.log('Inicializando dropdowns...');
        $('.dropdown-toggle').each(function() {
            console.log('Dropdown encontrado:', this.id);
        });
    });

    // Validación de inputs para el formulario
    document.getElementById('formAgregarActividad').addEventListener('submit', function(e) {
        const nombre = document.getElementById('actividad_nombre');
        const descripcion = document.getElementById('actividad_descripcion');
        let nombreError = document.getElementById('nombre_error');
        let descripcionError = document.getElementById('descripcion_error');
        let valid = true;

        if (!nombreError) {
            nombreError = document.createElement('div');
            nombreError.id = 'nombre_error';
            nombreError.className = 'error-message';
            nombre.parentElement.appendChild(nombreError);
        }
        if (!descripcionError) {
            descripcionError = document.createElement('div');
            descripcionError.id = 'descripcion_error';
            descripcionError.className = 'error-message';
            descripcion.parentElement.appendChild(descripcionError);
        }

        nombreError.textContent = '';
        descripcionError.textContent = '';

        if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]+$/.test(nombre.value)) {
            nombreError.textContent = 'Solo se permiten letras, números y algunos símbolos básicos.';
            valid = false;
        }
        if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]+$/.test(descripcion.value)) {
            descripcionError.textContent = 'Solo se permiten letras, números y algunos símbolos básicos.';
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
            console.log('Validación fallida');
        }
    });

    ['actividad_nombre', 'actividad_descripcion'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]/g, '');
            });
        }
    });
  // Función abrir modal editar (sin cambios)
function abrirModalEditar(actividad) {
    $('#modalEditarActividad').modal('show');
    $('#edit_actividad_id').val(actividad.id_actividad);
    $('#edit_nombre').val(actividad.nombre);
    $('#edit_descripcion').val(actividad.descripcion);
    $('#edit_fecha_actividad').val(actividad.fecha_actividad.substring(0,10));

    const ruta = "{{ route('proyectos.actividades.update', ['proyecto' => $proyecto->id_proyecto, 'actividad' => 'ID']) }}".replace('ID', actividad.id_actividad);
    $('#formEditarActividad').attr('action', ruta);

    $('#edit_imagen').val('');
    $('#editFileName').text('Elige nueva imagen');
}
    // Limpiar nombre de archivo al seleccionar uno nuevo (opcional, mejora UX)
    document.getElementById('edit_imagen').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'Elige nueva imagen';
        document.getElementById('editFileName').textContent = fileName;
    });

  function toggleMenu(e, menuId) {
    e.stopPropagation();
    const menu = document.getElementById(menuId);

    // Cerrar todos los demás menús
    document.querySelectorAll('[id^="menu-actividad-"]').forEach(m => {
        if (m.id !== menuId) m.classList.add('hidden');
    });

    // Toggle del actual
    menu.classList.toggle('hidden');
}

// Cerrar menús al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('button[onclick^="toggleMenu"]') && 
        !e.target.closest('[id^="menu-actividad-"]')) {
        document.querySelectorAll('[id^="menu-actividad-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});
</script>
@endpush