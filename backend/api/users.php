<?php
require_once __DIR__ . '/config.php';
$admin = requireAdmin();
$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? '';

// GET: list all users
if ($method === 'GET') {
    $stmt = $pdo->query('SELECT id, username, display_name, is_admin, is_active, created_at FROM users ORDER BY id');
    $users = $stmt->fetchAll();

    // Convert int flags to booleans for JSON
    foreach ($users as &$u) {
        $u['is_admin'] = (bool)$u['is_admin'];
        $u['is_active'] = (bool)$u['is_active'];
    }

    jsonResponse($users);
}

// POST: create a new user
if ($method === 'POST') {
    $data = getRequestBody();
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    $displayName = trim($data['display_name'] ?? '');
    $isAdmin = !empty($data['is_admin']);

    if ($username === '' || $password === '' || $displayName === '') {
        jsonError('Nom d\'utilisateur, mot de passe et nom d\'affichage requis');
    }

    if (strlen($password) < 4) {
        jsonError('Le mot de passe doit contenir au moins 4 caractères');
    }

    // Check username uniqueness
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        jsonError('Ce nom d\'utilisateur existe déjà');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, display_name, is_admin) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, $hash, $displayName, $isAdmin ? 1 : 0]);

    jsonResponse([
        'message' => 'Utilisateur créé',
        'id' => (int)$pdo->lastInsertId(),
    ], 201);
}

// PUT: update a user
if ($method === 'PUT') {
    $data = getRequestBody();
    $id = (int)($data['id'] ?? 0);

    if ($id <= 0) {
        jsonError('ID utilisateur requis');
    }

    // Check user exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        jsonError('Utilisateur non trouvé', 404);
    }

    $fields = [];
    $params = [];

    if (isset($data['display_name']) && trim($data['display_name']) !== '') {
        $fields[] = 'display_name = ?';
        $params[] = trim($data['display_name']);
    }

    if (isset($data['username']) && trim($data['username']) !== '') {
        // Check uniqueness
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $stmt->execute([trim($data['username']), $id]);
        if ($stmt->fetch()) {
            jsonError('Ce nom d\'utilisateur existe déjà');
        }
        $fields[] = 'username = ?';
        $params[] = trim($data['username']);
    }

    if (isset($data['password']) && $data['password'] !== '') {
        if (strlen($data['password']) < 4) {
            jsonError('Le mot de passe doit contenir au moins 4 caractères');
        }
        $fields[] = 'password_hash = ?';
        $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
    }

    if (isset($data['is_admin'])) {
        $fields[] = 'is_admin = ?';
        $params[] = !empty($data['is_admin']) ? 1 : 0;
    }

    if (isset($data['is_active'])) {
        $fields[] = 'is_active = ?';
        $params[] = !empty($data['is_active']) ? 1 : 0;
    }

    if (empty($fields)) {
        jsonError('Aucun champ à mettre à jour');
    }

    $params[] = $id;
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    jsonResponse(['message' => 'Utilisateur mis à jour']);
}

// DELETE: deactivate a user (soft delete)
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        jsonError('ID requis');
    }

    // Prevent self-deletion
    if ($id === (int)$admin['user_id']) {
        jsonError('Vous ne pouvez pas supprimer votre propre compte');
    }

    $stmt = $pdo->prepare('UPDATE users SET is_active = 0 WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        jsonError('Utilisateur non trouvé', 404);
    }

    jsonResponse(['message' => 'Utilisateur désactivé']);
}

jsonError('Méthode non autorisée', 405);
