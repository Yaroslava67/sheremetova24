<?php
require_once 'config.php';

$error = '';
$success = '';

// Получаем список подразделений
$departments = [];
try {
    $stmt = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
    $departments = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Ошибка загрузки подразделений: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = $_POST['last_name'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $middle_name = $_POST['middle_name'] ?? null;
    $inn = $_POST['inn'] ?? null;
    $snils = $_POST['snils'] ?? null;
    $birth_date = $_POST['birth_date'] ?? null;
    $hire_date = $_POST['hire_date'] ?? null;
    $dismissal_date = $_POST['dismissal_date'] ?? null;
    $department_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    
    if (empty($last_name) || empty($first_name)) {
        $error = "Фамилия и имя обязательны для заполнения";
    } else {
        try {
            $sql = "INSERT INTO employees (last_name, first_name, middle_name, inn, snils, birth_date, hire_date, dismissal_date, department_id) 
                    VALUES (:last_name, :first_name, :middle_name, :inn, :snils, :birth_date, :hire_date, :dismissal_date, :department_id)";
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
                ':department_id' => $department_id
            ]);
            $success = "Сотрудник успешно добавлен!";
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
    <title>Добавление сотрудника</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #2c3e50; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #219a52; }
        .error { color: red; background: #fee; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .success { color: green; background: #efe; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .back { display: inline-block; margin-top: 20px; color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h1>Добавление сотрудника</h1>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Фамилия *</label>
            <input type="text" name="last_name" required>
        </div>
        
        <div class="form-group">
            <label>Имя *</label>
            <input type="text" name="first_name" required>
        </div>
        
        <div class="form-group">
            <label>Отчество</label>
            <input type="text" name="middle_name">
        </div>
        
        <div class="form-group">
            <label>ИНН (12 цифр)</label>
            <input type="text" name="inn" maxlength="12">
        </div>
        
        <div class="form-group">
            <label>СНИЛС (14 цифр с дефисом)</label>
            <input type="text" name="snils" placeholder="XXX-XXX-XXX XX">
        </div>
        
        <div class="form-group">
            <label>Дата рождения</label>
            <input type="date" name="birth_date">
        </div>
        
        <div class="form-group">
            <label>Дата приема</label>
            <input type="date" name="hire_date">
        </div>
        
        <div class="form-group">
            <label>Дата увольнения</label>
            <input type="date" name="dismissal_date">
        </div>
        
        <div class="form-group">
            <label>Подразделение</label>
            <select name="department_id">
                <option value="">-- Выберите подразделение --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit">Сохранить</button>
    </form>
    
    <a href="index.php" class="back">← Назад к списку</a>
</div>
</body>
</html>