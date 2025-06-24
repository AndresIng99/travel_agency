<?php
// =====================================
// ARCHIVO: pages/admin.php - Panel REAL de Administrador
// =====================================
?>
<?php 
App::requireRole('admin');
$user = App::getUser(); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - <?= APP_NAME ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #e53e3e 0%, #fd746c 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Google Translate */
        #google_translate_element {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 20px;
        }

        .goog-te-banner-frame.skiptranslate { display: none !important; }
        body { top: 0px !important; }

        /* Main Content */
        .main-content {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Stats Cards */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #e53e3e;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            color: #718096;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            font-size: 24px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #2d3748;
        }

        .stat-loading {
            font-size: 16px;
            color: #718096;
            font-style: italic;
        }

        /* Management Sections */
        .management-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .section-title {
            font-size: 24px;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .add-btn {
            background: linear-gradient(135deg, #e53e3e 0%, #fd746c 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: transform 0.3s ease;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 62, 62, 0.3);
        }

        /* Users Table */
        .table-container {
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .users-table th,
        .users-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .users-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .users-table tr:hover {
            background: #f7fafc;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e53e3e 0%, #fd746c 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-details h4 {
            color: #2d3748;
            margin-bottom: 2px;
        }

        .user-details p {
            color: #718096;
            font-size: 13px;
        }

        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .role-admin {
            background: #fed7d7;
            color: #e53e3e;
        }

        .role-agent {
            background: #c6f6d5;
            color: #2f855a;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #c6f6d5;
            color: #2f855a;
        }

        .status-inactive {
            background: #fed7d7;
            color: #e53e3e;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #bee3f8;
            color: #2b6cb0;
        }

        .btn-edit:hover {
            background: #90cdf4;
        }

        .btn-delete {
            background: #fed7d7;
            color: #e53e3e;
        }

        .btn-delete:hover {
            background: #feb2b2;
        }

        .btn-toggle {
            background: #d6f5d6;
            color: #2f855a;
        }

        .btn-toggle:hover {
            background: #c6f6d5;
        }

        .btn-toggle.inactive {
            background: #fbb6ce;
            color: #97266d;
        }

        /* Loading Spinner */
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #e53e3e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-message {
            background: #fed7d7;
            color: #e53e3e;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #feb2b2;
        }

        .success-message {
            background: #c6f6d5;
            color: #2f855a;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #9ae6b4;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-title {
            font-size: 24px;
            color: #2d3748;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #718096;
            padding: 5px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 500;
            color: #4a5568;
        }

        .form-group input,
        .form-group select {
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #e53e3e;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-primary {
            background: linear-gradient(135deg, #e53e3e 0%, #fd746c 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            background: #2f855a;
        }

        .toast.error {
            background: #e53e3e;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }

            .stats-section {
                grid-template-columns: 1fr;
            }

            .users-table {
                font-size: 14px;
            }

            .users-table th,
            .users-table td {
                padding: 10px;
            }

            .modal-content {
                margin: 10px;
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <a href="<?= APP_URL ?>/dashboard" class="back-btn">← Dashboard</a>
            <h2>⚙️ Panel de Administrador</h2>
        </div>
        
        <div style="display: flex; align-items: center; gap: 15px;">
            <div id="google_translate_element"></div>
            <span><?= htmlspecialchars($user['name']) ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Statistics -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Total Usuarios</div>
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-number" id="totalUsers">
                    <div class="stat-loading">Cargando...</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Programas Activos</div>
                    <div class="stat-icon">✈️</div>
                </div>
                <div class="stat-number" id="totalPrograms">
                    <div class="stat-loading">Cargando...</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Recursos Biblioteca</div>
                    <div class="stat-icon">📚</div>
                </div>
                <div class="stat-number" id="totalResources">
                    <div class="stat-loading">Cargando...</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Sesiones Activas</div>
                    <div class="stat-icon">🔗</div>
                </div>
                <div class="stat-number" id="activeSessions">
                    <div class="stat-loading">Cargando...</div>
                </div>
            </div>
        </div>

        <!-- User Management -->
        <div class="management-section">
            <div class="section-header">
                <h2 class="section-title">
                    <span>👥</span>
                    Gestión de Usuarios
                </h2>
                <button class="add-btn" onclick="openUserModal('create')">➕ Nuevo Usuario</button>
            </div>

            <div class="loading" id="usersLoading">
                <div class="spinner"></div>
                <p>Cargando usuarios...</p>
            </div>

            <div id="usersError" class="error-message" style="display: none;"></div>

            <div class="table-container">
                <table class="users-table" id="usersTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Último Acceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Los usuarios se cargan dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Usuarios -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="userModalTitle">Nuevo Usuario</h2>
                <button class="close-btn" onclick="closeUserModal()">&times;</button>
            </div>

            <form id="userForm">
                <input type="hidden" id="userId">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Nombre de Usuario *</label>
                        <input type="text" id="username" name="username" required placeholder="usuario123" maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required placeholder="usuario@ejemplo.com" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="full_name">Nombre Completo *</label>
                        <input type="text" id="full_name" name="full_name" required placeholder="Juan Pérez" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="role">Rol *</label>
                        <select id="role" name="role" required>
                            <option value="">Seleccionar rol</option>
                            <option value="agent">Agente de Viajes</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div class="form-group" id="passwordGroup">
                        <label for="password">Contraseña *</label>
                        <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="active">Estado</label>
                        <select id="active" name="active">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeUserModal()">Cancelar</button>
                    <button type="submit" class="btn-primary" id="submitBtn">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const APP_URL = '<?= APP_URL ?>';
        let users = [];
        let isLoading = false;

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            loadUsers();
            initializeGoogleTranslate();
        });

        // Funciones de API
        async function apiRequest(endpoint, options = {}) {
            try {
                const response = await fetch(`${APP_URL}${endpoint}`, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    ...options
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Error en la respuesta del servidor');
                }

                return data;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }

        // Cargar estadísticas
        async function loadStatistics() {
            try {
                const response = await apiRequest('/admin/api?action=statistics');
                const stats = response.data;

                document.getElementById('totalUsers').textContent = stats.totalUsers;
                document.getElementById('totalPrograms').textContent = stats.totalPrograms;
                document.getElementById('totalResources').textContent = stats.totalResources;
                document.getElementById('activeSessions').textContent = stats.activeSessions;
            } catch (error) {
                console.error('Error al cargar estadísticas:', error);
                
                // Mostrar valores por defecto en caso de error
                document.getElementById('totalUsers').textContent = '0';
                document.getElementById('totalPrograms').textContent = '0';
                document.getElementById('totalResources').textContent = '0';
                document.getElementById('activeSessions').textContent = '0';
            }
        }

        // Cargar usuarios
        async function loadUsers() {
            const loading = document.getElementById('usersLoading');
            const table = document.getElementById('usersTable');
            const errorDiv = document.getElementById('usersError');
            
            loading.style.display = 'block';
            table.style.display = 'none';
            errorDiv.style.display = 'none';
            
            try {
                const response = await apiRequest('/admin/api?action=users');
                users = response.data;
                renderUsers();
                
                loading.style.display = 'none';
                table.style.display = 'table';
            } catch (error) {
                console.error('Error al cargar usuarios:', error);
                
                loading.style.display = 'none';
                errorDiv.textContent = `Error al cargar usuarios: ${error.message}`;
                errorDiv.style.display = 'block';
            }
        }

        // Renderizar usuarios en tabla
        function renderUsers() {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = users.map(user => createUserRow(user)).join('');
        }

        // Crear fila de usuario - BOTONES DINÁMICOS CORREGIDOS
        function createUserRow(user) {
            const roleClass = user.role === 'admin' ? 'role-admin' : 'role-agent';
            const roleText = user.role === 'admin' ? 'Administrador' : 'Agente';
            const statusClass = user.active ? 'status-active' : 'status-inactive';
            const statusText = user.active ? 'Activo' : 'Inactivo';
            const initials = user.full_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            const lastLogin = user.last_login_formatted || 'Nunca';

            // Lógica dinámica para botones según el estado del usuario
            let actionButtons = `
                <button class="action-btn btn-edit" onclick="editUser(${user.id})" title="Editar usuario">
                    ✏️ Editar
                </button>
            `;

            // Solo mostrar botones de estado si no es el admin principal
            if (user.id !== 1) {
                if (user.active) {
                    // Usuario activo: mostrar "Desactivar" y "Deshabilitar"
                    actionButtons += `
                        <button class="action-btn btn-toggle" onclick="toggleUserStatus(${user.id})" title="Desactivar usuario">
                            ⏸️ Desactivar
                        </button>
                    `;
                } else {
                    // Usuario inactivo: solo mostrar "Activar"
                    actionButtons += `
                        <button class="action-btn btn-toggle inactive" onclick="toggleUserStatus(${user.id})" title="Activar usuario">
                            ▶️ Activar
                        </button>
                    `;
                }
            } else {
                // Admin principal: solo botón de desactivar (deshabilitado)
                actionButtons += `
                    <button class="action-btn btn-toggle" style="opacity: 0.5; cursor: not-allowed;" title="No se puede desactivar el administrador principal">
                        ⏸️ Desactivar
                    </button>
                `;
            }

            return `
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">${initials}</div>
                            <div class="user-details">
                                <h4>${escapeHtml(user.full_name)}</h4>
                                <p>@${escapeHtml(user.username)}</p>
                            </div>
                        </div>
                    </td>
                    <td>${escapeHtml(user.email)}</td>
                    <td><span class="role-badge ${roleClass}">${roleText}</span></td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td>${lastLogin}</td>
                    <td>
                        <div class="action-buttons">
                            ${actionButtons}
                        </div>
                    </td>
                </tr>
            `;
        }

        // Escape HTML para prevenir XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Funciones del modal de usuario
        function openUserModal(mode, id = null) {
            const modal = document.getElementById('userModal');
            const title = document.getElementById('userModalTitle');
            const passwordGroup = document.getElementById('passwordGroup');
            const passwordField = document.getElementById('password');

            if (mode === 'create') {
                title.textContent = 'Nuevo Usuario';
                document.getElementById('userForm').reset();
                document.getElementById('userId').value = '';
                passwordField.required = true;
                passwordGroup.style.display = 'block';
                passwordGroup.querySelector('label').textContent = 'Contraseña *';
            } else if (mode === 'edit' && id) {
                title.textContent = 'Editar Usuario';
                loadUserData(id);
                passwordField.required = false;
                passwordGroup.style.display = 'block';
                passwordGroup.querySelector('label').textContent = 'Nueva Contraseña (opcional)';
            }

            modal.classList.add('show');
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('show');
        }

        function loadUserData(id) {
            const user = users.find(u => u.id === id);
            if (user) {
                document.getElementById('userId').value = user.id;
                document.getElementById('username').value = user.username;
                document.getElementById('email').value = user.email;
                document.getElementById('full_name').value = user.full_name;
                document.getElementById('role').value = user.role;
                document.getElementById('active').value = user.active ? '1' : '0';
                document.getElementById('password').value = '';
            }
        }

        // Submit del formulario de usuario
        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (isLoading) return;

            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;
            
            try {
                isLoading = true;
                submitBtn.textContent = 'Guardando...';
                submitBtn.disabled = true;

                const formData = new FormData(this);
                const id = document.getElementById('userId').value;

                if (id) {
                    formData.append('action', 'update_user');
                    formData.append('id', id);
                } else {
                    formData.append('action', 'create_user');
                }

                // Debug: mostrar datos que se envían
                console.log('Enviando datos:', Object.fromEntries(formData.entries()));

                const response = await fetch(`${APP_URL}/admin/api`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Error al guardar usuario');
                }

                showToast(data.message, 'success');
                closeUserModal();
                await loadUsers();
                await loadStatistics();

            } catch (error) {
                console.error('Error al guardar usuario:', error);
                showToast(`Error al guardar usuario: ${error.message}`, 'error');
            } finally {
                isLoading = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });

        // Cargar datos de usuario para editar - CORREGIDO
        function loadUserData(id) {
            const user = users.find(u => u.id == id); // Usar == en lugar de ===
            if (user) {
                console.log('Cargando usuario:', user); // Debug
                
                document.getElementById('userId').value = user.id;
                document.getElementById('username').value = user.username || '';
                document.getElementById('email').value = user.email || '';
                document.getElementById('full_name').value = user.full_name || '';
                document.getElementById('role').value = user.role || '';
                document.getElementById('active').value = user.active ? '1' : '0';
                document.getElementById('password').value = '';
            } else {
                console.error('Usuario no encontrado:', id);
                showToast('Usuario no encontrado', 'error');
            }
        }

        // Función editUser mejorada
        function editUser(id) {
            console.log('Editando usuario ID:', id, typeof id); // Debug
            openUserModal('edit', id);
        }

        // Funciones CRUD de usuarios
        function editUser(id) {
            openUserModal('edit', id);
        }

        async function toggleUserStatus(id) {
            const user = users.find(u => u.id === id);
            if (!user) return;

            const action = user.active ? 'desactivar' : 'activar';
            if (!confirm(`¿Estás seguro de que quieres ${action} este usuario?`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'toggle_user');
                formData.append('id', id);

                const response = await fetch(`${APP_URL}/admin/api`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Error al cambiar estado del usuario');
                }

                showToast(data.message, 'success');
                await loadUsers();
                await loadStatistics();

            } catch (error) {
                console.error('Error al cambiar estado:', error);
                showToast(`Error: ${error.message}`, 'error');
            }
        }

        async function deleteUser(id) {
            const user = users.find(u => u.id === id);
            if (!user) return;

            if (!confirm(`¿Estás seguro de que quieres eliminar el usuario "${user.username}"? Esta acción no se puede deshacer.`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'delete_user');
                formData.append('id', id);

                const response = await fetch(`${APP_URL}/admin/api`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Error al eliminar usuario');
                }

                showToast(data.message, 'success');
                await loadUsers();
                await loadStatistics();

            } catch (error) {
                console.error('Error al eliminar usuario:', error);
                showToast(`Error: ${error.message}`, 'error');
            }
        }

        // Mostrar notificaciones toast
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => document.body.removeChild(toast), 300);
            }, 4000);
        }

        // Google Translate
        function initializeGoogleTranslate() {
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'es',
                    includedLanguages: 'en,fr,pt,it,de,es',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                    autoDisplay: false
                }, 'google_translate_element');

                setTimeout(loadSavedLanguage, 1000);
            }

            function saveLanguage(lang) {
                sessionStorage.setItem('language', lang);
                localStorage.setItem('preferredLanguage', lang);
            }

            function loadSavedLanguage() {
                const saved = sessionStorage.getItem('language') || localStorage.getItem('preferredLanguage');
                if (saved && saved !== 'es') {
                    const select = document.querySelector('.goog-te-combo');
                    if (select) {
                        select.value = saved;
                        select.dispatchEvent(new Event('change'));
                    }
                }
            }

            if (!window.googleTranslateElementInit) {
                window.googleTranslateElementInit = googleTranslateElementInit;
                const script = document.createElement('script');
                script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
                document.head.appendChild(script);
            }

            setTimeout(function() {
                const select = document.querySelector('.goog-te-combo');
                if (select) {
                    select.addEventListener('change', function() {
                        if (this.value) saveLanguage(this.value);
                    });
                }
            }, 2000);
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserModal();
            }
        });
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>