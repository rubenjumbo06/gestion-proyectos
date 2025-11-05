@extends('layouts.app')
@section('title', 'Lista de Financiadores')
@section('content')
<section class="content-header">
    <h1>Financiadores <small>Controla la información de los financiadores</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Financiadores</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Lista de Financiadores</h3>
            <div class="flex justify-end space-x-2">
                @if(Auth::check() && Auth::user()->puede_descargar)
                    <a href="{{ route('proveedores.export.pdf') }}" class="btn btn-danger">Exportar a PDF</a>
                    <a href="{{ route('proveedores.export.excel') }}" class="btn btn-success">Exportar a Excel</a>
                @endif
                @if(Auth::check() && Auth::user()->puede_agregar)
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#addProveedorModal">Agregar Financiador</a>
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

            <!-- Modal Agregar Proveedor -->
            <div class="modal fade" id="addProveedorModal" tabindex="-1" role="dialog" aria-labelledby="addProveedorModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content custom-modal">
                        <form action="{{ route('proveedores.store') }}" method="POST" id="addProveedorForm">
                            @csrf
                            <div class="modal-header bg-primary">
                                <h4 class="modal-title text-white" id="addProveedorModalLabel">Añadir Nuevo Financiador</h4>
                            </div>
                            <div class="modal-body">

                                <!-- TIPO DE DOCUMENTO -->
                                <div class="form-group">
                                    <label class="font-semibold">Tipo de Identificación</label>
                                    <div class="flex justify-center space-x-6">
                                        <label><input type="radio" name="tipo_identificacion" value="RUC" checked> RUC</label>
                                        <label><input type="radio" name="tipo_identificacion" value="DNI"> DNI</label>
                                    </div>
                                </div>

                                <!-- Número de Documento -->    
                                <div class="form-group">
                                    <label for="identificacion" class="font-semibold">Número de Identificación</label>
                                    <input type="text" name="identificacion" id="identificacion" class="form-control number-only" 
                                        maxlength="11" required placeholder="Ingresa RUC o DNI">
                                    <span class="text-danger error-message" id="identificacion_error"></span>
                                </div>

                                <!-- Nombre -->
                                <div class="form-group">
                                    <label for="nombre_prov" class="font-semibold">Nombre del Financiador</label>
                                    <input type="text" name="nombre_prov" id="nombre_prov" class="form-control letter-dot-only" 
                                           value="{{ old('nombre_prov') }}" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]+" 
                                           title="Solo letras, espacios y puntos" placeholder="Ingresa el nombre del proveedor">
                                    <span class="text-danger error-message" id="nombre_prov_error"></span>
                                </div>

                                <!-- Descripción -->
                                <div class="form-group">
                                    <label for="descripcion_prov" class="font-semibold">Descripción</label>
                                    <textarea name="descripcion_prov" id="descripcion_prov" class="form-control letter-dot-only" rows="5" 
                                              placeholder="Ingresa la descripción del proveedor" 
                                              pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*" 
                                              title="Solo letras, espacios y puntos">{{ old('descripcion_prov') }}</textarea>
                                    <span class="text-danger error-message" id="descripcion_prov_error"></span>
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

            <!-- Tabla de Proveedores -->
            <table class="table table-bordered table-striped zpend-table">
                <thead>
                <tr>
                    <th data-column="tipo">Tipo</th>
                    <th data-column="identificacion">Identificación</th>
                    <th data-column="nombre">Nombre</th>
                    <th data-column="descripcion">Descripción</th>
                    <th data-column="acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proveedores as $proveedor)
                    <tr>
                        <td>{{ $proveedor->tipo_identificacion ?? '-' }}</td>
                        <td>{{ $proveedor->identificacion ?? '-' }}</td>
                        <td>{{ $proveedor->nombre_prov }}</td>
                        <td>{{ $proveedor->descripcion_prov ?? 'Sin descripción' }}</td>
                            <td class="action-buttons">
                                @if(Auth::check() && Auth::user()->puede_ver)
                                    <a href="#" class="btn btn-info btn-xs" data-toggle="modal" data-target="#showProveedorModal{{ $proveedor->id_proveedor }}"><i class="fa fa-eye"></i></a>
                                @endif
                                @if(Auth::check() && Auth::user()->puede_editar)
                                    <a href="#" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editProveedorModal{{ $proveedor->id_proveedor }}"><i class="fa fa-edit"></i></a>
                                @endif
                                @if(Auth::check() && Auth::user()->puede_eliminar)
                                    <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este financiador?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Ver -->
                        <div class="modal fade" id="showProveedorModal{{ $proveedor->id_proveedor }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content custom-modal">
                                    <div class="modal-header bg-primary">
                                        <h4 class="modal-title text-white">Detalles del Financiador</h4>
                                    </div>
                                    <div class="modal-body">
                                        <dl>
                                            <dt>Tipo de Identificación:</dt>
                                            <dd>{{ $proveedor->tipo_identificacion ?? 'No especificado' }}</dd>
                                            <dt>Número de Identificación:</dt>
                                            <dd>{{ $proveedor->identificacion ?? 'No especificado' }}</dd>
                                            <dt>Nombre:</dt>
                                            <dd>{{ $proveedor->nombre_prov }}</dd>
                                            <dt>Descripción:</dt>
                                            <dd>{{ $proveedor->descripcion_prov ?? 'Sin descripción' }}</dd>
                                            <dt>Fecha de Creación:</dt>
                                            <dd>{{ $proveedor->created_at->format('d/m/Y H:i') }}</dd>
                                            <dt>Última Actualización:</dt>
                                            <dd>{{ $proveedor->updated_at->format('d/m/Y H:i') }}</dd>
                                        </dl>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Editar -->
                        <div class="modal fade" id="editProveedorModal{{ $proveedor->id_proveedor }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content custom-modal">
                                    <form action="{{ route('proveedores.update', $proveedor) }}" method="POST" id="editProveedorForm{{ $proveedor->id_proveedor }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-primary">
                                            <h4 class="modal-title text-white">Editar Financiador</h4>
                                        </div>
                                        <div class="modal-body">

                                            <!-- TIPO DE DOCUMENTO -->
                                            <div class="form-group">
                                                <label class="font-semibold">Tipo de Identificación</label>
                                                <div class="flex justify-center space-x-6">
                                                    <label><input type="radio" name="tipo_identificacion" value="RUC" {{ $proveedor->tipo_identificacion === 'RUC' ? 'checked' : '' }}> RUC</label>
                                                    <label><input type="radio" name="tipo_identificacion" value="DNI" {{ $proveedor->tipo_identificacion === 'DNI' ? 'checked' : '' }}> DNI</label>
                                                </div>
                                            </div>

                                            <!-- Número de Identificación -->
                                            <div class="form-group">
                                                <label for="identificacion_{{ $proveedor->id_proveedor }}" class="font-semibold">Número de Identificación</label>
                                                <input type="text" name="identificacion" id="identificacion_{{ $proveedor->id_proveedor }}" 
                                                    class="form-control number-only" value="{{ old('identificacion', $proveedor->identificacion) }}" 
                                                    maxlength="11" required>
                                                <span class="text-danger error-message" id="identificacion_{{ $proveedor->id_proveedor }}_error"></span>
                                            </div>

                                            <!-- Nombre -->
                                            <div class="form-group">
                                                <label for="nombre_prov_{{ $proveedor->id_proveedor }}" class="font-semibold">Nombre del Financiador</label>
                                                <input type="text" name="nombre_prov" id="nombre_prov_{{ $proveedor->id_proveedor }}" 
                                                       class="form-control letter-dot-only" value="{{ old('nombre_prov', $proveedor->nombre_prov) }}" 
                                                       required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]+" 
                                                       title="Solo letras, espacios y puntos">
                                                <span class="text-danger error-message" id="nombre_prov_{{ $proveedor->id_proveedor }}_error"></span>
                                            </div>

                                            <!-- Descripción -->
                                            <div class="form-group">
                                                <label for="descripcion_prov_{{ $proveedor->id_proveedor }}" class="font-semibold">Descripción</label>
                                                <textarea name="descripcion_prov" id="descripcion_prov_{{ $proveedor->id_proveedor }}" 
                                                          class="form-control letter-dot-only" rows="5" 
                                                          pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*" 
                                                          title="Solo letras, espacios y puntos">{{ old('descripcion_prov', $proveedor->descripcion_prov) }}</textarea>
                                                <span class="text-danger error-message" id="descripcion_prov_{{ $proveedor->id_proveedor }}_error"></span>
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
                            <td colspan="5" class="text-center">No hay Financiadores registrados.</td>
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
        max-width: 500px !important;
    }
    .modal-content.custom-modal {
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .modal-body {
        padding: 1rem 1.5rem;
    }
    .modal-header, .modal-footer {
        border: none;
    }
    .error-message {
        font-size: 0.9rem !important;
        margin-top: 0.3rem !important;
        text-align: center;
        color: #dc3545;
    }
    .flex {
        display: flex;
    }
    .justify-center {
        justify-content: center;
    }
    .space-x-6 > * + * {
        margin-left: 1.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Solo números
    document.querySelectorAll('.number-only').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // Solo letras, espacios y puntos
    document.querySelectorAll('.letter-dot-only').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s.]/g, '');
        });
    });

    // Función para limitar longitud según tipo de identificación
    function setupIdentificacionValidation(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        const tipoRadios = form.querySelectorAll('input[name="tipo_identificacion"]');
        const identificacionInput = form.querySelector('input[name="identificacion"]');
        const errorElement = form.querySelector(`#identificacion${formId.includes('edit') ? '_' + formId.split('editProveedorForm')[1] : ''}_error`);

        function updateMaxLength() {
            const selected = form.querySelector('input[name="tipo_identificacion"]:checked').value;
            const max = selected === 'RUC' ? 11 : 8;

            // APLICAR maxlength dinámicamente
            identificacionInput.setAttribute('maxlength', max);
            identificacionInput.placeholder = selected === 'RUC' 
                ? 'Ingresa RUC (11 dígitos)' 
                : 'Ingresa DNI (8 dígitos)';

            // RECORTAR valor si excede el nuevo límite
            if (identificacionInput.value.length > max) {
                identificacionInput.value = identificacionInput.value.slice(0, max);
            }
        }

        tipoRadios.forEach(radio => {
            radio.addEventListener('change', updateMaxLength);
        });

        // Validación al enviar
        form.addEventListener('submit', function(e) {
            const selected = form.querySelector('input[name="tipo_identificacion"]:checked').value;
            const value = identificacionInput.value.trim();
            errorElement.textContent = '';

            if (selected === 'RUC' && value.length !== 11) {
                errorElement.textContent = 'El RUC debe tener exactamente 11 dígitos';
                e.preventDefault();
                return;
            }
            if (selected === 'DNI' && value.length !== 8) {
                errorElement.textContent = 'El DNI debe tener exactamente 8 dígitos';
                e.preventDefault();
                return;
            }
        });

        // Inicializar
        updateMaxLength();
    }

    // Validación general de letras
    function validateLetterFields(formId) {
        const form = document.getElementById(formId);
        const inputs = {
            nombre_prov: {
                pattern: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]+$/,
                error: 'Solo se permiten letras, espacios y puntos'
            },
            descripcion_prov: {
                pattern: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s.]*$/,
                error: 'Solo se permiten letras, espacios y puntos'
            }
        };
        let isValid = true;

        Object.keys(inputs).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            const errorId = `${field}${formId.includes('edit') ? '_' + formId.split('editProveedorForm')[1] : ''}_error`;
            const errorElement = form.querySelector(`#${errorId}`);
            if (errorElement) errorElement.textContent = '';

            if (input && input.value && !inputs[field].pattern.test(input.value)) {
                if (errorElement) errorElement.textContent = inputs[field].error;
                isValid = false;
            }
        });
        return isValid;
    }

    // Aplicar validaciones
    document.getElementById('addProveedorForm').addEventListener('submit', function(e) {
        if (!validateLetterFields('addProveedorForm')) {
            e.preventDefault();
        }
    });

    document.querySelectorAll('[id^="editProveedorForm"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateLetterFields(form.id)) {
                e.preventDefault();
            }
        });
    });

    // Inicializar validación de identificación en ambos formularios
    setupIdentificacionValidation('addProveedorForm');
    document.querySelectorAll('[id^="editProveedorForm"]').forEach(form => {
        setupIdentificacionValidation(form.id);
    });
</script>
@endpush