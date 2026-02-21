<?php
require_once __DIR__ . '/config.php';
$user = requireAuth();
$userId = $user['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDbConnection();

// GET: fetch entries for a date range
if ($method === 'GET') {
    $dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('monday this week'));
    $dateTo = $_GET['to'] ?? date('Y-m-d', strtotime('saturday this week'));

    $stmt = $pdo->prepare('
        SELECT te.id, te.entry_date, te.period, te.slot, te.workstation_id,
               w.code AS workstation_code, w.label AS workstation_label
        FROM time_entries te
        JOIN workstations w ON w.id = te.workstation_id
        WHERE te.user_id = ? AND te.entry_date BETWEEN ? AND ?
        ORDER BY te.entry_date, FIELD(te.period, "morning", "afternoon"), te.slot
    ');
    $stmt->execute([$userId, $dateFrom, $dateTo]);
    jsonResponse($stmt->fetchAll());
}

// POST: create or update entries for a day
if ($method === 'POST') {
    $data = getRequestBody();
    $entries = $data['entries'] ?? [];

    if (empty($entries)) {
        jsonError('Aucune entrée fournie');
    }

    $pdo->beginTransaction();
    try {
        foreach ($entries as $entry) {
            $date = $entry['date'] ?? null;
            $period = $entry['period'] ?? null;
            $slot = (int)($entry['slot'] ?? 0);
            $workstationId = (int)($entry['workstationId'] ?? 0);

            if (!$date || !$period || !$workstationId) {
                throw new InvalidArgumentException('Champs requis manquants: date, period, workstationId');
            }

            if (!in_array($period, ['morning', 'afternoon'], true)) {
                throw new InvalidArgumentException('Période invalide');
            }

            if ($slot < 0 || $slot > 4) {
                throw new InvalidArgumentException('Slot invalide (0-4)');
            }

            $stmt = $pdo->prepare('
                INSERT INTO time_entries (user_id, entry_date, period, slot, workstation_id)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE workstation_id = VALUES(workstation_id), updated_at = NOW()
            ');
            $stmt->execute([$userId, $date, $period, $slot, $workstationId]);
        }
        $pdo->commit();
        jsonResponse(['message' => 'Entrées enregistrées']);
    } catch (\Exception $e) {
        $pdo->rollBack();
        jsonError($e->getMessage());
    }
}

// DELETE: remove an entry
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonError('ID requis');
    }

    $stmt = $pdo->prepare('DELETE FROM time_entries WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);

    if ($stmt->rowCount() === 0) {
        jsonError('Entrée non trouvée', 404);
    }

    jsonResponse(['message' => 'Entrée supprimée']);
}

jsonError('Méthode non autorisée', 405);
