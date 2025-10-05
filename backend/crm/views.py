from rest_framework import viewsets, status
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework.permissions import IsAuthenticated
from django.db.models import Sum, Count, Q
from django.utils import timezone
from datetime import datetime, timedelta
from decimal import Decimal

from .models import Parent, Teacher, Room, Group, Student, Lesson, Finance, Task
from .serializers import (
    ParentSerializer, TeacherSerializer, RoomSerializer, GroupSerializer,
    StudentListSerializer, StudentDetailSerializer, StudentCreateUpdateSerializer,
    LessonSerializer, FinanceSerializer, TaskSerializer, DashboardSerializer
)


class ParentViewSet(viewsets.ModelViewSet):
    queryset = Parent.objects.all()
    serializer_class = ParentSerializer
    permission_classes = [IsAuthenticated]
    
    def get_queryset(self):
        queryset = Parent.objects.all()
        search = self.request.query_params.get('search', None)
        
        if search:
            queryset = queryset.filter(
                Q(name__icontains=search) | Q(phone__icontains=search)
            )
        
        return queryset


class TeacherViewSet(viewsets.ModelViewSet):
    queryset = Teacher.objects.all()
    serializer_class = TeacherSerializer
    permission_classes = [IsAuthenticated]
    
    def get_queryset(self):
        queryset = Teacher.objects.all()
        subject = self.request.query_params.get('subject', None)
        
        if subject:
            queryset = queryset.filter(subjects__contains=[subject])
        
        return queryset
    
    @action(detail=True, methods=['get'])
    def schedule(self, request, pk=None):
        """Получить расписание преподавателя"""
        teacher = self.get_object()
        date_from = request.query_params.get('date_from')
        date_to = request.query_params.get('date_to')
        
        lessons = Lesson.objects.filter(teacher=teacher)
        
        if date_from:
            lessons = lessons.filter(datetime__gte=date_from)
        if date_to:
            lessons = lessons.filter(datetime__lte=date_to)
        
        serializer = LessonSerializer(lessons, many=True)
        return Response(serializer.data)


class RoomViewSet(viewsets.ModelViewSet):
    queryset = Room.objects.all()
    serializer_class = RoomSerializer
    permission_classes = [IsAuthenticated]


class GroupViewSet(viewsets.ModelViewSet):
    queryset = Group.objects.all()
    serializer_class = GroupSerializer
    permission_classes = [IsAuthenticated]
    
    def get_queryset(self):
        queryset = Group.objects.all()
        subject = self.request.query_params.get('subject', None)
        teacher_id = self.request.query_params.get('teacher', None)
        
        if subject:
            queryset = queryset.filter(subject=subject)
        if teacher_id:
            queryset = queryset.filter(teacher_id=teacher_id)
        
        return queryset
    
    @action(detail=True, methods=['get'])
    def students(self, request, pk=None):
        """Получить учеников группы"""
        group = self.get_object()
        students = group.students.filter(status='active')
        serializer = StudentListSerializer(students, many=True)
        return Response(serializer.data)


class StudentViewSet(viewsets.ModelViewSet):
    queryset = Student.objects.all()
    permission_classes = [IsAuthenticated]
    
    def get_serializer_class(self):
        if self.action == 'list':
            return StudentListSerializer
        elif self.action in ['create', 'update', 'partial_update']:
            return StudentCreateUpdateSerializer
        return StudentDetailSerializer
    
    def get_queryset(self):
        queryset = Student.objects.select_related('parent', 'teacher', 'group').all()
        
        status_filter = self.request.query_params.get('status', None)
        grade = self.request.query_params.get('grade', None)
        subject = self.request.query_params.get('subject', None)
        learning_type = self.request.query_params.get('learning_type', None)
        search = self.request.query_params.get('search', None)
        
        if status_filter:
            queryset = queryset.filter(status=status_filter)
        if grade:
            queryset = queryset.filter(grade=grade)
        if subject:
            queryset = queryset.filter(subject=subject)
        if learning_type:
            queryset = queryset.filter(learning_type=learning_type)
        if search:
            queryset = queryset.filter(
                Q(name__icontains=search) | 
                Q(parent__name__icontains=search) |
                Q(parent__phone__icontains=search)
            )
        
        return queryset
    
    @action(detail=True, methods=['get'])
    def lessons(self, request, pk=None):
        """Получить уроки ученика"""
        student = self.get_object()
        lessons = student.lessons.all()[:20]
        serializer = LessonSerializer(lessons, many=True)
        return Response(serializer.data)
    
    @action(detail=True, methods=['get'])
    def payments(self, request, pk=None):
        """Получить платежи ученика"""
        student = self.get_object()
        payments = student.payments.all()
        serializer = FinanceSerializer(payments, many=True)
        return Response(serializer.data)
    
    @action(detail=True, methods=['post'])
    def change_group(self, request, pk=None):
        """Перевести ученика в другую группу"""
        student = self.get_object()
        group_id = request.data.get('group_id')
        
        if not group_id:
            return Response(
                {'error': 'Необходимо указать группу'},
                status=status.HTTP_400_BAD_REQUEST
            )
        
        try:
            group = Group.objects.get(id=group_id)
            
            if group.is_full:
                return Response(
                    {'error': f'Группа {group.name} заполнена'},
                    status=status.HTTP_400_BAD_REQUEST
                )
            
            student.group = group
            student.learning_type = 'group'
            student.teacher = group.teacher
            student.save()
            
            serializer = self.get_serializer(student)
            return Response(serializer.data)
            
        except Group.DoesNotExist:
            return Response(
                {'error': 'Группа не найдена'},
                status=status.HTTP_404_NOT_FOUND
            )


class LessonViewSet(viewsets.ModelViewSet):
    queryset = Lesson.objects.all()
    serializer_class = LessonSerializer
    permission_classes = [IsAuthenticated]
    
    def get_queryset(self):
        queryset = Lesson.objects.select_related(
            'student', 'group', 'teacher', 'room'
        ).all()
        
        date_from = self.request.query_params.get('date_from')
        date_to = self.request.query_params.get('date_to')
        teacher_id = self.request.query_params.get('teacher')
        room_id = self.request.query_params.get('room')
        status_filter = self.request.query_params.get('status')
        
        if date_from:
            queryset = queryset.filter(datetime__gte=date_from)
        if date_to:
            queryset = queryset.filter(datetime__lte=date_to)
        if teacher_id:
            queryset = queryset.filter(teacher_id=teacher_id)
        if room_id:
            queryset = queryset.filter(room_id=room_id)
        if status_filter:
            queryset = queryset.filter(status=status_filter)
        
        return queryset
    
    @action(detail=False, methods=['get'])
    def weekly(self, request):
        """Получить расписание на неделю"""
        today = timezone.now().date()
        week_start = today - timedelta(days=today.weekday())
        week_end = week_start + timedelta(days=6)
        
        lessons = self.get_queryset().filter(
            datetime__date__gte=week_start,
            datetime__date__lte=week_end
        )
        
        serializer = self.get_serializer(lessons, many=True)
        return Response(serializer.data)
    
    @action(detail=True, methods=['post'])
    def complete(self, request, pk=None):
        """Отметить урок как проведенный"""
        lesson = self.get_object()
        lesson.status = 'completed'
        lesson.save()
        
        # Обновляем дату последнего урока у ученика
        if lesson.student:
            lesson.student.last_lesson_date = lesson.datetime.date()
            lesson.student.save()
        
        serializer = self.get_serializer(lesson)
        return Response(serializer.data)


class FinanceViewSet(viewsets.ModelViewSet):
    queryset = Finance.objects.all()
    serializer_class = FinanceSerializer
    permission_classes = [IsAuthenticated]
    
    def get_queryset(self):
        queryset = Finance.objects.select_related('student', 'teacher').all()
        
        transaction_type = self.request.query_params.get('type')
        date_from = self.request.query_params.get('date_from')
        date_to = self.request.query_params.get('date_to')
        student_id = self.request.query_params.get('student')
        teacher_id = self.request.query_params.get('teacher')
        
        if transaction_type:
            queryset = queryset.filter(transaction_type=transaction_type)
        if date_from:
            queryset = queryset.filter(date__gte=date_from)
        if date_to:
            queryset = queryset.filter(date__lte=date_to)
        if student_id:
            queryset = queryset.filter(student_id=student_id)
        if teacher_id:
            queryset = queryset.filter(teacher_id=teacher_id)
        
        return queryset
    
    @action(detail=False, methods=['get'])
    def monthly_stats(self, request):
        """Статистика по месяцам"""
        today = timezone.now().date()
        month_start = today.replace(day=1)
        
        income = Finance.objects.filter(
            transaction_type='income',
            date__gte=month_start
        ).aggregate(total=Sum('amount'))['total'] or Decimal('0')
        
        expenses = Finance.objects.filter(
            transaction_type='expense',
            date__gte=month_start
        ).aggregate(total=Sum('amount'))['total'] or Decimal('0')
        
        return Response({
            'income': income,
            'expenses': expenses,
            'profit': income - expenses
        })
    
    @action(detail=False, methods=['get'])
    def by_subject(self, request):
        """Статистика дохода по предметам"""
        month = request.query_params.get('month')
        
        queryset = Finance.objects.filter(
            transaction_type='income',
            student__isnull=False
        )
        
        if month:
            queryset = queryset.filter(date__month=month)
        
        stats = []
        subjects = Student.objects.values_list('subject', flat=True).distinct()
        
        for subject in subjects:
            total = queryset.filter(
                student__subject=subject
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0')
            
            if total > 0:
                stats.append({
                    'subject': subject,
                    'total': total
                })
        
        return Response(stats)


class TaskViewSet(viewsets.ModelViewSet):
    queryset = Task.objects.all()
    serializer_class = TaskSerializer
    permission_classes = [IsAuthenticated]
    
    def get_queryset(self):
        queryset = Task.objects.select_related('student', 'teacher').all()
        
        status_filter = self.request.query_params.get('status')
        priority = self.request.query_params.get('priority')
        student_id = self.request.query_params.get('student')
        overdue = self.request.query_params.get('overdue')
        
        if status_filter:
            queryset = queryset.filter(status=status_filter)
        if priority:
            queryset = queryset.filter(priority=priority)
        if student_id:
            queryset = queryset.filter(student_id=student_id)
        if overdue == 'true':
            queryset = queryset.filter(
                due_date__lt=timezone.now().date(),
                status='pending'
            )
        
        return queryset
    
    @action(detail=True, methods=['post'])
    def complete(self, request, pk=None):
        """Отметить задачу как выполненную"""
        task = self.get_object()
        task.status = 'completed'
        task.save()
        
        serializer = self.get_serializer(task)
        return Response(serializer.data)


class DashboardViewSet(viewsets.ViewSet):
    """ViewSet для дашборда с общей статистикой"""
    permission_classes = [IsAuthenticated]
    
    @action(detail=False, methods=['get'])
    def stats(self, request):
        """Получить общую статистику"""
        today = timezone.now().date()
        month_start = today.replace(day=1)
        week_start = today - timedelta(days=today.weekday())
        
        # Основная статистика
        active_students = Student.objects.filter(status='active').count()
        total_groups = Group.objects.count()
        total_teachers = Teacher.objects.count()
        pending_tasks = Task.objects.filter(status='pending').count()
        
        # Финансовая статистика за месяц
        monthly_income = Finance.objects.filter(
            transaction_type='income',
            date__gte=month_start
        ).aggregate(total=Sum('amount'))['total'] or Decimal('0')
        
        monthly_expenses = Finance.objects.filter(
            transaction_type='expense',
            date__gte=month_start
        ).aggregate(total=Sum('amount'))['total'] or Decimal('0')
        
        # Уроки на этой неделе
        lessons_this_week = Lesson.objects.filter(
            datetime__date__gte=week_start,
            datetime__date__lte=today
        ).count()
        
        # Статистика по группам
        groups = Group.objects.all()
        groups_stats = []
        for group in groups:
            groups_stats.append({
                'id': group.id,
                'name': group.name,
                'subject': group.subject,
                'students_count': group.students_count,
                'max_students': group.max_students,
                'teacher': group.teacher.name if group.teacher else None,
                'room': group.room.name if group.room else None
            })
        
        # Статистика по предметам
        subjects = Student.objects.filter(status='active').values('subject').annotate(
            count=Count('id')
        )
        subjects_stats = []
        for subj in subjects:
            income = Finance.objects.filter(
                transaction_type='income',
                student__subject=subj['subject'],
                date__gte=month_start
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0')
            
            subjects_stats.append({
                'subject': subj['subject'],
                'students': subj['count'],
                'income': income
            })
        
        # Статистика по классам
        grades = Student.objects.filter(status='active').values('grade').annotate(
            count=Count('id')
        )
        grades_stats = []
        for grade in grades:
            income = Finance.objects.filter(
                transaction_type='income',
                student__grade=grade['grade'],
                date__gte=month_start
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0')
            
            grades_stats.append({
                'grade': grade['grade'],
                'students': grade['count'],
                'income': income
            })
        
        data = {
            'active_students': active_students,
            'total_groups': total_groups,
            'total_teachers': total_teachers,
            'pending_tasks': pending_tasks,
            'monthly_income': monthly_income,
            'monthly_expenses': monthly_expenses,
            'monthly_profit': monthly_income - monthly_expenses,
            'lessons_this_week': lessons_this_week,
            'groups_stats': groups_stats,
            'subjects_stats': subjects_stats,
            'grades_stats': grades_stats
        }
        
        serializer = DashboardSerializer(data)
        return Response(serializer.data)