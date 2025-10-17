from django.urls import path
from . import views

urlpatterns = [
    path('', views.dashboard, name='dashboard'),
    path('list/', views.student_list, name='student_list'),
    path('<int:pk>/', views.student_detail, name='student_detail'),
]
