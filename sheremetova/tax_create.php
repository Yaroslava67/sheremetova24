<?php
require_once 'config.php';

$error = '';
$success = '';

$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

// Получаем список всех сотрудников для выпадающего списка
$all_employees = [];
try {
    $stmt = $pdo->query("SELECT employee_id, last_name, first_name, middle_name FROM employees ORDER BY last_name");
    $all_employees = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Ошибка загрузки сотрудников: " . $e->getMessage();
}

// Получаем информацию о выбранном сотруднике
$employee = null;
if ($employee_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
        $stmt->execute([$employee_id]);
        $employee = $stmt->fetch();
    } catch(PDOException $e) {
        $error = "Ошибка: " . $e->getMessage();
    }
}

// Обработка добавления взноса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id_post = (int)$_POST['employee_id'];
    $period_year = (int)$_POST['period_year'];
    $period_month = (int)$_POST['period_month'];
    $tax_type = $_POST['tax_type'];
    $taxable_base = (float)$_POST['taxable_base'];
    $rate_percent = (float)$_POST['rate_percent'];
    $amount = (float)$_POST['amount'];
    $payment_status = $_POST['payment_status'];
    $payment_date = !empty($_POST['payment_date']) ? $_POST['payment_date'] : null;
    
    if ($employee_id_post <= 0) {
        $error = "Выберите сотрудника";
    } elseif ($taxable_base <= 0) {
        $error = "Налогооблагаемая база должна быть больше 0";
    } elseif ($rate_percent <= 0) {
        $error = "Ставка должна быть больше 0";
    } else {
        try {
            $sql = "INSERT INTO tax_contributions (employee_id, period_year, period_month, tax_type, taxable_base, rate_percent, amount, payment_status, payment_date) 
                    VALUES (:employee_id, :period_year, :period_month, :tax_type, :taxable_base, :rate_percent, :amount, :payment_status, :payment_date)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':employee_id' => $employee_id_post,
                ':period_year' => $period_year,
                ':period_month' => $period_month,
                ':tax_type' => $tax_type,
                ':taxable_base' => $taxable_base,
                ':rate_percent' => $rate_percent,
                ':amount' => $amount,
                ':payment_status' => $payment_status,
                ':payment_date' => $payment_date
            ]);
            $success = "Налоговый взнос успешно добавлен!";
            
            // Перенаправляем на страницу списка взносов
            header("Location: tax_contributions.php");
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
    <title>Добавление налогового взноса</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-top: 0; }
        h2 { color: #34495e; font-size: 18px; margin-top: 25px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 10px; text-decoration: none; background: #3498db; color: white; padding: 8px 16px; border-radius: 4px; display: inline-block; }
        .nav a:hover { background: #2980b9; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input, select { width: 100%; max-width: 400px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background: #219a52; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #e74c3c; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #27ae60; }
        .info-employee { background: #e8f4fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .back { display: inline-block; margin-top: 20px; color: #3498db; text-decoration: none; }
        hr { margin: 20px 0; }
        .form-row { display: flex; flex-wrap: wrap; gap: 20px; }
        .form-row .form-group { flex: 1; min-width: 200px; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="index.php">🏠 На главную</a>
        <a href="tax_contributions.php">📑 Назад к списку взносов</a>
    </div>
    
    <h1>➕ Добавление налогового взноса</h1>
    
    <?php if ($error): ?>
        <div class="error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>👤 Сотрудник *</label>
            <select name="employee_id" required>
                <option value="">-- Выберите сотрудника --</option>
                <?php foreach ($all_employees as $emp): ?>
                    <option value="<?= $emp['employee_id'] ?>" <?= ($employee_id == $emp['employee_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['last_name'] . ' ' . $emp['first_name'] . ' ' . ($emp['middle_name'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>📅 Год *</label>
                <input type="number" name="period_year" value="<?= date('Y') ?>" min="2020" max="2030" required>
            </div>
            
            <div class="form-group">
                <label>📅 Месяц *</label>
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
        
        <div class="form-group">
            <label>📊 Тип налога *</label>
            <select name="tax_type" required>
                <option value="NDFL">💰 НДФЛ (налог на доходы физ. лиц)</option>
                <option value="PFR">🏦 ПФР (пенсионный фонд)</option>
                <option value="FSS">🩺 ФСС (социальное страхование)</option>
                <option value="FOMS">🏥 ФОМС (медицинское страхование)</option>
            </select>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>💰 Налогооблагаемая база (руб.) *</label>
                <input type="number" step="0.01" name="taxable_base" id="taxable_base" placeholder="50000" required>
            </div>
            
            <div class="form-group">
                <label>📊 Ставка (%) *</label>
                <input type="number" step="0.01" name="rate_percent" id="rate_percent" placeholder="13" required>
            </div>
            
            <div class="form-group">
                <label>💵 Сумма (руб.) *</label>
                <input type="number" step="0.01" name="amount" id="amount" placeholder="6500" readonly style="background: #f0f0f0;">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>📌 Статус оплаты</label>
                <select name="payment_status">
                    <option value="pending">⏳ Ожидание</option>
                    <option value="paid">✅ Оплачено</option>
                    <option value="overdue">⚠️ Просрочено</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>📅 Дата оплаты</label>
                <input type="date" name="payment_date">
            </div>
        </div>
        
        <button type="submit">💾 Сохранить налоговый взнос</button>
    </form>
    
    <hr>
    <a href="tax_contributions.php" class="back">← Вернуться к списку налоговых взносов</a>
</div>

<script>
// Автоматический расчет суммы
function calculateAmount() {
    let base = document.getElementById('taxable_base').value;
    let rate = document.getElementById('rate_percent').value;
    if (base && rate && parseFloat(base) > 0 && parseFloat(rate) > 0) {
        let amount = (parseFloat(base) * parseFloat(rate) / 100).toFixed(2);
        document.getElementById('amount').value = amount;
    } else {
        document.getElementById('amount').value = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let baseInput = document.getElementById('taxable_base');
    let rateInput = document.getElementById('rate_percent');
    if (baseInput && rateInput) {
        baseInput.addEventListener('input', calculateAmount);
        rateInput.addEventListener('input', calculateAmount);
    }
});
</script>
</body>
</html>