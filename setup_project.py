#!/usr/bin/env python3
"""
Генератор структуры проекта CRM для репетиторского центра
Использование: python setup_project.py
"""

import os
import sys

PROJECT_NAME = "tutor_crm"
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# Структура проекта
STRUCTURE = {
    f"{PROJECT_NAME}/": {
        "__init__.py": "",
        "settings.py": "SETTINGS_CONTENT",
        "urls.py": "URLS_CONTENT",
        "wsgi.py": "WSGI_CONTENT",
        "asgi.py": "ASGI_CONTENT",
    },
    "apps/": {
        "students/": {
            "__init__.py": "",
            "models.py": "STUDENTS_MODELS",
            "views.py": "STUDENTS_VIEWS",
            "urls.py": "STUDENTS_URLS",
            "admin.py": "STUDENTS_ADMIN",
            "forms.py": "STUDENTS_FORMS",
        },
        "teachers/": {
            "__init__.py": "",
            "models.py": "TEACHERS_MODELS",
            "views.py": "TEACHERS_VIEWS",
            "urls.py": "TEACHERS_URLS",
            "admin.py": "TEACHERS_ADMIN",
        },
        "schedule/": {
            "__init__.py": "",
            "models.py": "SCHEDULE_MODELS",
            "views.py": "SCHEDULE_VIEWS",
            "urls.py": "SCHEDULE_URLS",
            "admin.py": "SCHEDULE_ADMIN",
        },
        "finance/": {
            "__init__.py": "",
            "models.py": "FINANCE_MODELS",
            "views.py": "FINANCE_VIEWS",
            "urls.py": "FINANCE_URLS",
            "admin.py": "FINANCE_ADMIN",
        },
        "tasks/": {
            "__init__.py": "",
            "models.py": "TASKS_MODELS",
            "views.py": "TASKS_VIEWS",
            "urls.py": "TASKS_URLS",
            "admin.py": "TASKS_ADMIN",
        },
    },
    "templates/": {
        "base.html": "BASE_TEMPLATE",
        "dashboard.html": "DASHBOARD_TEMPLATE",
        "students/": {
            "student_list.html": "STUDENT_LIST_TEMPLATE",
            "student_detail.html": "STUDENT_DETAIL_TEMPLATE",
        },
    },
    "static/": {
        "css/": {
            "styles.css": "CSS_CONTENT",
        },
        "js/": {
            "main.js": "JS_CONTENT",
        },
    },
    "public_html/": {
        ".htaccess": "HTACCESS_CONTENT",
        "wsgi.py": "PUBLIC_WSGI_CONTENT",
    },
}

# Содержимое файлов
CONTENTS = {
    "SETTINGS_CONTENT": """import os
from pathlib import Path
import environ

env = environ.Env(DEBUG=(bool, False))
BASE_DIR = Path(__file__).resolve().parent.parent
environ.Env.read_env(os.path.join(BASE_DIR, '.env'))

SECRET_KEY = env('SECRET_KEY', default='dev-secret-key')
DEBUG = env('DEBUG')
ALLOWED_HOSTS = env.list('ALLOWED_HOSTS', default=[])

INSTALLED_APPS = [
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
    'rest_framework',
    'apps.students',
    'apps.teachers',
    'apps.schedule',
    'apps.finance',
    'apps.tasks',
]

MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

ROOT_URLCONF = 'tutor_crm.urls'

TEMPLATES = [{
    'BACKEND': 'django.template.backends.django.DjangoTemplates',
    'DIRS': [os.path.join(BASE_DIR, 'templates')],
    'APP_DIRS': True,
    'OPTIONS': {
        'context_processors': [
            'django.template.context_processors.debug',
            'django.template.context_processors.request',
            'django.contrib.auth.context_processors.auth',
            'django.contrib.messages.context_processors.messages',
        ],
    },
}]

WSGI_APPLICATION = 'tutor_crm.wsgi.application'

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': env('DB_NAME'),
        'USER': env('DB_USER'),
        'PASSWORD': env('DB_PASSWORD'),
        'HOST': env('DB_HOST', default='localhost'),
        'PORT': env('DB_PORT', default='3306'),
        'OPTIONS': {'charset': 'utf8mb4'},
    }
}

AUTH_PASSWORD_VALIDATORS = [
    {'NAME': 'django.contrib.auth.password_validation.UserAttributeSimilarityValidator'},
    {'NAME': 'django.contrib.auth.password_validation.MinimumLengthValidator'},
]

LANGUAGE_CODE = 'ru-ru'
TIME_ZONE = 'Europe/Moscow'
USE_I18N = True
USE_L10N = True
USE_TZ = True

STATIC_URL = '/static/'
STATIC_ROOT = os.path.join(BASE_DIR, 'staticfiles')
STATICFILES_DIRS = [os.path.join(BASE_DIR, 'static')]
MEDIA_URL = '/media/'
MEDIA_ROOT = os.path.join(BASE_DIR, 'media')
DEFAULT_AUTO_FIELD = 'django.db.models.BigAutoField'

if not DEBUG:
    SECURE_SSL_REDIRECT = True
    SESSION_COOKIE_SECURE = True
    CSRF_COOKIE_SECURE = True
""",

    "URLS_CONTENT": """from django.contrib import admin
from django.urls import path, include
from django.views.generic import RedirectView

urlpatterns = [
    path('admin/', admin.site.urls),
    path('', RedirectView.as_view(url='/dashboard/', permanent=False)),
    path('dashboard/', include('apps.students.urls')),
    path('students/', include('apps.students.urls')),
    path('teachers/', include('apps.teachers.urls')),
    path('schedule/', include('apps.schedule.urls')),
    path('finance/', include('apps.finance.urls')),
    path('tasks/', include('apps.tasks.urls')),
]
""",

    "WSGI_CONTENT": """import os
from django.core.wsgi import get_wsgi_application
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'tutor_crm.settings')
application = get_wsgi_application()
""",

    "ASGI_CONTENT": """import os
from django.core.asgi import get_asgi_application
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'tutor_crm.settings')
application = get_asgi_application()
""",

    "STUDENTS_MODELS": """from django.db import models

class Parent(models.Model):
    name = models.CharField('Имя родителя', max_length=200)
    phone = models.CharField('Телефон', max_length=17)
    whatsapp = models.URLField('WhatsApp', blank=True)
    telegram = models.URLField('Telegram', blank=True)

    class Meta:
        verbose_name = 'Родитель'
        verbose_name_plural = 'Родители'

    def __str__(self):
        return self.name

class Student(models.Model):
    name = models.CharField('Имя ученика', max_length=200)
    grade = models.IntegerField('Класс')
    parent = models.ForeignKey(Parent, on_delete=models.PROTECT)
    subject = models.CharField('Предмет', max_length=100)
    learning_type = models.CharField('Тип обучения', max_length=20)
    status = models.CharField('Статус', max_length=20, default='active')

    class Meta:
        verbose_name = 'Ученик'
        verbose_name_plural = 'Ученики'

    def __str__(self):
        return self.name
""",

    "STUDENTS_VIEWS": """from django.shortcuts import render, get_object_or_404
from django.contrib.auth.decorators import login_required
from .models import Student

@login_required
def dashboard(request):
    return render(request, 'dashboard.html')

@login_required
def student_list(request):
    students = Student.objects.all()
    return render(request, 'students/student_list.html', {'students': students})

@login_required
def student_detail(request, pk):
    student = get_object_or_404(Student, pk=pk)
    return render(request, 'students/student_detail.html', {'student': student})
""",

    "STUDENTS_URLS": """from django.urls import path
from . import views

urlpatterns = [
    path('', views.dashboard, name='dashboard'),
    path('list/', views.student_list, name='student_list'),
    path('<int:pk>/', views.student_detail, name='student_detail'),
]
""",

    "STUDENTS_ADMIN": """from django.contrib import admin
from .models import Student, Parent

admin.site.register(Parent)
admin.site.register(Student)
""",

    "STUDENTS_FORMS": """from django import forms
from .models import Student

class StudentForm(forms.ModelForm):
    class Meta:
        model = Student
        fields = '__all__'
""",

    "TEACHERS_MODELS": """from django.db import models

class Teacher(models.Model):
    name = models.CharField('Имя', max_length=200)
    subjects = models.CharField('Предметы', max_length=300)

    class Meta:
        verbose_name = 'Преподаватель'
        verbose_name_plural = 'Преподаватели'

    def __str__(self):
        return self.name
""",

    "TEACHERS_VIEWS": """from django.shortcuts import render

def teacher_list(request):
    return render(request, 'teachers/teacher_list.html')
""",

    "TEACHERS_URLS": """from django.urls import path
from . import views

urlpatterns = [
    path('', views.teacher_list, name='teacher_list'),
]
""",

    "TEACHERS_ADMIN": """from django.contrib import admin
from .models import Teacher

admin.site.register(Teacher)
""",

    "SCHEDULE_MODELS": """from django.db import models

class Room(models.Model):
    name = models.CharField('Название', max_length=100)

    class Meta:
        verbose_name = 'Кабинет'
        verbose_name_plural = 'Кабинеты'

    def __str__(self):
        return self.name
""",

    "SCHEDULE_VIEWS": """from django.shortcuts import render

def schedule_view(request):
    return render(request, 'schedule/schedule.html')
""",

    "SCHEDULE_URLS": """from django.urls import path
from . import views

urlpatterns = [
    path('', views.schedule_view, name='schedule'),
]
""",

    "SCHEDULE_ADMIN": """from django.contrib import admin
from .models import Room

admin.site.register(Room)
""",

    "FINANCE_MODELS": """from django.db import models

class Payment(models.Model):
    amount = models.DecimalField('Сумма', max_digits=10, decimal_places=2)
    payment_date = models.DateField('Дата')

    class Meta:
        verbose_name = 'Платеж'
        verbose_name_plural = 'Платежи'
""",

    "FINANCE_VIEWS": """from django.shortcuts import render

def finance_dashboard(request):
    return render(request, 'finance/finance_dashboard.html')
""",

    "FINANCE_URLS": """from django.urls import path
from . import views

urlpatterns = [
    path('', views.finance_dashboard, name='finance_dashboard'),
]
""",

    "FINANCE_ADMIN": """from django.contrib import admin
from .models import Payment

admin.site.register(Payment)
""",

    "TASKS_MODELS": """from django.db import models

class Task(models.Model):
    title = models.CharField('Заголовок', max_length=200)
    description = models.TextField('Описание')

    class Meta:
        verbose_name = 'Задача'
        verbose_name_plural = 'Задачи'

    def __str__(self):
        return self.title
""",

    "TASKS_VIEWS": """from django.shortcuts import render

def task_list(request):
    return render(request, 'tasks/task_list.html')
""",

    "TASKS_URLS": """from django.urls import path
from . import views

urlpatterns = [
    path('', views.task_list, name='task_list'),
]
""",

    "TASKS_ADMIN": """from django.contrib import admin
from .models import Task

admin.site.register(Task)
""",

    "BASE_TEMPLATE": """<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Репетиторский центр</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold">CRM Центр</h1>
        </div>
    </nav>
    <main class="container mx-auto p-4">
        {% block content %}{% endblock %}
    </main>
</body>
</html>""",

    "DASHBOARD_TEMPLATE": """{% extends 'base.html' %}
{% block content %}
<h2 class="text-3xl font-bold mb-6">Dashboard</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-semibold">Ученики</h3>
        <p class="text-4xl font-bold text-blue-600">0</p>
    </div>
</div>
{% endblock %}""",

    "STUDENT_LIST_TEMPLATE": """{% extends 'base.html' %}
{% block content %}
<h2 class="text-3xl font-bold mb-6">Список учеников</h2>
<div class="bg-white rounded-lg shadow p-6">
    <p>Список учеников будет здесь</p>
</div>
{% endblock %}""",

    "STUDENT_DETAIL_TEMPLATE": """{% extends 'base.html' %}
{% block content %}
<h2 class="text-3xl font-bold mb-6">{{ student.name }}</h2>
<div class="bg-white rounded-lg shadow p-6">
    <p>Класс: {{ student.grade }}</p>
</div>
{% endblock %}""",

    "CSS_CONTENT": """body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}""",

    "JS_CONTENT": """console.log('CRM System loaded');""",

    "HTACCESS_CONTENT": """Options +ExecCGI
AddHandler wsgi-script .py
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_URI} ^/static/ [OR]
RewriteCond %{REQUEST_URI} ^/media/
RewriteRule ^(.*)$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /wsgi.py/$1 [QSA,L]""",

    "PUBLIC_WSGI_CONTENT": """#!/usr/bin/python3
import sys
import os

project_path = '/home/c/cw95865/public_html/tutor_crm'
venv_path = '/home/c/cw95865/venv'

sys.path.insert(0, project_path)
sys.path.insert(0, os.path.join(venv_path, 'lib/python3.6/site-packages'))

activate_this = os.path.join(venv_path, 'bin/activate_this.py')
if os.path.exists(activate_this):
    with open(activate_this) as f:
        exec(f.read(), {'__file__': activate_this})

os.environ['DJANGO_SETTINGS_MODULE'] = 'tutor_crm.settings'

from django.core.wsgi import get_wsgi_application
application = get_wsgi_application()""",
}


def create_structure(base_path, structure, level=0):
    """Рекурсивно создает структуру папок и файлов"""
    for name, content in structure.items():
        path = os.path.join(base_path, name)
        
        if isinstance(content, dict):
            os.makedirs(path, exist_ok=True)
            print(f"{'  ' * level}📁 {name}")
            create_structure(path, content, level + 1)
        else:
            file_content = CONTENTS.get(content, content)
            with open(path, 'w', encoding='utf-8') as f:
                f.write(file_content)
            print(f"{'  ' * level}📄 {name}")


def create_additional_files():
    """Создает дополнительные конфигурационные файлы"""
    files = {
        'manage.py': '''#!/usr/bin/env python
import os
import sys

if __name__ == '__main__':
    os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'tutor_crm.settings')
    try:
        from django.core.management import execute_from_command_line
    except ImportError as exc:
        raise ImportError("Couldn't import Django.") from exc
    execute_from_command_line(sys.argv)
''',
        'requirements.txt': '''Django==3.2.25
django-environ==0.11.2
mysqlclient==2.1.1
djangorestframework==3.14.0
''',
        '.env.example': '''DEBUG=True
SECRET_KEY=your-secret-key
ALLOWED_HOSTS=localhost,127.0.0.1,cw95865.tmweb.ru

DB_NAME=cw95865_rmtutori
DB_USER=cw95865_rmtutori
DB_PASSWORD=123456789
DB_HOST=localhost
DB_PORT=3306
''',
        'README.md': '''# CRM для репетиторского центра

## Установка
1. python manage.py migrate
2. python manage.py createsuperuser
3. python manage.py runserver
''',
    }
    
    for filename, content in files.items():
        with open(filename, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"📄 {filename}")


def main():
    print("=" * 60)
    print("🚀 Генератор проекта CRM")
    print("=" * 60)
    print()
    
    print("📦 Создание структуры проекта...\n")
    create_structure(BASE_DIR, STRUCTURE)
    
    print("\n📝 Создание дополнительных файлов...\n")
    create_additional_files()
    
    print("\n" + "=" * 60)
    print("✅ Проект успешно создан!")
    print("=" * 60)
    print("\nСледующие шаги:")
    print("1. cd tutor_crm")
    print("2. python -m venv venv")
    print("3. venv\\Scripts\\activate (Windows)")
    print("4. pip install -r requirements.txt")
    print("5. python manage.py migrate")
    print()


if __name__ == '__main__':
    main()