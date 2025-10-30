@extends('layouts.app')

@section('title', 'Actividades del Proyecto')

@section('content')
<section class="content-header">
    <h1>Actividades del Proyecto <small>{{ $proyecto->nombre }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('proyectos.index') }}">Proyectos</a></li>
        <li class="active">Actividades</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border d-flex justify-content-between align-items-center">
            <h3 class="box-title">Listado de Actividades</h3>
            <div class="flex justify-end space-x-1" style="margin-top: -10px;">
                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarActividad">
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
                        <div class="col-md-4">
                            <div class="box box-solid box-default position-relative">
                                <div class="box-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fa fa-check text-success mr-2"></i>
                                        <p class="text-black mb-0">{{ $actividad->nombre }}</p>
                                    </div>

                                    <p class="text-muted mb-0 mt-1">
                                        <small><i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($actividad->fecha_actividad)->format('d/m/Y') }}</small>
                                    </p>

                                    @if($actividad->imagen_url)
                                        <div class="mb-2 text-center">
                                            <img src="{{ $actividad->imagen_url }}" 
                                                 alt="Imagen de actividad"
                                                 class="img-thumbnail"
                                                 style="max-height:180px; width:auto; object-fit:cover; cursor:pointer;"
                                                 onclick="window.open('{{ $actividad->imagen_url }}','_blank')">
                                        </div>
                                    @endif

                                    <p class="text-justify">{{ $actividad->descripcion }}</p>

                                    <div class="d-flex justify-content-end gap-1 mt-2">
                                        @can('update', $actividad)
                                            <a href="{{ route('proyectos.actividades.edit', [$proyecto->id_proyecto, $actividad->id_actividad]) }}" 
                                               class="btn btn-sm btn-outline-primary">Editar</a>
                                        @endcan
                                        @can('delete', $actividad)
                                            <form action="{{ route('proyectos.actividades.destroy', [$proyecto->id_proyecto, $actividad->id_actividad]) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('¿Eliminar esta actividad?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
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

    .modal-dialog {
        max-width: 480px !important;
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

    .dropdown.show .dropdown-menu {
        display: block;
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
</script>
@endpush