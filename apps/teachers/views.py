from django.shortcuts import render

def teacher_list(request):
    return render(request, 'teachers/teacher_list.html')
