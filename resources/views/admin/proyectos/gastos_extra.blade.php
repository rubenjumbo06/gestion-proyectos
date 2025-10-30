@props(['proyecto', 'gastosExtra'])

<!-- Presupuesto Servicios -->
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Presupuesto Servicios</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-4"><p><strong>Asignado:</strong> S/<span id="srv-assigned">{{ isset($budgetServicios['assigned']) ? number_format($budgetServicios['assigned'], 2) : '0.00' }}</span></p></div>
            <div class="col-md-4"><p><strong>Gastado (Servicios + Extras):</strong> S/<span id="srv-spent">{{ isset($budgetServicios['spent']) ? number_format($budgetServicios['spent'], 2) : '0.00' }}</span></p></div>
            <div class="col-md-4"><p><strong>Restante:</strong> S/<span id="srv-remaining">{{ isset($budgetServicios['remaining']) ? number_format($budgetServicios['remaining'], 2) : '0.00' }}</span></p></div>
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
                <p><strong>Alimentación General:</strong> S/<span>{{ number_format($gastosExtra->sum('alimentacion_general'), 2) }}</span></p>
                <p><strong>Hospedaje:</strong> S/<span>{{ number_format($gastosExtra->sum('hospedaje'), 2) }}</span></p>
            </div>
            <div class="col-md-6">
                <p><strong>Pasajes:</strong> S/<span>{{ number_format($gastosExtra->sum('pasajes'), 2) }}</span></p>
                <p><strong>Servicios:</strong> S/<span>{{ number_format($servicios->sum('monto'), 2) }}</span></p>
                <p><strong>Total Gastos Extras:</strong> 
                    S/<span>{{ number_format(
                        $gastosExtra->sum(function($gasto) {
                            return $gasto->alimentacion_general + $gasto->hospedaje + $gasto->pasajes;
                        }) + $servicios->sum('monto'), 2) }}</span>
                </p>
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
                                    <button type="button" class="btn text-red-500 hover:text-red-600 text-2x" onclick="GastosExtras.removeGasto({{ $gasto->id_gasto }})">
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
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#servicios-modal">
                    <i class="fa fa-plus"></i> Agregar Servicio
                </button>
            @endif
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered table-striped zpend-table">
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
                        <tr>
                            <td>{{ $servicio->descripcion_serv }}</td>
                            <td>S/{{ number_format($servicio->monto, 2) }}</td>
                            <td>{{ $servicio->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if(Auth::check() && Auth::user()->puede_editar)
                                    <button type="button" class="btn text-yellow-500 hover:text-yellow-600 text-2xl" data-toggle="modal" data-target="#servicios-modal"
                                            data-id="{{ $servicio->id_servicio }}"
                                            data-descripcion="{{ $servicio->descripcion_serv }}"
                                            data-monto="{{ $servicio->monto }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                                @if(Auth::check() && Auth::user()->puede_eliminar)
                                    <form action="{{ route('servicios.destroy', $servicio->id_servicio) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn text-red-500 hover:text-red-600 text-2x">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
            <form action="{{ route('servicios.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_proyecto" value="{{ $proyecto->id_proyecto }}">
                <input type="hidden" id="id_servicio" name="id_servicio">
                <div class="modal-header bg-primary">
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
// Toast utility (define if not already present)
if (typeof window.showToast !== 'function') {
    (function(){
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
        window.showToast = function(message, type = 'info', duration = 5000){
            const container = getToastContainer();
            const toast = document.createElement('div');
            const cls = type === 'error' ? 'alert-danger' : (type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-info'));
            toast.className = `alert ${cls}`;
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
    })();
}

async function getBudgetServicesSummary(){
    const res = await fetch(`/api/proyectos/{{ $proyecto->id_proyecto }}/budgets`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) throw new Error('No se pudo obtener presupuesto');
    const data = await res.json();
    return data && data.services ? data.services : { assigned:0, spent:0, remaining:0 };
}

// Presupuesto: Servicios (incluye servicios + gastos extra)
async function refreshBudgetServices(){
    try{
        const res = await fetch(`/api/proyectos/{{ $proyecto->id_proyecto }}/budgets`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if(!res.ok) throw new Error('No se pudo obtener presupuesto');
        const data = await res.json();
        const s = data.services || { assigned:0, spent:0, remaining:0 };
        const aEl = document.getElementById('srv-assigned');
        const sEl = document.getElementById('srv-spent');
        const rEl = document.getElementById('srv-remaining');
        if(aEl) aEl.textContent = Number(s.assigned).toFixed(2);
        if(sEl) sEl.textContent = Number(s.spent).toFixed(2);
        if(rEl) rEl.textContent = Number(s.remaining).toFixed(2);
    }catch(e){ console.error(e); }
}

// Inicializar presupuesto al cargar el fragmento
refreshBudgetServices();

// Namespace para evitar conflictos globales
const GastosExtras = {
    isInitialized: false,
    currentOriginal: { ali: 0, hos: 0, pas: 0 },

    // Inicializar el módulo
    init() {
        if (this.isInitialized) {
            console.log('GastosExtras ya está inicializado');
            return;
        }

        console.log('Inicializando GastosExtras');
        this.bindEvents();
        this.isInitialized = true;
    },

    // Vincular eventos
    bindEvents() {
        // Eventos para abrir modal
        this.bindModalEvents();
        
        // Evento específico para el botón "Agregar Gasto Extra"
        const addButton = document.querySelector('.open-gasto-modal[data-gasto-id=""]');
        if (addButton) {
            addButton.addEventListener('click', this.handleAddClick.bind(this));
        }
        
        // Evento del formulario
        const form = document.getElementById('gastos-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });
        }

        // Evento para cuando se cierre el modal de Bootstrap
        const modal = document.getElementById('gastos-modal');
        if (modal) {
            $(modal).on('hidden.bs.modal', () => {
                const form = document.getElementById('gastos-form');
                const errorMessage = document.getElementById('error-message');
                
                if (form) form.reset();
                if (errorMessage) errorMessage.classList.add('hidden');
            });
        }

        // Evento para el botón de confirmación de eliminación
        const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                this.confirmDelete();
            });
        }
    },

    // Vincular eventos de los botones del modal
    bindModalEvents() {
        // Usar delegación de eventos para evitar problemas de duplicación
        const table = document.getElementById('gastos-table');
        if (table) {
            // Remover el listener anterior si existe
            table.removeEventListener('click', this.handleTableClick);
            // Agregar el nuevo listener con delegación
            table.addEventListener('click', this.handleTableClick.bind(this));
        }
    },

    // Manejar clic en la tabla (delegación de eventos)
    handleTableClick(event) {
        // Verificar si el clic fue en un botón de editar
        if (event.target.closest('.open-gasto-modal')) {
            event.preventDefault();
            event.stopPropagation();
            
            const button = event.target.closest('.open-gasto-modal');
            const gastoId = button.dataset.gastoId || null;
            
            console.log('Abriendo modal con ID:', gastoId);
            this.openModal(gastoId);
        }
    },

    // Abrir modal
    openModal(gastoId = null) {
        console.log('Abriendo modal, gastoId:', gastoId);
        
        const modal = document.getElementById('gastos-modal');
        const form = document.getElementById('gastos-form');
        const title = document.getElementById('gastos-modal-label');
        const errorMessage = document.getElementById('error-message');
        const gastoIdInput = document.getElementById('gasto_id');

        if (!modal) {
            console.error('Error: No se encontró el elemento #gastos-modal');
            return;
        }

        // Pre-chequeo de presupuesto para creación
        if (!gastoId) {
            getBudgetServicesSummary().then(s => {
                if (s.remaining <= 0) {
                    showToast('Se alcanzó el límite del presupuesto de Servicios.', 'error', 6000);
                    throw new Error('BudgetExceeded');
                }
                if (s.assigned > 0 && s.remaining <= s.assigned * 0.2) {
                    showToast('Aviso: queda 20% o menos del presupuesto de Servicios.', 'warning', 6000);
                }
            }).then(() => {
                // Limpiar estado anterior y abrir
                this.clearModalState();
                const isEdit = false;
                gastoIdInput.value = '';
                title.textContent = 'Agregar Gasto Extra';
                $(modal).modal('show');
                setTimeout(() => { document.getElementById('alimentacion_general').focus(); }, 100);
            }).catch(e => { if (e && e.message === 'BudgetExceeded') return; });
            return;
        }

        // Limpiar estado anterior
        this.clearModalState();

        // Configurar el modal según el tipo de operación
        const isEdit = gastoId && gastoId !== '';
        gastoIdInput.value = gastoId || '';
        title.textContent = isEdit ? 'Editar Gasto Extra' : 'Agregar Gasto Extra';

        // Mostrar el modal usando Bootstrap
        $(modal).modal('show');

        // Habilitar inputs
        const inputs = form.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            input.removeAttribute('disabled');
            input.removeAttribute('readonly');
        });

        // Dar foco al primer input
        setTimeout(() => {
            document.getElementById('alimentacion_general').focus();
        }, 100);

        // Si es edición, cargar los datos
        if (isEdit) {
            this.loadGastoData(gastoId);
        }
    },

    // Limpiar estado del modal
    clearModalState() {
        const form = document.getElementById('gastos-form');
        const errorMessage = document.getElementById('error-message');
        
        if (form) {
            form.reset();
        }
        if (errorMessage) {
            errorMessage.classList.add('hidden');
            errorMessage.textContent = '';
        }
        
        // Limpiar mensajes de error individuales
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(msg => {
            msg.textContent = '';
            msg.classList.add('hidden');
        });
    },

    // Obtener gasto del servidor y actualizar la tabla
    fetchGastoAndUpdate(gastoId, isEdit = false) {
        if (!gastoId) return;

        const url = '{{ route("proyectos.gastos-extra.show", [$proyecto->id_proyecto, "_id_"]) }}'.replace('_id_', gastoId);
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('No se pudo obtener el gasto actualizado');
            }
            return response.json();
        })
        .then(gasto => {
            this.updateTableRow(gasto, isEdit ? gastoId : null);
        })
        .catch(err => {
            console.error('Error al actualizar la fila desde el servidor:', err);
        });
    },

    // Cargar datos del gasto
    loadGastoData(gastoId) {
        console.log('Cargando datos del gasto ID:', gastoId);
        
        if (!gastoId || gastoId === '') {
            console.warn('No se proporcionó un ID de gasto válido');
            return;
        }
        
        const url = '{{ route("proyectos.gastos-extra.show", [$proyecto->id_proyecto, "_id_"]) }}'.replace('_id_', gastoId);
        console.log('URL de carga:', url);
        
        // Mostrar indicador de carga
        const inputs = document.querySelectorAll('#gastos-form input[type="number"]');
        inputs.forEach(input => {
            input.disabled = true;
            input.value = 'Cargando...';
        });
        
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            console.log('Respuesta al cargar gasto:', response);
            if (response.status === 401) {
                window.location.href = '{{ route("login") }}';
                return;
            }
            if (!response.ok) {
                throw new Error(`Error al cargar el gasto: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos del servidor:', data);
            
            // Verificar que los datos sean válidos
            if (!data || typeof data.id_gasto === 'undefined') {
                throw new Error('Datos de gasto inválidos recibidos del servidor');
            }
            
            // Cargar datos en el formulario
            document.getElementById('gasto_id').value = data.id_gasto;
            const ali = parseFloat(data.alimentacion_general) || 0;
            const hos = parseFloat(data.hospedaje) || 0;
            const pas = parseFloat(data.pasajes) || 0;
            document.getElementById('alimentacion_general').value = ali;
            document.getElementById('hospedaje').value = hos;
            document.getElementById('pasajes').value = pas;
            this.currentOriginal = { ali, hos, pas };
            
            // Habilitar inputs
            inputs.forEach(input => {
                input.disabled = false;
            });
            
            console.log('Datos cargados en el formulario:', {
                id: document.getElementById('gasto_id').value,
                alimentacion: document.getElementById('alimentacion_general').value,
                hospedaje: document.getElementById('hospedaje').value,
                pasajes: document.getElementById('pasajes').value
            });
        })
        .catch(error => {
            console.error('Error al cargar datos:', error);
            
            // Habilitar inputs y mostrar error
            inputs.forEach(input => {
                input.disabled = false;
                input.value = '';
            });
            
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = error.message;
            errorMessage.classList.remove('hidden');
        });
    },

    // Cerrar modal
    closeModal() {
        const modal = document.getElementById('gastos-modal');
        if (modal) {
            $(modal).modal('hide');
        }
        
        // Limpiar estado del modal
        this.clearModalState();
    },

    // Enviar formulario
    submitForm() {
        const gastoId = document.getElementById('gasto_id').value;
        const alimentacion = document.getElementById('alimentacion_general').value;
        const hospedaje = document.getElementById('hospedaje').value;
        const pasajes = document.getElementById('pasajes').value;
        const errorMessage = document.getElementById('error-message');

        console.log('Datos del formulario:', {
            gastoId: gastoId,
            alimentacion: alimentacion,
            hospedaje: hospedaje,
            pasajes: pasajes
        });

        const data = {
            alimentacion_general: parseFloat(alimentacion) || 0,
            hospedaje: parseFloat(hospedaje) || 0,
            pasajes: parseFloat(pasajes) || 0
        };

        // Pre-chequeo presupuesto (delta si edita, incremento si crea)
        getBudgetServicesSummary().then(s => {
            const totalNuevo = (data.alimentacion_general + data.hospedaje + data.pasajes);
            if (gastoId) {
                const totalOriginal = (this.currentOriginal.ali || 0) + (this.currentOriginal.hos || 0) + (this.currentOriginal.pas || 0);
                const delta = totalNuevo - totalOriginal;
                if (delta > (s.remaining || 0) + 1e-6) {
                    errorMessage.textContent = 'No se puede actualizar: se supera el límite del presupuesto de Servicios.';
                    errorMessage.classList.remove('hidden');
                    throw new Error('BudgetExceeded');
                }
                if ((s.assigned || 0) > 0 && (s.remaining - delta) <= (s.assigned * 0.2)) {
                    showToast('Aviso: con esta acción, quedarás a menos del 20% del presupuesto de Servicios.', 'warning', 6000);
                }
            } else {
                if (totalNuevo > (s.remaining || 0) + 1e-6) {
                    errorMessage.textContent = 'No se puede agregar: se supera el límite del presupuesto de Servicios.';
                    errorMessage.classList.remove('hidden');
                    throw new Error('BudgetExceeded');
                }
                if ((s.assigned || 0) > 0 && (s.remaining - totalNuevo) <= (s.assigned * 0.2)) {
                    showToast('Aviso: con este registro, quedarás a menos del 20% del presupuesto de Servicios.', 'warning', 6000);
                }
            }
        }).then(() => {
            // continuar con fetch
            const url = gastoId
                ? '{{ route("proyectos.gastos-extra.update", [$proyecto->id_proyecto, "_id_"]) }}'.replace('_id_', gastoId)
                : '{{ route("proyectos.gastos-extra.store", $proyecto->id_proyecto) }}';
            const method = gastoId ? 'PUT' : 'POST';
            return fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
        }).then(response => {
            console.log('Respuesta del servidor:', response);
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.error || `Error al guardar el gasto: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(resp => {
            // Extraer el gasto desde distintas formas de respuesta: {data}, {gasto}, o el objeto directo
            const payload = resp && typeof resp === 'object' ? resp : {};
            const candidate = payload.data || payload.gasto || (payload.id_gasto || payload.id || payload.alimentacion_general || payload.hospedaje || payload.pasajes ? payload : null);
            const returnedId = (candidate && (candidate.id_gasto || candidate.id)) || payload.id_gasto || payload.id || null;

            const hasFields = candidate && (
                candidate.alimentacion_general !== undefined &&
                candidate.hospedaje !== undefined &&
                candidate.pasajes !== undefined
            );

            if (hasFields) {
                this.updateTableRow(candidate, gastoId || null);
            } else if (gastoId || returnedId) {
                // Intentar obtener el gasto actualizado del servidor cuando no tenemos todos los campos
                this.fetchGastoAndUpdate(gastoId || returnedId, Boolean(gastoId));
            }

            this.updateResumen();
            refreshBudgetServices();
            this.closeModal();
            showToast((payload && payload.message) || 'Guardado correctamente', 'success', 5000);
        })
        .catch(error => {
            console.error('Error:', error);
            if (error && error.message === 'BudgetExceeded') return;
            errorMessage.textContent = error.message;
            errorMessage.classList.remove('hidden');
        });
    },

    // Actualizar fila de la tabla
    updateTableRow(data, gastoId) {
        const table = document.getElementById('gastos-table');
        const alim = parseFloat(data.alimentacion_general) || 0;
        const hosp = parseFloat(data.hospedaje) || 0;
        const pas = parseFloat(data.pasajes) || 0;
        const total = (alim + hosp + pas).toFixed(2);
        const fecha = data.created_at ? new Date(data.created_at) : new Date();
        const fechaFormateada = fecha.toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const rowHTML = `
            <td>${data.id_gasto}</td>
            <td>S/${alim.toFixed(2)}</td>
            <td>S/${hosp.toFixed(2)}</td>
            <td>S/${pas.toFixed(2)}</td>
            <td>S/${total}</td>
            <td>${fechaFormateada}</td>
            <td class="action-buttons">
                @if(Auth::check() && Auth::user()->puede_editar)
                <button type="button" class="open-gasto-modal btn btn-warning btn-xs" data-gasto-id="${data.id_gasto}">
                    <i class="fa fa-edit"></i>
                </button>
                @endif
                @if(Auth::check() && Auth::user()->puede_eliminar)
                <button type="button" class="btn btn-danger btn-xs" onclick="GastosExtras.removeGasto(${data.id_gasto})">
                    <i class="fa fa-trash"></i>
                </button>
                @endif
            </td>
        `;

        if (gastoId) {
            // Actualizar fila existente
            const row = document.querySelector(`#gastos-table tr[data-id="${gastoId}"]`);
            if (row) {
                // Actualizar solo las celdas de datos, no la estructura completa
                const cells = row.querySelectorAll('td');
                if (cells.length >= 7) {
                    cells[0].textContent = data.id_gasto;
                    cells[1].textContent = `S/${alim.toFixed(2)}`;
                    cells[2].textContent = `S/${hosp.toFixed(2)}`;
                    cells[3].textContent = `S/${pas.toFixed(2)}`;
                    cells[4].textContent = `S/${total}`;
                    cells[5].textContent = fechaFormateada;
                    // Las celdas de acciones (cells[6]) se mantienen intactas
                }
            }
        } else {
            // Remover la fila de "vacío" si existe antes de agregar la nueva
            const emptyRow = table.querySelector('.empty-row');
            if (emptyRow) {
                emptyRow.remove();
            }
            // Agregar nueva fila
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-id', data.id_gasto);
            newRow.innerHTML = `<td>${data.id_gasto}</td><td>S/${alim.toFixed(2)}</td><td>S/${hosp.toFixed(2)}</td><td>S/${pas.toFixed(2)}</td><td>S/${total}</td><td>${fechaFormateada}</td><td class="action-buttons">
                @if(Auth::check() && Auth::user()->puede_editar)
                <button type="button" class="open-gasto-modal btn btn-warning btn-xs" data-gasto-id="${data.id_gasto}">
                    <i class="fa fa-edit"></i>
                </button>
                @endif
                @if(Auth::check() && Auth::user()->puede_eliminar)
                <button type="button" class="btn btn-danger btn-xs" onclick="GastosExtras.removeGasto(${data.id_gasto})">
                    <i class="fa fa-trash"></i>
                </button>
                @endif
            </td>`;
            table.appendChild(newRow);
        }

        // No es necesario re-vincular eventos ya que usamos delegación de eventos
    },

    // Eliminar gasto
    removeGasto(gastoId) {
        // Mostrar modal de confirmación
        $('#confirm-delete-modal').modal('show');
        
        // Guardar el ID del gasto a eliminar
        this.gastoToDelete = gastoId;
    },

    // Confirmar eliminación
    confirmDelete() {
        const gastoId = this.gastoToDelete;
        if (!gastoId) return;

        fetch('{{ route("proyectos.gastos-extra.destroy", [$proyecto->id_proyecto, "_id_"]) }}'.replace('_id_', gastoId), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            console.log('Respuesta al eliminar gasto:', response);
            if (!response.ok) {
                throw new Error('Error al eliminar el gasto');
            }
            return response.json();
        })
        .then(data => {
            const row = document.querySelector(`#gastos-table tr[data-id="${gastoId}"]`);
            if (row) {
                row.remove();
            }
            // Verificar si la tabla está vacía ahora y agregar la fila de "vacío"
            const table = document.getElementById('gastos-table');
            if (table.children.length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.className = 'empty-row';
                emptyRow.innerHTML = '<td colspan="7" class="text-center">No hay gastos extras registrados.</td>';
                table.appendChild(emptyRow);
            }
            this.updateResumen();
            refreshBudgetServices();
            $('#confirm-delete-modal').modal('hide');
            showToast((data && data.message) || 'Eliminado correctamente', 'success', 5000);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#confirm-delete-modal').modal('hide');
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = error.message;
            errorMessage.classList.remove('hidden');
        });
    },

    // Actualizar resumen
    updateResumen() {
        fetch('{{ route("proyectos.gastos-extra.data", $proyecto->id_proyecto) }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            console.log('Respuesta al actualizar resumen:', response);
            if (!response.ok) {
                throw new Error('Error al actualizar resumen');
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos para actualizar resumen:', data);
            
            // Verificar que los datos estén en el formato correcto
            if (!data.success || !data.data || !Array.isArray(data.data)) {
                console.error('Formato de datos incorrecto:', data);
                return;
            }
            
            // Actualizar elementos del resumen
            const alimentacionTotal = document.getElementById('alimentacion-total');
            const hospedajeTotal = document.getElementById('hospedaje-total');
            const pasajesTotal = document.getElementById('pasajes-total');
            const totalGastos = document.getElementById('total-gastos');

            if (alimentacionTotal) alimentacionTotal.textContent = Number(data.data[0]).toFixed(2);
            if (hospedajeTotal) hospedajeTotal.textContent = Number(data.data[1]).toFixed(2);
            if (pasajesTotal) pasajesTotal.textContent = Number(data.data[2]).toFixed(2);
            if (totalGastos) totalGastos.textContent = (data.data[0] + data.data[1] + data.data[2]).toFixed(2);
        })
        .catch(error => {
            console.error('Error al actualizar resumen:', error);
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                errorMessage.textContent = error.message;
                errorMessage.classList.remove('hidden');
            }
        });
    },

    // Reinicializar cuando se active la pestaña
    reinitialize() {
        console.log('Reinicializando GastosExtras');
        // Solo re-vincular eventos si no están ya vinculados
        if (!this.isInitialized) {
            this.init();
        } else {
            this.bindModalEvents();
            
            // Re-vincular el botón de agregar
            const addButton = document.querySelector('.open-gasto-modal[data-gasto-id=""]');
            if (addButton) {
                // Remover listener anterior si existe
                addButton.removeEventListener('click', this.handleAddClick);
                // Agregar nuevo listener
                addButton.addEventListener('click', this.handleAddClick.bind(this));
            }
        }
    },
    
    // Manejar clic en botón de agregar
    handleAddClick(event) {
        event.preventDefault();
        event.stopPropagation();
        console.log('Abriendo modal para agregar nuevo gasto');
        this.openModal(null);
    }
};

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    GastosExtras.init();
});

// Manejar activación de pestaña
document.addEventListener('click', function(e) {
    if (e.target.matches('.tab-button[data-tab="gastos_extras"]')) {
        console.log('Pestaña Gastos Extras activada');
        setTimeout(() => {
            GastosExtras.reinitialize();
        }, 100);
    }
});

// Exponer funciones necesarias al scope global para compatibilidad
window.GastosExtras = GastosExtras;
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