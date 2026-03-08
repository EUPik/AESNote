<?php
// server/api.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// Helper function for authentication
function authenticate($pdo, $username, $key) {
    if (empty($username) || empty($key)) return false;
    try {
        $stmt = $pdo->prepare("SELECT api_key FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($key, $user['api_key'])) {
            return true;
        }
    } catch (Exception $e) {
        return false;
    }
    return false;
}

try {
    require_once __DIR__ . '/db.php';

    $action = $_GET['action'] ?? '';
    $username = $_GET['username'] ?? '';
    $key = $_GET['key'] ?? ''; // Primary: Look for key in URL parameter
    
    if (empty($key)) {
        // Fallback: headers
        $headers = getallheaders();
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'x-api-key') {
                $key = $value;
                break;
            }
        }
        if (empty($key)) {
            $key = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['X_API_KEY'] ?? '';
        }
    }

    switch ($action) {
        case 'register':
            if (empty($username) || empty($key)) {
                http_response_code(400);
                echo json_encode(['error' => 'Username and Key are required']);
                break;
            }
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['error' => 'User already exists']);
                break;
            }
            $hashed_key = password_hash($key, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, api_key) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_key]);
            echo json_encode(['status' => 'success', 'message' => 'User registered']);
            break;

        case 'change_key':
            if (!authenticate($pdo, $username, $key)) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            $new_key = $headers['X-New-API-Key'] ?? $headers['x-new-api-key'] ?? '';
            if (empty($new_key)) {
                http_response_code(400);
                echo json_encode(['error' => 'New Key is required']);
                break;
            }
            $hashed_key = password_hash($new_key, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET api_key = ? WHERE username = ?");
            $stmt->execute([$hashed_key, $username]);
            echo json_encode(['status' => 'success', 'message' => 'Key updated']);
            break;

        case 'list':
            if (!authenticate($pdo, $username, $key)) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            $stmt = $pdo->prepare("SELECT filename, last_modified FROM notes WHERE username = ?");
            $stmt->execute([$username]);
            echo json_encode($stmt->fetchAll());
            break;

        case 'read':
            if (!authenticate($pdo, $username, $key)) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            $filename = $_GET['filename'] ?? '';
            $stmt = $pdo->prepare("SELECT content FROM notes WHERE username = ? AND filename = ?");
            $stmt->execute([$username, $filename]);
            $note = $stmt->fetch();
            if ($note) {
                // Clear the default application/json and set appropriate type
                if (str_ends_with($filename, '.ptxt')) {
                    header('Content-Type: application/octet-stream');
                } else {
                    header('Content-Type: text/plain');
                }
                echo $note['content'];
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Note not found']);
            }
            break;

        case 'write':
            if (!authenticate($pdo, $username, $key)) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            $filename = $_GET['filename'] ?? '';
            $content = file_get_contents('php://input');
            $stmt = $pdo->prepare("INSERT INTO notes (username, filename, content) VALUES (?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE content = VALUES(content), last_modified = CURRENT_TIMESTAMP");
            $stmt->execute([$username, $filename, $content]);
            echo json_encode(['status' => 'success']);
            break;

        case 'delete':
            if (!authenticate($pdo, $username, $key)) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                break;
            }
            $filename = $_GET['filename'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM notes WHERE username = ? AND filename = ?");
            $stmt->execute([$username, $filename]);
            echo json_encode(['status' => 'success']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
