from django.shortcuts import render

def task_list(request):
    return render(request, 'tasks/task_list.html')
