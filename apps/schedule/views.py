from django.shortcuts import render

def schedule_view(request):
    return render(request, 'schedule/schedule.html')
