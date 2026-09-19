<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

session_bootstrap();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit("Noto'g'ri so'rov (CSRF).");
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if ($id === false || $id === null || $id <= 0) {
    header('Location: index.php');
    exit;
}

$pdo = get_pdo();

if ($action === 'delete') {
    $stmt = $pdo->prepare('DELETE FROM submissions WHERE id = :id');
    $stmt->execute(['id' => $id]);
} elseif ($action === 'set_status') {
    $newStatus = $_POST['new_status'] ?? '';
    if (in_array($newStatus, ALLOWED_STATUSES, true)) {
        $stmt = $pdo->prepare('UPDATE submissions SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $newStatus, 'id' => $id]);
    }
}

header('Location: index.php');
exit;
