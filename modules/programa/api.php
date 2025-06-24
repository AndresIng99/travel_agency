<?php
// =====================================
// ARCHIVO: pages/programa.php - Página Mi Programa
// =====================================
?>
<?php $user = App::getUser(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Programa - <?= APP_NAME ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .action-card {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-card:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        }

        .action-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .action-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .action-description {
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Solicitudes Grid */
        .solicitudes-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 24px;
            color: #2d3748;
        }

        .add-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .solicitudes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .solicitud-card {
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .solicitud-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }

        .solicitud-id {
            font-size: 14px;
            font-weight: 600;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .solicitud-destino {
            font-size: 20px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        .viajero-info {
            margin-bottom: 15px;
        }

        .viajero-nombre {
            font-size: 16px;
            font-weight: 600;
            color: #2d3748;
        }

        .fecha-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
            color: #718096;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 8px;
            font-size: 14px;
            color: #4a5568;
        }

        .estado-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .estado-borrador {
            background: #fed7d7;
            color: #e53e3e;
        }

        .estado-activa {
            background: #c6f6d5;
            color: #2f855a;
        }

        .estado-completada {
            background: #bee3f8;
            color: #2b6cb0;
        }

        .card-actions {
            padding: 15px 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
        }

        .action-btn {
            flex: 1;
            padding: 8px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: none;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .action-btn.primary {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .action-btn.primary:hover {
            background: #5a67d8;
        }

        .action-btn.secondary {
            color: #4a5568;
        }

        .action-btn.secondary:hover {
            background: #f7fafc;
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
            max-width: 700px;
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
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .solicitudes-grid {
                grid-template-columns: 1fr;
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
            <a href="<?= APP_URL ?>/dashboard" class="back-btn">← Volver</a>
            <h2>✈️ Mi Programa</h2>
        </div>
        
        <div style="display: flex; align-items: center; gap: 15px;">
            <div id="google_translate_element"></div>
            <span><?= htmlspecialchars($user['name']) ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2 style="margin-bottom: 20px; color: #2d3748;">Acciones Rápidas</h2>
            <div class="actions-grid">
                <div class="action-card" onclick="openModal('create')">
                    <div class="action-icon">➕</div>
                    <div class="action-title">Nueva Solicitud</div>
                    <div class="action-description">Crear una nueva solicitud de viajero con destino, fechas y preferencias personalizadas</div>
                </div>
                
                <div class="action-card" onclick="showPersonalizacion()">
                    <div class="action-icon">🎨</div>
                    <div class="action-title">Personalizar Programa</div>
                    <div class="action-description">Configura títulos, idiomas, fotos de portada y opciones avanzadas de tus programas</div>
                </div>
                
                <div class="action-card" onclick="showBiblioteca()">
                    <div class="action-icon">📚</div>
                    <div class="action-title">Gestionar Biblioteca</div>
                    <div class="action-description">Administra días, alojamientos, actividades y transportes para usar en tus programas</div>
                </div>
            </div>
        </div>

        <!-- Solicitudes -->
        <div class="solicitudes-section">
            <div class="section-header">
                <h2 class="section-title">Mis Solicitudes de Viajero</h2>
                <button class="add-btn" onclick="openModal('create')">➕ Nueva Solicitud</button>
            </div>

            <div class="solicitudes-grid" id="solicitudesGrid">
                <!-- Las solicitudes se cargan dinámicamente aquí -->
            </div>

            <!-- Empty State -->
            <div class="empty-state" id="emptyState" style="display: none;">
                <div class="empty-state-icon">✈️</div>
                <h3>No tienes solicitudes aún</h3>
                <p>Comienza creando tu primera solicitud de viajero</p>
                <button class="add-btn" onclick="openModal('create')" style="margin-top: 20px;">➕ Crear Primera Solicitud</button>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Solicitud -->
    <div class="modal" id="solicitudModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Nueva Solicitud de Viajero</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>

            <form id="solicitudForm">
                <input type="hidden" id="solicitudId">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre_viajero">Nombre del Viajero</label>
                        <input type="text" id="nombre_viajero" name="nombre_viajero" required placeholder="Nombre completo">
                    </div>

                    <div class="form-group">
                        <label for="apellido_viajero">Apellido del Viajero</label>
                        <input type="text" id="apellido_viajero" name="apellido_viajero" required placeholder="Apellidos">
                    </div>

                    <div class="form-group">
                        <label for="destino">Destino</label>
                        <input type="text" id="destino" name="destino" required placeholder="Ciudad, País">
                    </div>

                    <div class="form-group">
                        <label for="numero_viajeros">Número de Viajeros</label>
                        <select id="numero_viajeros" name="numero_viajeros" required>
                            <option value="">Seleccionar</option>
                            <option value="1">1 persona</option>
                            <option value="2">2 personas</option>
                            <option value="3">3 personas</option>
                            <option value="4">4 personas</option>
                            <option value="5">5 personas</option>
                            <option value="6">6+ personas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha_llegada">Fecha de Llegada</label>
                        <input type="date" id="fecha_llegada" name="fecha_llegada" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha_salida">Fecha de Salida</label>
                        <input type="date" id="fecha_salida" name="fecha_salida" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="acompanamiento">Tipo de Acompañamiento Solicitado</label>
                    <textarea id="acompanamiento" name="acompanamiento" rows="3" placeholder="Describe el tipo de acompañamiento necesario..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Solicitud</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const APP_URL = '<?= APP_URL ?>';
        let solicitudes = [];

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            loadSolicitudes();
            initializeGoogleTranslate();
            setupFormValidation();
        });

        // Cargar solicitudes
        async function loadSolicitudes() {
            try {
                // Datos de ejemplo hasta que tengamos la API real
                const sampleData = [
                    {
                        id: 1,
                        id_solicitud: 'SOL2025001',
                        nombre_viajero: 'María',
                        apellido_viajero: 'García',
                        destino: 'París, Francia',
                        fecha_llegada: '2025-07-15',
                        fecha_salida: '2025-07-22',
                        numero_viajeros: 2,
                        acompanamiento: 'Pareja romántica',
                        estado: 'activa',
                        created_at: '2025-01-10'
                    },
                    {
                        id: 2,
                        id_solicitud: 'SOL2025002',
                        nombre_viajero: 'Carlos',
                        apellido_viajero: 'Rodríguez',
                        destino: 'Roma, Italia',
                        fecha_llegada: '2025-08-10',
                        fecha_salida: '2025-08-17',
                        numero_viajeros: 4,
                        acompanamiento: 'Familia con niños',
                        estado: 'borrador',
                        created_at: '2025-01-12'
                    }
                ];

                solicitudes = sampleData;
                renderSolicitudes();
            } catch (error) {
                console.error('Error al cargar solicitudes:', error);
                showEmptyState();
            }
        }

        // Renderizar solicitudes
        function renderSolicitudes() {
            const grid = document.getElementById('solicitudesGrid');
            const emptyState = document.getElementById('emptyState');

            if (solicitudes.length === 0) {
                showEmptyState();
                return;
            }

            grid.style.display = 'grid';
            emptyState.style.display = 'none';

            grid.innerHTML = solicitudes.map(solicitud => createSolicitudCard(solicitud)).join('');
        }

        // Crear card de solicitud
        function createSolicitudCard(solicitud) {
            const fechaLlegada = new Date(solicitud.fecha_llegada).toLocaleDateString();
            const fechaSalida = new Date(solicitud.fecha_salida).toLocaleDateString();
            const duracion = calcularDuracion(solicitud.fecha_llegada, solicitud.fecha_salida);

            const estadoClasses = {
                'borrador': 'estado-borrador',
                'activa': 'estado-activa',
                'completada': 'estado-completada',
                'cancelada': 'estado-cancelada'
            };

            const estadoTextos = {
                'borrador': 'Borrador',
                'activa': 'Activa',
                'completada': 'Completada',
                'cancelada': 'Cancelada'
            };

            return `
                <div class="solicitud-card" onclick="viewSolicitud(${solicitud.id})">
                    <div class="card-header">
                        <div class="solicitud-id">${solicitud.id_solicitud}</div>
                        <div class="solicitud-destino">${solicitud.destino}</div>
                    </div>
                    <div class="card-body">
                        <div class="viajero-info">
                            <div class="viajero-nombre">${solicitud.nombre_viajero} ${solicitud.apellido_viajero}</div>
                        </div>
                        
                        <div class="fecha-info">
                            <span>📅 ${fechaLlegada}</span>
                            <span>📅 ${fechaSalida}</span>
                        </div>
                        
                        <div class="info-item">
                            <span>👥</span>
                            <span>${solicitud.numero_viajeros} viajero${solicitud.numero_viajeros > 1 ? 's' : ''}</span>
                        </div>
                        
                        <div class="info-item">
                            <span>⏱️</span>
                            <span>${duracion} días</span>
                        </div>
                        
                        ${solicitud.acompanamiento ? `
                        <div class="info-item">
                            <span>🤝</span>
                            <span>${solicitud.acompanamiento}</span>
                        </div>
                        ` : ''}
                        
                        <div style="margin-top: 15px;">
                            <span class="estado-badge ${estadoClasses[solicitud.estado]}">
                                ${estadoTextos[solicitud.estado]}
                            </span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="action-btn primary" onclick="event.stopPropagation(); editSolicitud(${solicitud.id})">
                            ✏️ Editar
                        </button>
                        <button class="action-btn secondary" onclick="event.stopPropagation(); duplicateSolicitud(${solicitud.id})">
                            📋 Duplicar
                        </button>
                        <button class="action-btn secondary" onclick="event.stopPropagation(); deleteSolicitud(${solicitud.id})">
                            🗑️ Eliminar
                        </button>
                    </div>
                </div>
            `;
        }

        // Calcular duración del viaje
        function calcularDuracion(fechaLlegada, fechaSalida) {
            const llegada = new Date(fechaLlegada);
            const salida = new Date(fechaSalida);
            const diffTime = Math.abs(salida - llegada);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return diffDays;
        }

        // Mostrar estado vacío
        function showEmptyState() {
            document.getElementById('solicitudesGrid').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
        }

        // Funciones del modal
        function openModal(mode, id = null) {
            const modal = document.getElementById('solicitudModal');
            const title = document.getElementById('modalTitle');

            if (mode === 'create') {
                title.textContent = 'Nueva Solicitud de Viajero';
                document.getElementById('solicitudForm').reset();
                document.getElementById('solicitudId').value = '';
            } else if (mode === 'edit' && id) {
                title.textContent = 'Editar Solicitud de Viajero';
                loadSolicitudData(id);
            }

            modal.classList.add('show');
            
            // Configurar fecha mínima
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('fecha_llegada').min = today;
            document.getElementById('fecha_salida').min = today;
        }

        function closeModal() {
            document.getElementById('solicitudModal').classList.remove('show');
        }

        // Cargar datos de solicitud para editar
        function loadSolicitudData(id) {
            const solicitud = solicitudes.find(s => s.id === id);
            if (solicitud) {
                document.getElementById('solicitudId').value = solicitud.id;
                document.getElementById('nombre_viajero').value = solicitud.nombre_viajero;
                document.getElementById('apellido_viajero').value = solicitud.apellido_viajero;
                document.getElementById('destino').value = solicitud.destino;
                document.getElementById('fecha_llegada').value = solicitud.fecha_llegada;
                document.getElementById('fecha_salida').value = solicitud.fecha_salida;
                document.getElementById('numero_viajeros').value = solicitud.numero_viajeros;
                document.getElementById('acompanamiento').value = solicitud.acompanamiento || '';
            }
        }

        // Configurar validación del formulario
        function setupFormValidation() {
            const fechaLlegada = document.getElementById('fecha_llegada');
            const fechaSalida = document.getElementById('fecha_salida');

            fechaLlegada.addEventListener('change', function() {
                fechaSalida.min = this.value;
                if (fechaSalida.value && fechaSalida.value < this.value) {
                    fechaSalida.value = this.value;
                }
            });

            fechaSalida.addEventListener('change', function() {
                if (this.value < fechaLlegada.value) {
                    alert('La fecha de salida no puede ser anterior a la fecha de llegada');
                    this.value = fechaLlegada.value;
                }
            });
        }

        // Submit del formulario
        document.getElementById('solicitudForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            const id = document.getElementById('solicitudId').value;

            try {
                if (id) {
                    // Actualizar solicitud existente
                    const index = solicitudes.findIndex(s => s.id == id);
                    if (index !== -1) {
                        solicitudes[index] = { ...solicitudes[index], ...data };
                    }
                    alert('Solicitud actualizada correctamente');
                } else {
                    // Crear nueva solicitud
                    const newSolicitud = {
                        id: Date.now(),
                        id_solicitud: generateSolicitudId(),
                        ...data,
                        estado: 'borrador',
                        created_at: new Date().toISOString().split('T')[0]
                    };
                    solicitudes.unshift(newSolicitud);
                    alert('Solicitud creada correctamente');
                }

                closeModal();
                renderSolicitudes();

                // TODO: Enviar a API real
            } catch (error) {
                alert('Error al guardar la solicitud: ' + error.message);
            }
        });

        // Generar ID de solicitud único
        function generateSolicitudId() {
            const year = new Date().getFullYear();
            const count = solicitudes.length + 1;
            return `SOL${year}${count.toString().padStart(3, '0')}`;
        }

        // Funciones CRUD
        function viewSolicitud(id) {
            // Redirigir a vista detallada (implementar después)
            alert(`Ver detalles de la solicitud ${id}`);
        }

        function editSolicitud(id) {
            openModal('edit', id);
        }

        function duplicateSolicitud(id) {
            const solicitud = solicitudes.find(s => s.id === id);
            if (solicitud) {
                const duplicated = {
                    ...solicitud,
                    id: Date.now(),
                    id_solicitud: generateSolicitudId(),
                    estado: 'borrador',
                    created_at: new Date().toISOString().split('T')[0]
                };
                solicitudes.unshift(duplicated);
                renderSolicitudes();
                alert('Solicitud duplicada correctamente');
            }
        }

        function deleteSolicitud(id) {
            if (confirm('¿Estás seguro de que quieres eliminar esta solicitud?')) {
                solicitudes = solicitudes.filter(s => s.id !== id);
                renderSolicitudes();
                alert('Solicitud eliminada correctamente');
            }
        }

        // Funciones de navegación
        function showPersonalizacion() {
            alert('Módulo de personalización en desarrollo');
        }

        function showBiblioteca() {
            window.location.href = APP_URL + '/biblioteca';
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

            // Cargar script
            if (!window.googleTranslateElementInit) {
                window.googleTranslateElementInit = googleTranslateElementInit;
                const script = document.createElement('script');
                script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
                document.head.appendChild(script);
            }

            // Detectar cambios
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
        document.getElementById('solicitudModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
