<?php
session_start();
require_once __DIR__ . '/api/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['user'] ?? '';
    $p = $_POST['pass'] ?? '';
    if ($u === ADMIN_USER && $p === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>NERO · Login Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center" style="min-height:100vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card bg-black text-light border-secondary">
          <div class="card-body">
            <h1 class="h5 text-center mb-3">NERO · Panel Admin</h1>
            <?php if ($error): ?>
              <div class="alert alert-danger py-1 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post">
              <div class="mb-2">
                <label class="form-label small">Usuario</label>
                <input type="text" name="user" class="form-control form-control-sm bg-dark text-light border-secondary" required>
              </div>
              <div class="mb-3">
                <label class="form-label small">Contraseña</label>
                <input type="password" name="pass" class="form-control form-control-sm bg-dark text-light border-secondary" required>
              </div>
              <button class="btn btn-light w-100 btn-sm">Entrar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
