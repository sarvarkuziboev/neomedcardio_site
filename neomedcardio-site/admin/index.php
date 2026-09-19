<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/_layout.php';

session_bootstrap();
require_login();

$pdo = get_pdo();

[$sql, $params, $f] = build_submissions_query($_GET);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

$totalCount = (int)$pdo->query('SELECT COUNT(*) FROM submissions')->fetchColumn();
$newCount = (int)$pdo->query("SELECT COUNT(*) FROM submissions WHERE status = 'yangi'")->fetchColumn();
$todayCount = (int)$pdo->query('SELECT COUNT(*) FROM submissions WHERE DATE(created_at) = CURDATE()')->fetchColumn();
$monthCount = (int)$pdo->query('SELECT COUNT(*) FROM submissions WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())')->fetchColumn();

$statusLabels = [
    'yangi' => 'Yangi',
    'korib_chiqilgan' => "Ko'rib chiqilgan",
    'boglanilgan' => "Bog'lanilgan",
];

$token = csrf_token();
$hasFilters = $f['q'] !== '' || $f['status'] !== '' || $f['service'] !== '';

function sort_link(string $column, string $label, array $f): string {
    $nextDir = ($f['sort'] === $column && $f['dir'] === 'asc') ? 'desc' : 'asc';
    $qs = http_build_query([
        'status' => $f['status'],
        'service' => $f['service'],
        'q' => $f['q'],
        'sort' => $column,
        'dir' => $nextDir,
    ]);
    $isActive = $f['sort'] === $column;
    $arrow = $isActive ? ($f['dir'] === 'asc' ? '↑' : '↓') : '↕';
    return '<a href="?' . h($qs) . '" class="th-sort' . ($isActive ? ' is-active' : '') . '">'
        . h($label) . '<span class="th-sort__arrow">' . $arrow . '</span></a>';
}

function initials(string $name): string {
    $parts = preg_split('/\s+/u', trim($name));
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $second = isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '';
    return mb_strtoupper($first . $second);
}

function human_date(string $ts): array {
    $time = strtotime($ts);
    $today = strtotime('today');
    if ($time >= $today) {
        return ['Bugun', date('H:i', $time)];
    }
    if ($time >= strtotime('-1 day', $today)) {
        return ['Kecha', date('H:i', $time)];
    }
    return [date('d.m.Y', $time), date('H:i', $time)];
}

$exportQs = http_build_query([
    'status' => $f['status'],
    'service' => $f['service'],
    'q' => $f['q'],
    'sort' => $f['sort'],
    'dir' => $f['dir'],
]);

$actions = '<a href="export.php?' . h($exportQs) . '" class="adm-btn adm-btn--ghost">' . admin_icon('download') . 'Excelga eksport</a>'
    . '<a href="stats.php" class="adm-btn adm-btn--primary">' . admin_icon('chart') . 'Statistika</a>';

admin_head('Zayavkalar', 'Saytdan kelgan barcha murojaatlar shu yerda to\'planadi', 'zayavkalar', $actions);
?>

<div class="kpi-row">
  <div class="kpi">
    <span class="kpi__label">Jami</span>
    <strong class="kpi__num"><?= $totalCount ?></strong>
    <span class="kpi__foot">barcha arizalar</span>
  </div>
  <div class="kpi kpi--accent-red">
    <span class="kpi__label">Yangi</span>
    <strong class="kpi__num"><?= $newCount ?></strong>
    <span class="kpi__foot">javob kutilmoqda</span>
  </div>
  <div class="kpi kpi--accent-blue">
    <span class="kpi__label">Bugun</span>
    <strong class="kpi__num"><?= $todayCount ?></strong>
    <span class="kpi__foot">bugungi murojaatlar</span>
  </div>
  <div class="kpi">
    <span class="kpi__label">Bu oy</span>
    <strong class="kpi__num"><?= $monthCount ?></strong>
    <span class="kpi__foot"><?= h(date('F Y')) ?></span>
  </div>
</div>

<section class="card card--flush">
  <form method="get" class="toolbar">
    <label class="toolbar__search">
      <?= admin_icon('search') ?>
      <input type="search" name="q" placeholder="Ism yoki telefon bo'yicha qidirish" value="<?= h($f['q']) ?>">
    </label>
    <select name="status" class="toolbar__select" onchange="this.form.submit()">
      <option value="">Barcha holatlar</option>
      <?php foreach ($statusLabels as $value => $label): ?>
        <option value="<?= h($value) ?>" <?= $f['status'] === $value ? 'selected' : '' ?>><?= h($label) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="service" class="toolbar__select" onchange="this.form.submit()">
      <option value="">Barcha yo'nalishlar</option>
      <?php foreach (ALLOWED_SERVICES as $service): ?>
        <option value="<?= h($service) ?>" <?= $f['service'] === $service ? 'selected' : '' ?>><?= h($service) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="adm-btn adm-btn--primary">Qidirish</button>
    <?php if ($hasFilters): ?>
      <a href="index.php" class="toolbar__reset">Tozalash</a>
    <?php endif; ?>
  </form>

  <div class="table-meta">
    <span><strong><?= count($submissions) ?></strong> ta ariza ko'rsatilmoqda</span>
  </div>

  <?php if (empty($submissions)): ?>
    <div class="empty">
      <div class="empty__icon"><?= admin_icon('inbox') ?></div>
      <h3><?= $hasFilters ? 'Hech narsa topilmadi' : 'Hozircha zayavkalar yo\'q' ?></h3>
      <p><?= $hasFilters ? 'Qidiruv shartlarini o\'zgartirib ko\'ring.' : 'Saytdagi forma to\'ldirilganda arizalar shu yerda paydo bo\'ladi.' ?></p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th><?= sort_link('name', 'Bemor', $f) ?></th>
            <th><?= sort_link('service', "Yo'nalish", $f) ?></th>
            <th>Izoh</th>
            <th><?= sort_link('status', 'Holat', $f) ?></th>
            <th><?= sort_link('created_at', 'Sana', $f) ?></th>
            <th class="table__actions-head">Amallar</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($submissions as $row): ?>
            <?php [$dayLabel, $timeLabel] = human_date($row['created_at']); ?>
            <tr>
              <td data-label="Bemor">
                <div class="person">
                  <span class="avatar avatar--<?= h($row['status']) ?>"><?= h(initials($row['name'])) ?></span>
                  <span class="person__text">
                    <strong><?= h($row['name']) ?></strong>
                    <a href="tel:<?= h($row['phone']) ?>" class="person__phone"><?= h($row['phone']) ?></a>
                  </span>
                </div>
              </td>
              <td data-label="Yo'nalish"><span class="tag"><?= h($row['service']) ?></span></td>
              <td data-label="Izoh" class="cell-note">
                <?= $row['message'] !== null && $row['message'] !== '' ? h($row['message']) : '<span class="muted">—</span>' ?>
              </td>
              <td data-label="Holat">
                <span class="pill pill--<?= h($row['status']) ?>"><?= h($statusLabels[$row['status']] ?? $row['status']) ?></span>
              </td>
              <td data-label="Sana">
                <span class="date"><strong><?= h($dayLabel) ?></strong><small><?= h($timeLabel) ?></small></span>
              </td>
              <td data-label="Amallar" class="cell-actions-cell">
                <div class="cell-actions">
                <form method="post" action="actions.php" class="cell-actions__status">
                  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <input type="hidden" name="action" value="set_status">
                  <select name="new_status" onchange="this.form.submit()" aria-label="Holatni o'zgartirish">
                    <?php foreach (ALLOWED_STATUSES as $s): ?>
                      <option value="<?= h($s) ?>" <?= $s === $row['status'] ? 'selected' : '' ?>><?= h($statusLabels[$s]) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
                <form method="post" action="actions.php" onsubmit="return confirm('Bu arizani o\'chirasizmi? Bu amalni qaytarib bo\'lmaydi.');">
                  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button type="submit" class="icon-btn icon-btn--danger" title="O'chirish" aria-label="O'chirish">✕</button>
                </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php admin_foot(); ?>
