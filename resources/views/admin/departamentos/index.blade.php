@extends('layouts.app')

@section('title', 'Lista de Departamentos')

@section('content')
<section class="content-header">
    <h1>Departamentos <small>Controla la información de los departamentos</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ URL::withTabToken(route('dashboard')) }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Departamentos</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Lista de Departamentos</h3>
            <div class="flex justify-end space-x-2">
                @if(Auth::check() && Auth::user()->puede_descargar)
                    <a href="{{ URL::withTabToken(route('departamentos.export.pdf')) }}" class="btn btn-danger">Exportar a PDF</a>
                    <a href="{{ URL::withTabToken(route('departamentos.export.excel')) }}" class="btn btn-success">Exportar a Excel</a>
                @endif
                @if(Auth::check() && Auth::user()->puede_agregar)
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#addDepartamentoModal">Agregar Departamento</a>
                @endif
            </div>
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
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="modal fade" id="addDepartamentoModal" tabindex="-1" role="dialog" aria-labelledby="addDepartamentoModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content custom-modal">
                        <form action="{{ route('departamentos.store') }}" method="POST" id="addDepartamentoForm">
                            @csrf
                            <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
                            <div class="modal-header bg-primary">
                                <h4 class="modal-title text-white" id="addDepartamentoModalLabel">Añadir Nuevo Departamento</h4>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="nombre_dep" class="font-semibold">Nombre</label>
                                    <input type="text" name="nombre_dep" id="nombre_dep" class="form-control letter-only" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios" placeholder="Ingresa el nombre del departamento">
                                    <span class="text-danger error-message" id="nombre_dep_error"></span>
                                </div>
                                <div class="form-group">
                                    <label for="descripcion_dep" class="font-semibold">Descripción</label>
                                    <textarea name="descripcion_dep" id="descripcion_dep" class="form-control letter-dot-only" placeholder="Ingresa la descripción del departamento" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*" title="Solo letras, espacios y puntos">{{ old('descripcion_dep') }}</textarea>
                                    <span class="text-danger error-message" id="descripcion_dep_error"></span>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Añadir</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-striped zpend-table">
                <thead>
                    <tr>
                        <th data-column="nombre">Nombre</th>
                        <th data-column="descripcion">Descripción</th>
                        <th data-column="acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departamentos as $departamento)
                        <tr>
                            <td>{{ $departamento->nombre_dep }}</td>
                            <td>{{ $departamento->descripcion_dep ?? 'Sin descripción' }}</td>
                            <td class="action-buttons">
                                @if(Auth::check() && Auth::user()->puede_ver)
                                    <a href="#" class="btn btn-info btn-xs" data-toggle="modal" data-target="#showDepartamentoModal{{ $departamento->id_departamento }}"><i class="fa fa-eye"></i></a>
                                @endif
                                @if(Auth::check() && Auth::user()->puede_editar)
                                    <a href="#" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editDepartamentoModal{{ $departamento->id_departamento }}"><i class="fa fa-edit"></i></a>
                                @endif
                                @if(Auth::check() && Auth::user()->puede_eliminar)
                                    <form action="{{ route('departamentos.destroy', $departamento) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este departamento?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
                                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        <div class="modal fade" id="showDepartamentoModal{{ $departamento->id_departamento }}" tabindex="-1" role="dialog" aria-labelledby="showDepartamentoModalLabel{{ $departamento->id_departamento }}">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content custom-modal">
                                    <div class="modal-header bg-primary">
                                        <h4 class="modal-title text-white" id="showDepartamentoModalLabel{{ $departamento->id_departamento }}">Detalles del Departamento</h4>
                                    </div>
                                    <div class="modal-body">
                                        <dl>
                                            <dt>Nombre:</dt>
                                            <dd>{{ $departamento->nombre_dep }}</dd>
                                            <dt>Descripción:</dt>
                                            <dd>{{ $departamento->descripcion_dep ?? 'Sin descripción' }}</dd>
                                            <dt>Fecha de Creación:</dt>
                                            <dd>{{ $departamento->created_at->format('d/m/Y H:i') }}</dd>
                                            <dt>Última Actualización:</dt>
                                            <dd>{{ $departamento->updated_at->format('d/m/Y H:i') }}</dd>
                                        </dl>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="editDepartamentoModal{{ $departamento->id_departamento }}" tabindex="-1" role="dialog" aria-labelledby="editDepartamentoModalLabel{{ $departamento->id_departamento }}">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content custom-modal">
                                    <form action="{{ route('departamentos.update', $departamento) }}" method="POST" id="editDepartamentoForm{{ $departamento->id_departamento }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
                                        <div class="modal-header bg-primary">
                                            <h4 class="modal-title text-white" id="editDepartamentoModalLabel{{ $departamento->id_departamento }}">Editar Departamento</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="nombre_dep_{{ $departamento->id_departamento }}" class="font-semibold">Nombre</label>
                                                <input type="text" name="nombre_dep" id="nombre_dep_{{ $departamento->id_departamento }}" class="form-control letter-only" value="{{ old('nombre_dep', $departamento->nombre_dep) }}" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios" placeholder="Ingresa el nombre del departamento">
                                                <span class="text-danger error-message" id="nombre_dep_{{ $departamento->id_departamento }}_error"></span>
                                            </div>
                                            <div class="form-group">
                                                <label for="descripcion_dep_{{ $departamento->id_departamento }}" class="font-semibold">Descripción</label>
                                                <textarea name="descripcion_dep" id="descripcion_dep_{{ $departamento->id_departamento }}" class="form-control letter-dot-only" placeholder="Ingresa la descripción del departamento" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*" title="Solo letras, espacios y puntos">{{ old('descripcion_dep', $departamento->descripcion_dep) }}</textarea>
                                                <span class="text-danger error-message" id="descripcion_dep_{{ $departamento->id_departamento }}_error"></span>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No hay departamentos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .form-control {
        border-radius: 2.5rem !important;
        border: 2px solid #ced4da !important;
        padding: 0.9rem 1.8rem !important;
        transition: border-color 0.3s ease, box-shadow 0.3s ease !important;
        background-color: #f8f9fa !important;
        color: #333 !important;
        font-size: 1.1rem !important;
        line-height: 1.6 !important;
        width: 100% !important;
        outline: none !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    }

    .form-control:focus {
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.5) !important;
        outline: none !important;
    }

    .form-control:hover {
        border-color: #0056b3 !important;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .form-group label {
        margin-bottom: 0.6rem;
        font-size: 1.1rem;
        font-weight: 600;
        color: #444;
        text-align: center;
    }

    .modal-dialog {
        max-width: 450px !important;
    }

    .modal-content.custom-modal {
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .modal-body {
        padding: 1rem 1.5rem;
    }

    .modal-header {
        border-bottom: none;
    }

    .modal-footer {
        border-top: none;
    }

    .error-message {
        font-size: 0.9rem !important;
        margin-top: 0.3rem !important;
        text-align: center;
        color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.letter-only').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
    });
});

document.querySelectorAll('.letter-dot-only').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s.]/g, '');
    });
});

function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = {
        nombre_dep: {
            pattern: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/,
            error: 'Solo se permiten letras y espacios'
        },
        descripcion_dep: {
            pattern: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*$/,
            error: 'Solo se permiten letras, espacios y puntos'
        }
    };

    let isValid = true;

    Object.keys(inputs).forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        const errorElement = form.querySelector(`#${field}_error${formId.includes('edit') ? `_${formId.split('editDepartamentoForm')[1]}` : ''}`);
        errorElement.textContent = '';

        if (input.value && !inputs[field].pattern.test(input.value)) {
            errorElement.textContent = inputs[field].error;
            isValid = false;
        }
    });

    return isValid;
}

document.getElementById('addDepartamentoForm').addEventListener('submit', function(e) {
    if (!validateForm('addDepartamentoForm')) {
        e.preventDefault();
    }
});

document.querySelectorAll('[id^="editDepartamentoForm"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!validateForm(form.id)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush