from django.contrib import admin
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
