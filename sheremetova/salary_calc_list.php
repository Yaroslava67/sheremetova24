<?php
require_once 'config.php';

$error = '';
$success = '';

$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

// Получаем информацию о сотруднике
$employee = null;
if ($employee_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
        $stmt->execute([$employee_id]);
        $employee = $stmt->fetch();
        
        if (!$employee) {
            $error = "Сотрудник не найден";
            $employee_id = 0;
        }
    } catch(PDOException $e) {
        $error = "Ошибка: " . $e->getMessage();
    }
}

// Получаем всех сотрудников для выпадающего списка
$all_employees = [];
try {
    $stmt = $pdo->query("SELECT employee_id, last_name, first_name, middle_name FROM employees ORDER BY last_name");
    $all_employees = $stmt->fetchAll();
} catch(PDOException $e) {
    // Таблица может быть пустой
}

// Обработка добавления расчета
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_calc'])) {
    $period_year = (int)$_POST['period_year'];
    $period_month = (int)$_POST['period_month'];
    $taxable_base = !empty($_POST['taxable_base']) ? (float)$_POST['taxable_base'] : 0;
    $rate_percent = !empty($_POST['rate_percent']) ? (float)$_POST['rate_percent'] : 13; // По умолчанию 13%
    $amount = !empty($_POST['amount']) ? (float)$_POST['amount'] : ($taxable_base * $rate_percent / 100);
    $payment_status = $_POST['payment_status'] ?? 'pending';
    $payment_date = !empty($_POST['payment_date']) ? $_POST['payment_date'] : null;
    
    // Если ставка не указана, устанавливаем 13% (НДФЛ)
    if ($rate_percent <= 0) {
        $rate_percent = 13;
        $amount = $taxable_base * $rate_percent / 100;
    }
    
    if ($taxable_base <= 0) {
        $error = "Налогооблагаемая база должна быть больше 0";
    } else {
        try {
            $sql = "INSERT INTO salary_calc (employee_id, period_year, period_month, taxable_base, rate_percent, amount, payment_status, payment_date) 
                    VALUES (:employee_id, :period_year, :period_month, :taxable_base, :rate_percent, :amount, :payment_status, :payment_date)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':employee_id' => $employee_id,
                ':period_year' => $period_year,
                ':period_month' => $period_month,
                ':taxable_base' => $taxable_base,
                ':rate_percent' => $rate_percent,
                ':amount' => $amount,
                ':payment_status' => $payment_status,
                ':payment_date' => $payment_date
            ]);
            $success = "Расчет успешно добавлен!";
            header("Location: salary_calc.php?employee_id=" . $employee_id);
            exit();
        } catch(PDOException $e) {
            $error = "Ошибка сохранения: " . $e->getMessage();
        }
    }
}

// Удаление расчета
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM salary_calc WHERE calc_id = ?");
        $stmt->execute([$delete_id]);
        $success = "Расчет удален";
        header("Location: salary_calc.php?employee_id=" . $employee_id);
        exit();
    } catch(PDOException $e) {
        $error = "Ошибка удаления: " . $e->getMessage();
    }
}

// Получаем существующие расчеты для сотрудника
$calculations = [];
if ($employee_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM salary_calc WHERE employee_id = ? ORDER BY period_year DESC, period_month DESC");
        $stmt->execute([$employee_id]);
        $calculations = $stmt->fetchAll();
    } catch(PDOException $e) {
        $error = "Ошибка загрузки расчетов: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Расчет зарплаты и налогов</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-top: 0; }
        h2 { color: #34495e; font-size: 18px; margin-top: 25px; }
        .nav { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .nav a { text-decoration: none; background: #3498db; color: white; padding: 8px 16px; border-radius: 4px; }
        .nav a:hover { background: #2980b9; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input, select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 100%; max-width: 300px; }
        button { background: #9b59b6; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background: #8e44ad; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #9b59b6; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #e74c3c; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #27ae60; }
        .info-employee { background: #f3e5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #9b59b6; }
        .btn-delete { background: #e74c3c; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .btn-delete:hover { background: #c0392b; }
        .employee-selector { background: #e8f4fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-end; }
        .employee-selector select { flex: 2; margin: 0; }
        .employee-selector button { margin: 0; background: #3498db; }
        .total { background: #ecf0f1; padding: 15px; border-radius: 5px; margin-top: 20px; font-weight: bold; }
        hr { margin: 20px 0; border: none; border-top: 1px solid #eee; }
        .form-row { display: flex; flex-wrap: wrap; gap: 20px; }
        .form-row .form-group { flex: 1; min-width: 200px; }
        .required:after { content: " *"; color: red; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="index.php">🏠 На главную</a>
        <a href="index.php">📋 Список сотрудников</a>
        <a href="departments.php">🏛 Подразделения</a>
    </div>
    
    <h1>💰 Расчет зарплаты и налогов</h1>
    
    <?php if ($error): ?>
        <div class="error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <!-- Выбор сотрудника -->
    <div class="employee-selector">
        <form method="GET" style="display: flex; gap: 10px; width: 100%;">
            <select name="employee_id" style="padding: 10px;">
                <option value="">-- Выберите сотрудника --</option>
                <?php foreach ($all_employees as $emp): ?>
                    <option value="<?= $emp['employee_id'] ?>" <?= ($employee_id == $emp['employee_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['last_name'] . ' ' . $emp['first_name'] . ' ' . ($emp['middle_name'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Выбрать</button>
        </form>
    </div>
    
    <?php if ($employee && $employee_id > 0): ?>
        <!-- Информация о сотруднике -->
        <div class="info-employee">
            <strong>👤 Сотрудник:</strong> <?= htmlspecialchars($employee['last_name'] . ' ' . $employee['first_name'] . ' ' . ($employee['middle_name'] ?? '')) ?>
            <br>
            <strong>🆔 ИНН:</strong> <?= htmlspecialchars($employee['inn'] ?? '-') ?>
            <br>
            <strong>📄 СНИЛС:</strong> <?= htmlspecialchars($employee['snils'] ?? '-') ?>
        </div>
        
        <!-- Форма добавления расчета -->
        <h2>📝 Добавить расчет</h2>
        <form method="POST" id="calcForm">
            <div class="form-row">
                <div class="form-group">
                    <label class="required">📅 Год</label>
                    <input type="number" name="period_year" value="<?= date('Y') ?>" min="2020" max="2030" required>
                </div>
                
                <div class="form-group">
                    <label class="required">📅 Месяц</label>
                    <select name="period_month" required>
                        <option value="1">Январь</option>
                        <option value="2">Февраль</option>
                        <option value="3">Март</option>
                        <option value="4">Апрель</option>
                        <option value="5" selected>Май</option>
                        <option value="6">Июнь</option>
                        <option value="7">Июль</option>
                        <option value="8">Август</option>
                        <option value="9">Сентябрь</option>
                        <option value="10">Октябрь</option>
                        <option value="11">Ноябрь</option>
                        <option value="12">Декабрь</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="required">💰 Налогооблагаемая база (руб.)</label>
                    <input type="number" step="0.01" name="taxable_base" id="taxable_base" placeholder="50000" required>
                </div>
                
                <div class="form-group">
                    <label>📊 Ставка (%) (по умолчанию 13%)</label>
                    <input type="number" step="0.01" name="rate_percent" id="rate_percent" placeholder="13" value="13">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>💵 Сумма (руб.)</label>
                    <input type="number" step="0.01" name="amount" id="amount" readonly style="background: #f0f0f0; font-weight: bold;">
                </div>
                
                <div class="form-group">
                    <label>📌 Статус оплаты</label>
                    <select name="payment_status">
                        <option value="pending">⏳ Ожидание</option>
                        <option value="paid">✅ Оплачено</option>
                        <option value="overdue">⚠️ Просрочено</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>📅 Дата оплаты</label>
                    <input type="date" name="payment_date">
                </div>
                <div class="form-group">
                    <!-- Пустое место для выравнивания -->
                </div>
            </div>
            
            <button type="submit" name="save_calc">💾 Сохранить расчет</button>
        </form>
        
        <!-- Список расчетов -->
        <h2>📋 История расчетов</h2>
        
        <?php if (empty($calculations)): ?>
            <p>Нет данных о расчетах зарплаты для этого сотрудника</p>
        <?php else: ?>
            </table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Период</th>
                        <th>База (руб.)</th>
                        <th>Ставка</th>
                        <th>Сумма (руб.)</th>
                        <th>Статус</th>
                        <th>Дата оплаты</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_amount = 0;
                    $total_base = 0;
                    foreach ($calculations as $calc): 
                        $total_amount += $calc['amount'] ?? 0;
                        $total_base += $calc['taxable_base'] ?? 0;
                    ?>
                        <tr>
                            <td><?= $calc['calc_id'] ?></td>
                            <td><?= $calc['period_year'] ?> / <?= $calc['period_month'] ?></td>
                            <td><?= number_format($calc['taxable_base'] ?? 0, 2) ?> ₽</td>
                            <td><?= number_format($calc['rate_percent'] ?? 0, 2) ?>%</td>
                            <td><strong><?= number_format($calc['amount'] ?? 0, 2) ?> ₽</strong></td>
                            <td>
                                <?php if (($calc['payment_status'] ?? 'pending') == 'pending'): ?>
                                    ⏳ Ожидание
                                <?php elseif (($calc['payment_status'] ?? '') == 'paid'): ?>
                                    ✅ Оплачено
                                <?php else: ?>
                                    ⚠️ Просрочено
                                <?php endif; ?>
                            </td>
                            <td><?= $calc['payment_date'] ?? '-' ?></td>
                            <td>
                                <a href="salary_calc.php?employee_id=<?= $employee_id ?>&delete_id=<?= $calc['calc_id'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Удалить расчет?')">🗑 Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total">
                📊 ИТОГО налогооблагаемая база: <?= number_format($total_base, 2) ?> ₽<br>
                📊 ИТОГО начислено налогов: <?= number_format($total_amount, 2) ?> ₽
            </div>
        <?php endif; ?>
        
    <?php elseif ($employee_id <= 0 && empty($error)): ?>
        <div class="info-employee" style="background: #fff3cd; color: #856404;">
            👈 Пожалуйста, выберите сотрудника из списка
        </div>
    <?php endif; ?>
    
    <hr>
    <p style="color: #666; font-size: 12px;">
        💡 Сумма рассчитывается автоматически по формуле: База × Ставка ÷ 100<br>
        📌 НДФЛ удерживается из зарплаты сотрудника (13%), остальные налоги платит работодатель
    </p>
</div>

<script>
// Автоматический расчет суммы
function calculateAmount() {
    let base = document.getElementById('taxable_base').value;
    let rate = document.getElementById('rate_percent').value;
    let amountField = document.getElementById('amount');
    
    // Если ставка не указана, используем 13
    if (!rate || parseFloat(rate) <= 0) {
        rate = 13;
        document.getElementById('rate_percent').value = 13;
    }
    
    if (base && parseFloat(base) > 0) {
        let amount = (parseFloat(base) * parseFloat(rate) / 100).toFixed(2);
        amountField.value = amount;
    } else {
        amountField.value = '';
    }
}

// Добавляем обработчики событий
document.addEventListener('DOMContentLoaded', function() {
    let baseInput = document.getElementById('taxable_base');
    let rateInput = document.getElementById('rate_percent');
    
    if (baseInput && rateInput) {
        baseInput.addEventListener('input', calculateAmount);
        rateInput.addEventListener('input', calculateAmount);
        // Вызываем расчет при загрузке
        calculateAmount();
    }
});
</script>
</body>
</html>