"""
Команда для заполнения базы данных тестовыми данными
Путь: crm/management/commands/populate_db.py

Использование: python manage.py populate_db
"""

from django.core.management.base import BaseCommand
from django.utils import timezone
from django.db.models import Sum
from crm.models import Parent, Teacher, Room, Group, Student, Lesson, Finance, Task
from datetime import datetime, timedelta
from decimal import Decimal
import random


class Command(BaseCommand):
    help = 'Заполнить базу данных тестовыми данными'

    def handle(self, *args, **kwargs):
        self.stdout.write('Начинаю заполнение базы данных...')

        # Очистка существующих данных (опционально)
        if input('Очистить существующие данные? (yes/no): ').lower() == 'yes':
            Task.objects.all().delete()
            Finance.objects.all().delete()
            Lesson.objects.all().delete()
            Student.objects.all().delete()
            Group.objects.all().delete()
            Room.objects.all().delete()
            Teacher.objects.all().delete()
            Parent.objects.all().delete()
            self.stdout.write(self.style.WARNING('Данные очищены'))

        # 1. Создаем преподавателей
        self.stdout.write('Создаю преподавателей...')
        teachers = []
        teachers_data = [
            {
                'name': 'Иванова Мария Ивановна',
                'subjects': ['Математика', 'Алгебра', 'Геометрия'],
                'phone': '+7 912 111-22-33',
                'salary_per_lesson': Decimal('500')
            },
            {
                'name': 'Петров Александр Сергеевич',
                'subjects': ['Физика'],
                'phone': '+7 923 222-33-44',
                'salary_per_lesson': Decimal('600')
            },
            {
                'name': 'Сидорова Ольга Леонидовна',
                'subjects': ['Русский язык', 'Литература'],
                'phone': '+7 934 333-44-55',
                'salary_per_lesson': Decimal('450')
            },
            {
                'name': 'Козлов Дмитрий Петрович',
                'subjects': ['Английский язык'],
                'phone': '+7 945 444-55-66',
                'salary_per_lesson': Decimal('550')
            },
            {
                'name': 'Васильева Анна Сергеевна',
                'subjects': ['Химия', 'Биология'],
                'phone': '+7 956 555-66-77',
                'salary_per_lesson': Decimal('500')
            },
            {
                'name': 'Новиков Игорь Владимирович',
                'subjects': ['История', 'Обществознание'],
                'phone': '+7 967 666-77-88',
                'salary_per_lesson': Decimal('450')
            }
        ]

        for data in teachers_data:
            teacher = Teacher.objects.create(**data)
            teachers.append(teacher)
            self.stdout.write(f'  ✓ {teacher.name}')

        # 2. Создаем кабинеты
        self.stdout.write('Создаю кабинеты...')
        rooms = []
        for i in range(1, 3):
            room = Room.objects.create(
                name=f'Кабинет {i}',
                capacity=6
            )
            rooms.append(room)
            self.stdout.write(f'  ✓ {room.name}')

        # 3. Создаем группы
        self.stdout.write('Создаю группы...')
        groups = []
        groups_data = [
            {
                'name': 'Математика 9А',
                'subject': 'Математика',
                'teacher': teachers[0],
                'room': rooms[0],
                'schedule': [
                    {'day': 'monday', 'time': '10:00'},
                    {'day': 'wednesday', 'time': '10:00'}
                ]
            },
            {
                'name': 'Физика 11Б',
                'subject': 'Физика',
                'teacher': teachers[1],
                'room': rooms[1],
                'schedule': [
                    {'day': 'tuesday', 'time': '14:00'},
                    {'day': 'thursday', 'time': '14:00'}
                ]
            },
            {
                'name': 'Русский 10А',
                'subject': 'Русский язык',
                'teacher': teachers[2],
                'room': rooms[0],
                'schedule': [
                    {'day': 'monday', 'time': '15:00'},
                    {'day': 'friday', 'time': '15:00'}
                ]
            },
            {
                'name': 'Английский 9Б',
                'subject': 'Английский язык',
                'teacher': teachers[3],
                'room': rooms[1],
                'schedule': [
                    {'day': 'tuesday', 'time': '10:00'},
                    {'day': 'thursday', 'time': '10:00'}
                ]
            },
            {
                'name': 'Химия 10Б',
                'subject': 'Химия',
                'teacher': teachers[4],
                'room': rooms[0],
                'schedule': [
                    {'day': 'wednesday', 'time': '14:00'},
                    {'day': 'friday', 'time': '14:00'}
                ]
            }
        ]

        for data in groups_data:
            group = Group.objects.create(**data)
            groups.append(group)
            self.stdout.write(f'  ✓ {group.name}')

        # 4. Создаем родителей
        self.stdout.write('Создаю родителей...')
        parents = []
        parents_data = [
            {
                'name': 'Смирнова Елена Владимировна',
                'phone': '+7 912 345-67-89',
                'whatsapp': 'https://wa.me/79123456789',
                'telegram': '@smirnova_ev'
            },
            {
                'name': 'Петрова Анна Ивановна',
                'phone': '+7 923 456-78-90',
                'whatsapp': 'https://wa.me/79234567890',
                'telegram': '@petrova_ai'
            },
            {
                'name': 'Козлов Виктор Петрович',
                'phone': '+7 934 567-89-01',
                'whatsapp': 'https://wa.me/79345678901',
                'telegram': '@kozlov_vp'
            },
            {
                'name': 'Васильева Татьяна Сергеевна',
                'phone': '+7 945 678-90-12',
                'whatsapp': 'https://wa.me/79456789012',
                'telegram': '@vasilyeva_ts'
            },
            {
                'name': 'Новикова Ольга Дмитриевна',
                'phone': '+7 956 789-01-23',
                'whatsapp': 'https://wa.me/79567890123',
                'telegram': '@novikova_od'
            },
            {
                'name': 'Соколов Андрей Владимирович',
                'phone': '+7 967 890-12-34',
                'whatsapp': 'https://wa.me/79678901234',
                'telegram': '@sokolov_av'
            },
            {
                'name': 'Морозова Екатерина Игоревна',
                'phone': '+7 978 901-23-45',
                'whatsapp': 'https://wa.me/79789012345',
                'telegram': '@morozova_ei'
            },
            {
                'name': 'Лебедев Николай Александрович',
                'phone': '+7 989 012-34-56',
                'whatsapp': 'https://wa.me/79890123456',
                'telegram': '@lebedev_na'
            }
        ]

        for data in parents_data:
            parent = Parent.objects.create(**data)
            parents.append(parent)
            self.stdout.write(f'  ✓ {parent.name}')

        # 5. Создаем учеников
        self.stdout.write('Создаю учеников...')
        students = []
        students_data = [
            # Групповое обучение
            {
                'name': 'Алексей Смирнов',
                'grade': 9,
                'parent': parents[0],
                'subject': 'Математика',
                'learning_type': 'group',
                'group': groups[0],
                'teacher': teachers[0],
                'price_per_lesson': Decimal('1500'),
                'status': 'active'
            },
            {
                'name': 'Екатерина Смирнова',
                'grade': 11,
                'parent': parents[0],
                'subject': 'Физика',
                'learning_type': 'group',
                'group': groups[1],
                'teacher': teachers[1],
                'price_per_lesson': Decimal('1600'),
                'status': 'active'
            },
            {
                'name': 'Мария Петрова',
                'grade': 11,
                'parent': parents[1],
                'subject': 'Физика',
                'learning_type': 'individual',
                'teacher': teachers[1],
                'price_per_lesson': Decimal('2000'),
                'status': 'active'
            },
            {
                'name': 'Иван Козлов',
                'grade': 10,
                'parent': parents[2],
                'subject': 'Русский язык',
                'learning_type': 'group',
                'group': groups[2],
                'teacher': teachers[2],
                'price_per_lesson': Decimal('1200'),
                'status': 'active'
            },
            {
                'name': 'Анна Васильева',
                'grade': 9,
                'parent': parents[3],
                'subject': 'Английский язык',
                'learning_type': 'group',
                'group': groups[3],
                'teacher': teachers[3],
                'price_per_lesson': Decimal('1400'),
                'status': 'active'
            },
            {
                'name': 'Дмитрий Новиков',
                'grade': 10,
                'parent': parents[4],
                'subject': 'Химия',
                'learning_type': 'group',
                'group': groups[4],
                'teacher': teachers[4],
                'price_per_lesson': Decimal('1500'),
                'status': 'active'
            },
            {
                'name': 'Ольга Соколова',
                'grade': 11,
                'parent': parents[5],
                'subject': 'История',
                'learning_type': 'individual',
                'teacher': teachers[5],
                'price_per_lesson': Decimal('1800'),
                'status': 'active'
            },
            {
                'name': 'Петр Морозов',
                'grade': 9,
                'parent': parents[6],
                'subject': 'Математика',
                'learning_type': 'group',
                'group': groups[0],
                'teacher': teachers[0],
                'price_per_lesson': Decimal('1500'),
                'status': 'active'
            },
            {
                'name': 'Светлана Лебедева',
                'grade': 10,
                'parent': parents[7],
                'subject': 'Русский язык',
                'learning_type': 'group',
                'group': groups[2],
                'teacher': teachers[2],
                'price_per_lesson': Decimal('1200'),
                'status': 'active'
            }
        ]

        for data in students_data:
            student = Student.objects.create(**data)
            students.append(student)
            self.stdout.write(f'  ✓ {student.name} ({student.grade} класс)')

        # 6. Создаем уроки (расписание на неделю)
        self.stdout.write('Создаю расписание...')
        today = timezone.now().date()
        week_start = today - timedelta(days=today.weekday())
        
        lessons_count = 0
        
        # Групповые занятия
        for group in groups:
            for schedule_item in group.schedule:
                day_map = {
                    'monday': 0, 'tuesday': 1, 'wednesday': 2,
                    'thursday': 3, 'friday': 4, 'saturday': 5
                }
                day_offset = day_map.get(schedule_item['day'], 0)
                lesson_date = week_start + timedelta(days=day_offset)
                
                hour, minute = map(int, schedule_item['time'].split(':'))
                lesson_datetime = timezone.make_aware(
                    datetime.combine(lesson_date, datetime.min.time())
                    .replace(hour=hour, minute=minute)
                )
                
                Lesson.objects.create(
                    group=group,
                    teacher=group.teacher,
                    room=group.room,
                    lesson_type='group',
                    datetime=lesson_datetime,
                    duration=90,
                    status='scheduled'
                )
                lessons_count += 1
        
        # Индивидуальные занятия
        individual_students = [s for s in students if s.learning_type == 'individual']
        for student in individual_students:
            # 2 урока в неделю
            for day_offset in [1, 3]:  # Вторник и Четверг
                lesson_date = week_start + timedelta(days=day_offset)
                lesson_datetime = timezone.make_aware(
                    datetime.combine(lesson_date, datetime.min.time())
                    .replace(hour=16, minute=0)
                )
                
                Lesson.objects.create(
                    student=student,
                    teacher=student.teacher,
                    room=rooms[0],
                    lesson_type='individual',
                    datetime=lesson_datetime,
                    duration=60,
                    status='scheduled'
                )
                lessons_count += 1
        
        self.stdout.write(f'  ✓ Создано {lessons_count} уроков')

        # 7. Создаем финансовые операции
        self.stdout.write('Создаю финансовые операции...')
        
        # Доходы от учеников (оплаты за октябрь)
        for student in students:
            # 8 уроков в месяц * стоимость урока
            amount = student.price_per_lesson * 8
            Finance.objects.create(
                student=student,
                transaction_type='income',
                amount=amount,
                date=timezone.now().date() - timedelta(days=random.randint(1, 10)),
                description=f'Оплата за октябрь - {student.name}'
            )
        
        # Расходы на зарплаты преподавателей
        for teacher in teachers:
            # Считаем количество уроков
            lessons_taught = Lesson.objects.filter(teacher=teacher).count()
            salary = teacher.salary_per_lesson * lessons_taught * 4  # За месяц
            
            Finance.objects.create(
                teacher=teacher,
                transaction_type='expense',
                amount=salary,
                date=timezone.now().date() - timedelta(days=5),
                description=f'Зарплата за октябрь - {teacher.name}'
            )
        
        self.stdout.write(f'  ✓ Создано финансовых операций')

        # 8. Создаем задачи
        self.stdout.write('Создаю задачи...')
        tasks_data = [
            {
                'title': 'Прозвонить родителей Смирнова',
                'description': 'Обсудить успеваемость и посещаемость',
                'student': students[0],
                'due_date': timezone.now().date() + timedelta(days=2),
                'priority': 'high',
                'status': 'pending'
            },
            {
                'title': 'Подготовить материалы к контрольной работе',
                'description': 'Тема: Тригонометрия',
                'teacher': teachers[0],
                'due_date': timezone.now().date() + timedelta(days=5),
                'priority': 'medium',
                'status': 'pending'
            },
            {
                'title': 'Обсудить успеваемость с родителями Петровой',
                'student': students[2],
                'due_date': timezone.now().date() + timedelta(days=1),
                'priority': 'high',
                'status': 'pending'
            },
            {
                'title': 'Пригласить на пробное занятие',
                'description': 'Новый ученик - Максим Иванов',
                'due_date': timezone.now().date() + timedelta(days=3),
                'priority': 'medium',
                'status': 'pending'
            },
            {
                'title': 'Заказать новые учебники',
                'description': 'Физика для 11 класса',
                'teacher': teachers[1],
                'due_date': timezone.now().date() + timedelta(days=7),
                'priority': 'low',
                'status': 'pending'
            }
        ]

        for data in tasks_data:
            task = Task.objects.create(**data)
            self.stdout.write(f'  ✓ {task.title}')

        # Итоговая статистика
        self.stdout.write(self.style.SUCCESS('\n' + '='*50))
        self.stdout.write(self.style.SUCCESS('База данных успешно заполнена!'))
        self.stdout.write(self.style.SUCCESS('='*50))
        self.stdout.write(f'Преподавателей: {Teacher.objects.count()}')
        self.stdout.write(f'Кабинетов: {Room.objects.count()}')
        self.stdout.write(f'Групп: {Group.objects.count()}')
        self.stdout.write(f'Родителей: {Parent.objects.count()}')
        self.stdout.write(f'Учеников: {Student.objects.count()}')
        self.stdout.write(f'Уроков: {Lesson.objects.count()}')
        self.stdout.write(f'Финансовых операций: {Finance.objects.count()}')
        self.stdout.write(f'Задач: {Task.objects.count()}')
        self.stdout.write(self.style.SUCCESS('='*50))
        
        # Статистика по финансам
        total_income = Finance.objects.filter(
            transaction_type='income'
        ).aggregate(total=Sum('amount'))['total'] or Decimal('0')
        
        total_expenses = Finance.objects.filter(
            transaction_type='expense'
        ).aggregate(total=Sum('amount'))['total'] or Decimal('0')
        
        self.stdout.write(f'\nДоход: {total_income} ₽')
        self.stdout.write(f'Расходы: {total_expenses} ₽')
        self.stdout.write(f'Прибыль: {total_income - total_expenses} ₽')
        
        self.stdout.write(self.style.SUCCESS('\n✓ Готово! Можете начинать работу с CRM'))
        self.stdout.write('\nДля доступа в админку:')
        self.stdout.write('URL: http://localhost:8000/admin')
        self.stdout.write('Создайте суперпользователя: python manage.py createsuperuser')