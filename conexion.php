<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function debugLog(string $location, string $message, array $data = [], string $hypothesisId = ''): void
{
    $entry = json_encode([
        'sessionId' => '9215b0',
        'timestamp' => (int) round(microtime(true) * 1000),
        'location' => $location,
        'message' => $message,
        'data' => $data,
        'hypothesisId' => $hypothesisId,
        'runId' => 'initial',
    ], JSON_UNESCAPED_UNICODE);
    file_put_contents(__DIR__ . '/debug-9215b0.log', $entry . "\n", FILE_APPEND | LOCK_EX);
}
// #endregion

function conectarBD(): mysqli
{
    try {
        $conexion = mysqli_connect("localhost", "root", "", "sistema_gestion_ti");
        mysqli_set_charset($conexion, "utf8mb4");
        // #region agent log
        debugLog('conexion.php:conectarBD', 'Conexión BD exitosa', ['host' => 'localhost', 'db' => 'sistema_gestion_ti'], 'A');
        // #endregion
        return $conexion;
    } catch (mysqli_sql_exception $e) {
        // #region agent log
        debugLog('conexion.php:conectarBD', 'Conexión BD fallida', ['error' => $e->getMessage(), 'code' => $e->getCode()], 'A');
        // #endregion
        throw $e;
    }
}

function escaparHTML(?string $texto): string
{
    return htmlspecialchars($texto ?? "", ENT_QUOTES, "UTF-8");
}
