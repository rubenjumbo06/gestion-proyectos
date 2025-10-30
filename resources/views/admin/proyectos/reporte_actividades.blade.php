@extends('layouts.app')

@section('title', 'Reporte de Actividades')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Reporte de Actividades <small>Visualiza y gestiona todas las actividades</small></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Reporte de Actividades</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Lista de Actividades</h3>
            <div class="card-tools">
                <select id="proyectoFiltro" class="form-control project-input mr-2" style="width: 200px; display: inline-block;">
                    <option value="">Todos los proyectos</option>
                    @foreach($proyectos as $proyecto)
                        <option value="{{ $proyecto->id_proyecto }}" {{ request('proyecto_id') == $proyecto->id_proyecto ? 'selected' : '' }}>{{ $proyecto->nombre }}</option>
                    @endforeach
                </select>
                @can('create', \App\Models\ActividadProyecto::class)
                    <button class="btn btn-primary project-btn project-btn-primary" data-toggle="modal" data-target="#addActividadModal">Agregar Actividad</button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible project-alert project-alert-success">
                    <button type="button" class="close project-alert-close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible project-alert project-alert-danger">
                    <button type="button" class="close project-alert-close" data-dismiss="alert">&times;</button>
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible project-alert project-alert-danger">
                    <button type="button" class="close project-alert-close" data-dismiss="alert">&times;</button>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="modal fade" id="addActividadModal" tabindex="-1" role="dialog" aria-labelledby="addActividadModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content custom-modal project-modal">
                        <form action="{{ route('reporte_actividades.store') }}" method="POST" id="addActividadForm">
                            @csrf
                            <div class="modal-header bg-primary project-modal-header">
                                <h4 class="modal-title text-white project-modal-title" id="addActividadModalLabel">Añadir Nueva Actividad</h4>
                                <button type="button" class="close project-modal-close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body project-modal-body">
                                <div class="form-group project-form-group">
                                    <label for="proyecto_id" class="font-semibold project-label">Proyecto</label>
                                    <select name="proyecto_id" id="proyecto_id" class="form-control project-input" required>
                                        <option value="">Selecciona un proyecto</option>
                                        @foreach($proyectos as $proyecto)
                                            <option value="{{ $proyecto->id_proyecto }}">{{ $proyecto->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger error-message project-error" id="proyecto_id_error"></span>
                                </div>
                                <div class="form-group project-form-group">
                                    <label for="descripcion" class="font-semibold project-label">Descripción</label>
                                    <textarea name="descripcion" id="descripcion" class="form-control project-input letter-dot-only" required placeholder="Ingresa la descripción de la actividad" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*" title="Solo letras, espacios y puntos">{{ old('descripcion') }}</textarea>
                                    <span class="text-danger error-message project-error" id="descripcion_error"></span>
                                </div>
                                <div class="form-group project-form-group">
                                    <label for="fecha_actividad" class="font-semibold project-label">Fecha de la Actividad</label>
                                    <input type="date" name="fecha_actividad" id="fecha_actividad" class="form-control project-input" required value="{{ old('fecha_actividad') }}">
                                    <span class="text-danger error-message project-error" id="fecha_actividad_error"></span>
                                </div>
                                <div class="form-group project-form-group">
                                    <label for="imagen_url" class="font-semibold project-label">URL de la Imagen (opcional)</label>
                                    <input type="url" name="imagen_url" id="imagen_url" class="form-control project-input" placeholder="Ingresa la URL de la imagen" value="{{ old('imagen_url') }}">
                                    <span class="text-danger error-message project-error" id="imagen_url_error"></span>
                                </div>
                            </div>
                            <div class="modal-footer project-modal-footer">
                                <button type="submit" class="btn btn-primary project-btn project-btn-primary">Añadir</button>
                                <button type="button" class="btn btn-secondary project-btn project-btn-default" data-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if($actividades->isEmpty())
                <p class="text-muted text-center">No hay actividades registradas.</p>
            @else
                <div class="row">
                    @foreach($actividades as $actividad)
                        <div class="col-md-4 col-sm-6 col-12 mb-4">
                            <div class="card card-default project-box h-100">
                                <div class="card-body">
                                    <h4 class="card-title project-title"><b>{{ $actividad->descripcion }}</b></h4>
                                    <p class="card-text">{{ $actividad->proyecto->nombre }}</p>
                                    <p class="card-text">{{ $actividad->fecha_actividad->format('d/m/Y') }}</p>
                                    @if($actividad->imagen_url)
                                        <img src="{{ $actividad->imagen_url }}" 
                                             alt="Imagen de actividad" 
                                             class="img-fluid img-thumbnail mb-2" 
                                             style="max-height: 180px; object-fit: cover; width: 100%;">
                                    @endif
                                    <p class="text-muted">
                                        <small><i class="fa fa-calendar"></i> {{ $actividad->created_at->format('d/m/Y H:i') }}</small>
                                    </p>
                                    <div class="action-buttons">
                                        @can('update', $actividad)
                                            <button class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#editActividadModal{{ $actividad->id }}"><i class="fa fa-edit"></i></button>
                                        @endcan
                                        @can('delete', $actividad)
                                            <form action="{{ route('reporte_actividades.destroy', $actividad->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta actividad?');" 
                                                  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="editActividadModal{{ $actividad->id }}" tabindex="-1" role="dialog" aria-labelledby="editActividadModalLabel{{ $actividad->id }}">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content custom-modal project-modal">
                                    <form action="{{ route('reporte_actividades.update', $actividad->id) }}" method="POST" id="editActividadForm{{ $actividad->id }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-primary project-modal-header">
                                            <h4 class="modal-title text-white project-modal-title" id="editActividadModalLabel{{ $actividad->id }}">Editar Actividad</h4>
                                            <button type="button" class="close project-modal-close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body project-modal-body">
                                            <div class="form-group project-form-group">
                                                <label for="proyecto_id_{{ $actividad->id }}" class="font-semibold project-label">Proyecto</label>
                                                <select name="proyecto_id" id="proyecto_id_{{ $actividad->id }}" class="form-control project-input" required>
                                                    <option value="">Selecciona un proyecto</option>
                                                    @foreach($proyectos as $proyecto)
                                                        <option value="{{ $proyecto->id_proyecto }}" {{ $actividad->proyecto_id == $proyecto->id_proyecto ? 'selected' : '' }}>{{ $proyecto->nombre }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger error-message project-error" id="proyecto_id_{{ $actividad->id }}_error"></span>
                                            </div>
                                            <div class="form-group project-form-group">
                                                <label for="descripcion_{{ $actividad->id }}" class="font-semibold project-label">Descripción</label>
                                                <textarea name="descripcion" id="descripcion_{{ $actividad->id }}" class="form-control project-input letter-dot-only" required placeholder="Ingresa la descripción de la actividad" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*" title="Solo letras, espacios y puntos">{{ old('descripcion', $actividad->descripcion) }}</textarea>
                                                <span class="text-danger error-message project-error" id="descripcion_{{ $actividad->id }}_error"></span>
                                            </div>
                                            <div class="form-group project-form-group">
                                                <label for="fecha_actividad_{{ $actividad->id }}" class="font-semibold project-label">Fecha de la Actividad</label>
                                                <input type="date" name="fecha_actividad" id="fecha_actividad_{{ $actividad->id }}" class="form-control project-input" required value="{{ old('fecha_actividad', $actividad->fecha_actividad->format('Y-m-d')) }}">
                                                <span class="text-danger error-message project-error" id="fecha_actividad_{{ $actividad->id }}_error"></span>
                                            </div>
                                            <div class="form-group project-form-group">
                                                <label for="imagen_url_{{ $actividad->id }}" class="font-semibold project-label">URL de la Imagen (opcional)</label>
                                                <input type="url" name="imagen_url" id="imagen_url_{{ $actividad->id }}" class="form-control project-input" placeholder="Ingresa la URL de la imagen" value="{{ old('imagen_url', $actividad->imagen_url) }}">
                                                <span class="text-danger error-message project-error" id="imagen_url_{{ $actividad->id }}_error"></span>
                                            </div>
                                        </div>
                                        <div class="modal-footer project-modal-footer">
                                            <button type="submit" class="btn btn-primary project-btn project-btn-primary">Actualizar</button>
                                            <button type="button" class="btn btn-secondary project-btn project-btn-default" data-dismiss="modal">Cancelar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

@push('styles')
<style>
    :root {
        --sidebar-bg: #1a73e8;
        --primary-color: #1a73e8;
        --primary-hover: #1557b0;
        --secondary-color: #6c757d;
        --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: 'Poppins', sans-serif !important;
        background-color: #f4f6f9;
    }

    .content-wrapper {
        background-color: #f4f6f9 !important;
    }

    .form-control {
        border-radius: 1rem !important;
        border: 1px solid #ced4da !important;
        padding: 0.5rem 1rem !important;
        transition: border-color 0.3s ease, box-shadow 0.3s ease !important;
        background-color: #fff !important;
        color: #333 !important;
        font-size: 0.95rem !important;
        line-height: 1.5 !important;
        width: 100% !important;
        box-shadow: var(--card-shadow) !important;
    }

    .form-control:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 0.2rem rgba(26, 115, 232, 0.25) !important;
        outline: none !important;
    }

    .form-control:hover {
        border-color: var(--primary-hover) !important;
    }

    .form-group.project-form-group {
        margin-bottom: 1rem;
    }

    .form-group.project-form-group label {
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        font-weight: 500;
        color: #333;
        font-family: 'Poppins', sans-serif;
    }

    .modal-dialog {
        max-width: 500px !important;
    }

    .modal-content.custom-modal.project-modal {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .modal-body.project-modal-body {
        padding: 1.5rem;
    }

    .modal-header.project-modal-header {
        border-bottom: none;
        background-color: var(--sidebar-bg);
    }

    .modal-footer.project-modal-footer {
        border-top: none;
        padding: 1rem 1.5rem;
    }

    .error-message.project-error {
        font-size: 0.85rem !important;
        margin-top: 0.25rem !important;
        color: #dc3545;
        font-family: 'Poppins', sans-serif;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
    }

    .btn.project-btn {
        font-family: 'Poppins', sans-serif;
        border-radius: 1rem;
        padding: 0.5rem 1.5rem;
        font-size: 0.95rem;
    }

    .btn.project-btn-primary {
        background-color: var(--primary-color);
        color: #fff;
        border: none;
    }

    .btn.project-btn-primary:hover {
        background-color: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn.project-btn-default {
        background-color: #f8f9fa;
        color: #333;
        border: 1px solid #ced4da;
    }

    .btn.project-btn-default:hover {
        background-color: #e9ecef;
        transform: translateY(-1px);
    }

    .project-box {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .project-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .project-title {
        font-family: 'Poppins', sans-serif;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }

    .alert.project-alert {
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .alert.project-alert-success {
        background-color: #d4edda;
        color: #155724;
    }

    .alert.project-alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .img-fluid {
        width: 100%;
        height: auto;
    }

    /* Preload critical styles to prevent FOUC */
    .content-header, .card, .modal {
        visibility: hidden;
    }
    .content-header.loaded, .card.loaded, .modal.loaded {
        visibility: visible;
        transition: opacity 0.3s ease;
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
// Prevent FOUC by adding 'loaded' class after DOM content is loaded
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.content-header, .card, .modal').forEach(el => {
        el.classList.add('loaded');
    });
});

// Input validation for letter-dot-only fields
document.querySelectorAll('.letter-dot-only').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s.]/g, '');
    });
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = {
        proyecto_id: {
            pattern: /^\d+$/,
            error: 'Por favor, selecciona un proyecto válido'
        },
        descripcion: {
            pattern: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*$/,
            error: 'Solo se permiten letras, espacios y puntos'
        },
        fecha_actividad: {
            pattern: /^\d{4}-\d{2}-\d{2}$/,
            error: 'Por favor, ingresa una fecha válida'
        },
        imagen_url: {
            pattern: /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/,
            error: 'Por favor, ingresa una URL válida'
        }
    };

    let isValid = true;

    Object.keys(inputs).forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        const errorElement = form.querySelector(`#${field}_error${formId.includes('edit') ? `_${formId.split('editActividadForm')[1]}` : ''}`);
        errorElement.textContent = '';

        if (input.value && !inputs[field].pattern.test(input.value)) {
            errorElement.textContent = inputs[field].error;
            isValid = false;
        }
    });

    return isValid;
}

document.getElementById('addActividadForm').addEventListener('submit', function(e) {
    if (!validateForm('addActividadForm')) {
        e.preventDefault();
    }
});

document.querySelectorAll('[id^="editActividadForm"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!validateForm(form.id)) {
            e.preventDefault();
        }
    });
});

document.getElementById('proyectoFiltro').addEventListener('change', function() {
    const proyectoId = this.value;
    const url = new URL(window.location.href);
    if (proyectoId) {
        url.searchParams.set('proyecto_id', proyectoId);
    } else {
        url.searchParams.delete('proyecto_id');
    }
    window.location.href = url.toString();
});
</script>
@endpush