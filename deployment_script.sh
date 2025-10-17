#!/bin/bash

# Скрипт для автоматического деплоя на Timeweb
# Использование: bash deploy_to_timeweb.sh

set -e

echo "================================"
echo "🚀 Деплой CRM на Timeweb"
echo "================================"

# Данные вашего хостинга
USERNAME="cw95865"
DOMAIN="cw95865.tmweb.ru"
SERVER="cw95865@cw95865.tmweb.ru"
ROOT_PATH="/home/c/cw95865"

echo "📋 Параметры деплоя:"
echo "   Пользователь: $USERNAME"
echo "   Домен: $DOMAIN"
echo "   Сервер: $SERVER"
echo "   Корневая папка: $ROOT_PATH"
echo ""

read -p "❓ Продолжить? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Отменено"
    exit 1
fi

echo ""
echo "📦 Создание архива проекта..."
tar -czf tutor_crm_deploy.tar.gz \
    --exclude='venv' \
    --exclude='env' \
    --exclude='__pycache__' \
    --exclude='*.pyc' \
    --exclude='db.sqlite3' \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='staticfiles' \
    --exclude='media' \
    --exclude='.env' \
    tutor_crm/ apps/ templates/ static/ manage.py requirements.txt

echo "✅ Архив создан: tutor_crm_deploy.tar.gz"
echo ""

echo "📤 Загрузка на сервер..."
scp tutor_crm_deploy.tar.gz ${SERVER}:~/

echo ""
echo "🔧 Настройка на сервере..."

ssh ${SERVER} << 'ENDSSH'
set -e

echo "📁 Создание структуры каталогов..."
cd ~/public_html

# Создаем backup если проект уже существует
if [ -d "tutor_crm" ]; then
    echo "💾 Создание backup..."
    mv tutor_crm tutor_crm_backup_$(date +%Y%m%d_%H%M%S)
fi

echo "📦 Распаковка архива..."
tar -xzf ~/tutor_crm_deploy.tar.gz -C ~/public_html/
rm ~/tutor_crm_deploy.tar.gz

echo "🐍 Настройка виртуального окружения..."
if [ ! -f "~/venv/bin/activate" ]; then
    echo "   Создание нового venv..."
    cd ~
    if [ ! -f "virtualenv.pyz" ]; then
        wget -q https://bootstrap.pypa.io/virtualenv/3.6/virtualenv.pyz
    fi
    python3 virtualenv.pyz ~/venv
fi

source ~/venv/bin/activate

echo "📚 Установка зависимостей..."
cd ~/public_html/tutor_crm
pip install -q --upgrade pip
pip install -q -r requirements.txt

echo "🔐 Установка прав доступа..."
chmod 755 ~/public_html/wsgi.py
chmod 644 ~/public_html/.htaccess
find ~/public_html/tutor_crm -type f -exec chmod 644 {} \;
find ~/public_html/tutor_crm -type d -exec chmod 755 {} \;

echo ""
echo "✅ Деплой завершен!"
echo ""
echo "📋 Следующие шаги:"
echo "1. Создайте файл .env в ~/public_html/tutor_crm/"
echo "   Используйте следующие настройки:"
echo ""
echo "   DEBUG=False"
echo "   SECRET_KEY=сгенерируйте-новый-ключ"
echo "   ALLOWED_HOSTS=cw95865.tmweb.ru"
echo ""
echo "   DB_NAME=cw95865_rmtutori"
echo "   DB_USER=cw95865_rmtutori"
echo "   DB_PASSWORD=123456789"
echo "   DB_HOST=localhost"
echo "   DB_PORT=3306"
echo ""
echo "2. Выполните миграции:"
echo "   cd ~/public_html/tutor_crm"
echo "   source ~/venv/bin/activate"
echo "   python manage.py migrate"
echo "   python manage.py collectstatic --noinput"
echo "   python manage.py createsuperuser"
echo ""
echo "3. Перезапустите WSGI:"
echo "   touch ~/public_html/wsgi.py"
echo ""

ENDSSH

echo ""
echo "🎉 Деплой успешно завершен!"
echo "🌐 Проверьте сайт: https://${DOMAIN}"
echo ""
echo "📝 Не забудьте:"
echo "   1. Настроить .env файл на сервере"
echo "   2. Применить миграции"
echo "   3. Создать суперпользователя"
echo ""

# Удаляем локальный архив
rm tutor_crm_deploy.tar.gz
echo "🧹 Временные файлы удалены"