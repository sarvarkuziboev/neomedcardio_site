<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

session_bootstrap();
require_login();

$pdo = get_pdo();

[$sql, $params] = build_submissions_query($_GET);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

$statusLabels = [
    'yangi' => 'Yangi',
    'korib_chiqilgan' => "Ko'rib chiqilgan",
    'boglanilgan' => "Bog'lanilgan",
];

$filename = 'zayavkalar_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');

// Excel'da o'zbekcha/kirill harflar to'g'ri ko'rinishi uchun UTF-8 BOM.
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, ['ID', 'Ism', 'Telefon', "Yo'nalish", 'Izoh', 'Holat', 'IP manzil', 'Yaratilgan sana', 'Yangilangan sana'], ';');

foreach ($submissions as $row) {
    fputcsv($out, [
        $row['id'],
        $row['name'],
        $row['phone'],
        $row['service'],
        $row['message'] ?? '',
        $statusLabels[$row['status']] ?? $row['status'],
        $row['ip_address'] ?? '',
        $row['created_at'],
        $row['updated_at'],
    ], ';');
}

fclose($out);
exit;
