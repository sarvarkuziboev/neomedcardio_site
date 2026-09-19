<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/charts.php';

session_bootstrap();
require_login();

$pdo = get_pdo();

$statusLabels = [
    'yangi' => 'Yangi',
    'korib_chiqilgan' => "Ko'rib chiqilgan",
    'boglanilgan' => "Bog'lanilgan",
];
$statusColors = [
    'yangi' => 'var(--chart-1)',
    'korib_chiqilgan' => 'var(--chart-2)',
    'boglanilgan' => 'var(--chart-5)',
];
$serviceColors = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)'];

// --- Oxirgi 14 kun (bo'sh kunlar 0 bilan to'ldiriladi) ---
$dailyRows = $pdo->query(
    "SELECT DATE(created_at) AS d, COUNT(*) AS c FROM submissions
     WHERE created_at >= (CURDATE() - INTERVAL 13 DAY)
     GROUP BY DATE(created_at)"
)->fetchAll();
$dailyMap = [];
for ($i = 13; $i >= 0; $i--) {
    $dailyMap[date('Y-m-d', strtotime("-{$i} day"))] = 0;
}
foreach ($dailyRows as $row) {
    if (isset($dailyMap[$row['d']])) {
        $dailyMap[$row['d']] = (int)$row['c'];
    }
}
$dailyPoints = [];
foreach ($dailyMap as $date => $count) {
    $dailyPoints[] = [
        'label' => date('d.m', strtotime($date)),
        'short' => date('d', strtotime($date)),
        'value' => $count,
    ];
}

// --- Davrlarni taqqoslash (oxirgi 7 kun vs undan oldingi 7 kun) ---
$last7 = (int)$pdo->query(
    'SELECT COUNT(*) FROM submissions WHERE created_at >= (CURDATE() - INTERVAL 6 DAY)'
)->fetchColumn();
$prev7 = (int)$pdo->query(
    'SELECT COUNT(*) FROM submissions WHERE created_at >= (CURDATE() - INTERVAL 13 DAY) AND created_at < (CURDATE() - INTERVAL 6 DAY)'
)->fetchColumn();
$trendPct = $prev7 > 0 ? round((($last7 - $prev7) / $prev7) * 100) : ($last7 > 0 ? 100 : 0);

$totalCount = (int)$pdo->query('SELECT COUNT(*) FROM submissions')->fetchColumn();
$todayCount = (int)$pdo->query('SELECT COUNT(*) FROM submissions WHERE DATE(created_at) = CURDATE()')->fetchColumn();
$avgPerDay = count($dailyPoints) > 0 ? round(array_sum(array_column($dailyPoints, 'value')) / count($dailyPoints), 1) : 0;

// --- Holat bo'yicha ---
$statusCounts = ['yangi' => 0, 'korib_chiqilgan' => 0, 'boglanilgan' => 0];
foreach ($pdo->query('SELECT status, COUNT(*) AS c FROM submissions GROUP BY status') as $row) {
    $statusCounts[$row['status']] = (int)$row['c'];
}
$statusSlices = [];
foreach ($statusCounts as $key => $count) {
    $statusSlices[] = [
        'label' => $statusLabels[$key],
        'value' => $count,
        'color' => $statusColors[$key],
    ];
}
$handled = $statusCounts['boglanilgan'];
$conversion = $totalCount > 0 ? round(($handled / $totalCount) * 100) : 0;

// --- Yo'nalish bo'yicha ---
$serviceSlices = [];
$i = 0;
foreach ($pdo->query('SELECT service, COUNT(*) AS c FROM submissions GROUP BY service ORDER BY c DESC') as $row) {
    $serviceSlices[] = [
        'label' => $row['service'],
        'value' => (int)$row['c'],
        'color' => $serviceColors[$i % count($serviceColors)],
    ];
    $i++;
}

// --- Hafta kunlari bo'yicha (1=Yakshanba ... 7=Shanba) ---
$weekdayNames = [2 => 'Dush', 3 => 'Sesh', 4 => 'Chor', 5 => 'Pay', 6 => 'Jum', 7 => 'Shan', 1 => 'Yak'];
$weekdayCounts = array_fill_keys(array_keys($weekdayNames), 0);
foreach ($pdo->query('SELECT DAYOFWEEK(created_at) AS wd, COUNT(*) AS c FROM submissions GROUP BY DAYOFWEEK(created_at)') as $row) {
    $weekdayCounts[(int)$row['wd']] = (int)$row['c'];
}
$weekdayPoints = [];
foreach ($weekdayNames as $num => $name) {
    $weekdayPoints[] = ['label' => $name, 'value' => $weekdayCounts[$num]];
}

$backBtn = '<a href="index.php" class="adm-btn adm-btn--ghost">' . admin_icon('back') . 'Zayavkalarga</a>';

admin_head('Statistika', "Arizalar dinamikasi va taqsimoti bo'yicha umumiy ko'rinish", 'statistika', $backBtn);
?>

<div class="kpi-row">
  <div class="kpi">
    <span class="kpi__label">Jami arizalar</span>
    <strong class="kpi__num"><?= $totalCount ?></strong>
    <span class="kpi__foot">boshidan buyon</span>
  </div>
  <div class="kpi">
    <span class="kpi__label">Oxirgi 7 kun</span>
    <strong class="kpi__num"><?= $last7 ?></strong>
    <span class="kpi__foot">
      <span class="trend <?= $trendPct >= 0 ? 'trend--up' : 'trend--down' ?>">
        <?= $trendPct >= 0 ? '▲' : '▼' ?> <?= abs($trendPct) ?>%
      </span>
      oldingi haftaga nisbatan
    </span>
  </div>
  <div class="kpi">
    <span class="kpi__label">Kunlik o'rtacha</span>
    <strong class="kpi__num"><?= $avgPerDay ?></strong>
    <span class="kpi__foot">oxirgi 14 kun</span>
  </div>
  <div class="kpi">
    <span class="kpi__label">Bugun</span>
    <strong class="kpi__num"><?= $todayCount ?></strong>
    <span class="kpi__foot">yangi murojaat</span>
  </div>
  <div class="kpi">
    <span class="kpi__label">Bog'lanildi</span>
    <strong class="kpi__num"><?= $conversion ?>%</strong>
    <span class="kpi__foot"><?= $handled ?> ta ariza yopildi</span>
  </div>
</div>

<section class="card card--chart">
  <div class="card__head">
    <div>
      <h2>Arizalar dinamikasi</h2>
      <p>Oxirgi 14 kun kesimida kunlik murojaatlar soni</p>
    </div>
  </div>
  <?= chart_bars($dailyPoints) ?>
</section>

<div class="grid-2">
  <section class="card">
    <div class="card__head">
      <div>
        <h2>Holat bo'yicha</h2>
        <p>Arizalarning ishlanish holati</p>
      </div>
    </div>
    <?= chart_donut($statusSlices, 'ariza') ?>
  </section>

  <section class="card">
    <div class="card__head">
      <div>
        <h2>Yo'nalish bo'yicha</h2>
        <p>Qaysi xizmatlar ko'proq so'ralmoqda</p>
      </div>
    </div>
    <?= chart_donut($serviceSlices, 'ariza') ?>
  </section>
</div>

<div class="grid-2">
  <section class="card">
    <div class="card__head">
      <div>
        <h2>Xizmatlar reytingi</h2>
        <p>Eng ko'p murojaat qilingan yo'nalishlar</p>
      </div>
    </div>
    <?= chart_rank($serviceSlices) ?>
  </section>

  <section class="card card--chart">
    <div class="card__head">
      <div>
        <h2>Hafta kunlari</h2>
        <p>Qaysi kunlarda murojaat ko'proq bo'ladi</p>
      </div>
    </div>
    <?= chart_bars($weekdayPoints, 'var(--chart-3)') ?>
  </section>
</div>

<?php admin_foot(); ?>
