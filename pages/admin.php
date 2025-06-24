<?php
// =====================================
// ARCHIVO: pages/admin.php - Panel de Administrador Completo
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

        .stat-change {
            font-size: 12px;
            margin-top: 5px;
        }

        .stat-increase {
            color: #2f855a;
        }

        .stat-decrease {
            color: #e53e3e;
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

        /* Configuration Section */
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .config-card {
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .config-card:hover {
            border-color: #e53e3e;
            box-shadow: 0 5px 15px rgba(229, 62, 62, 0.1);
        }

        .config-icon {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .config-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .config-description {
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
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

        /* Loading Spinner */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
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

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }

            .stats-section {
                grid-template-columns: 1fr;
            }

            .config-grid {
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
                <div class="stat-number" id="totalUsers">-</div>
                <div class="stat-change stat-increase">↗ +2 este mes</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Programas Activos</div>
                    <div class="stat-icon">✈️</div>
                </div>
                <div class="stat-number" id="totalPrograms">-</div>
                <div class="stat-change stat-increase">↗ +15 esta semana</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Recursos Biblioteca</div>
                    <div class="stat-icon">📚</div>
                </div>
                <div class="stat-number" id="totalResources">-</div>
                <div class="stat-change stat-increase">↗ +8 hoy</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Sesiones Activas</div>
                    <div class="stat-icon">🔗</div>
                </div>
                <div class="stat-number" id="activeSessions">-</div>
                <div class="stat-change stat-decrease">↘ -3 vs ayer</div>
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

        <!-- System Configuration -->
        <div class="management-section">
            <div class="section-header">
                <h2 class="section-title">
                    <span>⚙️</span>
                    Configuración del Sistema
                </h2>
            </div>

            <div class="config-grid">
                <div class="config-card" onclick="openConfigModal('company')">
                    <div class="config-icon">🏢</div>
                    <div class="config-title">Información de Empresa</div>
                    <div class="config-description">Configura el nombre, logo y colores de la empresa</div>
                </div>

                <div class="config-card" onclick="openConfigModal('appearance')">
                    <div class="config-icon">🎨</div>
                    <div class="config-title">Apariencia</div>
                    <div class="config-description">Personaliza colores, temas y elementos visuales</div>
                </div>

                <div class="config-card" onclick="openConfigModal('integrations')">
                    <div class="config-icon">🔗</div>
                    <div class="config-title">Integraciones</div>
                    <div class="config-description">Configura APIs de Mapbox, Google Translate y más</div>
                </div>

                <div class="config-card" onclick="openConfigModal('security')">
                    <div class="config-icon">🔒</div>
                    <div class="config-title">Seguridad</div>
                    <div class="config-description">Gestiona políticas de seguridad y acceso</div>
                </div>

                <div class="config-card" onclick="openConfigModal('backup')">
                    <div class="config-icon">💾</div>
                    <div class="config-title">Respaldos</div>
                    <div class="config-description">Configura respaldos automáticos de datos</div>
                </div>

                <div class="config-card" onclick="openConfigModal('reports')">
                    <div class="config-icon">📊</div>
                    <div class="config-title">Reportes</div>
                    <div class="config-description">Configura reportes automáticos y estadísticas</div>
                </div>
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
                        <label for="username">Nombre de Usuario</label>
                        <input type="text" id="username" name="username" required placeholder="usuario123">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="usuario@ejemplo.com">
                    </div>

                    <div class="form-group">
                        <label for="full_name">Nombre Completo</label>
                        <input type="text" id="full_name" name="full_name" required placeholder="Juan Pérez">
                    </div>

                    <div class="form-group">
                        <label for="role">Rol</label>
                        <select id="role" name="role" required>
                            <option value="">Seleccionar rol</option>
                            <option value="agent">Agente de Viajes</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div class="form-group" id="passwordGroup">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres">
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
                    <button type="submit" class="btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const APP_URL = '<?= APP_URL ?>';
        let users = [];

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            loadUsers();
            initializeGoogleTranslate();
        });

        // Cargar estadísticas
        async function loadStatistics() {
            try {
                // Simulación de datos hasta tener API real
                const stats = {
                    totalUsers: 5,
                    totalPrograms: 23,
                    totalResources: 156,
                    activeSessions: 3
                };

                document.getElementById('totalUsers').textContent = stats.totalUsers;
                document.getElementById('totalPrograms').textContent = stats.totalPrograms;
                document.getElementById('totalResources').textContent = stats.totalResources;
                document.getElementById('activeSessions').textContent = stats.activeSessions;

                // TODO: Conectar con API real
                // const response = await fetch(`${APP_URL}/admin/api?action=stats`);
                // const data = await response.json();
            } catch (error) {
                console.error('Error al cargar estadísticas:', error);
            }
        }

        // Cargar usuarios
        async function loadUsers() {
            const loading = document.getElementById('usersLoading');
            const table = document.getElementById('usersTable');
            
            loading.style.display = 'block';
            table.style.display = 'none';

            try {
                // Datos de ejemplo hasta tener API real
                const sampleUsers = [
                    {
                        id: 1,
                        username: 'admin',
                        email: 'admin@travelagency.com',
                        full_name: 'Administrador',
                        role: 'admin',
                        active: 1,
                        last_login: '2025-01-15 09:30:00'
                    },
                    {
                        id: 2,
                        username: 'agente1',
                        email: 'agente@travelagency.com',
                        full_name: 'Agente de Viajes',
                        role: 'agent',
                        active: 1,
                        last_login: '2025-01-15 08:45:00'
                    },
                    {
                        id: 3,
                        username: 'maria.garcia',
                        email: 'maria@travelagency.com',
                        full_name: 'María García',
                        role: 'agent',
                        active: 1,
                        last_login: '2025-01-14 16:20:00'
                    }
                ];

                users = sampleUsers;
                renderUsers();

                // TODO: Conectar con API real
                // const response = await fetch(`${APP_URL}/admin/api?action=users`);
                // const data = await response.json();
                // users = data.users;
                // renderUsers();
            } catch (error) {
                console.error('Error al cargar usuarios:', error);
            } finally {
                loading.style.display = 'none';
                table.style.display = 'table';
            }
        }

        // Renderizar usuarios en tabla
        function renderUsers() {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = users.map(user => createUserRow(user)).join('');
        }

        // Crear fila de usuario
        function createUserRow(user) {
            const lastLogin = user.last_login ? new Date(user.last_login).toLocaleDateString() : 'Nunca';
            const roleClass = user.role === 'admin' ? 'role-admin' : 'role-agent';
            const roleText = user.role === 'admin' ? 'Administrador' : 'Agente';
            const statusClass = user.active ? 'status-active' : 'status-inactive';
            const statusText = user.active ? 'Activo' : 'Inactivo';
            const initials = user.full_name.split(' ').map(n => n[0]).join('').substring(0, 2);

            return `
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">${initials}</div>
                            <div class="user-details">
                                <h4>${user.full_name}</h4>
                                <p>@${user.username}</p>
                            </div>
                        </div>
                    </td>
                    <td>${user.email}</td>
                    <td><span class="role-badge ${roleClass}">${roleText}</span></td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td>${lastLogin}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn btn-edit" onclick="editUser(${user.id})">
                                ✏️ Editar
                            </button>
                            <button class="action-btn btn-toggle" onclick="toggleUserStatus(${user.id})">
                                ${user.active ? '⏸️' : '▶️'} ${user.active ? 'Desactivar' : 'Activar'}
                            </button>
                            ${user.id !== 1 ? `
                            <button class="action-btn btn-delete" onclick="deleteUser(${user.id})">
                                🗑️ Eliminar
                            </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }

        // Funciones del modal de usuario
        function openUserModal(mode, id = null) {
            const modal = document.getElementById('userModal');
            const title = document.getElementById('userModalTitle');
            const passwordGroup = document.getElementById('passwordGroup');

            if (mode === 'create') {
                title.textContent = 'Nuevo Usuario';
                document.getElementById('userForm').reset();
                document.getElementById('userId').value = '';
                document.getElementById('password').required = true;
                passwordGroup.style.display = 'block';
            } else if (mode === 'edit' && id) {
                title.textContent = 'Editar Usuario';
                loadUserData(id);
                document.getElementById('password').required = false;
                passwordGroup.style.display = 'block';
                // Cambiar label de contraseña
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

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            const id = document.getElementById('userId').value;

            try {
                if (id) {
                    // Actualizar usuario existente
                    const index = users.findIndex(u => u.id == id);
                    if (index !== -1) {
                        users[index] = { ...users[index], ...data, id: parseInt(id) };
                    }
                    alert('Usuario actualizado correctamente');
                } else {
                    // Crear nuevo usuario
                    const newUser = {
                        id: Date.now(),
                        ...data,
                        active: parseInt(data.active),
                        last_login: null
                    };
                    users.push(newUser);
                    alert('Usuario creado correctamente');
                }

                closeUserModal();
                renderUsers();
                loadStatistics(); // Actualizar estadísticas

                // TODO: Enviar a API real
                // const response = await fetch(`${APP_URL}/admin/api`, {
                //     method: 'POST',
                //     body: formData
                // });
            } catch (error) {
                alert('Error al guardar usuario: ' + error.message);
            }
        });

        // Funciones CRUD de usuarios
        function editUser(id) {
            openUserModal('edit', id);
        }

        function toggleUserStatus(id) {
            const user = users.find(u => u.id === id);
            if (user) {
                const action = user.active ? 'desactivar' : 'activar';
                if (confirm(`¿Estás seguro de que quieres ${action} este usuario?`)) {
                    user.active = !user.active;
                    renderUsers();
                    alert(`Usuario ${action}do correctamente`);
                    
                    // TODO: Enviar a API real
                }
            }
        }

        function deleteUser(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.')) {
                users = users.filter(u => u.id !== id);
                renderUsers();
                loadStatistics();
                alert('Usuario eliminado correctamente');
                
                // TODO: Enviar a API real
            }
        }

        // Funciones de configuración
        function openConfigModal(configType) {
            switch(configType) {
                case 'company':
                    alert('Configuración de Empresa - En desarrollo');
                    break;
                case 'appearance':
                    alert('Configuración de Apariencia - En desarrollo');
                    break;
                case 'integrations':
                    alert('Configuración de Integraciones - En desarrollo');
                    break;
                case 'security':
                    alert('Configuración de Seguridad - En desarrollo');
                    break;
                case 'backup':
                    alert('Configuración de Respaldos - En desarrollo');
                    break;
                case 'reports':
                    alert('Configuración de Reportes - En desarrollo');
                    break;
            }
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
</body>
</html>