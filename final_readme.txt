# 🎓 CRM Система для репетиторского центра

**Готовое решение с полной автоматизацией развертывания**

---

## 📋 Ваши данные Timeweb

```
🌐 Домен:      https://cw95865.tmweb.ru/
🔐 SSH:        cw95865@cw95865.tmweb.ru
📁 Корневая:   /home/c/cw95865
💾 База:       cw95865_rmtutori
👤 Логин БД:   cw95865_rmtutori
🔑 Пароль БД:  123456789
```

---

## ⚡ Установка за 3 шага

### Шаг 1: Генерация проекта (10 секунд)

```bash
python3 setup_project.py
```

Создаст полную структуру проекта со всеми файлами!

### Шаг 2: Автоматический деплой (2 минуты)

```bash
bash deploy_to_timeweb.sh
```

Скрипт уже настроен под ваш Timeweb - просто запустите!

### Шаг 3: Завершение настройки (3 минуты)

```bash
ssh cw95865@cw95865.tmweb.ru
cd /home/c/cw95865/public_html/tutor_crm
source ~/venv/bin/activate

# Создайте .env (используйте готовый .env.production)
nano .env

# Примените миграции
python manage.py migrate
python manage.py collectstatic --noinput
python manage.py createsuperuser

# Перезапустите
touch /home/c/cw95865/public_html/wsgi.py
```

**Готово!** Откройте https://cw95865.tmweb.ru/

---

## 📚 Документация

| Файл | Описание |
|------|----------|
| **QUICKSTART.md** | Быстрый старт за 3 минуты |
| **DEPLOY_CUSTOM.md** | Подробная инструкция для вашего Timeweb |
| **COMMANDS.txt** | Шпаргалка команд для вашего сервера |
| **USAGE_GUIDE.md** | Полное руководство пользователя |
| **INSTALLATION.txt** | Простая текстовая инструкция |
| **.env.production** | Готовый конфиг для production |

---

## 🎯 Что включено

### ✅ Модули системы

- **Ученики и родители** - управление учениками, контакты родителей (WhatsApp, Telegram)
- **Преподаватели** - профили учителей, предметы, ставки
- **Расписание** - визуальный календарь, группы до 6 человек, управление кабинетами
- **Финансы** - учет доходов и расходов, статистика
- **Задачи** - To-Do список с привязкой к ученикам

### ✅ Технологии

- **Backend**: Python 3.6, Django 3.2 LTS, MySQL
- **Frontend**: Tailwind CSS, JavaScript
- **Хостинг**: Apache + mod_wsgi (Timeweb)

### ✅ Автоматизация

- Генерация проекта - 1 команда
- Деплой на Timeweb - 1 команда
- Все пути уже настроены под ваш хостинг
- Готовые конфигурационные файлы

---

## 🚀 Быстрые команды

### Подключение к серверу

```bash
ssh cw95865@cw95865.tmweb.ru
```

### Активация окружения

```bash
source ~/venv/bin/activate
cd /home/c/cw95865/public_html/tutor_crm
```

### Применить изменения

```bash
python manage.py migrate
python manage.py collectstatic --noinput
touch /home/c/cw95865/public_html/wsgi.py
```

### Просмотр логов

```bash
tail -f /home/c/cw95865/logs/error.log
```

---

## 📁 Структура проекта

```
/home/c/cw95865/
├── venv/                          # Виртуальное окружение
└── public_html/
    ├── wsgi.py                   # WSGI точка входа (уже настроен!)
    ├── .htaccess                 # Apache конфиг (уже настроен!)
    └── tutor_crm/                # Django проект
        ├── manage.py
        ├── requirements.txt
        ├── .env                  # Создать из .env.production
        ├── tutor_crm/            # Настройки Django
        ├── apps/                 # Приложения (students, teachers, etc)
        ├── templates/            # HTML шаблоны
        └── static/               # CSS, JS
```

---

## 🔧 Настройка .env

Используйте готовый файл `.env.production` или создайте:

```env
DEBUG=False
SECRET_KEY=сгенерируйте-новый-ключ
ALLOWED_HOSTS=cw95865.tmweb.ru

DB_NAME=cw95865_rmtutori
DB_USER=cw95865_rmtutori
DB_PASSWORD=123456789
DB_HOST=localhost
DB_PORT=3306
```

**Сгенерировать SECRET_KEY:**
```bash
python -c "from django.core.management.utils import get_random_secret_key; print(get_random_secret_key())"
```

---

## 🔄 Обновление проекта

```bash
# Способ 1: Автоматический
bash deploy_to_timeweb.sh

# Способ 2: Вручную
ssh cw95865@cw95865.tmweb.ru
cd /home/c/cw95865/public_html/tutor_crm
source ~/venv/bin/activate
# Загрузите файлы через FTP
python manage.py migrate
python manage.py collectstatic --noinput
touch /home/c/cw95865/public_html/wsgi.py
```

---

## 🐛 Решение проблем

### Ошибка 500

```bash
# Проверить логи
tail -n 50 /home/c/cw95865/logs/error.log

# Проверить права
chmod 755 /home/c/cw95865/public_html/wsgi.py

# Перезапустить
touch /home/c/cw95865/public_html/wsgi.py
```

### Статика не загружается

```bash
cd /home/c/cw95865/public_html/tutor_crm
source ~/venv/bin/activate
python manage.py collectstatic --noinput
```

### База данных

```bash
# Проверить .env
cat /home/c/cw95865/public_html/tutor_crm/.env

# Тест подключения
python manage.py dbshell
```

**Подробнее в файле COMMANDS.txt**

---

## 🌐 Ссылки

- **Главная**: https://cw95865.tmweb.ru/
- **Админка**: https://cw95865.tmweb.ru/admin/
- **Ученики**: https://cw95865.tmweb.ru/students/
- **Расписание**: https://cw95865.tmweb.ru/schedule/
- **Финансы**: https://cw95865.tmweb.ru/finance/
- **Задачи**: https://cw95865.tmweb.ru/tasks/

---

## 💾 Backup базы данных

```bash
# Создать backup
mysqldump -u cw95865_rmtutori -p cw95865_rmtutori > backup_$(date +%Y%m%d).sql
# Пароль: 123456789

# Восстановить
mysql -u cw95865_rmtutori -p cw95865_rmtutori < backup.sql
```

---

## ✅ Чеклист установки

- [ ] `python3 setup_project.py` выполнен
- [ ] `bash deploy_to_timeweb.sh` выполнен
- [ ] SSH подключение работает
- [ ] `.env` создан на сервере
- [ ] SECRET_KEY сгенерирован
- [ ] Миграции применены
- [ ] Статика собрана
- [ ] Суперпользователь создан
- [ ] https://cw95865.tmweb.ru/ открывается
- [ ] Админка работает
- [ ] Можно добавить ученика

---

## 🎓 Первые шаги

1. **Войдите в админку**: https://cw95865.tmweb.ru/admin/
2. **Добавьте данные**:
   - Кабинеты
   - Преподаватели
   - Родители
   - Ученики
   - Группы
3. **Используйте интерфейс**: Dashboard, Расписание, Финансы

---

## 📞 Поддержка

- **Полная документация**: см. файлы USAGE_GUIDE.md и DEPLOY_CUSTOM.md
- **Команды**: см. COMMANDS.txt
- **Логи**: `/home/c/cw95865/logs/error.log`

---

## 🎉 Готово!

Время установки: **5-10 минут**  
Ваша CRM готова к использованию: **https://cw95865.tmweb.ru/**

**Следующие шаги:**
1. Запустите `python3 setup_project.py`
2. Запустите `bash deploy_to_timeweb.sh`
3. Настройте `.env` на сервере
4. Примените миграции
5. Начните использовать!

**Удачи! 🚀**