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
        if (!$employee) {
            $error = "Сотрудник не найден";
            $employee_id = 0;
        }
    } catch(PDOException $e) {
        $error = "Ошибка: " . $e->getMessage();
    }
}

// Получаем начисления и удержания для выбранного сотрудника
$accruals = [];
if ($employee_id > 0 && $employee) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM accruals_deductions WHERE employee_id = ? ORDER BY created_at DESC");
        $stmt->execute([$employee_id]);
        $accruals = $stmt->fetchAll();
    } catch(PDOException $e) {
        $error = "Ошибка: " . $e->getMessage();
    }
}

// Добавление начисления/удержания
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $employee_id_post = (int)$_POST['employee_id'];
    $type = $_POST['type'];
    $code = $_POST['code'];
    $amount = (float)$_POST['amount'];
    $document_date = $_POST['document_date'];
    $description = $_POST['description'] ?? null;
    
    try {
        $sql = "INSERT INTO accruals_deductions (employee_id, type, code, amount, document_date, description) 
                VALUES (:employee_id, :type, :code, :amount, :document_date, :description)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':employee_id' => $employee_id_post,
            ':type' => $type,
            ':code' => $code,
            ':amount' => $amount,
            ':document_date' => $document_date,
            ':description' => $description
        ]);
        $success = "Запись добавлена";
        header("Location: accruals.php?employee_id=" . $employee_id_post);
        exit();
    } catch(PDOException $e) {
        $error = "Ошибка: " . $e->getMessage();
    }
}

// Удаление записи
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM accruals_deductions WHERE record_id = ?");
        $stmt->execute([$delete_id]);
        $success = "Запись удалена";
        header("Location: accruals.php?employee_id=$employee_id");
        exit();
    } catch(PDOException $e) {
        $error = "Ошибка удаления: " . $e->getMessage();
    }
}

// Обработка смены сотрудника через форму
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_employee'])) {
    $new_employee_id = (int)$_POST['select_employee'];
    header("Location: accruals.php?employee_id=" . $new_employee_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Начисления и удержания</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 8px; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #219a52; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #2c3e50; color: white; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 10px; text-decoration: none; background: #3498db; color: white; padding: 8px 16px; border-radius: 4px; display: inline-block; }
        .nav a:hover { background: #2980b9; }
        .accrual { color: green; font-weight: bold; }
        .deduction { color: red; font-weight: bold; }
        .error { color: red; background: #fee; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .success { color: green; background: #efe; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .btn-delete { background: #e74c3c; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .btn-delete:hover { background: #c0392b; }
        .total { margin-top: 20px; padding: 15px; background: #ecf0f1; border-radius: 5px; font-weight: bold; font-size: 18px; }
        .info-employee { background: #ecf0f1; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .employee-selector { background: #e8f4fd; padding: 15px; border-radius: 5px; margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-end; }
        .employee-selector .form-group { flex: 1; margin-bottom: 0; }
        .employee-selector button { margin-bottom: 0; width: auto; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="index.php">← На главную</a>
        <a href="index.php">Список сотрудников</a>
    </div>
    
    <h1>Начисления и удержания</h1>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <!-- Выбор сотрудника -->
    <div class="employee-selector">
        <form method="POST" style="display: flex; gap: 10px; width: 100%;">
            <div class="form-group" style="flex: 3;">
                <label>Выберите сотрудника:</label>
                <select name="select_employee" style="width: 100%;">
                    <option value="">-- Выберите сотрудника --</option>
                    <?php foreach ($all_employees as $emp): ?>
                        <option value="<?= $emp['employee_id'] ?>" <?= ($employee_id == $emp['employee_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['last_name'] . ' ' . $emp['first_name'] . ' ' . ($emp['middle_name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 0;">
                <label>&nbsp;</label>
                <button type="submit" name="change_employee">Выбрать</button>
            </div>
        </form>
    </div>
    
    <?php if ($employee && $employee_id > 0): ?>
        <div class="info-employee">
            <strong>Сотрудник:</strong> <?= htmlspecialchars($employee['last_name'] . ' ' . $employee['first_name'] . ' ' . ($employee['middle_name'] ?? '')) ?>
            <br>
            <strong>ИНН:</strong> <?= htmlspecialchars($employee['inn'] ?? '-') ?>
            <br>
            <strong>СНИЛС:</strong> <?= htmlspecialchars($employee['snils'] ?? '-') ?>
        </div>
        
        <h2>Добавить новую запись</h2>
        <form method="POST">
            <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
            
            <div class="form-group">
                <label>Тип операции *</label>
                <select name="type" required>
                    <option value="accrual">Начисление</option>
                    <option value="deduction">Удержание</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Код операции *</label>
                <input type="text" name="code" placeholder="Например: SALARY, BONUS, TAX, PENALTY" required>
            </div>
            
            <div class="form-group">
                <label>Сумма (руб.) *</label>
                <input type="number" step="0.01" name="amount" required>
            </div>
            
            <div class="form-group">
                <label>Дата документа *</label>
                <input type="date" name="document_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Описание</label>
                <textarea name="description" rows="3" placeholder="Дополнительная информация..."></textarea>
            </div>
            
            <button type="submit" name="save">Сохранить</button>
        </form>
        
        <h2>История операций</h2>
        
        <?php
        $total_accruals = 0;
        $total_deductions = 0;
        foreach ($accruals as $item) {
            if ($item['type'] == 'accrual') {
                $total_accruals += $item['amount'];
            } else {
                $total_deductions += $item['amount'];
            }
        }
        $total = $total_accruals - $total_deductions;
        ?>
        
        <?php if (empty($accruals)): ?>
            <p>Нет данных о начислениях и удержаниях</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Тип</th>
                        <th>Код</th>
                        <th>Сумма (руб.)</th>
                        <th>Дата документа</th>
                        <th>Описание</th>
                        <th>Дата создания</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accruals as $item): ?>
                        <tr>
                            <td><?= $item['record_id'] ?></td>
                            <td class="<?= $item['type'] == 'accrual' ? 'accrual' : 'deduction' ?>">
                                <?= $item['type'] == 'accrual' ? '➕ Начисление' : '➖ Удержание' ?>
                            </td>
                            <td><?= htmlspecialchars($item['code']) ?></td>
                            <td><?= number_format($item['amount'], 2) ?> ₽</td>
                            <td><?= $item['document_date'] ?? '-' ?></td>
                            <td><?= htmlspecialchars($item['description'] ?? '-') ?></td>
                            <td><?= $item['created_at'] ?? '-' ?></td>
                            <td>
                                <a href="accruals.php?employee_id=<?= $employee_id ?>&delete_id=<?= $item['record_id'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Удалить запись?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total">
                📊 ИТОГО начислений: <?= number_format($total_accruals, 2) ?> ₽<br>
                📊 ИТОГО удержаний: <?= number_format($total_deductions, 2) ?> ₽<br>
                💰 ИТОГО к выплате: <?= number_format($total, 2) ?> ₽
            </div>
        <?php endif; ?>
        
    <?php elseif ($employee_id <= 0 && !$error): ?>
        <div class="info-employee" style="background: #fff3cd; color: #856404;">
            <p>👈 Пожалуйста, выберите сотрудника из списка слева</p>
        </div>
    <?php endif; ?>
    
</div>
</body>
</html>