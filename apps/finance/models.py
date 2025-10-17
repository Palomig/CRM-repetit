from django.db import models

class Payment(models.Model):
    amount = models.DecimalField('Сумма', max_digits=10, decimal_places=2)
    payment_date = models.DateField('Дата')

    class Meta:
        verbose_name = 'Платеж'
        verbose_name_plural = 'Платежи'
