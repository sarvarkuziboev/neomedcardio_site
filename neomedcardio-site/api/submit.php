<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'method_not_allowed'], 405);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    json_response(['ok' => false, 'error' => 'invalid_json'], 400);
}

// Honeypot — real users never fill this hidden field. Bots that do get a
// fake success response so they don't know they were filtered out.
if (!empty($input['website'])) {
    json_response(['ok' => true]);
}

$name = sanitize_name((string)($input['name'] ?? ''));
$phone = sanitize_phone((string)($input['phone'] ?? ''));
$service = sanitize_service((string)($input['service'] ?? ''));
$message = sanitize_message((string)($input['message'] ?? ''));

if ($name === '' || $phone === null) {
    json_response(['ok' => false, 'error' => 'validation_failed'], 400);
}

$pdo = get_pdo();
$ip = $_SERVER['REMOTE_ADDR'] ?? null;

// Coarse spam throttle: block a second submission from the same IP within 60s.
if ($ip !== null) {
    $stmt = $pdo->prepare(
        'SELECT id FROM submissions WHERE ip_address = :ip AND created_at > (NOW() - INTERVAL 60 SECOND) LIMIT 1'
    );
    $stmt->execute(['ip' => $ip]);
    if ($stmt->fetch()) {
        json_response(['ok' => false, 'error' => 'rate_limited'], 429);
    }
}

$stmt = $pdo->prepare(
    'INSERT INTO submissions (name, phone, service, message, ip_address) VALUES (:name, :phone, :service, :message, :ip)'
);
$stmt->execute([
    'name' => $name,
    'phone' => $phone,
    'service' => $service,
    'message' => $message !== '' ? $message : null,
    'ip' => $ip,
]);

json_response(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
