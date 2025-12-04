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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Materiales (AHORA IGUAL QUE EN DETALLES) -->
            <div class="p-8 bg-white shadow-2xl rounded-2xl border-2 border-gray-200 hover:shadow-2xl hover:border-blue-600 transition duration-300">
                <h2 class="text-2xl font-bold mb-6 text-gray-900 border-b-2 border-gray-200 pb-2">Materiales</h2>
                <div id="chartMateriales" style="width: 100%; height: 480px;"></div>
                <ul id="materialesLegend" class="mt-4 space-y-1 text-sm text-gray-700"></ul>
            </div>

            <!-- Egresos (mejorado) -->
            <div class="p-8 bg-white shadow-2xl rounded-2xl border-2 border-gray-200 hover:shadow-2xl hover:border-blue-600 transition duration-300">
                <h2 class="text-2xl font-bold mb-6 text-gray-900 border-b-2 border-gray-200 pb-2">Egresos</h2>
                <div id="chartEgresos" style="width: 100%; height: 480px;"></div>
            </div>

            <!-- Control de Gastos (mejorado) -->
            <div class="lg:col-span-2 p-8 bg-white shadow-2xl rounded-2xl border-2 border-gray-200 hover:shadow-2xl hover:border-green-600 transition duration-300">
                <h2 class="text-2xl font-bold mb-6 text-gray-900 border-b-2 border-gray-200 pb-2">Control de Gastos</h2>
                <div id="chartControl" style="width: 100%; height: 420px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Carga asíncrona de ECharts
    function loadECharts(callback) {
        if (typeof echarts !== 'undefined') return callback();
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js';
        script.onload = callback;
        document.head.appendChild(script);
    }

    // Paleta de colores consistente
    function palette(i) {
        const colors = ['#3B82F6','#2563EB','#1E40AF','#F59E0B','#EF4444','#10B981','#6366F1','#14B8A6','#8B5CF6','#EC4899'];
        return colors[i % colors.length];
    }
    function adjustColor(color, amount) {
        return '#' + color.replace(/^#/, '').replace(/../g, c => 
            ('0'+Math.min(255, Math.max(0, parseInt(c, 16) + amount)).toString(16)).substr(-2)
        );
    }

    // Normalizar datos (compatible con todos los endpoints)
    function normalizeLabelsData(payload) {
        if (!payload || payload.error) return { labels: [], data: [] };
        if (Array.isArray(payload.labels)) {
            return { labels: payload.labels, data: (payload.montos || payload.data || []).map(Number) };
        }
        if (Array.isArray(payload)) {
            return {
                labels: payload.map(x => x.label || x.categoria || x.descripcion_mat || 'Sin nombre'),
                data: payload.map(x => Number(x.value || x.total || x.monto || x.monto_mat || 0))
            };
        }
        return { labels: [], data: [] };
    }

    // === GRÁFICO DE MATERIALES (100% IGUAL AL DE DETALLES) ===
    function buildMaterialesOption(payload) {
        const { labels, data: montos } = normalizeLabelsData(payload);
        if (labels.length === 0) {
            return { title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#9ca3af', fontSize: 16 } }, series: [] };
        }

        const enableScroll = labels.length > 10;
        const endPercent = enableScroll ? Math.min(100, Math.max(20, 1000 / labels.length)) : 100;

        return {
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' },
                formatter: p => `<div style="padding:4px"><div style="font-weight:600;color:#374151;margin-bottom:4px">${p[0].name}</div><div style="color:#f59e0b;font-weight:bold">S/ ${Number(p[0].value).toFixed(2)}</div></div>`,
                backgroundColor: '#ffffff', borderColor: '#e5e7eb', textStyle: { color: '#374151' }, padding: 12, borderRadius: 8
            },
            grid: { left: '3%', right: '4%', bottom: '3%', top: '3%', containLabel: true },
            xAxis: { type: 'value', axisLabel: { formatter: v => `S/${v >= 1000 ? (v/1000).toFixed(1)+'k' : v}`, color: '#6B7280', fontSize: 11 },
                splitLine: { lineStyle: { type: 'dashed', color: '#E5E7EB' } }
            },
            yAxis: { type: 'category', data: labels, axisLabel: { color: '#374151', fontSize: 12, width: 160, overflow: 'break', interval: 0 },
                axisTick: { show: false }, axisLine: { lineStyle: { color: '#D1D5DB' } }
            },
            dataZoom: enableScroll ? [
                { type: 'slider', yAxisIndex: 0, width: 20, right: 10, start: 0, end: endPercent, fillerColor: 'rgba(59,130,246,0.2)' },
                { type: 'inside', yAxisIndex: 0, zoomOnMouseWheel: false, moveOnMouseWheel: true }
            ] : [],
            series: [{
                type: 'bar',
                data: montos.map((v, i) => ({
                    value: v,
                    itemStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                            { offset: 0, color: palette(i) },
                            { offset: 1, color: adjustColor(palette(i), -20) }
                        ]),
                        borderRadius: [0, 4, 4, 0]
                    }
                })),
                barMaxWidth: 34,
                showBackground: true,
                backgroundStyle: { color: '#F3F4F6', borderRadius: [0, 4, 4, 0] }
            }]
        };
    }

    // === EGRESOS (mejorado) ===
    function buildEgresosOption(payload) {
        const { labels, data } = normalizeLabelsData(payload);
        if (labels.length === 0) return { title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#9ca3af' } }, series: [] };

        return {
            tooltip: { trigger: 'item', formatter: '{b}: S/{c} ({d}%)', backgroundColor: '#fff', borderColor: '#e5e7eb', padding: 10, borderRadius: 8 },
            legend: { top: 10, left: 'center', orient: 'horizontal', textStyle: { fontSize: 12 } },
            series: [{
                type: 'pie',
                radius: ['40%', '70%'],
                center: ['50%', '58%'],
                data: labels.map((l, i) => ({ name: l, value: data[i] || 0, itemStyle: { color: palette(i) } })),
                label: { show: false },
                emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.5)' } }
            }]
        };
    }

    // === CONTROL DE GASTOS (mejorado) ===
    function buildControlOption(payload) {
        if (!payload || payload.error) {
            return { title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#9ca3af' } }, series: [] };
        }
        const inicial = Number(payload.monto_inicial || 0);
        const gastado = Math.abs(Number(payload.total_egresos || 0));
        const restante = Math.max(0, inicial - gastado);

        return {
            title: { text: 'Control de Gastos', left: 'center', top: 10, textStyle: { fontSize: 18, fontWeight: 'bold' } },
            tooltip: { trigger: 'item', formatter: '{b}: S/{c} ({d}%)' },
            legend: { orient: 'vertical', left: 'left', top: 'middle', textStyle: { fontSize: 13 } },
            series: [{
                type: 'pie',
                radius: ['45%', '70%'],
                center: ['60%', '50%'],
                avoidLabelOverlap: false,
                label: { formatter: '{b}\nS/ {c}', fontSize: 13, fontWeight: 'bold' },
                labelLine: { show: true },
                data: [
                    { value: gastado, name: 'Gastado', itemStyle: { color: '#EF4444' } },
                    { value: restante, name: 'Restante', itemStyle: { color: '#10B981' } }
                ]
            }]
        };
    }

    // Fetch con CSRF
    async function fetchJSON(url) {
        try {
            const tabToken = '{{ session('tab_token_' . auth()->id()) }}';
            const withToken = (u) => u + (u.includes('?') ? '&' : '?') + 't=' + tabToken;
            const res = await fetch(withToken(url), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                }
            });
            if (!res.ok) throw new Error(await res.text());
            return await res.json();
        } catch (e) {
            console.error('Fetch error:', e);
            return { error: true };
        }
    }

    // Actualizar todos los gráficos
    async function updateCharts(proyectoId) {
        const base = `/api/proyectos/${proyectoId}`;
        const [mat, egr, bal] = await Promise.all([
            fetchJSON(`${base}/materiales`),
            fetchJSON(`${base}/egresos`),
            fetchJSON(`${base}/balance`)
        ]);

        const chartMateriales = echarts.getInstanceByDom(document.getElementById('chartMateriales')) || echarts.init(document.getElementById('chartMateriales'));
        const chartEgresos = echarts.getInstanceByDom(document.getElementById('chartEgresos')) || echarts.init(document.getElementById('chartEgresos'));
        const chartControl = echarts.getInstanceByDom(document.getElementById('chartControl')) || echarts.init(document.getElementById('chartControl'));

        // Materiales + leyenda manual
        if (mat && !mat.error) {
            chartMateriales.setOption(buildMaterialesOption(mat), true);
            const { labels } = normalizeLabelsData(mat);
            const legendEl = document.getElementById('materialesLegend');
            legendEl.innerHTML = '';
            labels.forEach((l, i) => {
                const li = document.createElement('li');
                li.innerHTML = `<span class="inline-block w-4 h-4 mr-2 rounded" style="background:${palette(i)}"></span>${l}`;
                legendEl.appendChild(li);
            });
        } else {
            chartMateriales.setOption({ title: { text: 'Sin datos de materiales' } });
            document.getElementById('materialesLegend').innerHTML = '';
        }

        chartEgresos.setOption(buildEgresosOption(egr));
        chartControl.setOption(buildControlOption(bal));

        setTimeout(() => {
            chartMateriales.resize();
            chartEgresos.resize();
            chartControl.resize();
        }, 100);
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', () => {
        initDropdown();
        loadECharts(() => {
            const initialId = document.getElementById('proyectoSelect').value;
            if (initialId) updateCharts(initialId);
        });

        function initDropdown() {
            const select = document.getElementById('proyectoSelect');
            const titulo = document.getElementById('tituloProyecto');
            const monto = document.getElementById('montoInicial');
            const cliente = document.getElementById('clienteProyecto');

            select.addEventListener('change', e => {
                const opt = e.target.selectedOptions[0];
                titulo.textContent = opt.dataset.nombre;
                cliente.textContent = opt.dataset.cliente;
                monto.textContent = Number(opt.dataset.monto || 0).toLocaleString('es-PE', { minimumFractionDigits: 2 });
                updateCharts(e.target.value);
            });

            // Cargar inicial
            if (select.value) {
                const opt = select.selectedOptions[0];
                titulo.textContent = opt.dataset.nombre;
                cliente.textContent = opt.dataset.cliente;
                monto.textContent = Number(opt.dataset.monto || 0).toLocaleString('es-PE', { minimumFractionDigits: 2 });
            }
        }

        window.addEventListener('resize', () => {
            echarts.getInstances().forEach(ch => ch.resize());
        });
    });
</script>
@endpush