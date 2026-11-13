<?php
declare(strict_types=1);

$LOG = '/tmp/makka-ai-8001.log';
$n   = isset($_GET['n']) ? max(10, min(500, (int)$_GET['n'])) : 80;

header('Content-Type: application/json; charset=utf-8');

// ---------- CLEAR ----------
if (isset($_GET['clear'])) {
    @file_put_contents($LOG, '');
    echo json_encode(['ok' => true, 'cleared' => true]);
    exit;
}

// ---------- READ ----------
if (!is_file($LOG)) {
    echo json_encode(['ok' => false, 'lines' => [], 'error' => 'log no existe']);
    exit;
}

$fp = @fopen($LOG, 'r');
if (!$fp) {
    echo json_encode(['ok' => false, 'lines' => [], 'error' => 'no se puede leer']);
    exit;
}

fseek($fp, 0, SEEK_END);
$size = ftell($fp);

// ⬇ FIX: archivo vacío → no llamar a fread(0)
if ($size <= 0) {
    fclose($fp);
    echo json_encode(['ok' => true, 'lines' => [], 'size' => 0]);
    exit;
}

$chunk = min($size, 64 * 1024);
fseek($fp, -$chunk, SEEK_END);
$tail = fread($fp, $chunk);
fclose($fp);

if ($tail === false || $tail === '') {
    echo json_encode(['ok' => true, 'lines' => [], 'size' => $size]);
    exit;
}

$lines = preg_split("/\r?\n/", trim($tail));
$lines = array_slice($lines, -$n);

echo json_encode(['ok' => true, 'lines' => $lines, 'size' => $size]);
