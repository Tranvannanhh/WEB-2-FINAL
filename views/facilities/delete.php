<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Facility.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $facilityModel = new Facility();
    $facilityModel->delete($id);
    flashMessage('success', 'Facility deleted.');
}
redirect(APP_URL . '/views/facilities/manage.php');
