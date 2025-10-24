<?php
declare(strict_types=1);

function money_fmt(int $kurus): string {
    return number_format($kurus/100, 2, ',', '.') . ' TL';
}

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ts_to_local(int $ts): string {
    return date('Y-m-d H:i', $ts);
}
