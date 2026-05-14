<?php
require_once 'config.php';

$error = '';
$success = '';

// Обработка удаления взноса
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tax_contributions WHERE tax_id = ?");
        $stmt->execute([$delete_id]);
        $success = "Налоговый взнос удален";
        header("Location: tax_contributions.php");
        exit();
    } catch(PDOException $e) {
        $error = "Ошибка удаления: " . $e->getMessage();
    }
}

// Получаем все налоговые взносы с информацией о сотрудниках
$taxes = [];
try {
    $sql = "SELECT t.*, e.last_name, e.first_name, e.middle_name 
            FROM tax_contributions t
            LEFT JOIN employees e ON t.employee_id = e.employee_id
            ORDER BY t.created_at DESC";
    $stmt = $pdo->query($sql);
    $taxes = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Ошибка: " . $e->getMessage();
}

// Подсчет итогов по типам налогов
$totals = [
    'NDFL' => 0,
    'PFR' => 0,
    'FSS' => 0,
    'FOMS' => 0
];

foreach ($taxes as $tax) {
    $type = $tax['tax_type'];
    if (isset($totals[$type])) {
        $totals[$type] += $tax['amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Налоговые взносы</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-top: 0; }
        .nav { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .nav a { text-decoration: none; background: #3498db; color: white; padding: 8px 16px; border-radius: 4px; }
        .nav a:hover { background: #2980b9; }
        .btn-add { background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 20px; }
        .btn-add:hover { background: #219a52; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #1abc9c; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #e74c3c; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #27ae60; }
        .btn-delete { background: #e74c3c; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .btn-delete:hover { background: #c0392b; }
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }
        .status-overdue { color: red; font-weight: bold; }
        .totals { background: #ecf0f1; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 20px; }
        .total-item { flex: 1; text-align: center; padding: 10px; background: white; border-radius: 5px; }
        .total-item .label { font-size: 12px; color: #666; }
        .total-item .amount { font-size: 18px; font-weight: bold; }
        .ndfl { color: #3498db; }
        .pfr { color: #9b59b6; }
        .fss { color: #e74c3c; }
        .foms { color: #1abc9c; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="index.php">🏠 На главную</a>
        <a href="index.php">📋 Список сотрудников</a>
        <a href="departments.php">🏛 Подразделения</a>
    </div>
    
    <h1>📑 Налоговые взносы сотрудников</h1>
    
    <a href="tax_create.php" class="btn-add">➕ Добавить налоговый взнос</a>
    
    <?php if ($error): ?>
        <div class="error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <!-- Итоги по налогам -->
    <div class="totals">
        <div class="total-item">
            <div class="label">💰 НДФЛ</div>
            <div class="amount ndfl"><?= number_format($totals['NDFL'], 2) ?> ₽</div>
        </div>
        <div class="total-item">
            <div class="label">🏦 ПФР</div>
            <div class="amount pfr"><?= number_format($totals['PFR'], 2) ?> ₽</div>
        </div>
        <div class="total-item">
            <div class="label">🩺 ФСС</div>
            <div class="amount fss"><?= number_format($totals['FSS'], 2) ?> ₽</div>
        </div>
        <div class="total-item">
            <div class="label">🏥 ФОМС</div>
            <div class="amount foms"><?= number_format($totals['FOMS'], 2) ?> ₽</div>
        </div>
    </div>
    
    <?php if (empty($taxes)): ?>
        <p>Нет данных о налоговых взносах. <a href="tax_create.php">Добавьте первый взнос</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Сотрудник</th>
                    <th>Период</th>
                    <th>Тип налога</th>
                    <th>База (руб.)</th>
                    <th>Ставка</th>
                    <th>Сумма (руб.)</th>
                    <th>Статус</th>
                    <th>Дата оплаты</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($taxes as $tax): ?>
                    <tr>
                        <td><?= $tax['tax_id'] ?></td>
                        <td><?= htmlspecialchars($tax['last_name'] . ' ' . $tax['first_name'] . ' ' . ($tax['middle_name'] ?? '')) ?></td>
                        <td><?= $tax['period_year'] ?> / <?= $tax['period_month'] ?></td>
                        <td>
                            <?php if ($tax['tax_type'] == 'NDFL'): ?>
                                💰 НДФЛ
                            <?php elseif ($tax['tax_type'] == 'PFR'): ?>
                                🏦 ПФР
                            <?php elseif ($tax['tax_type'] == 'FSS'): ?>
                                🩺 ФСС
                            <?php else: ?>
                                🏥 ФОМС
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($tax['taxable_base'], 2) ?></td>
                        <td><?= $tax['rate_percent'] ?>%</td>
                        <td><?= number_format($tax['amount'], 2) ?> ₽</td>
                        <td class="status-<?= $tax['payment_status'] ?>">
                            <?php if ($tax['payment_status'] == 'pending'): ?>
                                ⏳ Ожидание
                            <?php elseif ($tax['payment_status'] == 'paid'): ?>
                                ✅ Оплачено
                            <?php else: ?>
                                ⚠️ Просрочено
                            <?php endif; ?>
                        </td>
                        <td><?= $tax['payment_date'] ?? '-' ?></td>
                        <td>
                            <a href="tax_contributions.php?delete_id=<?= $tax['tax_id'] ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Удалить налоговый взнос?')">🗑 Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>