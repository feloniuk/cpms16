# 🚀 Инструкция по развертыванию IT Support Bot

## ✅ Что уже сделано

Создали базовую структуру проекта:
- ✅ Конфигурационные файлы настроены
- ✅ SQL структура базы данных готова  
- ✅ Основные классы бота написаны
- ✅ Репозитории для работы с БД созданы
- ✅ Webhook обработчик готов

## 🔧 Что нужно исправить в ваших файлах

### 1. Обновите config/telegram.php
Замените содержимое файла на исправленную версию (см. артефакт выше) - исправлен webhook_url.

### 2. Создайте недостающие файлы
Скопируйте все классы из артефактов в соответствующие папки.

## 📋 Пошаговая инструкция запуска

### Шаг 1: Импорт структуры БД
```bash
cd /var/www/html
mysql -u u442651334_main -p u442651334_main < sql/database.sql
```

### Шаг 2: Тестирование БД и создание тестовых данных
```bash
php test_db.php
```

### Шаг 3: Создание Telegram бота
1. Напишите **@BotFather** в Telegram
2. Отправьте `/newbot`
3. Укажите название: `IT Support Bot` 
4. Укажите username: `your_company_support_bot` (измените на свой)
5. Получите токен и **сохраните его!**

### Шаг 4: Настройка токена бота
Отредактируйте `config/telegram.php`:
```php
'bot_token' => 'ВАШ_ТОКЕН_ОТ_BOTFATHER',
```

### Шаг 5: Настройка webhook
```bash
php setup_webhook.php
```

### Шаг 6: Получение вашего Telegram ID

**Вариант А: Через специальных ботов**
- Напишите @userinfobot или @getmyid_bot

**Вариант Б: Через временный скрипт**
```bash
# 1. Временно перенаправьте webhook
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН/setWebhook" \
     -d "url=https://cpms16.online/get_my_id.php"

# 2. Напишите боту любое сообщение
# 3. Посмотрите ID в файле logs/telegram_ids.log
# 4. Верните webhook обратно
curl -X POST "https://api.telegram.org/botВАШ_ТОКЕН/setWebhook" \
     -d "url=https://cpms16.online/telegram-bot/webhook.php"

# 5. УДАЛИТЕ get_my_id.php!
rm get_my_id.php
```

### Шаг 7: Добавление себя как администратора
```bash
php add_admin.php ВАШ_TELEGRAM_ID "Ваше Имя"
# Пример: php add_admin.php 123456789 "Иван Петренко"
```

### Шаг 8: Тестирование бота
1. Найдите вашего бота в Telegram
2. Напишите `/start`
3. Проверьте что появилось главное меню
4. Попробуйте команду `/admin` - должна появиться админ-панель

## 📁 Структура файлов, которые нужно создать

```
/var/www/html/
├── core/
│   ├── Database.php ✅
│   └── repositories/
│       ├── BaseRepository.php ✅
│       ├── BranchRepository.php ✅
│       ├── AdminRepository.php ✅
│       └── UserStateRepository.php ✅
├── telegram-bot/
│   ├── TelegramBot.php ✅
│   ├── webhook.php ✅ (обновить)
│   └── keyboards/
│       └── Keyboards.php ✅
├── test_db.php ✅
├── setup_webhook.php ✅
├── add_admin.php ✅
├── get_my_id.php ✅ (временный)
└── DEPLOYMENT.md ✅
```

## 🔍 Отладка и логи

### Просмотр логов
```bash
# Логи webhook
tail -f logs/webhook.log

# Логи бота
tail -f logs/telegram.log

# Логи ошибок
tail -f logs/errors.log

# Очистка логов
echo "" > logs/webhook.log
echo "" > logs/telegram.log
echo "" > logs/errors.log
```

### Проверка webhook
```bash
# Информация о webhook
curl "https://api.telegram.org/botВАШ_ТОКЕН/getWebhookInfo"

# Тест доступности webhook
curl -X POST "https://cpms16.online/telegram-bot/webhook.php" \
     -H "Content-Type: application/json" \
     -d '{"test": true}'
```

## ❌ Возможные проблемы и решения

### Проблема: "Database connection failed"
**Решение:** Проверьте настройки в `config/database.php`

### Проблема: "Invalid JSON" в логах webhook
**Решение:** 
- Проверьте что webhook URL доступен
- Убедитесь что нет синтаксических ошибок в PHP

### Проблема: Бот не отвечает
**Решение:**
1. Проверьте токен бота в конфигурации
2. Проверьте webhook: `curl "https://api.telegram.org/botТОКЕН/getWebhookInfo"`
3. Посмотрите логи ошибок

### Проблема: "У вас немає прав адміністратора"
**Решение:** Добавьте свой Telegram ID в таблицу admins

## 📊 Проверка работоспособности

После запуска проверьте:

1. **База данных:**
   ```bash
   php test_db.php
   ```

2. **Webhook:**
   ```bash
   php setup_webhook.php
   ```

3. **Бот отвечает:**
   - Отправьте `/start` боту
   - Должно появиться главное меню

4. **Админ-панель:**
   - Отправьте `/admin` боту
   - Должно появиться меню администратора

## 🎯 Что дальше?

После успешного запуска можно:
1. Тестировать создание заявок на ремонт
2. Тестировать запросы на замену картриджей  
3. Добавлять других администраторов
4. Разрабатывать REST API (следующий этап)

## 🆘 Поддержка

При проблемах:
1. Проверьте логи в папке `logs/`
2. Убедитесь что все файлы созданы и имеют правильные права доступа
3. Проверьте конфигурацию БД и Telegram

**Готов помочь с настройкой на следующих этапах!** 🚀