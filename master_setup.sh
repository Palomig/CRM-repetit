#!/bin/bash

# ==========================================
# 🚀 Мастер-скрипт установки CRM системы
# ==========================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_NAME="tutor_crm"

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

echo_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

echo_error() {
    echo -e "${RED}❌ $1${NC}"
}

echo_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

banner() {
    echo ""
    echo "=========================================="
    echo "  🎓 CRM Репетиторский центр"
    echo "  📦 Автоматическая установка"
    echo "=========================================="
    echo ""
}

check_python() {
    if ! command -v python3 &> /dev/null; then
        echo_error "Python 3 не найден. Установите Python 3.6+"
        exit 1
    fi
    
    PYTHON_VERSION=$(python3 --version | cut -d' ' -f2 | cut -d'.' -f1,2)
    echo_info "Найден Python $PYTHON_VERSION"
}

main_menu() {
    banner
    echo "Выберите действие:"
    echo ""
    echo "1) 🏗️  Создать новый проект (локально)"
    echo "2) 🚀 Деплой на Timeweb"
    echo "3) 🔄 Обновить существующий проект"
    echo "4) 🧪 Запустить локальный сервер"
    echo "5) 📊 Проверить статус"
    echo "6) ❌ Выход"
    echo ""
    read -p "Введите номер (1-6): " choice
    
    case $choice in
        1) create_project ;;
        2) deploy_to_timeweb ;;
        3) update_project ;;
        4) run_local_server ;;
        5) check_status ;;
        6) echo_info "До свидания!"; exit 0 ;;
        *) echo_error "Неверный выбор"; main_menu ;;
    esac
}

create_project() {
    echo ""
    echo_info "=========================================="
    echo_info "Создание нового проекта"
    echo_info "=========================================="
    echo ""
    
    if [ -d "$PROJECT_NAME" ]; then
        echo_warning "Папка $PROJECT_NAME уже существует!"
        read -p "Удалить и создать заново? (y/n): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            rm -rf "$PROJECT_NAME"
            echo_success "Старая папка удалена"
        else
            echo_error "Отменено"
            main_menu
            return
        fi
    fi
    
    echo_info "Запуск генератора проекта..."
    
    if [ -f "setup_project.py" ]; then
        python3 setup_project.py
        echo_success "Проект создан!"
    else
        echo_error "Файл setup_project.py не найден!"
        exit 1
    fi
    
    echo ""
    echo_info "Настройка локального окружения..."
    
    cd "$PROJECT_NAME"
    
    # Создаем виртуальное окружение
    echo_info "Создание виртуального окружения..."
    python3 -m venv venv
    
    # Активируем
    source venv/bin/activate
    
    # Устанавливаем зависимости
    echo_info "Установка зависимостей..."
    pip install --upgrade pip -q
    pip install -r requirements.txt -q
    
    # Копируем .env
    if [ ! -f ".env" ]; then
        cp .env.example .env
        echo_success "Создан файл .env"
        echo_warning "⚠️  Не забудьте настроить .env файл!"
    fi
    
    # Применяем миграции
    echo_info "Применение миграций..."
    python manage.py makemigrations
    python manage.py migrate
    
    # Собираем статику
    echo_info "Сбор статических файлов..."
    python manage.py collectstatic --noinput
    
    echo ""
    echo_success "=========================================="
    echo_success "Проект успешно создан!"
    echo_success "=========================================="
    echo ""
    echo_info "Следующие шаги:"
    echo "  1. Перейдите в папку: cd $PROJECT_NAME"
    echo "  2. Активируйте venv: source venv/bin/activate"
    echo "  3. Настройте .env файл"
    echo "  4. Создайте суперпользователя: python manage.py createsuperuser"
    echo "  5. Запустите сервер: python manage.py runserver"
    echo ""
    
    read -p "Создать суперпользователя сейчас? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        python manage.py createsuperuser
    fi
    
    read -p "Запустить локальный сервер? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo_info "Запуск сервера на http://127.0.0.1:8000"
        python manage.py runserver
    fi
}

deploy_to_timeweb() {
    echo ""
    echo_info "=========================================="
    echo_info "Деплой на Timeweb"
    echo_info "=========================================="
    echo ""
    
    if [ ! -f "deploy_to_timeweb.sh" ]; then
        echo_error "Файл deploy_to_timeweb.sh не найден!"
        main_menu
        return
    fi
    
    read -p "Введите имя пользователя SSH: " ssh_user
    read -p "Введите домен: " domain
    
    echo ""
    echo_info "Запуск деплоя на $ssh_user@$domain..."
    
    bash deploy_to_timeweb.sh "$ssh_user" "$domain"
    
    echo_success "Деплой завершен!"
    echo ""
    read -p "Нажмите Enter для возврата в меню..."
    main_menu
}

update_project() {
    echo ""
    echo_info "=========================================="
    echo_info "Обновление проекта"
    echo_info "=========================================="
    echo ""
    
    if [ ! -d "$PROJECT_NAME" ]; then
        echo_error "Проект не найден!"
        main_menu
        return
    fi
    
    cd "$PROJECT_NAME"
    
    if [ ! -d "venv" ]; then
        echo_warning "Виртуальное окружение не найдено. Создаю..."
        python3 -m venv venv
    fi
    
    source venv/bin/activate
    
    echo_info "Обновление зависимостей..."
    pip install --upgrade pip -q
    pip install -r requirements.txt --upgrade -q
    
    echo_info "Применение миграций..."
    python manage.py migrate
    
    echo_info "Сбор статики..."
    python manage.py collectstatic --noinput
    
    echo_success "Проект обновлен!"
    echo ""
    read -p "Нажмите Enter для возврата в меню..."
    main_menu
}

run_local_server() {
    echo ""
    echo_info "=========================================="
    echo_info "Запуск локального сервера"
    echo_info "=========================================="
    echo ""
    
    if [ ! -d "$PROJECT_NAME" ]; then
        echo_error "Проект не найден! Сначала создайте проект."
        main_menu
        return
    fi
    
    cd "$PROJECT_NAME"
    
    if [ ! -d "venv" ]; then
        echo_error "Виртуальное окружение не найдено!"
        main_menu
        return
    fi
    
    source venv/bin/activate
    
    echo_info "Проверка проекта..."
    python manage.py check
    
    echo ""
    echo_success "Сервер запускается на http://127.0.0.1:8000"
    echo_info "Нажмите Ctrl+C для остановки"
    echo ""
    
    python manage.py runserver
}

check_status() {
    echo ""
    echo_info "=========================================="
    echo_info "Статус проекта"
    echo_info "=========================================="
    echo ""
    
    if [ ! -d "$PROJECT_NAME" ]; then
        echo_error "Проект не найден"
        echo ""
        read -p "Нажмите Enter для возврата в меню..."
        main_menu
        return
    fi
    
    cd "$PROJECT_NAME"
    
    # Проверка файлов
    echo_info "Проверка структуры проекта..."
    
    files=(
        "manage.py"
        "requirements.txt"
        ".env"
        "tutor_crm/settings.py"
        "tutor_crm/urls.py"
    )
    
    for file in "${files[@]}"; do
        if [ -f "$file" ]; then
            echo_success "✓ $file"
        else
            echo_error "✗ $file отсутствует"
        fi
    done
    
    echo ""
    
    # Проверка виртуального окружения
    if [ -d "venv" ]; then
        echo_success "✓ Виртуальное окружение найдено"
        
        source venv/bin/activate
        
        echo ""
        echo_info "Установленные пакеты:"
        pip list | grep -E "Django|mysql"
        
        echo ""
        echo_info "Проверка Django..."
        python manage.py check --deploy || true
        
    else
        echo_error "✗ Виртуальное окружение не найдено"
    fi
    
    echo ""
    read -p "Нажмите Enter для возврата в меню..."
    main_menu
}

# Проверка зависимостей
check_dependencies() {
    echo_info "Проверка зависимостей..."
    
    # Python
    if ! command -v python3 &> /dev/null; then
        echo_error "Python 3 не установлен"
        exit 1
    fi
    echo_success "Python 3 найден"
    
    # Git (опционально)
    if command -v git &> /dev/null; then
        echo_success "Git найден"
    else
        echo_warning "Git не найден (не критично)"
    fi
    
    echo ""
}

# Главная функция
main() {
    check_dependencies
    main_menu
}

# Обработка Ctrl+C
trap 'echo ""; echo_warning "Прервано пользователем"; exit 130' INT

# Запуск
main