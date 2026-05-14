<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

try {
    $sql = "SELECT e.*, d.department_name 
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.department_id
            WHERE e.employee_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        die("Сотрудник не найден");
    }
} catch(PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Просмотр сотрудника</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; padding: 20px; }
        .info { background: #f4f4f4; padding: 20px; border-radius: 5px; margin-top: 20px; }
        .info p { margin: 10px 0; }
        .back { display: inline-block; margin-top: 20px; text-decoration: none; background: #3498db; color: white; padding: 10px 20px; border-radius: 4px; }
        .label { font-weight: bold; display: inline-block; width: 180px; }
        h1 { color: #2c3e50; }
    </style>
</head>
<body>
<div class="container">
    <h1>👤 Просмотр сотрудника</h1>
    
    <div class="info">
        <p><span class="label">ID:</span> <?= $employee['employee_id'] ?></p>
        <p><span class="label">Фамилия:</span> <?= htmlspecialchars($employee['last_name']) ?></p>
        <p><span class="label">Имя:</span> <?= htmlspecialchars($employee['first_name']) ?></p>
        <p><span class="label">Отчество:</span> <?= htmlspecialchars($employee['middle_name'] ?? '-') ?></p>
        <p><span class="label">ИНН:</span> <?= htmlspecialchars($employee['inn'] ?? '-') ?></p>
        <p><span class="label">СНИЛС:</span> <?= htmlspecialchars($employee['snils'] ?? '-') ?></p>
        <p><span class="label">Дата рождения:</span> <?= $employee['birth_date'] ?? '-' ?></p>
        <p><span class="label">Дата приема:</span> <?= $employee['hire_date'] ?? '-' ?></p>
        <p><span class="label">Дата увольнения:</span> <?= $employee['dismissal_date'] ?? '-' ?></p>
        <p><span class="label">Подразделение:</span> <?= htmlspecialchars($employee['department_name'] ?? '-') ?></p>
        <p><span class="label">Дата создания:</span> <?= $employee['created_at'] ?? '-' ?></p>
    </div>
    
    <a href="index.php" class="back">← Назад к списку</a>
</div>
</body>
</html>