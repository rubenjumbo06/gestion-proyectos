@props(['proyecto', 'gastosExtra'])

<!-- Presupuesto Servicios -->
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Presupuesto Servicios</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-4"><p><strong>Asignado:</strong> S/<span id="srv-assigned">0.00</span></p></div>
            <div class="col-md-4"><p><strong>Gastado:</strong> S/<span id="srv-spent">0.00</span></p></div>
            <div class="col-md-4"><p><strong>Restante:</strong> S/<span id="srv-remaining">0.00</span></p></div>
        </div>
    </div>
</div>

<!-- Resumen de gastos extras -->
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Resumen de Gastos Extras</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Alimentación General:</strong> S/<span id="resumen-ali">0.00</span></p>
                <p><strong>Hospedaje:</strong> S/<span id="resumen-hos">0.00</span></p>
            </div>
            <div class="col-md-6">
                <p><strong>Pasajes:</strong> S/<span id="resumen-pas">0.00</span></p>
                <p><strong>Total Gastos Extras:</strong> S/<span id="resumen-total-extra">0.00</span></p>
            </div>
        </div>
    </div>
</div>

<!-- Lista de gastos extras -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Gestión de Gastos Extras</h3>
        <div class="box-tools pull-right">
            @if(Auth::check() && Auth::user()->puede_agregar)
                <button type="button" class="open-gasto-modal btn btn-primary btn-sm" data-gasto-id="">
                    <i class="fa fa-plus"></i> Agregar Gasto Extra
                </button>
            @endif
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered table-striped zpend-table">
            <thead>
                <tr>
                    <th data-column="alimentacion_general">Alimentación General</th>
                    <th data-column="hospedaje">Hospedaje</th>
                    <th data-column="pasajes">Pasajes</th>
                    <th data-column="total">Total</th>
                    <th data-column="created_at">Fecha Creación</th>
                    <th data-column="acciones">Acciones</th>
                </tr>
            </thead>
            <tbody id="gastos-table">
                @if ($gastosExtra->isNotEmpty())
                    @foreach ($gastosExtra as $gasto)
                        <tr data-id="{{ $gasto->id_gasto }}">
                            <td>S/{{ number_format($gasto->alimentacion_general, 2) }}</td>
                            <td>S/{{ number_format($gasto->hospedaje, 2) }}</td>
                            <td>S/{{ number_format($gasto->pasajes, 2) }}</td>
                            <td>S/{{ number_format($gasto->alimentacion_general + $gasto->hospedaje + $gasto->pasajes, 2) }}</td>
                            <td>{{ $gasto->created_at->format('d/m/Y H:i') }}</td>
                            <td class="action-buttons">
                                @if(Auth::check() && Auth::user()->puede_editar)
                                    <button type="button" class="open-gasto-modal text-yellow-500 hover:text-yellow-600 text-2xl" data-gasto-id="{{ $gasto->id_gasto }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                @endif
                                @if(Auth::check() && Auth::user()->puede_eliminar)
                                    <button type="button" class="btn text-red-500 hover:text-red-600 text-2xl" onclick="GastosExtras.remove({{ $gasto->id_gasto }})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr class="empty-row">
                        <td colspan="7" class="text-center">No hay gastos extras registrados.</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <!-- Paginación -->
        <div class="text-center">
            {{ $gastosExtra->links() }}
        </div>
    </div>
</div>

<!-- Lista de Servicios -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Gestión de Servicios</h3>
        <div class="box-tools pull-right">
            @if(Auth::check() && Auth::user()->puede_agregar)
                <button type="button" class="btn btn-primary btn-sm open-servicio-modal" data-servicio-id="">
                    <i class="fa fa-plus"></i> Agregar Servicio
                </button>
            @endif
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered table-striped zpend-table" id="servicios-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Monto</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @if ($servicios->isNotEmpty())
                    @foreach ($servicios as $servicio)
                        <tr data-id="{{ $servicio->id_servicio }}">
                            <td>{{ $servicio->descripcion_serv }}</td>
                            <td>S/{{ number_format($servicio->monto, 2) }}</td>
                            <td>{{ $servicio->created_at->format('d/m/Y H:i') }}</td>
                            <td class="action-buttons">
                                @if(Auth::check() && Auth::user()->puede_editar)
                                    <button type="button" class=" btn open-servicio-modal text-yellow-500 hover:text-yellow-600 text-2xl"
                                            data-servicio-id="{{ $servicio->id_servicio }}"
                                            data-descripcion="{{ $servicio->descripcion_serv }}"
                                            data-monto="{{ $servicio->monto }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                                @if(Auth::check() && Auth::user()->puede_eliminar)
                                    <button type="button" class="btn text-red-500 hover:text-red-600 text-2xl delete-servicio"
                                            data-id="{{ $servicio->id_servicio }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr class="empty-row">
                        <td colspan="4" class="text-center">No hay servicios registrados.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para agregar/editar servicio -->
<div class="modal fade" id="servicios-modal" tabindex="-1" role="dialog" aria-labelledby="servicios-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content custom-modal">
            <form id="servicios-form">
                @csrf
                <input type="hidden" name="id_proyecto" value="{{ $proyecto->id_proyecto }}">
                <input type="hidden" id="id_servicio" name="id_servicio">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title text-white" id="servicios-modal-label">Agregar Servicio</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="descripcion_serv">Descripción</label>
                        <input type="text" id="descripcion_serv" name="descripcion_serv" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="monto">Monto</label>
                        <input type="number" step="0.01" id="monto" name="monto" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar gasto extra -->
<div class="modal fade" id="gastos-modal" tabindex="-1" role="dialog" aria-labelledby="gastos-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content custom-modal">
            <form id="gastos-form">
                <input type="hidden" id="gasto_id" name="gasto_id">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title text-white" id="gastos-modal-label">Agregar Gasto Extra</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="alimentacion_general" class="font-semibold">Alimentación General</label>
                        <input type="number" step="0.01" id="alimentacion_general" name="alimentacion_general" class="form-control" placeholder="Ingresa el monto de alimentación general" required>
                        <span class="text-danger error-message" id="alimentacion_general_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="hospedaje" class="font-semibold">Hospedaje</label>
                        <input type="number" step="0.01" id="hospedaje" name="hospedaje" class="form-control" placeholder="Ingresa el monto de hospedaje" required>
                        <span class="text-danger error-message" id="hospedaje_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="pasajes" class="font-semibold">Pasajes</label>
                        <input type="number" step="0.01" id="pasajes" name="pasajes" class="form-control" placeholder="Ingresa el monto de pasajes" required>
                        <span class="text-danger error-message" id="pasajes_error"></span>
                    </div>
                    <div id="error-message" class="alert alert-danger hidden"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar gasto -->
<div class="modal fade" id="confirm-delete-modal" tabindex="-1" role="dialog" aria-labelledby="confirm-delete-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content custom-modal">
            <div class="modal-header bg-red">
                <h4 class="modal-title text-white" id="confirm-delete-modal-label">Confirmar Eliminación</h4>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este gasto extra?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">Eliminar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
// === TOAST ===
if (typeof window.showToast !== 'function') {
    (function(){
        const container = (() => {
            let c = document.getElementById('toast-container');
            if (!c) {
                c = Object.assign(document.createElement('div'), {
                    id: 'toast-container',
                    style: 'position:fixed;top:16px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:8px;'
                });
                document.body.appendChild(c);
            }
            return c;
        })();
        window.showToast = (msg, type = 'info', dur = 5000) => {
            const toast = Object.assign(document.createElement('div'), {
                className: `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'}`,
                textContent: msg,
                style: 'min-width:280px;max-width:420px;margin:0;box-shadow:0 4px 12px rgba(0,0,0,0.15);opacity:1;'
            });
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.transition = 'opacity 300ms';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 320);
            }, Math.max(1000, dur));
        };
    })();
}

// === OBTENER PRESUPUESTO ===
async function getPersonalBudget() {
    try {
        const res = await fetch(`/api/proyectos/{{ $proyecto->id_proyecto }}/budgets`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        return data.personal || { assigned: 0, spent: 0, remaining: 0 };
    } catch (e) {
        console.error(e);
        return { assigned: 0, spent: 0, remaining: 0 };
    }
}

// === ACTUALIZAR RESUMEN GASTOS EXTRA ===
function updateResumen() {
    fetch('{{ route("proyectos.gastos-extra.data", $proyecto->id_proyecto) }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(d => {
        if (!d.success || !Array.isArray(d.data)) return;
        const [ali, hos, pas] = d.data.map(v => parseFloat(v) || 0);
        const total = ali + hos + pas;

        document.getElementById('resumen-ali').textContent = ali.toFixed(2);
        document.getElementById('resumen-hos').textContent = hos.toFixed(2);
        document.getElementById('resumen-pas').textContent = pas.toFixed(2);
        document.getetElementById('resumen-total-extra').textContent = total.toFixed(2);
    });
}

// === ACTUALIZAR PRESUPUESTOS ===
async function refreshBudgets() {
    const res = await fetch(`/api/proyectos/{{ $proyecto->id_proyecto }}/budgets`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();

    // Personal
    const p = data.personal || { assigned: 0, spent: 0, remaining: 0 };
    document.getElementById('per-assigned').textContent = p.assigned.toFixed(2);
    document.getElementById('per-spent').textContent = p.spent.toFixed(2);
    document.getElementById('per-remaining').textContent = p.remaining.toFixed(2);

    // Servicios
    const s = data.services || { assigned: 0, spent: 0, remaining: 0 };
    document.getElementById('srv-assigned').textContent = s.assigned.toFixed(2);
    document.getElementById('srv-spent').textContent = s.spent.toFixed(2);
    document.getElementById('srv-remaining').textContent = s.remaining.toFixed(2);
}

// === GASTOS EXTRA ===
const GastosExtras = {
    deleteId: null,

    init() {
        this.bindEvents();
        updateResumen();
        refreshBudgets();
    },

    bindEvents() {
        document.querySelectorAll('.open-gasto-modal[data-gasto-id=""]').forEach(b => {
            b.onclick = () => this.openModal();
        });

        document.getElementById('gastos-table')?.addEventListener('click', e => {
            const btn = e.target.closest('.open-gasto-modal');
            if (btn?.dataset.gastoId) this.openModal(btn.dataset.gastoId);
        });

        document.getElementById('gastos-form')?.addEventListener('submit', e => {
            e.preventDefault();
            this.submit();
        });

        document.getElementById('confirm-delete-btn')?.addEventListener('click', () => this.delete());
    },

    async openModal(id = null) {
        document.getElementById('gastos-form').reset();
        document.getElementById('gasto_id').value = id || '';
        document.getElementById('gastos-modal-label').textContent = id ? 'Editar' : 'Agregar';

        if (id) {
            const url = '{{ route("proyectos.gastos-extra.show", [$proyecto->id_proyecto, "_id_"]) }}'.replace('_id_', id);
            const res = await fetch(url, { headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
            const data = await res.json();
            document.getElementById('alimentacion_general').value = data.alimentacion_general || 0;
            document.getElementById('hospedaje').value = data.hospedaje || 0;
            document.getElementById('pasajes').value = data.pasajes || 0;
            this.original = { ali: +data.alimentacion_general, hos: +data.hospedaje, pas: +data.pasajes };
        } else {
            const budget = await getPersonalBudget();
            if (budget.remaining <= 0) return showToast('No hay presupuesto en Personal.', 'error');
            if (budget.assigned > 0 && budget.remaining <= budget.assigned * 0.2) {
                showToast('Queda ≤20% del presupuesto de Personal.', 'warning');
            }
        }

        $('#gastos-modal').modal('show');
    },

    async submit() {
        const id = document.getElementById('gasto_id').value;
        const ali = +document.getElementById('alimentacion_general').value || 0;
        const hos = +document.getElementById('hospedaje').value || 0;
        const pas = +document.getElementById('pasajes').value || 0;
        const total = ali + hos + pas;

        const budget = await getPersonalBudget();
        const originalTotal = id ? this.original.ali + this.original.hos + this.original.pas : 0;
        const delta = total - originalTotal;

        if (delta > budget.remaining + 1e-6) {
            showToast('No hay suficiente presupuesto en Personal.', 'error');
            return;
        }

        const url = id
            ? '{{ route("proyectos.gastos-extra.update", [$proyecto->id_proyecto, "_id_"]) }}'.replace('_id_', id)
            : '{{ route("proyectos.gastos-extra.store", $proyecto->id_proyecto) }}';

        const res = await fetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ alimentacion_general: ali, hospedaje: hos, pasajes: pas })
        });

        if (!res.ok) return showToast('Error al guardar', 'error');

        const data = await res.json();
        this.updateRow(data.gasto || data);
        afterChange();
        $('#gastos-modal').modal('hide');
        showToast('Guardado', 'success');
    },

    updateRow(gasto) {
        const total = (+gasto.alimentacion_general + +gasto.hospedaje + +gasto.pasajes).toFixed(2);
        const fecha = new Date(gasto.created_at).toLocaleString('es-ES', {
            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        const row = document.querySelector(`#gastos-table tr[data-id="${gasto.id_gasto}"]`);
        const html = `
            <td>S/${(+gasto.alimentacion_general).toFixed(2)}</td>
            <td>S/${(+gasto.hospedaje).toFixed(2)}</td>
            <td>S/${(+gasto.pasajes).toFixed(2)}</td>
            <td>S/${total}</td>
            <td>${fecha}</td>
            <td class="action-buttons">
                @if(Auth::check() && Auth::user()->puede_editar)
                <button class="open-gasto-modal text-yellow-500 hover:text-yellow-600 text-2xl" data-gasto-id="${gasto.id_gasto}">
                    <i class="fa fa-edit"></i>
                </button>
                @endif
                @if(Auth::check() && Auth::user()->puede_eliminar)
                <button class="text-red-500 hover:text-red-600 text-2xl" onclick="GastosExtras.remove(${gasto.id_gasto})">
                    <i class="fa fa-trash"></i>
                </button>
                @endif
            </td>
        `;

        if (row) {
            row.innerHTML = html;
        } else {
            const table = document.getElementById('gastos-table');
            table.querySelector('.empty-row')?.remove();
            table.insertAdjacentHTML('beforeend', `<tr data-id="${gasto.id_gasto}">${html}</tr>`);
        }
    },

    remove(id) {
        this.deleteId = id;
        $('#confirm-delete-modal').modal('show');
    },

    async delete() {
        if (!this.deleteId) return;

        const url = '{{ route("proyectos.gastos-extra.destroy", [$proyecto->id_proyecto, "_id_"]) }}'.replace('_id_', this.deleteId);
        await fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
        document.querySelector(`#gastos-table tr[data-id="${this.deleteId}"]`)?.remove();

        if (!document.querySelector('#gastos-table tr[data-id]')) {
            document.getElementById('gastos-table').innerHTML = '<tr class="empty-row"><td colspan="6" class="text-center">No hay gastos extras.</td></tr>';
        }

        afterChange();
        $('#confirm-delete-modal').modal('hide');
        showToast('Eliminado', 'success');
    }
};

// === SERVICIOS ===
const Servicios = {
    init() {
        this.bindEvents();
        this.loadInitial();
    },

    bindEvents() {
        document.querySelectorAll('.open-servicio-modal').forEach(btn => {
            btn.onclick = () => this.openModal(
                btn.dataset.servicioId || null,
                btn.dataset.descripcion || '',
                btn.dataset.monto || ''
            );
        });

        document.getElementById('servicios-form')?.addEventListener('submit', e => {
            e.preventDefault();
            this.submit();
        });

        document.getElementById('servicios-table')?.addEventListener('click', e => {
            const delBtn = e.target.closest('.delete-servicio');
            if (delBtn) this.remove(delBtn.dataset.id);
        });
    },

    openModal(id = null, desc = '', monto = '') {
        document.getElementById('servicios-form').reset();
        document.getElementById('id_servicio').value = id || '';
        document.getElementById('descripcion_serv').value = desc;
        document.getElementById('monto').value = monto;
        document.getElementById('servicios-modal-label').textContent = id ? 'Editar Servicio' : 'Agregar Servicio';
        $('#servicios-modal').modal('show');
    },

    async submit() {
        const id = document.getElementById('id_servicio').value;
        const desc = document.getElementById('descripcion_serv').value.trim();
        const monto = +document.getElementById('monto').value || 0;

        if (!desc || monto <= 0) return showToast('Completa todos los campos', 'error');

        const url = id ? `/servicios/${id}` : `/servicios`;
        const method = id ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                id_proyecto: {{ $proyecto->id_proyecto }},
                descripcion_serv: desc,
                monto: monto
            })
        });

        if (!res.ok) return showToast('Error al guardar', 'error');

        const data = await res.json();
        this.updateRow(data.servicio);
        $('#servicios-modal').modal('hide');
        afterChange();
        showToast('Servicio guardado', 'success');
    },

    updateRow(servicio) {
        const fecha = new Date(servicio.created_at).toLocaleString('es-ES', {
            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        const row = document.querySelector(`#servicios-table tr[data-id="${servicio.id_servicio}"]`);
        const html = `
            <td>${servicio.descripcion_serv}</td>
            <td>S/${parseFloat(servicio.monto).toFixed(2)}</td>
            <td>${fecha}</td>
            <td class="action-buttons">
                <button class="open-servicio-modal text-yellow-500 hover:text-yellow-600 text-2xl"
                        data-servicio-id="${servicio.id_servicio}"
                        data-descripcion="${servicio.descripcion_serv}"
                        data-monto="${servicio.monto}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="delete-servicio text-red-500 hover:text-red-600 text-2xl" data-id="${servicio.id_servicio}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        if (row) {
            row.innerHTML = html;
        } else {
            const table = document.getElementById('servicios-table');
            table.querySelector('.empty-row')?.remove();
            table.insertAdjacentHTML('beforeend', `<tr data-id="${servicio.id_servicio}">${html}</tr>`);
        }
    },

    async remove(id) {
        if (!confirm('¿Eliminar este servicio?')) return;
        await fetch(`/servicios/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
        document.querySelector(`#servicios-table tr[data-id="${id}"]`)?.remove();
        afterChange();
        showToast('Eliminado', 'success');
    },

    loadInitial() {
        document.querySelectorAll('#servicios-table tr[data-id]').forEach(row => {
            const id = row.dataset.id;
            const desc = row.cells[0].textContent;
            const monto = row.cells[1].textContent.replace('S/', '').trim();
            row.cells[3].innerHTML = `
                <button class="open-servicio-modal text-yellow-500 hover:text-yellow-600 text-2xl"
                        data-servicio-id="${id}" data-descripcion="${desc}" data-monto="${monto}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="delete-servicio text-red-500 hover:text-red-600 text-2xl" data-id="${id}">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        });
    }
};

// === DESPUÉS DE CADA CAMBIO ===
function afterChange() {
    updateResumen();
    refreshBudgets();
}

// === INICIAR ===
document.addEventListener('DOMContentLoaded', () => {
    GastosExtras.init();
    Servicios.init();
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

    .modal-dialog {
        max-width: 450px !important;
    }

    .modal-content.custom-modal {
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .modal-body {
        padding: 1rem 1.5rem;
    }

    .modal-header {
        border-bottom: none;
    }

    .modal-footer {
        border-top: none;
    }

    .error-message {
        font-size: 0.9rem !important;
        margin-top: 0.3rem !important;
        text-align: center;
        color: #dc3545;
    }

    .action-buttons {
        white-space: nowrap;
    }

    .action-buttons .btn {
        margin-right: 5px;
    }

    .action-buttons .btn:last-child {
        margin-right: 0;
    }

    .box-tools .btn {
        margin-left: 5px;
    }

    .zpend-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }

    .zpend-table td {
        vertical-align: middle;
    }

    .zpend-table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush