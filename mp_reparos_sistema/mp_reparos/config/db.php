<?php
// ── Configuração do banco de dados ──────────────────────
// Altere aqui se necessário
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // senha do MySQL (padrão XAMPP = vazio)
define('DB_NAME', 'mp_reparos');

function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode(['erro' => 'Falha na conexão: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
