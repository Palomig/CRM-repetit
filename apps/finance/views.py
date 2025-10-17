from django.shortcuts import render

def finance_dashboard(request):
    return render(request, 'finance/finance_dashboard.html')
