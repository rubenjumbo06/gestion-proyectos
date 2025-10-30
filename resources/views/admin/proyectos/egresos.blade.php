{{-- resources/views/admin/proyectos/egresos.blade.php --}}
@php
    // Determinar id del proyecto (compatibilidad id_proyecto / id)
    $proyectoId = $proyecto->id_proyecto ?? $proyecto->id ?? null;

    // Calcular montos sumados en PHP (evita inyección compleja en JS)
    $sum_materiales = 0;
    $sum_planilla = 0;
    $sum_gastos_extra = 0;
    try {
        if ($proyectoId) {
            $sum_materiales = $proyecto->materiales()->sum('monto_mat');
            $sum_planilla = $proyecto->planilla()->sum(DB::raw('pago + alimentacion_trabajador + hospedaje_trabajador + pasajes_trabajador'));
            $sum_gastos_extra = $proyecto->gastosExtra()->sum(DB::raw('alimentacion_general + hospedaje + pasajes'));
        }
    } catch (\Throwable $e) {
        $sum_materiales = 0;
        $sum_planilla = 0;
        $sum_gastos_extra = 0;
    }

    // Monto inicial (fallebacks)
    $monto_inicial = 0;
    try {
        if ($proyectoId) {
            $monto_inicial = optional($proyecto->montopr)->monto_inicial
                ?? DB::table('montopr')->where('proyecto_id', $proyectoId)->value('monto_inicial')
                ?? DB::table('control_gastos')->where('id_proyecto', $proyectoId)->value('monto_inicial')
                ?? 0;
        }
    } catch (\Throwable $e) {
        $monto_inicial = 0;
    }

    // Asegurarnos de obtener el ÚLTIMO egreso persistido en BD (si no fue pasado por el controlador)
    if (!isset($egreso) || !$egreso) {
        try {
            $egreso = null;
            if ($proyectoId) {
                $egreso = DB::table('egresos')
                    ->where('id_proyecto', $proyectoId)
                    ->orderBy('id_egreso', 'desc')
                    ->first();
            }
        } catch (\Throwable $e) {
            $egreso = null;
        }
    }
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <i class="icon fa fa-check"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <i class="icon fa fa-ban"></i> {{ session('error') }}
    </div>
@endif

<!-- Resumen Preliminar -->
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Resumen Preliminar de Egresos (Datos Actuales)</h3>
    </div>
    <div class="box-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Monto (S/)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Materiales (Suma total)</td>
                    <td id="pre_materiales">
                        {{ number_format($sum_materiales, 2) }}
                    </td>
                </tr>
                <tr>
                    <td>Personal (Suma total incl. gastos)</td>
                    <td id="pre_planilla">
                        {{ number_format($sum_planilla, 2) }}
                    </td>
                </tr>
                <tr>
                    <td>Gastos Extras (Suma total)</td>
                    <td id="pre_gastos_extra">
                        {{ number_format($sum_gastos_extra, 2) }}
                    </td>
                </tr>
                <tr>
                    <td>SCTR aplicado</td>
                    <td id="pre_scr">
                        {{ number_format(optional($egreso)->scr ?? 0, 2) }}
                    </td>
                </tr>
                <tr>
                    <td>Gastos Administrativos (aplicados)</td>
                    <td id="pre_gastos_admin">
                        {{ number_format(optional($egreso)->gastos_administrativos ?? 0, 2) }}
                    </td>
                </tr>
                <tr class="bg-warning">
                    <td><strong>Total Egresos</strong></td>
                    <td id="pre_total_egresos">
                        @if($egreso && isset($egreso->total_egresos))
                            {{ number_format($egreso->total_egresos, 2) }}
                        @else
                            {{ number_format($sum_materiales + $sum_planilla + $sum_gastos_extra, 2) }}
                        @endif
                    </td>
                </tr>
                <tr class="bg-success">
                    <td><strong>Monto Inicial</strong></td>
                    <td id="pre_monto_inicial">
                        {{ number_format($monto_inicial, 2) }}
                    </td>
                </tr>
                <tr class="bg-info">
                    <td><strong>Utilidad estimada (Monto inicial - Total egresos)</strong></td>
                    <td id="pre_utilidad">S/0.00</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Formulario de Cálculo -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Configuración de Egresos</h3>
    </div>
    <div class="box-body">
        <form id="egresosForm">
            @csrf
            <div id="egresosInputsContainer" class="row {{ $egreso ? 'hidden' : '' }}">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="scr" class="control-label">Monto SCTR por Proyecto (S/)</label>
                        <input type="number" id="scr" name="scr" step="0.01" min="0"
                            class="form-control"
                            value="{{ optional($egreso)->scr ?? 127.5 }}"
                            {{ $egreso ? 'disabled' : '' }}
                            placeholder="Ingresa el monto SCTR">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="gastos_admin_mensual" class="control-label">Monto Gastos Admin (S/)</label>
                        <input type="number" id="gastos_admin_mensual" name="gastos_admin_mensual" step="0.01" min="0"
                            class="form-control"
                            value="{{ optional($egreso)->gastos_administrativos ?? 800 }}"
                            {{ $egreso ? 'disabled' : '' }}
                            placeholder="Ingresa el monto de gastos administrativos">
                    </div>
                </div>
            </div>

            <!-- Botón de cálculo -->
            <div class="form-group">
                <button type="button" id="calculateEgresosBtn" data-proyecto-id="{{ $proyectoId ?? '' }}"
                    class="btn btn-primary btn-lg">
                    <i class="fa fa-calculator"></i> 
                    {{ $egreso ? 'Recalcular Egresos' : 'Calcular y Guardar Egresos' }}
                </button>
            </div>
        </form>
        <div id="errorMessage" class="alert alert-danger hidden"></div>
    </div>
</div>

<!-- Tabla calculada -->
<div id="egresosTableContainer" class="box box-success hidden">
    <div class="box-header with-border">
        <h3 class="box-title">Resumen de Egresos Calculados</h3>
    </div>
    <div class="box-body">
        <table id="egresosTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Monto (S/)</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody id="egresosTableBody"></tbody>
            <tfoot>
                <tr class="bg-warning">
                    <td><strong>Total Egresos</strong></td>
                    <td id="totalEgresos"><strong>S/0.00</strong></td>
                    <td></td>
                </tr>
                <tr class="bg-info">
                    <td><strong>Utilidad Restante (Monto inicial - Total egresos)</strong></td>
                    <td id="utilidad"><strong>S/0.00</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Botón Finalizar Proyecto -->
<div class="box box-danger">
    <div class="box-body text-center">
        <button type="button" class="btn btn-danger btn-lg" data-toggle="modal" data-target="#finalizarModal">
            <i class="fa fa-stop"></i> Finalizar Proyecto
        </button>
    </div>
</div>

<!-- Modal Finalización -->
<div class="modal fade" id="finalizarModal" tabindex="-1" role="dialog" aria-labelledby="finalizarModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header bg-red" style="border-radius: 16px 16px 0 0;">
                <h4 class="modal-title text-white" id="finalizarModalLabel">Confirmar Finalización</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i></i>
                    <strong>¡Atención!</strong> ¿Estás seguro de que deseas <b>finalizar este proyecto</b>?
                </div>
                <p>Una vez finalizado:</p>
                <ul class="list-disc pl-6">
                    <li><i></i> Se registrará la fecha de finalización real.</li>
                    <li><i></i> Los trabajadores en planilla pasarán a estado <b>LIQUIDADO</b>.</li>
                    <li><i></i> No se podrán agregar más materiales, gastos ni personal.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i></i> Cancelar
                </button>
                <form action="{{ route('proyectos.finalizar', $proyectoId ?? '') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i></i> Sí, Finalizar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>


<!-- Librerías para export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // DOM
    const calculateBtn = document.getElementById('calculateEgresosBtn');
    const form = document.getElementById('egresosForm');
    const egresosInputsContainer = document.getElementById('egresosInputsContainer');
    const errorMessage = document.getElementById('errorMessage');

    const preMateriales = document.getElementById('pre_materiales');
    const prePlanilla = document.getElementById('pre_planilla');
    const preGastosExtra = document.getElementById('pre_gastos_extra');
    const preScr = document.getElementById('pre_scr');
    const preGastosAdmin = document.getElementById('pre_gastos_admin');
    const preTotal = document.getElementById('pre_total_egresos');
    const preMontoInicial = document.getElementById('pre_monto_inicial');
    const preUtilidad = document.getElementById('pre_utilidad');

    const egresosTableContainer = document.getElementById('egresosTableContainer');
    const egresosTableBody = document.getElementById('egresosTableBody');
    const totalEgresos = document.getElementById('totalEgresos');
    const utilidadEl = document.getElementById('utilidad');

    const egresosReportContainer = document.getElementById('egresosReportContainer');
    const egresosReportBody = document.getElementById('egresosReportBody');

    const exportPdfBtn = document.getElementById('exportPdfBtn');
    const exportExcelBtn = document.getElementById('exportExcelBtn');

    // Valores inyectados desde PHP (se usan de forma segura)
    const url = '{{ route("proyectos.calculate.egresos", $proyectoId ?? ($proyecto->id ?? 0)) }}';
    const token = '{{ csrf_token() }}';
    const montoInicial = Number({{ $monto_inicial ?? 0 }});
    const proyectoNombre = {!! json_encode($proyecto->nombre_proyecto ?? $proyecto->nombre ?? '') !!};

    // Sumatorios ya calculados en PHP
    const sumMaterialesServer = Number({{ $sum_materiales }});
    const sumPlanillaServer = Number({{ $sum_planilla }});
    const sumGastosExtraServer = Number({{ $sum_gastos_extra }});

    // existingEgresos: estructura JS (obj) o null — generado con json_encode en PHP
    const existingEgresos = {!! $egreso ? json_encode((array)$egreso) : 'null' !!};

    const fmt = (n) => `S/${Number(n || 0).toFixed(2)}`;

    // Si hay registro persistido, mostrarlo
    if (existingEgresos) {
        if (egresosInputsContainer) egresosInputsContainer.classList.add('hidden');
        if (calculateBtn) calculateBtn.textContent = 'Recalcular Egresos';

        // seteo preliminar con el registro persistido
        if (preScr) preScr.textContent = Number(existingEgresos.scr || 0).toFixed(2);
        if (preGastosAdmin) preGastosAdmin.textContent = Number(existingEgresos.gastos_administrativos || 0).toFixed(2);

        let totalPre = existingEgresos.total_egresos ?? (sumMaterialesServer + sumPlanillaServer + sumGastosExtraServer + Number(existingEgresos.scr || 0) + Number(existingEgresos.gastos_administrativos || 0));
        if (preTotal) preTotal.textContent = Number(totalPre || 0).toFixed(2);
        if (preMontoInicial) preMontoInicial.textContent = montoInicial.toFixed(2);

        const utilidadEstim = existingEgresos.utilidad !== undefined ? Number(existingEgresos.utilidad) : (montoInicial - Number(totalPre || 0));
        if (preUtilidad) preUtilidad.textContent = fmt(utilidadEstim);

            // llenar tabla calculada
            const m = Number(existingEgresos.materiales ?? sumMaterialesServer);
            const p = Number(existingEgresos.planilla ?? sumPlanillaServer);
            const ge = Number(existingEgresos.gastos_extra ?? sumGastosExtraServer);
            const s = Number(existingEgresos.scr || 0);
            const ga = Number(existingEgresos.gastos_administrativos || 0);
            const total = Number(existingEgresos.total_egresos ?? (m + p + ge + s + ga));

            if (egresosTableBody) {
                egresosTableBody.innerHTML = `
                    <tr>
                        <td>Materiales</td>
                        <td>${fmt(m)}</td>
                        <td>Suma total de materiales</td>
                    </tr>
                    <tr>
                        <td>Planilla</td>
                        <td>${fmt(p)}</td>
                        <td>Incluye pago, alimentación, hospedaje, pasajes</td>
                    </tr>
                    <tr>
                        <td>SCTR</td>
                        <td>${fmt(s)}</td>
                        <td>Monto fijo por proyecto</td>
                    </tr>
                    <tr>
                        <td>Gastos Administrativos</td>
                        <td>${fmt(ga)}</td>
                        <td>Monto único por proyecto</td>
                    </tr>
                    <tr>
                        <td>Gastos Extra</td>
                        <td>${fmt(ge)}</td>
                        <td>Otros gastos asociados</td>
                    </tr>
                `;
                totalEgresos.innerHTML = `<strong>${fmt(total)}</strong>`;
                utilidadEl.innerHTML = `<strong>${fmt(utilidadEstim)}</strong>`;
                egresosTableContainer.classList.remove('hidden');
            }

        // reporte exportable
        if (egresosReportBody) {
            egresosReportBody.innerHTML = `
                <tr draggable="true" class="egreso-report-row" data-id="${existingEgresos.id_egreso || ''}">
                    <td class="py-2 px-4 border-b" contenteditable="true">Egreso ${existingEgresos.id_egreso || ''} - ${new Date().toLocaleDateString()}</td>
                    <td class="py-2 px-4 border-b">${fmt(total)}</td>
                </tr>
            `;
            egresosReportContainer.classList.remove('hidden');
            initDragAndDrop();
        }
    }

    // Prevención submit
    if (form) {
        form.addEventListener('submit', (ev) => {
            ev.preventDefault();
            if (calculateBtn && !calculateBtn.disabled) handleCalculate();
        });
    }
    if (calculateBtn) {
        calculateBtn.addEventListener('click', (e) => {
            e.preventDefault();
            handleCalculate();
        });
    }

    // Llamada al backend para calcular y guardar usando el SP via controlador
    async function handleCalculate() {
        if (!calculateBtn) return;

        const scrInput = document.getElementById('scr');
        const gastosInput = document.getElementById('gastos_admin_mensual');

        const scr = scrInput ? parseFloat(scrInput.value) || 0 : 0;
        const gastos_admin = gastosInput ? parseFloat(gastosInput.value) || 0 : 0;

        if (errorMessage) { errorMessage.classList.add('hidden'); errorMessage.textContent = ''; }
        calculateBtn.disabled = true;
        calculateBtn.classList.add('opacity-50');

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    scr: scr,
                    sctr_monto: scr,
                    gastos_admin: gastos_admin,
                    gastos_admin_mensual: gastos_admin,
                    _token: token
                })
            });

            let json;
            try { json = await response.json(); } catch (e) { throw new Error('Respuesta no válida del servidor'); }

            if (!response.ok) {
                const msg = json?.error || json?.message || 'Error en el servidor';
                throw new Error(msg);
            }

            const payload = json.data ?? json;
            const mat = Number(payload.materiales || 0);
            const plan = Number(payload.planilla || 0);
            const ge = Number(payload.gastos_extra || 0);
            const s = Number(payload.scr || 0);
            const ga = Number(payload.gastos_administrativos ?? payload.gastos_admin ?? 0);
            const total = Number(payload.total_egresos || (mat + plan + ge + s + ga));
            const utilidadFromServer = payload.utilidad !== undefined ? Number(payload.utilidad) : null;
            const utilidadFinal = utilidadFromServer !== null ? utilidadFromServer : (montoInicial - Number(total || 0));

            // actualizar vista con payload (sin recargar)
            if (egresosTableBody) {
                egresosTableBody.innerHTML = `
                    <tr>
                        <td>Materiales</td>
                        <td>${fmt(mat)}</td>
                        <td>Suma total de materiales</td>
                    </tr>
                    <tr>
                        <td>Planilla</td>
                        <td>${fmt(plan)}</td>
                        <td>Incluye pago, alimentación, hospedaje, pasajes</td>
                    </tr>
                    <tr>
                        <td>SCTR</td>
                        <td>${fmt(s)}</td>
                        <td>Monto fijo por proyecto</td>
                    </tr>
                    <tr>
                        <td>Gastos Administrativos</td>
                        <td>${fmt(ga)}</td>
                        <td>Monto único por proyecto</td>
                    </tr>
                    <tr>
                        <td>Gastos Extra</td>
                        <td>${fmt(ge)}</td>
                        <td>Otros gastos asociados</td>
                    </tr>
                `;
                totalEgresos.innerHTML = `<strong>${fmt(total)}</strong>`;
                utilidadEl.innerHTML = `<strong>${fmt(utilidadFinal)}</strong>`;
                egresosTableContainer.classList.remove('hidden');
            }

            // actualizar resumen preliminar visual
            if (preScr) preScr.textContent = Number(s).toFixed(2);
            if (preGastosAdmin) preGastosAdmin.textContent = Number(ga).toFixed(2);
            if (preTotal) preTotal.textContent = Number(total).toFixed(2);
            if (preMateriales) preMateriales.textContent = Number(mat).toFixed(2);
            if (prePlanilla) prePlanilla.textContent = Number(plan).toFixed(2);
            if (preGastosExtra) preGastosExtra.textContent = Number(ge).toFixed(2);
            if (preMontoInicial) preMontoInicial.textContent = Number(montoInicial || 0).toFixed(2);
            if (preUtilidad) preUtilidad.textContent = fmt(utilidadFinal);

            // reporte
            if (egresosReportBody) {
                egresosReportBody.innerHTML = `
                    <tr draggable="true" class="egreso-report-row" data-id="${payload.id_egreso || ''}">
                        <td class="py-2 px-4 border-b" contenteditable="true">Egreso ${payload.id_egreso || ''} - ${new Date().toLocaleDateString()}</td>
                        <td class="py-2 px-4 border-b">${fmt(total)}</td>
                    </tr>
                `;
                egresosReportContainer.classList.remove('hidden');
                initDragAndDrop();
            }

            // bloquear inputs y ocultarlos (el botón queda)
            if (scrInput) scrInput.setAttribute('disabled', 'disabled');
            if (gastosInput) gastosInput.setAttribute('disabled', 'disabled');
            if (egresosInputsContainer) egresosInputsContainer.classList.add('hidden');
            if (calculateBtn) calculateBtn.textContent = 'Recalcular Egresos';

            // <- OPCIONAL: si quieres forzar que el usuario vea los datos persistidos en BD,
            // descomenta la línea siguiente para recargar la página:
            // location.reload();

        } catch (err) {
            console.error('Error en calcular egresos:', err);
            if (errorMessage) {
                errorMessage.textContent = 'Error: ' + (err.message || 'Ocurrió un error');
                errorMessage.classList.remove('hidden');
            } else {
                alert('Error: ' + (err.message || 'Ocurrió un error'));
            }
        } finally {
            if (calculateBtn && calculateBtn.isConnected) {
                calculateBtn.disabled = false;
                calculateBtn.classList.remove('opacity-50');
            }
        }
    }

    // Drag & drop helper
    function initDragAndDrop() {
        const rows = document.querySelectorAll('.egreso-report-row');
        rows.forEach(row => {
            row.addEventListener('dragstart', (e) => e.dataTransfer.setData('text/plain', row.dataset.id));
            row.addEventListener('dragover', (e) => e.preventDefault());
            row.addEventListener('drop', (e) => {
                e.preventDefault();
                const draggedId = e.dataTransfer.getData('text/plain');
                const draggedRow = document.querySelector(`.egreso-report-row[data-id="${draggedId}"]`);
                const targetRow = e.target.closest('.egreso-report-row');
                if (draggedRow && targetRow && draggedRow !== targetRow) {
                    const parent = egresosReportBody;
                    const draggedIndex = Array.from(parent.children).indexOf(draggedRow);
                    const targetIndex = Array.from(parent.children).indexOf(targetRow);
                    if (draggedIndex < targetIndex) {
                        parent.insertBefore(draggedRow, targetRow.nextSibling);
                    } else {
                        parent.insertBefore(draggedRow, targetRow);
                    }
                }
            });
        });
    }

    // Export PDF / Excel (usa proyectoNombre y proyectoId)
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const table = document.getElementById('egresosReportTable');
            const rows = table.querySelectorAll('tr');
            let y = 20;

            doc.setFontSize(16);
            doc.text('Reporte de Egresos - ' + proyectoNombre, 10, 10);
            doc.setFontSize(12);
            doc.text('Fecha: ' + new Date().toLocaleDateString(), 10, 20);

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    doc.setFontSize(10);
                    cells.forEach((cell, index) => {
                        const text = cell.textContent.trim();
                        if (index === 0) {
                            doc.text(text, 10, y);
                        } else {
                            doc.text(text, 180, y, { align: 'right' });
                        }
                    });
                    y += 8;
                    if (y > 280) {
                        doc.addPage();
                        y = 20;
                    }
                }
            });

            doc.save(`Reporte_Egresos_{{ $proyectoId ?? 0 }}_${new Date().toISOString().split('T')[0]}.pdf`);
        });
    }

    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', () => {
            const table = document.getElementById('egresosReportTable');
            const wb = XLSX.utils.table_to_book(table, { sheet: 'Reporte Egresos' });
            XLSX.writeFile(wb, `Reporte_Egresos_{{ $proyectoId ?? 0 }}_${new Date().toISOString().split('T')[0]}.xlsx`);
        });
    }
});
</script>

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
        margin-bottom: 1rem;
    }

    .form-group label {
        font-weight: 600;
        color: #444;
        margin-bottom: 0.5rem;
    }

    .box {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .box-header {
        border-bottom: 1px solid #f4f4f4;
        padding: 15px;
    }

    .box-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }

    .table td {
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .btn {
        border-radius: 4px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .alert {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .modal-content {
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }

    .modal-header {
        border-bottom: none;
        border-radius: 8px 8px 0 0;
    }

    .modal-footer {
        border-top: none;
        border-radius: 0 0 8px 8px;
    }

    .bg-warning {
        background-color: #f39c12 !important;
        color: white !important;
    }

    .bg-success {
        background-color: #27ae60 !important;
        color: white !important;
    }

    .bg-info {
        background-color: #3498db !important;
        color: white !important;
    }

    .bg-danger {
        background-color: #e74c3c !important;
        color: white !important;
    }
</style>
@endpush



