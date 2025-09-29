<div id="planilla-rj">
<div class="row">
    <!-- Resumen de plazas (abarca las 3 columnas en pantallas grandes) -->
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Resumen de Personal</h3>
                <div class="box-tools pull-right">
                    <button id="marcar-asistencia-btn" type="button" class="btn btn-success btn-sm">
                        <i class="fa fa-check"></i> Marcar Asistencia
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openModal()">
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

    <!-- Mensaje de error -->
    <div id="asistencia-error" class="hidden text-red-600 mt-2"></div>

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
                                        <button type="button" class="btn btn-default btn-xs set-pago-dia-btn" onclick="openPagoDiaModal({{ $planillaItem->id_planilla }}, '{{ $planillaItem->trabajador->nombre_trab }} {{ $planillaItem->trabajador->apellido_trab }}', {{ (float)($planillaItem->pago_dia ?? 0) }})" title="Establecer pago diario" {{ ($planillaItem->pago_dia ?? 0) > 0 ? 'disabled' : '' }}>
                                            <i class="fa fa-plus action-icon"></i>
                                        </button>
                                        <button type="button" class="open-update-modal btn btn-warning btn-xs" data-planilla-id="{{ $planillaItem->id_planilla }}">
                                            <i class="fa fa-edit action-icon"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-xs" onclick="removeTrabajador({{ $planillaItem->id_planilla }})">
                                            <i class="fa fa-trash action-icon"></i>
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
        <div class="modal-content planilla-custom-modal">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white" id="trabajadores-modal-label">Agregar Trabajador al Personal</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="trabajador_id" class="font-semibold">Seleccionar Trabajador</label>
                    <select id="trabajador_id" class="form-control" required>
                        <option value="" disabled selected>Seleccione al trabajador a Agregar</option>
                    </select>
                </div>
                <div id="error-message" class="alert alert-danger hidden"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="addTrabajador()">Agregar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Pago Día -->
<div class="modal fade" id="pago-dia-modal" tabindex="-1" role="dialog" aria-labelledby="pago-dia-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content planilla-custom-modal">
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
        <div class="modal-content planilla-custom-modal">
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
        <div class="modal-content planilla-custom-modal">
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
        <div class="modal-content planilla-custom-modal">
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
        <div class="modal-content planilla-custom-modal">
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
<div class="modal fade" id="eliminar-modal" tabindex="-1" role="dialog" aria-labelledby="eliminar-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content planilla-custom-modal">
            <div class="modal-header bg-red">
                <h4 class="modal-title text-white" id="eliminar-modal-label">Confirmar eliminación</h4>
            </div>
            <div class="modal-body">
                <p>¿Deseas eliminar este registro de la planilla?</p>
                <div id="delete-error-message" class="alert alert-danger hidden" style="margin-top:10px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteTrabajador()">Eliminar</button>
            </div>
        </div>
    </div>
</div>    
</div>
<script>
    let proyectoId = {{ $proyecto->id_proyecto }};
    let pendingDeleteId = null;
    let asistenciaBloqueadaHoy = false;

    // Funciones para el modal de agregar trabajador
    function openModal() {
        const modal = document.getElementById('trabajadores-modal');
        const select = document.getElementById('trabajador_id');
        const errorMessage = document.getElementById('error-message');
        if (!modal || !select) return;
        errorMessage.classList.add('hidden');
        select.innerHTML = '<option value="" disabled selected>Seleccione al trabajador a Agregar</option>';
        // Asegurar que al seleccionar se muestre el nombre en el mismo select y se oculte el error
        select.onchange = function() {
            if (this.value && errorMessage) {
                errorMessage.classList.add('hidden');
            }
        };
        $('#trabajadores-modal').modal('show');
        setTimeout(() => { try { select.focus(); } catch(e){} }, 150);
        fetch(`{{ route("trabajadores.list") }}?proyecto_id=${proyectoId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (response.status === 401) return window.location.href = '{{ route("login") }}';
            if (!response.ok) throw new Error('Error al cargar los trabajadores');
            return response.json();
        })
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                errorMessage.textContent = 'No hay trabajadores disponibles.';
                errorMessage.classList.remove('hidden');
                return;
            }
            data.forEach(trabajador => {
                const option = document.createElement('option');
                option.value = trabajador.id_trabajadores;
                const label = `${trabajador.nombre_trab} ${trabajador.apellido_trab} (${trabajador.dni_trab})`;
                option.textContent = label;
                option.label = label;
                option.dataset.nombre = `${trabajador.nombre_trab} ${trabajador.apellido_trab}`;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            errorMessage.textContent = 'Error al cargar los trabajadores.';
            errorMessage.classList.remove('hidden');
        });
    }

    function closeModal() {
        $('#trabajadores-modal').modal('hide');
    }

    function addTrabajador() {
        const trabajadorSelect = document.getElementById('trabajador_id');
        const trabajadorId = parseInt(trabajadorSelect.value);
        const trabajadorNombre = trabajadorSelect.selectedOptions[0]?.dataset.nombre;
        const errorMessage = document.getElementById('error-message');
        if (!trabajadorId) {
            errorMessage.textContent = 'Por favor, selecciona un trabajador.';
            errorMessage.classList.remove('hidden');
            return;
        }
        fetch(`{{ route("proyectos.addPlanilla", ":proyectoId") }}`.replace(':proyectoId', proyectoId), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ trabajador_id: trabajadorId })
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al agregar el trabajador');
            return response.json();
        })
        .then(data => {
            const table = document.getElementById('trabajadores-table');
            table.innerHTML += `
                <tr data-id="${data.id}">
                    <td>${trabajadorNombre}</td>
                    <td class="text-right">S/0.00</td>
                    <td class="text-center">0</td>
                    <td class="text-right">S/${Number(data.pago).toFixed(2)}</td>
                    <td>S/${Number(data.alimentacion_trabajador).toFixed(2)}</td>
                    <td>S/${Number(data.hospedaje_trabajador).toFixed(2)}</td>
                    <td>S/${Number(data.pasajes_trabajador).toFixed(2)}</td>
                    <td>${data.estado}</td>
                    <td class="action-buttons planilla-action-buttons">
                        <button type="button" class="btn btn-default btn-xs set-pago-dia-btn" onclick="openPagoDiaModal(${data.id}, '${trabajadorNombre}', 0)"><i class="fa fa-plus action-icon"></i></button>
                        <button type="button" class="open-update-modal btn btn-warning btn-xs" data-planilla-id="${data.id}"><i class="fa fa-edit action-icon"></i></button>
                        <button type="button" class="btn btn-danger btn-xs" onclick="removeTrabajador(${data.id})"><i class="fa fa-trash action-icon"></i></button>
                        <button type="button" class="btn btn-info btn-xs open-details-modal"
                            data-planilla-id="${data.id}"
                            data-nombre="${trabajadorNombre}"
                            data-dni="${data.dni_trab}"
                            data-pago="${Number(data.pago).toFixed(2)}"
                            data-alimentacion="${Number(data.alimentacion_trabajador).toFixed(2)}"
                            data-hospedaje="${Number(data.hospedaje_trabajador).toFixed(2)}"
                            data-pasajes="${Number(data.pasajes_trabajador).toFixed(2)}"
                            data-estado="${data.estado}">
                            <i class="fa fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
            updateResumen();
            initializeUpdateModalEvents();
            initializeDetailsModalEvents();
            closeModal();
        })
        .catch(error => {
            console.error('Error:', error);
            errorMessage.textContent = error.message;
            errorMessage.classList.remove('hidden');
        });
    }

    // Pago Día handlers
    function openPagoDiaModal(planillaId, nombre, currentPagoDia) {
        document.getElementById('pago_dia_planilla_id').value = planillaId;
        document.getElementById('pago-dia-trabajador').textContent = `Trabajador: ${nombre}`;
        document.getElementById('pago_dia_input').value = (Number(currentPagoDia) || 0).toFixed(2);
        document.getElementById('pago-dia-error').classList.add('hidden');
        $('#pago-dia-modal').modal('show');
        setTimeout(() => { try { document.getElementById('pago_dia_input').focus(); } catch(e){} }, 100);
    }

    function guardarPagoDia() {
        const planillaId = document.getElementById('pago_dia_planilla_id').value;
        const pagoDia = parseFloat(document.getElementById('pago_dia_input').value || '0');
        const errorBox = document.getElementById('pago-dia-error');
        errorBox.classList.add('hidden');
        if (isNaN(pagoDia) || pagoDia < 0) {
            errorBox.textContent = 'Ingrese un monto válido.';
            errorBox.classList.remove('hidden');
            return;
        }
        const url = `{{ route('proyectos.setPagoDia', ['proyecto' => ':proyectoId', 'planilla' => ':planillaId']) }}`
            .replace(':proyectoId', proyectoId)
            .replace(':planillaId', planillaId);
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ pago_dia: pagoDia })
        })
        .then(r => r.json().then(j => ({ ok: r.ok, body: j })))
        .then(({ ok, body }) => {
            if (!ok) throw new Error(body.error || 'Error al guardar pago diario');
            const row = document.querySelector(`#trabajadores-table tr[data-id="${planillaId}"]`);
            if (row) {
                // Columnas: 1 nombre, 2 pago_dia, 3 días, 4 pago, 5 ali, 6 hos, 7 pas, 8 estado, 9 acciones
                const pagoDiaCell = row.querySelector('td:nth-child(2)');
                if (pagoDiaCell) pagoDiaCell.textContent = `S/${pagoDia.toFixed(2)}`;
                // Deshabilitar botón establecer pago día
                const btn = row.querySelector('.set-pago-dia-btn');
                if (btn) btn.setAttribute('disabled', 'disabled');
            }
            $('#pago-dia-modal').modal('hide');
        })
        .catch(err => {
            errorBox.textContent = err.message;
            errorBox.classList.remove('hidden');
        });
    }

    // Funciones para el modal de agregar nuevos gastos
    function openUpdateModal(planillaId) {
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
        setTimeout(() => document.getElementById('alimentacion_trabajador').focus(), 100);
    }

    function closeUpdateModal() {
        $('#update-modal').modal('hide');
        const form = document.getElementById('update-form');
        if (form) form.reset();
        const errorMessage = document.getElementById('update-error-message');
        if (errorMessage) errorMessage.classList.add('hidden');
    }

    // Modal y acción para marcar asistencia (reemplaza Agregar Sueldos)
    function openAsistenciaModal() {
        // Pre-chequeo local: si ya se marcó hoy, no abrir
        if (asistenciaBloqueadaHoy) {
            showSystemNotice('No puedes marcar la asistencia porque ya se marcó hoy. Espera hasta mañana.');
            return;
        }
        // Pre-chequeo: exigir que TODOS los trabajadores tengan pago por día > 0
        const rows = Array.from(document.querySelectorAll('#trabajadores-table tr'));
        const todosConPagoDia = rows.length > 0 && rows.every(r => {
            const t = r.querySelector('td:nth-child(2)')?.textContent || '';
            const monto = parseFloat(t.replace('S/', '').replace(/,/g, '').trim()) || 0;
            return monto > 0;
        });
        if (!todosConPagoDia) {
            showSystemNotice('Primero agrega el monto que ganara por dia tu personal');
            return;
        }
        // Antes de abrir, consultar si ya se marcó hoy a nivel proyecto
        fetch(`{{ route('proyectos.asistenciaStatus', ':proyectoId') }}`.replace(':proyectoId', proyectoId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(status => {
            const errorBox = document.getElementById('asistencia-error');
            if (status && status.today_marked) {
                asistenciaBloqueadaHoy = true;
                showSystemNotice('No puedes marcar la asistencia porque ya se marcó hoy. Espera hasta mañana.');
                return; // no abrir modal
            }
            // Mostrar mensaje único: una vez marcada, hasta mañana
            document.getElementById('asistencia-warning').classList.remove('hidden');
            // Construir lista de personal desde la tabla actual
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
        }).catch(() => {
            // Fallback: si falla el status, proceder a abrir modal normal
            $('#asistencia-modal').modal('show');
        });
    }

    function marcarAsistenciaHoy() {
        const hoy = new Date().toISOString().slice(0,10);
        const errorBox = document.getElementById('asistencia-error');
        errorBox.classList.add('hidden');
        // Marcar solo seleccionados
        const checks = Array.from(document.querySelectorAll('.asistencia-item:checked'));
        if (checks.length === 0) {
            showTransientError('Selecciona al menos un trabajador.');
            return;
        }
        const idToRow = {};
        document.querySelectorAll('#trabajadores-table tr').forEach(r => { idToRow[r.getAttribute('data-id')] = r; });
        
        // Validar que todos los seleccionados tengan pago por día > 0
        const anySinPagoDia = checks.some(chk => {
            const row = idToRow[chk.value];
            const pagoDiaText = row?.querySelector('td:nth-child(2)')?.textContent || '';
            const monto = parseFloat(pagoDiaText.replace('S/', '').replace(/,/g, '').trim()) || 0;
            return monto <= 0;
        });
        if (anySinPagoDia) {
            showTransientError('Primero agrega el monto que ganara por dia tu personal');
            return;
        }
        const requests = checks.map(chk => {
            const planillaId = chk.value;
            const url = `{{ route('proyectos.marcarAsistencia', ['proyecto' => ':proyectoId', 'planilla' => ':planillaId']) }}`
                .replace(':proyectoId', proyectoId)
                .replace(':planillaId', planillaId);
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ fecha: hoy })
            }).then(r => r.json().then(j => ({ ok: r.ok, status: r.status, body: j, row: idToRow[planillaId] })));
        });
        Promise.all(requests).then(results => {
            const anyConflict = results.some(r => r.status === 409);
            const anySinPago = results.some(r => r.status === 422 && r.body && r.body.error);
            const anySuccess = results.some(r => r.ok && r.body && r.body.success);
            if (anySuccess) {
                asistenciaBloqueadaHoy = true;
                showToast('Asistencia marcada correctamente', 'success', 5000);
            }
            if (anySinPago) {
                showTransientError('Primero agrega el monto que ganara por dia tu personal');
            }
            if (anyConflict) {
                showTransientError('No puedes marcar la asistencia porque ya se marcó hoy. Espera hasta mañana.');
            }
            if (!anyConflict && !anySinPago) {
                $('#asistencia-modal').modal('hide');
            }
            // Actualizar UI por fila marcada
            results.forEach(({ ok, body, row }) => {
                if (ok && body && body.success) {
                    // Columnas actuales: 1 nombre, 2 pago_dia, 3 días, 4 pago
                    const diasCell = row.querySelector('td:nth-child(3)');
                    const pagoCell = row.querySelector('td:nth-child(4)');
                    if (diasCell) diasCell.textContent = String(body.dias_trabajados ?? diasCell.textContent);
                    if (pagoCell) pagoCell.textContent = `S/${Number(body.pago ?? 0).toFixed(2)}`;
                }
            });
        }).catch(err => {
            showTransientError('Error al marcar asistencia: ' + err.message);
        });
    }

    function closeSueldosModal() { $('#sueldos-modal').modal('hide'); }
    function toggleSeleccionAsistencia(cb){
        const all = document.querySelectorAll('.asistencia-item');
        all.forEach(x => x.checked = cb.checked);
    }

    // Notificación roja temporal (toast 5s)
    function showTransientError(message){
        showToast(message, 'error', 5000);
    }

    // Notificación de sistema (toast 5s)
    function showSystemNotice(message){
        showToast(message, 'error', 5000);
    }

    // Toasts flotantes
    function getToastContainer(){
        let c = document.getElementById('toast-container');
        if (!c){
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

    function showToast(message, type = 'info', duration = 5000){
        const container = getToastContainer();
        const toast = document.createElement('div');
        toast.className = `alert ${type === 'error' ? 'alert-danger' : (type === 'success' ? 'alert-success' : 'alert-info')}`;
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

    function confirmarSueldos() {
        const sueldosErrorEl = document.getElementById('sueldos-error');
        sueldosErrorEl.classList.add('hidden');

        // Tomamos el sueldo por trabajador calculado en el modal
        const sueldoPorTrabajador = parseFloat(document.getElementById('sueldo-por-trabajador').textContent);

        fetch(`{{ route('proyectos.agregarSueldos', ':proyectoId') }}`.replace(':proyectoId', proyectoId), {
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
            if (!response.ok) return response.text().then(t => { throw new Error('HTTP ' + response.status + ' - ' + t); });
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Actualizar en la vista sin recargar: aplicar sueldo por trabajador a cada fila (columna Pago)
                const sueldo = sueldoPorTrabajador.toFixed(2);
                document.querySelectorAll('#trabajadores-table tr').forEach(row => {
                    const pagoCell = row.querySelector('td:nth-child(2)');
                    if (pagoCell) pagoCell.textContent = `S/${sueldo}`;
                });
                closeSueldosModal();
            } else {
                sueldosErrorEl.textContent = data.error || 'No se pudo aplicar sueldos';
                sueldosErrorEl.classList.remove('hidden');
            }
        })
        .catch(err => {
            console.error('Error al confirmar sueldos:', err);
            sueldosErrorEl.textContent = 'Error al confirmar sueldos: ' + err.message;
            sueldosErrorEl.classList.remove('hidden');
        });
    }


    function removeTrabajador(planillaId) {
        pendingDeleteId = planillaId;
        openDeleteModal();
    }

    function openDeleteModal() {
        const err = document.getElementById('delete-error-message');
        if (err) err.classList.add('hidden');
        $('#eliminar-modal').modal('show');
    }

    function closeDeleteModal() {
        $('#eliminar-modal').modal('hide');
        pendingDeleteId = null;
    }

    function confirmDeleteTrabajador() {
        if (!pendingDeleteId) return;
        const planillaId = pendingDeleteId;
        const url = `{{ route('proyectos.removePlanilla', ['proyecto' => ':proyectoId', 'planilla' => ':planillaId']) }}`
            .replace(':proyectoId', proyectoId)
            .replace(':planillaId', planillaId);

        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content || '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Respuesta del servidor:', text);
                    throw new Error(`Error ${response.status}: ${text.substring(0, 100)}...`);
                });
            }
            return response.json();
        })
        .then(data => {
            const row = document.querySelector(`#trabajadores-table tr[data-id="${planillaId}"]`);
            if (row) row.remove();
            updateResumen();
            closeDeleteModal();
        })
        .catch(error => {
            console.error('Error en confirmDeleteTrabajador:', error);
            const err = document.getElementById('delete-error-message');
            if (err) {
                err.textContent = 'Error al eliminar: ' + (error.message || 'Error desconocido');
                err.classList.remove('hidden');
            }
        });
    }

    function updateResumen() {
        const ocupadas = document.querySelectorAll('#trabajadores-table tr').length;
        const plazas = {{ $proyecto->cantidad_trabajadores }};
        const plazasOcupadasEl = document.getElementById('plazas-ocupadas');
        const trabajadoresAdicionalesEl = document.getElementById('trabajadores-adicionales');
        if (plazasOcupadasEl) plazasOcupadasEl.textContent = ocupadas;
        if (trabajadoresAdicionalesEl) trabajadoresAdicionalesEl.textContent = ocupadas > plazas ? ocupadas - plazas : 0;
        // gráfico removido
    }

    function initializeUpdateModalEvents() {
        document.querySelectorAll('.open-update-modal').forEach(button => {
            button.removeEventListener('click', handleUpdateModalClick);
            button.addEventListener('click', handleUpdateModalClick);
        });
    }

    function initializeDetailsModalEvents() {
        document.querySelectorAll('.open-details-modal').forEach(button => {
            button.removeEventListener('click', handleDetailsModalClick);
            button.addEventListener('click', handleDetailsModalClick);
        });
    }

    function handleDetailsModalClick(event) {
        event.preventDefault();
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

    function openDetailsModal(data) {
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

    function closeDetailsModal() {
        $('#detalles-modal').modal('hide');
    }

    function handleUpdateModalClick(event) {
        event.preventDefault();
        const planillaId = this.dataset.planillaId;
        openUpdateModal(planillaId);
    }

    const updateForm = document.getElementById('update-form');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const planillaId = document.getElementById('update_planilla_id').value;
            const alimentacion = document.getElementById('alimentacion_trabajador').value;
            const hospedaje = document.getElementById('hospedaje_trabajador').value;
            const pasajes = document.getElementById('pasajes_trabajador').value;
            const errorMessage = document.getElementById('update-error-message');
            const url = `{{ route('proyectos.updatePlanillaGastos', ['proyecto' => ':proyectoId', 'planilla' => ':planillaId']) }}`
                .replace(':proyectoId', proyectoId)
                .replace(':planillaId', planillaId);
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    alimentacion_trabajador: parseFloat(alimentacion) || 0,
                    hospedaje_trabajador: parseFloat(hospedaje) || 0,
                    pasajes_trabajador: parseFloat(pasajes) || 0
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Error al actualizar los gastos');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`#trabajadores-table tr[data-id="${planillaId}"]`);
                    if (row) {
                        row.querySelector('td:nth-child(3)').textContent = `S/${Number(data.alimentacion_trabajador).toFixed(2)}`;
                        row.querySelector('td:nth-child(4)').textContent = `S/${Number(data.hospedaje_trabajador).toFixed(2)}`;
                        row.querySelector('td:nth-child(5)').textContent = `S/${Number(data.pasajes_trabajador).toFixed(2)}`;
                    }
                    closeUpdateModal();
                } else {
                    errorMessage.textContent = data.error || 'Error al actualizar los gastos';
                    errorMessage.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.textContent = error.message;
                errorMessage.classList.remove('hidden');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeUpdateModalEvents();
        initializeDetailsModalEvents();

        // Resetear formularios al cerrar modales (Bootstrap)
        $('#update-modal').on('hidden.bs.modal', function () {
            const form = document.getElementById('update-form');
            const errorMessage = document.getElementById('update-error-message');
            if (form) form.reset();
            if (errorMessage) errorMessage.classList.add('hidden');
        });

        $('#trabajadores-modal').on('hidden.bs.modal', function () {
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) errorMessage.classList.add('hidden');
        });

        $('#sueldos-modal').on('hidden.bs.modal', function () {
            const errorMessage = document.getElementById('sueldos-error');
            if (errorMessage) errorMessage.classList.add('hidden');
        });

        // Botón Marcar Asistencia
        const asistenciaBtn = document.getElementById('marcar-asistencia-btn');
        if (asistenciaBtn) asistenciaBtn.addEventListener('click', openAsistenciaModal);

        // Bootstrap maneja Escape y clic fuera automáticamente
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
</style>
@endpush