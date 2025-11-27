<?php
/**
 * Chat API Endpoint
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config/database.php';

if (!dbAvailable()) {
    echo json_encode(['success' => false, 'error' => 'Database not available']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'start':
        // Start or get existing conversation
        $visitorId = $_POST['visitor_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($visitorId)) {
            echo json_encode(['success' => false, 'error' => 'Visitor ID required']);
            exit;
        }
        
        // Check for existing active conversation
        $stmt = db()->prepare("SELECT * FROM chat_conversations WHERE visitor_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$visitorId]);
        $conversation = $stmt->fetch();
        
        if (!$conversation) {
            // Create new conversation
            $stmt = db()->prepare("INSERT INTO chat_conversations (visitor_id, visitor_name, visitor_email) VALUES (?, ?, ?)");
            $stmt->execute([$visitorId, $name ?: null, $email ?: null]);
            $conversationId = db()->lastInsertId();
            
            // Add welcome message
            $stmt = db()->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'admin', ?)");
            $stmt->execute([$conversationId, 'Pozdrav! Kako vam možemo pomoći?']);
        } else {
            $conversationId = $conversation['id'];
            // Update name/email if provided
            if ($name || $email) {
                $stmt = db()->prepare("UPDATE chat_conversations SET visitor_name = COALESCE(?, visitor_name), visitor_email = COALESCE(?, visitor_email) WHERE id = ?");
                $stmt->execute([$name ?: null, $email ?: null, $conversationId]);
            }
        }
        
        echo json_encode(['success' => true, 'conversation_id' => $conversationId]);
        break;
        
    case 'send':
        // Send a message
        $conversationId = (int)($_POST['conversation_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $senderType = $_POST['sender_type'] ?? 'visitor';
        
        if (!$conversationId || empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Missing data']);
            exit;
        }
        
        $stmt = db()->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, ?, ?)");
        $stmt->execute([$conversationId, $senderType, $message]);
        
        // Update conversation timestamp
        $stmt = db()->prepare("UPDATE chat_conversations SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$conversationId]);
        
        echo json_encode(['success' => true, 'message_id' => db()->lastInsertId()]);
        break;
        
    case 'get':
        // Get messages for a conversation
        $conversationId = (int)($_GET['conversation_id'] ?? 0);
        $lastId = (int)($_GET['last_id'] ?? 0);
        
        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Conversation ID required']);
            exit;
        }
        
        $sql = "SELECT * FROM chat_messages WHERE conversation_id = ?";
        $params = [$conversationId];
        
        if ($lastId > 0) {
            $sql .= " AND id > ?";
            $params[] = $lastId;
        }
        
        $sql .= " ORDER BY id ASC";
        
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $messages = $stmt->fetchAll();
        
        // Mark visitor messages as read if admin is requesting
        if (isset($_GET['mark_read']) && $_GET['mark_read'] === 'admin') {
            $stmt = db()->prepare("UPDATE chat_messages SET is_read = 1 WHERE conversation_id = ? AND sender_type = 'visitor'");
            $stmt->execute([$conversationId]);
        }
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        break;
        
    case 'list':
        // List all conversations (for admin)
        $status = $_GET['status'] ?? 'active';
        
        $stmt = db()->prepare("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = c.id AND sender_type = 'visitor' AND is_read = 0) as unread_count,
                   (SELECT message FROM chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_message
            FROM chat_conversations c 
            WHERE c.status = ?
            ORDER BY c.updated_at DESC
        ");
        $stmt->execute([$status]);
        $conversations = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'conversations' => $conversations]);
        break;
        
    case 'close':
        // Close a conversation
        $conversationId = (int)($_POST['conversation_id'] ?? 0);
        
        if ($conversationId) {
            $stmt = db()->prepare("UPDATE chat_conversations SET status = 'closed' WHERE id = ?");
            $stmt->execute([$conversationId]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid conversation']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

