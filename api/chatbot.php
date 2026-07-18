<?php
session_start();
require_once __DIR__ . '/../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!is_array($data) || !isset($data['history']) || !is_array($data['history'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el campo history']);
    exit;
}

$history = array_values(array_filter($data['history'], function ($entry) {
    return is_array($entry)
        && isset($entry['role'], $entry['content'])
        && is_string($entry['content']);
}));

$history = array_slice($history, -20);

$listaUsuarios = '';
$usuarioActual = '';

try {
    $conexion = conectarBD();
    $consulta = 'SELECT u.nombre, u.apellido, r.nombre_rol
                 FROM usuario AS u
                 LEFT JOIN roles AS r ON u.id_rol = r.id_rol
                 ORDER BY u.nombre, u.apellido';
    $resultado = mysqli_query($conexion, $consulta);

    $filas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $nombreCompleto = trim(($fila['nombre'] ?? '') . ' ' . ($fila['apellido'] ?? ''));
        $rol = $fila['nombre_rol'] ?? 'Sin rol';
        $filas[] = $nombreCompleto . ' (' . $rol . ')';
    }

    $listaUsuarios = implode(', ', $filas);

    if (isset($_SESSION['id_usuario'])) {
        $nombre = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
        $rol = $_SESSION['rol'] ?? 'Sin rol';
        $usuarioActual = $nombre . ' (' . $rol . ')';
    }
} catch (mysqli_sql_exception $e) {
    $listaUsuarios = 'No disponible';
}

$contextoUsuario = $usuarioActual !== ''
    ? "Usuario que está chateando ahora: {$usuarioActual}."
    : 'No hay un usuario logueado en esta sesión.';

$systemPrompt = "Sos la asistente virtual de Novaris, un sistema de gestión de soporte informático, tickets y recursos de TI.

Tu personalidad:
Hablas en español rioplatense (usa 'vos', 'queres', 'tenes', 'che').
Sos amable, profesional y directa.
Respuestas breves: máximo 3 oraciones.

Contexto de Novaris:
Novaris es una plataforma interna para gestionar tickets de soporte, usuarios, técnicos y equipos.
La app permite crear solicitudes de soporte, ver tickets activos, asignar técnicos y revisar equipos.

Roles del sistema:
Administrador: puede ver y gestionar todos los tickets, usuarios y equipos.
Técnico: puede ver y gestionar tickets asignados, actualizar estados y comunicarse con solicitantes.
Solicitante: puede crear tickets, ver el estado de sus solicitudes y comunicarse con técnicos.

{$contextoUsuario}

Lista de usuarios registrados (nombre y rol):
{$listaUsuarios}

Reglas sobre usuarios, en este orden de prioridad:
1. Si preguntan por sí mismos (por ejemplo: cómo me llamo, quién soy, qué rol tengo, cuál es mi rol), respondé con el nombre y rol del usuario que está chateando ahora.
2. Si preguntan por el rol de otro usuario, buscá el nombre en la lista de arriba y respondé con su nombre y rol exacto.
3. Si preguntan por un nombre que no está en la lista, indicá que no está registrado.


Políticas y comportamiento:
No inventes funcionalidades que no existan en el sistema.
Si no sabes algo, sugerí usar el formulario de Mesa de ayuda o contactar a soporte.";

$messages = array_merge(
    [['role' => 'system', 'content' => $systemPrompt]],
    array_map(function ($entry) {
        return ['role' => $entry['role'], 'content' => $entry['content']];
    }, $history)
);

$apiKey = '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';

if (empty($apiKey)) {
    echo json_encode(['reply' => getLocalReply($messages, $usuarioActual)]);
    exit;
}

$payload = [
    'model' => 'llama-3.1-8b-instant',
    'max_tokens' => 400,
    'temperature' => 0.7,
    'messages' => $messages
];

$groqResponse = callGroq($apiKey, $payload);

if (isset($groqResponse['error'])) {
    echo json_encode(['reply' => getLocalReply($messages, $usuarioActual)]);
    exit;
}

$reply = trim($groqResponse['choices'][0]['message']['content'] ?? '');
if ($reply === '') {
    $reply = getLocalReply($messages, $usuarioActual);
}

echo json_encode(['reply' => $reply]);

function callGroq($apiKey, $payload)
{
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $httpCode >= 400) {
        $decoded = json_decode($body, true);
        return ['error' => $decoded['error']['message'] ?? 'Error de Groq'];
    }

    return json_decode($body, true);
}

function getLocalReply($messages, $usuarioActual)
{
    $lastUser = '';

    foreach (array_reverse($messages) as $message) {
        if (($message['role'] ?? '') === 'user') {
            $lastUser = strtolower($message['content']);
            break;
        }
    }

    if (
        $usuarioActual !== ''
        && (
            strpos($lastUser, 'como me llamo') !== false
            || strpos($lastUser, 'cómo me llamo') !== false
            || strpos($lastUser, 'quien soy') !== false
            || strpos($lastUser, 'quién soy') !== false
            || strpos($lastUser, 'que rol') !== false
            || strpos($lastUser, 'qué rol') !== false
            || strpos($lastUser, 'mi rol') !== false
        )
    ) {
        return 'Sos ' . $usuarioActual . '.';
    }

    if (strpos($lastUser, 'ticket') !== false) {
        return 'Podés crear un ticket desde la sección de Mesa de ayuda. Si querés, te digo paso a paso cómo hacerlo.';
    }

    if (strpos($lastUser, 'técnico') !== false || strpos($lastUser, 'tecnico') !== false) {
        return 'Para asignar un técnico, abrí el ticket y elegí el responsable desde la vista de administración.';
    }

    if (strpos($lastUser, 'estado') !== false || strpos($lastUser, 'ver') !== false) {
        return 'Podés revisar el estado de tus tickets en la sección correspondiente del portal.';
    }

    if (strpos($lastUser, 'equipo') !== false) {
        return 'Los equipos se gestionan desde la sección de inventario y equipos del sistema.';
    }

    return 'Puedo ayudarte con tickets, asignaciones y estados del sistema. ¿Querés que te explique cómo crear uno?';
}
