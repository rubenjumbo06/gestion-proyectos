@extends('layouts.app')
@section('title', 'Detalles del Proyecto')

@section('content')
<!-- Tailwind CSS -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
 <!-- ECharts -->
 <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
 <!-- Font Awesome -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
 <!-- FullCalendar -->
 <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
 <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<div class="container mx-auto px-4 py-6 bg-gray-100 min-h-screen">

    <!-- NAV TABS -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="flex border-b border-gray-200 text-sm sm:text-base font-semibold text-gray-600">
            <button class="tab-button flex-1 py-3 text-center border-b-4 border-blue-600 focus:outline-none" data-tab="estadisticas">Estadísticas</button>
            <button class="tab-button flex-1 py-3 text-center border-b-4 border-transparent hover:bg-gray-100" data-tab="materiales-tab">Materiales</button>
            <button class="tab-button flex-1 py-3 text-center border-b-4 border-transparent hover:bg-gray-100" data-tab="planilla">Personal</button>
            <button class="tab-button flex-1 py-3 text-center border-b-4 border-transparent hover:bg-gray-100" data-tab="gastos_extra">Gastos Extras</button>
            <button class="tab-button flex-1 py-3 text-center border-b-4 border-transparent hover:bg-gray-100" data-tab="egresos">Egresos</button>
        </div>

        <!-- Calendario content moved to tabs section below -->
    </div>

    <!-- ENCABEZADO -->
    <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
        <div>
            <h1 class="project-title">{{ $proyecto->nombre_proyecto }}</h1>
            <p class="text-gray-600">Monto Inicial: S/ {{ number_format($proyecto->montopr->monto_inicial ?? 0, 2) }}</p>
            <p class="text-gray-600">Cliente: {{ $proyecto->cliente_proyecto }}</p>
        </div>
       <div class="flex space-x-3 mt-4 sm:mt-0">
            <!-- EDITAR -->
            <!-- <button onclick="openModal('editProyectoModal')" 
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-10 py-4 rounded-full flex items-center">
                <i class="fas fa-edit mr-2"></i> Editar
            </button> -->

            <!-- DETALLES -->
            <!-- <button onclick="openModal('detailsProyectoModal')" 
                class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-4 rounded-full flex items-center">
                <i class="fas fa-info-circle mr-2"></i> Detalles
            </button> -->

            <!-- SOLO EN ESTADÍSTICAS -->
            <button id="refreshChartsBtn" 
                class="hidden bg-green-500 hover:bg-green-600 text-white px-8 py-2 rounded-full items-center">
                <i class="fas fa-sync-alt mr-2"></i> Actualizar Gráficos
            </button>
        </div>
    </div>

    <!-- ESTADÍSTICAS (gráficos) -->
    <div id="estadisticas" class="tab-content">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Materiales -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Materiales</h3>
                <div id="materialesChart" class="w-full h-64 md:h-72"></div>
                <div class="mt-3">
                    <p class="text-gray-600 text-sm font-medium">Proveedores</p>
                    <ul id="materialesLegend" class="text-sm text-gray-700 space-y-1 mt-2"></ul>
                </div>
            </div>

            <!-- Egresos -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Egresos</h3>
                <div id="egresosChart" class="w-full h-64 md:h-72"></div>
            </div>

            <!-- Control de gastos (semi-donut) -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Control de gastos</h3>
                <div id="gastosChart" class="w-full h-64 md:h-72"></div>
            </div>

            <!-- Balance General (arriba, en lugar del calendario) -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Balance General</h3>
                <div id="balanceChart" class="w-full h-64 md:h-72"></div>
            </div>

            <!-- Asistencia (COMENTADA según petición: solo se comenta la parte del gráfico) -->
            <!--
            <div class="bg-white p-4 rounded-lg shadow md:col-span-2">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Asistencia</h3>
                <div id="asistenciaChart" class="w-full h-64 md:h-72"></div>
            </div>
            -->

            <!-- Calendario del Proyecto (abajo, a todo el ancho) -->
            <div class="bg-white p-4 rounded-lg shadow md:col-span-2">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Calendario del Proyecto</h3>
                <div id="calendar" class="w-full" style="min-height: 520px;"></div>
                <div class="mt-4 text-sm text-gray-600">
                    <span class="inline-block w-3 h-3 mr-2 rounded" style="background:#10B981"></span> Rango planificado (verde)
                    <span class="inline-block w-3 h-3 ml-4 mr-2 rounded" style="background:#F59E0B"></span> Días extendidos (amarillo)
                    <span class="inline-block w-3 h-3 ml-4 mr-2 rounded" style="background:#2563EB"></span> Presentes
                    <span class="inline-block w-3 h-3 ml-4 mr-2 rounded" style="background:#EF4444"></span> Ausentes
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Proyecto en SHOW -->
    <div class="modal fade" id="detailsProyectoModal{{ $proyecto->id_proyecto }}" tabindex="-1" role="dialog" aria-labelledby="detailsProyectoModalLabel{{ $proyecto->id_proyecto }}">
        <div class="modal-dialog" role="document">
            <div class="modal-content project-modal">
                <form action="{{ route('proyectos.update', $proyecto) }}" method="POST" id="editProyectoForm{{ $proyecto->id_proyecto }}">
                    @csrf
                    @method('PUT')
                    <div class="project-modal-header">
                        <h4 class="project-modal-title" id="detailsProyectoModalLabel{{ $proyecto->id_proyecto }}">
                            Editar Proyecto: {{ $proyecto->nombre_proyecto }}
                        </h4>
                        <button type="button" class="project-modal-close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="project-modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="project-form-group">
                            <label for="nombre_proyecto_{{ $proyecto->id_proyecto }}" class="project-label">Nombre del Proyecto</label>
                            <input type="text" name="nombre_proyecto" id="nombre_proyecto_{{ $proyecto->id_proyecto }}" class="project-input"
                                value="{{ old('nombre_proyecto', $proyecto->nombre_proyecto) }}" required placeholder="Ingresa el nombre del proyecto">
                            <span class="project-error" id="nombre_proyecto_{{ $proyecto->id_proyecto }}_error"></span>
                        </div>

                        <div class="project-form-group">
                            <label for="cliente_proyecto_{{ $proyecto->id_proyecto }}" class="project-label">Cliente del Proyecto</label>
                            <input type="text" name="cliente_proyecto" id="cliente_proyecto_{{ $proyecto->id_proyecto }}" class="project-input"
                                value="{{ old('cliente_proyecto', $proyecto->cliente_proyecto) }}" required placeholder="Ingresa el cliente del proyecto">
                            <span class="project-error" id="cliente_proyecto_{{ $proyecto->id_proyecto }}_error"></span>
                        </div>

                        <div class="project-form-group">
                            <label for="descripcion_proyecto_{{ $proyecto->id_proyecto }}" class="project-label">Descripción del Proyecto</label>
                            <textarea name="descripcion_proyecto" id="descripcion_proyecto_{{ $proyecto->id_proyecto }}" class="project-input" rows="5"
                                    placeholder="Ingresa una descripción">{{ old('descripcion_proyecto', $proyecto->descripcion_proyecto) }}</textarea>
                            <span class="project-error" id="descripcion_proyecto_{{ $proyecto->id_proyecto }}_error"></span>
                        </div>

                        <div class="project-form-group project-flex">
                            <div class="project-flex-item">
                                <label for="cantidad_trabajadores_{{ $proyecto->id_proyecto }}" class="project-label">Cantidad de Trabajadores</label>
                                <input type="number" name="cantidad_trabajadores" id="cantidad_trabajadores_{{ $proyecto->id_proyecto }}" class="project-input project-number-only"
                                    value="{{ old('cantidad_trabajadores', $proyecto->cantidad_trabajadores) }}" min="0" required placeholder="Ingresa la cantidad">
                                <span class="project-error" id="cantidad_trabajadores_{{ $proyecto->id_proyecto }}_error"></span>
                            </div>
                            <div class="project-flex-item">
                                <label for="sueldo_{{ $proyecto->id_proyecto }}" class="project-label">Sueldo</label>
                                <input type="number" name="sueldo" id="sueldo_{{ $proyecto->id_proyecto }}" class="project-input project-number-only"
                                    value="{{ old('sueldo', $proyecto->sueldo) }}" step="0.01" min="0" required placeholder="Ingresa el sueldo">
                                <span class="project-error" id="sueldo_{{ $proyecto->id_proyecto }}_error"></span>
                            </div>
                        </div>

                        <div class="project-form-group">
                            <label for="monto_{{ $proyecto->id_proyecto }}" class="project-label">Monto</label>
                            <input type="number" name="monto" id="monto_{{ $proyecto->id_proyecto }}" class="project-input project-number-only"
                                value="{{ old('monto', $proyecto->montopr->monto_inicial ?? 0) }}" step="0.01" min="0" required placeholder="Ingresa el monto">
                            <span class="project-error" id="monto_{{ $proyecto->id_proyecto }}_error"></span>
                        </div>

                        <div class="project-form-group project-flex">
                            <div class="project-flex-item">
                                <label for="fecha_inicio_{{ $proyecto->id_proyecto }}" class="project-label">Fecha de Inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio_{{ $proyecto->id_proyecto }}" class="project-input"
                                    value="{{ old('fecha_inicio', $proyecto->fechapr->fecha_inicio ?? '') }}" required>
                                <span class="project-error" id="fecha_inicio_{{ $proyecto->id_proyecto }}_error"></span>
                            </div>
                            <div class="project-flex-item">
                                <label for="fecha_fin_aprox_{{ $proyecto->id_proyecto }}" class="project-label">Fecha de Fin Aproximada</label>
                                <input type="date" name="fecha_fin_aprox" id="fecha_fin_aprox_{{ $proyecto->id_proyecto }}" class="project-input"
                                    value="{{ old('fecha_fin_aprox', $proyecto->fechapr->fecha_fin_aprox ?? '') }}">
                                <span class="project-error" id="fecha_fin_aprox_{{ $proyecto->id_proyecto }}_error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="project-modal-footer" style="position: sticky; bottom: 0; background: white; padding: 10px; border-top: 1px solid #ddd;">
                        <button type="submit" class="project-btn project-btn-primary" id="submit-edit-proyecto-{{ $proyecto->id_proyecto }}">Actualizar</button>
                        <button type="button" class="project-btn project-btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- OTRAS PESTAÑAS (contenido ya existente) -->
    <div id="materiales-tab" class="tab-content hidden">
        @include('admin.proyectos.materiales', ['proyecto' => $proyecto, 'materiales' => $materiales])
    </div>
    <div id="planilla" class="tab-content hidden">
        @include('admin.proyectos.planilla', ['proyecto' => $proyecto])
    </div>
    <div id="gastos_extra" class="tab-content hidden">
        @include('admin.proyectos.gastos_extra', ['proyecto' => $proyecto, 'gastosExtra' => $gastosExtra])
    </div>
    <div id="egresos" class="tab-content hidden">
        @include('admin.proyectos.egresos', ['proyecto' => $proyecto])
    </div>
</div>

<!-- Modal Detalle del Día del Calendario -->
<div id="calendarDayModal" class="fixed inset-0 bg-white bg-opacity-60 hidden z-50 flex items-start md:items-center justify-center p-4" style="backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px);">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl md:max-w-4xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
            <h3 class="text-xl font-semibold text-gray-800">Detalle de asistencia - <span id="dayDetailsDate" class="font-normal text-gray-600"></span></h3>
            <button class="text-gray-500 hover:text-gray-700" onclick="closeModal('calendarDayModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="dayDetailsCounts" class="flex flex-wrap gap-2 text-sm mb-4">
            <span id="badgePresentes" class="inline-flex items-center px-2 py-1 rounded bg-blue-50 text-blue-700 font-medium"></span>
            <span id="badgeAusentes" class="inline-flex items-center px-2 py-1 rounded bg-red-50 text-red-700 font-medium"></span>
            <span id="badgeTotal" class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-700 font-medium"></span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-green-700 mb-2">Presentes</h4>
                <ul id="dayDetailsPresentesList" class="text-sm text-gray-800 divide-y divide-gray-100 max-h-64 overflow-auto pr-1"></ul>
            </div>
            <div>
                <h4 class="font-semibold text-red-700 mb-2">Ausentes</h4>
                <ul id="dayDetailsAusentesList" class="text-sm text-gray-800 divide-y divide-gray-100 max-h-64 overflow-auto pr-1"></ul>
            </div>
        </div>
        <div class="mt-6 text-right">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" onclick="closeModal('calendarDayModal')">Cerrar</button>
        </div>
    </div>
    <script>
        async function openDayDetails(dateStr) {
            const baseUrl = `/api/proyectos/{{ $proyecto->id_proyecto }}`;
            try {
                const res = await fetch(`${baseUrl}/calendar/day/${dateStr}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error(await res.text());
                const payload = await res.json();

                document.getElementById('dayDetailsDate').textContent = payload.date;
                const counts = payload.counts || { presentes: 0, ausentes: 0, total: 0 };
                document.getElementById('badgePresentes').textContent = `Presentes: ${counts.presentes}`;
                document.getElementById('badgeAusentes').textContent = `Ausentes: ${counts.ausentes}`;
                document.getElementById('badgeTotal').textContent = `Total: ${counts.total}`;

                const presUL = document.getElementById('dayDetailsPresentesList');
                const ausUL  = document.getElementById('dayDetailsAusentesList');
                function initialsOf(p){
                    const n = (p.nombre || '').trim();
                    const a = (p.apellido || '').trim();
                    const dn = n ? n[0] : '';
                    const da = a ? a[0] : '';
                    const fromDni = (p.dni || '').toString();
                    const fallback = fromDni ? fromDni.slice(-2) : '?';
                    const init = (dn + da) || fallback;
                    return init.toUpperCase();
                }
                function personItem(p, present){
                    const initials = initialsOf(p);
                    const name = `${p.nombre || ''} ${p.apellido || ''}`.trim() || 'Sin nombre';
                    const dni  = p.dni ? `DNI: ${p.dni}` : '';
                    const badge = present ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                    return `<li class="py-2">
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full ${badge} font-semibold">${initials}</span>
                            <div>
                                <div class="font-medium text-gray-800">${name}</div>
                                <div class="text-xs text-gray-500">${dni}</div>
                            </div>
                        </div>
                    </li>`;
                }
                presUL.innerHTML = (payload.presentes || []).map(p => personItem(p, true)).join('') || '<li class="py-2 text-gray-500">Sin registros</li>';
                ausUL.innerHTML  = (payload.ausentes  || []).map(p => personItem(p, false)).join('') || '<li class="py-2 text-gray-500">Sin registros</li>';

                openModal('calendarDayModal');
            } catch (e) {
                console.error('Error cargando detalle del día', e);
                alert('No fue posible cargar el detalle del día');
            }
        }
        // Cerrar al hacer clic fuera del contenido
        const overlayEl = document.getElementById('calendarDayModal');
        overlayEl.addEventListener('click', (e) => {
            if (e.target === overlayEl) closeModal('calendarDayModal');
        });
        // Cerrar con tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('calendarDayModal');
                if (!modal.classList.contains('hidden')) closeModal('calendarDayModal');
            }
        });
    </script>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const proyectoId = {{ $proyecto->id_proyecto }};
        // ---------- Tabs ----------
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        // Al cargar la vista, si Estadísticas ya está visible, mostrar botón
        const refreshBtn = document.getElementById('refreshChartsBtn');
        if (document.getElementById('estadisticas') && 
            !document.getElementById('estadisticas').classList.contains('hidden')) {
            refreshBtn.classList.remove('hidden');
        }
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                tabButtons.forEach(b => b.classList.remove('border-blue-600'));
                tabContents.forEach(c => c.classList.add('hidden'));
                btn.classList.add('border-blue-600');
                document.getElementById(btn.dataset.tab).classList.remove('hidden');

                // 🔹 Mostrar/ocultar el botón de actualizar gráficos
                const refreshBtn = document.getElementById('refreshChartsBtn');
                if (btn.dataset.tab === 'estadisticas') {
                    refreshBtn.classList.remove('hidden');
                } else {
                    refreshBtn.classList.add('hidden');
                }

                if (btn.dataset.tab === 'estadisticas') {
                    [materialesChart, egresosChart, gastosChart, balanceChart].forEach(ch => ch && ch.resize());
                    if (calendarInstance) calendarInstance.updateSize();
                }
            });
        });

        // ---------- ECharts Instances ----------
        const materialesChart = echarts.init(document.getElementById('materialesChart'));
        const egresosChart    = echarts.init(document.getElementById('egresosChart'));
        const gastosChart     = echarts.init(document.getElementById('gastosChart'));
        // Nota: Asistencia está comentada (no se inicializa)
        // const asistenciaChart = echarts.init(document.getElementById('asistenciaChart'));
        const balanceChart    = echarts.init(document.getElementById('balanceChart'));
        // ---------- FullCalendar Instance ----------
        let calendarInstance = null;

        // ---------- Default Options (fallback) ----------
        const emptyBar = {
            title: { text: 'Sin datos', left: 'center' },
            xAxis: { type: 'category', data: [] },
            yAxis: { type: 'value' },
            series: [{ type: 'bar', data: [] }]
        };
        const emptyPie = {
            title: { text: 'Sin datos', left: 'center' },
            series: [{ type: 'pie', data: [{ value: 1, name: 'Sin datos' }]}]
        };
        const emptyLine = {
            title: { text: 'Sin datos', left: 'center' },
            xAxis: { type: 'category', data: [] },
            yAxis: { type: 'value' },
            series: [{ type: 'line', data: [] }]
        };

        materialesChart.setOption(emptyBar);
        egresosChart.setOption(emptyPie);
        gastosChart.setOption(emptyPie);
        // asistenciaChart.setOption(emptyLine); // Asistencia comentada
        balanceChart.setOption(emptyBar);

        // ---------- Helpers ----------
        const token   = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const baseUrl = `/api/proyectos/${proyectoId}`;

        const headers = { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token };

        const showLoading = (chart) => chart.showLoading('default', { text: 'Cargando…' });
        const hideLoading = (chart) => chart.hideLoading();

        const safe = (v, def) => (v === undefined || v === null) ? def : v;

        function normalizeLabelsData(payload) {
            if (Array.isArray(payload)) {
                const labels = payload.map(x => x.label ?? x.categoria ?? x.proveedor ?? 'Item');
                const data   = payload.map(x => x.value ?? x.total ?? x.cantidad ?? 0);
                return { labels, data };
            }
            if (payload && Array.isArray(payload.labels) && Array.isArray(payload.data)) {
                return { labels: payload.labels, data: payload.data };
            }
            return { labels: [], data: [] };
        }

        function palette(i) {
            const colors = ['#3B82F6','#2563EB','#1E40AF','#F59E0B','#EF4444','#10B981','#6366F1','#14B8A6'];
            return colors[i % colors.length];
        }

        async function getJSON(url) {
            try {
                const res = await fetch(url, { headers });
                if (!res.ok) throw new Error(`${res.status} ${res.statusText} - ${await res.text()}`);
                return res.json();
            } catch (error) {
                console.error(`Error fetching ${url}:`, error);
                return null;
            }
        }

        window.removeTrabajador = function(planillaId) {
            if (confirm('¿Estás seguro de que deseas eliminar este trabajador de la planilla?')) {
                fetch(`/admin/proyectos/${proyectoId}/remove-planilla/${planillaId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Trabajador eliminado correctamente.');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'No se pudo eliminar el trabajador.'));
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        };

        function buildMaterialesOption(payload) {
            const { labels, data } = normalizeLabelsData(payload);
            return {
                tooltip: { trigger: 'axis' },
                grid: { left: 40, right: 20, top: 20, bottom: 30 },
                xAxis: { type: 'category', data: labels },
                yAxis: { type: 'value', name: 'Cantidad' },
                series: [{
                    name: 'Materiales',
                    type: 'bar',
                    data: data,
                    itemStyle: {
                        color: (p) => palette(p.dataIndex)
                    }
                }]
            };
        }

        function buildEgresosOption(payload) {
            const { labels, data } = normalizeLabelsData(payload);
            return {
                tooltip: { trigger: 'item', formatter: '{b}: S/{c} ({d}%)' },
                series: [{
                    name: 'Egresos',
                    type: 'pie',
                    radius: ['45%','70%'],
                    data: labels.map((l, i) => ({ name: l, value: safe(data[i], 0), itemStyle: { color: palette(i) } })),
                    emphasis: { itemStyle: { shadowBlur: 10, shadowColor: 'rgba(0,0,0,0.2)' } }
                }]
            };
        }

        function buildGastosDonutOption(payload) {
            const { labels, data } = normalizeLabelsData(payload);
            const total = data.reduce((a, b) => a + (Number(b) || 0), 0);
            const datos = labels.map((l, i) => {
                let c = palette(i);
                const lower = (l || '').toString().toLowerCase();
                // Colores fijos por categoría típica (opcional)
                if (lower.includes('aliment')) c = '#34D399';
                if (lower.includes('hosped'))  c = '#60A5FA';
                if (lower.includes('pasaj') || lower.includes('pasajes')) c = '#F59E0B';
                return { name: l, value: safe(data[i], 0), itemStyle: { color: c } };
            });
            return {
                tooltip: { trigger: 'item', formatter: '{b}: S/{c} ({d}%)' },
                series: [{
                    type: 'pie',
                    radius: ['45%','70%'],
                    center: ['50%', '50%'],
                    avoidLabelOverlap: true,
                    label: { show: true, formatter: '{b}' },
                    labelLine: { show: true },
                    data: datos
                }]
            };
        }

        // Mantengo la función buildAsistenciaOption por si en el futuro quieres reactivar el gráfico,
        // pero NO se inicializa ni se usa en updateAll (la parte de Asistencia fue solicitada como comentada).
        function buildAsistenciaOption(payload) {
            const labels = Array.isArray(payload?.labels) ? payload.labels : [];
            const datasets = Array.isArray(payload?.datasets) ? payload.datasets : [];
            const series = datasets.map((ds, i) => ({
                name: ds.label ?? `Serie ${i+1}`,
                type: 'line',
                smooth: true,
                data: Array.isArray(ds.data) ? ds.data : [],
                itemStyle: { color: i === 0 ? '#10B981' : palette(i+1) }
            }));
            return {
                tooltip: { trigger: 'axis' },
                legend: { bottom: 0 },
                grid: { left: 40, right: 20, top: 20, bottom: 40 },
                xAxis: { type: 'category', data: labels },
                yAxis: { type: 'value', name: 'Trabajadores' },
                series
            };
        }

        // Construye el gráfico de Balance General (servicios, egresos, ganancia_neta)
        function buildBalanceOption(payload) {
            const labels = ['Servicios', 'Egresos', 'Ganancia Neta'];
            const data   = [
                Number(safe(payload?.total_servicios, 0)),
                Number(safe(payload?.egresos, 0)),
                Number(safe(payload?.ganancia_neta, 0))
            ];

            return {
                tooltip: { trigger: 'axis' },
                xAxis: { type: 'category', data: labels },
                yAxis: { type: 'value', name: 'S/' },
                series: [{
                    name: 'Balance',
                    type: 'line',
                    areaStyle: {},
                    smooth: true,
                    data: data,
                    lineStyle: { color: '#3B82F6' },
                    itemStyle: { color: '#3B82F6' }
                }]
            };
        }

        async function updateAll() {
            const btn = document.getElementById('refreshChartsBtn');
            console.log('Iniciando actualización de gráficos para proyectoId:', proyectoId);
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Actualizando…';

            // Mostrar loader en charts (sin asistencia porque está comentada)
            [materialesChart, egresosChart, gastosChart, balanceChart].forEach(showLoading);

            try {
                const [mat, egr, gas, /* asis, */ bal] = await Promise.all([
                    getJSON(`${baseUrl}/materiales`).catch((e) => { console.error('Materiales error:', e); return null; }),
                    getJSON(`${baseUrl}/egresos`).catch((e) => { console.error('Egresos error:', e); return null; }),
                    getJSON(`${baseUrl}/gastos`).catch((e) => { console.error('Gastos error:', e); return null; }),
                    // getJSON(`${baseUrl}/asistencia`).catch((e) => { console.error('Asistencia error:', e); return null; }), // Asistencia comentada
                    getJSON(`${baseUrl}/balance`).catch((e) => { console.error('Balance error:', e); return null; }),
                ]);

                console.log('Datos recibidos:', { mat, egr, gas, /* asis, */ bal });

                if (mat) {
                    materialesChart.setOption(buildMaterialesOption(mat), true);
                    const { labels } = normalizeLabelsData(mat);
                    const legend = document.getElementById('materialesLegend');
                    legend.innerHTML = '';
                    labels.forEach((l, i) => {
                        const li = document.createElement('li');
                        li.innerHTML = `<span class="inline-block w-3 h-3 mr-2 rounded" style="background:${palette(i)}"></span> ${l}`;
                        legend.appendChild(li);
                    });
                } else {
                    materialesChart.setOption(emptyBar, true);
                }

                if (egr) egresosChart.setOption(buildEgresosOption(egr), true);
                else egresosChart.setOption(emptyPie, true);

                if (gas) gastosChart.setOption(buildGastosDonutOption(gas), true);
                else gastosChart.setOption(emptyPie, true);

                // Asistencia está comentada: si quieres reactivarla en el futuro, descomenta la llamada arriba
                // and add: asistenciaChart.setOption(buildAsistenciaOption(asis), true);

                // Balance General: nuevo gráfico de barras
                if (bal) balanceChart.setOption(buildBalanceOption(bal), true);
                else balanceChart.setOption(emptyBar, true);

                setTimeout(() => {
                    [materialesChart, egresosChart, gastosChart, /* asistenciaChart, */ balanceChart].forEach(ch => ch.resize());
                }, 50);

            } finally {
                [materialesChart, egresosChart, gastosChart, /* asistenciaChart, */ balanceChart].forEach(hideLoading);
                btn.disabled = false; btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Actualizar Gráficos';
            }
        }

        updateAll();
        // Inicializar el calendario dentro de Estadísticas
        initCalendarIfNeeded();
        loadCalendarDataAndRender();
        document.getElementById('refreshChartsBtn').addEventListener('click', () => {
            updateAll();
            loadCalendarDataAndRender();
        });

        let rId = null;
        window.addEventListener('resize', () => {
            if (rId) cancelAnimationFrame(rId);
            rId = requestAnimationFrame(() => {
                [materialesChart, egresosChart, gastosChart, /* asistenciaChart, */ balanceChart].forEach(ch => ch.resize());
            });
        });

        // ---------- Funciones Modales ----------
        window.openModal = function(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
        }

        window.closeModal = function(id) {
            const modal = document.getElementById(id);
            modal.classList.add('hidden');
        }

        // ---------- Calendario del Proyecto ----------
        function addOneDay(dateStr) {
            const d = new Date(dateStr + 'T00:00:00');
            d.setDate(d.getDate() + 1);
            return d.toISOString().slice(0,10);
        }

        async function loadCalendarDataAndRender() {
            try {
                const data = await getJSON(`${baseUrl}/calendar`);
                if (!data) return;

                const events = [];

                // Rango de proyecto: verde (planificado) y amarillo (extensión/resto)
                const start = data.start;
                const plannedEnd = data.planned_end;
                const trueEnd = data.true_end;
                const visualEnd = data.visual_end || data.end || trueEnd || plannedEnd || start;

                if (start && plannedEnd) {
                    events.push({
                        start: start,
                        end: addOneDay(plannedEnd), // FullCalendar end exclusivo
                        display: 'background',
                        backgroundColor: 'rgba(16,185,129,0.22)',
                        borderColor: 'transparent'
                    });
                }

                // Amarillo: del día siguiente a plannedEnd hasta visualEnd, si aplica
                if (start && plannedEnd) {
                    const pe = new Date(plannedEnd);
                    const ve = new Date(visualEnd);
                    if (ve > pe) {
                        events.push({
                            start: addOneDay(plannedEnd),
                            end: addOneDay(visualEnd),
                            display: 'background',
                            backgroundColor: 'rgba(245,158,11,0.25)',
                            borderColor: 'transparent'
                        });
                    }
                }

                const total = Number(data.total_trabajadores || 0);
                (data.days || []).forEach(d => {
                    const presentes = d.presentes != null ? Number(d.presentes) : 0;
                    const ausentes = (d.ausentes === null || d.ausentes === undefined) ? null : Number(d.ausentes);
                    if (presentes > 0) {
                        events.push({ title: `Presentes: ${presentes}` , start: d.fecha, allDay: true, color: '#2563EB' });
                    }
                    if (ausentes !== null && ausentes > 0 && total > 0) {
                        events.push({ title: `Ausentes: ${ausentes}` , start: d.fecha, allDay: true, color: '#EF4444' });
                    }
                });

                // Limitar navegación y centrar en inicio (hasta visual_end)
                if (calendarInstance && start) {
                    const endForRange = visualEnd || start;
                    calendarInstance.setOption('validRange', { start: start, end: addOneDay(endForRange) });
                    calendarInstance.setOption('initialDate', start);
                }

                if (calendarInstance) {
                    calendarInstance.removeAllEvents();
                    calendarInstance.addEventSource(events);
                }
            } catch (e) {
                console.error('Error cargando calendario:', e);
            }
        }

        async function initCalendarIfNeeded() {
            if (calendarInstance) {
                calendarInstance.updateSize();
                return;
            }
            const el = document.getElementById('calendar');
            if (!el) return;
            calendarInstance = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                locale: 'es',
                height: 520,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                dayMaxEventRows: true,
                nowIndicator: true,
                dateClick: function(info) {
                    openDayDetails(info.dateStr);
                },
                eventClick: function(info) {
                    if (info?.event?.startStr) {
                        openDayDetails(info.event.startStr.slice(0,10));
                    }
                }
            });
            await loadCalendarDataAndRender();
            calendarInstance.render();
        }

        window.addEventListener('resize', () => {
            if (calendarInstance) {
                calendarInstance.updateSize();
            }
        });
    });
</script>
@endsection
