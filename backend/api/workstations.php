<?php
require_once __DIR__ . '/config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Méthode non autorisée', 405);
}

$pdo = getDbConnection();
$stmt = $pdo->query('SELECT id, code, label FROM workstations ORDER BY sort_order');
jsonResponse($stmt->fetchAll());
