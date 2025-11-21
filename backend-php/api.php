<?php
// CORS básico
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/BDconfig.php';


$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Filtros opcionales para GET
$filtroTitulo = isset($_GET['titulo']) ? trim($_GET['titulo']) : null;
$filtroAutor  = isset($_GET['autor']) ? trim($_GET['autor']) : null;
$filtroAnio   = isset($_GET['anio']) ? intval($_GET['anio']) : null; // año_publicacion
$filtroGenero = isset($_GET['genero']) ? trim($_GET['genero']) : null;

// Leer JSON (para POST y PUT)
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {

    // ================== LISTAR / BUSCAR ==================
    case 'GET':
        // 1) Si viene id, priorizamos obtener UN libro
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM libros WHERE id = ?");
            $stmt->execute([$id]);
            $libro = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($libro) {
                echo json_encode($libro);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Libro no encontrado']);
            }
            break;
        }

        // 2) Si no hay id, armamos consulta con filtros opcionales
        $query = "SELECT * FROM libros";
        $conditions = [];
        $params = [];

        if ($filtroTitulo !== null && $filtroTitulo !== '') {
            $conditions[] = "titulo LIKE ?";
            $params[] = "%$filtroTitulo%";
        }

        if ($filtroAutor !== null && $filtroAutor !== '') {
            $conditions[] = "autor LIKE ?";
            $params[] = "%$filtroAutor%";
        }

        if ($filtroAnio !== null && $filtroAnio > 0) {
            $conditions[] = "anio_publicacion = ?";
            $params[] = $filtroAnio;
        }

        if ($filtroGenero !== null && $filtroGenero !== '') {
            $conditions[] = "genero LIKE ?";
            $params[] = "%$filtroGenero%";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // al construir $query para listar sin id:
        $query .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($libros);
        break;

    // ================== CREAR ==================
    case 'POST':
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido']);
            exit;
        }

        $titulo = $input['titulo'] ?? null;
        $autor  = $input['autor'] ?? null;
        $anio   = $input['anio_publicacion'] ?? null;
        $genero = $input['genero'] ?? null;
        $isbn   = $input['isbn'] ?? null;

        if (!$titulo || !$autor || !$anio) {
            http_response_code(400);
            echo json_encode(['error' => 'Título, autor y año son obligatorios']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO libros (titulo, autor, anio_publicacion, genero, isbn)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$titulo, $autor, $anio, $genero, $isbn]);

        echo json_encode([
            'message' => 'Libro creado correctamente',
            'id'      => $pdo->lastInsertId()
        ]);
        break;

    // ================== ACTUALIZAR ==================
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido en la URL para actualizar']);
            exit;
        }

        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido']);
            exit;
        }

        $titulo = $input['titulo'] ?? null;
        $autor  = $input['autor'] ?? null;
        $anio   = $input['anio_publicacion'] ?? null;
        $genero = $input['genero'] ?? null;
        $isbn   = $input['isbn'] ?? null;

        if (!$titulo || !$autor || !$anio) {
            http_response_code(400);
            echo json_encode(['error' => 'Título, autor y año son obligatorios']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE libros
            SET titulo = ?, autor = ?, anio_publicacion = ?, genero = ?, isbn = ?
            WHERE id = ?
        ");
        $stmt->execute([$titulo, $autor, $anio, $genero, $isbn, $id]);

        echo json_encode(['message' => 'Libro actualizado correctamente']);
        break;

    // ================== ELIMINAR ==================
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido en la URL para eliminar']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM libros WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['message' => 'Libro eliminado correctamente']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
