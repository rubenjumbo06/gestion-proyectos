@props(['proyecto'])

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-700">Gestión de Materiales</h2>
        <button id="add-material-btn" 
                data-toggle="modal" 
                data-target="#addMaterialModal" 
                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-4 px-8 rounded-full text-xl transition duration-300 flex items-center">
            <i class="fas fa-plus-circle mr-2 text-2xl"></i>Agregar Material
        </button>
    </div>

    <div id="material-error" class="hidden text-red-600 mb-4 text-lg"></div>

    <div class="overflow-x-auto shadow-md rounded-lg">
        <table id="materiales-table" class="min-w-full bg-white border border-gray-200 text-xl">
            <thead class="bg-gray-200">
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
                        <td class="px-6 py-4 flex space-x-3">
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
        {{ $materiales->links() }}
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
                            <label for="id_proveedor" class="font-semibold text-left text-black">Proveedor</label>
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
</style>

<script>
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
document.getElementById('addMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
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
        alert(data.message);
        $('#addMaterialModal').modal('hide');
        updateTable(data.material);
        this.reset();
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message);
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
        alert(data.message);
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
        alert(data.message);
        $('#confirmDeleteMaterialModal').modal('hide');
        const row = document.querySelector(`tr[data-id="${data.material_id}"]`);
        if (row) row.remove();
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message);
    });
});

// Validaciones de inputs
document.querySelectorAll('.letter-dot-only').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.'"]/g, '');
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