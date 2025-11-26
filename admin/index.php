<?php
/**
 * Admin Dashboard
 */
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

// Get stats
$totalMessages = 0;
$unreadMessages = 0;
$totalPackages = 4;

if (dbAvailable()) {
    try {
        $stmt = db()->query("SELECT COUNT(*) as total FROM contact_submissions");
        $totalMessages = $stmt->fetch()['total'] ?? 0;
        
        $stmt = db()->query("SELECT COUNT(*) as unread FROM contact_submissions WHERE is_read = 0");
        $unreadMessages = $stmt->fetch()['unread'] ?? 0;
        
        $stmt = db()->query("SELECT COUNT(*) as total FROM packages WHERE active = 1");
        $totalPackages = $stmt->fetch()['total'] ?? 4;
    } catch (Exception $e) {
        // Use defaults
    }
}
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
        </div>
        <div class="stat-card__content">
            <h3><?php echo $totalPackages; ?></h3>
            <p>Aktivnih paketa</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--success">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
        </div>
        <div class="stat-card__content">
            <h3><?php echo $totalMessages; ?></h3>
            <p>Ukupno poruka</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--warning">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="stat-card__content">
            <h3><?php echo $unreadMessages; ?></h3>
            <p>Nepročitanih poruka</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--danger">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="2" y1="12" x2="22" y2="12"></line>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
        </div>
        <div class="stat-card__content">
            <h3>2</h3>
            <p>Jezika</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Dobrodošli u Admin Panel</h2>
    </div>
    <p style="color: var(--text-secondary); line-height: 1.6;">
        Ovdje možete upravljati sadržajem vaše web stranice. Koristite navigaciju s lijeve strane za pristup različitim sekcijama:
    </p>
    <ul style="color: var(--text-secondary); margin-top: 16px; margin-left: 20px; line-height: 1.8;">
        <li><strong>Paketi</strong> - Uređivanje cijena, opisa i značajki paketa</li>
        <li><strong>Prijevodi</strong> - Upravljanje tekstovima na hrvatskom i engleskom</li>
        <li><strong>Poruke</strong> - Pregled poruka s kontakt forme</li>
        <li><strong>Postavke</strong> - Opće postavke stranice (email, telefon, itd.)</li>
    </ul>
</div>

<?php if (!dbAvailable()): ?>
<div class="alert alert--error">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
    <span>Baza podataka nije povezana. Molimo konfigurirajte <code>config/database.php</code> s vašim podacima za pristup bazi.</span>
</div>
<?php endif; ?>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Brzi pristup</h2>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="packages.php" class="btn btn--primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
            Uredi pakete
        </a>
        <a href="messages.php" class="btn btn--secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            Pregledaj poruke
        </a>
        <a href="settings.php" class="btn btn--secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
            Postavke
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

