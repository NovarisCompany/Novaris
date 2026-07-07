<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function conectarBD(): mysqli
{
    $conexion = mysqli_connect("localhost", "root", "", "sistema_gestion_ti");
    mysqli_set_charset($conexion, "utf8mb4");

    return $conexion;
}

function escaparHTML(?string $texto): string
{
    return htmlspecialchars($texto ?? "", ENT_QUOTES, "UTF-8");
}
