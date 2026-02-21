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

$stmt = $pdo->prepare('
    SELECT te.entry_date, te.period, te.slot,
           w.label AS workstation
    FROM time_entries te
    JOIN workstations w ON w.id = te.workstation_id
    WHERE te.user_id = ? AND te.entry_date BETWEEN ? AND ?
    ORDER BY te.entry_date, FIELD(te.period, "morning", "afternoon"), te.slot
');
$stmt->execute([$userId, $dateFrom, $dateTo]);
$rows = $stmt->fetchAll();

// Generate CSV (compatible with Excel)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="timetracking_' . $dateFrom . '_' . $dateTo . '.csv"');

// Remove JSON content-type set by config.php
header_remove('Content-Type');
header('Content-Type: text/csv; charset=utf-8');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// Header row
fputcsv($output, ['Date', 'Période', 'Créneau', 'Poste de travail'], ';');

foreach ($rows as $row) {
    $periodLabel = $row['period'] === 'morning' ? 'Matin' : 'Après-midi';
    $slotLabel = $row['slot'] == 0 ? 'Demi-journée complète' : 'Heure ' . $row['slot'];

    fputcsv($output, [
        $row['entry_date'],
        $periodLabel,
        $slotLabel,
        $row['workstation'],
    ], ';');
}

fclose($output);
exit;
