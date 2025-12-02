<div id="planilla-rj">
<div class="row">
    <!-- Resumen de plazas (abarca las 3 columnas en pantallas grandes) -->
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Resumen de Personal</h3>
                <div class="box-tools pull-right">
                    <button id="marcar-asistencia-btn" type="button" class="btn btn-success btn-sm {{ $isFinalized ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $isFinalized ? 'disabled' : '' }}>
                        <i class="fa fa-check"></i> Marcar Asistencia
                    </button>
                    <button type="button" class="btn btn-primary btn-sm {{ $isFinalized ? 'opacity-50 cursor-not-allowed' : '' }}" onclick="openPlanillaModal()" {{ $isFinalized ? 'disabled' : '' }}>
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Cantidad de Plazas:</strong> {{ $proyecto->cantidad_trabajadores }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Plazas Ocupadas:</strong> <span id="plazas-ocupadas">{{ $planilla->total() }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Trabajadores Adicionales:</strong> <span id="trabajadores-adicionales">{{ $planilla->total() > $proyecto->cantidad_trabajadores ? $planilla->total() - $proyecto->cantidad_trabajadores : 0 }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Presupuesto Personal -->
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Presupuesto Personal</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-4"><p><strong>Asignado:</strong> S/<span id="per-assigned">{{ isset($budgetPersonal['assigned']) ? number_format($budgetPersonal['assigned'], 2) : '0.00' }}</span></p></div>
                    <div class="col-sm-4"><p><strong>Gastado(Personal + Extras):</strong> <span class="text-red-600">S/<span id="per-spent">{{ isset($budgetPersonal['spent']) ? number_format($budgetPersonal['spent'], 2) : '0.00' }}</span></span></p></div>
                    <div class="col-sm-4"><p><strong>Restante:</strong> <span class="text-green-600">S/<span id="per-remaining">{{ isset($budgetPersonal['remaining']) ? number_format($budgetPersonal['remaining'], 2) : '0.00' }}</span></span></p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensaje de error global (evitar duplicar ID con el modal) -->
    <div id="asistencia-global-error" class="hidden text-red-600 mt-2"></div>

    <!-- Planilla de trabajadores (ahora ocupa todo el ancho disponible) -->
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Gestión de Personal</h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped planilla-zpend-table">
                        <thead>
                            <tr>
                                <th data-column="nombre">Nombre</th>
                                <th data-column="pago_dia">Pago por día</th>
                                <th data-column="dias">Días</th>
                                <th data-column="pago">Pago</th>
                                <th data-column="alimentacion">Alimentación</th>
                                <th data-column="hospedaje">Hospedaje</th>
                                <th data-column="pasajes">Pasajes</th>
                                <th data-column="estado">Estado</th>
                                <th data-column="acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="trabajadores-table">
                            @foreach ($planilla as $planillaItem)
                                <tr data-id="{{ $planillaItem->id_planilla }}">
                                    <td>{{ $planillaItem->trabajador->nombre_trab }} {{ $planillaItem->trabajador->apellido_trab }}</td>
                                    <td class="text-right">S/{{ number_format($planillaItem->pago_dia ?? 0, 2) }}</td>
                                    <td class="text-center">{{ $planillaItem->dias_trabajados }}</td>
                                    <td class="text-right">S/{{ number_format($planillaItem->pago, 2) }}</td>
                                    <td class="text-right">S/{{ number_format($planillaItem->alimentacion_trabajador, 2) }}</td>
                                    <td class="text-right">S/{{ number_format($planillaItem->hospedaje_trabajador, 2) }}</td>
                                    <td class="text-right">S/{{ number_format($planillaItem->pasajes_trabajador, 2) }}</td>
                                    <td>{{ $planillaItem->estado }}</td>
                                    <td class="action-buttons planilla-action-buttons">
                                        <button type="button" class="btn text-2xl set-pago-dia-btn {{ $isFinalized ? 'opacity-50 cursor-not-allowed' : '' }}" onclick="openPagoDiaModal({{ $planillaItem->id_planilla }}, '{{ $planillaItem->trabajador->nombre_trab }} {{ $planillaItem->trabajador->apellido_trab }}', {{ (float)($planillaItem->pago_dia ?? 0) }})" title="Establecer pago diario" {{ ($planillaItem->pago_dia ?? 0) > 0 || $isFinalized ? 'disabled' : '' }}>
                                            <i class="fas fa-plus action-icon"></i>
                                        </button>
                                        <button type="button" class="open-update-modal text-yellow-500 hover:text-yellow-600 text-2xl {{ $isFinalized ? 'opacity-50 cursor-not-allowed' : '' }}" data-planilla-id="{{ $planillaItem->id_planilla }}" {{ $isFinalized ? 'disabled' : '' }}>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn text-red-500 hover:text-red-600 text-2xl {{ $isFinalized ? 'opacity-50 cursor-not-allowed' : '' }}" onclick="removeTrabajador({{ $planillaItem->id_planilla }}, event)" {{ $isFinalized ? 'disabled' : '' }}>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <!-- <button type="button" class="btn btn-info btn-xs open-details-modal"
                                            data-planilla-id="{{ $planillaItem->id_planilla }}"
                                            data-nombre="{{ $planillaItem->trabajador->nombre_trab }} {{ $planillaItem->trabajador->apellido_trab }}"
                                            data-dni="{{ $planillaItem->trabajador->dni_trab }}"
                                            data-pago="{{ number_format($planillaItem->pago, 2) }}"
                                            data-alimentacion="{{ number_format($planillaItem->alimentacion_trabajador, 2) }}"
                                            data-hospedaje="{{ number_format($planillaItem->hospedaje_trabajador, 2) }}"
                                            data-pasajes="{{ number_format($planillaItem->pasajes_trabajador, 2) }}"
                                            data-estado="{{ $planillaItem->estado }}">
                                            <i class="fa fa-eye"></i>
                                        </button> -->
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Paginación -->
                    <div class="text-center mt-4">
                        {{ $planilla->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</div>

<!-- Modal para agregar trabajador a la planilla -->
<div class="modal fade" id="trabajadores-modal" tabindex="-1" role="dialog" aria-labelledby="trabajadores-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content custom-modal">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white" id="trabajadores-modal-label">Agregar Trabajador al Personal</h4>
            </div>
            <form id="add-trabajador-form">
            <div class="modal-body">
                <div class="form-group">
                    <label for="trabajador_id" class="font-semibold">Seleccionar Trabajador</label>
                    <select id="trabajador_id" class="form-control" required>
                        <option value="" disabled selected>Seleccione al trabajador a Agregar</option>
                        @if(isset($trabajadoresPreload) && count($trabajadoresPreload))
                            @foreach($trabajadoresPreload as $t)
                                <option value="{{ $t->id_trabajadores }}" data-nombre="{{ trim(($t->nombre_trab ?? '') . ' ' . ($t->apellido_trab ?? '')) }}">
                                    {{ trim(($t->nombre_trab ?? '') . ' ' . ($t->apellido_trab ?? '')) }} ({{ $t->dni_trab ?? '' }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div id="trabajadores-error-message" class="alert alert-danger hidden"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-add-trabajador">Agregar</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Pago Día -->
<div class="modal fade" id="pago-dia-modal" tabindex="-1" role="dialog" aria-labelledby="pago-dia-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content custom-modal">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white" id="pago-dia-modal-label">Establecer Pago Diario</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pago_dia_planilla_id">
                <div class="form-group">
                    <label class="font-semibold" id="pago-dia-trabajador"></label>
                </div>
                <div class="form-group">
                    <label for="pago_dia_input" class="font-semibold">Pago por día (S/)</label>
                    <input type="number" step="0.01" id="pago_dia_input" class="form-control" placeholder="0.00">
                </div>
                <div id="pago-dia-error" class="alert alert-danger hidden"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarPagoDia()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Marcar Asistencia -->
<div class="modal fade" id="asistencia-modal" tabindex="-1" role="dialog" aria-labelledby="asistencia-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content custom-modal">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white" id="asistencia-modal-label">Marcar Asistencia (Hoy)</h4>
            </div>
            <div class="modal-body">
                <div id="asistencia-warning" class="alert alert-info">
                    Una vez marcada la asistencia hoy, no podrás volver a marcarla hasta mañana.
                </div>
                <div class="form-group" style="margin:8px 0;">
                    <label class="font-semibold" style="display:block; margin-bottom:6px;">Selecciona el personal para marcar asistencia</label>
                    <div style="margin-bottom:6px;">
                        <label style="font-weight:500; cursor:pointer;">
                            <input type="checkbox" id="asistencia-select-all" onclick="toggleSeleccionAsistencia(this)"> Seleccionar todo
                        </label>
                    </div>
                    <div id="asistencia-lista" style="max-height:260px; overflow:auto; border:1px solid #e5e7eb; border-radius:4px; padding:8px; background:#fff;"></div>
                </div>
                <div id="asistencia-error" class="alert alert-danger hidden"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="marcarAsistenciaHoy()">Marcar seleccionados</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar nuevos gastos del trabajador -->
<div class="modal fade" id="update-modal" tabindex="-1" role="dialog" aria-labelledby="update-modal-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content custom-modal">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white" id="update-modal-title">Agregar Nuevos Gastos del Trabajador</h4>
            </div>
            <form id="update-form">
                <div class="modal-body">
                    <input type="hidden" id="update_planilla_id" name="planilla_id">
                    <div class="form-group">
                        <label for="alimentacion_trabajador" class="font-semibold">Alimentación</label>
                        <input type="number" step="0.01" id="alimentacion_trabajador" name="alimentacion_trabajador" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label for="hospedaje_trabajador" class="font-semibold">Hospedaje</label>
                        <input type="number" step="0.01" id="hospedaje_trabajador" name="hospedaje_trabajador" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label for="pasajes_trabajador" class="font-semibold">Pasajes</label>
                        <input type="number" step="0.01" id="pasajes_trabajador" name="pasajes_trabajador" class="form-control" placeholder="0.00" required>
                    </div>
                    <div id="update-error-message" class="alert alert-danger hidden"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para agregar sueldos -->
<div class="modal fade" id="sueldos-modal" tabindex="-1" role="dialog" aria-labelledby="sueldos-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content custom-modal">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white" id="sueldos-modal-label">Confirmar Agregar Sueldos</h4>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <p><strong>Días totales del proyecto:</strong> <span id="dias-totales"></span></p>
                    <p><strong>Sueldo diario por trabajador:</strong> S/<span id="sueldo-diario"></span></p>
                    <p><strong>Plazas definidas:</strong> <span id="plazas-definidas">{{ $proyecto->cantidad_trabajadores }}</span></p>
                    <p><strong>Trabajadores en planilla:</strong> <span id="trabajadores-planilla"></span></p>
                    <p><strong>Sueldo total proyectado:</strong> S/<span id="sueldo-total"></span></p>
                    <p><strong>Sueldo por trabajador:</strong> S/<span id="sueldo-por-trabajador"></span></p>
                </div>
                <div id="sueldos-error" class="alert alert-danger hidden"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarSueldos()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de detalles del trabajador -->
<div class="modal fade" id="detalles-modal" tabindex="-1" role="dialog" aria-labelledby="detalles-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content custom-modal">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white" id="detalles-modal-label">Detalles del Trabajador</h4>
            </div>
            <div class="modal-body">
                <ul class="list-unstyled">
                    <li><strong>Nombre:</strong> <span id="detalle-nombre"></span></li>
                    <li><strong>DNI:</strong> <span id="detalle-dni"></span></li>
                    <li><strong>Pago:</strong> S/<span id="detalle-pago"></span></li>
                    <li><strong>Alimentación:</strong> S/<span id="detalle-alimentacion"></span></li>
                    <li><strong>Hospedaje:</strong> S/<span id="detalle-hospedaje"></span></li>
                    <li><strong>Pasajes:</strong> S/<span id="detalle-pasajes"></span></li>
                    <li><strong>Estado:</strong> <span id="detalle-estado"></span></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="eliminar-modal" tabindex="-1" role="dialog" aria-labelledby="eliminar-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminar-modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar a este trabajador de la planilla?</p>
                <div id="delete-error-message" class="alert alert-danger hidden"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">Eliminar</button>
            </div>
        </div>
    </div>
</div>   
</div>
<script>
    // Verificar que el script se carga
    console.log('[planilla.blade.php] Script iniciado a las', new Date().toLocaleString());

    // Fallbacks para UI
    window.showToast = typeof window.showToast === 'function' ? window.showToast : function(msg, type, ms) { console.log('[toast]', type || 'info', msg); };
    window.showSystemNotice = function(msg) { showToast(msg, 'info', 5000); };
    window.showTransientError = function(msg) { showToast(msg, 'error', 6000); };

    // Interceptar alert y confirm para depurar y bloquear
    const originalAlert = window.alert;
    window.alert = function(message) {
        console.error('[planilla] ALERT DETECTADO a las', new Date().toLocaleString(), ':', message);
        showToast('Alerta no esperada detectada: ' + message, 'error', 6000);
        return false;
    };
    const originalConfirm = window.confirm;
    window.confirm = function(message) {
        console.error('[planilla] CONFIRM DETECTADO a las', new Date().toLocaleString(), ':', message);
        showToast('Confirmación no esperada detectada: ' + message, 'error', 6000);
        return false;
    };

    // Variables globales
    const proyectoId = {{ $proyecto->id_proyecto }};
    const BASE = window.location.origin + '{{ request()->getBaseUrl() }}';
    const ADD_PLANILLA_PATH = "{{ route('proyectos.addPlanilla', $proyecto->id_proyecto, false) }}";
    const ADD_PLANILLA_URL = `${BASE}${ADD_PLANILLA_PATH}`;
    const TRABAJADORES_PRELOAD = @json(isset($trabajadoresPreload) ? $trabajadoresPreload : []);

    console.log('[planilla] Config:', { proyectoId, BASE, ADD_PLANILLA_URL, trabajadoresCount: TRABAJADORES_PRELOAD.length });

    // Función para escapar caracteres especiales
    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function escapeJS(str) {
        if (!str) return '';
        return str.replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n');
    }

    // Actualizar presupuesto personal
    async function refreshBudgetPersonal() {
        try {
            const res = await fetch(`${BASE}/api/proyectos/{{ $proyecto->id_proyecto }}/budgets`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('No se pudo obtener presupuesto');
            const data = await res.json();
            const p = data.personal || { assigned: 0, spent: 0, remaining: 0 };
            const a = document.getElementById('per-assigned');
            const s = document.getElementById('per-spent');
            const r = document.getElementById('per-remaining');
            if (a) a.textContent = Number(p.assigned).toFixed(2);
            if (s) s.textContent = Number(p.spent).toFixed(2);
            if (r) r.textContent = Number(p.remaining).toFixed(2);
        } catch (e) { console.error('[refreshBudgetPersonal] Error:', e); }
    }

    // Obtener presupuesto personal
    async function getBudgetPersonal() {
        const res = await fetch(`${BASE}/api/proyectos/{{ $proyecto->id_proyecto }}/budgets`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('No se pudo obtener presupuesto');
        const data = await res.json();
        return data && data.personal ? data.personal : { assigned: 0, spent: 0, remaining: 0 };
    }

    // Actualizar resumen
    function updateResumen() {
        const ocupadas = document.querySelectorAll('#trabajadores-table tr').length;
        const plazas = {{ $proyecto->cantidad_trabajadores }};
        const plazasOcupadasEl = document.getElementById('plazas-ocupadas');
        const trabajadoresAdicionalesEl = document.getElementById('trabajadores-adicionales');
        if (plazasOcupadasEl) plazasOcupadasEl.textContent = ocupadas;
        if (trabajadoresAdicionalesEl) trabajadoresAdicionalesEl.textContent = ocupadas > plazas ? ocupadas - plazas : 0;
    }

    // Renderizar opciones de trabajadores
    function renderTrabajadoresOptions(select, lista) {
        select.innerHTML = '<option value="" disabled selected>Seleccione al trabajador a Agregar</option>';
        if (!Array.isArray(lista) || lista.length === 0) return 0;

        // Obtener IDs de trabajadores ya en la planilla
        const existingIds = new Set(
            Array.from(document.querySelectorAll('#trabajadores-table tr'))
                .map(row => {
                    const id = row.getAttribute('data-id');
                    const btn = row.querySelector('.open-update-modal');
                    return btn ? parseInt(btn.dataset.planillaId) : null;
                })
                .filter(id => id !== null)
        );
        console.log('[planilla] IDs de trabajadores ya en planilla:', Array.from(existingIds));

        // Filtrar trabajadores que no están en la planilla
        const filteredLista = lista.filter(trabajador => {
            const id = trabajador.id_trabajadores || trabajador.id || trabajador.ID || trabajador.Id || null;
            return id && !existingIds.has(id);
        });

        filteredLista.forEach(trabajador => {
            const option = document.createElement('option');
            const id = trabajador.id_trabajadores || trabajador.id || trabajador.ID || trabajador.Id || null;
            const nombre = trabajador.nombre_trab || trabajador.nombre || trabajador.first_name || '';
            const apellido = trabajador.apellido_trab || trabajador.apellido || trabajador.last_name || '';
            const dni = trabajador.dni_trab || trabajador.dni || trabajador.DNI || '';
            if (!id) return;
            option.value = id;
            const label = `${nombre} ${apellido} (${dni})`.trim();
            option.textContent = label;
            option.label = label;
            option.dataset.nombre = `${nombre} ${apellido}`.trim();
            select.appendChild(option);
        });
        return select.options.length - 1;
    }

    // Abrir modal de trabajadores
    window.openPlanillaModal = async function() {
        console.log('[planilla] openPlanillaModal iniciado');
        const modal = document.getElementById('trabajadores-modal');
        const select = document.getElementById('trabajador_id');
        const btnAdd = document.getElementById('btn-add-trabajador');
        const errorMessage = document.getElementById('trabajadores-error-message');
        console.log('[planilla] Elementos:', { modal: !!modal, select: !!select, btnAdd: !!btnAdd });
        if (!modal || !select) return;

        try {
            const p = await getBudgetPersonal();
            if (p.remaining <= 0) {
                showToast('Se alcanzó el límite del presupuesto de Personal.', 'error', 6000);
            } else if (p.assigned > 0 && p.remaining <= p.assigned * 0.2) {
                showToast('Falta 20% para alcanzar el límite del presupuesto de Personal.', 'warning', 6000);
            }
        } catch(e) { console.warn('No se pudo verificar presupuesto antes de abrir modal:', e); }

        errorMessage.classList.add('hidden');
        
        // Usar TRABAJADORES_PRELOAD si tiene datos válidos
        if (Array.isArray(TRABAJADORES_PRELOAD) && TRABAJADORES_PRELOAD.length > 0) {
            const count = renderTrabajadoresOptions(select, TRABAJADORES_PRELOAD);
            console.log('[planilla] Opciones preload renderizadas:', count);
            if (btnAdd) btnAdd.disabled = count <= 1;
            if (count <= 1) {
                errorMessage.textContent = 'No hay trabajadores disponibles.';
                errorMessage.classList.remove('hidden');
            } else {
                $('#trabajadores-modal').modal('show');
                setTimeout(() => { try { select.focus(); } catch(e){} }, 50);
                return; // Evitar fetch si preload tiene datos
            }
        } else {
            select.innerHTML = '<option value="" disabled selected>Cargando trabajadores...</option>';
            if (btnAdd) btnAdd.disabled = true;
        }

        // Fetch solo si TRABAJADORES_PRELOAD está vacío
        fetch(`${BASE}/admin/trabajadores/list?proyecto_id=${proyectoId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            cache: 'no-store'
        })
        .then(response => {
            if (response.status === 401) return window.location.href = '{{ route("login.form") }}';
            const ct = (response.headers.get('content-type') || '').toLowerCase();
            if (!response.ok || !ct.includes('application/json')) throw new Error('Error al cargar los trabajadores');
            return response.json();
        })
        .then(async data => {
            console.log('Trabajadores disponibles recibidos (primario):', data);
            let listaRaw = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : []);
            let lista = listaRaw;
            if (lista.length === 0) {
                try {
                    const resAll = await fetch(`${BASE}/admin/trabajadores/list`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' });
                    const ct2 = (resAll.headers.get('content-type') || '').toLowerCase();
                    if (resAll.ok && ct2.includes('application/json')) {
                        const all = await resAll.json();
                        console.log('Trabajadores disponibles (fallback):', all);
                        lista = Array.isArray(all) ? all : (Array.isArray(all?.data) ? all.data : []);
                    }
                } catch(e) { console.warn('Fallback trabajadores.list sin proyecto_id falló:', e); }
            }
            const count = renderTrabajadoresOptions(select, lista);
            console.log('[planilla] Opciones renderizadas:', count);
            if (btnAdd) btnAdd.disabled = count <= 1;
            if (count <= 1) {
                errorMessage.textContent = 'No hay trabajadores disponibles.';
                errorMessage.classList.remove('hidden');
                return;
            }
            $('#trabajadores-modal').modal('show');
            setTimeout(() => { try { select.focus(); } catch(e){} }, 50);
        })
        .catch(async error => {
            console.error('Error carga trabajadores (primario):', error);
            try {
                const resAll = await fetch(`${BASE}/admin/trabajadores/list`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' });
                const ct2 = (resAll.headers.get('content-type') || '').toLowerCase();
                if (resAll.ok && ct2.includes('application/json')) {
                    const all = await resAll.json();
                    const lista = Array.isArray(all) ? all : (Array.isArray(all?.data) ? all.data : []);
                    const count = renderTrabajadoresOptions(select, lista);
                    console.log('[planilla] Opciones fallback renderizadas:', count);
                    if (btnAdd) btnAdd.disabled = count <= 1;
                    if (count <= 1) {
                        errorMessage.textContent = 'No hay trabajadores disponibles.';
                        errorMessage.classList.remove('hidden');
                        return;
                    }
                    $('#trabajadores-modal').modal('show');
                    setTimeout(() => { try { select.focus(); } catch(e){} }, 50);
                    return;
                }
            } catch (e2) {
                console.warn('Fallback trabajadores.list (catch) también falló:', e2);
            }
            errorMessage.textContent = 'Error al cargar los trabajadores.';
            errorMessage.classList.remove('hidden');
        });
    };

    // Función para agregar trabajador
    window.addTrabajador = function() {
        console.log('[planilla] addTrabajador() invoked - Paso 1: Inicio');

        const btn = document.getElementById('btn-add-trabajador');
        let originalText = '';
        if (btn) {
            if (btn.disabled) {
                console.warn('[planilla] addTrabajador: botón deshabilitado, ignorado');
                return;
            }
            btn.disabled = true;
            originalText = btn.textContent;
            btn.textContent = 'Agregando...';
            console.log('[planilla] Button disabled - Paso 2: Preparando request');
        } else {
            console.error('[planilla] No se encontró #btn-add-trabajador');
            showToast('No se encontró el botón de agregar. Recarga la página.', 'error', 6000);
            return;
        }

        const trabajadorSelect = document.getElementById('trabajador_id');
        const errorMessage = document.getElementById('trabajadores-error-message');

        if (!trabajadorSelect) {
            console.error('[planilla] No se encontró #trabajador_id');
            showToast('No se encontró el selector de trabajador. Recarga la página.', 'error', 6000);
            btn.disabled = false;
            btn.textContent = originalText;
            return;
        }

        const trabajadorId = parseInt(trabajadorSelect.value);
        const trabajadorNombre = trabajadorSelect.selectedOptions[0]?.dataset.nombre || 'Trabajador';

        if (!trabajadorId || isNaN(trabajadorId)) {
            console.log('[planilla] addTrabajador: no trabajador seleccionado - Paso 3: Validación fallida');
            if (errorMessage) {
                errorMessage.textContent = 'Por favor, selecciona un trabajador.';
                errorMessage.classList.remove('hidden');
            }
            showToast('Por favor, selecciona un trabajador.', 'error', 5000);
            btn.disabled = false;
            btn.textContent = originalText;
            return;
        }

        if (errorMessage) errorMessage.classList.add('hidden');

        const requestData = {
            trabajador_id: trabajadorId,
            _token: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        };

        console.log('[planilla] POST a:', ADD_PLANILLA_URL, 'data:', requestData, ' - Paso 4: Enviando fetch');

        fetch(ADD_PLANILLA_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': requestData._token
            },
            credentials: 'same-origin',
            body: JSON.stringify(requestData)
        })
        .then(async (response) => {
            console.log('[planilla] Response recibida - Paso 5:', { status: response.status, ok: response.ok, redirected: response.redirected, url: response.url });
            if (response.redirected && response.url.includes('/login')) {
                console.warn('[planilla] Redirect a login detectado - Paso 6: No autorizado');
                window.location.href = response.url;
                throw new Error('No autorizado - redirigiendo a login');
            }
            const ct = (response.headers.get('content-type') || '').toLowerCase();
            console.log('[planilla] Content-Type:', ct, ' - Paso 7');
            let body;
            if (ct.includes('application/json')) {
                body = await response.json();
            } else {
                const text = await response.text();
                console.warn('[planilla] Respuesta no JSON:', text.substring(0, 200), ' - Paso 8');
                throw new Error('Respuesta no es JSON válida: ' + text.substring(0, 100));
            }
            console.log('[planilla] Response body:', body, ' - Paso 9');
            if (!response.ok) {
                throw new Error(`${body.error || 'Error desconocido'} [HTTP ${response.status}]`);
            }
            return body;
        })
        .then(data => {
            console.log('[planilla] Data procesada exitosamente - Paso 10:', data);
            if (!data.success) {
                throw new Error(data.error || 'Error al agregar el trabajador');
            }

            const table = document.getElementById('trabajadores-table');
            if (table) {
                const row = table.insertRow();
                row.setAttribute('data-id', data.id);
                row.innerHTML = `
                    <td>${escapeHtml(data.nombre_completo)}</td>
                    <td class="text-right">S/${Number(data.pago_dia || 0).toFixed(2)}</td>
                    <td class="text-center">${data.dias_trabajados}</td>
                    <td class="text-right">S/${Number(data.pago).toFixed(2)}</td>
                    <td class="text-right">S/${Number(data.alimentacion_trabajador).toFixed(2)}</td>
                    <td class="text-right">S/${Number(data.hospedaje_trabajador).toFixed(2)}</td>
                    <td class="text-right">S/${Number(data.pasajes_trabajador).toFixed(2)}</td>
                    <td>${data.estado}</td>
                    <td class="action-buttons planilla-action-buttons">
                        <button type="button" class="btn btn-default btn-xs set-pago-dia-btn" onclick="openPagoDiaModal(${data.id}, '${escapeJS(data.nombre_completo)}', ${Number(data.pago_dia || 0)})" ${data.pago_dia > 0 ? 'disabled' : ''}>
                            <i class="fa fa-plus action-icon"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-xs open-update-modal" data-planilla-id="${data.id}">
                            <i class="fa fa-edit action-icon"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-xs" onclick="removeTrabajador(${data.id}, event)">
                            <i class="fa fa-trash action-icon"></i>
                        </button>
                    </td>
                `;
                initializeUpdateModalEvents(); // Reasignar eventos a los nuevos botones
            }

            trabajadorSelect.value = '';
            showToast(`Trabajador ${escapeHtml(trabajadorNombre)} agregado correctamente`, 'success', 5000);
            $('#trabajadores-modal').modal('hide');
            refreshBudgetPersonal();
            updateResumen();
            document.dispatchEvent(new CustomEvent('personalSaved'));
            console.log('[planilla] Trabajador agregado, tabla actualizada - Paso 11');
        })
        .catch(error => {
            console.error('[planilla] Error en fetch - Paso 12:', error.message);
            showToast(`Error: ${error.message}`, 'error', 6000);
            if (error.message.includes('no es JSON válida')) {
                showToast('Error en el servidor. Contacta al administrador.', 'error', 6000);
            }
        })
        .finally(() => {
            console.log('[planilla] Proceso finalizado - Paso 13');
            if (btn) {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    };

    // Inicializar eventos para botones de "Editar"
    function initializeUpdateModalEvents() {
        console.log('[planilla] initializeUpdateModalEvents ejecutado');
        document.querySelectorAll('.open-update-modal').forEach(button => {
            button.removeEventListener('click', handleUpdateModalClick);
            button.addEventListener('click', handleUpdateModalClick);
        });
    }

    function handleUpdateModalClick(event) {
        event.preventDefault();
        console.log('[planilla] Botón Editar clickeado');
        const planillaId = this.dataset.planillaId;
        openUpdateModal(planillaId);
    }

    // Inicializar eventos para botones de "Detalles" (aunque están comentados)
    function initializeDetailsModalEvents() {
        console.log('[planilla] initializeDetailsModalEvents ejecutado');
        document.querySelectorAll('.open-details-modal').forEach(button => {
            button.removeEventListener('click', handleDetailsModalClick);
            button.addEventListener('click', handleDetailsModalClick);
        });
    }

    function handleDetailsModalClick(event) {
        event.preventDefault();
        console.log('[planilla] Botón Detalles clickeado');
        const btn = event.currentTarget;
        openDetailsModal({
            nombre: btn.dataset.nombre || '',
            dni: btn.dataset.dni || '',
            pago: btn.dataset.pago || '0.00',
            alimentacion: btn.dataset.alimentacion || '0.00',
            hospedaje: btn.dataset.hospedaje || '0.00',
            pasajes: btn.dataset.pasajes || '0.00',
            estado: btn.dataset.estado || ''
        });
    }

    // Abrir modal de detalles
    function openDetailsModal(data) {
        console.log('[planilla] Abriendo modal de detalles:', data);
        const modal = document.getElementById('detalles-modal');
        if (!modal) return;
        document.getElementById('detalle-nombre').textContent = data.nombre;
        document.getElementById('detalle-dni').textContent = data.dni;
        document.getElementById('detalle-pago').textContent = data.pago;
        document.getElementById('detalle-alimentacion').textContent = data.alimentacion;
        document.getElementById('detalle-hospedaje').textContent = data.hospedaje;
        document.getElementById('detalle-pasajes').textContent = data.pasajes;
        document.getElementById('detalle-estado').textContent = data.estado;
        $('#detalles-modal').modal('show');
    }

    // Abrir modal de pago diario
    window.openPagoDiaModal = function(planillaId, nombre, currentPagoDia) {
        console.log('[planilla] openPagoDiaModal:', { planillaId, nombre, currentPagoDia });
        document.getElementById('pago_dia_planilla_id').value = planillaId;
        document.getElementById('pago-dia-trabajador').textContent = `Trabajador: ${nombre}`;
        document.getElementById('pago_dia_input').value = (Number(currentPagoDia) || 0).toFixed(2);
        document.getElementById('pago-dia-error').classList.add('hidden');
        $('#pago-dia-modal').modal('show');
        setTimeout(() => { try { document.getElementById('pago_dia_input').focus(); } catch(e){} }, 50);
    };

    // Guardar pago diario
    window.guardarPagoDia = function() {
        console.log('[planilla] guardarPagoDia iniciado');
        const planillaId = document.getElementById('pago_dia_planilla_id').value;
        const pagoDia = parseFloat(document.getElementById('pago_dia_input').value || '0');
        const errorBox = document.getElementById('pago-dia-error');
        errorBox.classList.add('hidden');
        if (isNaN(pagoDia) || pagoDia < 0) {
            errorBox.textContent = 'Ingrese un monto válido.';
            errorBox.classList.remove('hidden');
            showToast('Ingrese un monto válido.', 'error', 6000);
            return;
        }
        const url = `${BASE}/admin/proyectos/${proyectoId}/planilla/${planillaId}/pago-dia`;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ pago_dia: pagoDia })
        })
        .then(r => r.json().then(j => ({ ok: r.ok, body: j })))
        .then(({ ok, body }) => {
            console.log('[planilla] guardarPagoDia respuesta:', { ok, body });
            if (!ok) throw new Error(body.error || 'Error al guardar pago diario');
            const row = document.querySelector(`#trabajadores-table tr[data-id="${planillaId}"]`);
            if (row) {
                const pagoDiaCell = row.querySelector('td:nth-child(2)');
                if (pagoDiaCell) pagoDiaCell.textContent = `S/${pagoDia.toFixed(2)}`;
                const btn = row.querySelector('.set-pago-dia-btn');
                if (btn) btn.setAttribute('disabled', 'disabled');
            }
            $('#pago-dia-modal').modal('hide');
            refreshBudgetPersonal();
            document.dispatchEvent(new CustomEvent('personalSaved'));
            showToast('Pago diario guardado correctamente', 'success', 5000);
        })
        .catch(err => {
            console.error('[planilla] Error en guardarPagoDia:', err.message);
            errorBox.textContent = err.message;
            errorBox.classList.remove('hidden');
            showToast(err.message, 'error', 6000);
        });
    };

    // Abrir modal de actualizar gastos
    async function openUpdateModal(planillaId) {
        console.log('[planilla] openUpdateModal:', { planillaId });

        // VALIDATION: Check attendance
        try {
            // 1. Check if attendance is marked for the project today
            const statusRes = await fetch(`${BASE}/admin/proyectos/${proyectoId}/asistencia/status`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const status = await statusRes.json();

            if (status && status.today_marked) {
                // 2. If marked, check if this worker is absent
                // Use local date for "today" to match the attendance marking logic
                const d = new Date();
                const today = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                
                const calendarRes = await fetch(`/api/proyectos/${proyectoId}/calendar/day/${today}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if (calendarRes.ok) {
                    const calendarData = await calendarRes.json();
                    // Check if worker is in 'ausentes' list
                    // The list contains objects with { id_planilla: ... }
                    const isAbsent = calendarData.ausentes.some(a => a.id_planilla == planillaId);
                    
                    if (isAbsent) {
                        showToast('No puedes agregar gastos a este trabajador porque no asistió hoy.', 'error', 6000);
                        return; // BLOCK
                    }
                }
            }
        } catch (e) {
            console.error('[planilla] Error validating attendance:', e);
            // Proceed if validation fails (fail open)
        }

        const modal = document.getElementById('update-modal');
        const form = document.getElementById('update-form');
        const title = document.getElementById('update-modal-title');
        const errorMessage = document.getElementById('update-error-message');
        const planillaIdInput = document.getElementById('update_planilla_id');
        if (!modal) return;
        $('#update-modal').modal('show');
        form.reset();
        errorMessage.classList.add('hidden');
        planillaIdInput.value = planillaId;
        title.textContent = 'Agregar Nuevos Gastos del Trabajador';
        setTimeout(() => document.getElementById('alimentacion_trabajador').focus(), 50);
    }

    // Manejar submit del formulario de gastos
    const updateForm = document.getElementById('update-form');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('[planilla] update-form submit');
            const planillaId = document.getElementById('update_planilla_id').value;
            const alimentacion = document.getElementById('alimentacion_trabajador').value;
            const hospedaje = document.getElementById('hospedaje_trabajador').value;
            const pasajes = document.getElementById('pasajes_trabajador').value;
            const errorMessage = document.getElementById('update-error-message');
            const suma = (parseFloat(alimentacion) || 0) + (parseFloat(hospedaje) || 0) + (parseFloat(pasajes) || 0);
            getBudgetPersonal().then(p => {
                if (suma > (p.remaining || 0) + 1e-6) {
                    errorMessage.textContent = 'No se pueden agregar gastos: se supera el límite del presupuesto de Personal.';
                    errorMessage.classList.remove('hidden');
                    showToast('No se pueden agregar gastos: se supera el límite del presupuesto.', 'error', 6000);
                    throw new Error('BudgetExceeded');
                }
                if ((p.assigned || 0) > 0 && (p.remaining - suma) <= (p.assigned * 0.2)) {
                    showToast('Aviso: con esta acción, quedarás a menos del 20% del presupuesto de Personal.', 'warning', 6000);
                }
                return null;
            }).then(() => {
                const url = `${BASE}/admin/proyectos/${proyectoId}/planilla/${planillaId}/update-gastos`;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        alimentacion_trabajador: parseFloat(alimentacion) || 0,
                        hospedaje_trabajador: parseFloat(hospedaje) || 0,
                        pasajes_trabajador: parseFloat(pasajes) || 0
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(j => { throw new Error(j.error || 'Error al actualizar los gastos'); });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('[planilla] update-gastos éxito:', data);
                    if (data.success) {
                        const row = document.querySelector(`#trabajadores-table tr[data-id="${planillaId}"]`);
                        if (row) {
                            row.querySelector('td:nth-child(5)').textContent = `S/${Number(data.alimentacion_trabajador).toFixed(2)}`;
                            row.querySelector('td:nth-child(6)').textContent = `S/${Number(data.hospedaje_trabajador).toFixed(2)}`;
                            row.querySelector('td:nth-child(7)').textContent = `S/${Number(data.pasajes_trabajador).toFixed(2)}`;
                        }
                        $('#update-modal').modal('hide');
                        refreshBudgetPersonal();
                        document.dispatchEvent(new CustomEvent('personalSaved'));
                        showToast('Gastos actualizados correctamente', 'success', 5000);
                    } else {
                        errorMessage.textContent = data.error || 'Error al actualizar los gastos';
                        errorMessage.classList.remove('hidden');
                        showToast(data.error || 'Error al actualizar los gastos', 'error', 6000);
                    }
                })
                .catch(error => {
                    console.error('[planilla] Error en update-gastos:', error.message);
                    if (error.message !== 'BudgetExceeded') {
                        errorMessage.textContent = error.message;
                        errorMessage.classList.remove('hidden');
                        showToast(error.message, 'error', 6000);
                    }
                });
            });
        });
    }

    // Abrir modal de asistencia
    window.openAsistenciaModal = async function() {
        console.log('[planilla] openAsistenciaModal iniciado');
        const rowsCount = document.querySelectorAll('#trabajadores-table tr').length;
        if (rowsCount === 0) {
            console.log('[planilla] openAsistenciaModal: no hay trabajadores en la tabla');
            showToast('No puedes marcar asistencia. Primero agrega personal.', 'error', 6000);
            return;
        }
        try {
            const p = await getBudgetPersonal();
            console.log('[planilla] Presupuesto:', p);
            if (p.remaining <= 0) {
                console.log('[planilla] openAsistenciaModal: presupuesto agotado');
                showToast('Se alcanzó el límite del presupuesto de Personal.', 'error', 6000);
                return;
            }
            if (p.assigned > 0 && p.remaining <= p.assigned * 0.2) {
                showToast('Falta 20% para alcanzar el límite del presupuesto de Personal.', 'warning', 6000);
            }
        } catch(e) { console.warn('[planilla] No se pudo verificar presupuesto antes de asistencia:', e); }
        const rows = Array.from(document.querySelectorAll('#trabajadores-table tr'));
        const todosConPagoDia = rows.length > 0 && rows.every(r => {
            const t = r.querySelector('td:nth-child(2)')?.textContent || '';
            const monto = parseFloat(t.replace('S/', '').replace(/,/g, '').trim()) || 0;
            return monto > 0;
        });
        console.log('[planilla] Todos con pago_dia > 0:', todosConPagoDia);
        if (!todosConPagoDia) {
            showToast('Primero agrega el monto que ganará por día tu personal', 'error', 6000);
            return;
        }
        fetch(`${BASE}/admin/proyectos/${proyectoId}/asistencia/status`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(r => r.json()).then(status => {
            console.log('[planilla] Estado asistencia:', status);
            if (status && status.today_marked) {
                showToast('No puedes marcar la asistencia porque ya se marcó hoy. Espera hasta mañana.', 'error', 6000);
                return;
            }
            document.getElementById('asistencia-warning').classList.remove('hidden');
            const cont = document.getElementById('asistencia-lista');
            const selectAll = document.getElementById('asistencia-select-all');
            if (cont) {
                cont.innerHTML = '';
                document.querySelectorAll('#trabajadores-table tr').forEach(row => {
                    const planillaId = row.getAttribute('data-id');
                    const nombre = row.querySelector('td:first-child')?.textContent?.trim() || 'Trabajador';
                    const item = document.createElement('label');
                    item.style.display = 'flex';
                    item.style.alignItems = 'center';
                    item.style.gap = '8px';
                    item.style.margin = '4px 0';
                    item.innerHTML = `<input type="checkbox" class="asistencia-item" value="${planillaId}"> <span>${nombre}</span>`;
                    cont.appendChild(item);
                });
                if (selectAll) selectAll.checked = false;
            }
            $('#asistencia-modal').modal('show');
        }).catch(err => {
            console.error('[planilla] Error al verificar estado asistencia:', err);
            showToast('Error al verificar estado de asistencia: ' + err.message, 'error', 6000);
            $('#asistencia-modal').modal('show');
        });
    };

    // Marcar asistencia
    function marcarAsistenciaHoy() {
        console.log('[planilla] marcarAsistenciaHoy iniciado');
        // FIX: Use local date instead of UTC to match backend timezone (America/Lima)
        const d = new Date();
        const hoy = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        const errorBox = document.getElementById('asistencia-error');
        errorBox.classList.add('hidden');
        const totalRows = document.querySelectorAll('#trabajadores-table tr').length;
        if (totalRows === 0) {
            console.log('[planilla] marcarAsistenciaHoy: no hay trabajadores');
            showToast('No puedes marcar asistencia. Primero agrega personal.', 'error', 6000);
            return;
        }
        const checks = Array.from(document.querySelectorAll('.asistencia-item:checked'));
        if (checks.length === 0) {
            console.log('[planilla] marcarAsistenciaHoy: no se seleccionaron trabajadores');
            showToast('Selecciona al menos un trabajador.', 'error', 6000);
            return;
        }
        const idToRow = {};
        document.querySelectorAll('#trabajadores-table tr').forEach(r => { idToRow[r.getAttribute('data-id')] = r; });
        const anySinPagoDia = checks.some(chk => {
            const row = idToRow[chk.value];
            const pagoDiaText = row?.querySelector('td:nth-child(2)')?.textContent || '';
            const monto = parseFloat(pagoDiaText.replace('S/', '').replace(/,/g, '').trim()) || 0;
            return monto <= 0;
        });
        if (anySinPagoDia) {
            console.log('[planilla] marcarAsistenciaHoy: algunos trabajadores sin pago_dia');
            showToast('Primero agrega el monto que ganará por día tu personal', 'error', 6000);
            return;
        }
        const requests = checks.map(chk => {
            const planillaId = chk.value;
            const url = `${BASE}/admin/proyectos/${proyectoId}/planilla/${planillaId}/marcar-asistencia`;
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ fecha: hoy })
            }).then(r => r.json().then(j => ({ ok: r.ok, status: r.status, body: j, row: idToRow[planillaId] })));
        });
        Promise.all(requests).then(results => {
            console.log('[planilla] marcarAsistenciaHoy resultados:', results);
            const anyConflict = results.some(r => r.status === 409);
            const anySinPago = results.some(r => r.status === 422 && r.body && r.body.error);
            const budgetErrors = results.filter(r => r.status === 400);
            const anySuccess = results.some(r => r.ok && r.body && r.body.success);
            if (anySuccess) {
                showToast('Asistencia marcada correctamente', 'success', 5000);
            }
            if (anySinPago) {
                showToast('Primero agrega el monto que ganará por día tu personal', 'error', 6000);
            }
            if (budgetErrors.length > 0) {
                const msg = budgetErrors[0].body && budgetErrors[0].body.error ? budgetErrors[0].body.error : 'Se alcanzó el límite del presupuesto de Personal.';
                showToast(msg, 'error', 6000);
            }
            if (anyConflict) {
                showToast('No puedes marcar la asistencia porque ya se marcó hoy. Espera hasta mañana.', 'error', 6000);
            }
            if (!anyConflict && !anySinPago) {
                $('#asistencia-modal').modal('hide');
            }
            results.forEach(({ ok, body, row }) => {
                if (ok && body && body.success) {
                    const diasCell = row.querySelector('td:nth-child(3)');
                    const pagoCell = row.querySelector('td:nth-child(4)');
                    if (diasCell) diasCell.textContent = String(body.dias_trabajados ?? diasCell.textContent);
                    if (pagoCell) pagoCell.textContent = `S/${Number(body.pago ?? 0).toFixed(2)}`;
                }
            });
            refreshBudgetPersonal();
            document.dispatchEvent(new CustomEvent('personalSaved'));
        }).catch(err => {
            console.error('[planilla] Error en marcarAsistenciaHoy:', err.message);
            showToast('Error al marcar asistencia: ' + err.message, 'error', 6000);
        });
    }

    // Confirmar sueldos
    function confirmarSueldos() {
        console.log('[planilla] confirmarSueldos iniciado');
        const sueldosErrorEl = document.getElementById('sueldos-error');
        sueldosErrorEl.classList.add('hidden');
        const sueldoPorTrabajador = parseFloat(document.getElementById('sueldo-por-trabajador').textContent);
        console.log('[planilla] Sueldo por trabajador:', sueldoPorTrabajador);
        fetch(`/admin/proyectos/${proyectoId}/agregar-sueldos`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                proyecto_id: proyectoId,
                sueldo_por_trabajador: sueldoPorTrabajador
            })
        })
        .then(response => {
            console.log('[planilla] confirmarSueldos respuesta:', response.status);
            if (!response.ok) return response.text().then(t => { throw new Error('HTTP ' + response.status + ' - ' + t); });
            return response.json();
        })
        .then(data => {
            console.log('[planilla] confirmarSueldos éxito:', data);
            if (data.success) {
                document.querySelectorAll('#trabajadores-table tr').forEach(row => {
                    const pagoCell = row.querySelector('td:nth-child(4)');
                    if (pagoCell) pagoCell.textContent = `S/${sueldoPorTrabajador.toFixed(2)}`;
                });
                $('#sueldos-modal').modal('hide');
                showToast('Sueldos agregados correctamente', 'success', 5000);
            } else {
                sueldosErrorEl.textContent = data.error || 'No se pudo aplicar sueldos';
                sueldosErrorEl.classList.remove('hidden');
                showToast(data.error || 'No se pudo aplicar sueldos', 'error', 6000);
            }
        })
        .catch(err => {
            console.error('[planilla] Error en confirmarSueldos:', err);
            sueldosErrorEl.textContent = 'Error al confirmar sueldos: ' + err.message;
            sueldosErrorEl.classList.remove('hidden');
            showToast('Error al confirmar sueldos: ' + err.message, 'error', 6000);
        });
    }

    // Eliminar trabajador
    let pendingDeleteId = null;
    function removeTrabajador(planillaId, event) {
        // Manejar caso donde event no esté definido (por ejemplo, si se llama directamente)
        if (event) {
            event.preventDefault();
            console.log('[planilla] removeTrabajador a las', new Date().toLocaleString(), ':', { planillaId, eventType: event.type, target: event.target.tagName });
        } else {
            console.warn('[planilla] removeTrabajador llamado sin evento a las', new Date().toLocaleString(), ':', { planillaId });
        }
        if (typeof planillaId !== 'number' || isNaN(planillaId)) {
            console.error('[planilla] ID inválido en removeTrabajador a las', new Date().toLocaleString(), ':', planillaId);
            showToast('ID de trabajador inválido.', 'error', 6000);
            return;
        }
        pendingDeleteId = planillaId;
        console.log('[planilla] Intentando abrir modal de eliminación a las', new Date().toLocaleString(), ':', { pendingDeleteId });
        try {
            const modal = document.getElementById('eliminar-modal');
            if (!modal) {
                console.error('[planilla] Modal #eliminar-modal no encontrado a las', new Date().toLocaleString());
                showToast('El modal de eliminación no está disponible. Recarga la página.', 'error', 6000);
                return;
            }
            openDeleteModal();
        } catch (e) {
            console.error('[planilla] Error al abrir modal a las', new Date().toLocaleString(), ':', e.message);
            showToast('Error al abrir el modal de eliminación.', 'error', 6000);
        }
    }

    function openDeleteModal() {
        console.log('[planilla] openDeleteModal a las', new Date().toLocaleString());
        const err = document.getElementById('delete-error-message');
        if (err) err.classList.add('hidden');
        const modal = $('#eliminar-modal');
        if (modal.length) {
            modal.modal('show');
        } else {
            console.error('[planilla] Modal #eliminar-modal no encontrado en jQuery a las', new Date().toLocaleString());
            showToast('El modal de eliminación no está disponible. Recarga la página.', 'error', 6000);
        }
    }

    function confirmDeleteTrabajador() {
        console.log('[planilla] confirmDeleteTrabajador a las', new Date().toLocaleString(), ':', { pendingDeleteId });
        if (!pendingDeleteId) {
            showToast('No se seleccionó un trabajador para eliminar.', 'error', 6000);
            $('#eliminar-modal').modal('hide');
            return;
        }
        const planillaId = pendingDeleteId;
        const url = `${BASE}/admin/proyectos/${proyectoId}/remove-planilla/${planillaId}`;
        const row = document.querySelector(`#trabajadores-table tr[data-id="${planillaId}"]`);
        const nombre = row ? row.querySelector('td:first-child')?.textContent?.trim() || 'Trabajador' : 'Trabajador';
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content || '{{ csrf_token() }}'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('[planilla] confirmDeleteTrabajador respuesta a las', new Date().toLocaleString(), ':', { 
                status: response.status, 
                ok: response.ok, 
                redirected: response.redirected, 
                url: response.url,
                headers: [...response.headers.entries()]
            });
            if (response.redirected) {
                console.warn('[planilla] Redirección detectada a las', new Date().toLocaleString(), ':', response.url);
                showToast('El servidor intentó redirigir. Verifica la configuración del servidor.', 'error', 6000);
                throw new Error('Redirección no esperada');
            }
            const ct = (response.headers.get('content-type') || '').toLowerCase();
            console.log('[planilla] Content-Type a las', new Date().toLocaleString(), ':', ct);
            if (!ct.includes('application/json')) {
                return response.text().then(text => {
                    console.error('[planilla] Respuesta no JSON a las', new Date().toLocaleString(), ':', text.substring(0, 200));
                    throw new Error('Respuesta no es JSON válida: ' + text.substring(0, 100));
                });
            }
            if (!response.ok) {
                return response.json().then(j => {
                    throw new Error(j.error || `Error ${response.status}: Error desconocido`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('[planilla] confirmDeleteTrabajador éxito a las', new Date().toLocaleString(), ':', data);
            if (data.success) {
                if (row) row.remove();
                updateResumen();
                refreshBudgetPersonal();
                document.dispatchEvent(new CustomEvent('personalSaved'));
                $('#eliminar-modal').modal('hide');
                showToast(`Trabajador ${escapeHtml(nombre)} eliminado correctamente`, 'success', 5000);
                pendingDeleteId = null;
            } else {
                throw new Error(data.error || 'Error al eliminar el trabajador');
            }
        })
        .catch(error => {
            console.error('[planilla] Error en confirmDeleteTrabajador a las', new Date().toLocaleString(), ':', error.message);
            const err = document.getElementById('delete-error-message');
            if (err) {
                err.textContent = 'Error al eliminar: ' + (error.message || 'Error desconocido');
                err.classList.remove('hidden');
            }
            showToast(`Error al eliminar: ${error.message}`, 'error', 6000);
        });
    }

    // Toasts flotantes
    function getToastContainer() {
        let c = document.getElementById('toast-container');
        if (!c) {
            c = document.createElement('div');
            c.id = 'toast-container';
            c.style.position = 'fixed';
            c.style.top = '16px';
            c.style.right = '16px';
            c.style.zIndex = '9999';
            c.style.display = 'flex';
            c.style.flexDirection = 'column';
            c.style.gap = '8px';
            document.body.appendChild(c);
        }
        return c;
    }

    function showToast(message, type = 'info', duration = 5000) {
        console.log('[planilla] showToast a las', new Date().toLocaleString(), ':', { message, type, duration });
        const container = getToastContainer();
        const toast = document.createElement('div');
        toast.className = `alert ${
            type === 'error' ? 'alert-danger' : (
            type === 'success' ? 'alert-success' : (
            type === 'warning' ? 'alert-warning' : 'alert-info'))}`;
        toast.style.minWidth = '280px';
        toast.style.maxWidth = '420px';
        toast.style.margin = '0';
        toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        toast.style.opacity = '1';
        toast.setAttribute('role', 'alert');
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 300ms ease';
            toast.style.opacity = '0';
            setTimeout(() => { toast.remove(); }, 320);
        }, Math.max(1000, duration));
    }

    // Seleccionar todos los checkboxes de asistencia
    function toggleSeleccionAsistencia(cb) {
        console.log('[planilla] toggleSeleccionAsistencia a las', new Date().toLocaleString(), ':', cb.checked);
        const all = document.querySelectorAll('.asistencia-item');
        all.forEach(x => x.checked = cb.checked);
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[planilla] DOMContentLoaded a las', new Date().toLocaleString());
        const btnAdd = document.getElementById('btn-add-trabajador');
        console.log('[planilla] Estado inicial btn-add-trabajador a las', new Date().toLocaleString(), ':', {
            exists: !!btnAdd,
            disabled: btnAdd ? btnAdd.disabled : null
        });

        initializeUpdateModalEvents();
        initializeDetailsModalEvents();

        const addForm = document.getElementById('add-trabajador-form');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('[planilla] Formulario enviado a las', new Date().toLocaleString());
                window.addTrabajador();
            });
            addForm.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    console.log('[planilla] Enter bloqueado, llamando addTrabajador a las', new Date().toLocaleString());
                    window.addTrabajador();
                }
            });
        }

        $(document).ready(function() {
            $('#btn-add-trabajador').off('click').on('click', function(e) {
                e.preventDefault();
                console.log('[planilla] jQuery click en btn-add-trabajador a las', new Date().toLocaleString());
                window.addTrabajador();
            });
            $('#confirm-delete-btn').off('click').on('click', function(e) {
                e.preventDefault();
                console.log('[planilla] jQuery click en confirm-delete-btn a las', new Date().toLocaleString());
                confirmDeleteTrabajador();
            });
            // Interceptar eventos de eliminación para evitar comportamientos no deseados
            $('.delete-trabajador-btn').off('click').on('click', function(e) {
                e.preventDefault();
                const planillaId = $(this).closest('tr').data('id');
                console.log('[planilla] jQuery click en delete-trabajador-btn a las', new Date().toLocaleString(), ':', planillaId);
                removeTrabajador(planillaId, e);
            });
        });

        $('#trabajadores-modal').on('shown.bs.modal', function () {
            const sel = document.getElementById('trabajador_id');
            if (sel && !sel.value && sel.options.length > 1) {
                sel.selectedIndex = 1;
            }
        });

        $('#trabajadores-modal').on('hidden.bs.modal', function () {
            const errorMessage = document.getElementById('trabajadores-error-message');
            if (errorMessage) errorMessage.classList.add('hidden');
        });

        $('#update-modal').on('hidden.bs.modal', function () {
            const form = document.getElementById('update-form');
            const errorMessage = document.getElementById('update-error-message');
            if (form) form.reset();
            if (errorMessage) errorMessage.classList.add('hidden');
        });

        $('#sueldos-modal').on('hidden.bs.modal', function () {
            const errorMessage = document.getElementById('sueldos-error');
            if (errorMessage) errorMessage.classList.add('hidden');
        });

        $('#eliminar-modal').on('hidden.bs.modal', function () {
            const errorMessage = document.getElementById('delete-error-message');
            if (errorMessage) errorMessage.classList.add('hidden');
            pendingDeleteId = null;
        });

        const asistenciaBtn = document.getElementById('marcar-asistencia-btn');
        if (asistenciaBtn) {
            const fn = window.openAsistenciaModal;
            if (typeof fn === 'function') {
                asistenciaBtn.addEventListener('click', fn);
            } else if (window.$ && typeof $('#asistencia-modal').modal === 'function') {
                asistenciaBtn.addEventListener('click', function() { $('#asistencia-modal').modal('show'); });
            }
        }

        refreshBudgetPersonal();
    });
    // === ESCUCHAR ACTUALIZACIONES DESDE OTRAS PESTAÑAS ===
    window.addEventListener('budget-personal-updated', () => {
        console.log('[planilla] Evento recibido: presupuesto actualizado desde otra pestaña');
        refreshBudgetPersonal();
    });
</script>

@push('styles')
<!-- Estilos locales solo para este archivo -->
<style>
    /* Contenedor exclusivo para no afectar otras páginas */
    #planilla-rj {
        box-sizing: border-box;
    }

    /* Tabla (aislada) */
    #planilla-rj table.planilla-zpend-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem; /* un poco más grande, similar a gastos_extra */
        table-layout: fixed; /* control de anchos */
    }

    /* Heredar estilos base de gastos_extra para acciones */
    #planilla-rj .action-buttons .btn { margin-right: 5px; }
    #planilla-rj .action-buttons .btn:last-child { margin-right: 0; }

    /* Celdas */
    #planilla-rj table.planilla-zpend-table th,
    #planilla-rj table.planilla-zpend-table td {
        box-sizing: border-box;
        vertical-align: middle !important;
        padding: 8px 10px !important; /* más respiro */
        overflow: hidden; /* evitar que se rompa */
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Anchos sugeridos por columna (no afecta otras tablas) */
    #planilla-rj table.planilla-zpend-table th[data-column="nombre"],
    #planilla-rj table.planilla-zpend-table td:nth-child(1) { width: 26%; }
    #planilla-rj table.planilla-zpend-table th[data-column="pago_dia"],
    #planilla-rj table.planilla-zpend-table td:nth-child(2) { width: 10%; }
    #planilla-rj table.planilla-zpend-table th[data-column="dias"],
    #planilla-rj table.planilla-zpend-table td:nth-child(3) { width: 6%; }
    #planilla-rj table.planilla-zpend-table th[data-column="pago"],
    #planilla-rj table.planilla-zpend-table td:nth-child(4) { width: 10%; }
    #planilla-rj table.planilla-zpend-table th[data-column="alimentacion"],
    #planilla-rj table.planilla-zpend-table td:nth-child(5) { width: 10%; }
    #planilla-rj table.planilla-zpend-table th[data-column="hospedaje"],
    #planilla-rj table.planilla-zpend-table td:nth-child(6) { width: 10%; }
    #planilla-rj table.planilla-zpend-table th[data-column="pasajes"],
    #planilla-rj table.planilla-zpend-table td:nth-child(7) { width: 10%; }
    #planilla-rj table.planilla-zpend-table th[data-column="estado"],
    #planilla-rj table.planilla-zpend-table td:nth-child(8) { width: 8%; }
    #planilla-rj table.planilla-zpend-table th[data-column="acciones"],
    #planilla-rj table.planilla-zpend-table td:nth-child(9) { width: 10%; }

    /* Alineación por tipo */
    #planilla-rj table.planilla-zpend-table td.text-right { text-align: right !important; }
    #planilla-rj table.planilla-zpend-table td.text-center { text-align: center !important; }

    /* Encabezado pegajoso para mejorar lectura en tablas largas */
    #planilla-rj table.planilla-zpend-table thead th {
        position: sticky;
        top: 0;
        background: #f5f5f5;
        z-index: 2;
    }

    /* Columna acciones (fija y centrada) */
    #planilla-rj table.planilla-zpend-table th[data-column="acciones"],
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons {
        width: 12px !important; /* Ajustado para 3 botones */
        text-align: center !important;
        padding: 2px !important;
    }

    /* Contenedor de botones (flex) */
    #planilla-rj .planilla-action-buttons {
        display: flex !important;
        gap: 4px !important; /* botones más compactos */
        align-items: center !important;
        justify-content: center !important;
        white-space: nowrap !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Botones de acción */
    /* Fuerza tamaño mínimo y fijo de los botones en acciones (por encima de AdminLTE) */
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons button.btn.btn-xs,
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn.btn-warning.btn-xs,
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn.btn-danger.btn-xs,
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn.btn-info.btn-xs,
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn.btn-default.btn-xs {
        width: 14px !important;
        height: 14px !important;
        min-width: 14px !important;
        min-height: 14px !important;
        padding: 0 !important;
        border-radius: 3px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 14px !important;
        margin: 0 !important;
    }
    /* Ícono aún más pequeño y sin márgenes heredados */
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn i,
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn i.fa,
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn i.fas {
        font-size: 8px !important;
        line-height: 1 !important;
        margin: 0 !important;
    }
    /* Quita separación por defecto de AdminLTE entre icono y texto (no usamos texto) */
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn .fa { margin-right: 0 !important; }

    /* Íconos dentro del botón */
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn i,
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn i.fa,
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn i.fas {
        font-size: 9px !important; /* más pequeño */
        line-height: 1 !important;
        margin: 0 !important;
    }

    /* Hover para botones */
    #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn:hover {
        opacity: 0.9 !important;
    }

    /* Hover row */
    #planilla-rj .planilla-zpend-table tbody tr:hover {
        background-color: #f8f9fa !important;
    }

    /* Ajustes para pantallas pequeñas */
    @media (max-width: 768px) {
        #planilla-rj table.planilla-zpend-table th[data-column="acciones"],
        #planilla-rj table.planilla-zpend-table td.planilla-action-buttons {
            width: 80px !important;
        }

        #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn {
            min-width: 20px !important;
            height: 20px !important;
        }

        #planilla-rj table.planilla-zpend-table td.planilla-action-buttons .btn i {
            font-size: 0.75rem !important;
        }
    }

    /* Quitar esquinas blancas del modal */
    .planilla-custom-modal {
        background-color: #f8f9fa !important; /* o #fff si prefieres */
        border-radius: 0 !important;          /* esquinas rectas */
        border: none !important;              /* sin borde blanco */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important; /* sombra */
    }
    /* Encabezado que cubre todo el ancho sin esquinas blancas */
    .planilla-custom-modal .modal-header {
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
    }
    #btn-add-trabajador {
    pointer-events: auto !important;
    cursor: pointer !important;
    }
    .hidden {
        display: none;
    }
    .alert {
        margin-bottom: 0;
    }
</style>
@endpush