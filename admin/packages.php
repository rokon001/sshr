<?php
/**
 * Admin - Packages Management
 */
$pageTitle = 'Paketi';
require_once 'includes/header.php';

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // For now, show message that database is needed
    if (!dbAvailable()) {
        $error = 'Baza podataka nije povezana. Molimo konfigurirajte database.php';
    }
}

// Default packages (used when database is not available)
$packages = [
    [
        'id' => 1,
        'slug' => 'basic',
        'title_hr' => 'Osnovna Stranica',
        'title_en' => 'Basic Website',
        'price' => 300,
        'original_price' => 600,
        'badge_hr' => 'Osnovni',
        'is_featured' => 0,
        'active' => 1
    ],
    [
        'id' => 2,
        'slug' => 'professional',
        'title_hr' => 'Profesionalna Stranica',
        'title_en' => 'Professional Website',
        'price' => 500,
        'original_price' => 1000,
        'badge_hr' => 'Preporučeno',
        'is_featured' => 1,
        'active' => 1
    ],
    [
        'id' => 3,
        'slug' => 'premium',
        'title_hr' => 'Premium Stranica',
        'title_en' => 'Premium Website',
        'price' => 1000,
        'original_price' => 2000,
        'badge_hr' => 'Premium',
        'is_featured' => 0,
        'active' => 1
    ],
    [
        'id' => 4,
        'slug' => 'custom',
        'title_hr' => 'Custom Projekt',
        'title_en' => 'Custom Project',
        'price' => null,
        'original_price' => null,
        'badge_hr' => 'Custom',
        'is_featured' => 0,
        'active' => 1
    ]
];

// Try to get packages from database
if (dbAvailable()) {
    try {
        $stmt = db()->query("SELECT * FROM packages ORDER BY sort_order ASC");
        $dbPackages = $stmt->fetchAll();
        if (!empty($dbPackages)) {
            $packages = $dbPackages;
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
    <span>Baza podataka nije povezana. Prikazuju se zadane vrijednosti. Za uređivanje, konfigurirajte bazu podataka.</span>
</div>
<?php endif; ?>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Paketi</h2>
        <?php if (dbAvailable()): ?>
        <a href="package-edit.php" class="btn btn--primary btn--sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Dodaj paket
        </a>
        <?php endif; ?>
    </div>
    
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Naziv (HR)</th>
                    <th>Naziv (EN)</th>
                    <th>Cijena</th>
                    <th>Originalna cijena</th>
                    <th>Badge</th>
                    <th>Status</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($package['title_hr']); ?></strong></td>
                    <td><?php echo htmlspecialchars($package['title_en']); ?></td>
                    <td>
                        <?php if ($package['price']): ?>
                            €<?php echo number_format($package['price'], 0); ?>
                        <?php else: ?>
                            <span style="color: var(--text-secondary);">Po dogovoru</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($package['original_price']): ?>
                            €<?php echo number_format($package['original_price'], 0); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge--<?php echo $package['is_featured'] ? 'success' : 'warning'; ?>">
                            <?php echo htmlspecialchars($package['badge_hr']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge--<?php echo $package['active'] ? 'success' : 'danger'; ?>">
                            <?php echo $package['active'] ? 'Aktivan' : 'Neaktivan'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <?php if (dbAvailable()): ?>
                            <a href="package-edit.php?id=<?php echo $package['id']; ?>" class="btn btn--secondary btn--sm">Uredi</a>
                            <?php else: ?>
                            <button class="btn btn--secondary btn--sm" disabled title="Potrebna baza podataka">Uredi</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Napomena</h2>
    </div>
    <p style="color: var(--text-secondary); line-height: 1.6;">
        Za potpunu funkcionalnost uređivanja paketa, potrebno je povezati bazu podataka. 
        Pokrenite SQL skriptu iz <code>install/schema.sql</code> na vašem hosting računu 
        i ažurirajte <code>config/database.php</code> s vašim podacima.
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>

