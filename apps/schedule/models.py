from django.db import models

class Room(models.Model):
    name = models.CharField('Название', max_length=100)

    class Meta:
        verbose_name = 'Кабинет'
        verbose_name_plural = 'Кабинеты'

    def __str__(self):
        return self.name
