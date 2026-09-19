<?php

/**
 * Admin panelning umumiy qobig'i (sidebar + sarlavha).
 * Har bir admin sahifasi admin_head(...) bilan boshlanib, admin_foot() bilan tugaydi.
 */

function admin_icon(string $name): string {
    $icons = [
        'inbox' => '<path d="M3 12h4l2 3h6l2-3h4"/><path d="M5 5h14l2 7v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5z"/>',
        'chart' => '<path d="M3 3v18h18"/><rect x="7" y="11" width="3" height="6" rx="1"/><rect x="12" y="7" width="3" height="10" rx="1"/><rect x="17" y="13" width="3" height="4" rx="1"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
        'download' => '<path d="M12 3v12"/><path d="M7 11l5 5 5-5"/><path d="M5 21h14"/>',
        'back' => '<path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/>',
    ];
    $path = $icons[$name] ?? '';
    return '<svg class="adm-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

function admin_head(string $title, string $subtitle, string $active, string $actionsHtml = ''): void {
    $nav = [
        'zayavkalar' => ['index.php', 'Zayavkalar', 'inbox'],
        'statistika' => ['stats.php', 'Statistika', 'chart'],
    ];
    ?>
<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($title) ?> — Neo Med Cardio admin</title>
<link rel="icon" href="../images/favicon.png" type="image/png">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="adm-body">

<div class="adm">
  <aside class="adm-side">
    <a class="adm-brand" href="index.php">
      <img src="../images/logo.png" alt="">
      <span class="adm-brand__text">
        <strong>Neo Med Cardio</strong>
        <small>Admin panel</small>
      </span>
    </a>

    <nav class="adm-nav">
      <?php foreach ($nav as $key => [$href, $label, $icon]): ?>
        <a href="<?= h($href) ?>" class="adm-nav__item<?= $active === $key ? ' is-active' : '' ?>">
          <?= admin_icon($icon) ?><span><?= h($label) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <form class="adm-side__foot" method="post" action="logout.php">
      <button type="submit" class="adm-nav__item adm-nav__item--logout">
        <?= admin_icon('logout') ?><span>Chiqish</span>
      </button>
    </form>
  </aside>

  <main class="adm-main">
    <header class="adm-head">
      <div class="adm-head__text">
        <h1><?= h($title) ?></h1>
        <p><?= h($subtitle) ?></p>
      </div>
      <?php if ($actionsHtml !== ''): ?>
        <div class="adm-head__actions"><?= $actionsHtml ?></div>
      <?php endif; ?>
    </header>
<?php
}

function admin_foot(): void {
    ?>
  </main>
</div>

</body>
</html>
<?php
}
