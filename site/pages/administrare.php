<?php
/**
 * Controller principal pentru panoul de administrare.
 * Orchestrează verificarea permisiunilor, procesarea acțiunilor,
 * preluarea datelor și randarea view-ului.
 */

require_once __DIR__ . '/../components/config.php';
cere_roluri('administrator');
require_once __DIR__ . '/../components/database.php';
require_once __DIR__ . '/../components/product_images.php';

// 1. Utilitare specifice administrației (gestiune imagini)
require_once __DIR__ . '/admin/helpers.php';

// 2. Inițializare stare formular și mesaje
$root = '../';
$paginaActiva = '';
$titluPagina = 'Administrare';
$formType = '';
$formErrors = [];
$generalError = null;
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$categoryForm = ['id' => 0, 'name' => '', 'description' => ''];
$itemForm = ['id' => 0, 'category_id' => 0, 'name' => '', 'description' => '', 'price' => '', 'quantity_available' => '0', 'image_path' => null];

// 3. Procesare acțiuni POST (categorii, produse, solicitări)
require_once __DIR__ . '/admin/actions.php';

// 4. Preluare date (formulare editare GET + date catalog, comenzi, utilizatori)
require_once __DIR__ . '/admin/data.php';

// 5. Randare pagină (Header, View HTML, Footer)
require __DIR__ . '/../components/header.php';
require __DIR__ . '/admin/view.php';
require __DIR__ . '/../components/footer.php';
