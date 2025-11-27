<?php
/**
 * Admin - Live Chat Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireAuth();

// Handle send message BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['conversation_id'])) {
    $convId = (int)$_POST['conversation_id'];
    $message = trim($_POST['message']);
    
    if ($convId && $message && dbAvailable()) {
        try {
            $stmt = db()->prepare("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'admin', ?)");
            $stmt->execute([$convId, $message]);
            
            $stmt = db()->prepare("UPDATE chat_conversations SET updated_at = NOW() WHERE id = ?");
            $stmt->execute([$convId]);
            
            header("Location: chat.php?id=$convId");
            exit;
        } catch (Exception $e) {
            // Error
        }
    }
}

// Handle close conversation
if (isset($_GET['close'])) {
    $convId = (int)$_GET['close'];
    if ($convId && dbAvailable()) {
        try {
            $stmt = db()->prepare("UPDATE chat_conversations SET status = 'closed' WHERE id = ?");
            $stmt->execute([$convId]);
            header("Location: chat.php");
            exit;
        } catch (Exception $e) {
            // Error
        }
    }
}

$pageTitle = 'Live Chat';
require_once 'includes/header.php';

$activeConversations = [];
$closedConversations = [];
$activeConversation = null;
$messages = [];

if (dbAvailable()) {
    // Get active conversations
    try {
        $stmt = db()->query("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = c.id AND sender_type = 'visitor' AND is_read = 0) as unread_count,
                   (SELECT message FROM chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_message,
                   (SELECT created_at FROM chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_message_time
            FROM chat_conversations c 
            WHERE c.status = 'active'
            ORDER BY c.updated_at DESC
        ");
        $activeConversations = $stmt->fetchAll();
        
        // Get closed conversations
        $stmt = db()->query("
            SELECT c.*, 
                   (SELECT message FROM chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_message,
                   (SELECT created_at FROM chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_message_time
            FROM chat_conversations c 
            WHERE c.status = 'closed'
            ORDER BY c.updated_at DESC
            LIMIT 20
        ");
        $closedConversations = $stmt->fetchAll();
    } catch (Exception $e) {
        // Table might not exist
    }
    
    // Get selected conversation
    if (isset($_GET['id'])) {
        $convId = (int)$_GET['id'];
        
        try {
            $stmt = db()->prepare("SELECT * FROM chat_conversations WHERE id = ?");
            $stmt->execute([$convId]);
            $activeConversation = $stmt->fetch();
            
            if ($activeConversation) {
                // Mark messages as read
                $stmt = db()->prepare("UPDATE chat_messages SET is_read = 1 WHERE conversation_id = ? AND sender_type = 'visitor'");
                $stmt->execute([$convId]);
                
                // Get messages
                $stmt = db()->prepare("SELECT * FROM chat_messages WHERE conversation_id = ? ORDER BY id ASC");
                $stmt->execute([$convId]);
                $messages = $stmt->fetchAll();
            }
        } catch (Exception $e) {
            // Error
        }
    }
}

?>

<style>
.chat-container {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 20px;
    height: calc(100vh - 180px);
    min-height: 500px;
}

.chat-sidebar {
    background: var(--bg-card);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.chat-sidebar-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chat-sidebar-header .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
}

.chat-sidebar-header.closed .status-dot {
    background: #9ca3af;
}

.chat-list {
    flex: 1;
    overflow-y: auto;
}

.chat-section-label {
    padding: 12px 20px 8px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    background: var(--bg-secondary);
}

.chat-item {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: background 0.2s;
    text-decoration: none;
    display: block;
    color: inherit;
}

.chat-item:hover {
    background: var(--bg-hover);
}

.chat-item.active {
    background: rgba(99, 102, 241, 0.1);
    border-left: 3px solid var(--primary);
}

.chat-item.closed {
    opacity: 0.6;
    background: var(--bg-secondary);
}

.chat-item.closed:hover {
    opacity: 0.8;
}

.chat-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.chat-item-name {
    font-weight: 500;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.chat-item-name .online-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
}

.chat-item-time {
    font-size: 12px;
    color: var(--text-muted);
}

.chat-item-preview {
    font-size: 13px;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-item-unread {
    background: var(--primary);
    color: white;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
}

.chat-item-badge {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    background: #9ca3af;
    color: white;
}

.chat-main {
    background: var(--bg-card);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-main-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-main-info h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
}

.chat-main-info span {
    font-size: 13px;
    color: var(--text-secondary);
}

.chat-main-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: var(--bg-secondary);
}

.chat-msg {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.5;
}

.chat-msg--visitor {
    align-self: flex-start;
    background: #e5e7eb;
    color: #1f2937;
    border: none;
}

.chat-msg--admin {
    align-self: flex-end;
    background: var(--primary);
    color: white;
}

.chat-msg--system {
    align-self: center;
    background: #fef3c7;
    color: #92400e;
    font-size: 13px;
    padding: 8px 16px;
}

.chat-msg-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 4px;
}

.chat-main-form {
    padding: 16px 20px;
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 12px;
}

.chat-main-form input {
    flex: 1;
}

.chat-main-form.disabled {
    opacity: 0.5;
    pointer-events: none;
}

.chat-closed-notice {
    padding: 16px 20px;
    background: #f3f4f6;
    border-top: 1px solid var(--border-color);
    text-align: center;
    color: #6b7280;
    font-size: 14px;
}

.chat-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: var(--text-muted);
    text-align: center;
    padding: 40px;
}

.chat-empty svg {
    margin-bottom: 16px;
    opacity: 0.5;
}

/* Custom Modal */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-box {
    background: var(--bg-card);
    border-radius: 16px;
    padding: 32px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-icon {
    width: 64px;
    height: 64px;
    background: #fef2f2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: #ef4444;
}

.modal-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 12px;
    color: var(--text-primary);
}

.modal-text {
    color: var(--text-secondary);
    margin-bottom: 24px;
    line-height: 1.6;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

@media (max-width: 900px) {
    .chat-container {
        grid-template-columns: 1fr;
    }
    
    .chat-sidebar {
        max-height: 300px;
    }
}
</style>

<!-- Close Confirmation Modal -->
<div class="modal-overlay" id="closeModal">
    <div class="modal-box">
        <div class="modal-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <h3 class="modal-title">Zatvoriti razgovor?</h3>
        <p class="modal-text">Korisnik će vidjeti poruku da je razgovor zatvoren i neće moći nastaviti pisati. Ova akcija se ne može poništiti.</p>
        <div class="modal-actions">
            <button class="btn btn--secondary" onclick="closeModal()">Odustani</button>
            <a href="#" class="btn btn--danger" id="confirmCloseBtn">Zatvori razgovor</a>
        </div>
    </div>
</div>

<?php if (!dbAvailable()): ?>
<div class="alert alert--error">Baza podataka nije povezana.</div>
<?php else: ?>

<div class="chat-container">
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <span class="status-dot"></span>
            Razgovori
        </div>
        <div class="chat-list">
            <?php if (!empty($activeConversations)): ?>
                <div class="chat-section-label">Aktivni (<?php echo count($activeConversations); ?>)</div>
                <?php foreach ($activeConversations as $conv): ?>
                <a href="?id=<?php echo $conv['id']; ?>" class="chat-item <?php echo ($activeConversation && $activeConversation['id'] == $conv['id']) ? 'active' : ''; ?>">
                    <div class="chat-item-header">
                        <span class="chat-item-name">
                            <span class="online-dot"></span>
                            <?php echo htmlspecialchars($conv['visitor_name'] ?: 'Posjetitelj #' . $conv['id']); ?>
                        </span>
                        <?php if ($conv['unread_count'] > 0): ?>
                            <span class="chat-item-unread"><?php echo $conv['unread_count']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="chat-item-preview">
                        <?php echo htmlspecialchars($conv['last_message'] ?? 'Novi razgovor'); ?>
                    </div>
                    <div class="chat-item-time">
                        <?php echo $conv['last_message_time'] ? date('d.m. H:i', strtotime($conv['last_message_time'])) : date('d.m. H:i', strtotime($conv['created_at'])); ?>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (!empty($closedConversations)): ?>
                <div class="chat-section-label">Zatvoreni</div>
                <?php foreach ($closedConversations as $conv): ?>
                <a href="?id=<?php echo $conv['id']; ?>" class="chat-item closed <?php echo ($activeConversation && $activeConversation['id'] == $conv['id']) ? 'active' : ''; ?>">
                    <div class="chat-item-header">
                        <span class="chat-item-name">
                            <?php echo htmlspecialchars($conv['visitor_name'] ?: 'Posjetitelj #' . $conv['id']); ?>
                        </span>
                        <span class="chat-item-badge">Zatvoreno</span>
                    </div>
                    <div class="chat-item-preview">
                        <?php echo htmlspecialchars($conv['last_message'] ?? ''); ?>
                    </div>
                    <div class="chat-item-time">
                        <?php echo $conv['last_message_time'] ? date('d.m. H:i', strtotime($conv['last_message_time'])) : ''; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (empty($activeConversations) && empty($closedConversations)): ?>
                <div style="padding: 20px; text-align: center; color: var(--text-muted);">
                    Nema razgovora
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="chat-main">
        <?php if ($activeConversation): ?>
            <div class="chat-main-header">
                <div class="chat-main-info">
                    <h3><?php echo htmlspecialchars($activeConversation['visitor_name'] ?: 'Posjetitelj #' . $activeConversation['id']); ?></h3>
                    <span>
                        <?php if ($activeConversation['visitor_email']): ?>
                            <?php echo htmlspecialchars($activeConversation['visitor_email']); ?> • 
                        <?php endif; ?>
                        Započeto: <?php echo date('d.m.Y H:i', strtotime($activeConversation['created_at'])); ?>
                        <?php if ($activeConversation['status'] === 'closed'): ?>
                            • <strong style="color: #ef4444;">Zatvoreno</strong>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($activeConversation['status'] === 'active'): ?>
                <button class="btn btn--danger btn--sm" onclick="showCloseModal(<?php echo $activeConversation['id']; ?>)">Zatvori razgovor</button>
                <?php endif; ?>
            </div>
            
            <div class="chat-main-messages" id="chat-messages">
                <?php foreach ($messages as $msg): ?>
                <div class="chat-msg chat-msg--<?php echo $msg['sender_type']; ?>">
                    <?php echo htmlspecialchars($msg['message']); ?>
                    <div class="chat-msg-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($activeConversation['status'] === 'active'): ?>
            <form method="POST" class="chat-main-form">
                <input type="hidden" name="conversation_id" value="<?php echo $activeConversation['id']; ?>">
                <input type="text" name="message" class="form-control" placeholder="Napišite odgovor..." autocomplete="off" required autofocus>
                <button type="submit" class="btn btn--primary">Pošalji</button>
            </form>
            
            <script>
                // Auto-scroll to bottom
                const msgContainer = document.getElementById('chat-messages');
                msgContainer.scrollTop = msgContainer.scrollHeight;
                
                // Poll for new messages without page refresh
                const conversationId = <?php echo $activeConversation['id']; ?>;
                let lastMsgId = <?php echo !empty($messages) ? end($messages)['id'] : 0; ?>;
                
                async function checkNewMessages() {
                    try {
                        const response = await fetch('../chat-api.php?action=get&conversation_id=' + conversationId + '&last_id=' + lastMsgId + '&mark_read=admin');
                        const data = await response.json();
                        
                        if (data.success && data.messages.length > 0) {
                            data.messages.forEach(msg => {
                                if (msg.sender_type === 'visitor') {
                                    const div = document.createElement('div');
                                    div.className = 'chat-msg chat-msg--visitor';
                                    div.innerHTML = escapeHtml(msg.message) + '<div class="chat-msg-time">' + new Date(msg.created_at).toLocaleTimeString('hr-HR', {hour: '2-digit', minute: '2-digit'}) + '</div>';
                                    msgContainer.appendChild(div);
                                    msgContainer.scrollTop = msgContainer.scrollHeight;
                                }
                                lastMsgId = Math.max(lastMsgId, parseInt(msg.id));
                            });
                        }
                    } catch (e) {
                        console.error('Poll error:', e);
                    }
                }
                
                function escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }
                
                setInterval(checkNewMessages, 3000);
            </script>
            <?php else: ?>
            <div class="chat-closed-notice">
                Ovaj razgovor je zatvoren i ne možete slati nove poruke.
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="chat-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>Odaberite razgovor</h3>
                <p>Odaberite razgovor s lijeve strane za pregled i odgovaranje.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showCloseModal(convId) {
    document.getElementById('closeModal').classList.add('active');
    document.getElementById('confirmCloseBtn').href = '?close=' + convId;
}

function closeModal() {
    document.getElementById('closeModal').classList.remove('active');
}

// Close modal on overlay click
document.getElementById('closeModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
