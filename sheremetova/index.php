<?php
require_once 'config.php';

$message = '';
$error = '';

// Получаем всех сотрудников с информацией о подразделении
try {
    $sql = "SELECT e.*, d.department_name 
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.department_id
            ORDER BY e.employee_id DESC";
    $stmt = $pdo->query($sql);
    $employees = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Ошибка: " . $e->getMessage();
    $employees = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Страховая бухгалтерия - Сотрудники</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #2c3e50; color: white; padding: 20px; }
        .header h1 { margin-bottom: 10px; }
        .nav { background: #34495e; padding: 10px 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .nav a { color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; background: #2c3e50; transition: 0.3s; }
        .nav a:hover { background: #1abc9c; }
        .content { padding: 20px; }
        .btn-add { background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 20px; }
        .btn-add:hover { background: #219a52; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #3498db; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        tr:hover { background: #f1f1f1; }
        
        /* Стили для кнопок действий */
        .actions { display: flex; gap: 5px; flex-wrap: wrap; }
        .btn { padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; display: inline-block; transition: 0.2s; border: none; cursor: pointer; }
        .btn-view { background: #3498db; color: white; }
        .btn-view:hover { background: #2980b9; }
        .btn-edit { background: #f39c12; color: white; }
        .btn-edit:hover { background: #e67e22; }
        .btn-salary { background: #9b59b6; color: white; }
        .btn-salary:hover { background: #8e44ad; }
        .btn-tax { background: #1abc9c; color: white; }
        .btn-tax:hover { background: #16a085; }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-delete:hover { background: #c0392b; }
        
        .message { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        
        @media (max-width: 1000px) {
            table { font-size: 12px; }
            .btn { padding: 3px 6px; font-size: 10px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🏢 Страховая бухгалтерия</h1>
        <p>Управление сотрудниками, начислениями и налоговыми взносами</p>
    </div>
    
    <div class="nav">
        <a href="index.php">📋 Сотрудники</a>
        <a href="departments.php">🏛 Подразделения</a>
        <a href="salary_calc.php">💰 Расчет зарплаты</a>
        <a href="accruals.php">📊 Начисления/Удержания</a>
        <a href="tax_contributions.php">📑 Налоговые взносы</a>
    </div>
    
    <div class="content">
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <a href="employee_create.php" class="btn-add">➕ Добавить сотрудника</a>
        
        <h2>📋 Список сотрудников</h2>
        
        <?php if (empty($employees)): ?>
            <div style="background: #f9f9f9; padding: 40px; text-align: center; border-radius: 5px;">
                📭 Нет данных о сотрудниках. <a href="employee_create.php">Добавьте первого сотрудника</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Фамилия</th>
                        <th>Имя</th>
                        <th>Отчество</th>
                        <th>ИНН</th>
                        <th>СНИЛС</th>
                        <th>Дата рождения</th>
                        <th>Подразделение</th>
                        <th>Дата приема</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td><?= $emp['employee_id'] ?></td>
                            <td><?= htmlspecialchars($emp['last_name']) ?></td>
                            <td><?= htmlspecialchars($emp['first_name']) ?></td>
                            <td><?= htmlspecialchars($emp['middle_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($emp['inn'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($emp['snils'] ?? '-') ?></td>
                            <td><?= $emp['birth_date'] ?? '-' ?></td>
                            <td><?= htmlspecialchars($emp['department_name'] ?? '-') ?></td>
                            <td><?= $emp['hire_date'] ?? '-' ?></td>
                            <td class="actions">
                                <a href="employee_view.php?id=<?= $emp['employee_id'] ?>" class="btn btn-view" title="Просмотр">👁 Просмотр</a>
                                <a href="employee_edit.php?id=<?= $emp['employee_id'] ?>" class="btn btn-edit" title="Редактировать">✏ Ред.</a>
                                <a href="salary_calc_list.php?employee_id=<?= $emp['employee_id'] ?>" class="btn btn-salary" title="Зарплата">💰 Зарплата</a>
                                <a href="tax_list.php?employee_id=<?= $emp['employee_id'] ?>" class="btn btn-tax" title="Налоги">📑 Налоги</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>