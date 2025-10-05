from django.db import models
from django.core.validators import MinValueValidator, MaxValueValidator
from django.utils import timezone


class Parent(models.Model):
    """Модель родителя"""
    name = models.CharField(max_length=200, verbose_name='Имя родителя')
    phone = models.CharField(max_length=20, verbose_name='Телефон')
    whatsapp = models.CharField(max_length=200, blank=True, verbose_name='WhatsApp')
    telegram = models.CharField(max_length=200, blank=True, verbose_name='Telegram')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = 'Родитель'
        verbose_name_plural = 'Родители'
        ordering = ['name']

    def __str__(self):
        return self.name


class Teacher(models.Model):
    """Модель преподавателя"""
    name = models.CharField(max_length=200, verbose_name='Имя преподавателя')
    subjects = models.JSONField(default=list, verbose_name='Предметы')
    phone = models.CharField(max_length=20, blank=True, verbose_name='Телефон')
    salary_per_lesson = models.DecimalField(
        max_digits=10, 
        decimal_places=2, 
        default=0,
        verbose_name='Зарплата за урок'
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = 'Преподаватель'
        verbose_name_plural = 'Преподаватели'
        ordering = ['name']

    def __str__(self):
        return self.name


class Room(models.Model):
    """Модель кабинета"""
    name = models.CharField(max_length=100, verbose_name='Название кабинета')
    capacity = models.IntegerField(
        default=6, 
        validators=[MinValueValidator(1)],
        verbose_name='Вместимость'
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = 'Кабинет'
        verbose_name_plural = 'Кабинеты'
        ordering = ['name']

    def __str__(self):
        return self.name


class Group(models.Model):
    """Модель группы"""
    name = models.CharField(max_length=200, verbose_name='Название группы')
    subject = models.CharField(max_length=100, verbose_name='Предмет')
    teacher = models.ForeignKey(
        Teacher, 
        on_delete=models.SET_NULL, 
        null=True,
        related_name='groups',
        verbose_name='Преподаватель'
    )
    room = models.ForeignKey(
        Room, 
        on_delete=models.SET_NULL, 
        null=True,
        related_name='groups',
        verbose_name='Кабинет'
    )
    max_students = models.IntegerField(
        default=6,
        validators=[MinValueValidator(1), MaxValueValidator(10)],
        verbose_name='Максимум учеников'
    )
    schedule = models.JSONField(
        default=list,
        verbose_name='Расписание',
        help_text='[{"day": "monday", "time": "10:00"}, ...]'
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = 'Группа'
        verbose_name_plural = 'Группы'
        ordering = ['name']

    def __str__(self):
        return self.name

    @property
    def students_count(self):
        return self.students.filter(status='active').count()

    @property
    def is_full(self):
        return self.students_count >= self.max_students


class Student(models.Model):
    """Модель ученика"""
    STATUS_CHOICES = [
        ('active', 'Активен'),
        ('inactive', 'Временно не ходит'),
        ('archived', 'Архив'),
    ]
    
    TYPE_CHOICES = [
        ('individual', 'Индивидуальное'),
        ('group', 'Групповое'),
    ]

    name = models.CharField(max_length=200, verbose_name='Имя ученика')
    grade = models.IntegerField(
        validators=[MinValueValidator(1), MaxValueValidator(11)],
        verbose_name='Класс'
    )
    parent = models.ForeignKey(
        Parent,
        on_delete=models.CASCADE,
        related_name='children',
        verbose_name='Родитель'
    )
    subject = models.CharField(max_length=100, verbose_name='Предмет')
    learning_type = models.CharField(
        max_length=20,
        choices=TYPE_CHOICES,
        default='individual',
        verbose_name='Тип обучения'
    )
    group = models.ForeignKey(
        Group,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name='students',
        verbose_name='Группа'
    )
    teacher = models.ForeignKey(
        Teacher,
        on_delete=models.SET_NULL,
        null=True,
        related_name='students',
        verbose_name='Преподаватель'
    )
    status = models.CharField(
        max_length=20,
        choices=STATUS_CHOICES,
        default='active',
        verbose_name='Статус'
    )
    last_lesson_date = models.DateField(
        null=True,
        blank=True,
        verbose_name='Дата последнего урока'
    )
    price_per_lesson = models.DecimalField(
        max_digits=10,
        decimal_places=2,
        default=0,
        verbose_name='Стоимость урока'
    )
    notes = models.TextField(blank=True, verbose_name='Заметки')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = 'Ученик'
        verbose_name_plural = 'Ученики'
        ordering = ['name']

    def __str__(self):
        return f"{self.name} ({self.grade} класс)"


class Lesson(models.Model):
    """Модель урока"""
    LESSON_TYPE_CHOICES = [
        ('individual', 'Индивидуальный'),
        ('group', 'Групповой'),
    ]

    STATUS_CHOICES = [
        ('scheduled', 'Запланирован'),
        ('completed', 'Проведен'),
        ('cancelled', 'Отменен'),
    ]

    student = models.ForeignKey(
        Student,
        on_delete=models.CASCADE,
        null=True,
        blank=True,
        related_name='lessons',
        verbose_name='Ученик'
    )
    group = models.ForeignKey(
        Group,
        on_delete=models.CASCADE,
        null=True,
        blank=True,
        related_name='lessons',
        verbose_name='Группа'
    )
    teacher = models.ForeignKey(
        Teacher,
        on_delete=models.CASCADE,
        related_name='lessons',
        verbose_name='Преподаватель'
    )
    room = models.ForeignKey(
        Room,
        on_delete=models.CASCADE,
        related_name='lessons',
        verbose_name='Кабинет'
    )
    lesson_type = models.CharField(
        max_length=20,
        choices=LESSON_TYPE_CHOICES,
        verbose_name='Тип урока'
    )
    datetime = models.DateTimeField(verbose_name='Дата и время')
    duration = models.IntegerField(
        default=60,
        verbose_name='Длительность (минуты)'
    )
    status = models.CharField(
        max_length=20,
        choices=STATUS_CHOICES,
        default='scheduled',
        verbose_name='Статус'
    )
    notes = models.TextField(blank=True, verbose_name='Заметки')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = 'Урок'
        verbose_name_plural = 'Уроки'
        ordering = ['-datetime']

    def __str__(self):
        if self.group:
            return f"{self.group.name} - {self.datetime.strftime('%d.%m.%Y %H:%M')}"
        return f"{self.student.name} - {self.datetime.strftime('%d.%m.%Y %H:%M')}"


class Finance(models.Model):
    """Модель финансов"""
    TRANSACTION_TYPE_CHOICES = [
        ('income', 'Доход'),
        ('expense', 'Расход'),
    ]

    student = models.ForeignKey(
        Student,
        on_delete=models.CASCADE,
        null=True,
        blank=True,
        related_name='payments',
        verbose_name='Ученик'
    )
    teacher = models.ForeignKey(
        Teacher,
        on_delete=models.CASCADE,
        null=True,
        blank=True,
        related_name='salaries',
        verbose_name='Преподаватель'
    )
    transaction_type = models.CharField(
        max_length=20,
        choices=TRANSACTION_TYPE_CHOICES,
        verbose_name='Тип транзакции'
    )
    amount = models.DecimalField(
        max_digits=10,
        decimal_places=2,
        verbose_name='Сумма'
    )
    date = models.DateField(default=timezone.now, verbose_name='Дата')
    description = models.TextField(blank=True, verbose_name='Описание')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = 'Финансовая операция'
        verbose_name_plural = 'Финансы'
        ordering = ['-date']

    def __str__(self):
        return f"{self.get_transaction_type_display()} - {self.amount} ({self.date})"


class Task(models.Model):
    """Модель задачи"""
    STATUS_CHOICES = [
        ('pending', 'В работе'),
        ('completed', 'Выполнено'),
        ('cancelled', 'Отменено'),
    ]

    PRIORITY_CHOICES = [
        ('low', 'Низкий'),
        ('medium', 'Средний'),
        ('high', 'Высокий'),
    ]

    title = models.CharField(max_length=200, verbose_name='Название задачи')
    description = models.TextField(blank=True, verbose_name='Описание')
    student = models.ForeignKey(
        Student,
        on_delete=models.CASCADE,
        null=True,
        blank=True,
        related_name='tasks',
        verbose_name='Ученик'
    )
    teacher = models.ForeignKey(
        Teacher,
        on_delete=models.CASCADE,
        null=True,
        blank=True,
        related_name='tasks',
        verbose_name='Преподаватель'
    )
    due_date = models.DateField(verbose_name='Срок выполнения')
    status = models.CharField(
        max_length=20,
        choices=STATUS_CHOICES,
        default='pending',
        verbose_name='Статус'
    )
    priority = models.CharField(
        max_length=20,
        choices=PRIORITY_CHOICES,
        default='medium',
        verbose_name='Приоритет'
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = 'Задача'
        verbose_name_plural = 'Задачи'
        ordering = ['due_date', '-priority']

    def __str__(self):
        return self.title