<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = getRequestBody();
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if ($username === '' || $password === '') {
        jsonError('Nom d\'utilisateur et mot de passe requis');
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT id, username, password_hash, display_name FROM users WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonError('Identifiants incorrects', 401);
    }

    $token = createJwt([
        'user_id' => $user['id'],
        'username' => $user['username'],
        'display_name' => $user['display_name'],
    ]);

    jsonResponse([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'displayName' => $user['display_name'],
        ],
    ]);
}

// GET /api/auth.php - verify current token
if ($method === 'GET') {
    $user = requireAuth();
    jsonResponse([
        'user' => [
            'id' => $user['user_id'],
            'username' => $user['username'],
            'displayName' => $user['display_name'],
        ],
    ]);
}

jsonError('Méthode non autorisée', 405);
