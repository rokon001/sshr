<?php
/**
 * Admin - Translations Management
 */
$pageTitle = 'Prijevodi';
require_once 'includes/header.php';

$message = '';
$error = '';
$translations = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && dbAvailable()) {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            // Add new translation
            $key = sanitize($_POST['translation_key'] ?? '');
            $hr = sanitize($_POST['lang_hr'] ?? '');
            $en = sanitize($_POST['lang_en'] ?? '');
            $category = sanitize($_POST['category'] ?? 'general');
            
            if (!empty($key)) {
                $stmt = db()->prepare("INSERT INTO translations (translation_key, lang_hr, lang_en, category) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE lang_hr = ?, lang_en = ?, category = ?");
                $stmt->execute([$key, $hr, $en, $category, $hr, $en, $category]);
                $message = 'Prijevod uspješno dodan!';
            }
        } elseif (isset($_POST['translations'])) {
            // Update existing translations
            foreach ($_POST['translations'] as $id => $data) {
                $stmt = db()->prepare("UPDATE translations SET lang_hr = ?, lang_en = ? WHERE id = ?");
                $stmt->execute([$data['hr'], $data['en'], $id]);
            }
            $message = 'Prijevodi uspješno ažurirani!';
        }
    } catch (Exception $e) {
        $error = 'Greška: ' . $e->getMessage();
    }
}

// Handle delete
if (isset($_GET['delete']) && dbAvailable()) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = db()->prepare("DELETE FROM translations WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Prijevod obrisan.';
    } catch (Exception $e) {
        $error = 'Greška pri brisanju.';
    }
}

// Load translations from database
if (dbAvailable()) {
    try {
        $stmt = db()->query("SELECT * FROM translations ORDER BY category, translation_key");
        $translations = $stmt->fetchAll();
    } catch (Exception $e) {
        // Table might be empty
    }
}

// Group by category
$grouped = [];
foreach ($translations as $t) {
    $grouped[$t['category']][] = $t;
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
    <span>Baza podataka nije povezana. Prijevodi se ne mogu uređivati.</span>
</div>
<?php endif; ?>

<!-- Add New Translation -->
<div class="card">
    <div class="card__header">
        <h2 class="card__title">Dodaj novi prijevod</h2>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 150px auto; gap: 12px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Ključ</label>
                <input type="text" class="form-control" name="translation_key" placeholder="npr. hero-title" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Hrvatski</label>
                <input type="text" class="form-control" name="lang_hr" placeholder="Hrvatski tekst">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>English</label>
                <input type="text" class="form-control" name="lang_en" placeholder="English text">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Kategorija</label>
                <select class="form-control" name="category">
                    <option value="general">General</option>
                    <option value="nav">Navigation</option>
                    <option value="hero">Hero</option>
                    <option value="packages">Packages</option>
                    <option value="contact">Contact</option>
                    <option value="faq">FAQ</option>
                    <option value="footer">Footer</option>
                </select>
            </div>
            <button type="submit" class="btn btn--primary" <?php echo !dbAvailable() ? 'disabled' : ''; ?>>Dodaj</button>
        </div>
    </form>
</div>

<!-- Existing Translations -->
<?php if (!empty($grouped)): ?>
<form method="POST" action="">
    <?php foreach ($grouped as $category => $items): ?>
    <div class="card">
        <div class="card__header">
            <h2 class="card__title"><?php echo ucfirst(htmlspecialchars($category)); ?></h2>
            <span class="badge badge--secondary"><?php echo count($items); ?> prijevoda</span>
        </div>
        
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 200px;">Ključ</th>
                        <th>Hrvatski</th>
                        <th>English</th>
                        <th style="width: 80px;">Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $t): ?>
                    <tr>
                        <td><code style="font-size: 12px;"><?php echo htmlspecialchars($t['translation_key']); ?></code></td>
                        <td>
                            <input type="text" class="form-control" name="translations[<?php echo $t['id']; ?>][hr]" value="<?php echo htmlspecialchars($t['lang_hr']); ?>">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="translations[<?php echo $t['id']; ?>][en]" value="<?php echo htmlspecialchars($t['lang_en']); ?>">
                        </td>
                        <td>
                            <a href="?delete=<?php echo $t['id']; ?>" class="btn btn--danger btn--sm" onclick="return confirm('Obrisati ovaj prijevod?')">×</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
    
    <button type="submit" class="btn btn--primary" <?php echo !dbAvailable() ? 'disabled' : ''; ?>>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
            <polyline points="17 21 17 13 7 13 7 21"></polyline>
            <polyline points="7 3 7 8 15 8"></polyline>
        </svg>
        Spremi sve prijevode
    </button>
</form>
<?php else: ?>
<div class="card">
    <div class="empty-state">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="2" y1="12" x2="22" y2="12"></line>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
        </svg>
        <h3>Nema prijevoda u bazi</h3>
        <p>Dodajte prijevode koristeći formu iznad ili koristite prijevode iz script.js datoteke.</p>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Napomena</h2>
    </div>
    <p style="color: var(--text-secondary); line-height: 1.6;">
        Prijevodi u bazi podataka nadopunjuju prijevode iz <code>script.js</code> datoteke. 
        Ako želite koristiti prijevode iz baze na frontend-u, potrebno je implementirati API endpoint koji će dohvaćati prijevode.
        Za sada, glavni prijevodi se i dalje nalaze u <code>script.js</code>.
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
