<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Получаем данные сотрудника
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = :id");
    $stmt->execute([':id' => $id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        die("Сотрудник не найден");
    }
} catch(PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}

// Получаем подразделения
$departments = [];
try {
    $stmt = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
    $departments = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Ошибка загрузки подразделений: " . $e->getMessage();
}

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = trim($_POST['last_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = !empty($_POST['middle_name']) ? trim($_POST['middle_name']) : null;
    $inn = !empty($_POST['inn']) ? trim($_POST['inn']) : null;
    $snils = !empty($_POST['snils']) ? trim($_POST['snils']) : null;
    
    // Важно: преобразуем пустые даты в NULL
    $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
    $hire_date = !empty($_POST['hire_date']) ? $_POST['hire_date'] : null;
    $dismissal_date = !empty($_POST['dismissal_date']) ? $_POST['dismissal_date'] : null;
    
    $department_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    
    if (empty($last_name) || empty($first_name)) {
        $error = "Фамилия и имя обязательны для заполнения";
    } else {
        try {
            $sql = "UPDATE employees SET 
                    last_name = :last_name,
                    first_name = :first_name,
                    middle_name = :middle_name,
                    inn = :inn,
                    snils = :snils,
                    birth_date = :birth_date,
                    hire_date = :hire_date,
                    dismissal_date = :dismissal_date,
                    department_id = :department_id
                    WHERE employee_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':last_name' => $last_name,
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':inn' => $inn,
                ':snils' => $snils,
                ':birth_date' => $birth_date,
                ':hire_date' => $hire_date,
                ':dismissal_date' => $dismissal_date,
                ':department_id' => $department_id,
                ':id' => $id
            ]);
            $success = "Данные успешно обновлены!";
            header("Location: index.php");
            exit();
        } catch(PDOException $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование сотрудника</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input, select { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        input:focus, select:focus { outline: none; border-color: #3498db; }
        button { background: #f39c12; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #e67e22; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #e74c3c; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #27ae60; }
        .back { display: inline-block; margin-top: 20px; color: #3498db; text-decoration: none; }
        .back:hover { text-decoration: underline; }
        hr { margin: 20px 0; border: none; border-top: 1px solid #eee; }
        .note { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
<div class="container">
    <h1>✏ Редактирование сотрудника</h1>
    
    <?php if ($error): ?>
        <div class="error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Фамилия *</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($employee['last_name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Имя *</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($employee['first_name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Отчество</label>
            <input type="text" name="middle_name" value="<?= htmlspecialchars($employee['middle_name'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label>ИНН (12 цифр)</label>
            <input type="text" name="inn" value="<?= htmlspecialchars($employee['inn'] ?? '') ?>" maxlength="12" pattern="\d*">
            <div class="note">Только цифры, 12 символов</div>
        </div>
        
        <div class="form-group">
            <label>СНИЛС</label>
            <input type="text" name="snils" value="<?= htmlspecialchars($employee['snils'] ?? '') ?>" placeholder="XXX-XXX-XXX XX">
        </div>
        
        <div class="form-group">
            <label>Дата рождения</label>
            <input type="date" name="birth_date" value="<?= $employee['birth_date'] ?? '' ?>">
        </div>
        
        <div class="form-group">
            <label>Дата приема</label>
            <input type="date" name="hire_date" value="<?= $employee['hire_date'] ?? '' ?>">
        </div>
        
        <div class="form-group">
            <label>Дата увольнения</label>
            <input type="date" name="dismissal_date" value="<?= $employee['dismissal_date'] ?? '' ?>">
            <div class="note">Оставьте пустым если сотрудник работает</div>
        </div>
        
        <div class="form-group">
            <label>Подразделение</label>
            <select name="department_id">
                <option value="">-- Выберите подразделение --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['department_id'] ?>" <?= ($employee['department_id'] == $dept['department_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['department_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit">💾 Сохранить изменения</button>
    </form>
    
    <hr>
    <a href="index.php" class="back">← Назад к списку сотрудников</a>
</div>
</body>
</html>