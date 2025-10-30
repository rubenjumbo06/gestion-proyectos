@props(['proyecto'])

<div class="p-6">
    <!-- Presupuesto de Materiales -->
    <div class="col-md-12 mb-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Presupuesto Materiales</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-4">
                        <p><strong>Asignado:</strong> S/<span id="mat-assigned">{{ isset($budgetMaterials['assigned']) ? number_format($budgetMaterials['assigned'], 2) : '0.00' }}</span></p>
                    </div>
                    <div class="col-sm-4">
                        <p><strong>Gastado:</strong> <span class="text-red-600">S/<span id="mat-spent">{{ isset($budgetMaterials['spent']) ? number_format($budgetMaterials['spent'], 2) : '0.00' }}</span></span></p>
                    </div>
                    <div class="col-sm-4">
                        <p><strong>Restante:</strong> <span class="text-green-600">S/<span id="mat-remaining">{{ isset($budgetMaterials['remaining']) ? number_format($budgetMaterials['remaining'], 2) : '0.00' }}</span></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="material-error" class="hidden text-red-600 mb-4 text-lg"></div>

    <!-- Sección de Gestión de Materiales (igual que Gestión de Personal) -->
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Gestión de Materiales</h3>
            <div class="box-tools pull-right">
                <button id="add-material-btn" 
                        onclick="openAddMaterialWithCheck()"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-full text-lg transition duration-300 flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i> Agregar Material
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table id="materiales-table" class="table table-bordered table-striped min-w-full bg-white border border-gray-200 text-xl">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xl">Descripción</th>
                            <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xl">Proveedor</th>
                            <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xl">Monto</th>
                            <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xl">Fecha Creación</th>
                            <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xl">Fecha Actualización</th>
                            <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xl">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-xl">
                        @foreach($materiales as $material)
                            <tr data-id="{{ $material->id_material }}">
                                <td class="px-6 py-4">{{ $material->descripcion_mat }}</td>
                                <td class="px-6 py-4">{{ $material->proveedor->nombre_prov }}</td>
                                <td class="px-6 py-4">S/{{ number_format($material->monto_mat, 2) }}</td>
                                <td class="px-6 py-4">{{ \Carbon\Carbon::parse($material->fecha_mat)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">{{ $material->updated_at ? \Carbon\Carbon::parse($material->updated_at)->format('d/m/Y') : 'N/A' }}</td>
                                <td class="px-6 py-4 flex space-x-3 justify-center">
                                    <button data-id="{{ $material->id_material }}" 
                                            onclick="editMaterial({{ $material->id_material }})" 
                                            class="text-yellow-500 hover:text-yellow-600 text-2xl">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button data-id="{{ $material->id_material }}" 
                                            onclick="confirmDeleteMaterial({{ $material->id_material }})" 
                                            class="text-red-500 hover:text-red-600 text-2xl">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-center mt-4">
                    {{ $materiales->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar material -->
    <div class="modal fade" id="addMaterialModal" tabindex="-1" role="dialog" aria-labelledby="addMaterialModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content custom-modal">
                <form id="addMaterialForm">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title text-white" id="addMaterialModalLabel">Añadir Nuevo Material</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="descripcion_mat" class="font-semibold text-left text-black">Descripción</label>
                            <input type="text" id="descripcion_mat" name="descripcion_mat" class="form-control letter-dot-only" placeholder="Descripción del material" required>
                            <span class="text-danger error-message" id="descripcion_mat_error"></span>
                        </div>
                        <div class="form-group">
                            <label for="id_proveedor" class="font-semibold text-left text-black">Comprado por:</label>
                            <select id="id_proveedor" name="id_proveedor" class="form-control" required>
                                <option value="">Seleccionar proveedor</option>
                                @foreach(\App\Models\Proveedor::all() as $proveedor)
                                    <option value="{{ $proveedor->id_proveedor }}">{{ $proveedor->nombre_prov }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-message" id="id_proveedor_error"></span>
                        </div>
                        <div class="form-group">
                            <label for="monto_mat" class="font-semibold text-left text-black">Monto (S/)</label>
                            <input type="number" step="0.01" id="monto_mat" name="monto_mat" class="form-control project-number-only" placeholder="Monto del material" required>
                            <span class="text-danger error-message" id="monto_mat_error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Añadir</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para editar material -->
    <div class="modal fade" id="editMaterialModal" tabindex="-1" role="dialog" aria-labelledby="editMaterialModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content custom-modal">
                <form id="editMaterialForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_materialId" name="id_material">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title text-white" id="editMaterialModalLabel">Editar Material</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_descripcion_mat" class="font-semibold text-left text-black">Descripción</label>
                            <input type="text" id="edit_descripcion_mat" name="descripcion_mat" class="form-control letter-dot-only" required>
                            <span class="text-danger error-message" id="edit_descripcion_mat_error"></span>
                        </div>
                        <div class="form-group">
                            <label for="edit_id_proveedor" class="font-semibold text-left text-black">Proveedor</label>
                            <select id="edit_id_proveedor" name="id_proveedor" class="form-control" required>
                                <option value="">Seleccionar proveedor</option>
                                @foreach(\App\Models\Proveedor::all() as $proveedor)
                                    <option value="{{ $proveedor->id_proveedor }}">{{ $proveedor->nombre_prov }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-message" id="edit_id_proveedor_error"></span>
                        </div>
                        <div class="form-group">
                            <label for="edit_monto_mat" class="font-semibold text-left text-black">Monto (S/)</label>
                            <input type="number" step="0.01" id="edit_monto_mat" name="monto_mat" class="form-control project-number-only" required>
                            <span class="text-danger error-message" id="edit_monto_mat_error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Eliminar -->
    <div class="modal fade" id="confirmDeleteMaterialModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content custom-modal rounded-2xl">
                <div class="modal-header bg-red text-white">
                    <h4 class="modal-title">Confirmar Eliminación</h4>
                </div>
                <div class="modal-body">
                    <p id="confirmDeleteMessage">¿Estás seguro de que deseas eliminar este material?</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteMaterialForm" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Aplica Poppins en todos los modales */
.modal * {
    font-family: 'Poppins', sans-serif !important;
}

/* Inputs redondos y con tipografía */
.modal .form-control,
.modal select,
.modal input[type="text"],
.modal input[type="number"],
.modal input[type="email"],
.modal input[type="date"],
.modal textarea {
    border-radius: 2rem !important;
    padding: 0.9rem 1.5rem !important;
    font-family: 'Poppins', sans-serif !important;
    font-size: 1.3rem !important;
    line-height: 1.5 !important;
    height: auto !important;
    box-sizing: border-box;
}

/* Select más alto y sin texto cortado */
.modal select {
    min-height: 3.2rem !important;
    padding-right: 2rem !important;
    background-position: right 1rem center !important;
}

/* Label en Poppins */
.modal label {
    font-family: 'Poppins', sans-serif !important;
    font-size: 1.4rem !important;
    font-weight: 600 !important;
    margin-bottom: 0.5rem !important;
}

.modal-title {
    font-size: 1.5rem !important;
}

/* Estilos para los modales */
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

#confirmDeleteMaterialModal .modal-header {
    background-color: #dc3545 !important;
    color: #fff !important;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

#confirmDeleteMaterialModal .modal-body {
    padding: 2rem 1.5rem 1rem 1.5rem; /* Más espacio arriba (2rem) */
}

#addMaterialModal .modal-header,
#editMaterialModal .modal-header {
    background-color: #386FA4 !important;
    color: #fff !important;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

#addMaterialModal .modal-dialog,
#editMaterialModal .modal-dialog {
    max-width: 400px;
    margin: 1.75rem auto;
}

.form-group {
    display: flex;
    flex-direction: column;
    align-items: left;
    margin-bottom: 0.5rem;
}

.form-group label {
    margin-bottom: 0.6rem;
    font-size: 1.1rem;
    font-weight: 600;
    color: #444;
    text-align: left;
}

/* --- Ajustes de ancho y proporción de tabla --- */
#materiales-table {
    width: 100%;
    table-layout: fixed; /* Fuerza que las columnas respeten proporciones */
}

/* Ajustar proporciones generales */
#materiales-table th,
#materiales-table td {
    word-wrap: break-word;
    white-space: normal;
}

/* Columnas específicas (ajusta los porcentajes si lo ves necesario) */
#materiales-table th:nth-child(1),
#materiales-table td:nth-child(1) {
    width: 30%; /* Descripción un poco más angosta */
}

#materiales-table th:nth-child(2),
#materiales-table td:nth-child(2) {
    width: 18%; /* Proveedor */
}

#materiales-table th:nth-child(3),
#materiales-table td:nth-child(3) {
    width: 10%; /* Monto */
}

#materiales-table th:nth-child(4),
#materiales-table td:nth-child(4),
#materiales-table th:nth-child(5),
#materiales-table td:nth-child(5) {
    width: 15%; /* Fechas un poco más angostas */
}

#materiales-table th:nth-child(6),
#materiales-table td:nth-child(6) {
    width: 10%; /* Acciones */
}

/* Asegura que el cuadro del presupuesto tenga el mismo ancho visual que otros */
.box.box-info,
.box.box-primary {
    width: 100%;
}

/* Igualar el padding para que las cajas se vean del mismo tamaño */
.box-body {
    padding: 1.5rem 1.8rem !important;
}

/* Centrar título y mantener proporción */
.box-header .box-title {
    font-size: 1.5rem;
    font-weight: 600;
}

</style>

<script>
// Presupuesto: Materiales
async function refreshBudgetMaterials() {
    try {
        const res = await fetch(`/api/proyectos/{{ $proyecto->id_proyecto }}/budgets`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('No se pudo obtener el presupuesto');
        const data = await res.json();
        const m = data.materials || { assigned:0, spent:0, remaining:0 };
        const asg = document.getElementById('mat-assigned');
        const sp  = document.getElementById('mat-spent');
        const rem = document.getElementById('mat-remaining');
        if (asg) asg.textContent = Number(m.assigned).toFixed(2);
        if (sp)  sp.textContent  = Number(m.spent).toFixed(2);
        if (rem) rem.textContent = Number(m.remaining).toFixed(2);
    } catch(e){ console.error(e); }
}

// Inicializar presupuesto al cargar este fragmento
refreshBudgetMaterials();

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

async function getBudgetMaterialsSummary(){
    const res = await fetch(`/api/proyectos/{{ $proyecto->id_proyecto }}/budgets`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) throw new Error('No se pudo obtener presupuesto');
    const data = await res.json();
    return data && data.materials ? data.materials : { assigned:0, spent:0, remaining:0 };
}

async function openAddMaterialWithCheck(){
    try {
        const m = await getBudgetMaterialsSummary();
        if (m.remaining <= 0) {
            showToast('Se alcanzó el límite del presupuesto de Materiales.', 'error', 6000);
            return false;
        }
        if (m.assigned > 0 && m.remaining <= m.assigned * 0.2) {
            showToast('Aviso: queda 20% o menos del presupuesto de Materiales.', 'warning', 6000);
        }
        openMaterialModal();
        return true;
    } catch(e) {
        console.warn('No se pudo verificar presupuesto antes de abrir modal de Materiales:', e);
        openMaterialModal();
        return true;
    }
}

// Open Add Material Modal
function openMaterialModal() {
    console.log('Opening addMaterialModal');
    $('#addMaterialModal').modal({
        backdrop: true,
        keyboard: true
    });
    const form = document.getElementById('addMaterialForm');
    if (form) form.reset();
    document.querySelectorAll('#addMaterialForm .error-message').forEach(el => el.textContent = '');
    document.getElementById('material-error').classList.add('hidden');
}

// Close Add Material Modal
function closeMaterialModal() {
    console.log('Closing addMaterialModal');
    $('#addMaterialModal').modal('hide');
    document.getElementById('material-error').classList.add('hidden');
}

// Open Edit Material Modal
function editMaterial(id) {
    console.log('Editando material ID:', id);
    fetch(`/admin/proyectos/{{ $proyecto->id_proyecto }}/materiales/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.error || 'Error en respuesta del servidor: ' + response.statusText);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        if (!data || !data.id_material) {
            throw new Error('Respuesta inválida: id_material no encontrado');
        }
        document.getElementById('edit_materialId').value = data.id_material || '';
        document.getElementById('edit_descripcion_mat').value = data.descripcion_mat || '';
        document.getElementById('edit_id_proveedor').value = data.id_proveedor || '';
        document.getElementById('edit_monto_mat').value = data.monto_mat ? parseFloat(data.monto_mat).toFixed(2) : '';
        document.querySelectorAll('#editMaterialForm .error-message').forEach(el => el.textContent = '');
        $('#editMaterialModal').modal({
            backdrop: true,
            keyboard: true
        });
    })
    .catch(error => {
        console.error('Error al cargar material:', error);
        const errorDiv = document.getElementById('material-error');
        if (errorDiv) {
            errorDiv.textContent = 'Error al cargar el material: ' + error.message;
            errorDiv.classList.remove('hidden');
        }
    });
}

// Confirm Delete Material
function confirmDeleteMaterial(id) {
    console.log('Confirmando eliminación de material ID:', id);
    const row = document.querySelector(`tr[data-id="${id}"]`);
    const descripcion = row ? row.querySelector('td:first-child').textContent : 'este material';
    document.getElementById('confirmDeleteMessage').innerHTML = 
        `¿Estás seguro de que deseas eliminar el material <strong>"${descripcion}"</strong>?`;
    const form = document.getElementById('deleteMaterialForm');
    form.action = `/admin/proyectos/{{ $proyecto->id_proyecto }}/materiales/${id}`;
    $('#confirmDeleteMaterialModal').modal({
        backdrop: true,
        keyboard: true
    });
}

// Update table dynamically
function updateTable(data) {
    const tableBody = document.querySelector('#materiales-table tbody');
    const row = document.createElement('tr');
    row.setAttribute('data-id', data.id_material);
    row.innerHTML = `
        <td class="px-6 py-4">${data.descripcion_mat}</td>
        <td class="px-6 py-4">${data.proveedor_nombre}</td>
        <td class="px-6 py-4">S/${parseFloat(data.monto_mat).toFixed(2)}</td>
        <td class="px-6 py-4">${data.fecha_mat}</td>
        <td class="px-6 py-4">${data.updated_at ? data.updated_at : 'N/A'}</td>
        <td class="px-6 py-4 flex space-x-3">
            <button data-id="${data.id_material}" onclick="editMaterial(${data.id_material})" class="text-yellow-500 hover:text-yellow-600 text-2xl">
                <i class="fas fa-edit"></i>
            </button>
            <button data-id="${data.id_material}" onclick="confirmDeleteMaterial(${data.id_material})" class="text-red-500 hover:text-red-600 text-2xl">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tableBody.insertBefore(row, tableBody.firstChild);
}

// Handle add material form submission
document.getElementById('addMaterialForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    // Pre-chequeo de presupuesto
    try {
        const m = await getBudgetMaterialsSummary();
        const montoNuevo = parseFloat(formData.get('monto_mat')) || 0;
        if (montoNuevo > (m.remaining || 0) + 1e-6) {
            showToast('No se puede agregar el material: se supera el límite del presupuesto de Materiales.', 'error', 7000);
            return;
        }
        if ((m.assigned || 0) > 0 && (m.remaining - montoNuevo) <= (m.assigned * 0.2)) {
            showToast('Aviso: con este registro, quedarás a menos del 20% del presupuesto de Materiales.', 'warning', 6000);
        }
    } catch(e) { console.warn('No se pudo validar presupuesto en cliente (Materiales):', e); }
    fetch(`/admin/proyectos/{{ $proyecto->id_proyecto }}/materiales`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.error || 'Error al guardar el material');
            });
        }
        return response.json();
    })
    .then(data => {
        showToast(data.message || 'Material agregado correctamente.', 'success', 5000);
        $('#addMaterialModal').modal('hide');
        updateTable(data.material);
        this.reset();
        refreshBudgetMaterials();
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message, 'error', 6000);
    });
});

// Handle edit material form submission
document.getElementById('editMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const materialId = document.getElementById('edit_materialId').value;
    fetch(`/admin/proyectos/{{ $proyecto->id_proyecto }}/materiales/${materialId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.error || 'Error al actualizar el material');
            });
        }
        return response.json();
    })
    .then(data => {
        showToast(data.message || 'Material actualizado correctamente.', 'success', 5000);
        $('#editMaterialModal').modal('hide');
        const row = document.querySelector(`tr[data-id="${data.material.id_material}"]`);
        if (row) {
            row.innerHTML = `
                <td class="px-6 py-4">${data.material.descripcion_mat}</td>
                <td class="px-6 py-4">${data.material.proveedor_nombre}</td>
                <td class="px-6 py-4">S/${parseFloat(data.material.monto_mat).toFixed(2)}</td>
                <td class="px-6 py-4">${data.material.fecha_mat}</td>
                <td class="px-6 py-4">${data.material.updated_at ? data.material.updated_at : 'N/A'}</td>
                <td class="px-6 py-4 flex space-x-3">
                    <button data-id="${data.material.id_material}" onclick="editMaterial(${data.material.id_material})" class="text-yellow-500 hover:text-yellow-600 text-2xl">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button data-id="${data.material.id_material}" onclick="confirmDeleteMaterial(${data.material.id_material})" class="text-red-500 hover:text-red-600 text-2xl">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message);
    });
});

// Handle delete material form submission
document.getElementById('deleteMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const action = this.action;
    fetch(action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.error || 'Error al eliminar el material');
            });
        }
        return response.json();
    })
    .then(data => {
        showToast(data.message || 'Material eliminado correctamente.', 'success', 5000);
        $('#confirmDeleteMaterialModal').modal('hide');
        const row = document.querySelector(`tr[data-id="${data.material_id}"]`);
        if (row) row.remove();
        refreshBudgetMaterials();
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message);
    });
});

// Validaciones de inputs
document.querySelectorAll('.letter-dot-only').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ0-9() \.'""]/g, '');
    });
});

document.querySelectorAll('.project-number-only').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
        const parts = this.value.split('.');
        if (parts.length > 2) {
            this.value = parts[0] + '.' + parts[1];
        }
    });
    
    input.addEventListener('blur', function() {
        if (this.value && !isNaN(this.value)) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
});
</script>