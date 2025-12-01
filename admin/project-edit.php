<?php
/**
 * Admin - Project Edit
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

// Handle file deletion
if (isset($_GET['delete_file']) && dbAvailable()) {
    $fileId = (int)$_GET['delete_file'];
    try {
        $stmt = db()->prepare("SELECT file_path FROM project_files WHERE id = ? AND project_id = ?");
        $stmt->execute([$fileId, $id]);
        $file = $stmt->fetch();
        
        if ($file) {
            $filePath = __DIR__ . '/../' . $file['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $stmt = db()->prepare("DELETE FROM project_files WHERE id = ?");
            $stmt->execute([$fileId]);
            header('Location: project-edit.php?id=' . $id . '&success=file_deleted');
            exit;
        }
    } catch (Exception $e) {
        $error = 'Greška pri brisanju datoteke: ' . $e->getMessage();
    }
}

// Handle file uploads (separate from main form)
if (isset($_POST['upload_files']) && $id > 0 && dbAvailable()) {
    try {
        // Check if table exists by trying to query it
        try {
            $testStmt = db()->query("SELECT 1 FROM project_files LIMIT 1");
        } catch (Exception $e) {
            throw new Exception('Tablica project_files ne postoji. Molimo prvo pokrenite SQL skriptu za kreiranje tablice.');
        }
        
        $uploadDir = __DIR__ . '/../uploads/projects/' . $id . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (isset($_FILES['project_files'])) {
            foreach ($_FILES['project_files']['name'] as $key => $fileName) {
                if ($_FILES['project_files']['error'][$key] === UPLOAD_ERR_OK && !empty($fileName)) {
                    $fileType = sanitize($_POST['file_types'][$key] ?? 'document');
                    $tmpName = $_FILES['project_files']['tmp_name'][$key];
                    $fileSize = $_FILES['project_files']['size'][$key];
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Allowed file types
                    $allowed = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'];
                    
                    if (in_array($ext, $allowed)) {
                        $safeFileName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
                        $filePath = $uploadDir . $safeFileName;
                        
                        if (move_uploaded_file($tmpName, $filePath)) {
                            $relativePath = 'uploads/projects/' . $id . '/' . $safeFileName;
                            $stmt = db()->prepare("INSERT INTO project_files (project_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$id, $fileName, $relativePath, $fileType, $fileSize]);
                        }
                    }
                }
            }
            header('Location: project-edit.php?id=' . $id . '&success=files_uploaded');
            exit;
        }
    } catch (Exception $e) {
        $error = 'Greška pri uploadu datoteka: ' . $e->getMessage();
    }
}

// Handle form submission (exclude file uploads which are handled separately)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['upload_files']) && dbAvailable()) {
    try {
        $name = sanitize(trim($_POST['name'] ?? ''));
        $clientName = sanitize(trim($_POST['client_name'] ?? ''));
        $clientEmail = sanitize(trim($_POST['client_email'] ?? ''));
        $packageType = sanitize($_POST['package_type'] ?? 'basic');
        $agreementDate = $_POST['agreement_date'] ?? '';
        $deadline = $_POST['deadline'] ?? '';
        $status = sanitize($_POST['status'] ?? 'current');
        $currentPhase = sanitize($_POST['current_phase'] ?? 'agreement');
        $notes = sanitize(trim($_POST['notes'] ?? ''));
        
        // Validate required fields
        if (empty($name) || empty($agreementDate) || empty($deadline)) {
            throw new Exception('Ime projekta, datum sporazuma i rok su obavezni.');
        }
        
        // Handle agreement status and meeting
        $hasAgreement = isset($_POST['has_agreement']) ? 1 : 0;
        $meetingDate = !empty($_POST['meeting_date']) ? $_POST['meeting_date'] : null;
        
        // Insert or update project
        if ($id > 0) {
            $stmt = db()->prepare("UPDATE projects SET name = ?, client_name = ?, client_email = ?, package_type = ?, agreement_date = ?, deadline = ?, status = ?, current_phase = ?, notes = ?, has_agreement = ?, meeting_date = ? WHERE id = ?");
            $stmt->execute([$name, $clientName, $clientEmail, $packageType, $agreementDate, $deadline, $status, $currentPhase, $notes, $hasAgreement, $meetingDate, $id]);
        } else {
            $stmt = db()->prepare("INSERT INTO projects (name, client_name, client_email, package_type, agreement_date, deadline, status, current_phase, notes, has_agreement, meeting_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $clientName, $clientEmail, $packageType, $agreementDate, $deadline, $status, $currentPhase, $notes, $hasAgreement, $meetingDate]);
            $id = db()->lastInsertId();
        }
        
        // Handle phases
        $phases = ['agreement', 'planning', 'design', 'development', 'content', 'testing', 'final'];
        
        // Delete existing phases
        $stmt = db()->prepare("DELETE FROM project_phases WHERE project_id = ?");
        $stmt->execute([$id]);
        
        // Handle meeting scheduling
        if (!empty($meetingDate)) {
            // Delete existing meetings for this project
            $stmt = db()->prepare("DELETE FROM project_meetings WHERE project_id = ?");
            $stmt->execute([$id]);
            
            // Insert new meeting
            $stmt = db()->prepare("INSERT INTO project_meetings (project_id, meeting_date, meeting_type, notes) VALUES (?, ?, 'agreement', ?)");
            $meetingNotes = sanitize(trim($_POST['meeting_notes'] ?? ''));
            $stmt->execute([$id, $meetingDate, $meetingNotes]);
        }
        
        // Insert phases with durations
        $startDate = new DateTime($agreementDate);
        foreach ($phases as $index => $phaseName) {
            $durationDays = (int)($_POST['phase_duration_' . $phaseName] ?? 0);
            
            if ($durationDays > 0 || $phaseName === 'agreement') {
                $phaseStartDate = clone $startDate;
                if ($index > 0) {
                    // Calculate start date based on previous phases
                    $prevPhases = array_slice($phases, 0, $index);
                    foreach ($prevPhases as $prevPhase) {
                        $prevDuration = (int)($_POST['phase_duration_' . $prevPhase] ?? 0);
                        if ($prevDuration > 0) {
                            $phaseStartDate->modify('+' . $prevDuration . ' days');
                        }
                    }
                }
                
                $phaseEndDate = clone $phaseStartDate;
                $phaseEndDate->modify('+' . $durationDays . ' days');
                
                $completed = isset($_POST['phase_completed_' . $phaseName]) ? 1 : 0;
                $completedAt = $completed ? date('Y-m-d H:i:s') : null;
                
                $stmt = db()->prepare("INSERT INTO project_phases (project_id, phase_name, duration_days, start_date, end_date, completed, completed_at, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $phaseName, $durationDays, $phaseStartDate->format('Y-m-d'), $phaseEndDate->format('Y-m-d'), $completed, $completedAt, $index]);
            }
        }
        
        // Handle checklist items
        $stmt = db()->prepare("DELETE FROM project_checklist WHERE project_id = ?");
        $stmt->execute([$id]);
        
        if (isset($_POST['checklist']) && is_array($_POST['checklist'])) {
            foreach ($_POST['checklist'] as $phaseName => $items) {
                if (is_array($items)) {
                    foreach ($items as $index => $task) {
                        $task = trim($task);
                        if (!empty($task)) {
                            $task = sanitize($task);
                            $completed = isset($_POST['checklist_completed'][$phaseName][$index]) ? 1 : 0;
                            $completedAt = $completed ? date('Y-m-d H:i:s') : null;
                            
                            $stmt = db()->prepare("INSERT INTO project_checklist (project_id, phase_name, task, completed, completed_at, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$id, $phaseName, $task, $completed, $completedAt, $index]);
                        }
                    }
                }
            }
        }
        
        // Note: File uploads are handled separately via upload_files form
        
        // Debug: Log before redirect
        error_log('Project saved successfully. ID: ' . $id . ', Redirecting...');
        
        header('Location: projects.php?success=' . ($id > 0 ? 'updated' : 'created'));
        exit;
        
    } catch (Exception $e) {
        $error = 'Greška: ' . $e->getMessage();
    }
}

$pageTitle = $id > 0 ? 'Uredi Projekt' : 'Novi Projekt';
require_once 'includes/header.php';

$project = null;
$phases = [];
$checklist = [];
$projectFiles = [];

// Load project data
if ($id > 0 && dbAvailable()) {
    try {
        $stmt = db()->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        
        if ($project) {
            // Load phases
            $stmt = db()->prepare("SELECT * FROM project_phases WHERE project_id = ? ORDER BY sort_order");
            $stmt->execute([$id]);
            $phasesData = $stmt->fetchAll();
            foreach ($phasesData as $phase) {
                $phases[$phase['phase_name']] = $phase;
            }
            
            // Load checklist
            $stmt = db()->prepare("SELECT * FROM project_checklist WHERE project_id = ? ORDER BY phase_name, sort_order");
            $stmt->execute([$id]);
            $checklistData = $stmt->fetchAll();
            foreach ($checklistData as $item) {
                if (!isset($checklist[$item['phase_name']])) {
                    $checklist[$item['phase_name']] = [];
                }
                $checklist[$item['phase_name']][] = $item;
            }
            
            // Load meeting
            $stmt = db()->prepare("SELECT * FROM project_meetings WHERE project_id = ? ORDER BY meeting_date DESC LIMIT 1");
            $stmt->execute([$id]);
            $meeting = $stmt->fetch();
            if ($meeting) {
                $project['meeting_date'] = $meeting['meeting_date'];
                $project['meeting_notes'] = $meeting['notes'];
            }
            
            // Load project files (if table exists)
            try {
                $stmt = db()->prepare("SELECT * FROM project_files WHERE project_id = ? ORDER BY uploaded_at DESC");
                $stmt->execute([$id]);
                $projectFiles = $stmt->fetchAll();
            } catch (Exception $e) {
                // Table might not exist yet
                $projectFiles = [];
            }
        }
    } catch (Exception $e) {
        $error = 'Greška pri učitavanju projekta: ' . $e->getMessage();
    }
}

// Default values
if (!$project) {
    $project = [
        'name' => '',
        'client_name' => '',
        'client_email' => '',
        'package_type' => 'basic',
        'agreement_date' => date('Y-m-d'),
        'deadline' => date('Y-m-d', strtotime('+30 days')),
        'status' => 'current',
        'current_phase' => 'agreement',
        'has_agreement' => 0,
        'meeting_date' => null,
        'notes' => ''
    ];
}

$phaseNames = [
    'agreement' => 'Dogovor',
    'planning' => 'Planiranje',
    'design' => 'Dizajn',
    'development' => 'Razvoj',
    'content' => 'Sadržaj',
    'testing' => 'Testiranje',
    'final' => 'Finalizacija'
];

$packageTypes = [
    'basic' => 'Osnovna Stranica',
    'professional' => 'Profesionalna Stranica',
    'premium' => 'Premium Stranica',
    'custom' => 'Custom Projekt'
];
?>

<?php if ($error): ?>
    <div class="alert alert--error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] === 'files_uploaded'): ?>
        <div class="alert alert--success">Datoteke su uspješno uploadane!</div>
    <?php elseif ($_GET['success'] === 'file_deleted'): ?>
        <div class="alert alert--success">Datoteka je uspješno obrisana!</div>
    <?php endif; ?>
<?php endif; ?>

<form method="POST" action="" id="project-form">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title"><?php echo $id > 0 ? 'Uredi Projekt' : 'Novi Projekt'; ?></h2>
            <a href="projects.php" class="btn btn--secondary btn--sm">← Natrag</a>
        </div>
        
        <div class="card__body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="name">Naziv projekta *</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($project['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="package_type">Tip paketa *</label>
                    <select class="form-control" id="package_type" name="package_type" required>
                        <?php foreach ($packageTypes as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $project['package_type'] === $key ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="client_name">Ime klijenta</label>
                    <input type="text" class="form-control" id="client_name" name="client_name" value="<?php echo htmlspecialchars($project['client_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="client_email">Email klijenta (opcionalno)</label>
                    <input type="email" class="form-control" id="client_email" name="client_email" value="<?php echo htmlspecialchars($project['client_email'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Agreement Status Section -->
            <div style="padding: 20px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; margin-bottom: 20px; border-left: 4px solid var(--primary);">
                <h3 style="color: var(--primary); margin-bottom: 16px; font-size: 16px; font-weight: 600;">Status Dogovora</h3>
                <div style="display: grid; gap: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 8px; background: rgba(255, 255, 255, 0.05); border-radius: 6px;">
                        <input type="checkbox" name="has_agreement" value="1" <?php echo (isset($project['has_agreement']) && $project['has_agreement']) ? 'checked' : ''; ?>>
                        <span style="color: var(--text-primary); font-weight: 500;">Sporazum je potpisan</span>
                    </label>
                    
                    <div id="meeting-section" style="<?php echo (isset($project['has_agreement']) && $project['has_agreement']) ? 'display: none;' : ''; ?>">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 6px;">
                            <div class="form-group">
                                <label for="meeting_date" style="color: var(--text-primary); font-weight: 500; margin-bottom: 6px; display: block;">Datum i vrijeme sastanka</label>
                                <input type="datetime-local" class="form-control" id="meeting_date" name="meeting_date" value="<?php echo isset($project['meeting_date']) && $project['meeting_date'] ? date('Y-m-d\TH:i', strtotime($project['meeting_date'])) : ''; ?>" style="background: var(--bg-dark); border: 1px solid rgba(255, 255, 255, 0.1); color: var(--text-primary); color-scheme: dark;">
                            </div>
                            <div class="form-group">
                                <label for="meeting_notes" style="color: var(--text-primary); font-weight: 500; margin-bottom: 6px; display: block;">Napomene o sastanku</label>
                                <input type="text" class="form-control" id="meeting_notes" name="meeting_notes" value="<?php echo isset($project['meeting_notes']) ? htmlspecialchars($project['meeting_notes']) : ''; ?>" placeholder="Lokacija, tema, itd." style="background: var(--bg-dark); border: 1px solid rgba(255, 255, 255, 0.1);">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="agreement_date">Datum sporazuma *</label>
                    <input type="date" class="form-control" id="agreement_date" name="agreement_date" value="<?php echo $project['agreement_date']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="deadline">Rok *</label>
                    <input type="date" class="form-control" id="deadline" name="deadline" value="<?php echo $project['deadline']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="future" <?php echo $project['status'] === 'future' ? 'selected' : ''; ?>>Budući</option>
                        <option value="current" <?php echo $project['status'] === 'current' ? 'selected' : ''; ?>>Trenutni</option>
                        <option value="past" <?php echo $project['status'] === 'past' ? 'selected' : ''; ?>>Završen</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="current_phase">Trenutna faza</label>
                <select class="form-control" id="current_phase" name="current_phase">
                    <?php foreach ($phaseNames as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $project['current_phase'] === $key ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="notes">Napomene</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($project['notes']); ?></textarea>
            </div>
        </div>
    </div>
    
    <!-- Phases Configuration -->
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Konfiguracija faza</h2>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: 8px;">Postavite trajanje svake faze u danima</p>
        </div>
        
        <div class="card__body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: var(--primary); margin: 0; font-size: 18px;">Faze projekta</h3>
                <button type="button" class="btn btn--accent btn--sm" onclick="autopopulateChecklist(<?php echo $id; ?>, '<?php echo $project['package_type'] ?? 'basic'; ?>')" id="autopopulate-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px; vertical-align: middle;">
                        <path d="M12 2v20M2 12h20"></path>
                    </svg>
                    Auto-popuni checklist
                </button>
            </div>
            <div style="display: grid; gap: 16px;">
                <?php foreach ($phaseNames as $phaseKey => $phaseLabel): ?>
                <div style="padding: 16px; background: var(--bg-input); border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <label style="font-weight: 600; color: var(--text-primary);" for="phase_duration_<?php echo $phaseKey; ?>">
                            <?php echo $phaseLabel; ?>
                        </label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="number" 
                                   class="form-control" 
                                   id="phase_duration_<?php echo $phaseKey; ?>" 
                                   name="phase_duration_<?php echo $phaseKey; ?>" 
                                   value="<?php echo isset($phases[$phaseKey]) && $phases[$phaseKey]['duration_days'] > 0 ? $phases[$phaseKey]['duration_days'] : ''; ?>" 
                                   min="0" 
                                   style="width: 80px; background: var(--bg-dark); border: 1px solid rgba(255, 255, 255, 0.1); color: var(--text-primary);"
                                   placeholder="0">
                            <span style="color: var(--text-secondary); font-size: 14px;">dana</span>
                            <?php if (isset($phases[$phaseKey]) && $phases[$phaseKey]['completed']): ?>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="checkbox" name="phase_completed_<?php echo $phaseKey; ?>" checked>
                                <span style="color: var(--success); font-size: 12px;">Završeno</span>
                            </label>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Checklist for this phase -->
                    <div style="margin-top: 12px;">
                        <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Checklist za <?php echo $phaseLabel; ?>:</div>
                        <div id="checklist-<?php echo $phaseKey; ?>" style="display: grid; gap: 8px;">
                            <?php if (isset($checklist[$phaseKey])): ?>
                                <?php foreach ($checklist[$phaseKey] as $index => $item): ?>
                                <div style="display: flex; align-items: center; gap: 8px; padding: 8px; background: var(--bg-dark); border-radius: 6px;">
                                    <input type="checkbox" 
                                           name="checklist_completed[<?php echo $phaseKey; ?>][<?php echo $index; ?>]" 
                                           <?php echo $item['completed'] ? 'checked' : ''; ?>
                                           style="cursor: pointer;">
                                    <input type="text" 
                                           class="form-control" 
                                           name="checklist[<?php echo $phaseKey; ?>][]" 
                                           value="<?php echo htmlspecialchars($item['task']); ?>" 
                                           placeholder="Zadatak..."
                                           style="flex: 1;">
                                    <button type="button" class="btn btn--danger btn--sm" onclick="this.parentElement.remove()">×</button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn--secondary btn--sm" style="margin-top: 8px;" onclick="addChecklistItem('<?php echo $phaseKey; ?>')">
                            + Dodaj zadatak
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 12px; margin-top: 24px;">
        <button type="submit" class="btn btn--primary" id="save-project-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Spremi projekt
        </button>
        <a href="projects.php" class="btn btn--secondary">Odustani</a>
    </div>
</form>

<!-- Project Files Section (outside main form to avoid nested forms) -->
<div class="card" style="margin-top: 24px;">
    <div class="card__header">
        <h2 class="card__title">Dokumenti i datoteke</h2>
        <p style="color: var(--text-secondary); font-size: 13px; margin-top: 8px;">Upload sporazuma, dokumenata i drugih datoteka</p>
    </div>
    <div class="card__body">
        <!-- File Upload Form -->
        <?php if ($id > 0): ?>
        <form method="POST" action="" enctype="multipart/form-data" id="file-upload-form" style="margin-bottom: 24px;">
                <div style="display: grid; gap: 16px;">
                    <div id="file-uploads">
                        <div class="file-upload-item" style="display: grid; grid-template-columns: 1fr 200px auto; gap: 12px; align-items: end; padding: 12px; background: var(--bg-input); border-radius: 8px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; color: var(--text-primary); margin-bottom: 6px; font-size: 14px;">Datoteka</label>
                                <input type="file" name="project_files[]" class="form-control" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar" style="background: var(--bg-dark); border: 1px solid rgba(255, 255, 255, 0.1);">
                            </div>
                            <div>
                                <label style="display: block; color: var(--text-primary); margin-bottom: 6px; font-size: 14px;">Tip</label>
                                <select name="file_types[]" class="form-control" style="background: var(--bg-dark); border: 1px solid rgba(255, 255, 255, 0.1);">
                                    <option value="agreement">Sporazum</option>
                                    <option value="document" selected>Dokument</option>
                                    <option value="other">Ostalo</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn--secondary btn--sm" onclick="addFileUpload()" style="height: fit-content;">+</button>
                        </div>
                    </div>
                    <button type="submit" name="upload_files" class="btn btn--primary btn--sm" style="width: fit-content;">
                        Upload datoteke
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div style="padding: 20px; background: var(--bg-input); border-radius: 8px; text-align: center; color: var(--text-secondary); margin-bottom: 24px;">
                <p>Molimo prvo spremite projekt da biste mogli uploadati datoteke.</p>
            </div>
            <?php endif; ?>
            
            <!-- Existing Files -->
            <?php if ($id > 0): ?>
                <?php if (!empty($projectFiles)): ?>
                <div style="border-top: 1px solid var(--border); padding-top: 20px;">
                    <h3 style="color: var(--text-primary); margin-bottom: 16px; font-size: 16px;">Uploadane datoteke</h3>
                    <div style="display: grid; gap: 12px;">
                        <?php foreach ($projectFiles as $file): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg-input); border-radius: 8px;">
                            <div style="flex: 1;">
                                <div style="color: var(--text-primary); font-weight: 500; margin-bottom: 4px;">
                                    <?php echo htmlspecialchars($file['file_name']); ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-secondary);">
                                    <?php 
                                    $fileTypeLabels = ['agreement' => 'Sporazum', 'document' => 'Dokument', 'other' => 'Ostalo'];
                                    echo $fileTypeLabels[$file['file_type']] ?? 'Dokument';
                                    ?>
                                    • <?php echo number_format($file['file_size'] / 1024, 2); ?> KB
                                    • <?php echo date('d.m.Y H:i', strtotime($file['uploaded_at'])); ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <a href="../<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank" class="btn btn--secondary btn--sm">Pregledaj</a>
                                <a href="?id=<?php echo $id; ?>&delete_file=<?php echo $file['id']; ?>" class="btn btn--danger btn--sm" onclick="return confirm('Jeste li sigurni da želite obrisati ovu datoteku?')">Obriši</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div style="border-top: 1px solid var(--border); padding-top: 20px; text-align: center; padding: 40px; color: var(--text-secondary);">
                    <p>Nema uploadanih datoteka.</p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
function addChecklistItem(phaseKey) {
    const container = document.getElementById('checklist-' + phaseKey);
    const index = container.children.length;
    const div = document.createElement('div');
    div.style.cssText = 'display: flex; align-items: center; gap: 8px; padding: 8px; background: var(--bg-dark); border-radius: 6px;';
    div.innerHTML = `
        <input type="checkbox" name="checklist_completed[${phaseKey}][${index}]" style="cursor: pointer;">
        <input type="text" class="form-control" name="checklist[${phaseKey}][]" placeholder="Zadatak..." style="flex: 1;" required>
        <button type="button" class="btn btn--danger btn--sm" onclick="this.parentElement.remove()">×</button>
    `;
    container.appendChild(div);
}

// Toggle meeting section based on agreement checkbox
document.addEventListener('DOMContentLoaded', function() {
    const agreementCheckbox = document.querySelector('input[name="has_agreement"]');
    const meetingSection = document.getElementById('meeting-section');
    
    if (agreementCheckbox && meetingSection) {
        agreementCheckbox.addEventListener('change', function() {
            meetingSection.style.display = this.checked ? 'none' : 'block';
        });
    }
});

// Add file upload row
function addFileUpload() {
    const container = document.getElementById('file-uploads');
    const newRow = document.createElement('div');
    newRow.className = 'file-upload-item';
    newRow.style.cssText = 'display: grid; grid-template-columns: 1fr 200px auto; gap: 12px; align-items: end; padding: 12px; background: var(--bg-input); border-radius: 8px; margin-bottom: 12px;';
    newRow.innerHTML = `
        <div>
            <label style="display: block; color: var(--text-primary); margin-bottom: 6px; font-size: 14px;">Datoteka</label>
            <input type="file" name="project_files[]" class="form-control" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar" style="background: var(--bg-dark); border: 1px solid rgba(255, 255, 255, 0.1);">
        </div>
        <div>
            <label style="display: block; color: var(--text-primary); margin-bottom: 6px; font-size: 14px;">Tip</label>
            <select name="file_types[]" class="form-control" style="background: var(--bg-dark); border: 1px solid rgba(255, 255, 255, 0.1);">
                <option value="agreement">Sporazum</option>
                <option value="document" selected>Dokument</option>
                <option value="other">Ostalo</option>
            </select>
        </div>
        <button type="button" class="btn btn--danger btn--sm" onclick="this.parentElement.remove()" style="height: fit-content;">×</button>
    `;
    container.appendChild(newRow);
}

// Auto-populate checklist function
function autopopulateChecklist(projectId, tier) {
    if (!confirm('Želite li automatski popuniti checklist za sve faze? Ovo će zamijeniti postojeće zadatke.')) {
        return;
    }
    
    const btn = document.getElementById('autopopulate-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Učitavanje...';
    
    // If project doesn't exist yet (new project), populate form fields directly
    if (projectId === 0 || projectId === '0') {
        populateChecklistForm(tier);
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Checklist je automatski popunjen u formi. Spremite projekt da se zadaci sačuvaju.');
        return;
    }
    
    // For existing projects, use API
    fetch('project-autopopulate.php?project_id=' + projectId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Greška: ' + data.error);
                btn.disabled = false;
                btn.innerHTML = originalText;
                return;
            }
            
            if (data.success) {
                alert(data.message + ' (' + data.inserted + ' zadataka dodano)');
                // Reload page to show new checklist items
                window.location.reload();
            }
        })
        .catch(error => {
            alert('Greška pri učitavanju: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
}

// Populate checklist form fields directly (for new projects)
function populateChecklistForm(tier) {
    // Checklist templates (same as PHP templates)
    const templates = {
        'basic': {
            'agreement': ['Potvrditi zahtjeve klijenta', 'Definirati opseg projekta', 'Potpisati sporazum', 'Primiti početnu uplatu'],
            'planning': ['Analizirati konkurenciju', 'Kreirati strukturu stranice', 'Planirati navigaciju', 'Odabrati boje i fontove'],
            'design': ['Kreirati wireframe', 'Dizajnirati homepage', 'Dizajnirati ostale stranice', 'Pripremiti dizajn za development'],
            'development': ['Postaviti HTML strukturu', 'Dodati CSS stilove', 'Implementirati JavaScript funkcionalnosti', 'Dodati kontakt formu', 'Integrirati Umami Analytics', 'Dodati Cloudflare Turnstile', 'Testirati responsive dizajn'],
            'content': ['Pripremiti tekstove', 'Optimizirati slike', 'Dodati SEO meta tagove', 'Provjeriti pravopis'],
            'testing': ['Testirati na različitim preglednicima', 'Testirati na mobilnim uređajima', 'Provjeriti brzinu učitavanja', 'Testirati kontakt formu'],
            'final': ['Finalna provjera', 'Upload na Infonet.hr', 'Povezati domenu', 'Konfigurirati email sistem', 'Predati klijentu']
        },
        'professional': {
            'agreement': ['Potvrditi zahtjeve klijenta', 'Definirati opseg projekta', 'Analizirati konkurenciju', 'Potpisati sporazum', 'Primiti početnu uplatu'],
            'planning': ['Kreirati sitemap', 'Planirati informacijsku arhitekturu', 'Odabrati CMS ili framework', 'Planirati SEO strategiju', 'Definirati user flow'],
            'design': ['Kreirati wireframes za sve stranice', 'Dizajnirati homepage', 'Dizajnirati unutarnje stranice', 'Kreirati UI komponente', 'Pripremiti design system', 'Dizajnirati mobilnu verziju'],
            'development': ['Postaviti development okruženje', 'Kreirati bazu podataka', 'Implementirati backend funkcionalnosti', 'Razviti frontend', 'Integrirati CMS', 'Dodati admin panel', 'Implementirati SEO optimizacije', 'Dodati Umami Analytics', 'Dodati Cloudflare Turnstile', 'Testirati responsive dizajn'],
            'content': ['Kreirati sve tekstove', 'Optimizirati sve slike', 'Dodati SEO meta tagove', 'Kreirati blog postove (ako potrebno)', 'Provjeriti pravopis i gramatiku'],
            'testing': ['Testirati na različitim preglednicima', 'Testirati na mobilnim uređajima', 'Provjeriti brzinu učitavanja', 'Testirati sve forme', 'Testirati admin panel', 'Provjeriti SEO optimizacije'],
            'final': ['Finalna provjera', 'Upload na Infonet.hr', 'Povezati domenu', 'Konfigurirati email sistem', 'Postaviti SSL certifikat', 'Predati klijentu', 'Obuka klijenta za admin panel']
        },
        'premium': {
            'agreement': ['Potvrditi zahtjeve klijenta', 'Definirati opseg projekta', 'Analizirati konkurenciju', 'Kreirati projektni plan', 'Potpisati sporazum', 'Primiti početnu uplatu'],
            'planning': ['Kreirati detaljnu sitemap', 'Planirati informacijsku arhitekturu', 'Odabrati tehnologije i framework', 'Planirati SEO strategiju', 'Definirati user personas', 'Planirati user experience', 'Kreirati content strategiju'],
            'design': ['Kreirati wireframes za sve stranice', 'Dizajnirati homepage', 'Dizajnirati unutarnje stranice', 'Kreirati UI komponente', 'Pripremiti design system', 'Dizajnirati mobilnu verziju', 'Kreirati animacije i interakcije', 'Dizajnirati email template'],
            'development': ['Postaviti development okruženje', 'Kreirati bazu podataka', 'Implementirati backend API', 'Razviti frontend aplikaciju', 'Integrirati CMS', 'Dodati custom admin panel', 'Implementirati napredne SEO optimizacije', 'Dodati Umami Analytics', 'Dodati Cloudflare Turnstile', 'Implementirati caching', 'Optimizirati performanse', 'Testirati responsive dizajn', 'Dodati PWA funkcionalnosti (ako potrebno)'],
            'content': ['Kreirati sve tekstove', 'Optimizirati sve slike', 'Dodati SEO meta tagove', 'Kreirati blog postove', 'Kreirati video sadržaj (ako potrebno)', 'Provjeriti pravopis i gramatiku', 'Optimizirati za pretraživanje'],
            'testing': ['Testirati na različitim preglednicima', 'Testirati na mobilnim uređajima', 'Provjeriti brzinu učitavanja', 'Testirati sve forme', 'Testirati admin panel', 'Provjeriti SEO optimizacije', 'Testirati security', 'Provjeriti accessibility', 'Load testing'],
            'final': ['Finalna provjera', 'Upload na Infonet.hr', 'Povezati domenu', 'Konfigurirati email sistem', 'Postaviti SSL certifikat', 'Optimizirati performanse na produkciji', 'Predati klijentu', 'Obuka klijenta za admin panel', 'Postaviti backup sistem']
        },
        'custom': {
            'agreement': ['Potvrditi zahtjeve klijenta', 'Definirati opseg projekta', 'Analizirati konkurenciju', 'Kreirati detaljni projektni plan', 'Potpisati sporazum', 'Primiti početnu uplatu'],
            'planning': ['Kreirati detaljnu sitemap', 'Planirati informacijsku arhitekturu', 'Odabrati tehnologije i framework', 'Planirati SEO strategiju', 'Definirati user personas', 'Planirati user experience', 'Kreirati content strategiju', 'Planirati custom funkcionalnosti'],
            'design': ['Kreirati wireframes za sve stranice', 'Dizajnirati homepage', 'Dizajnirati unutarnje stranice', 'Kreirati UI komponente', 'Pripremiti design system', 'Dizajnirati mobilnu verziju', 'Kreirati animacije i interakcije', 'Dizajnirati custom funkcionalnosti'],
            'development': ['Postaviti development okruženje', 'Kreirati bazu podataka', 'Implementirati backend API', 'Razviti frontend aplikaciju', 'Integrirati CMS', 'Dodati custom admin panel', 'Implementirati custom funkcionalnosti', 'Implementirati napredne SEO optimizacije', 'Dodati Umami Analytics', 'Dodati Cloudflare Turnstile', 'Implementirati caching', 'Optimizirati performanse', 'Testirati responsive dizajn'],
            'content': ['Kreirati sve tekstove', 'Optimizirati sve slike', 'Dodati SEO meta tagove', 'Kreirati blog postove', 'Provjeriti pravopis i gramatiku', 'Optimizirati za pretraživanje'],
            'testing': ['Testirati na različitim preglednicima', 'Testirati na mobilnim uređajima', 'Provjeriti brzinu učitavanja', 'Testirati sve forme', 'Testirati admin panel', 'Provjeriti SEO optimizacije', 'Testirati security', 'Provjeriti accessibility', 'Testirati custom funkcionalnosti'],
            'final': ['Finalna provjera', 'Upload na Infonet.hr', 'Povezati domenu', 'Konfigurirati email sistem', 'Postaviti SSL certifikat', 'Optimizirati performanse na produkciji', 'Predati klijentu', 'Obuka klijenta za admin panel', 'Postaviti backup sistem']
        }
    };
    
    const template = templates[tier] || templates['basic'];
    const phases = ['agreement', 'planning', 'design', 'development', 'content', 'testing', 'final'];
    
    phases.forEach(function(phase) {
        const container = document.getElementById('checklist-' + phase);
        if (!container) return;
        
        // Clear existing items
        container.innerHTML = '';
        
        // Add items from template
        if (template[phase]) {
            template[phase].forEach(function(task, index) {
                const div = document.createElement('div');
                div.style.cssText = 'display: flex; align-items: center; gap: 8px; padding: 8px; background: var(--bg-dark); border-radius: 6px;';
                div.innerHTML = `
                    <input type="checkbox" name="checklist_completed[${phase}][${index}]" style="cursor: pointer;">
                    <input type="text" class="form-control" name="checklist[${phase}][]" value="${escapeHtml(task)}" placeholder="Zadatak..." style="flex: 1;" required>
                    <button type="button" class="btn btn--danger btn--sm" onclick="this.parentElement.remove()">×</button>
                `;
                container.appendChild(div);
            });
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once 'includes/footer.php'; ?>

