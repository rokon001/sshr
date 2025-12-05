<?php
/**
 * Admin - Settings
 */
$pageTitle = 'Postavke';
require_once 'includes/header.php';

$message = '';
$error = '';

// Default settings
$settings = [
    'site_name' => 'Start Smart HR',
    'site_email' => 'info@startsmarthr.eu',
    'site_phone' => '+385 99 610 5673',
    'site_phone_2' => '+385 95 837 4220',
    'site_location' => 'Zagreb, Hrvatska',
    'turnstile_site_key' => '0x4AAAAAACAsbbl9JPV5qKN3',
    'turnstile_secret_key' => '0x4AAAAAACAsbam0KqzsMjxQ9thDQnn0e8U',
    'default_language' => 'hr'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!dbAvailable()) {
        $error = 'Baza podataka nije povezana. Postavke se ne mogu spremiti.';
    } else {
        try {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'setting_') === 0) {
                    $settingKey = substr($key, 8);
                    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$settingKey, $value, $value]);
                }
            }
            $message = 'Postavke uspješno spremljene!';
        } catch (Exception $e) {
            $error = 'Greška pri spremanju postavki: ' . $e->getMessage();
        }
    }
}

// Load settings from database
if (dbAvailable()) {
    try {
        $stmt = db()->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // Use defaults
    }
}
?>

<?php if ($message): ?>
    <div class="alert alert--success"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert--error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!dbAvailable()): ?>
<div class="alert alert--error">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
    <span>Baza podataka nije povezana. Postavke se prikazuju iz zadanih vrijednosti i ne mogu se spremiti.</span>
</div>
<?php endif; ?>

<form method="POST" action="">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Opće postavke</h2>
        </div>
        
        <div class="form-group">
            <label for="site_name">Naziv stranice</label>
            <input type="text" class="form-control" id="site_name" name="setting_site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>">
        </div>
        
        <div class="form-group">
            <label for="site_email">Email adresa</label>
            <input type="email" class="form-control" id="site_email" name="setting_site_email" value="<?php echo htmlspecialchars($settings['site_email']); ?>">
        </div>
        
        <div class="form-group">
            <label for="site_phone">Telefon 1</label>
            <input type="text" class="form-control" id="site_phone" name="setting_site_phone" value="<?php echo htmlspecialchars($settings['site_phone']); ?>">
        </div>
        
        <div class="form-group">
            <label for="site_phone_2">Telefon 2</label>
            <input type="text" class="form-control" id="site_phone_2" name="setting_site_phone_2" value="<?php echo htmlspecialchars($settings['site_phone_2'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="site_location">Lokacija</label>
            <input type="text" class="form-control" id="site_location" name="setting_site_location" value="<?php echo htmlspecialchars($settings['site_location']); ?>">
        </div>
        
        <div class="form-group">
            <label for="default_language">Zadani jezik</label>
            <select class="form-control" id="default_language" name="setting_default_language">
                <option value="hr" <?php echo $settings['default_language'] === 'hr' ? 'selected' : ''; ?>>Hrvatski</option>
                <option value="en" <?php echo $settings['default_language'] === 'en' ? 'selected' : ''; ?>>English</option>
            </select>
        </div>
    </div>
    
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Cloudflare Turnstile</h2>
        </div>
        
        <div class="form-group">
            <label for="turnstile_site_key">Site Key</label>
            <input type="text" class="form-control" id="turnstile_site_key" name="setting_turnstile_site_key" value="<?php echo htmlspecialchars($settings['turnstile_site_key']); ?>">
        </div>
        
        <div class="form-group">
            <label for="turnstile_secret_key">Secret Key</label>
            <input type="text" class="form-control" id="turnstile_secret_key" name="setting_turnstile_secret_key" value="<?php echo htmlspecialchars($settings['turnstile_secret_key']); ?>">
        </div>
        
        <p style="color: var(--text-secondary); font-size: 14px;">
            Turnstile ključevi se koriste za zaštitu kontakt forme od spam botova.
            Možete ih dobiti na <a href="https://dash.cloudflare.com/" target="_blank" style="color: var(--primary);">Cloudflare Dashboard</a>.
        </p>
    </div>
    
    <div style="display: flex; gap: 12px;">
        <button type="submit" class="btn btn--primary" <?php echo !dbAvailable() ? 'disabled' : ''; ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Spremi postavke
        </button>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>


