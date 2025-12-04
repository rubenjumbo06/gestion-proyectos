@extends('layouts.app')

@section('title', 'Lista de Trabajadores')

@section('content')
<section class="content-header">
    <h1>Trabajadores <small>Controla la información de los trabajadores</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ URL::withTabToken(route('dashboard')) }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Trabajadores</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Lista de Trabajadores</h3>
            <div class="flex justify-end space-x-2">
                @if(Auth::check() && Auth::user()->permisos && Auth::user()->puede_descargar)
                    <a href="{{ URL::withTabToken(route('trabajadores.export.pdf')) }}" class="btn btn-danger">Exportar a PDF</a>
                    <a href="{{ URL::withTabToken(route('trabajadores.export.excel')) }}" class="btn btn-success">Exportar a Excel</a>
                @endif
                @if(Auth::check() && Auth::user()->permisos && Auth::user()->puede_agregar)
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#addTrabajadorModal">Agregar Trabajador</a>
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

            <!-- Modal para agregar trabajador -->
            <div class="modal fade" id="addTrabajadorModal" tabindex="-1" role="dialog" aria-labelledby="addTrabajadorModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content custom-modal">
                        <form action="{{ route('trabajadores.store') }}" method="POST" enctype="multipart/form-data" id="addTrabajadorForm">
                            @csrf
                            <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
                            <div class="modal-header bg-primary">
                                <h4 class="modal-title text-white" id="addTrabajadorModalLabel">Añadir Nuevo Trabajador</h4>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="nombre_trab" class="font-semibold text-black">Nombre</label>
                                    <input type="text" name="nombre_trab" id="nombre_trab" class="form-control letter-only" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios" placeholder="Ingresa tu nombre" value="{{ old('nombre_trab') }}">
                                    <span class="text-danger error-message" id="nombre_trab_error">{{ $errors->first('nombre_trab') }}</span>
                                </div>
                                <div class="form-group">
                                    <label for="apellido_trab" class="font-semibold text-black">Apellidos</label>
                                    <input type="text" name="apellido_trab" id="apellido_trab" class="form-control letter-only" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios" placeholder="Ingresa tus apellidos" value="{{ old('apellido_trab') }}">
                                    <span class="text-danger error-message" id="apellido_trab_error">{{ $errors->first('apellido_trab') }}</span>
                                </div>
                                <div class="form-group flex">
                                    <div class="flex-1 mr-2">
                                        <label for="dni_trab" class="font-semibold text-black">DNI</label>
                                        <input type="text" name="dni_trab" id="dni_trab" maxlength="8" minlength="8" class="form-control number-only" required pattern="\d{8}" title="Debe contener exactamente 8 dígitos" placeholder="Ingresa tu DNI" value="{{ old('dni_trab') }}">
                                        <span class="text-danger error-message" id="dni_trab_error">{{ $errors->first('dni_trab') }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <label for="num_telef" class="font-semibold text-black">Teléfono</label>
                                        <input type="tel" name="num_telef" id="num_telef" maxlength="9" minlength="9" class="form-control number-only" required pattern="\d{9}" title="Debe contener exactamente 9 dígitos" placeholder="Ingresa tu teléfono" value="{{ old('num_telef') }}">
                                        <span class="text-danger error-message" id="num_telef_error">{{ $errors->first('num_telef') }}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="correo_trab" class="font-semibold text-black">Correo</label>
                                    <input type="email" name="correo_trab" id="correo_trab" class="form-control" required placeholder="Ingresa tu correo" value="{{ old('correo_trab') }}">
                                    <span class="text-danger error-message" id="correo_trab_error">{{ $errors->first('correo_trab') }}</span>
                                </div>
                                <div class="form-group flex items-start">
                                    <div class="flex-1">
                                        <label class="font-semibold text-black">Sexo</label>
                                        <div class="flex space-x-4">
                                            <div class="form-check inline-flex items-center">
                                                <input type="radio" name="sexo_trab" id="sexo_masculino" value="Masculino" class="form-check-input" required {{ old('sexo_trab') == 'Masculino' ? 'checked' : '' }}>
                                                <label for="sexo_masculino" class="form-check-label text-black ml-2">Masculino</label>
                                            </div>
                                            <div class="form-check inline-flex items-center">
                                                <input type="radio" name="sexo_trab" id="sexo_femenino" value="Femenino" class="form-check-input" required {{ old('sexo_trab') == 'Femenino' ? 'checked' : '' }}>
                                                <label for="sexo_femenino" class="form-check-label text-black ml-2">Femenino</label>
                                            </div>
                                        </div>
                                        <span class="text-danger error-message" id="sexo_trab_error">{{ $errors->first('sexo_trab') }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <label for="fecha_nac" class="font-semibold text-black">Fecha de Nacimiento</label>
                                        <input type="date" name="fecha_nac" id="fecha_nac" class="form-control" required value="{{ old('fecha_nac') }}">
                                        <span class="text-danger error-message" id="fecha_nac_error">{{ $errors->first('fecha_nac') }}</span>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label for="departamento_nombre" class="block font-semibold text-black">
                                        Departamento
                                    </label>
                                    <input type="text" id="departamento_nombre" 
                                           class="form-control" 
                                           placeholder="Selecciona un departamento"
                                           readonly
                                           data-toggle="modal" data-target="#modalDepartamentos">
                                    <input type="hidden" id="id_departamento" name="id_departamento">
                                    <span class="text-danger error-message" id="id_departamento_error">{{ $errors->first('id_departamento') }}</span>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Añadir</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal para seleccionar departamento (Add) -->
            <div class="modal fade" id="modalDepartamentos" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-sm modal-dialog-centered custom-modal" role="document">
                    <div class="modal-content">
                        <div class="modal-header justify-content-center">
                            <h5 class="modal-title font-bold">Selecciona un Departamento</h5>
                        </div>
                        <div class="modal-body">
                            @if ($departamentos->isEmpty())
                                <div class="text-center text-danger">No hay departamentos disponibles. Por favor, añade departamentos primero.</div>
                            @else
                                @foreach($departamentos as $departamento)
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="departamento_option" 
                                               id="dep{{ $departamento->id_departamento }}" 
                                               value="{{ $departamento->id_departamento }}" 
                                               data-nombre="{{ $departamento->nombre_dep }}">
                                        <label class="form-check-label" for="dep{{ $departamento->id_departamento }}">
                                            {{ $departamento->nombre_dep }}
                                        </label>
                                    </div>
                                @endforeach
                            @endif
                            <div id="departamento_error_msg" class="text-danger text-center" style="display:none; margin-top: 10px;">Por favor, selecciona un departamento.</div>
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="button" class="btn btn-primary" id="guardarDepartamento">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-striped zpend-table">
                <thead>
                    <tr>
                        <th data-column="nombre">Nombre</th>
                        <th data-column="apellidos">Apellidos</th>
                        <th data-column="dni">DNI</th>
                        <th data-column="correo">Correo</th>
                        <th data-column="telefono">Teléfono</th>
                        <th data-column="sexo">Sexo</th>
                        <th data-column="fecha_nacimiento">Fecha de Nacimiento</th>
                        <th data-column="departamento">Departamento</th>
                        <th data-column="acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trabajadores as $trabajador)
                        <tr>
                            <td>{{ $trabajador->nombre_trab }}</td>
                            <td>{{ $trabajador->apellido_trab }}</td>
                            <td>{{ $trabajador->dni_trab }}</td>
                            <td>{{ $trabajador->correo_trab }}</td>
                            <td>{{ $trabajador->num_telef }}</td>
                            <td>{{ $trabajador->sexo_trab }}</td>
                            <td>{{ $trabajador->fecha_nac->format('d/m/Y') }}</td>
                            <td>{{ $trabajador->departamento ? $trabajador->departamento->nombre_dep : 'Sin departamento' }}</td>
                            <td class="action-buttons flex space-x-1">
                                @if(Auth::check() && Auth::user()->permisos && Auth::user()->puede_ver)
                                    <a href="#" class="btn btn-info btn-xs" data-toggle="modal" data-target="#showTrabajadorModal{{ $trabajador->id_trabajadores }}"><i class="fa fa-eye"></i></a>
                                @endif
                                @if(Auth::check() && Auth::user()->permisos && Auth::user()->puede_editar)
                                    <a href="#" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editTrabajadorModal{{ $trabajador->id_trabajadores }}"><i class="fa fa-edit"></i></a>
                                @endif
                                @if(Auth::check() && Auth::user()->permisos && Auth::user()->puede_eliminar)
                                    <form action="{{ route('trabajadores.destroy', $trabajador) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este trabajador?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
                                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal para ver detalles -->
                        <div class="modal fade" id="showTrabajadorModal{{ $trabajador->id_trabajadores }}" tabindex="-1" role="dialog" aria-labelledby="showTrabajadorModalLabel{{ $trabajador->id_trabajadores }}">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content custom-modal">
                                    <div class="modal-header bg-primary text-white">
                                        <h4 class="modal-title font-semibold" id="showTrabajadorModalLabel{{ $trabajador->id_trabajadores }}" style="font-size: 1.5rem !important; font-weight: bold !important;">Detalles del Trabajador</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="card">
                                            <div class="card-body" style="background-color: transparent !important; box-shadow: none !important;">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded">
                                                            <h6 class="font-semibold" style="font-size: 1.5rem !important; font-weight: bold !important; background-color: transparent !important; box-shadow: none !important;">Nombre</h6>
                                                            <p class="mb-0">{{ $trabajador->nombre_trab }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded">
                                                            <h6 class="font-semibold" style="font-size: 1.5rem !important; font-weight: bold !important; background-color: transparent !important; box-shadow: none !important;">Apellidos</h6>
                                                            <p class="mb-0">{{ $trabajador->apellido_trab }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded">
                                                            <h6 class="font-semibold" style="font-size: 1.5rem !important; font-weight: bold !important; background-color: transparent !important; box-shadow: none !important;">DNI</h6>
                                                            <p class="mb-0">{{ $trabajador->dni_trab }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded">
                                                            <h6 class="font-semibold" style="font-size: 1.5rem !important; font-weight: bold !important; background-color: transparent !important; box-shadow: none !important;">Correo</h6>
                                                            <p class="mb-0">{{ $trabajador->correo_trab }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded">
                                                            <h6 class="font-semibold" style="font-size: 1.5rem !important; font-weight: bold !important; background-color: transparent !important; box-shadow: none !important;">Teléfono</h6>
                                                            <p class="mb-0">{{ $trabajador->num_telef }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded">
                                                            <h6 class="font-semibold" style="font-size: 1.5rem !important; font-weight: bold !important; background-color: transparent !important; box-shadow: none !important;">Sexo</h6>
                                                            <p class="mb-0">{{ $trabajador->sexo_trab }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded">
                                                            <h6 class="font-semibold" style="font-size: 1.5rem !important; font-weight: bold !important; background-color: transparent !important; box-shadow: none !important;">Fecha de Nacimiento</h6>
                                                            <p class="mb-0">{{ $trabajador->fecha_nac->format('d/m/Y') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded">
                                                            <h6 class="font-semibold" style="font-size: 1.5rem !important; font-weight: bold !important; background-color: transparent !important; box-shadow: none !important;">Departamento</h6>
                                                            <p class="mb-0">{{ $trabajador->departamento ? $trabajador->departamento->nombre_dep : 'Sin departamento' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded">
                                                            <h6 class="font-semibold" style="font-size: 1.5rem !important; font-weight: bold !important; background-color: transparent !important; box-shadow: none !important;">Fecha de Creación</h6>
                                                            <p class="mb-0">{{ $trabajador->created_at->format('d/m/Y H:i') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded">
                                                            <h6 class="font-semibold" style="font-size: 1.5rem !important; font-weight: bold !important; background-color: transparent !important; box-shadow: none !important;">Última Actualización</h6>
                                                            <p class="mb-0">{{ $trabajador->updated_at->format('d/m/Y H:i') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal para editar trabajador -->
                        <div class="modal fade" id="editTrabajadorModal{{ $trabajador->id_trabajadores }}" tabindex="-1" role="dialog" aria-labelledby="editTrabajadorModalLabel{{ $trabajador->id_trabajadores }}">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content custom-modal">
                                    <form action="{{ route('trabajadores.update', $trabajador) }}" method="POST" enctype="multipart/form-data" id="editTrabajadorForm{{ $trabajador->id_trabajadores }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
                                        <div class="modal-header bg-primary">
                                            <h4 class="modal-title text-white font-semibold" id="editTrabajadorModalLabel{{ $trabajador->id_trabajadores }}">Editar Trabajador</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="nombre_trab_{{ $trabajador->id_trabajadores }}" class="font-semibold text-black">Nombre</label>
                                                <input type="text" name="nombre_trab" id="nombre_trab_{{ $trabajador->id_trabajadores }}" class="form-control letter-only" value="{{ old('nombre_trab', $trabajador->nombre_trab) }}" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios" placeholder="Ingresa tu nombre">
                                                <span class="text-danger error-message" id="nombre_trab_{{ $trabajador->id_trabajadores }}_error">{{ $errors->first('nombre_trab') }}</span>
                                            </div>
                                            <div class="form-group">
                                                <label for="apellido_trab_{{ $trabajador->id_trabajadores }}" class="font-semibold text-black">Apellidos</label>
                                                <input type="text" name="apellido_trab" id="apellido_trab_{{ $trabajador->id_trabajadores }}" class="form-control letter-only" value="{{ old('apellido_trab', $trabajador->apellido_trab) }}" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios" placeholder="Ingresa tus apellidos">
                                                <span class="text-danger error-message" id="apellido_trab_{{ $trabajador->id_trabajadores }}_error">{{ $errors->first('apellido_trab') }}</span>
                                            </div>
                                            <div class="form-group flex">
                                                <div class="flex-1 mr-2">
                                                    <label for="dni_trab_{{ $trabajador->id_trabajadores }}" class="font-semibold text-black">DNI</label>
                                                    <input type="text" name="dni_trab" id="dni_trab_{{ $trabajador->id_trabajadores }}" maxlength="8" minlength="8" class="form-control number-only" value="{{ old('dni_trab', $trabajador->dni_trab) }}" required pattern="\d{8}" title="Debe contener exactamente 8 dígitos" placeholder="Ingresa tu DNI">
                                                    <span class="text-danger error-message" id="dni_trab_{{ $trabajador->id_trabajadores }}_error">{{ $errors->first('dni_trab') }}</span>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="num_telef_{{ $trabajador->id_trabajadores }}" class="font-semibold text-black">Teléfono</label>
                                                    <input type="tel" name="num_telef" id="num_telef_{{ $trabajador->id_trabajadores }}" maxlength="9" minlength="9" class="form-control number-only" value="{{ old('num_telef', $trabajador->num_telef) }}" required pattern="\d{9}" title="Debe contener exactamente 9 dígitos" placeholder="Ingresa tu teléfono">
                                                    <span class="text-danger error-message" id="num_telef_{{ $trabajador->id_trabajadores }}_error">{{ $errors->first('num_telef') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="correo_trab_{{ $trabajador->id_trabajadores }}" class="font-semibold text-black">Correo</label>
                                                <input type="email" name="correo_trab" id="correo_trab_{{ $trabajador->id_trabajadores }}" class="form-control" value="{{ old('correo_trab', $trabajador->correo_trab) }}" required placeholder="Ingresa tu correo">
                                                <span class="text-danger error-message" id="correo_trab_{{ $trabajador->id_trabajadores }}_error">{{ $errors->first('correo_trab') }}</span>
                                            </div>
                                            <div class="form-group flex items-start">
                                                <div class="flex-1">
                                                    <label class="font-semibold text-black">Sexo</label>
                                                    <div class="flex space-x-4">
                                                        <div class="form-check inline-flex items-center">
                                                            <input type="radio" name="sexo_trab" id="sexo_masculino_{{ $trabajador->id_trabajadores }}" value="Masculino" class="form-check-input" {{ old('sexo_trab', $trabajador->sexo_trab) == 'Masculino' ? 'checked' : '' }} required>
                                                            <label for="sexo_masculino_{{ $trabajador->id_trabajadores }}" class="form-check-label text-black ml-2">Masculino</label>
                                                        </div>
                                                        <div class="form-check inline-flex items-center">
                                                            <input type="radio" name="sexo_trab" id="sexo_femenino_{{ $trabajador->id_trabajadores }}" value="Femenino" class="form-check-input" {{ old('sexo_trab', $trabajador->sexo_trab) == 'Femenino' ? 'checked' : '' }} required>
                                                            <label for="sexo_femenino_{{ $trabajador->id_trabajadores }}" class="form-check-label text-black ml-2">Femenino</label>
                                                        </div>
                                                    </div>
                                                    <span class="text-danger error-message" id="sexo_trab_{{ $trabajador->id_trabajadores }}_error">{{ $errors->first('sexo_trab') }}</span>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="fecha_nac_{{ $trabajador->id_trabajadores }}" class="font-semibold text-black">Fecha de Nacimiento</label>
                                                    <input type="date" name="fecha_nac" id="fecha_nac_{{ $trabajador->id_trabajadores }}" class="form-control" value="{{ old('fecha_nac', $trabajador->fecha_nac->format('Y-m-d')) }}" required>
                                                    <span class="text-danger error-message" id="fecha_nac_{{ $trabajador->id_trabajadores }}_error">{{ $errors->first('fecha_nac') }}</span>
                                                </div>
                                            </div>
                                            <div class="mb-0">
                                                <label for="departamento_nombre_edit_{{ $trabajador->id_trabajadores }}" class="block font-semibold text-black">
                                                    Departamento
                                                </label>
                                                <input type="text" id="departamento_nombre_edit_{{ $trabajador->id_trabajadores }}" 
                                                       class="form-control" 
                                                       value="{{ old('departamento_nombre', $trabajador->departamento ? $trabajador->departamento->nombre_dep : '') }}"
                                                       readonly
                                                       data-toggle="modal" data-target="#modalDepartamentosEdit{{ $trabajador->id_trabajadores }}">
                                                <input type="hidden" id="id_departamento_edit_{{ $trabajador->id_trabajadores }}" 
                                                       name="id_departamento" 
                                                       value="{{ old('id_departamento', $trabajador->id_departamento) }}">
                                                <span class="text-danger error-message" id="id_departamento_edit_{{ $trabajador->id_trabajadores }}_error">{{ $errors->first('id_departamento') }}</span>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal para seleccionar departamento en edit modal -->
                        <div class="modal fade" id="modalDepartamentosEdit{{ $trabajador->id_trabajadores }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-sm modal-dialog-centered custom-modal" role="document">
                                <div class="modal-content">
                                    <div class="modal-header justify-content-center">
                                        <h5 class="modal-title font-bold">Selecciona un Departamento</h5>
                                    </div>
                                    <div class="modal-body">
                                        @if ($departamentos->isEmpty())
                                            <div class="text-center text-danger">No hay departamentos disponibles. Por favor, añade departamentos primero.</div>
                                        @else
                                            @foreach($departamentos as $departamento)
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="radio" 
                                                           name="departamento_option_edit_{{ $trabajador->id_trabajadores }}" 
                                                           id="dep_edit_{{ $trabajador->id_trabajadores }}_{{ $departamento->id_departamento }}" 
                                                           value="{{ $departamento->id_departamento }}" 
                                                           data-nombre="{{ $departamento->nombre_dep }}"
                                                           {{ old('id_departamento', $trabajador->id_departamento) == $departamento->id_departamento ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="dep_edit_{{ $trabajador->id_trabajadores }}_{{ $departamento->id_departamento }}">
                                                        {{ $departamento->nombre_dep }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @endif
                                        <div id="departamento_error_msg_edit_{{ $trabajador->id_trabajadores }}" class="text-danger text-center" style="display:none; margin-top: 10px;">Por favor, selecciona un departamento.</div>
                                    </div>
                                    <div class="modal-footer d-flex justify-content-center">
                                        <button type="button" class="btn btn-primary" id="guardarDepartamentoEdit_{{ $trabajador->id_trabajadores }}">Guardar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No hay trabajadores registrados.</td>
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

    /* SOLO para el modal departamentos */
    #modalDepartamentos .modal-dialog,
    [id^="modalDepartamentosEdit"] .modal-dialog {
        max-width: 350px !important;
    }

    #modalDepartamentos .modal-content,
    [id^="modalDepartamentosEdit"] .modal-content {
        border-radius: 15px;
    }

    .modal-dialog {
        max-width: 450px !important;
    }

    .modal-body .card {
        background-color: transparent !important;
        box-shadow: none !important;
    }

    .modal-body .card-body {
        background-color: transparent !important;
        box-shadow: none !important;
    }

    .modal-body {
        padding-bottom: 0;
    }

    .modal-body .card-body h6 {
        font-size: 1.25rem !important;
        font-weight: bold !important;
        background-color: transparent !important;
        box-shadow: none !important;
    }

    .modal-body .card-body p {
        margin-bottom: 0 !important;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }

    .error-message {
        font-size: 0.9rem !important;
        margin-top: 0.3rem !important;
        text-align: center;
        color: #dc3545;
    }

    .custom-modal {
        max-width: 400px;
    }

    .custom-modal .modal-content {
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .modal .modal-body {
        padding: 1rem 1.5rem;
    }

    .modal .modal-content {
        border-radius: 12px;
    }

    .modal .modal-dialog {
        margin: 1.5rem auto;
    }

    .modal-header {
        border-bottom: none;
    }

    .modal-footer {
        border-top: none;
    }
</style>
@endpush

@push('scripts')
<script>
// Guardar departamento para el modal de "Añadir Trabajador"
document.getElementById('guardarDepartamento')?.addEventListener('click', function() {
    const seleccionado = document.querySelector('input[name="departamento_option"]:checked');
    const errorMsg = document.getElementById('departamento_error_msg');
    const departamentoNombre = document.getElementById('departamento_nombre');
    const departamentoId = document.getElementById('id_departamento');

    if (!seleccionado) {
        errorMsg.style.display = 'block';
        departamentoNombre.classList.add('is-invalid');
        return;
    }
    errorMsg.style.display = 'none';
    departamentoNombre.value = seleccionado.dataset.nombre;
    departamentoId.value = seleccionado.value;
    departamentoNombre.classList.remove('is-invalid');
    $('#modalDepartamentos').modal('hide');
});
// Solo en el modal de "Añadir Trabajador"
document.getElementById('dni_trab').addEventListener('blur', function () {
    let dni = this.value.trim();
    
    if (dni.length !== 8 || !/^\d+$/.test(dni)) return;

    // Mostrar loading
    this.disabled = true;
    let loading = document.createElement('span');
    loading.textContent = ' Consultando...';
    loading.className = 'text-info ml-2';
    this.parentNode.appendChild(loading);

    fetch(`/api/dni/${dni}`)
        .then(res => res.json())
        .then(data => {
            if (data.nombres) {
                document.getElementById('nombre_trab').value = data.nombres;
                document.getElementById('apellido_trab').value = 
                    (data.apellido_paterno + ' ' + data.apellido_materno).trim();
            }

            if (data.fecha_nacimiento) {
                // Convertir "15/03/1995" → "1995-03-15"
                const [dia, mes, anio] = data.fecha_nacimiento.split('/');
                document.getElementById('fecha_nac').value = `${anio}-${mes}-${dia}`;
            }

            // Opcional: autoseleccionar sexo si viene (algunos scrapers lo dan)
        })
        .catch(err => {
            console.log('Error consultando DNI:', err);
        })
        .finally(() => {
            this.disabled = false;
            if (loading && loading.parentNode) loading.remove();
        });
});
// Guardar departamento para los modales de "Editar Trabajador"
document.querySelectorAll('[id^="guardarDepartamentoEdit_"]').forEach(button => {
    button.addEventListener('click', function() {
        const trabajadorId = this.id.split('guardarDepartamentoEdit_')[1];
        const seleccionado = document.querySelector(`input[name="departamento_option_edit_${trabajadorId}"]:checked`);
        const errorMsg = document.getElementById(`departamento_error_msg_edit_${trabajadorId}`);
        const departamentoNombre = document.getElementById(`departamento_nombre_edit_${trabajadorId}`);
        const departamentoId = document.getElementById(`id_departamento_edit_${trabajadorId}`);

        if (!seleccionado) {
            errorMsg.style.display = 'block';
            departamentoNombre.classList.add('is-invalid');
            return;
        }

        errorMsg.style.display = 'none';
        departamentoNombre.value = seleccionado.dataset.nombre;
        departamentoId.value = seleccionado.value;
        departamentoNombre.classList.remove('is-invalid');
        document.getElementById(`id_departamento_edit_${trabajadorId}_error`).textContent = '';

        console.log('Departamento seleccionado (Edit ' + trabajadorId + '):', {
            id: departamentoId.value,
            nombre: departamentoNombre.value
        });

        $(`#modalDepartamentosEdit${trabajadorId}`).modal('hide');
    });
});

// Validaciones de inputs (letras y números)
document.querySelectorAll('.letter-only').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
    });
});

document.querySelectorAll('.number-only').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});

// Función de validación del formulario
function validateForm(formId) {
    const form = document.getElementById(formId);
    const suffix = formId.includes('edit') ? `_${formId.split('editTrabajadorForm')[1]}` : '';
    const inputs = {
        nombre_trab: {
            pattern: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/,
            error: 'Solo se permiten letras y espacios'
        },
        apellido_trab: {
            pattern: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/,
            error: 'Solo se permiten letras y espacios'
        },
        dni_trab: {
            pattern: /^\d{8}$/,
            error: 'Debe contener exactamente 8 dígitos'
        },
        num_telef: {
            pattern: /^\d{9}$/,
            error: 'Debe contener exactamente 9 dígitos'
        },
        correo_trab: {
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            error: 'Ingresa un correo electrónico válido'
        },
        sexo_trab: {
            check: () => form.querySelector('input[name="sexo_trab"]:checked'),
            error: 'Selecciona una opción'
        },
        fecha_nac: {
            check: (input) => input.value !== '',
            error: 'Selecciona una fecha'
        },
        id_departamento: {
            check: (input) => input && input.value && input.value !== '' && !isNaN(input.value),
            error: 'Selecciona un departamento válido'
        }
    };

    let isValid = true;

    Object.keys(inputs).forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        const errorId = `${field}${field === 'id_departamento' ? (formId.includes('edit') ? '_edit' : '') + '_error' : '_error'}${suffix}`;
        const errorElement = document.getElementById(errorId);

        if (errorElement) {
            errorElement.textContent = '';
        }

        if (field === 'sexo_trab' || field === 'id_departamento' || field === 'fecha_nac') {
            if (!inputs[field].check(input)) {
                if (errorElement) {
                    errorElement.textContent = inputs[field].error;
                }
                if (field === 'id_departamento') {
                    const nombreId = `departamento_nombre${formId.includes('edit') ? '_edit_' + formId.split('editTrabajadorForm')[1] : ''}`;
                    document.getElementById(nombreId).classList.add('is-invalid');
                } else if (input) {
                    input.classList.add('is-invalid');
                }
                isValid = false;
            } else {
                if (field === 'id_departamento') {
                    const nombreId = `departamento_nombre${formId.includes('edit') ? '_edit_' + formId.split('editTrabajadorForm')[1] : ''}`;
                    document.getElementById(nombreId).classList.remove('is-invalid');
                } else if (input) {
                    input.classList.remove('is-invalid');
                }
            }
        } else {
            if (!inputs[field].pattern.test(input.value)) {
                if (errorElement) {
                    errorElement.textContent = inputs[field].error;
                }
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        }
    });

    console.log(`Validación formulario ${formId}:`, {
        isValid: isValid,
        id_departamento: form.querySelector('[name="id_departamento"]')?.value || 'No encontrado'
    });

    return isValid;
}

// Evento submit para el formulario de "Añadir Trabajador"
document.getElementById('addTrabajadorForm').addEventListener('submit', function(e) {
    if (!validateForm('addTrabajadorForm')) {
        e.preventDefault();
        console.log('Formulario de Añadir no válido');
    } else {
        console.log('Formulario de Añadir válido, enviando...');
        console.log('Datos enviados (Add):', {
            id_departamento: document.getElementById('id_departamento').value
        });
    }
});

// Evento submit para los formularios de "Editar Trabajador"
document.querySelectorAll('[id^="editTrabajadorForm"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!validateForm(form.id)) {
            e.preventDefault();
            console.log(`Formulario de Editar ${form.id} no válido`);
        } else {
            console.log(`Formulario de Editar ${form.id} válido, enviando...`);
            console.log('Datos enviados (Edit):', {
                id_departamento: form.querySelector('[name="id_departamento"]').value
            });
        }
    });
});

// === AUTOCOMPLETADO DNI - SOLO EN MODAL AGREGAR (FUNCIONANDO 100%) ===
document.getElementById('addTrabajadorModal').addEventListener('shown.bs.modal', function () {
    const dniInput = document.getElementById('dni_trab');
    const nombreInput = document.getElementById('nombre_trab');
    const apellidoInput = document.getElementById('apellido_trab');
    const fechaInput = document.getElementById('fecha_nac');

    // Limpiar evento anterior para evitar duplicados
    dniInput.removeEventListener('blur', consultarDni);

    // Función principal
    function consultarDni() {
        let dni = dniInput.value.trim();

        if (dni.length !== 8 || !/^\d+$/.test(dni)) {
            return;
        }

        // Evitar consultas duplicadas
        if (dniInput.dataset.consultando === 'true') return;
        dniInput.dataset.consultando = 'true';

        // Loading visual
        const loading = document.createElement('small');
        loading.textContent = ' Consultando DNI...';
        loading.className = 'text-primary font-italic ml-2';
        dniInput.parentNode.appendChild(loading);

        fetch(`/api/dni/${dni}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
    console.log('DNI encontrado:', data);

    // Función para poner primera letra en mayúscula
    function toTitleCase(str) {
    if (!str) return '';
    return str
        .toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

    if (data.nombres) {
        nombreInput.value = toTitleCase(data.nombres.trim());
    }

    const apellidos = [data.apellido_paterno, data.apellido_materno]
        .filter(Boolean)
        .join(' ')
        .trim();
    if (apellidos) {
        apellidoInput.value = toTitleCase(apellidos);
    }

    if (data.fecha_nacimiento) {
        const [dia, mes, anio] = data.fecha_nacimiento.split('/');
        if (dia && mes && anio) {
            fechaInput.value = `${anio}-${mes.padStart(2, '0')}-${dia.padStart(2, '0')}`;
        }
    }

    // Mensaje de éxito
    const success = document.createElement('small');
    success.textContent = ' ¡DNI encontrado!';
    success.className = 'text-success font-bold ml-2';
    dniInput.parentNode.appendChild(success);
    setTimeout(() => success.remove(), 3000);
})
            .catch(err => {
                console.error('Error consultando DNI:', err);
                const error = document.createElement('small');
                error.textContent = ' No encontrado';
                error.className = 'text-danger ml-2';
                dniInput.parentNode.appendChild(error);
                setTimeout(() => error.remove(), 4000);
            })
            .finally(() => {
                dniInput.dataset.consultando = 'false';
                if (loading.parentNode) loading.remove();
                dniInput.disabled = false;
            });
    }

    // Adjuntar evento blur
    dniInput.addEventListener('blur', consultarDni);

    // Opcional: también al presionar Enter
    dniInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            consultarDni();
        }
    });
});

// Reset al abrir los modales de "Editar Trabajador"
document.querySelectorAll('[id^="editTrabajadorModal"]').forEach(modal => {
    modal.addEventListener('shown.bs.modal', function () {
        const trabajadorId = this.id.replace('editTrabajadorModal', '');
        const dniInput = document.getElementById(`dni_trab_${trabajadorId}`);
        const nombreInput = document.getElementById(`nombre_trab_${trabajadorId}`);
        const apellidoInput = document.getElementById(`apellido_trab_${trabajadorId}`);
        const fechaInput = document.getElementById(`fecha_nac_${trabajadorId}`);

        function consultarDniEdit() {
            let dni = dniInput.value.trim();
            if (dni.length !== 8 || !/^\d+$/.test(dni)) return;
            if (dniInput.dataset.consultando === 'true') return;

            dniInput.dataset.consultando = 'true';
            const loading = document.createElement('small');
            loading.textContent = ' Consultando...';
            loading.className = 'text-info ml-2';
            dniInput.parentNode.appendChild(loading);

            fetch(`/api/dni/${dni}`)
                .then(r => { if (!r.ok) throw new Error(); return r.json(); })
                .then(data => {
                    if (data.nombres) {
                        nombreInput.value = toTitleCase(data.nombres.trim());
                    }
                    const apellidos = [data.apellido_paterno, data.apellido_materno]
                        .filter(Boolean).join(' ').trim();
                    if (apellidos) {
                        apellidoInput.value = toTitleCase(apellidos);
                    }
                    if (data.fecha_nacimiento) {
                        const [d, m, a] = data.fecha_nacimiento.split('/');
                        fechaInput.value = `${a}-${m.padStart(2,'0')}-${d.padStart(2,'0')}`;
                    }

                    const success = document.createElement('small');
                    success.textContent = ' ¡Actualizado!';
                    success.className = 'text-success font-bold ml-2';
                    dniInput.parentNode.appendChild(success);
                    setTimeout(() => success.remove(), 3000);
                })
                .catch(() => {
                    const err = document.createElement('small');
                    err.textContent = ' No encontrado';
                    err.className = 'text-danger ml-2';
                    dniInput.parentNode.appendChild(err);
                    setTimeout(() => err.remove(), 4000);
                })
                .finally(() => {
                    dniInput.dataset.consultando = 'false';
                    if (loading.parentNode) loading.remove();
                });
        }

        // Limpiamos eventos anteriores y añadimos los nuevos
        dniInput.removeEventListener('blur', consultarDniEdit);
        dniInput.addEventListener('blur', consultarDniEdit);
        dniInput.addEventListener('keypress', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                consultarDniEdit();
            }
        });
    });
});
</script>
@endpush