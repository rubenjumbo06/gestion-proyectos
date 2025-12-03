@extends('layouts.app')

@section('content')
<div class="w-full min-h-screen bg-gradient-to-br from-gray-50 to-white p-6">
    <div class="max-w-screen-2xl mx-auto">
        <!-- Encabezado -->
        <div class="mb-12 border-b-2 border-gray-300 pb-8">
            <div class="flex justify-between items-center px-8">
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-wide"><span id="tituloProyecto">{{ $proyecto->nombre_proyecto ?? '' }}</span></h1>
                <div class="flex items-center space-x-6">
                    <label for="proyectoSelect" class="text-xl font-semibold text-gray-800">Seleccionar Proyecto:</label>
                    <select id="proyectoSelect" class="border-2 border-gray-300 rounded-xl p-3 shadow-md focus:outline-none focus:ring-2 focus:ring-blue-600 text-lg bg-white hover:bg-gray-50 transition duration-200">
                        @foreach($proyectos as $p)
                            <option
                                value="{{ $p->id_proyecto }}"
                                data-nombre="{{ $p->nombre_proyecto }}"
                                data-cliente="{{ $p->cliente_proyecto }}"
                                data-monto="{{ optional($p->montopr)->monto_inicial ?? 0 }}"
                                {{ isset($proyecto) && $p->id_proyecto == $proyecto->id_proyecto ? 'selected' : '' }}>
                                {{ $p->nombre_proyecto }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Info general -->
        <div class="mb-12">
            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-200">
                <div class="grid grid-cols-2 gap-8">
                    <p class="text-2xl"><strong class="text-gray-900">Monto Inicial:</strong> S/. <span id="montoInicial">{{ number_format($proyecto->montopr->monto_inicial ?? 0, 2) }}</span></p>
                    <p class="text-2xl"><strong class="text-gray-900">Cliente:</strong> <span id="clienteProyecto">{{ $proyecto->cliente_proyecto ?? '' }}</span></p>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-2 gap-12">
            <!-- Materiales -->
            <div class="p-8 bg-white from-gray-100 to-white/80 shadow-2xl rounded-2xl border-2 border-gray-200 hover:shadow-2xl hover:border-blue-600 transition duration-300">
                <h2 class="text-2xl font-bold mb-6 text-gray-900 border-b-2 border-gray-200 pb-2">Materiales</h2>
                <div id="chartMateriales" style="width: 100%; height: 350px;"></div>
                <div id="materialesLegend" class="mt-6 text-lg text-gray-700"></div>
            </div>

            <!-- Egresos -->
            <div class="p-8 bg-white from-gray-100 to-white/80 shadow-2xl rounded-2xl border-2 border-gray-200 hover:shadow-2xl hover:border-blue-600 transition duration-300">
                <h2 class="text-2xl font-bold mb-6 text-gray-900 border-b-2 border-gray-200 pb-2">Egresos</h2>
                <div id="chartEgresos" style="width: 100%; height: 350px;"></div>
            </div>

            <!-- Control de Gastos -->
            <div class="col-span-2 p-8 bg-white from-gray-200 to-white/80 shadow-2xl rounded-2xl border-2 border-gray-200 hover:shadow-2xl hover:border-blue-700 transition duration-300">
                <h2 class="text-2xl font-bold mb-6 text-gray-900 border-b-2 border-gray-200 pb-2">Control de Gastos</h2>
                <div id="chartControl" style="width: 100%; height: 350px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Carga asíncrona de ECharts (CDN directo)
    function loadECharts(callback) {
        if (typeof echarts !== 'undefined') {
            callback();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js';
        script.onload = callback;
        script.onerror = function() {
            console.error('=== DASHBOARD ERROR: ECharts CDN falló');
            showErrors('ECharts no cargó (CDN falló)');
        };
        document.head.appendChild(script);
    }

    // Inicializar dropdown (independiente de ECharts)
    function initDropdown() {
        console.log('=== DASHBOARD: Inicializando dropdown');
        const selectProyecto = document.getElementById('proyectoSelect');
        const tituloProyecto = document.getElementById('tituloProyecto');
        const montoInicialEl = document.getElementById('montoInicial');
        const clienteProyectoEl = document.getElementById('clienteProyecto');

        if (!selectProyecto) {
            console.error('=== DASHBOARD ERROR: Select no encontrado');
            return;
        }

        selectProyecto.addEventListener('change', async function(e) {
            console.log('=== DASHBOARD: Cambio detectado, nuevo ID', e.target.value);
            const id = e.target.value;
            const opt = e.target.options[e.target.selectedIndex];
            const nombre = opt.getAttribute('data-nombre') || '';
            const cliente = opt.getAttribute('data-cliente') || '';
            let monto = Number(opt.getAttribute('data-monto') || 0);

            if (monto === 0 && id) {
                try {
                    const bal = await fetchJSON(`/api/proyectos/${id}/balance`);
                    if (bal && !bal.error) monto = Number(bal.total_servicios || bal.monto_inicial || 0);
                } catch (e) {
                    console.error('=== DASHBOARD: Fallo fetch monto fallback', e);
                }
            }

            tituloProyecto.textContent = nombre;
            clienteProyectoEl.textContent = cliente;
            montoInicialEl.textContent = monto.toLocaleString('es-PE', { minimumFractionDigits: 2 });
            console.log('=== DASHBOARD: Dropdown actualizado - Título:', nombre, 'Monto:', monto);

            if (typeof updateCharts === 'function') updateCharts(id);
        });

        // Inicial inicial
        const initialId = selectProyecto.value;
        if (initialId) {
            const opt = selectProyecto.options[selectProyecto.selectedIndex];
            const nombre = opt.getAttribute('data-nombre') || '';
            const cliente = opt.getAttribute('data-cliente') || '';
            let monto = Number(opt.getAttribute('data-monto') || 0);
            tituloProyecto.textContent = nombre;
            clienteProyectoEl.textContent = cliente;
            montoInicialEl.textContent = monto.toLocaleString('es-PE', { minimumFractionDigits: 2 });
            console.log('=== DASHBOARD: Inicial dropdown cargado');
        }
    }

    // Inicializar charts solo si ECharts cargado
    function initCharts() {
        console.log('=== DASHBOARD: Inicializando charts');
        let chartMateriales = echarts.init(document.getElementById('chartMateriales'));
        let chartEgresos = echarts.init(document.getElementById('chartEgresos'));
        let chartControl = echarts.init(document.getElementById('chartControl'));
        console.log('=== DASHBOARD: Charts inicializados');

        window.addEventListener('resize', () => {
            chartMateriales.resize();
            chartEgresos.resize();
            chartControl.resize();
        });

        const initialId = document.getElementById('proyectoSelect').value;
        if (initialId) {
            updateCharts(initialId);
        } else {
            showErrors('No proyecto inicial');
        }
    }

    // Actualizar charts
    async function updateCharts(proyectoId) {
        console.log('=== DASHBOARD: Actualizando charts para ID', proyectoId);
        let chartMateriales = echarts.getInstanceByDom(document.getElementById('chartMateriales'));
        let chartEgresos = echarts.getInstanceByDom(document.getElementById('chartEgresos'));
        let chartControl = echarts.getInstanceByDom(document.getElementById('chartControl'));

        [chartMateriales, chartEgresos, chartControl].forEach(chart => chart.setOption(emptyBar('Cargando...')));

        const base = `/api/proyectos/${proyectoId}`;
        const [mat, egr, bal] = await Promise.all([
            fetchJSON(`${base}/materiales`),
            fetchJSON(`${base}/egresos`),
            fetchJSON(`${base}/balance`)
        ]);

        console.log('=== DASHBOARD: Datos cargados', { mat, egr, bal });

        chartMateriales.setOption(buildMaterialesOption(mat) || emptyBar('Sin Materiales'));
        chartEgresos.setOption(buildEgresosOption(egr) || emptyPie('Sin Egresos'));
        chartControl.setOption(buildControlOption(bal) || emptyPie('Sin Balance'));
    }

    // Funciones auxiliares
    function emptyBar(title, message) {
        return {
            title: { text: title, left: 'center' },
            xAxis: { type: 'category', data: [] },
            yAxis: { type: 'value' },
            series: [{ type: 'bar', data: [] }],
            graphic: [{ type: 'text', left: 'center', top: 'center', style: { text: message, fill: '#999', fontSize: 14 } }]
        };
    }

    function emptyPie(title, message) {
        return {
            title: { text: title, left: 'center' },
            series: [{ type: 'pie', data: [], radius: '50%', label: { show: false } }],
            graphic: [{ type: 'text', left: 'center', top: 'center', style: { text: message, fill: '#999', fontSize: 14 } }]
        };
    }

    async function fetchJSON(url) {
        console.log('=== DASHBOARD FETCH:', url);
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                credentials: 'same-origin'
            });
            if (!response.ok) {
                const text = await response.text();
                throw new Error(`HTTP ${response.status}: ${text.substring(0, 100)}`);
            }
            const data = await response.json();
            console.log('=== DASHBOARD FETCH OK:', data);
            return data;
        } catch (error) {
            console.error('=== DASHBOARD FETCH ERROR:', error);
            return { error: error.message };
        }
    }

    function normalizeLabelsData(payload, preferMontos = false) {
        if (!payload || payload.error) return { labels: [], data: [], error: payload?.error };
        if (Array.isArray(payload.labels) && (Array.isArray(payload.data) || Array.isArray(payload.montos))) {
            const data = (preferMontos && Array.isArray(payload.montos)) ? payload.montos : payload.data || [];
            return { labels: payload.labels, data: data.map(x => Number(x || 0)) };
        }
        if (Array.isArray(payload)) {
            const labels = payload.map(it => it.descripcion_mat ?? it.label ?? '');
            const data = payload.map(it => Number(it.monto_mat ?? it.monto ?? 0));
            return { labels, data };
        }
        return { labels: [], data: [] };
    }

    function buildMaterialesOption(payload) {
        const norm = normalizeLabelsData(payload, true);
        if (norm.error) return emptyBar('Error Materiales', norm.error);
        return {
            title: { text: 'Monto por Material', left: 'center', textStyle: { fontSize: 16, fontWeight: 'bold' } },
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'shadow' },
                formatter: p => {
                    const val = p[0].value;
                    return `<div style="font-family: 'Poppins', sans-serif; padding: 4px;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 4px;">${p[0].name}</div>
                        <div style="color: #3B82F6; font-weight: bold;">S/ ${Number(val).toFixed(2)}</div>
                    </div>`;
                },
                backgroundColor: '#ffffff',
                borderColor: '#e5e7eb',
                textStyle: { color: '#374151' },
                padding: 12,
                borderRadius: 8
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                top: '12%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: norm.labels,
                axisLabel: {
                    color: '#374151',
                    fontSize: 11,
                    interval: 0,
                    rotate: norm.labels.length > 5 ? 45 : 0
                },
                axisTick: { show: false },
                axisLine: { lineStyle: { color: '#D1D5DB' } }
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: val => `S/ ${val >= 1000 ? (val/1000).toFixed(1) + 'k' : val}`,
                    color: '#6B7280',
                    fontSize: 11
                },
                splitLine: {
                    lineStyle: { type: 'dashed', color: '#E5E7EB' }
                }
            },
            series: [{
                data: norm.data.map((v, i) => ({
                    value: v,
                    itemStyle: {
                        color: new echarts.graphic.LinearGradient(0, 1, 0, 0, [
                            { offset: 0, color: '#3B82F6' },
                            { offset: 1, color: '#60A5FA' }
                        ]),
                        borderRadius: [4, 4, 0, 0]
                    }
                })),
                type: 'bar',
                barMaxWidth: 50,
                showBackground: true,
                backgroundStyle: { color: '#F3F4F6', borderRadius: [4, 4, 0, 0] },
                label: {
                    show: norm.data.length <= 8,
                    position: 'top',
                    formatter: '{c}',
                    fontSize: 10,
                    color: '#374151'
                }
            }]
        };
    }

    function buildEgresosOption(payload) {
        const norm = normalizeLabelsData(payload);
        if (norm.error) return emptyPie('Error Egresos', norm.error);
        const pieData = norm.labels.map((l, i) => ({ name: l, value: norm.data[i] || 0 }));
        return {
            title: { text: 'Distribución de Egresos', left: 'center' },
            tooltip: { trigger: 'item', formatter: '{b}: S/{c} ({d}%)' },
            series: [{ name: 'Egresos', type: 'pie', radius: '50%', data: pieData }]
        };
    }

    function buildControlOption(balancePayload) {
        if (!balancePayload || balancePayload.error) return emptyPie('Error Control', balancePayload?.error || 'Sin Balance');
        const totalServicios = Number(balancePayload.total_servicios || balancePayload.monto_inicial || 0);
        const egresos = Number(balancePayload.total_egresos || 0); // Monto gastado - CORREGIDO: usar total_egresos
        const gananciaNeta = Number(balancePayload.ganancia_neta || (totalServicios - egresos)); // Utilidad restante

        // Asegurar valores no negativos
        const gastado = Math.max(0, egresos);
        const utilidadRestante = Math.max(0, gananciaNeta);

        return {
            title: {
                text: 'Control de Gastos',
                left: 'center',
                textStyle: { fontSize: 16, fontWeight: 'bold', color: '#374151' }
            },
            tooltip: {
                trigger: 'item',
                formatter: p => `${p.marker} ${p.name}: <strong>S/ ${Number(p.value).toFixed(2)}</strong> (${p.percent}%)`,
                backgroundColor: '#ffffff',
                borderColor: '#e5e7eb',
                textStyle: { color: '#374151', fontFamily: 'Poppins, sans-serif' },
                padding: 12,
                borderRadius: 8
            },
            legend: {
                orient: 'vertical',
                left: 'left',
                top: 'center',
                data: ['Gastado', 'Utilidad Restante'],
                textStyle: { fontSize: 12, color: '#374151' }
            },
            series: [{
                name: 'Gastos',
                type: 'pie',
                radius: ['35%', '65%'],
                center: ['60%', '50%'],
                avoidLabelOverlap: false,
                label: {
                    show: true,
                    formatter: params => `${params.name}\nS/ ${Number(params.value).toFixed(2)}`,
                    fontSize: 12,
                    position: 'outside',
                    color: '#374151',
                    fontWeight: 'bold'
                },
                labelLine: {
                    show: true,
                    length: 15,
                    length2: 10
                },
                emphasis: {
                    label: { show: true, fontSize: 14, fontWeight: 'bold' },
                    itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0, 0, 0, 0.5)' }
                },
                data: [
                    {
                        value: gastado,
                        name: 'Gastado',
                        itemStyle: { color: '#EF4444' }
                    },
                    {
                        value: utilidadRestante,
                        name: 'Utilidad Restante',
                        itemStyle: { color: '#10B981' }
                    }
                ]
            }]
        };
    }

    function showErrors(message) {
        ['chartMateriales', 'chartEgresos', 'chartControl'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = `<p class="text-red-500 text-center p-4">Error: ${message}</p>`;
        });
    }

    // Iniciar dropdown y cargar ECharts
    initDropdown();
    loadECharts(initCharts);
</script>
@endpush