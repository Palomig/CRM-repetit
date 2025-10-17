from django.db import models

class Task(models.Model):
    title = models.CharField('Заголовок', max_length=200)
    description = models.TextField('Описание')

    class Meta:
        verbose_name = 'Задача'
        verbose_name_plural = 'Задачи'

    def __str__(self):
        return self.title
