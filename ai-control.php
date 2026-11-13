<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// Configuración por variables de entorno (con fallback relativo)
$PROJECT_DIR = getenv('MAKKA_AI_DIR') ?: __DIR__ . '/ai';
$PORT        = (int)(getenv('MAKKA_AI_PORT') ?: 8001);
$VENV        = getenv('MAKKA_AI_VENV') ?: $PROJECT_DIR . '/.venv';
$PID_FILE    = sys_get_temp_dir() . '/makka-ai-' . $PORT . '.pid';
$LOG_FILE    = sys_get_temp_dir() . '/makka-ai-' . $PORT . '.log';

function is_up(int $port, float $timeout = 0.3): bool {
    $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, $timeout);
    if ($fp) { fclose($fp); return true; }
    return false;
}

function read_pid(string $file): int {
    return is_file($file) ? (int)trim((string)@file_get_contents($file)) : 0;
}

function pid_alive(int $pid): bool {
    if ($pid <= 0) return false;
    if (!is_dir('/proc/' . $pid)) return false;
    $cmd = @file_get_contents('/proc/' . $pid . '/cmdline');
    return $cmd !== false && str_contains($cmd, 'uvicorn');
}

$action = $_GET['action'] ?? 'status';

switch ($action) {

  // ---------------- STATUS ----------------
  case 'status':
    $pid = read_pid($PID_FILE);
    echo json_encode([
        'running' => is_up($PORT),
        'pid'     => $pid ?: null,
        'port'    => $PORT,
    ]);
    break;

  // ---------------- START ----------------
  case 'start':
    if (is_up($PORT)) {
        echo json_encode(['ok' => true, 'msg' => 'ya estaba arrancado']);
        break;
    }

    // `sh -c "… exec uvicorn …"` convierte el sh en uvicorn → $! = PID real.
    // `</dev/null` evita que Uvicorn herede stdin y se quede colgado.
    // `>> LOG 2>&1` antes del redireccionamiento de stdin: correcto orden bash.
    $cmd = sprintf(
        'sh -c "cd %s && exec env HOME=/tmp XDG_RUNTIME_DIR=/tmp %s/bin/uvicorn app:app '
        . '--host 127.0.0.1 --port %d --log-level info --access-log" '
        . '>> %s 2>&1 < /dev/null & echo $!',
        escapeshellarg($PROJECT_DIR),
        escapeshellarg($VENV),
        $PORT,
        escapeshellarg($LOG_FILE)
    );

    $out = [];
    exec($cmd, $out, $rc);
    $pid = (int)($out[0] ?? 0);
    if ($pid > 0) {
        @file_put_contents($PID_FILE, (string)$pid);
    }

    // esperar a que levante (hasta 8 s)
    $up = false;
    for ($i = 0; $i < 16; $i++) {
        usleep(500_000);
        if (is_up($PORT)) { $up = true; break; }
    }

    echo json_encode([
        'ok'  => $up,
        'pid' => $pid ?: null,
        'msg' => $up ? 'arrancado'
                     : 'no levantó a tiempo (mira ' . $LOG_FILE . ')',
    ]);
    break;

  // ---------------- STOP ----------------
  case 'stop':
    $pid = read_pid($PID_FILE);

    if ($pid && pid_alive($pid)) {
        // SIGTERM limpio
        exec('kill ' . $pid . ' 2>/dev/null');
        for ($i = 0; $i < 10; $i++) {
            usleep(200_000);
            if (!pid_alive($pid)) break;
        }
        // SIGKILL si no cedió
        if (pid_alive($pid)) {
            exec('kill -9 ' . $pid . ' 2>/dev/null');
        }
    } else {
        // huérfano: matar por puerto
        exec('fuser -k ' . $PORT . '/tcp 2>/dev/null');
    }

    @unlink($PID_FILE);

    // esperar a que el puerto quede libre (hasta 2 s)
    for ($i = 0; $i < 10; $i++) {
        if (!is_up($PORT)) break;
        usleep(200_000);
    }

    echo json_encode([
        'ok'  => !is_up($PORT),
        'msg' => !is_up($PORT) ? 'parado' : 'no se pudo parar',
    ]);
    break;

  // ---------------- DEFAULT ----------------
  default:
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'acción no válida (usa status | start | stop)']);
}
