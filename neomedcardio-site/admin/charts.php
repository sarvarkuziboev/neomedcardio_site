<?php

/**
 * Tashqi kutubxonasiz diagrammalar.
 *
 * Muhim qoida: barcha MATN har doim oddiy HTML elementlarida chiziladi,
 * SVG ichida emas — aks holda SVG viewBox bilan birga shrift ham
 * masshtablanib, harflar buzilib ko'rinadi.
 */

/**
 * Vertikal ustunli diagramma (vaqt bo'yicha dinamika uchun).
 *
 * @param array<int,array{label:string,value:int,sub?:string}> $points
 */
function chart_bars(array $points, string $color = 'var(--chart-2)'): string {
    if (empty($points)) {
        return '<p class="chart-empty">Ma\'lumot yo\'q.</p>';
    }
    $max = max(1, max(array_column($points, 'value')));
    $ticks = chart_axis_ticks($max);
    $axisMax = max($ticks);

    $html = '<div class="bars">';

    $html .= '<div class="bars__grid">';
    foreach (array_reverse($ticks) as $t) {
        $html .= '<div class="bars__gridline"><span>' . $t . '</span></div>';
    }
    $html .= '</div>';

    $html .= '<div class="bars__plot">';
    foreach ($points as $p) {
        $v = (int)$p['value'];
        $pct = $axisMax > 0 ? ($v / $axisMax) * 100 : 0;
        $label = h($p['label']);
        $title = $label . ($p['sub'] ?? '') . ' — ' . $v . ' ta';
        $html .= '<div class="bars__col" title="' . h($title) . '">';
        $html .= '<div class="bars__track">';
        $html .= '<div class="bars__bar' . ($v === 0 ? ' is-empty' : '') . '" style="height:' . round($pct, 2) . '%;--bar-color:' . $color . '">';
        if ($v > 0) {
            $html .= '<span class="bars__value">' . $v . '</span>';
        }
        $html .= '</div></div>';
        $short = isset($p['short']) ? h($p['short']) : $label;
        $html .= '<div class="bars__label"><span class="bars__label-full">' . $label . '</span>'
            . '<span class="bars__label-short">' . $short . '</span></div>';
        $html .= '</div>';
    }
    $html .= '</div></div>';

    return $html;
}

/** O'qdagi belgilar uchun ozroq "yumaloq" qiymatlar tanlaydi. */
function chart_axis_ticks(int $max): array {
    $step = 1;
    foreach ([1, 2, 5, 10, 20, 50, 100, 200, 500] as $candidate) {
        if ($max / $candidate <= 4) {
            $step = $candidate;
            break;
        }
        $step = $candidate;
    }
    $top = (int)(ceil($max / $step) * $step);
    $ticks = [];
    for ($v = 0; $v <= $top; $v += $step) {
        $ticks[] = $v;
    }
    return $ticks;
}

/**
 * Donut (pie) diagramma + yonida izoh ro'yxati.
 *
 * @param array<int,array{label:string,value:int,color:string}> $slices
 */
function chart_donut(array $slices, string $centerLabel = 'jami'): string {
    $total = array_sum(array_column($slices, 'value'));
    if ($total === 0) {
        return '<p class="chart-empty">Hozircha ma\'lumot yo\'q.</p>';
    }

    $svg = '<svg class="donut__svg" viewBox="0 0 42 42" role="img" aria-label="Diagramma">';
    $svg .= '<circle class="donut__ring" cx="21" cy="21" r="15.915"></circle>';

    $offset = 25; // 12 soat tomonidan boshlash uchun
    foreach ($slices as $s) {
        $value = (int)$s['value'];
        if ($value === 0) {
            continue;
        }
        $pct = ($value / $total) * 100;
        $svg .= sprintf(
            '<circle class="donut__seg" cx="21" cy="21" r="15.915" stroke="%s" stroke-dasharray="%.2f %.2f" stroke-dashoffset="%.2f"><title>%s: %d</title></circle>',
            $s['color'],
            $pct,
            100 - $pct,
            $offset,
            h($s['label']),
            $value
        );
        $offset -= $pct;
        if ($offset < 0) {
            $offset += 100;
        }
    }
    $svg .= '</svg>';

    $html = '<div class="donut-block">';
    $html .= '<div class="donut">' . $svg;
    $html .= '<div class="donut__center"><strong>' . $total . '</strong><span>' . h($centerLabel) . '</span></div>';
    $html .= '</div>';

    $html .= '<ul class="legend">';
    foreach ($slices as $s) {
        $value = (int)$s['value'];
        $pct = $total > 0 ? round(($value / $total) * 100) : 0;
        $html .= '<li class="legend__item">';
        $html .= '<i class="legend__dot" style="background:' . $s['color'] . '"></i>';
        $html .= '<span class="legend__label">' . h($s['label']) . '</span>';
        $html .= '<b class="legend__value">' . $value . '</b>';
        $html .= '<em class="legend__pct">' . $pct . '%</em>';
        $html .= '</li>';
    }
    $html .= '</ul></div>';

    return $html;
}

/**
 * Gorizontal reyting chiziqlari (uzun nomli kategoriyalar uchun qulay).
 *
 * @param array<int,array{label:string,value:int,color:string}> $rows
 */
function chart_rank(array $rows): string {
    if (empty($rows)) {
        return '<p class="chart-empty">Ma\'lumot yo\'q.</p>';
    }
    $max = max(1, max(array_column($rows, 'value')));
    $total = max(1, array_sum(array_column($rows, 'value')));

    $html = '<ul class="rank">';
    foreach ($rows as $r) {
        $value = (int)$r['value'];
        $pct = round(($value / $max) * 100, 1);
        $share = round(($value / $total) * 100);
        $html .= '<li class="rank__row">';
        $html .= '<div class="rank__top"><span class="rank__label">' . h($r['label']) . '</span>';
        $html .= '<span class="rank__meta"><b>' . $value . '</b> · ' . $share . '%</span></div>';
        $html .= '<div class="rank__track"><div class="rank__fill" style="width:' . $pct . '%;background:' . $r['color'] . '"></div></div>';
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}
