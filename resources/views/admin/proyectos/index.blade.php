@extends('layouts.app')

@section('title', 'Lista de Proyectos')

@section('content')
<!-- Tailwind CSS CDN for grid layout -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<section class="content-header">
    <h1>Proyectos <small>Controla la información de los proyectos</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Proyectos</li>
    </ol>
</section>

<section class="content">
    <div class="flex justify-between items-center mb-4">
        <h3 class="project-title">Lista de Proyectos</h3>
        <a href="#" class="project-btn project-btn-primary" data-toggle="modal" data-target="#addProyectoModal">Agregar Proyecto</a>
    </div>
    <!-- Caja de búsqueda -->
    <div class="mb-6">
        <input type="text" id="searchProject" class="project-input w-full md:w-1/3 bg-white" placeholder="Buscar por nombre de proyecto..." />
    </div>
    <!-- Notificaciones -->
    @if (session('success'))
        <div class="project-alert project-alert-success">
            <button type="button" class="project-alert-close" data-dismiss="alert">×</button>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error') && session('error') !== '¡Ups! No se pudo crear el proyecto.')
        <div class="project-alert project-alert-danger">
            <button type="button" class="project-alert-close" data-dismiss="alert">×</button>
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="project-alert project-alert-danger">
            <button type="button" class="project-alert-close" data-dismiss="alert">×</button>
            <ul>
                @foreach ($errors->all() as $error)
                    @if ($error !== '¡Ups! No se pudo crear el proyecto.')
                        <li>{{ $error }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

   <!-- Cuadrícula de proyectos (solo 4 más recientes) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 font-poppins">
        @forelse ($allProyectos->sortByDesc('fecha_creacion')->take(4) as $proyecto)
            <div class="project-box">
                <!-- Ícono de carpeta y nombre -->
                <a href="{{ route('proyectos.show', $proyecto) }}" class="project-card-link">
                    <i class="fas fa-folder project-icon"></i>
                    <span class="project-name">{{ $proyecto->nombre_proyecto }}</span>
                </a>
                <!-- Menú contextual -->
                <div class="project-context-menu">
                    <button class="project-context-btn" onclick="toggleContextMenu('context-menu-{{ $proyecto->id_proyecto }}')">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div id="context-menu-{{ $proyecto->id_proyecto }}" class="hidden project-dropdown">
                        <a href="{{ route('proyectos.show', $proyecto) }}" class="project-dropdown-item">Ver</a>
                        <a href="#" class="project-dropdown-item" data-toggle="modal" data-target="#editProyectoModal{{ $proyecto->id_proyecto }}">Editar</a>
                        <form action="{{ route('proyectos.destroy', $proyecto) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="project-dropdown-item project-dropdown-danger" onclick="return confirm('¿Seguro que deseas eliminar este proyecto?')">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-500 py-4">
                No hay proyectos registrados.
            </div>
        @endforelse
    </div>

    <!-- Nueva sección para tabla -->
        <div class="mt-8">
            <h3 class="project-title text-xl font-semibold mb-6">Todos los proyectos</h3>
            <div class="overflow-x-auto shadow-md rounded-lg">
                <table class="w-full text-xl text-left text-gray-700 border-collapse bg-white">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-6 py-3 border">Nombre del Proyecto</th>
                            <th class="px-6 py-3 border">Cliente</th>
                            <th class="px-6 py-3 border">Fecha de Creación</th>
                            <th class="px-6 py-3 border">Usuario Creador</th>
                            <th class="px-6 py-3 border">Última Actualización</th>
                        </tr>
                    </thead>
                    <tbody id="projectsTableBody">
                        @foreach ($proyectos as $proyecto)
                            <tr class="hover:bg-gray-100 border-t">
                                <td class="px-6 py-3 border"><a href="{{ route('proyectos.show', $proyecto) }}" class="text-blue-600 hover:underline">{{ $proyecto->nombre_proyecto }}</a></td>
                                <td class="px-6 py-3 border">{{ $proyecto->cliente_proyecto }}</td>
                                <td class="px-6 py-3 border">{{ $proyecto->fecha_creacion ? $proyecto->fecha_creacion->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td class="px-6 py-3 border">
                                    @if ($proyecto->user && $proyecto->user->img)
                                        <img src="{{ $proyecto->user->img }}" alt="Foto de {{ $proyecto->user->name }}" class="w-8 h-8 rounded-full inline-block mr-2">
                                    @endif
                                    {{ $proyecto->user->name ?? 'Desconocido' }}
                                </td>
                                <td class="px-6 py-3 border">{{ $proyecto->updated_at ? $proyecto->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
</section>

<!-- Modal para Crear Proyecto -->
<div class="modal fade" id="addProyectoModal" tabindex="-1" role="dialog" aria-labelledby="addProyectoModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content project-modal">
            <form action="{{ route('proyectos.store') }}" method="POST" id="addProyectoForm">
                @csrf
                <div class="project-modal-header">
                    <h4 class="project-modal-title" id="addProyectoModalLabel">Añadir Nuevo Proyecto</h4>
                </div>
                <div class="project-modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="project-form-group">
                        <label for="nombre_proyecto" class="project-label">Nombre del Proyecto</label>
                        <input type="text" name="nombre_proyecto" id="nombre_proyecto" class="project-input" value="{{ old('nombre_proyecto') }}" required placeholder="Ingresa el nombre del proyecto">
                        <span class="project-error" id="nombre_proyecto_error"></span>
                    </div>
                    <div class="project-form-group">
                        <label for="cliente_proyecto" class="project-label">Cliente del Proyecto</label>
                        <input type="text" name="cliente_proyecto" id="cliente_proyecto" class="project-input" value="{{ old('cliente_proyecto') }}" required placeholder="Ingresa el cliente del proyecto">
                        <span class="project-error" id="cliente_proyecto_error"></span>
                    </div>
                    <div class="project-form-group">
                        <label for="descripcion_proyecto" class="project-label">Descripción del Proyecto</label>
                        <textarea name="descripcion_proyecto" id="descripcion_proyecto" class="project-input" rows="5" placeholder="Ingresa una descripción">{{ old('descripcion_proyecto') }}</textarea>
                        <span class="project-error" id="descripcion_proyecto_error"></span>
                    </div>
                    <div class="project-form-group project-flex">
                        <div class="project-flex-item">
                            <label for="cantidad_trabajadores" class="project-label">Cantidad de Trabajadores</label>
                            <input type="number" name="cantidad_trabajadores" id="cantidad_trabajadores" class="project-input project-number-only" value="{{ old('cantidad_trabajadores', 0) }}" min="0" required placeholder="Ingresa la cantidad">
                            <span class="project-error" id="cantidad_trabajadores_error"></span>
                        </div>
                        <div class="project-flex-item">
                            <label for="monto_material" class="project-label">Monto para Materiales</label>
                            <input type="number" name="monto_material" id="monto_material" class="project-input project-number-only" value="{{ old('monto_material') }}" step="0.01" min="0" required placeholder="Ingresa el monto para materiales">
                            <span class="project-error" id="monto_material_error"></span>
                        </div>
                    </div>
                    <div class="project-form-group project-flex">
                        <div class="project-flex-item">
                            <label for="monto_operativos" class="project-label">Monto para Personal</label>
                            <input type="number" name="monto_operativos" id="monto_operativos" class="project-input project-number-only" value="{{ old('monto_operativos') }}" step="0.01" min="0" required placeholder="Ingresa el monto para personal">
                            <span class="project-error" id="monto_operativos_error"></span>
                        </div>
                        <div class="project-flex-item">
                            <label for="monto_servicios" class="project-label">Monto para Servicios</label>
                            <input type="number" name="monto_servicios" id="monto_servicios" class="project-input project-number-only" value="{{ old('monto_servicios') }}" step="0.01" min="0" required placeholder="Ingresa el monto para servicios">
                            <span class="project-error" id="monto_servicios_error"></span>
                        </div>
                    </div>
                    <div class="project-form-group project-flex">
                        <div class="project-flex-item">
                            <label for="fecha_inicio" class="project-label">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="project-input" value="{{ old('fecha_inicio') }}" required>
                            <span class="project-error" id="fecha_inicio_error"></span>
                        </div>
                        <div class="project-flex-item">
                            <label for="fecha_fin_aprox" class="project-label">Fecha de Fin Aproximada</label>
                            <input type="date" name="fecha_fin_aprox" id="fecha_fin_aprox" class="project-input" value="{{ old('fecha_fin_aprox') }}">
                            <span class="project-error" id="fecha_fin_aprox_error"></span>
                        </div>
                    </div>
                </div>
                <div class="project-modal-footer" style="position: sticky; bottom: 0; background: white; padding: 10px; border-top: 1px solid #ddd;">
                    <button type="submit" class="project-btn project-btn-primary" id="submit-add-proyecto">Añadir</button>
                    <button type="button" class="project-btn project-btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals para Editar Proyecto (outside the loop) -->
@forelse ($proyectos as $proyecto)
    @empty
    @endforelse
@php
    // Ya tienes $montosApartados desde el controlador
@endphp

@foreach ($proyectos as $proyecto)
@php
    // Obtener montos para este proyecto
    $montos = $montosApartados->get($proyecto->id_proyecto);
    $material = $montos->monto_material ?? 0;
    $operativos = $montos->monto_operativos ?? 0;
    $servicios = $montos->monto_servicios ?? 0;
@endphp

<div class="modal fade" id="editProyectoModal{{ $proyecto->id_proyecto }}" tabindex="-1" role="dialog" aria-labelledby="editProyectoModalLabel{{ $proyecto->id_proyecto }}">
    <div class="modal-dialog" role="document">
        <div class="modal-content project-modal">
            <form action="{{ route('proyectos.update', $proyecto) }}" method="POST" id="editProyectoForm{{ $proyecto->id_proyecto }}">
                @csrf
                @method('PUT')
                <div class="project-modal-header">
                    <h4 class="project-modal-title" id="editProyectoModalLabel{{ $proyecto->id_proyecto }}">
                        Editar Proyecto: {{ $proyecto->nombre_proyecto }}
                    </h4>
                </div>
                <div class="project-modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Nombre y Cliente -->
                    <div class="project-form-group">
                        <label for="nombre_proyecto_{{ $proyecto->id_proyecto }}" class="project-label">Nombre del Proyecto</label>
                        <input type="text" name="nombre_proyecto" id="nombre_proyecto_{{ $proyecto->id_proyecto }}" class="project-input"
                               value="{{ old('nombre_proyecto', $proyecto->nombre_proyecto) }}" required>
                        <span class="project-error" id="nombre_proyecto_{{ $proyecto->id_proyecto }}_error"></span>
                    </div>

                    <div class="project-form-group">
                        <label for="cliente_proyecto_{{ $proyecto->id_proyecto }}" class="project-label">Cliente del Proyecto</label>
                        <input type="text" name="cliente_proyecto" id="cliente_proyecto_{{ $proyecto->id_proyecto }}" class="project-input"
                               value="{{ old('cliente_proyecto', $proyecto->cliente_proyecto) }}" required>
                        <span class="project-error" id="cliente_proyecto_{{ $proyecto->id_proyecto }}_error"></span>
                    </div>

                    <!-- Descripción -->
                   <div class="project-form-group">
                        <label for="descripcion_proyecto_{{ $proyecto->id_proyecto }}" class="project-label">Descripción del Proyecto</label>
                        <textarea name="descripcion_proyecto" id="descripcion_proyecto_{{ $proyecto->id_proyecto }}" class="project-input" rows="5"
                                  placeholder="Ingresa una descripción">{{ old('descripcion_proyecto', $proyecto->descripcion_proyecto ?? '') }}</textarea>
                        <span class="project-error" id="descripcion_proyecto_{{ $proyecto->id_proyecto }}_error"></span>
                    </div>

                    <!-- Trabajadores + Materiales -->
                    <div class="project-form-group project-flex">
                        <div class="project-flex-item">
                            <label for="cantidad_trabajadores_{{ $proyecto->id_proyecto }}" class="project-label">Cantidad de Trabajadores</label>
                            <input type="number" name="cantidad_trabajadores" id="cantidad_trabajadores_{{ $proyecto->id_proyecto }}"
                                   class="project-input project-number-only"
                                   value="{{ old('cantidad_trabajadores', $proyecto->cantidad_trabajadores ?? 0) }}" min="0" required>
                            <span class="project-error" id="cantidad_trabajadores_{{ $proyecto->id_proyecto }}_error"></span>
                        </div>
                        <div class="project-flex-item">
                            <label for="monto_material_{{ $proyecto->id_proyecto }}" class="project-label">Monto para Materiales</label>
                            <input type="number" name="monto_material" id="monto_material_{{ $proyecto->id_proyecto }}"
                                   class="project-input project-number-only"
                                   value="{{ old('monto_material', $material) }}" step="0.01" min="0" required>
                            <span class="project-error" id="monto_material_{{ $proyecto->id_proyecto }}_error"></span>
                        </div>
                    </div>

                    <!-- Personal + Servicios -->
                    <div class="project-form-group project-flex">
                        <div class="project-flex-item">
                            <label for="monto_operativos_{{ $proyecto->id_proyecto }}" class="project-label">Monto para Personal</label>
                            <input type="number" name="monto_operativos" id="monto_operativos_{{ $proyecto->id_proyecto }}"
                                   class="project-input project-number-only"
                                   value="{{ old('monto_operativos', $operativos) }}" step="0.01" min="0" required>
                            <span class="project-error" id="monto_operativos_{{ $proyecto->id_proyecto }}_error"></span>
                        </div>
                        <div class="project-flex-item">
                            <label for="monto_servicios_{{ $proyecto->id_proyecto }}" class="project-label">Monto para Servicios</label>
                            <input type="number" name="monto_servicios" id="monto_servicios_{{ $proyecto->id_proyecto }}"
                                   class="project-input project-number-only"
                                   value="{{ old('monto_servicios', $servicios) }}" step="0.01" min="0" required>
                            <span class="project-error" id="monto_servicios_{{ $proyecto->id_proyecto }}_error"></span>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="project-form-group project-flex">
                        <div class="project-flex-item">
                            <label for="fecha_inicio_{{ $proyecto->id_proyecto }}" class="project-label">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio_{{ $proyecto->id_proyecto }}" class="project-input"
                                   value="{{ old('fecha_inicio', $proyecto->fecha_inicio ? \Carbon\Carbon::parse($proyecto->fecha_inicio)->format('Y-m-d') : '') }}" required>
                            <span class="project-error" id="fecha_inicio_{{ $proyecto->id_proyecto }}_error"></span>
                        </div>
                        <div class="project-flex-item">
                            <label for="fecha_fin_aprox_{{ $proyecto->id_proyecto }}" class="project-label">Fecha de Fin Aproximada</label>
                            <input type="date" name="fecha_fin_aprox" id="fecha_fin_aprox_{{ $proyecto->id_proyecto }}" class="project-input"
                                   value="{{ old('fecha_fin_aprox', $proyecto->fecha_fin_aprox ? \Carbon\Carbon::parse($proyecto->fecha_fin_aprox)->format('Y-m-d') : '') }}">
                            <span class="project-error" id="fecha_fin_aprox_{{ $proyecto->id_proyecto }}_error"></span>
                        </div>
                    </div>
                </div>

                <div class="project-modal-footer" style="position: sticky; bottom: 0; background: white; padding: 10px; border-top: 1px solid #ddd;">
                    <button type="submit" class="project-btn project-btn-primary" id="submit-edit-proyecto-{{ $proyecto->id_proyecto }}">
                        Actualizar
                    </button>
                    <button type="button" class="project-btn project-btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
<!-- Script para manejar el menú contextual, validación y búsqueda -->
<script>
    function toggleContextMenu(menuId) {
        const menu = document.getElementById(menuId);
        const isHidden = menu.classList.contains('hidden');
        document.querySelectorAll('[id^="context-menu-"]').forEach(m => m.classList.add('hidden'));
        if (isHidden) {
            menu.classList.remove('hidden');
        }
    }

    document.addEventListener('click', function(event) {
        if (!event.target.closest('.project-context-btn') && !event.target.closest('.project-dropdown')) {
            document.querySelectorAll('[id^="context-menu-"]').forEach(menu => menu.classList.add('hidden'));
        }
    });

    // Validación de formularios
    document.getElementById('addProyectoForm').addEventListener('submit', function(e) {
        if (!validateProyectoForm('addProyectoForm')) {
            e.preventDefault();
        } else {
            const submitButton = document.getElementById('submit-add-proyecto');
            submitButton.disabled = true;
            submitButton.textContent = 'Guardando...';
        }
    });

    document.querySelectorAll('[id^="editProyectoForm"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateProyectoForm(form.id)) {
                e.preventDefault();
            } else {
                const submitButton = document.getElementById(`submit-edit-proyecto-${form.id.split('editProyectoForm')[1]}`);
                submitButton.disabled = true;
                submitButton.textContent = 'Guardando...';
            }
        });
    });

    function validateProyectoForm(formId) {
        const form = document.getElementById(formId);
        const inputs = {
            nombre_proyecto: {
                pattern: /^[A-Za-z0-9ÁÉÍÓÚáéíóúÑñ.()\s]+$/,
                error: 'Solo se permiten letras, números y espacios'
            },
            cliente_proyecto: {
                pattern: /^[A-Za-z0-9ÁÉÍÓÚáéíóúÑ.ñ()\s]+$/,
                error: 'Solo se permiten letras, números y espacios'
            },
            cantidad_trabajadores: {
                pattern: /^\d+$/,
                error: 'Debe ser un número entero no negativo'
            },
            monto_material: {
                pattern: /^\d+(\.\d{1,2})?$/,
                error: 'Debe ser un número con hasta 2 decimales'
            },
            monto_operativos: {
                pattern: /^\d+(\.\d{1,2})?$/,
                error: 'Debe ser un número con hasta 2 decimales'
            },
            monto_servicios: {
                pattern: /^\d+(\.\d{1,2})?$/,
                error: 'Debe ser un número con hasta 2 decimales'
            },
            fecha_inicio: {
                check: (input) => input.value !== '',
                error: 'Selecciona una fecha'
            }
        };

        let isValid = true;

        Object.keys(inputs).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            // Resolver el elemento de error de forma robusta para add/edit
            const fallbackError = form.querySelector(`#${field}_error`);
            const computedId = `#${field}_${formId.includes('edit') ? formId.split('editProyectoForm')[1] + '_' : ''}error`;
            const errorElement = form.querySelector(computedId) || fallbackError;
            if (errorElement) errorElement.textContent = '';

            if (!input) {
                return; // Campo no existe en este formulario
            }

            if (field === 'fecha_inicio') {
                if (!inputs[field].check(input)) {
                    if (errorElement) errorElement.textContent = inputs[field].error;
                    isValid = false;
                }
            } else {
                    if (!inputs[field].pattern.test(input.value)) {
                    if (errorElement) errorElement.textContent = inputs[field].error;
                    isValid = false;
                }
            }
        });

        return isValid;
    }

    // Restricción de entrada para campos numéricos
    document.querySelectorAll('.project-number-only').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, '');
        });
    });

    // Búsqueda en tiempo real
    document.getElementById('searchProject').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#projectsTableBody tr');
        rows.forEach(row => {
            const projectName = row.cells[0].textContent.toLowerCase();
            row.style.display = projectName.includes(searchTerm) ? '' : 'none';
        });
    });

    // Carga de más proyectos
    let allProjects = @json($proyectos);
    let visibleProjects = allProjects.sort((a, b) => new Date(b.fecha_creacion) - new Date(a.fecha_creacion)).slice(0, 4);
let currentIndex = 4;

function loadMoreProjects() {
    fetch(`/proyectos/load-more?offset=${currentIndex}`)
        .then(response => response.json())
        .then(nextProjects => {
            if (nextProjects.length > 0) {
                nextProjects.forEach(proyecto => {
                    const projectBox = document.createElement('div');
                    projectBox.className = 'project-box bg-white shadow-md rounded-lg overflow-hidden hover:shadow-lg transition-shadow';
                    projectBox.innerHTML = `
                        <a href="${ '{{ route('proyectos.show', ['proyecto' => 'id_placeholder']) }}'.replace('id_placeholder', proyecto.id_proyecto) }" class="project-card-link block p-4 text-gray-800 hover:bg-gray-100">
                            <i class="fas fa-folder project-icon text-yellow-500 mr-2"></i>
                            <span class="project-name text-lg font-medium">${proyecto.nombre_proyecto}</span>
                        </a>
                        <div class="project-context-menu relative">
                            <button class="project-context-btn text-gray-500 hover:text-gray-700 p-2" onclick="toggleContextMenu('context-menu-${proyecto.id_proyecto}')">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="context-menu-${proyecto.id_proyecto}" class="hidden project-dropdown absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg z-10">
                                <a href="${ '{{ route('proyectos.show', ['proyecto' => 'id_placeholder']) }}'.replace('id_placeholder', proyecto.id_proyecto) }" class="project-dropdown-item block px-4 py-2 text-gray-700 hover:bg-gray-100">Ver</a>
                                <a href="#" class="project-dropdown-item block px-4 py-2 text-gray-700 hover:bg-gray-100" data-toggle="modal" data-target="#editProyectoModal${proyecto.id_proyecto}">Editar</a>
                                <form action="${ '{{ route('proyectos.destroy', ['proyecto' => 'id_placeholder']) }}'.replace('id_placeholder', proyecto.id_proyecto) }" method="POST">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="project-dropdown-item project-dropdown-danger block w-full text-left px-4 py-2 text-red-600 hover:bg-red-100" onclick="return confirm('¿Seguro que deseas eliminar este proyecto?')">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    `;
                    document.querySelector('.grid').insertBefore(projectBox, document.getElementById('loadMoreBtn')?.parentElement);
                });
                currentIndex += 5;
                if (nextProjects.length < 5) {
                    document.getElementById('loadMoreBtn').style.display = 'none';
                }
            } else {
                document.getElementById('loadMoreBtn').style.display = 'none';
            }
        })
        .catch(error => console.error('Error al cargar más proyectos:', error));
}
</script>
@endsection