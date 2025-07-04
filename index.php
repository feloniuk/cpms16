<?php
// IT Support Bot - Главная страница
// Показывается при переходе на https://cpms16.online/

$config = require __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['app']['name']; ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .status-card {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .status-card.warning {
            background: #fff3cd;
            border-color: #ffc107;
        }
        .status-card.error {
            background: #f8d7da;
            border-color: #dc3545;
        }
        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .action-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s;
        }
        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .action-card h3 {
            margin-top: 0;
            color: #495057;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn.danger {
            background: #dc3545;
        }
        .btn.danger:hover {
            background: #c82333;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 <?php echo $config['app']['name']; ?></h1>
            <p>Система управління IT підтримкою</p>
            <p><strong>Версія:</strong> <?php echo $config['app']['version']; ?></p>
        </div>

        <?php
        // Проверка состояния системы
        $systemStatus = [];
        
        try {
            require_once __DIR__ . '/core/Database.php';
            $db = Database::getInstance();
            $systemStatus['database'] = 'OK';
        } catch (Exception $e) {
            $systemStatus['database'] = 'ERROR: ' . $e->getMessage();
        }
        
        // Проверка конфигурации Telegram
        $telegramConfig = require __DIR__ . '/config/telegram.php';
        if ($telegramConfig['bot_token'] === 'YOUR_BOT_TOKEN_HERE') {
            $systemStatus['telegram'] = 'NOT_CONFIGURED';
        } else {
            $systemStatus['telegram'] = 'CONFIGURED';
        }
        
        // Проверка webhook
        $webhookFile = __DIR__ . '/telegram-bot/webhook.php';
        if (file_exists($webhookFile)) {
            $systemStatus['webhook'] = 'OK';
        } else {
            $systemStatus['webhook'] = 'MISSING';
        }
        ?>

        <div class="status-card <?php echo ($systemStatus['database'] === 'OK') ? '' : 'error'; ?>">
            <h3>📊 Стан бази даних</h3>
            <p><?php echo $systemStatus['database']; ?></p>
        </div>

        <div class="status-card <?php echo ($systemStatus['telegram'] === 'CONFIGURED') ? '' : 'warning'; ?>">
            <h3>🤖 Конфігурація Telegram</h3>
            <p><?php echo ($systemStatus['telegram'] === 'CONFIGURED') ? 'Налаштовано' : 'Потрібне налаштування'; ?></p>
        </div>

        <div class="status-card <?php echo ($systemStatus['webhook'] === 'OK') ? '' : 'error'; ?>">
            <h3>🔗 Webhook</h3>
            <p><?php echo ($systemStatus['webhook'] === 'OK') ? 'Файл існує' : 'Файл відсутній'; ?></p>
        </div>

        <div class="info">
            <strong>💡 Корисна інформація:</strong><br>
            • Webhook URL: <code><?php echo $config['app']['base_url']; ?>telegram-bot/webhook.php</code><br>
            • API URL: <code><?php echo $config['app']['api_url']; ?></code><br>
            • Часовий пояс: <code><?php echo $config['app']['timezone']; ?></code>
        </div>

        <div class="actions">
            <div class="action-card">
                <h3>🔧 Тестування БД</h3>
                <p>Перевірка підключення до бази даних та створення тестових даних</p>
                <a href="test_db.php" class="btn">Запустити тест</a>
            </div>

            <div class="action-card">
                <h3>🌐 Налаштування Webhook</h3>
                <p>Конфігурація webhook для Telegram бота</p>
                <a href="setup_webhook.php" class="btn">Налаштувати</a>
            </div>

            <div class="action-card">
                <h3>👤 Додати адміністратора</h3>
                <p>Додавання нового адміністратора через командний рядок</p>
                <p><small>Використайте: <code>php add_admin.php TELEGRAM_ID "ІМ'Я"</code></small></p>
            </div>

            <div class="action-card">
                <h3>🆔 Отримати Telegram ID</h3>
                <p>Тимчасовий скрипт для отримання вашого Telegram ID</p>
                <a href="get_my_id.php" class="btn">Переглянути</a>
                <p><small><strong>Видаліть після використання!</strong></small></p>
            </div>
        </div>

        <?php if ($systemStatus['database'] === 'OK'): ?>
        <div class="info">
            <strong>📈 Швидка статистика:</strong><br>
            <?php
            try {
                $stmt = $db->query("SELECT COUNT(*) as count FROM branches");
                $branches = $stmt->fetch()['count'];
                echo "• Філії: $branches<br>";
                
                $stmt = $db->query("SELECT COUNT(*) as count FROM admins WHERE is_active = 1");
                $admins = $stmt->fetch()['count'];
                echo "• Активні адміністратори: $admins<br>";
                
                $stmt = $db->query("SELECT COUNT(*) as count FROM repair_requests");
                $repairs = $stmt->fetch()['count'];
                echo "• Заявки на ремонт: $repairs<br>";
                
            } catch (Exception $e) {
                echo "Помилка отримання статистики";
            }
            ?>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>Розроблено для автоматизації IT підтримки</p>
            <p>Документація: <a href="README.md">README.md</a> | <a href="DEPLOYMENT.md">DEPLOYMENT.md</a></p>
        </div>
    </div>
</body>
</html>