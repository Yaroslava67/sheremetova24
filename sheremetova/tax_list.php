<?php
require_once 'config.php';

$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

if ($employee_id <= 0) {
    header("Location: index.php");
    exit();
}

// Получаем информацию о сотруднике
$employee = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();
} catch(PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}

// Получаем налоговые взносы
$taxes = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM tax_contributions WHERE employee_id = ? ORDER BY period_year DESC, period_month DESC");
    $stmt->execute([$employee_id]);
    $taxes = $stmt->fetchAll();
} catch(PDOException $e) {
    $taxes = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Налоги сотрудника</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 8px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #1abc9c; color: white; }
        .back { display: inline-block; margin-top: 20px; text-decoration: none; background: #3498db; color: white; padding: 10px 20px; border-radius: 4px; }
        .info-employee { background: #ecf0f1; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1>📑 Налоговые взносы сотрудника</h1>
    
    <div class="info-employee">
        <strong>Сотрудник:</strong> <?= htmlspecialchars($employee['last_name'] . ' ' . $employee['first_name']) ?>
    </div>
    
    <?php if (empty($taxes)): ?>
        <p>Нет данных о налоговых взносах</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Период</th>
                    <th>Тип налога</th>
                    <th>База (руб.)</th>
                    <th>Ставка</th>
                    <th>Сумма (руб.)</th>
                    <th>Статус</th>
                    <th>Дата оплаты</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($taxes as $tax): ?>
                    <tr>
                        <td><?= $tax['tax_id'] ?></td>
                        <td><?= $tax['period_year'] ?> / <?= $tax['period_month'] ?></td>
                        <td><?= $tax['tax_type'] ?></td>
                        <td><?= number_format($tax['taxable_base'], 2) ?></td>
                        <td><?= $tax['rate_percent'] ?>%</td>
                        <td><?= number_format($tax['amount'], 2) ?></td>
                        <td><?= $tax['payment_status'] ?></td>
                        <td><?= $tax['payment_date'] ?? '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <a href="index.php" class="back">← Назад к списку</a>
</div>
</body>
</html>