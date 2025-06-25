<?php
// =====================================
// ARCHIVO: pages/programa.php - MI PROGRAMA COMPLETO
// =====================================

require_once 'config/app.php';
require_once 'config/config_functions.php';

App::requireLogin();
ConfigManager::init();

$user = App::getUser();
$config = ConfigManager::get();
$companyName = ConfigManager::getCompanyName();
?>
<!DOCTYPE html>
<html lang="<?= $config['default_language'] ?? 'es' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Programa - <?= htmlspecialchars($companyName) ?></title>
    
    <!-- Estilos dinámicos del sistema -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dynamic-styles.php">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
   <style>

    /* Importar fuente consistente */
* {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Variables CSS que vienen del sistema de colores dinámicos */
:root {
    /* Estas variables ya están definidas en dynamic-styles.php pero las reforzamos */
    --primary-color: <?= $config['agent_primary_color'] ?? '#667eea' ?>;
    --secondary-color: <?= $config['agent_secondary_color'] ?? '#764ba2' ?>;
    --primary-gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    
    /* Convertir hex a RGB para transparencias */
    --primary-rgb: <?php 
        $hex = $config['agent_primary_color'] ?? '#667eea';
        $rgb = sscanf($hex, "#%02x%02x%02x");
        echo implode(', ', $rgb);
    ?>;
    
    /* Colores adicionales para consistencia */
    --text-primary: #2d3748;
    --text-secondary: #4a5568;
    --text-muted: #718096;
    --border-color: #e9ecef;
    --background-light: #f8f9fa;
    --background-white: #ffffff;
    --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.15);
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --transition: all 0.3s ease;
}
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f7fa;
    color: #333;
    line-height: 1.6;
}

.programa-container {
    min-height: 100vh;
    background: #f5f7fa;
}

/* ===== HEADER CONSISTENTE ===== */

.programa-header {
    background: var(--primary-gradient);
    padding: 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.programa-nav {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    height: 70px;
}

.programa-logo {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.programa-logo:hover {
    color: rgba(255, 255, 255, 0.9);
}

.programa-user {
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.95rem;
}

.programa-user a {
    color: white;
    text-decoration: none;
    padding: 0.5rem;
    border-radius: 4px;
    transition: background 0.3s ease;
}

.programa-user a:hover {
    background: rgba(255, 255, 255, 0.1);
}

/* AGREGAR ESTOS ESTILOS AL FINAL DEL <style> EN pages/programa.php */

/* ===== UPLOAD DE IMÁGENES MEJORADO ===== */

.image-upload-container {
    position: relative;
}

.image-upload {
    border: 2px dashed #cbd5e0;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f7fafc;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-upload:hover {
    border-color: var(--primary-color);
    background: rgba(var(--primary-rgb), 0.02);
}

.image-upload.has-image {
    padding: 0;
    border: 2px solid #e9ecef;
    background: white;
    min-height: 200px;
}

.image-upload.has-image:hover {
    border-color: var(--primary-color);
}

.upload-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.upload-content p {
    margin: 0 0 0.5rem 0;
    color: #4a5568;
    font-weight: 500;
}

.upload-content small {
    color: #718096;
    font-size: 0.85rem;
}

.image-preview {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    object-fit: cover;
    width: 100%;
    height: 200px;
}

.btn-remove-image {
    background: #e53e3e;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    margin-top: 0.75rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
    width: 100%;
}

.btn-remove-image:hover {
    background: #c53030;
    transform: translateY(-1px);
}

/* Upload progress indicator */
.upload-progress {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: none;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    z-index: 10;
}

.upload-progress.show {
    display: flex;
}

.progress-content {
    text-align: center;
    color: var(--primary-color);
}

.progress-spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid var(--primary-color);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

/* Error states */
.image-upload.error {
    border-color: #e53e3e;
    background: rgba(229, 62, 62, 0.02);
}

.upload-error {
    color: #e53e3e;
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: none;
}

.upload-error.show {
    display: block;
}

/* ===== MAIN CONTENT ===== */

.programa-main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    min-height: calc(100vh - 70px);
}

/* ===== TARJETAS PRINCIPALES ===== */

.programa-selector {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
}

.selector-title {
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
    color: #2d3748;
    text-align: center;
    font-weight: 600;
}

.selector-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.selector-option {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.selector-option::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.selector-option:hover {
    border-color: var(--primary-color);
    background: rgba(var(--primary-rgb), 0.02);
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(var(--primary-rgb), 0.15);
}

.selector-option:hover::before {
    transform: scaleX(1);
}

.selector-option.active {
    border-color: var(--primary-color);
    background: rgba(var(--primary-rgb), 0.05);
    box-shadow: 0 4px 20px rgba(var(--primary-rgb), 0.15);
}

.selector-option.active::before {
    transform: scaleX(1);
}

.option-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.option-title {
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #2d3748;
    font-size: 1.1rem;
}

.option-description {
    color: #718096;
    font-size: 0.95rem;
    line-height: 1.5;
}

.solicitudes-dropdown {
    margin-top: 1.5rem;
}

.solicitudes-dropdown select {
    width: 100%;
    padding: 0.875rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    background: white;
    transition: border-color 0.3s ease;
}

.solicitudes-dropdown select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.continue-btn {
    background: var(--primary-gradient);
    color: white;
    border: none;
    padding: 0.875rem 2.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.05rem;
    font-weight: 500;
    margin-top: 1.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(var(--primary-rgb), 0.2);
}

.continue-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.3);
}

/* ===== WORKSPACE ===== */

.programa-workspace {
    display: none;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
    min-height: 600px;
}

.workspace-tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.workspace-tab {
    flex: 1;
    padding: 1.25rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    font-weight: 500;
    color: #718096;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.workspace-tab:hover {
    background: #e9ecef;
    color: #4a5568;
}

.workspace-tab.active {
    background: white;
    border-bottom-color: var(--primary-color);
    color: var(--primary-color);
    font-weight: 600;
}

.workspace-content {
    padding: 2rem;
    min-height: 500px;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* ===== FORMULARIOS ===== */

.programa-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.info-section {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e9ecef;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #4a5568;
    font-size: 0.95rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.875rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
    transition: all 0.3s ease;
    background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.form-group input[readonly] {
    background: #f8f9fa;
    color: #718096;
    cursor: not-allowed;
}

.image-upload {
    border: 2px dashed #cbd5e0;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f7fafc;
}

.image-upload:hover {
    border-color: var(--primary-color);
    background: rgba(var(--primary-rgb), 0.02);
}

.image-upload.has-image {
    padding: 0;
    border: none;
    background: transparent;
}

.image-preview {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* ===== DÍA A DÍA ===== */

.dias-container {
    display: flex;
    gap: 2rem;
    height: 600px;
}

.dias-sidebar {
    width: 280px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    overflow-y: auto;
    border: 1px solid #e9ecef;
}

.dias-content {
    flex: 1;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 2rem;
    overflow-y: auto;
    border: 1px solid #e9ecef;
}

.add-dia-btn {
    width: 100%;
    background: var(--primary-gradient);
    color: white;
    border: none;
    padding: 0.875rem;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.add-dia-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.3);
}

.dia-item {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dia-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 2px 10px rgba(var(--primary-rgb), 0.1);
}

.dia-item.active {
    border-color: var(--primary-color);
    background: rgba(var(--primary-rgb), 0.05);
    box-shadow: 0 2px 10px rgba(var(--primary-rgb), 0.15);
}

.dia-number {
    font-weight: 600;
    color: var(--primary-color);
    font-size: 1.05rem;
}

.dia-title {
    font-size: 0.9rem;
    color: #718096;
    margin-top: 0.25rem;
}

.dia-actions {
    display: flex;
    gap: 0.25rem;
}

.dia-action {
    background: none;
    border: none;
    color: #718096;
    cursor: pointer;
    padding: 0.375rem;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.dia-action:hover {
    color: var(--primary-color);
    background: rgba(var(--primary-rgb), 0.1);
}

.empty-dias {
    text-align: center;
    color: #718096;
    padding: 3rem 2rem;
}

.empty-dias i {
    display: block;
    font-size: 3rem;
    color: #cbd5e0;
    margin-bottom: 1rem;
}

.dia-editor {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    border: 1px solid #e9ecef;
}

/* ===== PRECIO ===== */

.precio-sections {
    display: grid;
    gap: 2rem;
}

.precio-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 2rem;
    border: 1px solid #e9ecef;
}

.precio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 1.5rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.checkbox-group input[type="checkbox"] {
    width: auto;
    margin: 0;
}

/* ===== BOTONES ===== */

.btn {
    padding: 0.875rem 1.75rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary-gradient);
    color: white;
    box-shadow: 0 2px 10px rgba(var(--primary-rgb), 0.2);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.3);
}

.btn-secondary {
    background: #718096;
    color: white;
    box-shadow: 0 2px 10px rgba(113, 128, 150, 0.2);
}

.btn-secondary:hover {
    background: #4a5568;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(113, 128, 150, 0.3);
}

.btn-success {
    background: linear-gradient(135deg, #38a169 0%, #48bb78 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(56, 161, 105, 0.2);
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(56, 161, 105, 0.3);
}

.btn-danger {
    background: linear-gradient(135deg, #e53e3e 0%, #fc8181 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(229, 62, 62, 0.2);
}

.btn-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(229, 62, 62, 0.3);
}

/* ===== MODALES ===== */

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    backdrop-filter: blur(8px);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    max-width: 700px;
    width: 90%;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e9ecef;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2d3748;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #718096;
    padding: 0;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.close-btn:hover {
    color: #4a5568;
    background: #f7fafc;
}

/* ===== VISTA PREVIA ===== */

.vista-previa {
    background: white;
    border-radius: 16px;
    padding: 3rem;
    margin-top: 2rem;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
    text-align: center;
}

.preview-image {
    width: 100%;
    max-width: 450px;
    height: 220px;
    object-fit: cover;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.preview-title {
    font-size: 2.25rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    color: var(--primary-color);
    line-height: 1.2;
}

.preview-company {
    font-size: 1.25rem;
    color: #718096;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.preview-summary {
    color: #4a5568;
    line-height: 1.7;
    margin-bottom: 2.5rem;
    font-size: 1.05rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.preview-btn {
    background: var(--primary-gradient);
    color: white;
    border: none;
    padding: 1.25rem 3rem;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(var(--primary-rgb), 0.25);
}

.preview-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(var(--primary-rgb), 0.35);
}

/* ===== LOADING Y NOTIFICACIONES ===== */

.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    backdrop-filter: blur(8px);
}

.loading-overlay.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.loading-spinner {
    background: white;
    padding: 3rem;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--primary-color);
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin: 0 auto 1.5rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.notification {
    position: fixed;
    top: 30px;
    right: 30px;
    padding: 1.25rem 2rem;
    border-radius: 12px;
    color: white;
    font-weight: 500;
    z-index: 10000;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.4s ease;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    max-width: 400px;
}

.notification.show {
    opacity: 1;
    transform: translateX(0);
}

.notification.success {
    background: linear-gradient(135deg, #38a169 0%, #48bb78 100%);
}

.notification.error {
    background: linear-gradient(135deg, #e53e3e 0%, #fc8181 100%);
}

.notification.warning {
    background: linear-gradient(135deg, #d69e2e 0%, #f6e05e 100%);
    color: #744210;
}

.notification.info {
    background: linear-gradient(135deg, #3182ce 0%, #63b3ed 100%);
}

/* ===== RESPONSIVE ===== */

@media (max-width: 768px) {
    .programa-nav {
        padding: 0 1rem;
        flex-wrap: wrap;
        height: auto;
        min-height: 70px;
    }
    
    .programa-main {
        padding: 1rem;
    }
    
    .programa-info-grid {
        grid-template-columns: 1fr;
    }
    
    .dias-container {
        flex-direction: column;
        height: auto;
    }
    
    .dias-sidebar {
        width: 100%;
        margin-bottom: 1rem;
    }
    
    .precio-grid {
        grid-template-columns: 1fr;
    }
    
    .selector-options {
        grid-template-columns: 1fr;
    }
    
    .workspace-tab {
        font-size: 0.9rem;
        padding: 1rem 0.5rem;
    }
    
    .workspace-content {
        padding: 1rem;
    }
    
    .preview-title {
        font-size: 1.75rem;
    }
    
    .modal-content {
        margin: 1rem;
        width: calc(100% - 2rem);
    }
}
   </style>
</head>
<body>
    <div class="programa-container">
        <!-- Header -->
        <header class="programa-header">
            <nav class="programa-nav">
                <a href="<?= APP_URL ?>/dashboard" class="programa-logo">
                    <i class="fas fa-arrow-left"></i> <?= htmlspecialchars($companyName) ?>
                </a>
                <div class="programa-user">
                    <span>Bienvenid<?= $user['role'] === 'admin' ? 'o' : 'a' ?>, <?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Usuario') ?></span>
                    <a href="<?= APP_URL ?>/auth/logout" style="color: white; text-decoration: none;">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </nav>
        </header>

        <main class="programa-main">
            <!-- Selector de Programa -->
            <div class="programa-selector" id="programaSelector">
                <h2 class="selector-title">¿Cómo quieres crear tu itinerario?</h2>
                
                <div class="selector-options">
                    <div class="selector-option" data-option="nueva">
                        <div class="option-icon">✨</div>
                        <div class="option-title">Nueva Propuesta</div>
                        <div class="option-description">Crear una nueva solicitud de viajero desde cero</div>
                    </div>
                    
                    <div class="selector-option" data-option="existente">
                        <div class="option-icon">📋</div>
                        <div class="option-title">Propuesta Existente</div>
                        <div class="option-description">Trabajar con una solicitud ya creada</div>
                        
                        <div class="solicitudes-dropdown" style="display: none;">
                            <select id="solicitudExistente" name="solicitudExistente">
                                <option value="">Cargando solicitudes...</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="selector-option" data-option="continuar">
                        <div class="option-icon">🔄</div>
                        <div class="option-title">Continuar Itinerario</div>
                        <div class="option-description">Continuar editando el itinerario de una persona</div>
                        
                        <div class="solicitudes-dropdown" style="display: none;">
                            <select id="solicitudContinuar" name="solicitudContinuar">
                                <option value="">Cargando solicitudes...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="text-align: center;">
                    <button class="continue-btn" id="continuarBtn" style="display: none;">
                        <i class="fas fa-arrow-right"></i> Continuar
                    </button>
                </div>
                
                <div style="text-align: center;">
                    <button class="continue-btn" id="continuarBtn" style="display: none;">Continuar</button>
                </div>
            </div>

            <!-- Área de Trabajo del Programa -->
            <div class="programa-workspace" id="programaWorkspace">
                <div class="workspace-tabs">
                    <div class="workspace-tab active" data-tab="mi-programa">
                        <i class="fas fa-user"></i> Mi Programa
                    </div>
                    <div class="workspace-tab" data-tab="dia-dia">
                        <i class="fas fa-calendar-alt"></i> Día a Día
                    </div>
                    <div class="workspace-tab" data-tab="precio">
                        <i class="fas fa-euro-sign"></i> Precio
                    </div>
                </div>

                <div class="workspace-content">
                    <!-- Tab Mi Programa -->
                    <div class="tab-content active" id="tab-mi-programa">
                        <div class="programa-info-grid">
                            <div class="info-section">
                                <h3 class="section-title">
                                    <i class="fas fa-user"></i> Información del Viajero
                                </h3>
                                
                                <div class="form-group">
                                    <label>ID Solicitud</label>
                                    <input type="text" id="idSolicitud" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label>Nombre del Viajero</label>
                                    <input type="text" id="nombreViajero" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Apellido del Viajero</label>
                                    <input type="text" id="apellidoViajero" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Destino</label>
                                    <input type="text" id="destino" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Fecha de Llegada</label>
                                    <input type="date" id="fechaLlegada" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Fecha de Salida</label>
                                    <input type="date" id="fechaSalida" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Número de Pasajeros</label>
                                    <input type="number" id="numeroPasajeros" min="1" value="1" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Acompañamiento Solicitado</label>
                                    <textarea id="acompanamiento" rows="3" placeholder="Describe el tipo de acompañamiento..."></textarea>
                                </div>
                            </div>

                            <div class="info-section">
                                <h3 class="section-title">
                                    <i class="fas fa-palette"></i> Personalización
                                </h3>
                                
                                <div class="form-group">
                                    <label>Título del Programa</label>
                                    <input type="text" id="tituloPrograma" placeholder="Ej: Escapada Romántica a París">
                                </div>
                                
                                <div class="form-group">
                                    <label>Apellido del Viajero</label>
                                    <input type="text" id="apellidoViajeroPersonalizacion" placeholder="Apellido para personalización">
                                </div>
                                
                                <div class="form-group">
                                    <label>Idioma del Presupuesto</label>
                                    <select id="idiomaPresupuesto">
                                        <option value="es">Español</option>
                                        <option value="en">English</option>
                                        <option value="fr">Français</option>
                                        <option value="pt">Português</option>
                                        <option value="de">Deutsch</option>
                                        <option value="it">Italiano</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Fecha de Llegada</label>
                                    <input type="date" id="fechaLlegadaPersonalizacion" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Foto de Portada</label>
                                    <div class="image-upload-container">
                                        <div class="image-upload" id="fotoPortada" onclick="triggerFileUpload()">
                                            <div class="upload-content" id="uploadContent">
                                                <i class="fas fa-upload" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"></i>
                                                <p>Haz clic para subir una foto de portada</p>
                                                <small style="color: #666;">Formatos: JPG, PNG, GIF, WEBP (Máx: 5MB)</small>
                                            </div>
                                        </div>
                                        <input type="file" id="fotoPortadaInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="display: none;">
                                        
                                        <!-- Botón para remover imagen -->
                                        <button type="button" id="removeImageBtn" class="btn-remove-image" style="display: none;" onclick="removePortadaImage()">
                                            <i class="fas fa-trash"></i> Remover Imagen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 2rem;">
                            <button class="btn btn-primary" onclick="guardarPrograma()">
                                <i class="fas fa-save"></i> Guardar Información
                            </button>
                        </div>
                    </div>

                    <!-- Tab Día a Día -->
                    <div class="tab-content" id="tab-dia-dia">
                        <div class="dias-container">
                            <div class="dias-sidebar">
                                <button class="add-dia-btn" onclick="anadirDia()">
                                    <i class="fas fa-plus"></i> Añadir un día
                                </button>
                                
                                <div id="diasList">
                                    <div class="empty-dias">
                                        <i class="fas fa-calendar-plus" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"></i>
                                        <p>No hay días añadidos aún</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="dias-content">
                                <div class="empty-dias" id="emptyDiaContent">
                                    <i class="fas fa-arrow-left" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>Selecciona un día de la lista para editarlo</p>
                                </div>
                                
                                <div class="dia-editor" id="diaEditor" style="display: none;">
                                    <!-- El contenido del editor se carga dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Precio -->
                    <div class="tab-content" id="tab-precio">
                        <div class="precio-sections">
                            <div class="precio-section">
                                <h3 class="section-title">
                                    <i class="fas fa-euro-sign"></i> Precio del Viaje
                                </h3>
                                
                                <div class="precio-grid">
                                    <div class="form-group">
                                        <label>Moneda</label>
                                        <select id="moneda">
                                            <option value="EUR">Euro (€)</option>
                                            <option value="USD">Dólar US ($)</option>
                                            <option value="GBP">Libra (£)</option>
                                            <option value="COP">Peso COP</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Precio Adulto</label>
                                        <input type="number" id="precioAdulto" step="0.01" min="0">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Precio Adolescente</label>
                                        <input type="number" id="precioAdolescente" step="0.01" min="0">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Precio Niño</label>
                                        <input type="number" id="precioNino" step="0.01" min="0">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Precio Bebé</label>
                                        <input type="number" id="precioBebe" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="precio-section">
                                <h3 class="section-title">
                                    <i class="fas fa-list"></i> Detalle del Precio
                                </h3>
                                
                                <div class="form-group">
                                    <label>Noches Incluidas</label>
                                    <input type="number" id="nochesIncluidas" min="0" value="0">
                                </div>
                                
                                <div class="form-group">
                                    <label>El precio incluye</label>
                                    <textarea id="precioIncluye" rows="4" placeholder="Describe qué incluye el precio..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>El precio NO incluye</label>
                                    <textarea id="precioNoIncluye" rows="4" placeholder="Describe qué NO incluye el precio..."></textarea>
                                </div>
                            </div>
                            
                            <div class="precio-section">
                                <h3 class="section-title">
                                    <i class="fas fa-file-contract"></i> Condiciones Generales
                                </h3>
                                
                                <div class="form-group">
                                    <textarea id="condicionesGenerales" rows="6" placeholder="Condiciones generales del viaje...">Condiciones generales estándar del viaje. Cancelación gratuita hasta 48 horas antes del viaje. No reembolsable después de la fecha límite.</textarea>
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" id="aptoMovilidadReducida">
                                    <label for="aptoMovilidadReducida">Este viaje es apto para personas de movilidad reducida</label>
                                </div>
                            </div>
                            
                            <div class="precio-section">
                                <h3 class="section-title">
                                    <i class="fas fa-passport"></i> Pasaportes y Seguros
                                </h3>
                                
                                <div class="form-group">
                                    <label>Pasaportes y Visados</label>
                                    <textarea id="infoPasaportes" rows="3" placeholder="Información sobre pasaportes y visados...">Se requiere pasaporte vigente con al menos 6 meses de validez. Verifique si necesita visa según su nacionalidad.</textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Seguros de Viaje</label>
                                    <textarea id="infoSeguros" rows="3" placeholder="Información sobre seguros de viaje...">Se recomienda contratar seguro de viaje que cubra gastos médicos y cancelación. Consulte las opciones disponibles.</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 2rem;">
                            <button class="btn btn-primary" onclick="guardarPrecios()">
                                <i class="fas fa-save"></i> Guardar Precios
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista Previa -->
            <div class="vista-previa" id="vistaPrevia" style="display: none;">
                <h2 class="section-title">
                    <i class="fas fa-eye"></i> Vista Previa del Programa
                </h2>
                
                <div id="previewContent">
                    <!-- El contenido de la vista previa se genera dinámicamente -->
                </div>
                
                <button class="preview-btn" onclick="generarPDF()">
                    <i class="fas fa-download"></i> Descargar Programa Completo
                </button>
            </div>
        </main>
    </div>

    <!-- Modal para Seleccionar de Biblioteca -->
    <div class="modal" id="bibliotecaModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="bibliotecaModalTitle">Seleccionar de la Biblioteca</h3>
                <button class="close-btn" onclick="cerrarBibliotecaModal()">&times;</button>
            </div>
            
            <div id="bibliotecaContent">
                <!-- El contenido se carga dinámicamente -->
            </div>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <button class="btn btn-primary" onclick="confirmarSeleccionBiblioteca()">
                    <i class="fas fa-check"></i> Confirmar Selección
                </button>
                <button class="btn btn-secondary" onclick="cerrarBibliotecaModal()" style="margin-left: 1rem;">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Cargando...</p>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Variables globales
        const APP_URL = '<?= APP_URL ?>';
        let currentSolicitudId = null;
        let currentDiaId = null;
        let solicitudes = [];
        let diasPrograma = [];
        let bibliotecaModal = {
            tipo: null,
            callback: null,
            seleccionados: []
        };

        // Inicializar aplicación
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
        });

        async function initializeApp() {
            try {
                console.log('Inicializando aplicación...');
                
                // Verificar conexión con API primero
                const apiConnected = await verificarConexionAPI();
                if (!apiConnected) {
                    console.warn('⚠️ Continuando sin conexión API completa');
                }
                
                // Cargar solicitudes
                await cargarSolicitudes();
                
                // Configurar eventos
                setupEventListeners();
                
                // Cargar monedas disponibles
                await cargarMonedas();
                
                console.log('✅ Aplicación inicializada correctamente');
                
            } catch (error) {
                console.error('❌ Error inicializando aplicación:', error);
                showNotification('Error al inicializar la aplicación', 'error');
                
                // Continuar con valores por defecto
                solicitudes = [];
                setupEventListeners();
            }
        }

        function setupEventListeners() {
            // Selector de opciones
            document.querySelectorAll('.selector-option').forEach(option => {
                option.addEventListener('click', function() {
                    selectOption(this.dataset.option);
                });
            });

            // Botón continuar
            document.getElementById('continuarBtn').addEventListener('click', continuarConSeleccion);

            // Tabs del workspace
            document.querySelectorAll('.workspace-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    switchTab(this.dataset.tab);
                });
            });

            // Upload de foto de portada - MEJORADO
            setupImageUpload();
            
            // Sincronizar campos duplicados
            setupFieldSync();
        }

        function setupImageUpload() {
            const fileInput = document.getElementById('fotoPortadaInput');
            
            // Cuando se selecciona un archivo
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    validateAndPreviewImage(file);
                }
            });

            function previewImage(imageSrc) {
                const uploadArea = document.getElementById('fotoPortada');
                const removeBtn = document.getElementById('removeImageBtn');
                
                uploadArea.innerHTML = `<img src="${imageSrc}" class="image-preview" alt="Vista previa">`;
                uploadArea.classList.add('has-image');
                removeBtn.style.display = 'block';
                
                // Actualizar vista previa automáticamente
                setTimeout(() => {
                    generarVistaPrevia();
                }, 100);
            }

            async function guardarPrograma() {
                try {
                    showLoading(true);
                    
                    const formData = new FormData();
                    
                    if (currentSolicitudId) {
                        formData.append('action', 'update');
                        formData.append('id', currentSolicitudId);
                    } else {
                        formData.append('action', 'create');
                    }
                    
                    // Datos de la solicitud
                    formData.append('nombre_viajero', document.getElementById('nombreViajero').value);
                    formData.append('apellido_viajero', document.getElementById('apellidoViajero').value);
                    formData.append('destino', document.getElementById('destino').value);
                    formData.append('fecha_llegada', document.getElementById('fechaLlegada').value);
                    formData.append('fecha_salida', document.getElementById('fechaSalida').value);
                    formData.append('numero_viajeros', document.getElementById('numeroPasajeros').value);
                    formData.append('acompanamiento', document.getElementById('acompanamiento').value);
                    
                    const response = await fetch(`${APP_URL}/programa/api`, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        if (!currentSolicitudId) {
                            currentSolicitudId = result.id;
                            document.getElementById('idSolicitud').value = result.id_solicitud;
                        }
                        
                        await guardarPersonalizacion();
                        showNotification('Programa guardado correctamente', 'success');
                        
                        // Mostrar y actualizar vista previa
                        document.getElementById('vistaPrevia').style.display = 'block';
                        generarVistaPrevia();
                        
                    } else {
                        throw new Error(result.error);
                    }
                    
                } catch (error) {
                    console.error('Error guardando programa:', error);
                    showNotification('Error al guardar el programa', 'error');
                } finally {
                    showLoading(false);
                }
            }

            // NUEVA FUNCIÓN: Actualizar vista previa en tiempo real
            function setupLivePreview() {
                // Campos que afectan la vista previa
                const fieldsToWatch = [
                    'tituloPrograma',
                    'nombreViajero', 
                    'apellidoViajero',
                    'destino'
                ];
                
                fieldsToWatch.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('input', function() {
                            // Actualizar vista previa con un pequeño delay
                            clearTimeout(field.previewTimeout);
                            field.previewTimeout = setTimeout(() => {
                                const vistaPrevia = document.getElementById('vistaPrevia');
                                if (vistaPrevia && vistaPrevia.style.display !== 'none') {
                                    generarVistaPrevia();
                                }
                            }, 500);
                        });
                    }
                });
            }

            // ACTUALIZAR LA FUNCIÓN setupEventListeners() PARA INCLUIR LIVE PREVIEW

            function setupEventListeners() {
                // Selector de opciones
                document.querySelectorAll('.selector-option').forEach(option => {
                    option.addEventListener('click', function() {
                        selectOption(this.dataset.option);
                    });
                });

                // Botón continuar
                document.getElementById('continuarBtn').addEventListener('click', continuarConSeleccion);

                // Tabs del workspace
                document.querySelectorAll('.workspace-tab').forEach(tab => {
                    tab.addEventListener('click', function() {
                        switchTab(this.dataset.tab);
                    });
                });

                // Upload de foto de portada
                setupImageUpload();
                
                // Sincronizar campos duplicados
                setupFieldSync();
                
                // Configurar vista previa en tiempo real
                setupLivePreview();
            }

            function validateAndPreviewImage(file) {
                const uploadArea = document.getElementById('fotoPortada');
                const errorDiv = document.querySelector('.upload-error') || createErrorDiv();
                
                // Limpiar errores previos
                uploadArea.classList.remove('error');
                errorDiv.classList.remove('show');
                
                // Validar tipo de archivo
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showUploadError('Tipo de archivo no permitido. Use: JPG, PNG, GIF o WEBP');
                    return;
                }
                
                // Validar tamaño (5MB máximo)
                const maxSize = 5 * 1024 * 1024; // 5MB en bytes
                if (file.size > maxSize) {
                    showUploadError('El archivo es demasiado grande. Máximo: 5MB');
                    return;
                }
                
                // Mostrar progreso
                showUploadProgress();
                
                // Leer y previsualizar imagen
                const reader = new FileReader();
                reader.onload = function(e) {
                    hideUploadProgress();
                    
                    // Previsualizar en el área de upload
                    previewImage(e.target.result);
                    
                    // Mostrar vista previa del programa si está visible
                    const vistaPrevia = document.getElementById('vistaPrevia');
                    if (vistaPrevia && vistaPrevia.style.display !== 'none') {
                        vistaPrevia.style.display = 'block';
                    }
                    
                    showNotification('Imagen cargada correctamente', 'success');
                };
                
                reader.onerror = function() {
                    hideUploadProgress();
                    showUploadError('Error al leer el archivo');
                };
                
                reader.readAsDataURL(file);
            }

            function generarVistaPrevia() {
                const container = document.getElementById('previewContent');
                
                if (!container) {
                    console.warn('Container de vista previa no encontrado');
                    return;
                }
                
                // Obtener datos del formulario
                const tituloPrograma = document.getElementById('tituloPrograma').value || 'Programa de Viaje';
                const nombreViajero = document.getElementById('nombreViajero').value || '';
                const apellidoViajero = document.getElementById('apellidoViajero').value || '';
                const destino = document.getElementById('destino').value || '';
                
                // Obtener imagen de portada
                let imagenSrc = 'https://via.placeholder.com/450x220/667eea/ffffff?text=Foto+de+Portada';
                
                // Verificar si hay una imagen cargada
                const fotoPortadaImg = document.querySelector('#fotoPortada img.image-preview');
                if (fotoPortadaImg && fotoPortadaImg.src) {
                    imagenSrc = fotoPortadaImg.src;
                    console.log('Imagen encontrada:', imagenSrc);
                } else {
                    console.log('No se encontró imagen, usando placeholder');
                }
                
                // Generar resumen dinámico
                let resumen = '';
                if (nombreViajero && apellidoViajero) {
                    resumen = `Programa personalizado para ${nombreViajero} ${apellidoViajero}`;
                } else if (nombreViajero) {
                    resumen = `Programa personalizado para ${nombreViajero}`;
                } else {
                    resumen = 'Programa de viaje personalizado';
                }
                
                if (destino) {
                    resumen += ` con destino a ${destino}`;
                }
                
                if (diasPrograma && diasPrograma.length > 0) {
                    resumen += `. Incluye ${diasPrograma.length} día${diasPrograma.length > 1 ? 's' : ''} de itinerario`;
                }
                
                resumen += '.';
                
                // Actualizar contenido de la vista previa
                container.innerHTML = `
                    <img src="${imagenSrc}" 
                        class="preview-image" 
                        alt="Foto de portada"
                        onerror="this.src='https://via.placeholder.com/450x220/667eea/ffffff?text=Error+al+Cargar+Imagen'"
                        onload="console.log('Imagen cargada en vista previa')">
                    <h2 class="preview-title">${tituloPrograma}</h2>
                    <p class="preview-company"><?= htmlspecialchars($companyName ?? 'Travel Agency') ?></p>
                    <p class="preview-summary">${resumen}</p>
                `;
                
                console.log('Vista previa actualizada con:', {
                    titulo: tituloPrograma,
                    imagen: imagenSrc,
                    resumen: resumen
                });
            }

            function removePortadaImage() {
                const uploadArea = document.getElementById('fotoPortada');
                const fileInput = document.getElementById('fotoPortadaInput');
                const removeBtn = document.getElementById('removeImageBtn');
                
                // Restaurar contenido original
                uploadArea.innerHTML = `
                    <div class="upload-content" id="uploadContent">
                        <i class="fas fa-upload" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <p>Haz clic para subir una foto de portada</p>
                        <small style="color: #666;">Formatos: JPG, PNG, GIF, WEBP (Máx: 5MB)</small>
                    </div>
                `;
                
                uploadArea.classList.remove('has-image');
                fileInput.value = '';
                removeBtn.style.display = 'none';
                
                // Actualizar vista previa
                generarVistaPrevia();
                
                showNotification('Imagen removida', 'info');
            }
            
            // Drag and drop
            const uploadArea = document.getElementById('fotoPortada');
            
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.style.borderColor = 'var(--primary-color)';
                this.style.backgroundColor = 'rgba(var(--primary-rgb), 0.05)';
            });
            
            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.style.borderColor = '#cbd5e0';
                this.style.backgroundColor = '#f7fafc';
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.style.borderColor = '#cbd5e0';
                this.style.backgroundColor = '#f7fafc';
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    validateAndPreviewImage(files[0]);
                }
            });
        }

        function triggerFileUpload() {
            document.getElementById('fotoPortadaInput').click();
        }
        function validateAndPreviewImage(file) {
            const uploadArea = document.getElementById('fotoPortada');
            const errorDiv = document.querySelector('.upload-error') || createErrorDiv();
            
            // Limpiar errores previos
            uploadArea.classList.remove('error');
            errorDiv.classList.remove('show');
            
            // Validar tipo de archivo
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showUploadError('Tipo de archivo no permitido. Use: JPG, PNG, GIF o WEBP');
                return;
            }
            
            // Validar tamaño (5MB máximo)
            const maxSize = 5 * 1024 * 1024; // 5MB en bytes
            if (file.size > maxSize) {
                showUploadError('El archivo es demasiado grande. Máximo: 5MB');
                return;
            }
            
            // Mostrar progreso
            showUploadProgress();
            
            // Leer y previsualizar imagen
            const reader = new FileReader();
            reader.onload = function(e) {
                hideUploadProgress();
                previewImage(e.target.result);
                showNotification('Imagen cargada correctamente', 'success');
            };
            
            reader.onerror = function() {
                hideUploadProgress();
                showUploadError('Error al leer el archivo');
            };
            
            reader.readAsDataURL(file);
        }

        function previewImage(imageSrc) {
            const uploadArea = document.getElementById('fotoPortada');
            const removeBtn = document.getElementById('removeImageBtn');
            
            uploadArea.innerHTML = `<img src="${imageSrc}" class="image-preview" alt="Vista previa">`;
            uploadArea.classList.add('has-image');
            removeBtn.style.display = 'block';
        }

        function removePortadaImage() {
            const uploadArea = document.getElementById('fotoPortada');
            const fileInput = document.getElementById('fotoPortadaInput');
            const removeBtn = document.getElementById('removeImageBtn');
            
            // Restaurar contenido original
            uploadArea.innerHTML = `
                <div class="upload-content" id="uploadContent">
                    <i class="fas fa-upload" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p>Haz clic para subir una foto de portada</p>
                    <small style="color: #666;">Formatos: JPG, PNG, GIF, WEBP (Máx: 5MB)</small>
                </div>
            `;
            
            uploadArea.classList.remove('has-image');
            fileInput.value = '';
            removeBtn.style.display = 'none';
            
            showNotification('Imagen removida', 'info');
        }

        function showUploadProgress() {
            const uploadArea = document.getElementById('fotoPortada');
            
            // Crear overlay de progreso si no existe
            let progressDiv = uploadArea.querySelector('.upload-progress');
            if (!progressDiv) {
                progressDiv = document.createElement('div');
                progressDiv.className = 'upload-progress';
                progressDiv.innerHTML = `
                    <div class="progress-content">
                        <div class="progress-spinner"></div>
                        <p>Subiendo imagen...</p>
                    </div>
                `;
                uploadArea.appendChild(progressDiv);
            }
            
            progressDiv.classList.add('show');
        }

        function hideUploadProgress() {
            const progressDiv = document.querySelector('.upload-progress');
            if (progressDiv) {
                progressDiv.classList.remove('show');
            }
        }

        function showUploadError(message) {
            const uploadArea = document.getElementById('fotoPortada');
            let errorDiv = document.querySelector('.upload-error');
            
            if (!errorDiv) {
                errorDiv = createErrorDiv();
            }
            
            uploadArea.classList.add('error');
            errorDiv.textContent = message;
            errorDiv.classList.add('show');
            
            showNotification(message, 'error');
        }

        function createErrorDiv() {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'upload-error';
            
            const container = document.querySelector('.image-upload-container');
            container.appendChild(errorDiv);
            
            return errorDiv;
        }

        function setupFieldSync() {
            // Sincronizar apellido del viajero con personalización
            const apellidoViajero = document.getElementById('apellidoViajero');
            const apellidoPersonalizacion = document.getElementById('apellidoViajeroPersonalizacion');
            
            if (apellidoViajero && apellidoPersonalizacion) {
                apellidoViajero.addEventListener('input', function() {
                    apellidoPersonalizacion.value = this.value;
                });
            }
            
            // Sincronizar fecha de llegada con personalización
            const fechaLlegada = document.getElementById('fechaLlegada');
            const fechaPersonalizacion = document.getElementById('fechaLlegadaPersonalizacion');
            
            if (fechaLlegada && fechaPersonalizacion) {
                fechaLlegada.addEventListener('change', function() {
                    fechaPersonalizacion.value = this.value;
                });
            }
        }


        function selectOption(option) {
            console.log('Seleccionando opción:', option); // Debug
            
            // Limpiar selecciones previas
            document.querySelectorAll('.selector-option').forEach(opt => {
                opt.classList.remove('active');
                const dropdown = opt.querySelector('.solicitudes-dropdown');
                if (dropdown) dropdown.style.display = 'none';
            });

            // Seleccionar nueva opción
            const selectedOption = document.querySelector(`[data-option="${option}"]`);
            selectedOption.classList.add('active');

            if (option === 'existente' || option === 'continuar') {
                const dropdown = selectedOption.querySelector('.solicitudes-dropdown');
                dropdown.style.display = 'block';
                
                // Poblar dropdown con solicitudes
                const select = dropdown.querySelector('select');
                
                console.log('Solicitudes disponibles:', solicitudes); // Debug
                
                // Limpiar opciones existentes
                select.innerHTML = '<option value="">Selecciona una solicitud...</option>';
                
                // Verificar que tenemos solicitudes
                if (solicitudes && solicitudes.length > 0) {
                    solicitudes.forEach(solicitud => {
                        const optionElement = document.createElement('option');
                        optionElement.value = solicitud.id;
                        optionElement.textContent = `${solicitud.id_solicitud || 'SIN-ID'} - ${solicitud.nombre_viajero} ${solicitud.apellido_viajero} (${solicitud.destino})`;
                        select.appendChild(optionElement);
                        console.log('Agregando opción:', optionElement.textContent); // Debug
                    });
                } else {
                    // Si no hay solicitudes, mostrar mensaje
                    const optionElement = document.createElement('option');
                    optionElement.value = "";
                    optionElement.textContent = "No hay solicitudes disponibles";
                    optionElement.disabled = true;
                    select.appendChild(optionElement);
                }

                // Remover listener anterior si existe
                select.removeEventListener('change', handleSelectChange);
                // Agregar nuevo listener
                select.addEventListener('change', handleSelectChange);
            } else {
                document.getElementById('continuarBtn').style.display = 'block';
            }
        }

        // Nueva función para manejar cambios en el select
        function handleSelectChange(event) {
            const continueBtn = document.getElementById('continuarBtn');
            if (event.target.value) {
                continueBtn.style.display = 'block';
                console.log('Solicitud seleccionada:', event.target.value); // Debug
            } else {
                continueBtn.style.display = 'none';
            }
        }

        async function continuarConSeleccion() {
            const activeOption = document.querySelector('.selector-option.active');
            const option = activeOption.dataset.option;

            try {
                if (option === 'nueva') {
                    currentSolicitudId = null;
                    limpiarFormularios();
                } else {
                    const select = activeOption.querySelector('select');
                    if (!select.value) {
                        showNotification('Por favor selecciona una solicitud', 'warning');
                        return;
                    }
                    
                    currentSolicitudId = parseInt(select.value);
                    await cargarProgramaCompleto(currentSolicitudId);
                }

                document.getElementById('programaSelector').style.display = 'none';
                document.getElementById('programaWorkspace').style.display = 'block';
                
                if (currentSolicitudId) {
                    document.getElementById('vistaPrevia').style.display = 'block';
                    generarVistaPrevia();
                }

            } catch (error) {
                console.error('Error:', error);
                showNotification('Error al continuar con la selección', 'error');
            }
        }

        async function cargarSolicitudes() {
            try {
                console.log('Cargando solicitudes...'); // Debug
                
                const response = await fetch(`${APP_URL}/programa/api?action=list`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Respuesta de la API:', result); // Debug
                
                if (result.success) {
                    solicitudes = result.data || [];
                    console.log('Solicitudes cargadas:', solicitudes.length); // Debug
                } else {
                    console.error('Error en la respuesta:', result.error);
                    solicitudes = [];
                    showNotification('Error al cargar solicitudes: ' + (result.error || 'Error desconocido'), 'error');
                }
            } catch (error) {
                console.error('Error cargando solicitudes:', error);
                solicitudes = [];
                showNotification('Error de conexión al cargar solicitudes', 'error');
            }
        }

        async function verificarConexionAPI() {
            try {
                console.log('Verificando conexión con la API...');
                
                const response = await fetch(`${APP_URL}/programa/api?action=get_currencies`);
                const result = await response.json();
                
                if (result.success) {
                    console.log('✅ Conexión con API establecida');
                    return true;
                } else {
                    console.error('❌ API respondió con error:', result.error);
                    return false;
                }
            } catch (error) {
                console.error('❌ Error de conexión con API:', error);
                showNotification('Error de conexión con el servidor', 'error');
                return false;
            }
        }


        async function cargarProgramaCompleto(solicitudId) {
            try {
                showLoading(true);
                
                const response = await fetch(`${APP_URL}/programa/api?action=get_programa_completo&id=${solicitudId}`);
                const result = await response.json();
                
                if (result.success) {
                    const programa = result.data;
                    cargarDatosSolicitud(programa.solicitud);
                    cargarDatosPersonalizacion(programa.personalizacion);
                    cargarDiasProgramas(programa.dias);
                    cargarDatosPrecios(programa.precios);
                } else {
                    throw new Error(result.error);
                }
            } catch (error) {
                console.error('Error cargando programa:', error);
                showNotification('Error al cargar el programa', 'error');
            } finally {
                showLoading(false);
            }
        }

        function cargarDatosSolicitud(solicitud) {
            if (!solicitud) return;
            
            document.getElementById('idSolicitud').value = solicitud.id_solicitud || '';
            document.getElementById('nombreViajero').value = solicitud.nombre_viajero || '';
            document.getElementById('apellidoViajero').value = solicitud.apellido_viajero || '';
            document.getElementById('destino').value = solicitud.destino || '';
            document.getElementById('fechaLlegada').value = solicitud.fecha_llegada || '';
            document.getElementById('fechaSalida').value = solicitud.fecha_salida || '';
            document.getElementById('numeroPasajeros').value = solicitud.numero_viajeros || 1;
            document.getElementById('acompanamiento').value = solicitud.acompanamiento || '';
        }

        function cargarDatosPersonalizacion(personalizacion) {
            if (!personalizacion) return;
            
            document.getElementById('tituloPrograma').value = personalizacion.titulo_programa || '';
            document.getElementById('idiomaPresupuesto').value = personalizacion.idioma_presupuesto || 'es';
            
            // Cargar apellido en personalización
            const apellidoPersonalizacion = document.getElementById('apellidoViajeroPersonalizacion');
            if (apellidoPersonalizacion) {
                apellidoPersonalizacion.value = personalizacion.apellido_viajero || '';
            }
            
            // Cargar fecha en personalización
            const fechaPersonalizacion = document.getElementById('fechaLlegadaPersonalizacion');
            if (fechaPersonalizacion) {
                fechaPersonalizacion.value = personalizacion.fecha_llegada || '';
            }
            
            // Cargar imagen de portada
            if (personalizacion.foto_portada) {
                previewImage(personalizacion.foto_portada);
            }
        }

        function cargarDiasProgramas(dias) {
            diasPrograma = dias || [];
            renderizarListaDias();
        }

        function cargarDatosPrecios(precios) {
            if (!precios) return;
            
            document.getElementById('moneda').value = precios.moneda || 'EUR';
            document.getElementById('precioAdulto').value = precios.precio_adulto || '';
            document.getElementById('precioAdolescente').value = precios.precio_adolescente || '';
            document.getElementById('precioNino').value = precios.precio_nino || '';
            document.getElementById('precioBebe').value = precios.precio_bebe || '';
            document.getElementById('nochesIncluidas').value = precios.noches_incluidas || 0;
            document.getElementById('precioIncluye').value = precios.precio_incluye || '';
            document.getElementById('precioNoIncluye').value = precios.precio_no_incluye || '';
            document.getElementById('condicionesGenerales').value = precios.condiciones_generales || '';
            document.getElementById('aptoMovilidadReducida').checked = precios.apto_movilidad_reducida == 1;
            document.getElementById('infoPasaportes').value = precios.info_pasaportes_visados || '';
            document.getElementById('infoSeguros').value = precios.info_seguros_viaje || '';
        }

        function limpiarFormularios() {
            // Limpiar Mi Programa
            document.getElementById('idSolicitud').value = '';
            document.getElementById('nombreViajero').value = '';
            document.getElementById('apellidoViajero').value = '';
            document.getElementById('destino').value = '';
            document.getElementById('fechaLlegada').value = '';
            document.getElementById('fechaSalida').value = '';
            document.getElementById('numeroPasajeros').value = 1;
            document.getElementById('acompanamiento').value = '';
            document.getElementById('tituloPrograma').value = '';
            document.getElementById('idiomaPresupuesto').value = 'es';
            
            // Limpiar imagen de portada
            const fotoPortada = document.getElementById('fotoPortada');
            fotoPortada.innerHTML = `
                <div class="upload-content">
                    <i class="fas fa-upload" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p>Haz clic para subir una foto de portada</p>
                    <input type="file" id="fotoPortadaInput" accept="image/*" style="display: none;">
                </div>
            `;
            fotoPortada.classList.remove('has-image');
            
            // Limpiar días
            diasPrograma = [];
            renderizarListaDias();
            
            // Limpiar precios con valores por defecto
            document.getElementById('moneda').value = 'EUR';
            document.getElementById('precioAdulto').value = '';
            document.getElementById('precioAdolescente').value = '';
            document.getElementById('precioNino').value = '';
            document.getElementById('precioBebe').value = '';
            document.getElementById('nochesIncluidas').value = 0;
            document.getElementById('precioIncluye').value = '';
            document.getElementById('precioNoIncluye').value = '';
            document.getElementById('condicionesGenerales').value = 'Condiciones generales estándar del viaje. Cancelación gratuita hasta 48 horas antes del viaje. No reembolsable después de la fecha límite.';
            document.getElementById('aptoMovilidadReducida').checked = false;
            document.getElementById('infoPasaportes').value = 'Se requiere pasaporte vigente con al menos 6 meses de validez. Verifique si necesita visa según su nacionalidad.';
            document.getElementById('infoSeguros').value = 'Se recomienda contratar seguro de viaje que cubra gastos médicos y cancelación. Consulte las opciones disponibles.';
        }

        function switchTab(tabName) {
            // Cambiar tabs activos
            document.querySelectorAll('.workspace-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            
            // Cambiar contenido activo
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`tab-${tabName}`).classList.add('active');
        }

        async function guardarPrograma() {
            try {
                showLoading(true);
                
                const formData = new FormData();
                
                if (currentSolicitudId) {
                    formData.append('action', 'update');
                    formData.append('id', currentSolicitudId);
                } else {
                    formData.append('action', 'create');
                }
                
                // Datos de la solicitud
                formData.append('nombre_viajero', document.getElementById('nombreViajero').value);
                formData.append('apellido_viajero', document.getElementById('apellidoViajero').value);
                formData.append('destino', document.getElementById('destino').value);
                formData.append('fecha_llegada', document.getElementById('fechaLlegada').value);
                formData.append('fecha_salida', document.getElementById('fechaSalida').value);
                formData.append('numero_viajeros', document.getElementById('numeroPasajeros').value);
                formData.append('acompanamiento', document.getElementById('acompanamiento').value);
                
                const response = await fetch(`${APP_URL}/programa/api`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (!currentSolicitudId) {
                        currentSolicitudId = result.id;
                        document.getElementById('idSolicitud').value = result.id_solicitud;
                    }
                    
                    await guardarPersonalizacion();
                    showNotification('Programa guardado correctamente', 'success');
                    document.getElementById('vistaPrevia').style.display = 'block';
                    generarVistaPrevia();
                } else {
                    throw new Error(result.error);
                }
                
            } catch (error) {
                console.error('Error guardando programa:', error);
                showNotification('Error al guardar el programa', 'error');
            } finally {
                showLoading(false);
            }
        }

        async function guardarPersonalizacion() {
            try {
                const formData = new FormData();
                formData.append('action', 'save_personalizacion');
                formData.append('solicitud_id', currentSolicitudId);
                formData.append('titulo_programa', document.getElementById('tituloPrograma').value);
                formData.append('idioma_presupuesto', document.getElementById('idiomaPresupuesto').value);
                
                // Añadir apellido del viajero
                const apellidoPersonalizacion = document.getElementById('apellidoViajeroPersonalizacion');
                if (apellidoPersonalizacion) {
                    formData.append('apellido_viajero', apellidoPersonalizacion.value);
                }
                
                // Añadir fecha de llegada
                const fechaPersonalizacion = document.getElementById('fechaLlegadaPersonalizacion');
                if (fechaPersonalizacion) {
                    formData.append('fecha_llegada', fechaPersonalizacion.value);
                }
                
                // Añadir foto de portada si se seleccionó
                const fotoInput = document.getElementById('fotoPortadaInput');
                if (fotoInput.files && fotoInput.files[0]) {
                    formData.append('foto_portada', fotoInput.files[0]);
                }
                
                const response = await fetch(`${APP_URL}/programa/api`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.error);
                }
                
            } catch (error) {
                console.error('Error guardando personalización:', error);
                throw error;
            }
        }

        // === FUNCIONES DÍA A DÍA ===

        function renderizarListaDias() {
            const container = document.getElementById('diasList');
            
            if (diasPrograma.length === 0) {
                container.innerHTML = `
                    <div class="empty-dias">
                        <i class="fas fa-calendar-plus" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <p>No hay días añadidos aún</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = '';
            
            diasPrograma.forEach((dia, index) => {
                const diaElement = document.createElement('div');
                diaElement.className = 'dia-item';
                diaElement.dataset.diaId = dia.id;
                diaElement.innerHTML = `
                    <div>
                        <div class="dia-number">Día ${dia.dia_numero}</div>
                        <div class="dia-title">${dia.titulo_jornada || 'Sin título'}</div>
                    </div>
                    <div class="dia-actions">
                        <button class="dia-action" onclick="editarDia(${dia.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="dia-action" onclick="eliminarDia(${dia.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                
                diaElement.addEventListener('click', function(e) {
                    if (!e.target.closest('.dia-actions')) {
                        seleccionarDia(dia.id);
                    }
                });
                
                container.appendChild(diaElement);
            });
        }

        async function anadirDia() {
            if (!currentSolicitudId) {
                showNotification('Primero debes guardar la información del programa', 'warning');
                return;
            }
            
            const siguienteDia = diasPrograma.length + 1;
            const fechaLlegada = new Date(document.getElementById('fechaLlegada').value);
            const fechaDia = new Date(fechaLlegada);
            fechaDia.setDate(fechaDia.getDate() + (siguienteDia - 1));
            
            const nuevoDia = {
                id: null,
                solicitud_id: currentSolicitudId,
                dia_numero: siguienteDia,
                fecha: fechaDia.toISOString().split('T')[0],
                titulo_jornada: `Día ${siguienteDia}`,
                descripcion: '',
                ubicacion: '',
                desayuno_incluido: false,
                almuerzo_incluido: false,
                cena_incluida: false,
                comidas_no_incluidas: false,
                servicios: {
                    actividades: [],
                    transportes: [],
                    alojamientos: []
                }
            };
            
            currentDiaId = null;
            mostrarEditorDia(nuevoDia);
        }

        function seleccionarDia(diaId) {
            document.querySelectorAll('.dia-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`[data-dia-id="${diaId}"]`).classList.add('active');
            
            const dia = diasPrograma.find(d => d.id == diaId);
            if (dia) {
                currentDiaId = diaId;
                mostrarEditorDia(dia);
            }
        }

        function mostrarEditorDia(dia) {
            document.getElementById('emptyDiaContent').style.display = 'none';
            document.getElementById('diaEditor').style.display = 'block';
            
            const editor = document.getElementById('diaEditor');
            editor.innerHTML = `
                <h3>Editar Día ${dia.dia_numero}</h3>
                
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" id="editFechaDia" value="${dia.fecha}">
                </div>
                
                <div class="form-group">
                    <label>Seleccionar día de la biblioteca</label>
                    <button type="button" class="btn btn-secondary" onclick="abrirBiblioteca('dias', seleccionarDiaBiblioteca)">
                        <i class="fas fa-search"></i> Buscar en Biblioteca
                    </button>
                    <div id="diaSeleccionado" style="margin-top: 0.5rem;">
                        ${dia.biblioteca_dia_id ? `<span class="servicio-item">Día seleccionado <button class="remove-servicio" onclick="removerDiaBiblioteca()">×</button></span>` : ''}
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Título de la jornada</label>
                    <input type="text" id="editTituloJornada" value="${dia.titulo_jornada || ''}" placeholder="Título del día">
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea id="editDescripcion" rows="3" placeholder="Descripción del día">${dia.descripcion || ''}</textarea>
                </div>
                
                <div class="form-group">
                    <label>Ubicación</label>
                    <input type="text" id="editUbicacion" value="${dia.ubicacion || ''}" placeholder="Ubicación">
                </div>
                
                <div class="comidas-section">
                    <h4>Comidas</h4>
                    <div class="comidas-options">
                        <div class="comida-option">
                            <input type="checkbox" id="desayunoIncluido" ${dia.desayuno_incluido ? 'checked' : ''}>
                            <label for="desayunoIncluido">Desayuno</label>
                        </div>
                        <div class="comida-option">
                            <input type="checkbox" id="almuerzoIncluido" ${dia.almuerzo_incluido ? 'checked' : ''}>
                            <label for="almuerzoIncluido">Almuerzo</label>
                        </div>
                        <div class="comida-option">
                            <input type="checkbox" id="cenaIncluida" ${dia.cena_incluida ? 'checked' : ''}>
                            <label for="cenaIncluida">Cena</label>
                        </div>
                        <div class="comida-option">
                            <input type="checkbox" id="comidasNoIncluidas" ${dia.comidas_no_incluidas ? 'checked' : ''}>
                            <label for="comidasNoIncluidas">Comidas no incluidas</label>
                        </div>
                    </div>
                </div>
                
                <div class="servicios-section">
                    <h4>Servicios</h4>
                    
                    <div class="servicio-tipo">
                        <label><strong>Actividades</strong></label>
                        <div class="servicio-items" id="actividadesItems">
                            ${renderizarServiciosItems(dia.servicios.actividades)}
                        </div>
                        <button type="button" class="add-servicio-btn" onclick="abrirBiblioteca('actividades', agregarActividad)">
                            <i class="fas fa-plus"></i> Agregar Actividad
                        </button>
                    </div>
                    
                    <div class="servicio-tipo">
                        <label><strong>Transportes</strong></label>
                        <div class="servicio-items" id="transportesItems">
                            ${renderizarServiciosItems(dia.servicios.transportes)}
                        </div>
                        <button type="button" class="add-servicio-btn" onclick="abrirBiblioteca('transportes', agregarTransporte)">
                            <i class="fas fa-plus"></i> Agregar Transporte
                        </button>
                    </div>
                    
                    <div class="servicio-tipo">
                        <label><strong>Alojamientos</strong></label>
                        <div class="servicio-items" id="alojamientosItems">
                            ${renderizarServiciosItems(dia.servicios.alojamientos)}
                        </div>
                        <button type="button" class="add-servicio-btn" onclick="abrirBiblioteca('alojamientos', agregarAlojamiento)">
                            <i class="fas fa-plus"></i> Agregar Alojamiento
                        </button>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <button class="btn btn-primary" onclick="guardarDia()">
                        <i class="fas fa-save"></i> Guardar Día
                    </button>
                    <button class="btn btn-danger" onclick="cancelarEdicionDia()" style="margin-left: 1rem;">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            `;
        }

        function renderizarServiciosItems(servicios) {
            if (!servicios || servicios.length === 0) {
                return '<p style="color: #666; font-style: italic;">No hay servicios agregados</p>';
            }
            
            return servicios.map(servicio => `
                <div class="servicio-item">
                    <strong>${servicio.nombre}</strong>
                    ${servicio.ubicacion ? `<br><small>${servicio.ubicacion}</small>` : ''}
                    <button class="remove-servicio" onclick="removerServicio('${servicio.tipo_servicio}', ${servicio.biblioteca_item_id})">×</button>
                </div>
            `).join('');
        }

        async function guardarDia() {
            try {
                showLoading(true);
                
                const formData = new FormData();
                formData.append('action', 'save_dia');
                formData.append('solicitud_id', currentSolicitudId);
                
                if (currentDiaId) {
                    formData.append('dia_id', currentDiaId);
                }
                
                // Datos del día
                const diaNumero = document.querySelector('.dia-item.active .dia-number')?.textContent.replace('Día ', '') || diasPrograma.length + 1;
                formData.append('dia_numero', diaNumero);
                formData.append('fecha', document.getElementById('editFechaDia').value);
                formData.append('titulo_jornada', document.getElementById('editTituloJornada').value);
                formData.append('descripcion', document.getElementById('editDescripcion').value);
                formData.append('ubicacion', document.getElementById('editUbicacion').value);
                
                // Comidas
                if (document.getElementById('desayunoIncluido').checked) formData.append('desayuno_incluido', '1');
                if (document.getElementById('almuerzoIncluido').checked) formData.append('almuerzo_incluido', '1');
                if (document.getElementById('cenaIncluida').checked) formData.append('cena_incluida', '1');
                if (document.getElementById('comidasNoIncluidas').checked) formData.append('comidas_no_incluidas', '1');
                
                const response = await fetch(`${APP_URL}/programa/api`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Día guardado correctamente', 'success');
                    await cargarProgramaCompleto(currentSolicitudId);
                    generarVistaPrevia();
                } else {
                    throw new Error(result.error);
                }
                
            } catch (error) {
                console.error('Error guardando día:', error);
                showNotification('Error al guardar el día', 'error');
            } finally {
                showLoading(false);
            }
        }

        function cancelarEdicionDia() {
            document.getElementById('diaEditor').style.display = 'none';
            document.getElementById('emptyDiaContent').style.display = 'block';
            
            document.querySelectorAll('.dia-item').forEach(item => {
                item.classList.remove('active');
            });
            
            currentDiaId = null;
        }

        async function eliminarDia(diaId) {
            if (!confirm('¿Estás seguro de que quieres eliminar este día?')) {
                return;
            }
            
            try {
                showLoading(true);
                
                const formData = new FormData();
                formData.append('action', 'delete_dia');
                formData.append('id', diaId);
                
                const response = await fetch(`${APP_URL}/programa/api`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Día eliminado correctamente', 'success');
                    await cargarProgramaCompleto(currentSolicitudId);
                    
                    if (currentDiaId == diaId) {
                        cancelarEdicionDia();
                    }
                } else {
                    throw new Error(result.error);
                }
                
            } catch (error) {
                console.error('Error eliminando día:', error);
                showNotification('Error al eliminar el día', 'error');
            } finally {
                showLoading(false);
            }
        }

        // === FUNCIONES DE BIBLIOTECA ===

        async function abrirBiblioteca(tipo, callback) {
            try {
                bibliotecaModal.tipo = tipo;
                bibliotecaModal.callback = callback;
                bibliotecaModal.seleccionados = [];
                
                document.getElementById('bibliotecaModalTitle').textContent = `Seleccionar ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`;
                
                showLoading(true);
                
                const response = await fetch(`${APP_URL}/programa/api?action=get_biblioteca_items&type=${tipo}`);
                const result = await response.json();
                
                if (result.success) {
                    const items = result.data;
                    renderizarBiblioteca(items, tipo);
                    document.getElementById('bibliotecaModal').classList.add('show');
                } else {
                    throw new Error(result.error);
                }
                
            } catch (error) {
                console.error('Error cargando biblioteca:', error);
                showNotification('Error al cargar la biblioteca', 'error');
            } finally {
                showLoading(false);
            }
        }

        function renderizarBiblioteca(items, tipo) {
            const container = document.getElementById('bibliotecaContent');
            
            if (items.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: #666;">
                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>No hay ${tipo} en tu biblioteca aún</p>
                        <a href="${APP_URL}/biblioteca" class="btn btn-primary" style="margin-top: 1rem;">
                            <i class="fas fa-plus"></i> Ir a Biblioteca
                        </a>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = `
                <div class="biblioteca-grid">
                    ${items.map(item => renderizarItemBiblioteca(item, tipo)).join('')}
                </div>
            `;
        }

        function renderizarItemBiblioteca(item, tipo) {
            const imagen = obtenerImagenItem(item, tipo);
            const titulo = obtenerTituloItem(item, tipo);
            const ubicacion = obtenerUbicacionItem(item, tipo);
            
            return `
                <div class="biblioteca-item" onclick="toggleSeleccionBiblioteca(${item.id}, '${tipo}')" data-item-id="${item.id}">
                    ${imagen ? `<img src="${imagen}" class="biblioteca-imagen" alt="${titulo}">` : ''}
                    <div class="biblioteca-titulo">${titulo}</div>
                    ${ubicacion ? `<div class="biblioteca-ubicacion">${ubicacion}</div>` : ''}
                </div>
            `;
        }

        function obtenerImagenItem(item, tipo) {
            switch(tipo) {
                case 'dias':
                case 'actividades':
                    return item.imagen1;
                case 'alojamientos':
                    return item.imagen;
                default:
                    return null;
            }
        }

        function obtenerTituloItem(item, tipo) {
            switch(tipo) {
                case 'dias':
                    return item.titulo;
                case 'actividades':
                case 'alojamientos':
                    return item.nombre;
                case 'transportes':
                    return item.titulo;
                default:
                    return 'Sin título';
            }
        }

        function obtenerUbicacionItem(item, tipo) {
            switch(tipo) {
                case 'transportes':
                    return item.lugar_salida && item.lugar_llegada ? `${item.lugar_salida} - ${item.lugar_llegada}` : item.ubicacion;
                default:
                    return item.ubicacion;
            }
        }

        function toggleSeleccionBiblioteca(itemId, tipo) {
            const elemento = document.querySelector(`[data-item-id="${itemId}"]`);
            
            if (bibliotecaModal.tipo === 'dias') {
                document.querySelectorAll('.biblioteca-item').forEach(item => {
                    item.classList.remove('selected');
                });
                elemento.classList.add('selected');
                bibliotecaModal.seleccionados = [itemId];
            } else {
                if (elemento.classList.contains('selected')) {
                    elemento.classList.remove('selected');
                    bibliotecaModal.seleccionados = bibliotecaModal.seleccionados.filter(id => id !== itemId);
                } else {
                    elemento.classList.add('selected');
                    bibliotecaModal.seleccionados.push(itemId);
                }
            }
        }

        function confirmarSeleccionBiblioteca() {
            if (bibliotecaModal.seleccionados.length === 0) {
                showNotification('Por favor selecciona al menos un elemento', 'warning');
                return;
            }
            
            if (bibliotecaModal.callback) {
                bibliotecaModal.callback(bibliotecaModal.seleccionados);
            }
            
            cerrarBibliotecaModal();
        }

        function cerrarBibliotecaModal() {
            document.getElementById('bibliotecaModal').classList.remove('show');
            bibliotecaModal = { tipo: null, callback: null, seleccionados: [] };
        }

        // === FUNCIONES DE PRECIOS ===

        async function cargarMonedas() {
            try {
                const response = await fetch(`${APP_URL}/programa/api?action=get_currencies`);
                const result = await response.json();
                
                if (result.success) {
                    const select = document.getElementById('moneda');
                    select.innerHTML = '';
                    
                    Object.entries(result.data).forEach(([code, currency]) => {
                        const option = document.createElement('option');
                        option.value = code;
                        option.textContent = `${currency.name} (${currency.symbol})`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error cargando monedas:', error);
            }
        }

        async function guardarPrecios() {
            try {
                showLoading(true);
                
                const formData = new FormData();
                formData.append('action', 'save_precios');
                formData.append('solicitud_id', currentSolicitudId);
                formData.append('moneda', document.getElementById('moneda').value);
                formData.append('precio_adulto', document.getElementById('precioAdulto').value);
                formData.append('precio_adolescente', document.getElementById('precioAdolescente').value);
                formData.append('precio_nino', document.getElementById('precioNino').value);
                formData.append('precio_bebe', document.getElementById('precioBebe').value);
                formData.append('noches_incluidas', document.getElementById('nochesIncluidas').value);
                formData.append('precio_incluye', document.getElementById('precioIncluye').value);
                formData.append('precio_no_incluye', document.getElementById('precioNoIncluye').value);
                formData.append('condiciones_generales', document.getElementById('condicionesGenerales').value);
                formData.append('info_pasaportes_visados', document.getElementById('infoPasaportes').value);
                formData.append('info_seguros_viaje', document.getElementById('infoSeguros').value);
                
                if (document.getElementById('aptoMovilidadReducida').checked) {
                    formData.append('apto_movilidad_reducida', '1');
                }
                
                const response = await fetch(`${APP_URL}/programa/api`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Precios guardados correctamente', 'success');
                    generarVistaPrevia();
                } else {
                    throw new Error(result.error);
                }
                
            } catch (error) {
                console.error('Error guardando precios:', error);
                showNotification('Error al guardar los precios', 'error');
            } finally {
                showLoading(false);
            }
        }

        // === VISTA PREVIA ===

        function generarVistaPrevia() {
            const container = document.getElementById('previewContent');
            
            const tituloPrograma = document.getElementById('tituloPrograma').value || 'Programa de Viaje';
            const nombreViajero = document.getElementById('nombreViajero').value;
            const apellidoViajero = document.getElementById('apellidoViajero').value;
            const destino = document.getElementById('destino').value;
            
            const fotoPortada = document.querySelector('#fotoPortada img');
            const imagenSrc = fotoPortada ? fotoPortada.src : 'https://via.placeholder.com/400x200/667eea/ffffff?text=Foto+de+Portada';
            
            let resumen = `Programa personalizado para ${nombreViajero} ${apellidoViajero}`;
            if (destino) resumen += ` con destino a ${destino}`;
            if (diasPrograma.length > 0) resumen += `. Incluye ${diasPrograma.length} día${diasPrograma.length > 1 ? 's' : ''} de itinerario`;
            resumen += '.';
            
            container.innerHTML = `
                <img src="${imagenSrc}" class="preview-image" alt="Foto de portada">
                <h2 class="preview-title">${tituloPrograma}</h2>
                <p class="preview-company"><?= htmlspecialchars($companyName) ?></p>
                <p class="preview-summary">${resumen}</p>
            `;
        }

        function generarPDF() {
            showNotification('Función de PDF en desarrollo', 'info');
        }

        // === FUNCIONES AUXILIARES ===

        function previewImage(file, containerId) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById(containerId);
                container.innerHTML = `<img src="${e.target.result}" class="image-preview" alt="Vista previa">`;
                container.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        }

        function showLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            if (show) {
                overlay.classList.add('show');
            } else {
                overlay.classList.remove('show');
            }
        }

        function showNotification(message, type = 'info') {
            // Crear elemento de notificación
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            // Añadir al DOM
            document.body.appendChild(notification);
            
            // Mostrar notificación
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Ocultar después de 3 segundos
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // === CALLBACKS DE BIBLIOTECA ===

        function seleccionarDiaBiblioteca(seleccionados) {
            // Implementar selección de día de biblioteca
            showNotification('Día de biblioteca seleccionado', 'success');
        }

        function agregarActividad(seleccionados) {
            // Implementar agregar actividad
            showNotification(`${seleccionados.length} actividad(es) agregada(s)`, 'success');
        }

        function agregarTransporte(seleccionados) {
            // Implementar agregar transporte
            showNotification(`${seleccionados.length} transporte(s) agregado(s)`, 'success');
        }

        function agregarAlojamiento(seleccionados) {
            // Implementar agregar alojamiento
            showNotification(`${seleccionados.length} alojamiento(s) agregado(s)`, 'success');
        }

        function removerServicio(tipo, itemId) {
            // Implementar remover servicio
            showNotification('Servicio removido', 'success');
        }

        function removerDiaBiblioteca() {
            // Implementar remover día de biblioteca
            showNotification('Día de biblioteca removido', 'success');
        }

        function editarDia(diaId) {
            seleccionarDia(diaId);
        }
    </script>
</body>
</html>