<?php
require_once 'config.php';

$error = '';
$success = '';

// Обработка добавления подразделения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_department'])) {
    $department_name = trim($_POST['department_name'] ?? '');
    $cost_center_code = !empty($_POST['cost_center_code']) ? trim($_POST['cost_center_code']) : null;
    
    if (empty($department_name)) {
        $error = "Название подразделения обязательно для заполнения";
    } else {
        try {
            // Проверяем, существует ли уже такое подразделение
            $check = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE department_name = :name");
            $check->execute([':name' => $department_name]);
            $exists = $check->fetchColumn();
            
            if ($exists > 0) {
                $error = "Подразделение с названием '{$department_name}' уже существует";
            } else {
                $sql = "INSERT INTO departments (department_name, cost_center_code) VALUES (:name, :code)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':name' => $department_name, ':code' => $cost_center_code]);
                $success = "Подразделение '{$department_name}' успешно добавлено";
                header("Location: departments.php");
                exit();
            }
        } catch(PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = "Подразделение с таким названием уже существует";
            } else {
                $error = "Ошибка: " . $e->getMessage();
            }
        }
    }
}

// Обработка удаления подразделения
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    try {
        // Проверяем, есть ли сотрудники в этом подразделении
        $check = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE department_id = :id");
        $check->execute([':id' => $delete_id]);
        $employee_count = $check->fetchColumn();
        
        if ($employee_count > 0) {
            $error = "Невозможно удалить подразделение. В нем числится {$employee_count} сотрудников. Сначала переведите сотрудников в другое подразделение.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM departments WHERE department_id = :id");
            $stmt->execute([':id' => $delete_id]);
            $success = "Подразделение успешно удалено";
            header("Location: departments.php");
            exit();
        }
    } catch(PDOException $e) {
        $error = "Ошибка удаления: " . $e->getMessage();
    }
}

// Обработка редактирования подразделения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_department'])) {
    $edit_id = (int)$_POST['department_id'];
    $department_name = trim($_POST['department_name'] ?? '');
    $cost_center_code = !empty($_POST['cost_center_code']) ? trim($_POST['cost_center_code']) : null;
    
    if (empty($department_name)) {
        $error = "Название подразделения обязательно";
    } else {
        try {
            // Проверяем, не занято ли название другим подразделением
            $check = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE department_name = :name AND department_id != :id");
            $check->execute([':name' => $department_name, ':id' => $edit_id]);
            $exists = $check->fetchColumn();
            
            if ($exists > 0) {
                $error = "Подразделение с названием '{$department_name}' уже существует";
            } else {
                $sql = "UPDATE departments SET department_name = :name, cost_center_code = :code WHERE department_id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':name' => $department_name, ':code' => $cost_center_code, ':id' => $edit_id]);
                $success = "Подразделение успешно обновлено";
                header("Location: departments.php");
                exit();
            }
        } catch(PDOException $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}

// Получаем все подразделения
$departments = [];
try {
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY department_id");
    $departments = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Ошибка загрузки: " . $e->getMessage();
}

// Получаем ID для редактирования
$edit_department = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    foreach ($departments as $dept) {
        if ($dept['department_id'] == $edit_id) {
            $edit_department = $dept;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление подразделениями</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-top: 0; }
        h2 { color: #34495e; font-size: 18px; margin-top: 30px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 10px; text-decoration: none; background: #3498db; color: white; padding: 8px 16px; border-radius: 4px; display: inline-block; }
        .nav a:hover { background: #2980b9; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input { width: 100%; max-width: 400px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #219a52; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #2c3e50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #e74c3c; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #27ae60; }
        .btn-edit { background: #f39c12; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; margin-right: 5px; }
        .btn-edit:hover { background: #e67e22; }
        .btn-delete { background: #e74c3c; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .btn-delete:hover { background: #c0392b; }
        .edit-form { background: #fef9e7; padding: 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid #f39c12; }
        hr { margin: 20px 0; border: none; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="index.php">🏠 На главную</a>
        <a href="index.php">📋 Список сотрудников</a>
    </div>
    
    <h1>🏛 Управление подразделениями</h1>
    
    <?php if ($error): ?>
        <div class="error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($edit_department): ?>
        <!-- Форма редактирования -->
        <div class="edit-form">
            <h2>✏ Редактирование подразделения</h2>
            <form method="POST">
                <input type="hidden" name="department_id" value="<?= $edit_department['department_id'] ?>">
                <div class="form-group">
                    <label>Название подразделения *</label>
                    <input type="text" name="department_name" value="<?= htmlspecialchars($edit_department['department_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Код центра затрат</label>
                    <input type="text" name="cost_center_code" value="<?= htmlspecialchars($edit_department['cost_center_code'] ?? '') ?>">
                </div>
                <button type="submit" name="edit_department">💾 Сохранить изменения</button>
                <a href="departments.php" style="margin-left: 10px; color: #e74c3c; text-decoration: none;">Отмена</a>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Форма добавления -->
    <h2>➕ Добавить новое подразделение</h2>
    <form method="POST">
        <div class="form-group">
            <label>Название подразделения *</label>
            <input type="text" name="department_name" placeholder="Например: IT-отдел, Бухгалтерия" required>
        </div>
        <div class="form-group">
            <label>Код центра затрат</label>
            <input type="text" name="cost_center_code" placeholder="Например: CC-IT-005">
        </div>
        <button type="submit" name="add_department">➕ Добавить</button>
    </form>
    
    <h2>📋 Список подразделений</h2>
    
    <?php if (empty($departments)): ?>
        <p>Нет добавленных подразделений. Создайте первое подразделение.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Код центра затрат</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $dept): ?>
                    <tr>
                        <td><?= $dept['department_id'] ?></td>
                        <td><?= htmlspecialchars($dept['department_name']) ?></td>
                        <td><?= htmlspecialchars($dept['cost_center_code'] ?? '-') ?></td>
                        <td><?= $dept['created_at'] ?? '-' ?></td>
                        <td>
                            <a href="departments.php?edit_id=<?= $dept['department_id'] ?>" class="btn-edit">✏ Ред.</a>
                            <a href="departments.php?delete_id=<?= $dept['department_id'] ?>" class="btn-delete" onclick="return confirm('Вы уверены? Сотрудники из этого подразделения не будут удалены, но останутся без подразделения.')">🗑 Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <hr>
    <p style="color: #666; font-size: 12px;">
        💡 Подсказка: При удалении подразделения сотрудники не удаляются, у них просто обнуляется связь с подразделением.
    </p>
</div>
</body>
</html>