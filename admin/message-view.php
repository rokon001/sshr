<?php
/**
 * Admin - View Single Message
 */
$pageTitle = 'Pregled poruke';
require_once 'includes/header.php';

$msg = null;
$error = '';

if (!dbAvailable()) {
    $error = 'Baza podataka nije povezana.';
} else {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id > 0) {
        try {
            // Mark as read
            $stmt = db()->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
            
            // Get message
            $stmt = db()->prepare("SELECT * FROM contact_submissions WHERE id = ?");
            $stmt->execute([$id]);
            $msg = $stmt->fetch();
            
            if (!$msg) {
                $error = 'Poruka nije pronađena.';
            }
        } catch (Exception $e) {
            $error = 'Greška pri učitavanju poruke.';
        }
    } else {
        $error = 'Nevažeći ID poruke.';
    }
}
?>

<?php if ($error): ?>
    <div class="alert alert--error"><?php echo $error; ?></div>
    <a href="messages.php" class="btn btn--secondary">← Natrag na poruke</a>
<?php elseif ($msg): ?>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Poruka od <?php echo htmlspecialchars($msg['name']); ?></h2>
        <a href="messages.php" class="btn btn--secondary btn--sm">← Natrag</a>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div>
            <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Ime</label>
            <p style="color: var(--text-primary); font-size: 16px; margin-top: 4px;"><?php echo htmlspecialchars($msg['name']); ?></p>
        </div>
        
        <div>
            <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Email</label>
            <p style="margin-top: 4px;">
                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color: var(--primary); font-size: 16px;">
                    <?php echo htmlspecialchars($msg['email']); ?>
                </a>
            </p>
        </div>
        
        <div>
            <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Telefon</label>
            <p style="color: var(--text-primary); font-size: 16px; margin-top: 4px;">
                <?php if ($msg['phone']): ?>
                    <a href="tel:<?php echo htmlspecialchars($msg['phone']); ?>" style="color: var(--primary);">
                        <?php echo htmlspecialchars($msg['phone']); ?>
                    </a>
                <?php else: ?>
                    <span style="color: var(--text-muted);">-</span>
                <?php endif; ?>
            </p>
        </div>
        
        <div>
            <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Datum</label>
            <p style="color: var(--text-primary); font-size: 16px; margin-top: 4px;">
                <?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?>
            </p>
        </div>
    </div>
    
    <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
        <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 8px;">Poruka</label>
        <div style="background: var(--bg-input); padding: 20px; border-radius: 8px; color: var(--text-primary); line-height: 1.7; white-space: pre-wrap;"><?php echo htmlspecialchars($msg['message']); ?></div>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Tehnički detalji</h2>
    </div>
    
    <div style="display: grid; gap: 12px; font-size: 14px;">
        <div>
            <label style="color: var(--text-muted);">IP adresa:</label>
            <span style="color: var(--text-secondary); margin-left: 8px;"><?php echo htmlspecialchars($msg['ip_address'] ?? '-'); ?></span>
        </div>
        <div>
            <label style="color: var(--text-muted);">User Agent:</label>
            <span style="color: var(--text-secondary); margin-left: 8px; word-break: break-all;"><?php echo htmlspecialchars($msg['user_agent'] ?? '-'); ?></span>
        </div>
    </div>
</div>

<div style="display: flex; gap: 12px;">
    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: Poruka s web stranice" class="btn btn--primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
        </svg>
        Odgovori na email
    </a>
    <a href="messages.php?delete=<?php echo $msg['id']; ?>" class="btn btn--danger" onclick="return confirm('Jeste li sigurni da želite obrisati ovu poruku?')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </svg>
        Obriši poruku
    </a>
</div>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

