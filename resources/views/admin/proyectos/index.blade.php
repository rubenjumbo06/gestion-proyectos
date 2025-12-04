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
        <li><a href="{{ url()->withTabToken(route('dashboard')) }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
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
    <!-- Cuadrícula de proyectos (los 4 más recientes) -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 p-4">
    @forelse ($allProyectos->sortByDesc('fecha_creacion')->take(4) as $proyecto)
        <div class="relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-200 overflow-visible group">
            
            <!-- Área clicleable (solo el contenido principal) -->
            <a href="{{ url()->withTabToken(route('proyectos.show', $proyecto)) }}" 
               class="block p-6 cursor-pointer">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 mt-1">
                        <i class="fas fa-folder text-yellow-500 text-5xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xl font-semibold text-gray-800 truncate" title="{{ $proyecto->nombre_proyecto }}">
                            {{ $proyecto->nombre_proyecto }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $proyecto->cliente_proyecto }}</p>
                        <p class="text-xs text-gray-400 mt-3">
                            Creado: {{ $proyecto->fecha_creacion?->format('d/m/Y') ?? 'Sin fecha' }}
                        </p>
                    </div>
                </div>
            </a>

            <!-- Botón de 3 puntos (FUERA del enlace) -->
            <div class="absolute top-4 right-4 z-10">
                <button 
                    type="button"
                    onclick="toggleMenu(event, 'menu-{{ $proyecto->id_proyecto }}')"
                    class="p-3 rounded-full bg-white shadow-md hover:bg-gray-100 transition-all opacity-0 group-hover:opacity-100 focus:opacity-100">
                    <i class="fas fa-ellipsis-v text-gray-600"></i>
                </button>

                <!-- Dropdown (sale por fuera de la tarjeta) -->
                <div id="menu-{{ $proyecto->id_proyecto }}" 
                     class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-200 hidden z-50 overflow-hidden">
                    <div class="py-2">
                        <a href="{{ url()->withTabToken(route('proyectos.show', $proyecto)) }}" 
                           class="flex items-center px-5 py-3 text-gray-700 hover:bg-gray-50 transition">
                            <i class="fas fa-eye mr-3 text-blue-600"></i>
                            Ver proyecto
                        </a>
                        <button type="button"
        data-toggle="modal" 
        data-target="#editProyectoModal{{ $proyecto->id_proyecto }}"
        class="w-full text-left flex items-center px-5 py-3 text-gray-700 hover:bg-gray-50 transition">
    <i class="fas fa-edit mr-3 text-amber-600"></i>
    Editar
</button>
                        <div class="border-t border-gray-200 my-1"></div>
                        <form action="{{ route('proyectos.destroy', $proyecto) }}" method="POST" class="m-0">
                            @csrf @method('DELETE')
                            <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
                            <button type="submit" 
                                    onclick="return confirm('¿Seguro que quieres eliminar este proyecto?')"
                                    class="w-full flex items-center px-5 py-3 text-red-600 hover:bg-red-50 transition">
                                <i class="fas fa-trash-alt mr-3"></i>
                                Eliminar proyecto
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-16">
            <i class="fas fa-folder-open text-8xl text-gray-200 mb-6"></i>
            <p class="text-xl text-gray-500">Aún no hay proyectos creados</p>
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
                                <td class="px-6 py-3 border"><a href="{{ url()->withTabToken(route('proyectos.show', $proyecto)) }}" class="text-blue-600 hover:underline">{{ $proyecto->nombre_proyecto }}</a></td>
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
                <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
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
                <input type="hidden" name="t" value="{{ session('tab_token_' . auth()->id()) }}">
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
    function toggleMenu(e, menuId) {
    e.stopPropagation();
    const menu = document.getElementById(menuId);
    const isHidden = menu.classList.contains('hidden');

    // Cerrar todos
    document.querySelectorAll('[id^="menu-"]').forEach(m => {
        if (m.id !== menuId) m.classList.add('hidden');
    });

    // Toggle actual
    menu.classList.toggle('hidden');
}

// Cerrar menús al hacer click fuera
document.addEventListener('click', function(e) {
    const isClickInsideMenu = e.target.closest('[id^="menu-"]');
    const isClickOnButton = e.target.closest('button[onclick*="toggleMenu"]');

    if (!isClickInsideMenu && !isClickOnButton) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});
function openEditModal(proyectoId) {
    const modal = document.getElementById('editProyectoModal' + proyectoId);
    if (modal) {
        // Forzar que Bootstrap lo abra correctamente
        $(modal).modal('show');
        
        // Cerrar cualquier menú dropdown abierto
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
}

// === FUNCIÓN PARA EL MENÚ DE 3 PUNTOS (corregido y simplificado) ===
function toggleMenu(e, menuId) {
    e.stopPropagation();

    const menu = document.getElementById(menuId);
    const isHidden = menu.classList.contains('hidden');

    // Cerrar todos los menús
    document.querySelectorAll('[id^="menu-"]').forEach(m => {
        if (m.id !== menuId) m.classList.add('hidden');
    });

    // Toggle del actual
    menu.classList.toggle('hidden');
}

// Cerrar menús al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('button[onclick^="toggleMenu"]') && 
        !e.target.closest('[id^="menu-"]')) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

// === BÚSQUEDA EN TIEMPO REAL (tabla) ===
document.getElementById('searchProject')?.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#projectsTableBody tr').forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        row.style.display = name.includes(term) ? '' : 'none';
    });
});

// === VALIDACIÓN DE FORMULARIOS (agregar y editar) ===
function validateProyectoForm(formId) {
    const form = document.getElementById(formId);
    let isValid = true;

    const fields = [
        { name: 'nombre_proyecto', regex: /^[A-Za-z0-9ÁÉÍÓÚáéíóúÑñ.()\s]+$/, msg: 'Solo letras, números y espacios' },
        { name: 'cliente_proyecto', regex: /^[A-Za-z0-9ÁÉÍÓÚáéíóúÑñ.()\s]+$/, msg: 'Solo letras, números y espacios' },
        { name: 'cantidad_trabajadores', regex: /^\d+$/, msg: 'Número entero positivo' },
        { name: 'monto_material', regex: /^\d+(\.\d{1,2})?$/, msg: 'Número con hasta 2 decimales' },
        { name: 'monto_operativos', regex: /^\d+(\.\d{1,2})?$/, msg: 'Número con hasta 2 decimales' },
        { name: 'monto_servicios', regex: /^\d+(\.\d{1,2})?$/, msg: 'Número con hasta 2 decimales' },
    ];

    fields.forEach(field => {
        const input = form.querySelector(`[name="${field.name}"]`);
        const errorEl = form.querySelector(`#${field.name}_error`) || 
                        form.querySelector(`#${field.name}_${formId.match(/\d+/)?.[0] || ''}_error`);

        if (input) {
            if (input.hasAttribute('required') && !input.value.trim()) {
                if (errorEl) errorEl.textContent = 'Este campo es obligatorio';
                isValid = false;
            } else if (input.value && !field.regex.test(input.value.trim())) {
                if (errorEl) errorEl.textContent = field.msg;
                isValid = false;
            } else {
                if (errorEl) errorEl.textContent = '';
            }
        }
    });

    return isValid;
}

// Aplicar validación a todos los formularios
document.getElementById('addProyectoForm')?.addEventListener('submit', function(e) {
    if (!validateProyectoForm('addProyectoForm')) {
        e.preventDefault();
    } else {
        const btn = this.querySelector('#submit-add-proyecto');
        btn.disabled = true;
        btn.textContent = 'Guardando...';
    }
});

document.querySelectorAll('[id^="editProyectoForm"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!validateProyectoForm(this.id)) {
            e.preventDefault();
        } else {
            const id = this.id.replace('editProyectoForm', '');
            const btn = document.getElementById('submit-edit-proyecto-' + id);
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Actualizando...';
            }
        }
    });
});

// === RESTRICCIÓN DE NÚMEROS EN INPUTS ===
document.querySelectorAll('.project-number-only').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
        // Permitir solo un punto
        const parts = this.value.split('.');
        if (parts.length > 2) {
            this.value = parts[0] + '.' + parts.slice(1).join('');
        }
    });
});
</script>
@endsection