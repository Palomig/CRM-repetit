from django.db import models

class Parent(models.Model):
    name = models.CharField('Имя родителя', max_length=200)
    phone = models.CharField('Телефон', max_length=17)
    whatsapp = models.URLField('WhatsApp', blank=True)
    telegram = models.URLField('Telegram', blank=True)

    class Meta:
        verbose_name = 'Родитель'
        verbose_name_plural = 'Родители'

    def __str__(self):
        return self.name

class Student(models.Model):
    name = models.CharField('Имя ученика', max_length=200)
    grade = models.IntegerField('Класс')
    parent = models.ForeignKey(Parent, on_delete=models.PROTECT)
    subject = models.CharField('Предмет', max_length=100)
    learning_type = models.CharField('Тип обучения', max_length=20)
    status = models.CharField('Статус', max_length=20, default='active')

    class Meta:
        verbose_name = 'Ученик'
        verbose_name_plural = 'Ученики'

    def __str__(self):
        return self.name
