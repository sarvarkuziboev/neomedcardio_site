<?php

function h(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function sanitize_name(string $value): string {
    $value = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? '');
    return mb_substr($value, 0, 120);
}

function sanitize_phone(string $value): ?string {
    $value = trim($value);
    if (!preg_match('/^[0-9+\-\s()]{6,32}$/u', $value)) {
        return null;
    }
    return $value;
}

function sanitize_message(string $value): string {
    $value = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '');
    return mb_substr($value, 0, 2000);
}

const ALLOWED_SERVICES = [
    'Kardiologiya',
    'Pulmonologiya',
    'Diagnostika (MRT/MSKT)',
    'Statsionar',
    'Boshqa',
];

function sanitize_service(string $value): string {
    return in_array($value, ALLOWED_SERVICES, true) ? $value : 'Boshqa';
}

function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

const ALLOWED_STATUSES = ['yangi', 'korib_chiqilgan', 'boglanilgan'];
const ALLOWED_SORT_COLUMNS = ['name', 'phone', 'service', 'status', 'created_at'];

/**
 * Admin panel uchun umumiy filtr/qidiruv/saralash so'rov quruvchisi.
 * admin/index.php (ekran) va admin/export.php (CSV) bir xil natija
 * ko'rsatishi uchun ikkalasi ham shu funksiyadan foydalanadi.
 *
 * @return array{0:string,1:array<string,string>,2:array<string,string>} [$sql, $params, $normalized]
 */
function build_submissions_query(array $get): array {
    $status = (string)($get['status'] ?? '');
    $service = (string)($get['service'] ?? '');
    $q = mb_substr(trim((string)($get['q'] ?? '')), 0, 120);
    $sort = (string)($get['sort'] ?? 'created_at');
    $dir = strtolower((string)($get['dir'] ?? 'desc'));

    if (!in_array($status, ALLOWED_STATUSES, true)) {
        $status = '';
    }
    if (!in_array($service, ALLOWED_SERVICES, true)) {
        $service = '';
    }
    if (!in_array($sort, ALLOWED_SORT_COLUMNS, true)) {
        $sort = 'created_at';
    }
    if (!in_array($dir, ['asc', 'desc'], true)) {
        $dir = 'desc';
    }

    $sql = 'SELECT * FROM submissions WHERE 1=1';
    $params = [];

    if ($status !== '') {
        $sql .= ' AND status = :status';
        $params['status'] = $status;
    }
    if ($service !== '') {
        $sql .= ' AND service = :service';
        $params['service'] = $service;
    }
    if ($q !== '') {
        $sql .= ' AND (name LIKE :q1 OR phone LIKE :q2)';
        $like = '%' . addcslashes($q, '%_') . '%';
        $params['q1'] = $like;
        $params['q2'] = $like;
    }

    // $sort/$dir kelib chiqishi faqat yuqoridagi ro'yxatdan tekshirilgan
    // qiymatlar bo'lgani uchun to'g'ridan-to'g'ri qo'shish xavfsiz.
    $sql .= " ORDER BY {$sort} {$dir}";

    $normalized = [
        'status' => $status,
        'service' => $service,
        'q' => $q,
        'sort' => $sort,
        'dir' => $dir,
    ];

    return [$sql, $params, $normalized];
}
