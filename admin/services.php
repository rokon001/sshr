<?php
/**
 * Admin - Optional Services Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireAuth();

$message = '';
$error = '';

// Handle delete
if (isset($_GET['delete']) && dbAvailable()) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = db()->prepare("DELETE FROM optional_services WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: services.php?success=deleted');
        exit;
    } catch (Exception $e) {
        $error = 'Greška pri brisanju.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && dbAvailable()) {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'name_hr' => trim($_POST['name_hr'] ?? ''),
        'name_en' => trim($_POST['name_en'] ?? ''),
        'description_hr' => trim($_POST['description_hr'] ?? ''),
        'description_en' => trim($_POST['description_en'] ?? ''),
        'price' => !empty($_POST['price']) ? (float)$_POST['price'] : null,
        'price_text_hr' => trim($_POST['price_text_hr'] ?? ''),
        'price_text_en' => trim($_POST['price_text_en'] ?? ''),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'active' => isset($_POST['active']) ? 1 : 0
    ];
    
    try {
        if ($id > 0) {
            $stmt = db()->prepare("UPDATE optional_services SET name_hr = ?, name_en = ?, description_hr = ?, description_en = ?, price = ?, price_text_hr = ?, price_text_en = ?, sort_order = ?, active = ? WHERE id = ?");
            $stmt->execute([$data['name_hr'], $data['name_en'], $data['description_hr'], $data['description_en'], $data['price'], $data['price_text_hr'], $data['price_text_en'], $data['sort_order'], $data['active'], $id]);
        } else {
            $stmt = db()->prepare("INSERT INTO optional_services (name_hr, name_en, description_hr, description_en, price, price_text_hr, price_text_en, sort_order, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['name_hr'], $data['name_en'], $data['description_hr'], $data['description_en'], $data['price'], $data['price_text_hr'], $data['price_text_en'], $data['sort_order'], $data['active']]);
        }
        header('Location: services.php?success=saved');
        exit;
    } catch (Exception $e) {
        $error = 'Greška: ' . $e->getMessage();
    }
}

$pageTitle = 'Dodatne usluge';
require_once 'includes/header.php';

// Handle success messages
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'saved') $message = 'Usluga uspješno spremljena!';
    if ($_GET['success'] === 'deleted') $message = 'Usluga obrisana.';
}

$services = [];
$editService = null;

if (dbAvailable()) {
    try {
        $stmt = db()->query("SELECT * FROM optional_services ORDER BY sort_order ASC");
        $services = $stmt->fetchAll();
        
        if (isset($_GET['edit'])) {
            $editId = (int)$_GET['edit'];
            $stmt = db()->prepare("SELECT * FROM optional_services WHERE id = ?");
            $stmt->execute([$editId]);
            $editService = $stmt->fetch();
        }
    } catch (Exception $e) {
        $error = 'Greška pri učitavanju.';
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
<div class="alert alert--error">Baza podataka nije povezana.</div>
<?php else: ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Form -->
    <div class="card">
        <div class="card__header">
            <h2 class="card__title"><?php echo $editService ? 'Uredi uslugu' : 'Nova usluga'; ?></h2>
            <?php if ($editService): ?>
            <a href="services.php" class="btn btn--secondary btn--sm">Odustani</a>
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <?php if ($editService): ?>
            <input type="hidden" name="id" value="<?php echo $editService['id']; ?>">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Naziv (HR) *</label>
                    <input type="text" class="form-control" name="name_hr" value="<?php echo htmlspecialchars($editService['name_hr'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Naziv (EN) *</label>
                    <input type="text" class="form-control" name="name_en" value="<?php echo htmlspecialchars($editService['name_en'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Opis (HR)</label>
                    <textarea class="form-control" name="description_hr" rows="3"><?php echo htmlspecialchars($editService['description_hr'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Opis (EN)</label>
                    <textarea class="form-control" name="description_en" rows="3"><?php echo htmlspecialchars($editService['description_en'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Cijena (€)</label>
                    <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $editService['price'] ?? ''; ?>" placeholder="Prazno = tekst">
                </div>
                <div class="form-group">
                    <label>Tekst cijene (HR)</label>
                    <input type="text" class="form-control" name="price_text_hr" value="<?php echo htmlspecialchars($editService['price_text_hr'] ?? ''); ?>" placeholder="npr. po dogovoru">
                </div>
                <div class="form-group">
                    <label>Tekst cijene (EN)</label>
                    <input type="text" class="form-control" name="price_text_en" value="<?php echo htmlspecialchars($editService['price_text_en'] ?? ''); ?>" placeholder="npr. by agreement">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 100px 1fr; gap: 12px; align-items: end;">
                <div class="form-group">
                    <label>Redoslijed</label>
                    <input type="number" class="form-control" name="sort_order" value="<?php echo $editService['sort_order'] ?? 0; ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="active" <?php echo (!$editService || $editService['active']) ? 'checked' : ''; ?>>
                        <span>Aktivna</span>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn btn--primary" style="margin-top: 16px;">
                <?php echo $editService ? 'Spremi promjene' : 'Dodaj uslugu'; ?>
            </button>
        </form>
    </div>
    
    <!-- List -->
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Sve usluge</h2>
        </div>
        
        <?php if (empty($services)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 20px;">Nema dodatnih usluga.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Naziv</th>
                            <th>Cijena</th>
                            <th>Status</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($service['name_hr']); ?></strong></td>
                            <td>
                                <?php if ($service['price']): ?>
                                    €<?php echo number_format($service['price'], 0); ?>/mj
                                <?php else: ?>
                                    <?php echo htmlspecialchars($service['price_text_hr'] ?: 'Po dogovoru'); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge--<?php echo $service['active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $service['active'] ? 'Aktivna' : 'Neaktivna'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="?edit=<?php echo $service['id']; ?>" class="btn btn--secondary btn--sm">Uredi</a>
                                    <a href="?delete=<?php echo $service['id']; ?>" class="btn btn--danger btn--sm" onclick="return confirm('Obrisati ovu uslugu?')">Obriši</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card__header">
        <h2 class="card__title">Napomena</h2>
    </div>
    <p style="color: var(--text-secondary); line-height: 1.6;">
        Dodatne usluge se prikazuju na svim paketima kada korisnik klikne "Dodatne mjesečne usluge".
        Ako usluga ima cijenu, prikazat će se kao "50€/mjesec". Ako nema cijenu, prikazat će se tekst cijene (npr. "po dogovoru").
    </p>
</div>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

