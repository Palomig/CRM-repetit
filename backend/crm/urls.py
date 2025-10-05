from django.urls import path, include
from rest_framework.routers import DefaultRouter
from .views import (
    ParentViewSet, TeacherViewSet, RoomViewSet, GroupViewSet,
    StudentViewSet, LessonViewSet, FinanceViewSet, TaskViewSet,
    DashboardViewSet
)

router = DefaultRouter()
router.register(r'parents', ParentViewSet, basename='parent')
router.register(r'teachers', TeacherViewSet, basename='teacher')
router.register(r'rooms', RoomViewSet, basename='room')
router.register(r'groups', GroupViewSet, basename='group')
router.register(r'students', StudentViewSet, basename='student')
router.register(r'lessons', LessonViewSet, basename='lesson')
router.register(r'finance', FinanceViewSet, basename='finance')
router.register(r'tasks', TaskViewSet, basename='task')
router.register(r'dashboard', DashboardViewSet, basename='dashboard')

urlpatterns = [
    path('', include(router.urls)),
]