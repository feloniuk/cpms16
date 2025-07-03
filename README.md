# Техническое задание
## Система управления IT поддержкой с Telegram ботом

**Версия:** 1.0  
**Дата:** 01.07.2025  
**Статус:** В разработке

---

## 1. ОБЩИЕ СВЕДЕНИЯ

### 1.1 Наименование системы
Система управления IT поддержкой с Telegram ботом (IT Support Management System)

### 1.2 Назначение системы
Автоматизация процессов подачи заявок на техническое обслуживание, учета замен картриджей, инвентаризации оборудования и генерации отчетности через Telegram бота с возможностью дальнейшей интеграции с веб-приложением.

### 1.3 Цели разработки
- Упростить процесс подачи заявок на ремонт оборудования
- Автоматизировать учет замен картриджей
- Создать централизованную систему инвентаризации
- Обеспечить оперативное уведомление администраторов
- Создать масштабируемую архитектуру для дальнейшего развития

### 1.4 Область применения
Организации с распределенной IT инфраструктурой, имеющие несколько филиалов и требующие централизованного управления техническими заявками.

---

## 2. ТРЕБОВАНИЯ К СИСТЕМЕ

### 2.1 Функциональные требования

#### 2.1.1 Модуль "Заявки на ремонт"
**FR-001:** Пользователь должен иметь возможность создать заявку на ремонт оборудования
- Выбор филиала из предустановленного списка
- Указание номера кабинета (текстовое поле)
- Ввод описания неисправности (текстовое поле, макс. 1000 символов)
- Необязательное указание номера телефона для связи
- Автоматическое сохранение даты и времени подачи заявки
- Автоматическое присвоение статуса "новая"

**FR-002:** Администратор должен получать уведомления о новых заявках
- Мгновенное уведомление в Telegram при создании заявки
- Уведомление должно содержать: филиал, кабинет, описание, пользователя, телефон
- Кнопка быстрого перехода к управлению заявкой

**FR-003:** Администратор должен иметь возможность управлять заявками
- Просмотр списка всех заявок с фильтрацией по статусу
- Изменение статуса заявки (новая → в_работе → выполнена)
- Просмотр детальной информации по заявке
- Поиск заявок по филиалу, кабинету, дате

#### 2.1.2 Модуль "Замена картриджей"
**FR-004:** Пользователь должен иметь возможность запросить замену картриджа
- Выбор филиала и указание номера кабинета
- Поиск принтера по инвентарному или серийному номеру
- Выбор принтера из результатов поиска или ввод информации вручную
- Указание типа картриджа
- Автоматическое сохранение даты запроса

**FR-005:** Система должна вести учет замен картриджей
- Сохранение истории всех замен
- Привязка замен к конкретному оборудованию (если найдено в инвентаре)
- Отчеты по заменам за период, по филиалам, по оборудованию

#### 2.1.3 Модуль "Инвентаризация" (только для администраторов)
**FR-006:** Администратор должен иметь возможность проводить инвентаризацию кабинетов
- Выбор филиала и номера кабинета
- Добавление оборудования с использованием шаблонов или создание нового
- Обязательные поля: тип оборудования, бренд, модель, инвентарный номер
- Дополнительные поля: серийный номер (обязателен для принтеров), заметки
- Возможность быстрого добавления нескольких единиц одного типа

**FR-007:** Система должна поддерживать шаблоны оборудования
- Создание шаблонов с предустановленными параметрами
- Указание обязательности серийного номера для типа оборудования
- Быстрый выбор из существующих шаблонов при инвентаризации

**FR-008:** Система должна обеспечивать поиск по инвентарю
- Поиск по инвентарному номеру
- Поиск по серийному номеру
- Поиск по филиалу и кабинету
- Поиск по типу оборудования
- Комбинированные фильтры

#### 2.1.4 Модуль "Отчетность" (только для администраторов)
**FR-009:** Система должна генерировать отчеты
- Отчет по инвентарю по филиалам
- Отчет по инвентарю по кабинетам
- Отчет по заявкам за период
- Отчет по заменам картриджей за период
- Экспорт отчетов в текстовом формате для Telegram

#### 2.1.5 Модуль "Администрирование"
**FR-010:** Система должна поддерживать управление филиалами
- Добавление новых филиалов
- Редактирование названий филиалов
- Деактивация филиалов (скрытие из списков без удаления данных)

**FR-011:** Система должна поддерживать управление администраторами
- Добавление администраторов по Telegram ID
- Удаление администраторов
- Просмотр списка активных администраторов

### 2.2 Нефункциональные требования

#### 2.2.1 Требования к производительности
**NFR-001:** Время отклика API не должно превышать 2 секунд для 95% запросов
**NFR-002:** Система должна поддерживать до 100 одновременных пользователей
**NFR-003:** Telegram бот должен отвечать на сообщения в течение 3 секунд

#### 2.2.2 Требования к надежности
**NFR-004:** Доступность системы должна составлять не менее 99% времени
**NFR-005:** Все критические операции должны логироваться
**NFR-006:** Система должна автоматически восстанавливаться после сбоев

#### 2.2.3 Требования к безопасности
**NFR-007:** Все API endpoints должны быть защищены аутентификацией
**NFR-008:** Доступ к административным функциям только по whitelist Telegram ID
**NFR-009:** Все входящие данные должны проходить валидацию
**NFR-010:** SQL инъекции должны быть исключены использованием prepared statements

#### 2.2.4 Требования к масштабируемости
**NFR-011:** Архитектура должна поддерживать добавление новых интерфейсов (веб, мобильный)
**NFR-012:** База данных должна поддерживать горизонтальное масштабирование
**NFR-013:** API должен быть RESTful и документированным

---

## 3. ТЕХНИЧЕСКАЯ АРХИТЕКТУРА

### 3.1 Общая архитектура системы

```
┌─────────────────────────────────────────────────────────────────┐
│                        PRESENTATION LAYER                       │
├─────────────────┬─────────────────┬─────────────────────────────┤
│  Telegram Bot   │  Laravel Web    │    Future Mobile App        │
│   (Phase 1)     │   (Phase 2)     │       (Phase 3)             │
└─────────┬───────┴─────────┬───────┴─────────┬───────────────────┘
          │                 │                 │
          │ HTTP/cURL       │ HTTP/Axios      │ HTTP/REST
          │                 │                 │
┌─────────▼─────────────────▼─────────────────▼───────────────────┐
│                     APPLICATION LAYER                           │
├─────────────────────────────────────────────────────────────────┤
│                       REST API                                  │
│  ┌─────────────┬──────────────┬───────────────┬──────────────┐  │
│  │ Controllers │  Middleware  │   Services    │     Auth     │  │
│  └─────────────┴──────────────┴───────────────┴──────────────┘  │
└─────────────────────────┬───────────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────────┐
│                     BUSINESS LAYER                              │
├─────────────────────────────────────────────────────────────────┤
│               Core Business Logic Services                      │
│  ┌─────────────┬──────────────┬───────────────┬──────────────┐  │
│  │   Models    │ Repositories │   Services    │ Validators   │  │
│  └─────────────┴──────────────┴───────────────┴──────────────┘  │
└─────────────────────────┬───────────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────────┐
│                      DATA LAYER                                 │
├─────────────────────────────────────────────────────────────────┤
│                    MySQL Database                               │
│              (Centralized Data Storage)                         │
└─────────────────────────────────────────────────────────────────┘
```

### 3.2 Структура директорий

```
/var/www/html/it-support/
├── config/                          # Конфигурационные файлы
│   ├── config.php                  # Основные настройки приложения
│   ├── database.php                # Параметры подключения к БД
│   └── telegram.php                # Настройки Telegram API
├── core/                           # Бизнес-логика (переиспользуемая)
│   ├── models/                     # Модели данных
│   │   ├── Admin.php
│   │   ├── Branch.php
│   │   ├── RepairRequest.php
│   │   ├── Inventory.php
│   │   ├── InventoryTemplate.php
│   │   └── CartridgeReplacement.php
│   ├── services/                   # Бизнес-сервисы
│   │   ├── AdminService.php
│   │   ├── BranchService.php
│   │   ├── RepairService.php
│   │   ├── InventoryService.php
│   │   ├── CartridgeService.php
│   │   ├── NotificationService.php
│   │   └── ReportService.php
│   └── repositories/               # Репозитории для работы с БД
│       ├── BaseRepository.php
│       ├── AdminRepository.php
│       ├── BranchRepository.php
│       ├── RepairRepository.php
│       ├── InventoryRepository.php
│       └── CartridgeRepository.php
├── api/                            # REST API
│   ├── index.php                   # Роутер и точка входа
│   ├── auth/                       # Аутентификация
│   │   ├── ApiAuthService.php
│   │   └── JwtAuthService.php
│   ├── middleware/                 # Промежуточное ПО
│   │   ├── ApiAuthMiddleware.php
│   │   ├── AdminMiddleware.php
│   │   └── CorsMiddleware.php
│   ├── controllers/                # Контроллеры API
│   │   ├── AuthController.php
│   │   ├── BranchController.php
│   │   ├── RepairController.php
│   │   ├── InventoryController.php
│   │   ├── CartridgeController.php
│   │   └── AdminController.php
│   └── utils/
│       └── HttpResponse.php        # Утилиты для HTTP ответов
├── telegram-bot/                   # Telegram Bot
│   ├── webhook.php                 # Webhook endpoint
│   ├── TelegramBot.php            # Основной класс бота
│   ├── handlers/                   # Обработчики команд
│   │   ├── RepairHandler.php
│   │   ├── InventoryHandler.php
│   │   ├── CartridgeHandler.php
│   │   └── AdminHandler.php
│   ├── keyboards/                  # Клавиатуры и меню
│   │   └── Keyboards.php
│   └── utils/
│       └── ApiClient.php          # HTTP клиент для API запросов
├── sql/                           # SQL скрипты
│   ├── database.sql               # Структура БД
│   └── sample_data.sql            # Тестовые данные
├── logs/                          # Логи
│   ├── api.log
│   ├── telegram.log
│   └── errors.log
├── docs/                          # Документация
│   ├── api_endpoints.md
│   └── bot_commands.md
└── README.md
```

### 3.3 Технологический стек

**Backend:**
- **Язык:** PHP 7.4+
- **База данных:** MySQL 8.0+
- **Web-сервер:** Apache/Nginx
- **HTTP клиент:** cURL (встроенный в PHP)

**Telegram API:**
- **Библиотека:** Longman/telegram-bot или собственная реализация на cURL
- **Метод получения обновлений:** Webhook

**Безопасность:**
- **API аутентификация:** Bearer tokens
- **JWT:** Для будущего веб-приложения
- **Валидация:** Собственные валидаторы

**Логирование:**
- **Формат:** JSON
- **Ротация:** По размеру (10MB) и времени (30 дней)

---

## 4. СТРУКТУРА БАЗЫ ДАННЫХ

### 4.1 Логическая модель данных

```
┌──────────────┐    ┌─────────────────┐    ┌────────────────┐
│    admins    │    │    branches     │    │ repair_requests│
├──────────────┤    ├─────────────────┤    ├────────────────┤
│ id (PK)      │    │ id (PK)         │    │ id (PK)        │
│ telegram_id  │    │ name            │    │ user_telegram_id│
│ name         │    │ is_active       │◄───┤ branch_id (FK) │
│ is_active    │    │ created_at      │    │ room_number    │
│ created_at   │    └─────────────────┘    │ description    │
└──────────────┘                           │ phone          │
                                           │ status         │
                                           │ created_at     │
                                           │ updated_at     │
                                           └────────────────┘

┌─────────────────────┐    ┌──────────────────┐
│ inventory_templates │    │ room_inventory   │
├─────────────────────┤    ├──────────────────┤
│ id (PK)             │    │ id (PK)          │
│ name                │◄───┤ template_id (FK) │
│ equipment_type      │    │ admin_telegram_id│
│ brand               │    │ branch_id (FK)   │◄─┐
│ model               │    │ room_number      │  │
│ requires_serial     │    │ equipment_type   │  │
│ requires_inventory  │    │ brand            │  │
│ created_at          │    │ model            │  │
└─────────────────────┘    │ serial_number    │  │
                           │ inventory_number │  │
                           │ notes            │  │
                           │ created_at       │  │
                           └──────────────────┘  │
                                                 │
┌──────────────────────┐                        │
│ cartridge_replacements│                       │
├──────────────────────┤                        │
│ id (PK)              │                        │
│ user_telegram_id     │                        │
│ username             │                        │
│ branch_id (FK)       │◄───────────────────────┘
│ room_number          │
│ printer_inventory_id │◄─┐
│ printer_info         │  │
│ cartridge_type       │  │
│ replacement_date     │  │
│ notes                │  │
│ created_at           │  │
└──────────────────────┘  │
                          │
                          │ (Связь с принтерами
                          │  в room_inventory)
                          └─ equipment_type = 'принтер'

┌──────────────┐    ┌─────────────┐
│ api_tokens   │    │ user_states │
├──────────────┤    ├─────────────┤
│ id (PK)      │    │ telegram_id │
│ name         │    │ current_state│
│ token        │    │ temp_data   │
│ permissions  │    │ updated_at  │
│ is_active    │    └─────────────┘
│ created_at   │
└──────────────┘
```

### 4.2 Физическая структура таблиц

#### 4.2.1 Таблица admins
```sql
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telegram_id BIGINT NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_telegram_id (telegram_id),
    INDEX idx_is_active (is_active)
);
```

#### 4.2.2 Таблица branches
```sql
CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
);
```

#### 4.2.3 Таблица repair_requests
```sql
CREATE TABLE repair_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_telegram_id BIGINT NOT NULL,
    username VARCHAR(255) NULL,
    branch_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    phone VARCHAR(20) NULL,
    status ENUM('новая', 'в_работе', 'выполнена') DEFAULT 'новая',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    INDEX idx_user_telegram_id (user_telegram_id),
    INDEX idx_branch_id (branch_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
```

#### 4.2.4 Таблица inventory_templates
```sql
CREATE TABLE inventory_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    equipment_type VARCHAR(100) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    requires_serial TINYINT(1) DEFAULT 0,
    requires_inventory TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_equipment_type (equipment_type)
);
```

#### 4.2.5 Таблица room_inventory
```sql
CREATE TABLE room_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_telegram_id BIGINT NOT NULL,
    branch_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    template_id INT NULL,
    equipment_type VARCHAR(100) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    serial_number VARCHAR(255) NULL,
    inventory_number VARCHAR(255) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (template_id) REFERENCES inventory_templates(id),
    INDEX idx_branch_room (branch_id, room_number),
    INDEX idx_serial_number (serial_number),
    INDEX idx_inventory_number (inventory_number),
    INDEX idx_equipment_type (equipment_type)
);
```

#### 4.2.6 Таблица cartridge_replacements
```sql
CREATE TABLE cartridge_replacements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_telegram_id BIGINT NOT NULL,
    username VARCHAR(255) NULL,
    branch_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    printer_inventory_id INT NULL,
    printer_info VARCHAR(500) NULL,
    cartridge_type VARCHAR(255) NOT NULL,
    replacement_date DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (printer_inventory_id) REFERENCES room_inventory(id),
    INDEX idx_user_telegram_id (user_telegram_id),
    INDEX idx_branch_id (branch_id),
    INDEX idx_replacement_date (replacement_date),
    INDEX idx_printer_inventory_id (printer_inventory_id)
);
```

#### 4.2.7 Таблица api_tokens
```sql
CREATE TABLE api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    permissions JSON NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_is_active (is_active)
);
```

#### 4.2.8 Таблица user_states
```sql
CREATE TABLE user_states (
    telegram_id BIGINT PRIMARY KEY,
    current_state VARCHAR(100) NULL,
    temp_data JSON NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 4.3 Индексы и оптимизация

**Основные индексы:**
- Все внешние ключи автоматически индексируются
- Поля для поиска (serial_number, inventory_number)
- Поля для фильтрации (status, equipment_type, is_active)
- Составные индексы для частых запросов (branch_id + room_number)

**Оптимизация запросов:**
- Использование LIMIT для пагинации
- Кеширование часто запрашиваемых данных (список филиалов)
- Индексы для сортировки по дате

---

## 5. API СПЕЦИФИКАЦИЯ

### 5.1 Общие принципы

**Базовый URL:** `{BASE_URL}/api/`  
**Формат данных:** JSON  
**Кодировка:** UTF-8  
**HTTP методы:** GET, POST, PUT, DELETE  

### 5.2 Аутентификация

**Заголовок:** `Authorization: Bearer {TOKEN}`

**Типы токенов:**
- **API Token** - для Telegram бота (статический)
- **JWT Token** - для веб-приложения (временный)

### 5.3 Стандартные HTTP коды ответов

- **200 OK** - Успешный запрос
- **201 Created** - Ресурс успешно создан
- **400 Bad Request** - Некорректные данные
- **401 Unauthorized** - Не авторизован
- **403 Forbidden** - Нет прав доступа
- **404 Not Found** - Ресурс не найден
- **422 Unprocessable Entity** - Ошибки валидации
- **500 Internal Server Error** - Внутренняя ошибка сервера

### 5.4 Структура ответов

**Успешный ответ:**
```json
{
    "success": true,
    "data": { ... },
    "message": "Операция выполнена успешно"
}
```

**Ответ с ошибкой:**
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Ошибка валидации данных",
        "details": {
            "field_name": ["Поле обязательно для заполнения"]
        }
    }
}
```

### 5.5 Endpoints

#### 5.5.1 Филиалы

**GET /api/branches**
- **Описание:** Получение списка филиалов
- **Доступ:** Публичный (только активные) / Админ (все)
- **Параметры:** `?active_only=true`
- **Ответ:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Центральный офис",
            "is_active": true,
            "created_at": "2025-07-01T10:00:00Z"
        }
    ]
}
```

**POST /api/branches** (только админы)
- **Описание:** Создание нового филиала
- **Данные:**
```json
{
    "name": "Новый филиал"
}
```

**PUT /api/branches/{id}** (только админы)
- **Описание:** Обновление филиала
- **Данные:**
```json
{
    "name": "Обновленное название",
    "is_active": false
}
```

#### 5.5.2 Заявки на ремонт

**POST /api/repair-requests**
- **Описание:** Создание заявки на ремонт
- **Доступ:** Любой пользователь с API токеном
- **Данные:**
```json
{
    "user_telegram_id": 123456789,
    "username": "john_doe",
    "branch_id": 1,
    "room_number": "205",
    "description": "Не работает принтер",
    "phone": "+380501234567"
}
```

**GET /api/repair-requests** (только админы)
- **Описание:** Получение списка заявок
- **Параметры:** `?status=новая&branch_id=1&page=1&limit=20`
- **Ответ:**
```json
{
    "success": true,
    "data": {
        "requests": [...],
        "pagination": {
            "current_page": 1,
            "total_pages": 5,
            "total_items": 87,
            "items_per_page": 20
        }
    }
}
```

**PUT /api/repair-requests/{id}** (только админы)
- **Описание:** Обновление статуса заявки
- **Данные:**
```json
{
    "status": "в_работе"
}
```

#### 5.5.3 Инвентарь

**GET /api/inventory/search**
- **Описание:** Поиск оборудования
- **Параметры:** `?query=INV123&type=inventory_number`
- **Ответ:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "equipment_type": "принтер",
            "brand": "HP",
            "model": "LaserJet Pro",
            "serial_number": "SN123456",
            "inventory_number": "INV123",
            "branch_name": "Центральный офис",
            "room_number": "205"
        }
    ]
}
```

**POST /api/inventory** (только админы)
- **Описание:** Добавление оборудования
- **Данные:**
```json
{
    "admin_telegram_id": 987654321,
    "branch_id": 1,
    "room_number": "205",
    "template_id": 1,
    "equipment_type": "принтер",
    "brand": "HP",
    "model": "LaserJet Pro",
    "serial_number": "SN123456",
    "inventory_number": "INV123",
    "notes": "Новый принтер"
}
```

#### 5.5.4 Замены картриджей

**POST /api/cartridge-replacements**
- **Описание:** Запрос замены картриджа
- **Данные:**
```json
{
    "user_telegram_id": 123456789,
    "username": "john_doe",
    "branch_id": 1,
    "room_number": "205",
    "printer_inventory_id": 1,
    "cartridge_type": "HP CF217A",
    "replacement_date": "2025-07-01",
    "notes": "Картридж закончился"
}
```

### 5.6 Валидация данных

**Общие правила:**
- Все обязательные поля должны быть заполнены
- Максимальная длина текстовых полей ограничена
- Telegram ID должен быть положительным целым числом
- Номера телефонов должны соответствовать международному формату
- Даты должны быть в формате YYYY-MM-DD
- Email адреса должны быть валидными (для будущих функций)

**Специфичные правила валидации:**

**repair_requests:**
- `description`: 10-1000 символов
- `room_number`: 1-50 символов, только буквы, цифры, дефис
- `phone`: опционально, формат +380XXXXXXXXX
- `status`: только из разрешенного списка

**room_inventory:**
- `inventory_number`: обязательно, 3-255 символов, уникальный
- `serial_number`: обязательно для принтеров, 3-255 символов
- `equipment_type`: из предопределенного списка
- `room_number`: 1-50 символов

**branches:**
- `name`: 2-255 символов, уникальное название

---

## 6. TELEGRAM BOT СПЕЦИФИКАЦИЯ

### 6.1 Архитектура бота

**Метод получения обновлений:** Webhook  
**URL webhook:** `{BASE_URL}/telegram-bot/webhook.php`  
**Библиотека:** Собственная реализация на cURL  

### 6.2 Состояния пользователей

Бот использует конечные автоматы для отслеживания состояний диалога:

```
START
  ├── repair_flow
  │   ├── awaiting_branch_selection
  │   ├── awaiting_room_number
  │   ├── awaiting_description
  │   └── awaiting_phone (optional)
  ├── cartridge_flow
  │   ├── awaiting_branch_selection
  │   ├── awaiting_room_number
  │   ├── awaiting_printer_search
  │   ├── awaiting_printer_selection
  │   └── awaiting_cartridge_type
  └── admin_flow
      ├── inventory_flow
      │   ├── awaiting_branch_selection
      │   ├── awaiting_room_number
      │   ├── awaiting_template_selection
      │   └── awaiting_equipment_details
      ├── repair_management
      ├── cartridge_history
      ├── reports_flow
      └── settings_flow
```

### 6.3 Команды бота

**Основные команды:**
- `/start` - Запуск бота и показ главного меню
- `/help` - Справочная информация
- `/cancel` - Отмена текущего действия и возврат в главное меню
- `/admin` - Переход в админ-панель (только для админов)
- `/status` - Проверка статуса бота (для отладки)

**Скрытые команды для админов:**
- `/repair_{id}` - Быстрый переход к управлению заявкой
- `/stats` - Быстрая статистика системы

### 6.4 Структура меню

#### 6.4.1 Главное меню (для всех пользователей)
```
🏠 Главное меню
├── 🔧 Вызов ИТ мастера
├── 🖨️ Замена картриджа
└── ❓ Помощь
```

#### 6.4.2 Админ-панель (только для администраторов)
```
⚙️ Админ-панель
├── 📋 Инвентаризация кабинета
├── 📝 Управление шаблонами
├── 📊 Заявки на ремонт
├── 🖨️ История картриджей
├── 🔍 Поиск по инвентарю
├── 📈 Отчеты
├── 🏢 Управление филиалами
├── 👥 Управление админами
└── 🏠 Главное меню
```

### 6.5 Пользовательские сценарии

#### 6.5.1 Сценарий "Вызов ИТ мастера"

1. **Пользователь выбирает "🔧 Вызов ИТ мастера"**
   - Состояние: `repair_awaiting_branch`
   - Отображается: Inline клавиатура со списком филиалов

2. **Пользователь выбирает филиал**
   - Состояние: `repair_awaiting_room`
   - Отображается: "Введите номер кабинета:"

3. **Пользователь вводит номер кабинета**
   - Состояние: `repair_awaiting_description`
   - Отображается: "Опишите проблему:"

4. **Пользователь описывает проблему**
   - Состояние: `repair_awaiting_phone`
   - Отображается: "Укажите номер телефона или нажмите 'Пропустить'"
   - Кнопки: ["Пропустить", "Отмена"]

5. **Пользователь указывает телефон или пропускает**
   - Создается заявка через API
   - Отправляется уведомление админам
   - Отображается подтверждение с номером заявки
   - Состояние сбрасывается

#### 6.5.2 Сценарий "Замена картриджа"

1. **Пользователь выбирает "🖨️ Замена картриджа"**
   - Состояние: `cartridge_awaiting_branch`
   - Отображается: Inline клавиатура со списком филиалов

2. **Пользователь выбирает филиал**
   - Состояние: `cartridge_awaiting_room`
   - Отображается: "Введите номер кабинета:"

3. **Пользователь вводит номер кабинета**
   - Состояние: `cartridge_awaiting_printer_search`
   - Отображается: "Введите инвентарный или серийный номер принтера:"

4. **Пользователь вводит номер для поиска**
   - Поиск принтера через API
   - Если найден: отображается список найденных принтеров
   - Если не найден: предложение ввести информацию вручную
   - Состояние: `cartridge_awaiting_printer_selection`

5. **Пользователь выбирает принтер или вводит вручную**
   - Состояние: `cartridge_awaiting_cartridge_type`
   - Отображается: "Укажите тип картриджа:"

6. **Пользователь указывает тип картриджа**
   - Создается запись о замене через API
   - Отправляется уведомление админам
   - Отображается подтверждение
   - Состояние сбрасывается

#### 6.5.3 Сценарий "Инвентаризация кабинета" (админы)

1. **Админ выбирает "📋 Инвентаризация кабинета"**
   - Состояние: `inventory_awaiting_branch`
   - Отображается: Inline клавиатура со списком филиалов

2. **Админ выбирает филиал**
   - Состояние: `inventory_awaiting_room`
   - Отображается: "Введите номер кабинета:"

3. **Админ вводит номер кабинета**
   - Состояние: `inventory_awaiting_template_selection`
   - Отображается: Список шаблонов + кнопка "Создать новый"

4. **Админ выбирает шаблон или "Создать новый"**
   - Если шаблон: предзаполнение данных
   - Если новый: пустая форма
   - Состояние: `inventory_awaiting_equipment_details`

5. **Админ заполняет детали оборудования**
   - Пошаговый ввод: тип → бренд → модель → серийный № → инвентарный № → заметки
   - Кнопки: ["Сохранить", "Добавить еще", "Отмена"]

6. **Админ сохраняет оборудование**
   - Сохранение через API
   - Отображается подтверждение
   - Предложение добавить еще оборудование или завершить

### 6.6 Обработка ошибок в боте

**Типы ошибок:**
1. **Сетевые ошибки API** - "Временная ошибка сервера, попробуйте позже"
2. **Ошибки валидации** - Конкретное сообщение об ошибке + повтор ввода
3. **Отсутствие прав доступа** - "У вас нет прав для выполнения этого действия"
4. **Неизвестная команда** - Возврат к главному меню с подсказкой

**Механизм восстановления:**
- Кнопка "🏠 Главное меню" всегда доступна
- Команда `/cancel` сбрасывает текущее состояние
- Автоматический сброс состояния через 30 минут неактивности

### 6.7 Уведомления администраторов

#### 6.7.1 Уведомление о новой заявке на ремонт
```
🔧 Новая заявка на ремонт! #123

📍 Филиал: Центральный офис
🏢 Кабинет: 205
📝 Проблема: Не работает принтер
👤 Пользователь: @john_doe
📞 Телефон: +380501234567

⏰ 01.07.2025 14:30

[Принять в работу] [Подробнее]
```

#### 6.7.2 Уведомление о замене картриджа
```
🖨️ Запрос на замену картриджа! #456

📍 Филиал: Центральный офис
🏢 Кабинет: 205
🖨️ Принтер: HP LaserJet Pro (INV123)
🛒 Картридж: HP CF217A
👤 Пользователь: @john_doe

⏰ 01.07.2025 14:35

[Выполнено] [Подробнее]
```

---

## 7. БЕЗОПАСНОСТЬ И АУТЕНТИФИКАЦИЯ

### 7.1 Уровни безопасности

#### 7.1.1 Уровень транспорта
- **HTTPS**: Обязательно для всех соединений
- **SSL сертификат**: Let's Encrypt или коммерческий
- **HSTS**: Принудительное использование HTTPS

#### 7.1.2 Уровень приложения
- **API токены**: SHA-256 хеширование
- **JWT токены**: Подпись алгоритмом HS256
- **Rate limiting**: 100 запросов в минуту на IP
- **CORS**: Настройка разрешенных доменов

#### 7.1.3 Уровень базы данных
- **Prepared statements**: Защита от SQL инъекций
- **Минимальные привилегии**: Отдельный пользователь БД с ограниченными правами
- **Шифрование**: Шифрование чувствительных данных в покое

### 7.2 Аутентификация API

#### 7.2.1 API токены (для бота)
```php
// Генерация токена
$token = bin2hex(random_bytes(32)); // 64 символа

// Сохранение в БД
INSERT INTO api_tokens (name, token, permissions, is_active) 
VALUES ('Telegram Bot', '$token', '["user", "admin"]', 1);

// Проверка токена
$token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $token);
// Поиск в БД и проверка прав
```

#### 7.2.2 JWT токены (для веб-приложения)
```php
// Структура JWT payload
{
    "user_id": 123,
    "telegram_id": 987654321,
    "roles": ["admin"],
    "iat": 1625097600,
    "exp": 1625184000
}
```

### 7.3 Авторизация

#### 7.3.1 Роли и права доступа

**Роль "user" (обычный пользователь):**
- Создание заявок на ремонт
- Запрос замены картриджей
- Поиск оборудования (ограниченный)

**Роль "admin" (администратор):**
- Все права пользователя
- Управление заявками (просмотр, изменение статуса)
- Инвентаризация оборудования
- Управление шаблонами
- Генерация отчетов
- Управление филиалами
- Управление администраторами

#### 7.3.2 Проверка прав в middleware
```php
class AdminMiddleware {
    public function handle($request) {
        $token = $this->getTokenFromRequest($request);
        $permissions = $this->getTokenPermissions($token);
        
        if (!in_array('admin', $permissions)) {
            return $this->forbiddenResponse();
        }
        
        return $request;
    }
}
```

### 7.4 Валидация и санитизация

#### 7.4.1 Входящие данные
```php
class RequestValidator {
    public function validateRepairRequest($data) {
        $rules = [
            'user_telegram_id' => 'required|integer|min:1',
            'branch_id' => 'required|integer|exists:branches,id',
            'room_number' => 'required|string|max:50|regex:/^[a-zA-Z0-9\-]+$/',
            'description' => 'required|string|min:10|max:1000',
            'phone' => 'nullable|string|regex:/^\+380\d{9}$/'
        ];
        
        return $this->validate($data, $rules);
    }
}
```

#### 7.4.2 Санитизация вывода
```php
class ResponseSanitizer {
    public function sanitizeForTelegram($text) {
        // Экранирование специальных символов Telegram
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = str_replace(['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'], 
                          ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)', '\\~', '\\`', '\\>', '\\#', '\\+', '\\-', '\\=', '\\|', '\\{', '\\}', '\\.', '\\!'], 
                          $text);
        return $text;
    }
}
```

### 7.5 Логирование безопасности

#### 7.5.1 События для логирования
- Успешная аутентификация
- Неудачная аутентификация
- Попытки доступа без прав
- Изменения критических данных
- Подозрительная активность (много запросов)

#### 7.5.2 Формат логов безопасности
```json
{
    "timestamp": "2025-07-01T14:30:00Z",
    "event_type": "auth_failure",
    "source_ip": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "details": {
        "token": "invalid_token_here",
        "endpoint": "/api/repair-requests",
        "method": "POST"
    },
    "severity": "warning"
}
```

---

## 8. МОНИТОРИНГ И ЛОГИРОВАНИЕ

### 8.1 Система логирования

#### 8.1.1 Уровни логирования
- **DEBUG**: Детальная отладочная информация
- **INFO**: Общая информация о работе системы
- **WARNING**: Предупреждения о потенциальных проблемах
- **ERROR**: Ошибки, которые не останавливают работу
- **CRITICAL**: Критические ошибки, требующие немедленного внимания

#### 8.1.2 Файлы логов
```
logs/
├── api.log           # Все API запросы и ответы
├── telegram.log      # Активность Telegram бота
├── errors.log        # Все ошибки системы
├── security.log      # События безопасности
└── performance.log   # Метрики производительности
```

#### 8.1.3 Ротация логов
- **Размер файла**: Максимум 10 MB
- **Количество файлов**: Максимум 30 файлов
- **Период хранения**: 30 дней
- **Сжатие**: Старые файлы сжимаются gzip

### 8.2 Метрики производительности

#### 8.2.1 API метрики
- Время ответа на запросы
- Количество запросов в секунду/минуту/час
- Соотношение успешных/неуспешных запросов
- Топ самых медленных endpoints

#### 8.2.2 База данных
- Время выполнения запросов
- Количество подключений
- Размер таблиц
- Эффективность индексов

#### 8.2.3 Telegram Bot
- Время обработки команд
- Количество активных пользователей
- Частота использования функций
- Ошибки отправки сообщений

### 8.3 Мониторинг здоровья системы

#### 8.3.1 Health Check endpoints
```
GET /api/health
{
    "status": "healthy",
    "timestamp": "2025-07-01T14:30:00Z",
    "services": {
        "database": "healthy",
        "telegram_api": "healthy",
        "file_system": "healthy"
    },
    "metrics": {
        "uptime": 86400,
        "memory_usage": "45%",
        "disk_usage": "23%"
    }
}
```

#### 8.3.2 Алерты
- Высокое время ответа API (> 5 секунд)
- Ошибки базы данных
- Недоступность Telegram API
- Заполнение диска (> 90%)
- Высокое использование памяти (> 90%)

---

## 9. ТЕСТИРОВАНИЕ

### 9.1 Стратегия тестирования

#### 9.1.1 Типы тестирования
- **Unit тесты**: Тестирование отдельных классов и методов
- **Integration тесты**: Тестирование взаимодействия компонентов
- **API тесты**: Тестирование REST endpoints
- **E2E тесты**: Тестирование пользовательских сценариев
- **Security тесты**: Тестирование безопасности

#### 9.1.2 Покрытие тестами
- **Минимальное покрытие**: 70%
- **Целевое покрытие**: 85%
- **Критические функции**: 100%

### 9.2 Unit тестирование

#### 9.2.1 Тестируемые компоненты
- Модели данных
- Бизнес-сервисы
- Репозитории
- Валидаторы
- Утилиты

#### 9.2.2 Пример теста
```php
class RepairServiceTest extends PHPUnit\Framework\TestCase {
    public function testCreateRepairRequest() {
        // Arrange
        $data = [
            'user_telegram_id' => 123456789,
            'branch_id' => 1,
            'room_number' => '205',
            'description' => 'Printer not working'
        ];
        
        // Act
        $result = $this->repairService->createRepairRequest($data);
        
        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('id', $result['data']);
        $this->assertEquals('новая', $result['data']['status']);
    }
}
```

### 9.3 API тестирование

#### 9.3.1 Тестовые сценарии
- Создание ресурсов с валидными данными
- Создание ресурсов с невалидными данными
- Получение ресурсов с правильными правами доступа
- Попытки доступа без аутентификации
- Попытки доступа с недостаточными правами

#### 9.3.2 Пример API теста
```php
class RepairRequestApiTest extends ApiTestCase {
    public function testCreateRepairRequestSuccess() {
        $response = $this->postJson('/api/repair-requests', [
            'user_telegram_id' => 123456789,
            'branch_id' => 1,
            'room_number' => '205',
            'description' => 'Printer not working'
        ], [
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ]);
        
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => ['id', 'status', 'created_at']
                ]);
    }
}
```

### 9.4 Тестирование Telegram бота

#### 9.4.1 Симуляция Telegram API
```php
class TelegramBotTest extends TestCase {
    public function testStartCommand() {
        $update = $this->createTelegramUpdate([
            'message' => [
                'text' => '/start',
                'from' => ['id' => 123456789]
            ]
        ]);
        
        $response = $this->bot->handleUpdate($update);
        
        $this->assertContains('Главное меню', $response['text']);
        $this->assertCount(2, $response['reply_markup']['inline_keyboard']);
    }
}
```

### 9.5 Тестовые данные

#### 9.5.1 Фикстуры для тестирования
```sql
-- Тестовые филиалы
INSERT INTO branches (id, name, is_active) VALUES 
(1, 'Тестовый филиал 1', 1),
(2, 'Тестовый филиал 2', 1),
(3, 'Неактивный филиал', 0);

-- Тестовые администраторы
INSERT INTO admins (id, telegram_id, name, is_active) VALUES 
(1, 987654321, 'Тестовый админ', 1);

-- Тестовые API токены
INSERT INTO api_tokens (id, name, token, permissions, is_active) VALUES 
(1, 'Test Token', 'test_token_123', '["user", "admin"]', 1);
```

---

## 10. РАЗВЕРТЫВАНИЕ И ЭКСПЛУАТАЦИЯ

### 10.1 Требования к серверу

#### 10.1.1 Минимальные системные требования
- **OS**: Ubuntu 20.04 LTS или CentOS 8
- **CPU**: 1 vCPU (2.0 GHz)
- **RAM**: 2 GB
- **Storage**: 20 GB SSD
- **Network**: 100 Mbps

#### 10.1.2 Рекомендуемые требования
- **OS**: Ubuntu 22.04 LTS
- **CPU**: 2 vCPU (2.4 GHz)
- **RAM**: 4 GB
- **Storage**: 50 GB SSD
- **Network**: 1 Gbps

#### 10.1.3 Программное обеспечение
- **Web-сервер**: Apache 2.4+ или Nginx 1.18+
- **PHP**: 7.4+ (рекомендуется 8.1+)
- **MySQL**: 8.0+
- **SSL**: Let's Encrypt или коммерческий сертификат

### 10.2 Процедура развертывания

#### 10.2.1 Подготовка сервера
```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка необходимых пакетов
sudo apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-curl php8.1-json php8.1-mbstring -y

# Настройка MySQL
sudo mysql_secure_installation

# Создание базы данных
mysql -u root -p
CREATE DATABASE it_support_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'it_support_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON it_support_db.* TO 'it_support_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 10.2.2 Развертывание приложения
```bash
# Создание директории проекта
sudo mkdir -p /var/www/html/it-support
cd /var/www/html/it-support

# Загрузка кода (пример с git)
sudo git clone https://github.com/your-repo/it-support-bot.git .

# Настройка прав доступа
sudo chown -R www-data:www-data /var/www/html/it-support
sudo chmod -R 755 /var/www/html/it-support
sudo chmod -R 777 /var/www/html/it-support/logs

# Импорт структуры БД
mysql -u it_support_user -p it_support_db < sql/database.sql
```

#### 10.2.3 Настройка конфигурации
```bash
# Копирование примеров конфигурации
cp config/config.example.php config/config.php
cp config/database.example.php config/database.php
cp config/telegram.example.php config/telegram.php

# Редактирование конфигураций
nano config/database.php  # Параметры БД
nano config/telegram.php  # Токен бота
nano config/config.php    # Основные настройки
```

#### 10.2.4 Настройка веб-сервера
**Apache виртуальный хост:**
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/it-support
    
    <Directory /var/www/html/it-support>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/it-support_error.log
    CustomLog ${APACHE_LOG_DIR}/it-support_access.log combined
</VirtualHost>
```

### 10.3 SSL сертификат

#### 10.3.1 Let's Encrypt
```bash
# Установка Certbot
sudo apt install certbot python3-certbot-apache -y

# Получение SSL сертификата
sudo certbot --apache -d your-domain.com

# Автообновление сертификата
sudo crontab -e
# Добавить строку: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 10.4 Настройка Telegram бота

#### 10.4.1 Создание бота
```bash
# 1. Обратиться к @BotFather в Telegram
# 2. Выполнить команду /newbot
# 3. Следовать инструкциям для создания бота
# 4. Получить API токен
# 5. Добавить токен в config/telegram.php
```

#### 10.4.2 Установка webhook
```bash
# Установка webhook
curl -X POST "https://api.telegram.org/bot{YOUR_BOT_TOKEN}/setWebhook" \
     -d "url=https://your-domain.com/telegram-bot/webhook.php" \
     -d "max_connections=100"

# Проверка webhook
curl "https://api.telegram.org/bot{YOUR_BOT_TOKEN}/getWebhookInfo"
```

### 10.5 Создание первого администратора

```sql
-- Добавление первого администратора
INSERT INTO admins (telegram_id, name, is_active) 
VALUES (YOUR_TELEGRAM_ID, 'Главный администратор', 1);

-- Создание API токена для бота
INSERT INTO api_tokens (name, token, permissions, is_active) 
VALUES ('Telegram Bot', 'your_secure_api_token_here', '["user", "admin"]', 1);
```

### 10.6 Мониторинг и обслуживание

#### 10.6.1 Ротация логов
```bash
# Создание конфигурации logrotate
sudo nano /etc/logrotate.d/it-support

/var/www/html/it-support/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
    create 644 www-data www-data
}
```

#### 10.6.2 Backup базы данных
```bash
# Создание скрипта резервного копирования
nano /usr/local/bin/backup-it-support.sh

#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/it-support"
mkdir -p $BACKUP_DIR

# Создание резервной копии БД
mysqldump -u it_support_user -p'secure_password_here' it_support_db > $BACKUP_DIR/db_backup_$DATE.sql

# Архивирование файлов проекта
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/html/it-support --exclude=/var/www/html/it-support/logs

# Удаление старых бэкапов (старше 7 дней)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"

# Добавление в crontab
# sudo crontab -e
# 0 2 * * * /usr/local/bin/backup-it-support.sh >> /var/log/backup.log 2>&1
```

#### 10.6.3 Мониторинг здоровья системы
```bash
# Создание скрипта проверки состояния
nano /usr/local/bin/health-check.sh

#!/bin/bash
LOG_FILE="/var/log/health-check.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

# Проверка веб-сервера
if curl -s -o /dev/null -w "%{http_code}" http://localhost/api/health | grep -q "200"; then
    SERVER_STATUS="OK"
else
    SERVER_STATUS="ERROR"
fi

# Проверка базы данных
if mysql -u it_support_user -p'secure_password_here' -e "SELECT 1" it_support_db &> /dev/null; then
    DB_STATUS="OK"
else
    DB_STATUS="ERROR"
fi

# Проверка места на диске
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 90 ]; then
    DISK_STATUS="WARNING"
elif [ $DISK_USAGE -gt 95 ]; then
    DISK_STATUS="CRITICAL"
else
    DISK_STATUS="OK"
fi

# Логирование
echo "[$DATE] Server: $SERVER_STATUS, DB: $DB_STATUS, Disk: $DISK_STATUS ($DISK_USAGE%)" >> $LOG_FILE

# Отправка алертов при проблемах
if [ "$SERVER_STATUS" = "ERROR" ] || [ "$DB_STATUS" = "ERROR" ] || [ "$DISK_STATUS" = "CRITICAL" ]; then
    # Здесь можно добавить отправку уведомлений в Telegram или email
    echo "ALERT: System health check failed at $DATE" >> $LOG_FILE
fi
```

### 10.7 Обновление системы

#### 10.7.1 Процедура обновления
```bash
# 1. Создание резервной копии
/usr/local/bin/backup-it-support.sh

# 2. Загрузка новой версии
cd /var/www/html/it-support
sudo git fetch origin
sudo git checkout main
sudo git pull origin main

# 3. Обновление зависимостей (если есть composer)
# sudo composer install --no-dev --optimize-autoloader

# 4. Выполнение миграций БД (если есть)
# php migration/migrate.php

# 5. Очистка кэша (если есть)
# sudo rm -rf cache/*

# 6. Перезапуск веб-сервера
sudo systemctl reload apache2

# 7. Проверка работоспособности
curl -s http://localhost/api/health
```

#### 10.7.2 Откат изменений
```bash
# В случае проблем с обновлением
cd /var/www/html/it-support
sudo git checkout previous_stable_version
sudo systemctl reload apache2

# Восстановление БД из резервной копии (если нужно)
# mysql -u it_support_user -p'password' it_support_db < /var/backups/it-support/db_backup_YYYYMMDD_HHMMSS.sql
```

---

## 11. ПРОИЗВОДИТЕЛЬНОСТЬ И ОПТИМИЗАЦИЯ

### 11.1 Оптимизация базы данных

#### 11.1.1 Индексирование
```sql
-- Основные индексы уже созданы в структуре таблиц
-- Дополнительные составные индексы для частых запросов

-- Поиск заявок по статусу и дате
CREATE INDEX idx_repair_status_date ON repair_requests(status, created_at);

-- Поиск инвентаря по филиалу и кабинету
CREATE INDEX idx_inventory_branch_room ON room_inventory(branch_id, room_number);

-- Поиск замен картриджей по дате
CREATE INDEX idx_cartridge_date ON cartridge_replacements(replacement_date, branch_id);

-- Анализ использования индексов
EXPLAIN SELECT * FROM repair_requests WHERE status = 'новая' ORDER BY created_at DESC LIMIT 20;
```

#### 11.1.2 Конфигурация MySQL
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
# Основные настройки производительности
innodb_buffer_pool_size = 1G          # 70-80% от доступной RAM
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Настройки для небольших таблиц
table_open_cache = 4000
query_cache_type = 1
query_cache_size = 128M

# Настройки соединений
max_connections = 200
wait_timeout = 600
interactive_timeout = 600
```

### 11.2 Кэширование

#### 11.2.1 Кэширование списка филиалов
```php
class BranchService {
    private $cache_file = '/tmp/branches_cache.json';
    private $cache_ttl = 3600; // 1 час
    
    public function getActiveBranches() {
        // Проверка кэша
        if (file_exists($this->cache_file) && 
            (time() - filemtime($this->cache_file)) < $this->cache_ttl) {
            return json_decode(file_get_contents($this->cache_file), true);
        }
        
        // Получение из БД
        $branches = $this->branchRepository->getActive();
        
        // Сохранение в кэш
        file_put_contents($this->cache_file, json_encode($branches));
        
        return $branches;
    }
}
```

#### 11.2.2 Кэширование шаблонов инвентаря
```php
class InventoryTemplateService {
    private $cache = [];
    
    public function getTemplates() {
        if (empty($this->cache)) {
            $this->cache = $this->templateRepository->getAll();
        }
        return $this->cache;
    }
    
    public function clearCache() {
        $this->cache = [];
        // Очистка файлового кэша
        $files = glob('/tmp/templates_cache_*.json');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
```

### 11.3 Оптимизация API

#### 11.3.1 Пагинация
```php
class RepairController {
    public function getRepairRequests($request) {
        $page = (int)($request['page'] ?? 1);
        $limit = min((int)($request['limit'] ?? 20), 100); // Максимум 100 записей
        $offset = ($page - 1) * $limit;
        
        $requests = $this->repairService->getRequests($offset, $limit);
        $total = $this->repairService->getTotalCount();
        
        return [
            'data' => $requests,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }
}
```

#### 11.3.2 Оптимизация запросов
```php
class RepairRepository {
    public function getRequestsWithBranches($offset, $limit) {
        // Использование JOIN вместо отдельных запросов
        $sql = "
            SELECT r.*, b.name as branch_name 
            FROM repair_requests r 
            LEFT JOIN branches b ON r.branch_id = b.id 
            ORDER BY r.created_at DESC 
            LIMIT ? OFFSET ?
        ";
        
        return $this->db->prepare($sql)->execute([$limit, $offset])->fetchAll();
    }
}
```

### 11.4 Оптимизация Telegram бота

#### 11.4.1 Batch обработка уведомлений
```php
class NotificationService {
    private $notification_queue = [];
    
    public function queueNotification($admin_id, $message) {
        $this->notification_queue[] = [
            'admin_id' => $admin_id,
            'message' => $message,
            'timestamp' => time()
        ];
        
        // Отправка пакетом каждые 5 уведомлений или каждые 30 секунд
        if (count($this->notification_queue) >= 5 || 
            (time() - $this->notification_queue[0]['timestamp']) > 30) {
            $this->sendBatchNotifications();
        }
    }
    
    private function sendBatchNotifications() {
        foreach ($this->notification_queue as $notification) {
            $this->sendTelegramMessage($notification['admin_id'], $notification['message']);
            usleep(100000); // 0.1 секунды между сообщениями
        }
        $this->notification_queue = [];
    }
}
```

#### 11.4.2 Оптимизация состояний пользователей
```php
class UserStateManager {
    private $states_cache = [];
    
    public function getUserState($telegram_id) {
        // Проверка локального кэша
        if (isset($this->states_cache[$telegram_id])) {
            return $this->states_cache[$telegram_id];
        }
        
        // Получение из БД
        $state = $this->stateRepository->getByTelegramId($telegram_id);
        $this->states_cache[$telegram_id] = $state;
        
        return $state;
    }
    
    public function updateUserState($telegram_id, $state, $temp_data = null) {
        $this->stateRepository->updateState($telegram_id, $state, $temp_data);
        $this->states_cache[$telegram_id] = [
            'current_state' => $state,
            'temp_data' => $temp_data
        ];
    }
}
```

---

## 12. ПЛАНЫ РАЗВИТИЯ

### 12.1 Дорожная карта развития

#### 12.1.1 Версия 1.0 (Базовый функционал)
**Срок: 4-6 недель**
- ✅ Создание заявок на ремонт
- ✅ Запросы на замену картриджей
- ✅ Инвентаризация кабинетов (админы)
- ✅ Управление филиалами
- ✅ Базовые отчеты
- ✅ REST API
- ✅ Telegram бот с основным функционалом

#### 12.1.2 Версия 1.1 (Улучшения)
**Срок: 2-3 недели после v1.0**
- 📊 Расширенная отчетность с графиками
- 🔍 Продвинутый поиск и фильтрация
- 📱 Push уведомления для админов
- 🏷️ Система тегов для оборудования
- 📸 Возможность прикрепления фото к заявкам
- ⚡ Улучшенная производительность

#### 12.1.3 Версия 1.2 (Интеграции)
**Срок: 3-4 недели после v1.1**
- 🌐 Laravel веб-приложение
- 📧 Email уведомления
- 📅 Интеграция с календарем
- 📋 Экспорт отчетов в Excel/PDF
- 🔄 Система workflow для заявок
- 👥 Роли и права доступа

#### 12.1.4 Версия 2.0 (Расширенный функционал)
**Срок: 6-8 недель после v1.2**
- 📱 Мобильное приложение
- 🤖 AI помощник для диагностики
- 📊 Аналитика и dashboard
- 🔔 Настраиваемые уведомления
- 🌍 Мультиязычность
- ☁️ Облачная версия

### 12.2 Возможные интеграции

#### 12.2.1 Внешние системы
- **Active Directory**: Синхронизация пользователей
- **LDAP**: Аутентификация через корпоративную сеть  
- **1C**: Интеграция с учетными системами
- **Email серверы**: SMTP для уведомлений
- **SMS шлюзы**: SMS уведомления
- **IP телефония**: Интеграция с корпоративной связью

#### 12.2.2 Сторонние сервисы
- **Google Workspace**: Календарь, Drive, Gmail
- **Microsoft 365**: Teams, Outlook, OneDrive
- **Slack**: Уведомления в корпоративный чат
- **Jira/ServiceNow**: Интеграция с Service Desk
- **Zabbix/Nagios**: Мониторинг инфраструктуры
- **Tableau/Power BI**: Продвинутая аналитика

### 12.3 Технологические улучшения

#### 12.3.1 Архитектурные улучшения
- **Микросервисная архитектура**: Разделение на независимые сервисы
- **Message Queue**: Асинхронная обработка задач (Redis/RabbitMQ)
- **Кэширование**: Redis для кэширования данных
- **CDN**: Ускорение загрузки статических файлов
- **Load Balancer**: Распределение нагрузки между серверами
- **Docker**: Контейнеризация приложения

#### 12.3.2 Безопасность
- **OAuth 2.0**: Интеграция с внешними провайдерами аутентификации
- **2FA**: Двухфакторная аутентификация
- **Audit Log**: Детальное логирование действий пользователей
- **Encryption**: Шифрование чувствительных данных
- **WAF**: Web Application Firewall
- **SIEM**: Интеграция с системами мониторинга безопасности

### 12.4 Масштабирование

#### 12.4.1 Горизонтальное масштабирование
```
                    [Load Balancer]
                          |
        ┌─────────────────┼─────────────────┐
        │                 │                 │
   [Web Server 1]   [Web Server 2]   [Web Server 3]
        │                 │                 │
        └─────────────────┼─────────────────┘
                          |
                   [Database Cluster]
                 ┌────────┼────────┐
           [Master DB] [Slave DB 1] [Slave DB 2]
```

#### 12.4.2 Вертикальное масштабирование
- **CPU**: Увеличение количества ядер процессора
- **RAM**: Увеличение объема оперативной памяти
- **Storage**: Переход на более быстрые SSD диски
- **Network**: Увеличение пропускной способности сети

### 12.5 Метрики успеха

#### 12.5.1 Технические метрики
- **Uptime**: > 99.5%
- **Response Time**: < 2 секунды для 95% запросов
- **Error Rate**: < 1% от общего количества запросов
- **Database Performance**: < 100ms для основных запросов
- **User Satisfaction**: > 4.5 из 5 по результатам опросов

#### 12.5.2 Бизнес метрики
- **Количество пользователей**: Рост на 20% каждый квартал
- **Количество заявок**: Обработка > 1000 заявок в месяц
- **Время обработки заявок**: Сокращение на 50%
- **Качество обслуживания**: Увеличение NPS на 30%
- **ROI**: Окупаемость системы в течение 6 месяцев

---

## 13. ЗАКЛЮЧЕНИЕ

### 13.1 Резюме проекта

Данное техническое задание описывает разработку комплексной системы управления IT поддержкой с использованием Telegram бота и REST API. Система решает следующие ключевые задачи:

1. **Автоматизация процессов**: Упрощение подачи заявок на ремонт и замену картриджей
2. **Централизация данных**: Единая база данных для всех IT активов и заявок
3. **Оперативность**: Мгновенные уведомления администраторов через Telegram
4. **Масштабируемость**: Архитектура позволяет легко добавлять новые интерфейсы
5. **Отчетность**: Комплексная система отчетов для анализа и планирования

### 13.2 Ключевые преимущества решения

- **Простота использования**: Интуитивный интерфейс Telegram бота
- **Мобильность**: Доступ к системе с любого устройства через Telegram
- **Гибкость**: REST API позволяет интегрировать с любыми системами
- **Надежность**: Продуманная архитектура с обработкой ошибок
- **Безопасность**: Многоуровневая система защиты данных
- **Экономичность**: Использование бесплатных и open-source технологий

### 13.3 Риски и митигация

#### 13.3.1 Технические риски
- **Недоступность Telegram API**: Резервные каналы уведомлений
- **Перегрузка сервера**: Мониторинг и автоматическое масштабирование
- **Потеря данных**: Регулярные бэкапы и репликация БД
- **Безопасность**: Регулярные аудиты безопасности и обновления

#### 13.3.2 Операционные риски
- **Сопротивление пользователей**: Обучение и постепенное внедрение
- **Неточные требования**: Итеративная разработка с обратной связью
- **Превышение бюджета**: Четкое планирование и контроль затрат
- **Задержки разработки**: Агильная методология и буферное время

### 13.4 Критерии приемки

Система считается успешно реализованной при выполнении следующих условий:

#### 13.4.1 Функциональные критерии
- ✅ Все описанные пользовательские сценарии работают корректно
- ✅ REST API соответствует спецификации и проходит все тесты
- ✅ Telegram бот корректно обрабатывает все команды и состояния
- ✅ Система уведомлений работает в реальном времени
- ✅ Отчеты генерируются корректно и содержат актуальные данные

#### 13.4.2 Нефункциональные критерии
- ✅ Время отклика API не превышает 2 секунд
- ✅ Система выдерживает нагрузку 100 одновременных пользователей
- ✅ Все данные защищены согласно требованиям безопасности
- ✅ Документация полная и актуальная
- ✅ Код покрыт тестами минимум на 70%

### 13.5 Поддержка и сопровождение

После внедрения системы предусматривается:

- **Гарантийная поддержка**: 6 месяцев бесплатной поддержки
- **Обновления безопасности**: Регулярные патчи безопасности
- **Техническая документация**: Руководства для администраторов
- **Обучение пользователей**: Инструкции и видео-руководства
- **Мониторинг**: Система мониторинга работоспособности
- **Резервное копирование**: Автоматические бэкапы данных

---

**Данное техническое задание является живым документом и может обновляться в процессе разработки с учетом обратной связи от заказчика и изменений требований.**

**Статус документа**: Готов к согласованию  
**Версия**: 1.0  
**Дата последнего обновления**: 01.07.2025