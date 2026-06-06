<?php
define('SIMULATOR_URL', 'http://localhost:4444/TransferSimulator/');
define('LOG_FILE', 'validation_log.json');
define('TEST_CASES_FILE', 'test_cases.json');

$result = null;
$generatedValue = null;
$type = null;
$isValid = false;
$errorMessage = "";
$simulatorError = false;

$testTypes = [
    'mobilePhone'  => ['label' => 'Проверка Телефона']
];

function logValidationResult($type, $receivedValue, $isValid, $errorMessage = '', $simulatorError = false) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'received_value' => $receivedValue,
        'is_valid' => $isValid,
        'error_message' => $errorMessage,
        'simulator_error' => $simulatorError
    ];

    $existing = [];
    if (file_exists(LOG_FILE)) {
        $json = file_get_contents(LOG_FILE);
        $existing = json_decode($json, true) ?? [];
    }
    $existing[] = $logEntry;
    if (count($existing) > 50) array_shift($existing);
    file_put_contents(LOG_FILE, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $logEntry;
}

function saveTestCase($action, $expectedResult, $actualResult, $status) {
    $testCases = [];
    if (file_exists(TEST_CASES_FILE)) {
        $testCases = json_decode(file_get_contents(TEST_CASES_FILE), true) ?? [];
    }
    
    $testCases[] = [
        'id' => uniqid(),
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'expected_result' => $expectedResult,
        'actual_result' => $actualResult,
        'status' => $status
    ];
    
    if (count($testCases) > 100) array_shift($testCases);
    file_put_contents(TEST_CASES_FILE, json_encode($testCases, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getTestCases() {
    if (!file_exists(TEST_CASES_FILE)) return [];
    return json_decode(file_get_contents(TEST_CASES_FILE), true) ?? [];
}

function getLastLogs($limit = 10) {
    if (!file_exists(LOG_FILE)) return [];
    $json = file_get_contents(LOG_FILE);
    $logs = json_decode($json, true) ?? [];
    return array_slice(array_reverse($logs), 0, $limit);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $action = $_POST['action'] ?? 'validate';
    
    if ($action === 'clear_test_cases') {
        if (file_exists(TEST_CASES_FILE)) unlink(TEST_CASES_FILE);
        if (file_exists(LOG_FILE)) unlink(LOG_FILE);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if (array_key_exists($type, $testTypes)) {
        $url = SIMULATOR_URL . $type;

        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $response = @file_get_contents($url, false, $context);

        if ($response !== false) {
            $data = json_decode($response, true);
            $generatedValue = $data['value'] ?? 'Неизвестно';
            $simulatorError = false;

            switch ($type) {
                case 'mobilePhone':
                    $isValid = preg_match('/^(\+7|8)\d{10}$/', $generatedValue);
                    $errorMessage = "Неверный формат мобильного телефона. Ожидается +7XXXXXXXXXX или 8XXXXXXXXXX (10 цифр после кода)";
                    break;
            }

            $actionText = "Проверка номера мобильного телефона";
            $expectedText = "Номер должен соответствовать формату +7XXXXXXXXXX или 8XXXXXXXXXX (10 цифр после кода)";
            $actualText = $generatedValue;
            $statusText = $isValid ? "Успешно" : "Не успешно";
            
            saveTestCase($actionText, $expectedText, $actualText, $statusText);
            logValidationResult($type, $generatedValue, $isValid, $isValid ? '' : $errorMessage, false);
            $result = true;
        } else {
            $simulatorError = true;
            logValidationResult($type, '', false, 'Симулятор недоступен', true);
            
            saveTestCase(
                "Проверка номера мобильного телефона",
                "Симулятор должен быть доступен и возвращать корректные данные",
                "Ошибка подключения к симулятору на порту 4444",
                "Не успешно"
            );
            $result = false;
        }
    }
}

$lastLogs = getLastLogs();
$testCases = getTestCases();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Валидация данных с тест-кейсами</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .test-case-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        .test-case-table th,
        .test-case-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }
        .test-case-table th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        .test-case-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-success {
            color: #28a745;
            font-weight: bold;
        }
        .status-failed {
            color: #dc3545;
            font-weight: bold;
        }
        .clear-btn {
            background-color: #dc3545;
            margin-left: 10px;
        }
        .export-btn {
            background-color: #17a2b8;
            margin-left: 10px;
        }
        .pdf-btn {
            background-color: #dc3545;
            margin-left: 10px;
        }
        .pdf-btn:hover {
            background-color: #c82333;
        }
        .button-group {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .pdf-content {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .pdf-content h1 {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 20px;
        }
        .pdf-content .date {
            text-align: right;
            color: #666;
            margin-bottom: 20px;
        }
        .pdf-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .pdf-content th,
        .pdf-content td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .pdf-content th {
            background-color: #4CAF50;
            color: white;
        }
        .pdf-content .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        .pdf-content .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .save-result {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .save-result.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .save-result.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<div class="container">
    <p><a href="admin.php">← Назад</a></p>
    <h1>Валидация данных и тест-кейсы</h1>

    <?php if ($result === false && $simulatorError && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="error">
            ⚠️ Внимание! Не удалось подключиться к TransferSimulator.<br>
            Проверьте, запущена ли Java-программа в фоне на порту 4444.
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="post">
            <label>Выберите тип данных для проверки:</label>
            <select name="type" required>
                <?php foreach ($testTypes as $key => $values): ?>
                    <option value="<?= $key ?>" <?= $type === $key ? 'selected' : '' ?>>
                        <?= $values['label'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="action" value="validate">Получить данные</button>
        </form>
        
        <div class="button-group">
            <form method="post" onsubmit="return confirm('Очистить все тест-кейсы?');">
                <button type="submit" name="action" value="clear_test_cases" class="clear-btn">Очистить тест-кейсы</button>
            </form>
        </div>
    </div>

    <?php if ($result === true && !$simulatorError): ?>
        <div class="validation-block">
            <h2>📡 Ответ от симулятора</h2>
            <div class="simulator-value">
                <?= htmlspecialchars($generatedValue) ?>
            </div>

            
        </div>
    <?php endif; ?>

    <!-- Скрытый блок для PDF экспорта -->
    <div id="pdfExportContent" style="display: none;">
        <div class="pdf-content">
            <h1>Отчёт о валидации данных</h1>
            <div class="date">Дата формирования: <?= date('d.m.Y H:i:s') ?></div>
            
            <?php if (!empty($testCases)): ?>
                <p><strong>Всего тест-кейсов:</strong> <?= count($testCases) ?></p>
                <table>
                    <thead>
                        <tr>
                            <th>№ п/п</th>
                            <th>Дата/Время</th>
                            <th>Действие</th>
                            <th>Ожидаемый результат</th>
                            <th>Фактический результат</th>
                            <th>Результат</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($testCases) as $index => $testCase): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($testCase['timestamp']) ?></td>
                            <td><?= htmlspecialchars($testCase['action']) ?></td>
                            <td><?= htmlspecialchars($testCase['expected_result']) ?></td>
                            <td><?= htmlspecialchars($testCase['actual_result']) ?></td>
                            <td style="<?= $testCase['status'] === 'Успешно' ? 'color: green; font-weight: bold;' : 'color: red; font-weight: bold;' ?>">
                                <?= htmlspecialchars($testCase['status']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php 
                $successCount = 0;
                $failedCount = 0;
                foreach ($testCases as $tc) {
                    if ($tc['status'] === 'Успешно') $successCount++;
                    else $failedCount++;
                }
                ?>
                <div class="summary" style="margin-top: 20px; padding: 10px; background-color: #f0f0f0;">
                    <strong>Статистика:</strong><br>
                  Успешно: <?= $successCount ?><br>
                 Не успешно: <?= $failedCount ?><br>
                  Процент успеха: <?= $successCount > 0 ? round(($successCount / count($testCases)) * 100, 2) : 0 ?>%
                </div>
            <?php else: ?>
                <p>Нет сохраненных тест-кейсов.</p>
            <?php endif; ?>
            
            <div class="footer">
                * Отчёт сгенерирован автоматически системой валидации данных.<br>
                ** Данные получены от TransferSimulator на порту 4444.
            </div>
        </div>
    </div>

    <!-- Таблица тест-кейсов -->
    <hr>
    <h2>📋 Тест-кейсы</h2>
    
    <?php if (!empty($testCases)): ?>
        <button id="savePdfBtn" class="pdf-btn">💾 Сохранить отчёт в PDF</button>
        <div id="saveResult" class="save-result"></div>
    <?php endif; ?>
    
    <?php if (empty($testCases)): ?>
        <p>Нет сохраненных тест-кейсов. Выполните проверку, чтобы создать тест-кейс.</p>
    <?php else: ?>
        <table class="test-case-table" id="testCaseTable">
            <thead>
                <tr>
                    <th>№ п/п</th>
                    <th>Дата/Время</th>
                    <th>Действие</th>
                    <th>Ожидаемый результат</th>
                    <th>Фактический результат</th>
                    <th>Результат (Успешно/Не успешно)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($testCases) as $index => $testCase): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($testCase['timestamp']) ?></td>
                    <td><?= htmlspecialchars($testCase['action']) ?></td>
                    <td><?= htmlspecialchars($testCase['expected_result']) ?></td>
                    <td><?= htmlspecialchars($testCase['actual_result']) ?></td>
                    <td class="<?= $testCase['status'] === 'Успешно' ? 'status-success' : 'status-failed' ?>">
                        <?= htmlspecialchars($testCase['status']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php 
        $successCount = 0;
        $failedCount = 0;
        foreach ($testCases as $tc) {
            if ($tc['status'] === 'Успешно') $successCount++;
            else $failedCount++;
        }
        ?>
        <div class="stats" style="margin-top: 10px; padding: 10px; background-color: #f5f5f5; border-radius: 4px;">
            <strong>Статистика:</strong> 
          Успешно: <?= $successCount ?> | 
           Не успешно: <?= $failedCount ?> | 
          Процент успеха: <?= $successCount > 0 ? round(($successCount / count($testCases)) * 100, 2) : 0 ?>%
        </div>
        
        <div class="small" style="margin-top: 10px; color: #666;">
            * Тест-кейсы сохраняются в файл <code><?= TEST_CASES_FILE ?></code><br>
            * Всего сохранено: <?= count($testCases) ?> тест-кейсов
        </div>
    <?php endif; ?>

    <?php if (!empty($lastLogs)): ?>
        <hr>
        <h3>📋 История проверок (JSON-лог)</h3>
        <table class="log-table">
            <thead>
                <tr><th>Время</th><th>Тип</th><th>Полученное значение</th><th>Статус</th></tr>
            </thead>
            <tbody>
            <?php foreach ($lastLogs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['timestamp']) ?></td>
                    <td><?= htmlspecialchars($log['type']) ?></td>
                    <td style="font-family: monospace;"><?= htmlspecialchars(mb_substr($log['received_value'], 0, 80)) ?></td>
                    <td>
                        <?php if ($log['simulator_error']): ?>
                            <span class="badge-secondary">Симулятор недоступен</span>
                        <?php elseif ($log['is_valid']): ?>
                            <span class="badge-success">Валидно</span>
                        <?php else: ?>
                            <span class="badge-danger">Невалидно</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="small">* Полный лог хранится в файле <code><?= LOG_FILE ?></code></div>
    <?php endif; ?>
</div>

<script>
function exportTestCases() {
    const table = document.getElementById('testCaseTable');
    if (!table || table.querySelectorAll('tbody tr').length === 0) {
        alert('Нет данных для экспорта');
        return;
    }
    
    const date = new Date().toLocaleString('ru-RU');
    
    const tableClone = table.cloneNode(true);
    tableClone.removeAttribute('class');
    tableClone.removeAttribute('id');
    
    const style = `
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 2cm;
                font-size: 12pt;
            }
            h1 { 
                color: #4CAF50;
                font-size: 24pt;
                text-align: center;
                margin-bottom: 20px;
            }
            table { 
                border-collapse: collapse; 
                width: 100%;
                margin: 20px 0;
            }
            th, td { 
                border: 1px solid #000000;
                padding: 8px;
                text-align: left;
                vertical-align: top;
            }
            th { 
                background-color: #4CAF50;
                color: #ffffff;
                font-weight: bold;
            }
            .status-success {
                color: #008000;
                font-weight: bold;
            }
            .status-failed {
                color: #FF0000;
                font-weight: bold;
            }
            .footer {
                margin-top: 50px;
                font-size: 10pt;
                text-align: center;
                color: #666666;
            }
        </style>
    `;
    
    const htmlContent = `<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Тест-кейсы валидации данных</title>
        ${style}
    </head>
    <body>
        <h1>Тест-кейсы валидации данных</h1>
        
        <div style="margin-bottom: 20px;">
            <strong>Дата формирования отчета:</strong> ${date}<br>
            <strong>Всего тест-кейсов:</strong> ${tableClone.querySelectorAll('tbody tr').length}
        </div>
        
        ${tableClone.outerHTML}
        
        <div class="footer">
            <p>Документ сгенерирован автоматически системой валидации</p>
        </div>
    </body>
    </html>`;
    
    const blob = new Blob([htmlContent], { type: 'application/msword' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.href = url;
    link.download = 'test_cases_' + new Date().toISOString().slice(0,19).replace(/:/g, '-') + '.doc';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// PDF сохранение
document.getElementById('savePdfBtn')?.addEventListener('click', function() {
    const element = document.getElementById('pdfExportContent');
    const btn = this;
    const resultDiv = document.getElementById('saveResult');
    
    btn.disabled = true;
    resultDiv.innerHTML = '<p>⏳ Генерация PDF и отправка на сервер...</p>';
    resultDiv.className = 'save-result';
    
    // Показываем блок для экспорта
    element.style.display = 'block';
    
    const opt = {
        margin: [0.5, 0.5, 0.5, 0.5],
        filename: 'temp.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    
    html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
        // Скрываем блок обратно
        element.style.display = 'none';
        
        // Отправляем Blob на сервер
        const formData = new FormData();
        formData.append('pdf_file', pdfBlob, 'validation_report_<?= date('Y-m-d_H-i-s') ?>.pdf');
        
        return fetch('save_pdf.php', {
            method: 'POST',
            body: formData
        });
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (data.success) {
            resultDiv.innerHTML = `<p>PDF сохранён на сервере: <a href="${data.file}" target="_blank">${data.file}</a></p>`;
            resultDiv.className = 'save-result success';
        } else {
            resultDiv.innerHTML = `<p>Ошибка: ${data.error}</p>`;
            resultDiv.className = 'save-result error';
        }
    }).catch(function(error) {
        console.error(error);
        resultDiv.innerHTML = '<p>❌ Ошибка при создании или отправке PDF</p>';
        resultDiv.className = 'save-result error';
        element.style.display = 'none';
    }).finally(function() {
        btn.disabled = false;
    });
});
</script>

</body>
</html>