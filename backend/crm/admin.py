from django.contrib import admin
from .models import Parent, Teacher, Room, Group, Student, Lesson, Finance, Task


@admin.register(Parent)
class ParentAdmin(admin.ModelAdmin):
    list_display = ['name', 'phone', 'whatsapp', 'telegram', 'created_at']
    search_fields = ['name', 'phone']
    list_filter = ['created_at']


@admin.register(Teacher)
class TeacherAdmin(admin.ModelAdmin):
    list_display = ['name', 'phone', 'salary_per_lesson', 'get_subjects', 'created_at']
    search_fields = ['name', 'phone']
    list_filter = ['created_at']
    
    def get_subjects(self, obj):
        return ', '.join(obj.subjects) if obj.subjects else '-'
    get_subjects.short_description = 'Предметы'


@admin.register(Room)
class RoomAdmin(admin.ModelAdmin):
    list_display = ['name', 'capacity', 'created_at']
    search_fields = ['name']


@admin.register(Group)
class GroupAdmin(admin.ModelAdmin):
    list_display = ['name', 'subject', 'teacher', 'room', 'students_count', 'max_students', 'is_full']
    search_fields = ['name', 'subject']
    list_filter = ['subject', 'teacher', 'room']
    filter_horizontal = []
    
    def students_count(self, obj):
        return obj.students_count
    students_count.short_description = 'Кол-во учеников'


@admin.register(Student)
class StudentAdmin(admin.ModelAdmin):
    list_display = ['name', 'grade', 'subject', 'learning_type', 'teacher', 'group', 'status', 'price_per_lesson', 'last_lesson_date']
    search_fields = ['name', 'parent__name', 'parent__phone']
    list_filter = ['status', 'learning_type', 'grade', 'subject', 'teacher']
    readonly_fields = ['created_at', 'updated_at']
    
    fieldsets = (
        ('Основная информация', {
            'fields': ('name', 'grade', 'subject', 'status')
        }),
        ('Родитель', {
            'fields': ('parent',)
        }),
        ('Обучение', {
            'fields': ('learning_type', 'teacher', 'group', 'price_per_lesson', 'last_lesson_date')
        }),
        ('Дополнительно', {
            'fields': ('notes', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )


@admin.register(Lesson)
class LessonAdmin(admin.ModelAdmin):
    list_display = ['get_lesson_name', 'teacher', 'room', 'datetime', 'duration', 'lesson_type', 'status']
    search_fields = ['student__name', 'group__name', 'teacher__name']
    list_filter = ['status', 'lesson_type', 'teacher', 'room', 'datetime']
    date_hierarchy = 'datetime'
    
    def get_lesson_name(self, obj):
        if obj.student:
            return f"{obj.student.name}"
        elif obj.group:
            return f"{obj.group.name}"
        return '-'
    get_lesson_name.short_description = 'Ученик/Группа'
    
    fieldsets = (
        ('Участники', {
            'fields': ('student', 'group', 'teacher')
        }),
        ('Время и место', {
            'fields': ('datetime', 'duration', 'room')
        }),
        ('Детали', {
            'fields': ('lesson_type', 'status', 'notes')
        }),
    )


@admin.register(Finance)
class FinanceAdmin(admin.ModelAdmin):
    list_display = ['date', 'transaction_type', 'amount', 'get_related_person', 'description']
    search_fields = ['student__name', 'teacher__name', 'description']
    list_filter = ['transaction_type', 'date']
    date_hierarchy = 'date'
    
    def get_related_person(self, obj):
        if obj.student:
            return f"Ученик: {obj.student.name}"
        elif obj.teacher:
            return f"Преподаватель: {obj.teacher.name}"
        return '-'
    get_related_person.short_description = 'Связано с'


@admin.register(Task)
class TaskAdmin(admin.ModelAdmin):
    list_display = ['title', 'due_date', 'status', 'priority', 'get_related_person']
    search_fields = ['title', 'description', 'student__name', 'teacher__name']
    list_filter = ['status', 'priority', 'due_date']
    date_hierarchy = 'due_date'
    
    def get_related_person(self, obj):
        if obj.student:
            return f"Ученик: {obj.student.name}"
        elif obj.teacher:
            return f"Преподаватель: {obj.teacher.name}"
        return '-'
    get_related_person.short_description = 'Связано с'
    
    fieldsets = (
        ('Основная информация', {
            'fields': ('title', 'description', 'status', 'priority')
        }),
        ('Связи', {
            'fields': ('student', 'teacher', 'due_date')
        }),
    )


# Кастомизация заголовка Admin панели
admin.site.site_header = 'CRM Репетиторский Центр'
admin.site.site_title = 'CRM Admin'
admin.site.index_title = 'Управление системой'