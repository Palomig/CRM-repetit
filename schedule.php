<?php
require_once 'includes/header.php';

$teachers = db()->fetchAll("SELECT id, name FROM teachers WHERE status = 'active' ORDER BY name");
$students = db()->fetchAll("SELECT id, name FROM students WHERE status = 'active' ORDER BY name");
$groups = db()->fetchAll("SELECT id, name FROM `groups` WHERE status = 'active' ORDER BY name");

// Цвета для преподавателей
$teacherColors = [
    '#3B82F6', // blue
    '#8B5CF6', // purple
    '#10B981', // green
    '#F59E0B', // amber
    '#EF4444', // red
    '#EC4899', // pink
    '#06B6D4', // cyan
    '#6366F1', // indigo
];
?>

<style>
.schedule-container {
    max-width: 100%;
    width: 100%;
}

.schedule-grid {
    display: grid;
    grid-template-columns: 80px repeat(7, 1fr);
    gap: 1px;
    background: #374151;
    overflow-x: auto;
}

.time-slot {
    background: #1f2937;
    padding: 0.75rem 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 600;
    color: #9ca3af;
    border-right: 2px solid #374151;
}

.day-header {
    background: #1f2937;
    padding: 1rem;
    text-align: center;
    font-weight: bold;
    color: white;
    border-bottom: 2px solid #374151;
}

.lesson-cell {
    background: #111827;
    min-height: 80px;
    padding: 0.5rem;
    position: relative;
    cursor: pointer;
    transition: all 0.2s;
}

.lesson-cell.empty {
    background: #064e3b;
    opacity: 0.3;
}

.lesson-cell.empty:hover {
    opacity: 0.6;
}

.lesson-cell.has-lesson {
    opacity: 1;
}

.lesson-cell:hover {
    transform: scale(1.02);
    z-index: 10;
}

.lesson-card {
    border-radius: 0.375rem;
    padding: 0.5rem;
    height: 100%;
    border: 2px solid;
}

.lesson-title {
    font-weight: 600;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    color: white;
}

.lesson-students {
    font-size: 0.75rem;
    color: #e5e7eb;
    line-height: 1.4;
}

.day-selector {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.day-button {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    border: 2px solid #4b5563;
    background: #374151;
    color: #9ca3af;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
}

.day-button:hover {
    border-color: #6b7280;
    background: #4b5563;
}

.day-button.selected {
    border-color: #3b82f6;
    background: #1e40af;
    color: white;
}

@media (max-width: 1536px) {
    .lesson-title {
        font-size: 0.75rem;
    }
    .lesson-students {
        font-size: 0.7rem;
    }
}
</style>

<div x-data="scheduleApp()" class="schedule-container">
    <!-- Заголовок и элементы управления -->
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Расписание</h1>
                <p class="text-gray-400 mt-1">Недельное расписание занятий</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <!-- Навигация по неделям -->
                <div class="flex items-center gap-2">
                    <button @click="changeWeek(-1)" class="px-3 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="text-white font-medium px-4" x-text="currentWeekLabel"></span>
                    <button @click="changeWeek(1)" class="px-3 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button @click="goToCurrentWeek()" class="px-3 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">
                        <i class="fas fa-calendar-day mr-1"></i>Сегодня
                    </button>
                </div>

                <button @click="showModal = true; modalMode = 'create'; resetForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Добавить урок
                </button>
            </div>
        </div>

        <!-- Легенда преподавателей -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-4 mb-4">
            <div class="flex flex-wrap gap-4 items-center">
                <span class="text-sm font-medium text-gray-200">Преподаватели:</span>
                <template x-for="(teacher, index) in teachers" :key="teacher.id">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded" :style="`background-color: ${getTeacherColor(index)}`"></div>
                        <span class="text-sm text-gray-300" x-text="teacher.name"></span>
                    </div>
                </template>
                <div class="ml-auto px-3 py-1 bg-blue-600 rounded text-sm">
                    <span class="text-white font-medium">Уроков загружено: </span>
                    <span class="text-white font-bold" x-text="lessons.length"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Расписание -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-4 overflow-x-auto">
        <div class="schedule-grid" style="min-width: 1200px;">
            <!-- Заголовок: пустая ячейка для времени -->
            <div class="day-header">Время</div>

            <!-- Заголовки дней недели -->
            <template x-for="day in weekDays" :key="day.date">
                <div class="day-header">
                    <div x-text="day.name" class="text-lg"></div>
                    <div x-text="day.dateFormatted" class="text-sm text-gray-400 mt-1"></div>
                </div>
            </template>

            <!-- Все ячейки таблицы (время + уроки) в правильном порядке для grid -->
            <template x-for="cell in getScheduleCells()" :key="cell.type + '-' + cell.time + '-' + (cell.date || '')">
                <div
                    :class="{
                        'time-slot': cell.type === 'time',
                        'lesson-cell': cell.type === 'lesson',
                        'has-lesson': cell.type === 'lesson' && getLesson(cell.date, cell.time),
                        'empty': cell.type === 'lesson' && !getLesson(cell.date, cell.time)
                    }"
                    @click="cell.type === 'lesson' && openAddLessonModal(cell.date, cell.time)"
                >
                    <!-- Содержимое ячейки времени -->
                    <template x-if="cell.type === 'time'">
                        <span x-text="cell.time"></span>
                    </template>

                    <!-- Содержимое ячейки урока -->
                    <template x-if="cell.type === 'lesson'">
                        <div
                            x-show="getLesson(cell.date, cell.time)"
                            class="lesson-card"
                            :style="getLesson(cell.date, cell.time) ? `border-color: ${getLesson(cell.date, cell.time).color}; background-color: ${getLesson(cell.date, cell.time).color}20` : ''"
                            @click.stop="getLesson(cell.date, cell.time) && viewLesson(getLesson(cell.date, cell.time))"
                        >
                            <div class="lesson-title" x-text="getLesson(cell.date, cell.time)?.title"></div>
                            <div class="lesson-students" x-html="getLesson(cell.date, cell.time) ? formatStudents(getLesson(cell.date, cell.time).students) : ''"></div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Модальное окно урока -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-w-2xl w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white" x-text="modalMode === 'create' ? 'Добавить урок' : modalMode === 'edit' ? 'Редактировать урок' : 'Информация об уроке'"></h3>
                    <button @click="showModal = false" class="text-gray-500 hover:text-gray-400">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveLesson" x-show="modalMode !== 'view'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Тип урока</label>
                            <select x-model="form.lessonType" @change="handleLessonTypeChange()" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="individual">Индивидуальный</option>
                                <option value="group">Групповой</option>
                            </select>
                        </div>

                        <div x-show="form.lessonType === 'individual'">
                            <label class="block text-sm font-medium text-gray-200 mb-2">Ученик *</label>
                            <select x-model="form.student_id" :required="form.lessonType === 'individual'" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите ученика</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>"><?= e($student['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div x-show="form.lessonType === 'group'">
                            <label class="block text-sm font-medium text-gray-200 mb-2">Группа *</label>
                            <select x-model="form.group_id" :required="form.lessonType === 'group'" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите группу</option>
                                <?php foreach ($groups as $group): ?>
                                    <option value="<?= $group['id'] ?>"><?= e($group['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Преподаватель *</label>
                            <select x-model="form.teacher_id" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите преподавателя</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= e($teacher['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Дни недели *</label>
                            <div class="day-selector">
                                <button type="button" @click="toggleDay(1)" :class="form.selectedDays.includes(1) ? 'selected' : ''" class="day-button">Пн</button>
                                <button type="button" @click="toggleDay(2)" :class="form.selectedDays.includes(2) ? 'selected' : ''" class="day-button">Вт</button>
                                <button type="button" @click="toggleDay(3)" :class="form.selectedDays.includes(3) ? 'selected' : ''" class="day-button">Ср</button>
                                <button type="button" @click="toggleDay(4)" :class="form.selectedDays.includes(4) ? 'selected' : ''" class="day-button">Чт</button>
                                <button type="button" @click="toggleDay(5)" :class="form.selectedDays.includes(5) ? 'selected' : ''" class="day-button">Пт</button>
                                <button type="button" @click="toggleDay(6)" :class="form.selectedDays.includes(6) ? 'selected' : ''" class="day-button">Сб</button>
                                <button type="button" @click="toggleDay(0)" :class="form.selectedDays.includes(0) ? 'selected' : ''" class="day-button">Вс</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Время *</label>
                            <input type="time" x-model="form.lesson_time" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Длительность (мин)</label>
                            <input type="number" x-model="form.duration" min="15" step="15" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Статус</label>
                            <select x-model="form.status" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="scheduled">Запланирован</option>
                                <option value="completed">Завершен</option>
                                <option value="cancelled">Отменен</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Примечания</label>
                            <textarea x-model="form.notes" rows="2" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-200 hover:bg-gray-600">
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
                            <p class="text-sm text-gray-400">Преподаватель</p>
                            <p class="font-medium text-white" x-text="viewData.teacher_name"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Длительность</p>
                            <p class="font-medium text-white" x-text="viewData.duration + ' мин'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Статус</p>
                            <p class="font-medium text-white" x-text="getStatusText(viewData.status)"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Дата</p>
                            <p class="font-medium text-white" x-text="viewData.lesson_date + ' ' + viewData.lesson_time"></p>
                        </div>
                    </div>
                    <div x-show="viewData.notes" class="border-t border-gray-700 pt-4">
                        <p class="text-sm text-gray-400 mb-2">Примечания</p>
                        <p class="text-white" x-text="viewData.notes"></p>
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
        showModal: false,
        modalMode: 'create',
        currentWeekStart: null,
        currentWeekLabel: '',
        weekDays: [],
        lessons: [],
        teachers: <?= json_encode($teachers) ?>,
        teacherColors: <?= json_encode($teacherColors) ?>,
        form: {
            id: null,
            lessonType: 'individual',
            student_id: '',
            group_id: '',
            teacher_id: '',
            selectedDays: [],
            lesson_time: '',
            duration: 60,
            status: 'scheduled',
            notes: ''
        },
        viewData: {},

        // Временные слоты
        weekdaySlots: ['16:00', '17:00', '18:00', '19:00', '20:00'],
        weekendSlots: ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'],

        init() {
            this.goToCurrentWeek();
            this.loadLessons();
        },

        getTimeSlotsForWeek() {
            // Возвращаем все уникальные временные слоты
            const allSlots = new Set([...this.weekdaySlots, ...this.weekendSlots]);
            return Array.from(allSlots).sort();
        },

        // Генерируем плоский массив всех ячеек для grid в правильном порядке
        getScheduleCells() {
            const cells = [];
            const timeSlots = this.getTimeSlotsForWeek();

            timeSlots.forEach(timeSlot => {
                // Сначала добавляем ячейку времени
                cells.push({
                    type: 'time',
                    time: timeSlot
                });

                // Затем добавляем ячейки уроков для каждого дня
                this.weekDays.forEach(day => {
                    cells.push({
                        type: 'lesson',
                        date: day.date,
                        time: timeSlot
                    });
                });
            });

            return cells;
        },

        goToCurrentWeek() {
            const today = new Date();
            const dayOfWeek = today.getDay();
            const diff = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
            this.currentWeekStart = new Date(today);
            this.currentWeekStart.setDate(today.getDate() + diff);
            this.updateWeekDays();
        },

        changeWeek(direction) {
            this.currentWeekStart.setDate(this.currentWeekStart.getDate() + (direction * 7));
            this.updateWeekDays();
            this.loadLessons();
        },

        updateWeekDays() {
            this.weekDays = [];
            const dayNames = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];

            for (let i = 0; i < 7; i++) {
                const date = new Date(this.currentWeekStart);
                date.setDate(this.currentWeekStart.getDate() + i);

                this.weekDays.push({
                    name: dayNames[date.getDay()],
                    date: date.toISOString().split('T')[0],
                    dateFormatted: `${date.getDate()}.${date.getMonth() + 1}`,
                    fullDate: date
                });
            }

            const startDate = this.weekDays[0].fullDate;
            const endDate = this.weekDays[6].fullDate;
            this.currentWeekLabel = `${startDate.getDate()}.${startDate.getMonth() + 1} - ${endDate.getDate()}.${endDate.getMonth() + 1}.${endDate.getFullYear()}`;
        },

        async loadLessons() {
            try {
                const startDate = this.weekDays[0].date;
                const endDate = this.weekDays[6].date;
                const url = `/api/schedule.php?start=${startDate}&end=${endDate}`;

                console.log('Loading lessons from:', url);
                console.log('Date range:', startDate, 'to', endDate);

                const response = await fetch(url);
                console.log('Response status:', response.status);

                if (!response.ok) {
                    console.error('HTTP Error:', response.status, response.statusText);
                    return;
                }

                const data = await response.json();
                console.log('API Response:', data);

                if (data.success) {
                    console.log('Raw lessons from API:', data.data.length, 'lessons');

                    // Добавляем цвет для каждого урока на основе преподавателя
                    this.lessons = data.data.map(lesson => {
                        const teacherIndex = this.teachers.findIndex(t => t.id == lesson.extendedProps.teacher_id);
                        lesson.color = this.getTeacherColor(teacherIndex);
                        lesson.students = lesson.extendedProps.students || [];
                        console.log('Lesson:', lesson.id, lesson.start, lesson.title);
                        return lesson;
                    });

                    console.log('Total lessons loaded:', this.lessons.length);
                    console.log('Lessons array:', this.lessons);

                    // Force Alpine to re-evaluate
                    console.log('Forcing Alpine.js reactivity update...');
                    this.$nextTick(() => {
                        console.log('After nextTick - lessons count:', this.lessons.length);
                    });
                } else {
                    console.error('API returned success:false', data);
                }
            } catch (error) {
                console.error('Error loading lessons:', error);
            }
        },

        getLesson(date, time) {
            // Add counter to track function calls
            if (!this._getLessonCallCount) this._getLessonCallCount = 0;
            this._getLessonCallCount++;

            const lesson = this.lessons.find(l => {
                const lessonDate = l.start.split(' ')[0];
                const lessonTime = l.start.split(' ')[1].substring(0, 5);

                // Log all comparisons for debugging
                if (this._getLessonCallCount <= 50) { // Only log first 50 calls to avoid spam
                    console.log(`Call #${this._getLessonCallCount}: Comparing lesson ${l.id} (${lessonDate} ${lessonTime}) with cell (${date} ${time})`);
                }

                return lessonDate === date && lessonTime === time;
            });

            // Debug specific cell
            if (date === '2025-10-21' && time === '16:00') {
                console.log('=== SPECIAL DEBUG for 2025-10-21 16:00 ===');
                console.log('Total lessons to search:', this.lessons.length);
                console.log('Found lesson:', lesson);
                if (lesson) {
                    console.log('Lesson details:', {
                        id: lesson.id,
                        start: lesson.start,
                        title: lesson.title,
                        color: lesson.color,
                        students: lesson.students
                    });
                }
            }

            return lesson;
        },

        formatStudents(students) {
            if (!students || students.length === 0) return '';
            return students.map(s => `• ${s}`).join('<br>');
        },

        getTeacherColor(index) {
            if (index < 0) return this.teacherColors[0];
            return this.teacherColors[index % this.teacherColors.length];
        },

        toggleDay(dayNumber) {
            const index = this.form.selectedDays.indexOf(dayNumber);
            if (index > -1) {
                this.form.selectedDays.splice(index, 1);
            } else {
                this.form.selectedDays.push(dayNumber);
            }
        },

        openAddLessonModal(date, time) {
            this.resetForm();
            const dayOfWeek = new Date(date).getDay();
            this.form.selectedDays = [dayOfWeek];
            this.form.lesson_time = time;
            this.modalMode = 'create';
            this.showModal = true;
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
                selectedDays: [],
                lesson_time: '10:00',
                duration: 60,
                status: 'scheduled',
                notes: ''
            };
        },

        viewLesson(lesson) {
            this.viewData = {
                id: lesson.id,
                title: lesson.title,
                teacher_name: lesson.extendedProps.teacher_name,
                status: lesson.extendedProps.status,
                duration: lesson.extendedProps.duration,
                notes: lesson.extendedProps.notes,
                student_id: lesson.extendedProps.student_id,
                group_id: lesson.extendedProps.group_id,
                teacher_id: lesson.extendedProps.teacher_id,
                lesson_date: lesson.start.split(' ')[0],
                lesson_time: lesson.start.split(' ')[1].substring(0, 5)
            };
            this.modalMode = 'view';
            this.showModal = true;
        },

        editLesson(data) {
            const dayOfWeek = new Date(data.lesson_date).getDay();
            this.form = {
                id: data.id,
                lessonType: data.student_id ? 'individual' : 'group',
                student_id: data.student_id || '',
                group_id: data.group_id || '',
                teacher_id: data.teacher_id,
                selectedDays: [dayOfWeek],
                lesson_time: data.lesson_time,
                duration: data.duration,
                status: data.status,
                notes: data.notes || ''
            };
            this.modalMode = 'edit';
        },

        async saveLesson() {
            try {
                if (this.form.selectedDays.length === 0) {
                    showNotification('Выберите хотя бы один день недели', 'error');
                    return;
                }

                // Если это редактирование, сохраняем как обычно
                if (this.modalMode === 'edit') {
                    // Находим дату урока на основе выбранного дня
                    const selectedDay = this.form.selectedDays[0];
                    const targetDate = this.weekDays.find(d => new Date(d.date).getDay() === selectedDay);

                    const response = await fetch('/api/schedule.php', {
                        method: 'PUT',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            id: this.form.id,
                            student_id: this.form.student_id || null,
                            group_id: this.form.group_id || null,
                            teacher_id: this.form.teacher_id,
                            lesson_date: targetDate.date,
                            lesson_time: this.form.lesson_time,
                            duration: this.form.duration,
                            status: this.form.status,
                            notes: this.form.notes
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        showNotification(data.message, 'success');
                        this.showModal = false;
                        await this.loadLessons();
                    } else {
                        showNotification(data.error || 'Ошибка сохранения', 'error');
                    }
                } else {
                    // Создаём уроки для каждого выбранного дня
                    const promises = [];

                    for (const selectedDay of this.form.selectedDays) {
                        const targetDate = this.weekDays.find(d => new Date(d.date).getDay() === selectedDay);
                        if (!targetDate) continue;

                        promises.push(
                            fetch('/api/schedule.php', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({
                                    student_id: this.form.student_id || null,
                                    group_id: this.form.group_id || null,
                                    teacher_id: this.form.teacher_id,
                                    room_id: 1, // Заглушка для обязательного поля
                                    lesson_date: targetDate.date,
                                    lesson_time: this.form.lesson_time,
                                    duration: this.form.duration,
                                    status: this.form.status,
                                    notes: this.form.notes
                                })
                            })
                        );
                    }

                    const results = await Promise.all(promises);
                    const allSuccess = results.every(async r => {
                        const data = await r.json();
                        return data.success;
                    });

                    if (allSuccess) {
                        showNotification(`Создано уроков: ${this.form.selectedDays.length}`, 'success');
                        this.showModal = false;
                        await this.loadLessons();
                    } else {
                        showNotification('Ошибка создания некоторых уроков', 'error');
                    }
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
                    await this.loadLessons();
                } else {
                    showNotification(data.error || 'Ошибка удаления', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка удаления', 'error');
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
