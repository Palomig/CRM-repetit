from django.db import models

class Teacher(models.Model):
    name = models.CharField('Имя', max_length=200)
    subjects = models.CharField('Предметы', max_length=300)

    class Meta:
        verbose_name = 'Преподаватель'
        verbose_name_plural = 'Преподаватели'

    def __str__(self):
        return self.name
