<?php
/**
 * index.php — Front Controller / Router
 * SiMoU RSUD Kilisuci — Native PHP MVC Router
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/auth.php';

// Parse URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$baseUri    = rtrim($scriptName, '/');
$path       = '/' . ltrim(substr($requestUri, strlen($baseUri)), '/');
$path       = rtrim($path, '/') ?: '/';
$method     = $_SERVER['REQUEST_METHOD'];

// ─── Route Table ─────────────────────────────────────────────────────────
$routes = [
    // Root & Auth
    ['GET',  '/',                       'controllers/DashboardController.php',   'dashboard_index'],
    ['GET',  '/login',                  'controllers/AuthController.php',        'auth_login_form'],
    ['POST', '/login',                  'controllers/AuthController.php',        'auth_login_post'],
    ['GET',  '/auth/login',             'controllers/AuthController.php',        'auth_login_form'],
    ['POST', '/auth/login',             'controllers/AuthController.php',        'auth_login_post'],
    ['GET',  '/logout',                 'controllers/AuthController.php',        'auth_logout'],
    ['GET',  '/auth/logout',            'controllers/AuthController.php',        'auth_logout'],

    // Admin Dashboard
    ['GET',  '/admin',                  'controllers/DashboardController.php',   'dashboard_index'],

    // MoU / MoA CRUD & Protected Document Download
    ['GET',  '/admin/mou',              'controllers/MouController.php',         'mou_index'],
    ['GET',  '/admin/mou/create',       'controllers/MouController.php',         'mou_create_form'],
    ['POST', '/admin/mou/create',       'controllers/MouController.php',         'mou_create_post'],
    ['GET',  '/admin/mou/{id}/edit',    'controllers/MouController.php',         'mou_edit_form'],
    ['POST', '/admin/mou/{id}/edit',    'controllers/MouController.php',         'mou_edit_post'],
    ['POST', '/admin/mou/{id}/delete',  'controllers/MouController.php',         'mou_delete'],
    ['GET',  '/admin/mou/{id}/renew',   'controllers/MouController.php',         'mou_renew_form'],
    ['POST', '/admin/mou/{id}/renew',   'controllers/MouController.php',         'mou_renew_post'],
    ['GET',  '/admin/mou/{id}/renew/{renewal_id}/edit',   'controllers/MouController.php', 'mou_renew_edit_form'],
    ['POST', '/admin/mou/{id}/renew/{renewal_id}/edit',  'controllers/MouController.php', 'mou_renew_edit_post'],
    ['POST', '/admin/mou/{id}/renew/{renewal_id}/delete','controllers/MouController.php', 'mou_renew_delete'],
    ['GET',  '/admin/mou/{id}/document','controllers/MouController.php',         'mou_download_document'],
    ['GET',  '/admin/mou/{id}/addendum/{renewal_id}/document', 'controllers/MouController.php', 'mou_download_addendum'],

    // Kategori MoU
    ['GET',  '/admin/master/categories',                   'controllers/MasterController.php', 'master_categories_index'],
    ['POST', '/admin/master/categories/create',            'controllers/MasterController.php', 'master_categories_create'],
    ['POST', '/admin/master/categories/{id}/edit',         'controllers/MasterController.php', 'master_categories_edit'],
    ['POST', '/admin/master/categories/{id}/delete',       'controllers/MasterController.php', 'master_categories_delete'],

    // Institutions (moved under Master section in sidebar)
    ['GET',  '/admin/institutions',           'controllers/InstitutionController.php', 'inst_index'],
    ['POST', '/admin/institutions/create',    'controllers/InstitutionController.php', 'inst_create'],
    ['POST', '/admin/institutions/{id}/edit', 'controllers/InstitutionController.php', 'inst_edit'],
    ['POST', '/admin/institutions/{id}/delete','controllers/InstitutionController.php','inst_delete'],

    // Units (moved under Master section in sidebar)
    ['GET',  '/admin/units',                  'controllers/UnitController.php',        'unit_index'],
    ['POST', '/admin/units/create',           'controllers/UnitController.php',        'unit_create'],
    ['POST', '/admin/units/{id}/edit',        'controllers/UnitController.php',        'unit_edit'],
    ['POST', '/admin/units/{id}/delete',      'controllers/UnitController.php',        'unit_delete'],

    // Profile & Password Update (All Logged-in Users)
    ['POST', '/admin/profile/password',        'controllers/UserController.php',        'user_update_own_password'],

    // User Management (Superadmin)
    ['GET',  '/admin/users',                  'controllers/UserController.php',        'user_index'],
    ['POST', '/admin/users/create',           'controllers/UserController.php',        'user_create'],
    ['POST', '/admin/users/{id}/edit',        'controllers/UserController.php',        'user_edit'],
    ['POST', '/admin/users/{id}/toggle-status','controllers/UserController.php',       'user_toggle_status'],
    ['POST', '/admin/users/{id}/delete',      'controllers/UserController.php',        'user_delete'],

    // Export
    ['GET',  '/admin/export',           'controllers/ExportController.php',      'export_csv'],

    // Logs
    ['GET',  '/admin/logs',             'controllers/LogController.php',         'logs_index'],

    // Backup & Restore Database & Documents (Superadmin)
    ['GET',  '/admin/backup',                  'controllers/BackupController.php', 'backup_index'],
    ['POST', '/admin/backup/export',           'controllers/BackupController.php', 'backup_export'],
    ['POST', '/admin/backup/export-documents', 'controllers/BackupController.php', 'backup_export_documents'],
    ['POST', '/admin/backup/export-all',       'controllers/BackupController.php', 'backup_export_all'],
    ['POST', '/admin/backup/import',           'controllers/BackupController.php', 'backup_import'],
];

// ─── Route Matching ───────────────────────────────────────────────────────
$params  = [];
$matched = false;

foreach ($routes as [$routeMethod, $routePattern, $controllerFile, $controllerFn]) {
    $regex = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $routePattern);
    $regex = '#^' . $regex . '$#';

    if (preg_match($regex, $path, $matches) && $method === $routeMethod) {
        foreach ($matches as $k => $v) {
            if (is_string($k)) {
                $params[$k] = $v;
            }
        }
        $matched = true;
        require_once __DIR__ . '/' . $controllerFile;
        $controllerFn($params);
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    require __DIR__ . '/views/errors/404.php';
}
