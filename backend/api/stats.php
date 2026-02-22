<?php
require_once __DIR__ . '/config.php';
$user = requireAuth();
$userId = $user['user_id'];
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Méthode non autorisée', 405);
}

$dateFrom = $_GET['from'] ?? date('Y-m-01');
$dateTo = $_GET['to'] ?? date('Y-m-t');

// Calculate time spent per workstation
// slot=0 means whole half-day (4 hours equivalent)
// slot=1-4 means 1 hour each
$stmt = $pdo->prepare('
    SELECT w.code, w.label, w.sort_order,
           SUM(CASE WHEN te.slot = 0 THEN 4 ELSE 1 END) AS total_hours
    FROM time_entries te
    JOIN workstations w ON w.id = te.workstation_id
    WHERE te.user_id = ? AND te.entry_date BETWEEN ? AND ?
    GROUP BY w.id, w.code, w.label, w.sort_order
    ORDER BY total_hours DESC
');
$stmt->execute([$userId, $dateFrom, $dateTo]);
$byWorkstation = $stmt->fetchAll();

// Daily breakdown
$stmt = $pdo->prepare('
    SELECT te.entry_date,
           SUM(CASE WHEN te.slot = 0 THEN 4 ELSE 1 END) AS total_hours
    FROM time_entries te
    WHERE te.user_id = ? AND te.entry_date BETWEEN ? AND ?
    GROUP BY te.entry_date
    ORDER BY te.entry_date
');
$stmt->execute([$userId, $dateFrom, $dateTo]);
$byDay = $stmt->fetchAll();

$totalHours = array_sum(array_column($byWorkstation, 'total_hours'));

// Daily breakdown per workstation (for evolution chart)
$stmt = $pdo->prepare('
    SELECT te.entry_date, w.label,
           SUM(CASE WHEN te.slot = 0 THEN 4 ELSE 1 END) AS hours
    FROM time_entries te
    JOIN workstations w ON w.id = te.workstation_id
    WHERE te.user_id = ? AND te.entry_date BETWEEN ? AND ?
    GROUP BY te.entry_date, w.id, w.label
    ORDER BY te.entry_date, w.label
');
$stmt->execute([$userId, $dateFrom, $dateTo]);
$dailyByWorkstation = $stmt->fetchAll();

jsonResponse([
    'from' => $dateFrom,
    'to' => $dateTo,
    'totalHours' => $totalHours,
    'byWorkstation' => $byWorkstation,
    'byDay' => $byDay,
    'dailyByWorkstation' => $dailyByWorkstation,
]);
