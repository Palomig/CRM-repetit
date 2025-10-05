from rest_framework import serializers
from .models import Parent, Teacher, Room, Group, Student, Lesson, Finance, Task


class ParentSerializer(serializers.ModelSerializer):
    children_count = serializers.SerializerMethodField()

    class Meta:
        model = Parent
        fields = '__all__'

    def get_children_count(self, obj):
        return obj.children.filter(status='active').count()


class TeacherSerializer(serializers.ModelSerializer):
    students_count = serializers.SerializerMethodField()
    groups_count = serializers.SerializerMethodField()

    class Meta:
        model = Teacher
        fields = '__all__'

    def get_students_count(self, obj):
        return obj.students.filter(status='active').count()

    def get_groups_count(self, obj):
        return obj.groups.count()


class RoomSerializer(serializers.ModelSerializer):
    class Meta:
        model = Room
        fields = '__all__'


class GroupSerializer(serializers.ModelSerializer):
    teacher_name = serializers.CharField(source='teacher.name', read_only=True)
    room_name = serializers.CharField(source='room.name', read_only=True)
    students_count = serializers.IntegerField(read_only=True)
    is_full = serializers.BooleanField(read_only=True)

    class Meta:
        model = Group
        fields = '__all__'


class StudentListSerializer(serializers.ModelSerializer):
    parent_name = serializers.CharField(source='parent.name', read_only=True)
    parent_phone = serializers.CharField(source='parent.phone', read_only=True)
    teacher_name = serializers.CharField(source='teacher.name', read_only=True)
    group_name = serializers.CharField(source='group.name', read_only=True)

    class Meta:
        model = Student
        fields = [
            'id', 'name', 'grade', 'subject', 'learning_type', 'status',
            'last_lesson_date', 'price_per_lesson', 'parent_name', 
            'parent_phone', 'teacher_name', 'group_name', 'created_at'
        ]


class StudentDetailSerializer(serializers.ModelSerializer):
    parent = ParentSerializer(read_only=True)
    teacher = TeacherSerializer(read_only=True)
    group = GroupSerializer(read_only=True)
    lessons_count = serializers.SerializerMethodField()
    total_paid = serializers.SerializerMethodField()

    class Meta:
        model = Student
        fields = '__all__'

    def get_lessons_count(self, obj):
        return obj.lessons.filter(status='completed').count()

    def get_total_paid(self, obj):
        payments = obj.payments.filter(transaction_type='income')
        return sum(p.amount for p in payments)


class StudentCreateUpdateSerializer(serializers.ModelSerializer):
    class Meta:
        model = Student
        fields = '__all__'

    def validate(self, data):
        if data.get('learning_type') == 'group' and not data.get('group'):
            raise serializers.ValidationError(
                "Для группового обучения необходимо указать группу"
            )
        
        if data.get('group'):
            group = data['group']
            if group.is_full and not self.instance:
                raise serializers.ValidationError(
                    f"Группа {group.name} заполнена (максимум {group.max_students} учеников)"
                )
        
        return data


class LessonSerializer(serializers.ModelSerializer):
    student_name = serializers.CharField(source='student.name', read_only=True)
    group_name = serializers.CharField(source='group.name', read_only=True)
    teacher_name = serializers.CharField(source='teacher.name', read_only=True)
    room_name = serializers.CharField(source='room.name', read_only=True)

    class Meta:
        model = Lesson
        fields = '__all__'

    def validate(self, data):
        # Проверка что указан либо студент либо группа
        if not data.get('student') and not data.get('group'):
            raise serializers.ValidationError(
                "Необходимо указать ученика или группу"
            )
        
        if data.get('student') and data.get('group'):
            raise serializers.ValidationError(
                "Нельзя указать одновременно ученика и группу"
            )
        
        # Проверка на пересечение уроков в одном кабинете
        datetime = data.get('datetime')
        room = data.get('room')
        duration = data.get('duration', 60)
        
        if datetime and room:
            from datetime import timedelta
            end_time = datetime + timedelta(minutes=duration)
            
            overlapping = Lesson.objects.filter(
                room=room,
                datetime__lt=end_time,
                datetime__gte=datetime - timedelta(minutes=120)
            )
            
            if self.instance:
                overlapping = overlapping.exclude(id=self.instance.id)
            
            if overlapping.exists():
                raise serializers.ValidationError(
                    f"В кабинете {room.name} уже запланирован урок в это время"
                )
        
        return data


class FinanceSerializer(serializers.ModelSerializer):
    student_name = serializers.CharField(source='student.name', read_only=True)
    teacher_name = serializers.CharField(source='teacher.name', read_only=True)

    class Meta:
        model = Finance
        fields = '__all__'


class TaskSerializer(serializers.ModelSerializer):
    student_name = serializers.CharField(source='student.name', read_only=True)
    teacher_name = serializers.CharField(source='teacher.name', read_only=True)

    class Meta:
        model = Task
        fields = '__all__'


class DashboardSerializer(serializers.Serializer):
    """Сериализатор для дашборда с общей статистикой"""
    active_students = serializers.IntegerField()
    total_groups = serializers.IntegerField()
    total_teachers = serializers.IntegerField()
    pending_tasks = serializers.IntegerField()
    monthly_income = serializers.DecimalField(max_digits=10, decimal_places=2)
    monthly_expenses = serializers.DecimalField(max_digits=10, decimal_places=2)
    monthly_profit = serializers.DecimalField(max_digits=10, decimal_places=2)
    lessons_this_week = serializers.IntegerField()
    
    # Статистика по группам
    groups_stats = serializers.ListField()
    
    # Финансовая статистика по предметам
    subjects_stats = serializers.ListField()
    
    # Финансовая статистика по классам
    grades_stats = serializers.ListField()