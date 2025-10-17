from django.shortcuts import render, get_object_or_404
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
