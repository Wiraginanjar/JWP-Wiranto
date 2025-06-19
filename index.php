<?php
// Mulai atau lanjutkan sesi yang ada.
session_start();

// Inisialisasi array tasks di dalam session jika belum ada.
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// ---- LOGIKA UNTUK MENGELOLA TUGAS ----

// Aksi 1: Menambah tugas baru (via metode POST dari form utama)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $newTaskText = trim($_POST['task_text']);
    if (!empty($newTaskText)) {
        $newTask = ['text' => $newTaskText, 'completed' => false];
        array_push($_SESSION['tasks'], $newTask);
    }
    header("Location: index.php");
    exit;
}

// Aksi 2: Memperbarui (Update) tugas yang ada (via metode POST dari form edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $updatedText = trim($_POST['updated_text']);
    
    // Pastikan ID valid dan teks baru tidak kosong
    if (isset($_SESSION['tasks'][$id]) && !empty($updatedText)) {
        $_SESSION['tasks'][$id]['text'] = $updatedText;
    }
    header("Location: index.php"); // Redirect untuk keluar dari mode edit
    exit;
}


// Aksi 3: Mengelola aksi lain (via metode GET dari link/checkbox)
if (isset($_GET['action'])) {
    // Pengecualian untuk action 'edit' yang tidak mengubah data, hanya tampilan
    if ($_GET['action'] !== 'edit') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : -1;

        if ($id >= 0 && isset($_SESSION['tasks'][$id])) {
            switch ($_GET['action']) {
                case 'toggle':
                    $_SESSION['tasks'][$id]['completed'] = !$_SESSION['tasks'][$id]['completed'];
                    break;
                case 'delete':
                    array_splice($_SESSION['tasks'], $id, 1);
                    break;
            }
        }
        header("Location: index.php"); // Redirect setelah aksi selesai
        exit;
    }
}

// Variabel untuk menentukan task mana yang sedang di-edit. -1 berarti tidak ada.
$edit_id = (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) ? (int)$_GET['id'] : -1;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List Bootstrap (dengan Edit)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f0f2f5; }
    </style>
</head>
<body>

    <header class="bg-primary text-white text-center py-3 shadow-sm">
        <h1><i class="bi bi-check2-square"></i> My To-Do List</h1>
        <p class="m-0">Dikelola dengan PHP & Bootstrap</p>
    </header>

    <main class="container mt-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <strong>Tambah Tugas Baru</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="add">
                    <div class="input-group">
                        <input type="text" name="task_text" class="form-control" placeholder="Apa yang akan Anda lakukan hari ini?" required autocomplete="off">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg"></i> Tambah</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Daftar Tugas</strong>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (empty($_SESSION['tasks'])): ?>
                        <li class="list-group-item text-center text-muted">Hore, tidak ada tugas!</li>
                    <?php else: ?>
                        <?php foreach ($_SESSION['tasks'] as $index => $task): ?>
                            <li class="list-group-item">
                                <?php if ($index === $edit_id): // JIKA TUGAS INI DALAM MODE EDIT ?>
                                    
                                    <form method="POST" action="index.php">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= $index ?>">
                                        <div class="input-group">
                                            <input type="text" name="updated_text" class="form-control" value="<?= htmlspecialchars($task['text']) ?>" required autocomplete="off">
                                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> Simpan</button>
                                            <a href="index.php" class="btn btn-secondary btn-sm"><i class="bi bi-x-lg"></i> Batal</a>
                                        </div>
                                    </form>

                                <?php else: // TAMPILAN NORMAL (BUKAN MODE EDIT) ?>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <form method="GET" action="index.php" class="m-0 d-inline">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="id" value="<?= $index ?>">
                                                <input class="form-check-input me-2" type="checkbox" onchange="this.form.submit()" <?php if ($task['completed']) echo 'checked'; ?>>
                                            </form>
                                            <span class="<?= $task['completed'] ? 'text-muted text-decoration-line-through' : '' ?>">
                                                <?= htmlspecialchars($task['text']) ?>
                                            </span>
                                        </div>
                                        
                                        <div>
                                            <?php if ($task['completed']): ?>
                                                <span class="badge bg-success">Selesai</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Belum Selesai</span>
                                            <?php endif; ?>
                                            <a href="?action=edit&id=<?= $index ?>" class="btn btn-warning btn-sm ms-2"><i class="bi bi-pencil"></i> Edit</a>
                                            <a href="?action=delete&id=<?= $index ?>" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </div>
                                    
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </main>
    
    <footer class="text-center text-muted py-3 mt-4">
        <p>&copy; <?= date('Y') ?> To-Do List App</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>