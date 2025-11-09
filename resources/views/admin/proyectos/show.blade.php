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
    </div>

    <!-- ENCABEZADO -->
    <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
        <div>
            <h1 class="project-title">{{ $proyecto->nombre_proyecto }}</h1>
            <p class="text-gray-600">Monto Inicial: S/ {{ number_format($proyecto->montopr->monto_inicial ?? 0, 2) }}</p>
            <p class="text-gray-600">Cliente: {{ $proyecto->cliente_proyecto }}</p>
        </div>
        <div class="flex space-x-3 mt-4 sm:mt-0">
            <button id="refreshChartsBtn" class="hidden bg-green-500 hover:bg-green-600 text-white px-8 py-2 rounded-full flex items-center">
                <i class="fas fa-sync-alt mr-2"></i> Actualizar Gráficos
            </button>
            <a href="{{ route('proyectos.actividades.index', ['proyecto' => $proyecto->id_proyecto]) }}" 
               class="bg-purple-500 hover:bg-purple-600 text-white px-8 py-2 rounded-full flex items-center">
                <i class="fas fa-tasks mr-2"></i> Ver Actividades
            </a>
        </div>
    </div>

    <!-- ESTADÍSTICAS -->
    <div id="estadisticas" class="tab-content">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Materiales (SIN LEYENDA "Proveedores") -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Materiales</h3>
                <div id="materialesChart" class="w-full h-80 md:h-72"></div>
                <!-- Leyenda manual con colores -->
                <ul id="materialesLegend" class="text-sm text-gray-700 space-y-1 mt-2"></ul>
            </div>

            <!-- Egresos (LEYENDA ARRIBA, NO CHOCA) -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Egresos</h3>
                <div id="egresosChart" class="w-full" style="height: 250px;"></div>
            </div>

            <!-- Control de gastos -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Control de gastos</h3>
                <div id="gastosChart" class="w-full" style="height: 250px;"></div>
            </div>

            <!-- Balance General - TAMAÑO IDEAL 300px -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-2 text-center">Balance General</h3>
                <div id="balanceChart" class="w-full" style="height: 300px;"></div>
            </div>

            <!-- Calendario -->
            <div class="bg-white p-4 rounded-lg shadow md:col-span-2">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Calendario del Proyecto</h3>
                <div id="calendar" class="w-full h-64"></div>
                <div class="mt-4 text-sm text-gray-600">
                    <span class="inline-block w-3 h-3 mr-2 rounded" style="background:#10B981"></span> Rango planificado (verde)
                    <span class="inline-block w-3 h-3 ml-4 mr-2 rounded" style="background:#F59E0B"></span> Días extendidos (amarillo)
                    <span class="inline-block w-3 h-3 ml-4 mr-2 rounded" style="background:#2563EB"></span> Presentes
                    <span class="inline-block w-3 h-3 ml-4 mr-2 rounded" style="background:#EF4444"></span> Ausentes
                </div>
            </div>
        </div>
    </div>

    <!-- OTRAS PESTAÑAS -->
    <div id="materiales-tab" class="tab-content hidden">
        @include('admin.proyectos.materiales', [
            'proyecto' => $proyecto,
            'materiales' => $materiales,
            'budgetMaterials' => $budgetMaterials ?? null,
        ])
    </div>
    <div id="planilla" class="tab-content hidden">
        @include('admin.proyectos.planilla', [
            'proyecto' => $proyecto,
            'budgetPersonal' => $budgetPersonal ?? null,
            'trabajadoresPreload' => $trabajadoresPreload ?? collect(),
        ])
    </div>
    <div id="gastos_extra" class="tab-content hidden">
        @include('admin.proyectos.gastos_extra', [
            'proyecto' => $proyecto,
            'gastosExtra' => $gastosExtra,
            'budgetServicios' => $budgetServicios ?? null,
        ])
    </div>
    <div id="egresos" class="tab-content hidden">
        @include('admin.proyectos.egresos', ['proyecto' => $proyecto])
    </div>
</div>

<!-- Modal Detalle del Día -->
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const proyectoId = {{ $proyecto->id_proyecto }};
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        const refreshBtn = document.getElementById('refreshChartsBtn');

        if (!document.getElementById('estadisticas').classList.contains('hidden')) {
            refreshBtn.classList.remove('hidden');
        }

        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                tabButtons.forEach(b => b.classList.remove('border-blue-600'));
                tabContents.forEach(c => c.classList.add('hidden'));
                btn.classList.add('border-blue-600');
                document.getElementById(btn.dataset.tab).classList.remove('hidden');
                refreshBtn.classList.toggle('hidden', btn.dataset.tab !== 'estadisticas');
                if (btn.dataset.tab === 'estadisticas') {
                    [materialesChart, egresosChart, gastosChart, balanceChart].forEach(ch => ch?.resize());
                    calendarInstance?.updateSize();
                }
            });
        });

        // === ECHARTS ===
        const materialesChart = echarts.init(document.getElementById('materialesChart'));
        const egresosChart    = echarts.init(document.getElementById('egresosChart'));
        const gastosChart     = echarts.init(document.getElementById('gastosChart'));
        const balanceChart    = echarts.init(document.getElementById('balanceChart'));
        let calendarInstance = null;

        const emptyBar = { title: { text: 'Sin datos', left: 'center' }, xAxis: { type: 'category', data: [] }, yAxis: { type: 'value' }, series: [{ type: 'bar', data: [] }] };
        const emptyPie = { title: { text: 'Sin datos', left: 'center' }, series: [{ type: 'pie', data: [{ value: 1, name: 'Sin datos' }]}] };

        materialesChart.setOption(emptyBar);
        egresosChart.setOption(emptyPie);
        gastosChart.setOption(emptyPie);
        balanceChart.setOption(emptyBar);

        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const baseUrl = `/api/proyectos/${proyectoId}`;
        const headers = { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token };

        const showLoading = (chart) => chart.showLoading();
        const hideLoading = (chart) => chart.hideLoading();
        const safe = (v, def) => (v === undefined || v === null) ? def : v;

        function palette(i) {
            const colors = ['#3B82F6','#2563EB','#1E40AF','#F59E0B','#EF4444','#10B981','#6366F1','#14B8A6'];
            return colors[i % colors.length];
        }

        async function getJSON(url) {
            try {
                const res = await fetch(url, { headers });
                if (!res.ok) throw new Error(await res.text());
                return await res.json();
            } catch (e) {
                console.error('Error:', e);
                return null;
            }
        }

        function normalizeLabelsData(payload) {
            if (Array.isArray(payload)) {
                const labels = payload.map(x => x.label ?? x.categoria ?? x.proveedor ?? 'Item');
                const data   = payload.map(x => x.value ?? x.total ?? x.monto ?? x.cantidad ?? 0);
                return { labels, data };
            }
            if (payload && Array.isArray(payload.labels) && Array.isArray(payload.montos)) {
                return { labels: payload.labels, data: payload.montos };
            }
            if (payload && Array.isArray(payload.labels) && Array.isArray(payload.data)) {
                return { labels: payload.labels, data: payload.data };
            }
            return { labels: [], data: [] };
        }

        // === MATERIALES (SIN "Proveedores") ===
        function buildMaterialesOption(payload) {
            const { labels, data: montos } = normalizeLabelsData(payload);
            return {
                tooltip: {
                    trigger: 'axis',
                    formatter: p => `<strong>${p[0].name}</strong><br/>Monto: <strong>S/ ${p[0].value.toFixed(2)}</strong>`,
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    textStyle: { color: '#fff', fontSize: 13 },
                    padding: 10
                },
                grid: { left: 90, right: 20, top: 50, bottom: 60 },
                xAxis: { type: 'value', name: 'Monto (S/)', nameLocation: 'middle', nameGap: 30, axisLabel: { formatter: 'S/ {value}', fontSize: 12 } },
                yAxis: { type: 'category', data: labels, axisLabel: { fontSize: 12, interval: 0 } },
                series: [{ type: 'bar', barWidth: 28, data: montos.map((v, i) => ({ value: v, itemStyle: { color: palette(i) } })) }]
            };
        }

        // === EGRESOS (LEYENDA ARRIBA, NO CHOCA) ===
        function buildEgresosOption(payload) {
            const { labels, data } = normalizeLabelsData(payload);
            return {
                tooltip: { trigger: 'item', formatter: '{b}: S/{c} ({d}%)' },
                legend: { top: 5, left: 'center', orient: 'horizontal', textStyle: { fontSize: 11 } },
                series: [{
                    type: 'pie',
                    radius: ['40%','65%'],
                    center: ['50%', '60%'],
                    data: labels.map((l, i) => ({ name: l, value: safe(data[i], 0), itemStyle: { color: palette(i) } })),
                    label: { show: false },
                    emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.5)' } }
                }]
            };
        }

        // === GASTOS DONUT ===
        function buildGastosDonutOption(payload) {
            const { labels, data } = normalizeLabelsData(payload);
            const datos = labels.map((l, i) => {
                let c = palette(i);
                const lower = (l || '').toLowerCase();
                if (lower.includes('aliment')) c = '#34D399';
                if (lower.includes('hosped')) c = '#60A5FA';
                if (lower.includes('pasaj')) c = '#F59E0B';
                return { name: l, value: safe(data[i], 0), itemStyle: { color: c } };
            });
            return {
                tooltip: { trigger: 'item', formatter: '{b}: S/{c} ({d}%)' },
                series: [{ type: 'pie', radius: ['45%','70%'], label: { formatter: '{b}' }, data: datos }]
            };
        }

        // === BALANCE GENERAL - VERSIÓN FINAL 100% COMPATIBLE CON TU GRID ===
        function buildBalanceOption(payload) {
            const ingresos = Math.abs(Number(payload.monto_inicial || 0));
            const egresos = Math.abs(Number(payload.total_egresos || 0));
            const ganancia = Number(payload.ganancia_neta || 0);

            return {
                title: {
                    text: `S/ ${ganancia.toFixed(2)}`,
                    left: 'center',
                    top: 8,
                    textStyle: {
                        fontSize: 19,
                        fontWeight: 'bold',
                        color: ganancia >= 0 ? '#10B981' : '#EF4444'
                    }
                },
                tooltip: {
                    trigger: 'axis',
                    formatter: params => params
                        .filter(p => p.value != null)
                        .map(p => `${p.marker} ${p.seriesName}: <strong>S/ ${Math.abs(p.value).toFixed(2)}</strong>`)
                        .join('<br>')
                },
                legend: {
                    show: true,
                    top: 35,           // Subimos la leyenda
                    left: 'center',
                    data: ['Ingresos', 'Egresos', 'Utilidad'],
                    textStyle: { fontSize: 10, fontWeight: 'bold', color: '#374151' },
                    itemGap: 16        // Más espacio entre items
                },
                grid: {
                    left: '10%',
                    right: '8%',
                    bottom: '14%',
                    top: 70,           // Bajamos el gráfico para dar espacio arriba
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: ['INGRESOS', 'EGRESOS', 'UTILIDAD'],
                    axisLabel: {
                        fontSize: 10,
                        fontWeight: 'bold',
                        color: '#4b5563',
                        margin: 12
                    },
                    axisTick: { show: false }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        fontSize: 10,
                        formatter: 'S/ {value}'
                    },
                    splitLine: { lineStyle: { color: '#f3f4f6' } }
                },
                series: [
                    {
                        name: 'Ingresos',
                        type: 'bar',
                        barWidth: '24%',
                        data: [ingresos, null, null],
                        itemStyle: { color: '#10B981', borderRadius: [8, 8, 0, 0] },
                        label: { 
                            show: ingresos > 0, 
                            position: 'top', 
                            formatter: 'S/ {c}', 
                            fontSize: 11, 
                            fontWeight: 'bold', 
                            color: '#065f46' 
                        }
                    },
                    {
                        name: 'Egresos',
                        type: 'bar',
                        barWidth: '24%',
                        data: [null, egresos, null],
                        itemStyle: { color: '#EF4444', borderRadius: [8, 8, 0, 0] },
                        label: { 
                            show: egresos > 0, 
                            position: 'top', 
                            formatter: 'S/ {c}', 
                            fontSize: 11, 
                            fontWeight: 'bold', 
                            color: '#991b1b' 
                        }
                    },
                    {
                        name: 'Utilidad',
                        type: 'bar',
                        barWidth: '24%',
                        data: [null, null, ganancia],
                        itemStyle: { 
                            color: ganancia >= 0 ? '#3B82F6' : '#F59E0B',
                            borderRadius: [8, 8, 0, 0]
                        },
                        label: {
                            show: true,
                            position: 'top',
                            formatter: () => ganancia >= 0 
                                ? `S/ ${ganancia.toFixed(2)}` 
                                : `-S/ ${Math.abs(ganancia).toFixed(2)}`,
                            fontSize: 12,
                            fontWeight: 'bold',
                            color: '#fff',
                            backgroundColor: ganancia >= 0 ? '#3B82F6' : '#F59E0B',
                            padding: [3, 7],
                            borderRadius: 6
                        }
                    }
                ]
            };
        }

        // === UPDATE ALL ===
        async function updateAll() {
            const btn = refreshBtn;
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Actualizando…';
            [materialesChart, egresosChart, gastosChart, balanceChart].forEach(showLoading);

            try {
                const [mat, egr, gas, bal] = await Promise.all([
                    getJSON(`${baseUrl}/materiales`),
                    getJSON(`${baseUrl}/egresos`),
                    getJSON(`${baseUrl}/gastos`),
                    getJSON(`${baseUrl}/balance`)
                ]);

                // Materiales + leyenda manual (sin "Proveedores")
                if (mat) {
                    materialesChart.setOption(buildMaterialesOption(mat), true);
                    const { labels, data: montos } = normalizeLabelsData(mat);
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

                egresosChart.setOption(egr ? buildEgresosOption(egr) : emptyPie, true);
                gastosChart.setOption(gas ? buildGastosDonutOption(gas) : emptyPie, true);
                balanceChart.setOption(bal ? buildBalanceOption(bal) : emptyBar, true);

                setTimeout(() => [materialesChart, egresosChart, gastosChart, balanceChart].forEach(ch => ch.resize()), 100);
            } finally {
                [materialesChart, egresosChart, gastosChart, balanceChart].forEach(hideLoading);
                btn.disabled = false; btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Actualizar Gráficos';
            }
        }

        updateAll();
        refreshBtn.addEventListener('click', updateAll);
        document.addEventListener('materialSaved', updateAll);

        // === CALENDARIO ===
        function addOneDay(d) { const date = new Date(d); date.setDate(date.getDate() + 1); return date.toISOString().slice(0,10); }

        async function loadCalendarDataAndRender() {
            const data = await getJSON(`${baseUrl}/calendar`);
            if (!data) return;

            const events = [];
            const start = data.start;
            const plannedEnd = data.planned_end;
            const visualEnd = data.visual_end || data.end || data.true_end || plannedEnd || start;

            if (start && plannedEnd) {
                events.push({ start, end: addOneDay(plannedEnd), display: 'background', backgroundColor: 'rgba(16,185,129,0.22)' });
                if (new Date(visualEnd) > new Date(plannedEnd)) {
                    events.push({ start: addOneDay(plannedEnd), end: addOneDay(visualEnd), display: 'background', backgroundColor: 'rgba(245,158,11,0.25)' });
                }
            }

            (data.days || []).forEach(d => {
                if (d.presentes > 0) events.push({ title: `Presentes: ${d.presentes}`, start: d.fecha, allDay: true, color: '#2563EB' });
                if (d.ausentes > 0) events.push({ title: `Ausentes: ${d.ausentes}`, start: d.fecha, allDay: true, color: '#EF4444' });
            });

            if (calendarInstance) {
                calendarInstance.removeAllEvents();
                calendarInstance.addEventSource(events);
            }
        }

        function initCalendarIfNeeded() {
            if (calendarInstance) return calendarInstance.updateSize();
            const el = document.getElementById('calendar');
            if (!el) return;

            calendarInstance = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                locale: 'es',
                height: 520,
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth' },
                dayMaxEventRows: true,
                dateClick: info => openDayDetails(info.dateStr),
                eventClick: info => info.event.startStr && openDayDetails(info.event.startStr.slice(0,10))
            });
            loadCalendarDataAndRender().then(() => calendarInstance.render());
        }

        initCalendarIfNeeded();
        window.addEventListener('resize', () => {
            [materialesChart, egresosChart, gastosChart, balanceChart].forEach(ch => ch?.resize());
            calendarInstance?.updateSize();
        });

        // === MODALES ===
        window.openModal = id => document.getElementById(id).classList.remove('hidden');
        window.closeModal = id => document.getElementById(id).classList.add('hidden');

        // === DETALLE DÍA ===
        window.openDayDetails = async function(dateStr) {
            try {
                const res = await fetch(`${baseUrl}/calendar/day/${dateStr}`, { headers });
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
                    return ((dn + da) || fallback).toUpperCase();
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
                console.error('Error:', e);
                alert('No se pudo cargar el detalle del día');
            }
        };

        document.getElementById('calendarDayModal').addEventListener('click', e => {
            if (e.target === e.currentTarget) closeModal('calendarDayModal');
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !document.getElementById('calendarDayModal').classList.contains('hidden')) {
                closeModal('calendarDayModal');
            }
        });
    });
</script>
@endsection