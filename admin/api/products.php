<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'list';

// Cualquier acción que no sea "list" requiere login
if ($action !== 'list') {
    if (empty($_SESSION['admin_logged_in'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
}

try {
    switch ($action) {

        case 'list':
            $pdo = db();

            $stmt = $pdo->query('SELECT * FROM productos ORDER BY id DESC');
            $rows = $stmt->fetchAll();
            $ids  = array_column($rows, 'id');

            $imagenesPorProducto = [];

            if ($ids) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmtImg = $pdo->prepare(
                    'SELECT * FROM producto_imagenes
                     WHERE producto_id IN (' . $in . ')
                     ORDER BY orden ASC, id ASC'
                );
                $stmtImg->execute($ids);
                while ($img = $stmtImg->fetch()) {
                    $pid = $img['producto_id'];
                    if (!isset($imagenesPorProducto[$pid])) {
                        $imagenesPorProducto[$pid] = [];
                    }
                    $imagenesPorProducto[$pid][] = UPLOAD_URL_BASE . $img['archivo'];
                }
            }

            foreach ($rows as &$r) {
                if (!empty($r['imagen'])) {
                    $r['imagen_url'] = UPLOAD_URL_BASE . $r['imagen'];
                }
                $pid = $r['id'];
                $r['imagenes'] = $imagenesPorProducto[$pid] ?? [];
                if (!empty($r['imagen_url'])) {
                    array_unshift($r['imagenes'], $r['imagen_url']);
                    $r['imagenes'] = array_values(array_unique($r['imagenes']));
                }
            }

            echo json_encode(['products' => $rows]);
            break;

        case 'save':
            $pdo = db();

            $id         = $_POST['id'] ?? '';
            $nombre     = trim($_POST['nombre'] ?? '');
            $categoria  = trim($_POST['categoria'] ?? '');
            $coleccion  = trim($_POST['coleccion'] ?? '');
            $precio     = floatval($_POST['precio'] ?? 0);
            $tallas     = trim($_POST['tallas'] ?? '');
            $colores    = trim($_POST['colores'] ?? '');
            $stock      = intval($_POST['stock'] ?? 0);
            $descripcion= trim($_POST['descripcion'] ?? '');
            $tags       = trim($_POST['tags'] ?? '');
            $destacado  = isset($_POST['destacado']) ? 1 : 0;
            $whatsapp   = trim($_POST['whatsapp'] ?? '');

            if ($nombre === '') {
                throw new Exception('El nombre es obligatorio');
            }

            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0775, true);
            }

            $imagenPrincipal = null;
            if (!empty($_FILES['imagen']['name'])) {
                $ext      = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $safeName = uniqid('nero_main_') . '.' . strtolower($ext);
                $dest     = UPLOAD_DIR . $safeName;
                if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                    throw new Exception('No se pudo subir la imagen principal');
                }
                $imagenPrincipal = $safeName;
            }

            if ($id) {
                // UPDATE
                if ($imagenPrincipal) {
                    $sql = 'UPDATE productos
                            SET nombre=?, categoria=?, coleccion=?, precio=?, tallas=?,
                                colores=?, stock=?, descripcion=?, tags=?, destacado=?,
                                whatsapp=?, imagen=?
                            WHERE id=?';
                    $params = [
                        $nombre,$categoria,$coleccion,$precio,$tallas,
                        $colores,$stock,$descripcion,$tags,$destacado,
                        $whatsapp,$imagenPrincipal,$id
                    ];
                } else {
                    $sql = 'UPDATE productos
                            SET nombre=?, categoria=?, coleccion=?, precio=?, tallas=?,
                                colores=?, stock=?, descripcion=?, tags=?, destacado=?,
                                whatsapp=?
                            WHERE id=?';
                    $params = [
                        $nombre,$categoria,$coleccion,$precio,$tallas,
                        $colores,$stock,$descripcion,$tags,$destacado,
                        $whatsapp,$id
                    ];
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } else {
                // INSERT
                $sql = 'INSERT INTO productos
                        (nombre,categoria,coleccion,precio,tallas,colores,stock,
                         descripcion,tags,destacado,whatsapp,imagen)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nombre,$categoria,$coleccion,$precio,$tallas,$colores,
                    $stock,$descripcion,$tags,$destacado,$whatsapp,$imagenPrincipal
                ]);
                $id = $pdo->lastInsertId();
            }

            // Galería
            if (!empty($_FILES['galeria']['name']) && is_array($_FILES['galeria']['name'])) {
                // Borra galería anterior
                $del = $pdo->prepare('DELETE FROM producto_imagenes WHERE producto_id=?');
                $del->execute([$id]);

                $count  = count($_FILES['galeria']['name']);
                $orden  = 1;
                $insert = $pdo->prepare(
                    'INSERT INTO producto_imagenes (producto_id,archivo,orden)
                     VALUES (?,?,?)'
                );

                for ($i=0; $i<$count; $i++) {
                    if ($_FILES['galeria']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $name = $_FILES['galeria']['name'][$i];
                    $tmp  = $_FILES['galeria']['tmp_name'][$i];
                    $ext  = pathinfo($name, PATHINFO_EXTENSION);
                    $safeName = uniqid('nero_gal_') . '.' . strtolower($ext);
                    $dest = UPLOAD_DIR . $safeName;
                    if (move_uploaded_file($tmp, $dest)) {
                        $insert->execute([$id, $safeName, $orden++]);
                    }
                }
            }

            echo json_encode(['success' => true, 'id' => $id]);
            break;

        case 'delete':
            $pdo = db();
            $id = intval($_POST['id'] ?? 0);
            if (!$id) {
                throw new Exception('ID inválido');
            }

            // Borra galerías (registros; los archivos físicos podrías eliminarlos si quieres)
            $delImg = $pdo->prepare('DELETE FROM producto_imagenes WHERE producto_id=?');
            $delImg->execute([$id]);

            // Borra producto
            $stmt = $pdo->prepare('DELETE FROM productos WHERE id=?');
            $stmt->execute([$id]);

            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
