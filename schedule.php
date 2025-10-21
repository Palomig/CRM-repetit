<?php
require_once 'includes/header.php';

$teachers = db()->fetchAll("SELECT id, name FROM teachers WHERE status = 'active' ORDER BY name");
$rooms = db()->fetchAll("SELECT id, name FROM rooms WHERE status = 'active' ORDER BY name");
$students = db()->fetchAll("SELECT id, name FROM students WHERE status = 'active' ORDER BY name");
$groups = db()->fetchAll("SELECT id, name FROM `groups` WHERE status = 'active' ORDER BY name");
?>

<div x-data="scheduleApp()">
    <!-- Заголовок и фильтры -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Расписание</h1>
                <p class="text-gray-600 mt-1">Календарь занятий</p>
            </div>
            <button @click="showModal = true; modalMode = 'create'; resetForm()" class="mt-4 sm:mt-0 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Добавить урок
            </button>
        </div>

        <!-- Фильтр по преподавателю -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                <label class="text-sm font-medium text-gray-700">Фильтр по преподавателю:</label>
                <select x-model="filterTeacherId" @change="calendar.refetchEvents()" class="flex-1 sm:flex-initial px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Все преподаватели</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= $teacher['id'] ?>"><?= e($teacher['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button @click="filterTeacherId = ''; calendar.refetchEvents()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-redo mr-2"></i>Сбросить
                </button>
            </div>
        </div>
    </div>

    <!-- Календарь -->
    <div class="bg-white rounded-lg shadow p-6">
        <div id="calendar"></div>
    </div>

    <!-- Модальное окно урока -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900" x-text="modalMode === 'create' ? 'Добавить урок' : modalMode === 'edit' ? 'Редактировать урок' : 'Информация об уроке'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveLesson" x-show="modalMode !== 'view'">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Тип урока</label>
                            <select x-model="form.lessonType" @change="handleLessonTypeChange()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="individual">Индивидуальный</option>
                                <option value="group">Групповой</option>
                            </select>
                        </div>

                        <div x-show="form.lessonType === 'individual'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ученик *</label>
                            <select x-model="form.student_id" :required="form.lessonType === 'individual'" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите ученика</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>"><?= e($student['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div x-show="form.lessonType === 'group'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Группа *</label>
                            <select x-model="form.group_id" :required="form.lessonType === 'group'" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите группу</option>
                                <?php foreach ($groups as $group): ?>
                                    <option value="<?= $group['id'] ?>"><?= e($group['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Преподаватель *</label>
                            <select x-model="form.teacher_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите преподавателя</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= e($teacher['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Кабинет *</label>
                            <select x-model="form.room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите кабинет</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?= $room['id'] ?>"><?= e($room['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Дата *</label>
                            <input type="date" x-model="form.lesson_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Время *</label>
                            <input type="time" x-model="form.lesson_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Длительность (мин)</label>
                            <input type="number" x-model="form.duration" min="15" step="15" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                            <select x-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="scheduled">Запланирован</option>
                                <option value="completed">Завершен</option>
                                <option value="cancelled">Отменен</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Примечания</label>
                            <textarea x-model="form.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <span x-text="modalMode === 'create' ? 'Добавить' : 'Сохранить'"></span>
                        </button>
                    </div>
                </form>

                <!-- Просмотр информации -->
                <div x-show="modalMode === 'view'" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Преподаватель</p>
                            <p class="font-medium" x-text="viewData.teacher_name"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Кабинет</p>
                            <p class="font-medium" x-text="viewData.room_name"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Длительность</p>
                            <p class="font-medium" x-text="viewData.duration + ' мин'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Статус</p>
                            <p class="font-medium" x-text="getStatusText(viewData.status)"></p>
                        </div>
                    </div>
                    <div x-show="viewData.notes" class="border-t pt-4">
                        <p class="text-sm text-gray-600 mb-2">Примечания</p>
                        <p x-text="viewData.notes"></p>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="editLesson(viewData)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-edit mr-2"></i>Редактировать
                        </button>
                        <button @click="deleteLessonConfirm(viewData.id)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function scheduleApp() {
    return {
        calendar: null,
        showModal: false,
        modalMode: 'create',
        filterTeacherId: '',
        form: {
            id: null,
            lessonType: 'individual',
            student_id: '',
            group_id: '',
            teacher_id: '',
            room_id: '',
            lesson_date: '',
            lesson_time: '',
            duration: 60,
            status: 'scheduled',
            notes: ''
        },
        viewData: {},

        init() {
            this.initCalendar();
        },

        initCalendar() {
            const calendarEl = document.getElementById('calendar');
            this.calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'ru',
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                slotMinTime: '08:00:00',
                slotMaxTime: '21:00:00',
                allDaySlot: false,
                height: 'auto',
                events: (info, successCallback, failureCallback) => {
                    this.loadEvents(info, successCallback, failureCallback);
                },
                eventClick: (info) => {
                    this.viewLesson(info.event);
                },
                editable: true,
                eventDrop: (info) => {
                    this.handleEventDrop(info);
                },
                eventResize: (info) => {
                    this.handleEventResize(info);
                }
            });
            this.calendar.render();
        },

        async loadEvents(info, successCallback, failureCallback) {
            try {
                const params = new URLSearchParams({
                    start: info.startStr.split('T')[0],
                    end: info.endStr.split('T')[0]
                });

                if (this.filterTeacherId) {
                    params.append('teacher_id', this.filterTeacherId);
                }

                const response = await fetch(`/api/schedule.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    successCallback(data.data);
                } else {
                    failureCallback();
                }
            } catch (error) {
                console.error('Error loading events:', error);
                failureCallback();
            }
        },

        handleLessonTypeChange() {
            if (this.form.lessonType === 'individual') {
                this.form.group_id = '';
            } else {
                this.form.student_id = '';
            }
        },

        resetForm() {
            this.form = {
                id: null,
                lessonType: 'individual',
                student_id: '',
                group_id: '',
                teacher_id: '',
                room_id: '',
                lesson_date: new Date().toISOString().split('T')[0],
                lesson_time: '10:00',
                duration: 60,
                status: 'scheduled',
                notes: ''
            };
        },

        viewLesson(event) {
            this.viewData = {
                id: event.id,
                title: event.title,
                teacher_name: event.extendedProps.teacher_name,
                room_name: event.extendedProps.room_name,
                status: event.extendedProps.status,
                duration: event.extendedProps.duration,
                notes: event.extendedProps.notes,
                student_id: event.extendedProps.student_id,
                group_id: event.extendedProps.group_id,
                teacher_id: event.extendedProps.teacher_id,
                room_id: event.extendedProps.room_id
            };
            this.modalMode = 'view';
            this.showModal = true;
        },

        editLesson(data) {
            const startDate = this.calendar.getEventById(data.id);
            this.form = {
                id: data.id,
                lessonType: data.student_id ? 'individual' : 'group',
                student_id: data.student_id || '',
                group_id: data.group_id || '',
                teacher_id: data.teacher_id,
                room_id: data.room_id,
                lesson_date: startDate ? startDate.start.toISOString().split('T')[0] : '',
                lesson_time: startDate ? startDate.start.toTimeString().slice(0, 5) : '',
                duration: data.duration,
                status: data.status,
                notes: data.notes || ''
            };
            this.modalMode = 'edit';
        },

        async saveLesson() {
            try {
                const method = this.modalMode === 'create' ? 'POST' : 'PUT';
                const response = await fetch('/api/schedule.php', {
                    method: method,
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.showModal = false;
                    this.calendar.refetchEvents();
                } else {
                    showNotification(data.error || 'Ошибка сохранения', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка сохранения', 'error');
            }
        },

        async deleteLessonConfirm(id) {
            if (!confirmAction('Вы уверены, что хотите удалить урок?')) return;

            try {
                const response = await fetch('/api/schedule.php', {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id})
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.showModal = false;
                    this.calendar.refetchEvents();
                } else {
                    showNotification(data.error || 'Ошибка удаления', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка удаления', 'error');
            }
        },

        async handleEventDrop(info) {
            const event = info.event;
            const newDate = event.start.toISOString().split('T')[0];
            const newTime = event.start.toTimeString().slice(0, 5);

            try {
                const response = await fetch('/api/schedule.php', {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        id: event.id,
                        lesson_date: newDate,
                        lesson_time: newTime
                    })
                });

                const data = await response.json();
                
                if (!data.success) {
                    info.revert();
                    showNotification('Ошибка перемещения', 'error');
                } else {
                    showNotification('Урок успешно перемещен', 'success');
                }
            } catch (error) {
                info.revert();
                showNotification('Ошибка перемещения', 'error');
            }
        },

        async handleEventResize(info) {
            const event = info.event;
            const duration = Math.round((event.end - event.start) / 60000);

            try {
                const response = await fetch('/api/schedule.php', {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        id: event.id,
                        duration: duration
                    })
                });

                const data = await response.json();
                
                if (!data.success) {
                    info.revert();
                    showNotification('Ошибка изменения длительности', 'error');
                } else {
                    showNotification('Длительность успешно изменена', 'success');
                }
            } catch (error) {
                info.revert();
                showNotification('Ошибка изменения длительности', 'error');
            }
        },

        getStatusText(status) {
            const statuses = {
                'scheduled': 'Запланирован',
                'completed': 'Завершен',
                'cancelled': 'Отменен'
            };
            return statuses[status] || status;
        }
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>