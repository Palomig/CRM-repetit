<?php
require_once 'includes/header.php';

$students = db()->fetchAll("SELECT id, name FROM students WHERE status = 'active' ORDER BY name");
$teachers = db()->fetchAll("SELECT id, name FROM teachers WHERE status = 'active' ORDER BY name");
?>

<div x-data="tasksApp()">
    <!-- Заголовок и кнопки -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold text-white">Задачи</h1>
            <p class="text-gray-400 mt-1">Управление задачами центра</p>
        </div>
        <button @click="showModal = true; modalMode = 'create'; resetForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Добавить задачу
        </button>
    </div>

    <!-- Фильтры -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <button @click="filterStatus = ''; loadTasks()"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    :class="filterStatus === '' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-200 hover:bg-gray-600'">
                Все
            </button>
            <button @click="filterStatus = 'pending'; loadTasks()"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    :class="filterStatus === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-200 hover:bg-gray-600'">
                Ожидают
            </button>
            <button @click="filterStatus = 'in_progress'; loadTasks()"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    :class="filterStatus === 'in_progress' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-200 hover:bg-gray-600'">
                В работе
            </button>
            <button @click="filterStatus = 'completed'; loadTasks()"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    :class="filterStatus === 'completed' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-200 hover:bg-gray-600'">
                Завершены
            </button>
        </div>
    </div>

    <!-- Список задач -->
    <div class="space-y-4">
        <template x-for="task in tasks" :key="task.id">
            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            <!-- Чекбокс -->
                            <input
                                type="checkbox"
                                :checked="task.status === 'completed'"
                                @change="toggleTaskStatus(task)"
                                class="mt-1 h-5 w-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer">

                            <div class="flex-1">
                                <!-- Заголовок и приоритет -->
                                <div class="flex items-center space-x-2 mb-2">
                                    <h3 class="text-lg font-medium text-white"
                                        :class="{'line-through text-gray-500': task.status === 'completed'}"
                                        x-text="task.title">
                                    </h3>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full"
                                          :class="{
                                              'bg-red-500/20 text-red-400': task.priority === 'high',
                                              'bg-yellow-500/20 text-yellow-400': task.priority === 'medium',
                                              'bg-gray-500/20 text-gray-400': task.priority === 'low'
                                          }">
                                        <template x-if="task.priority === 'high'">Высокий</template>
                                        <template x-if="task.priority === 'medium'">Средний</template>
                                        <template x-if="task.priority === 'low'">Низкий</template>
                                    </span>
                                </div>

                                <!-- Описание -->
                                <p x-show="task.description" class="text-sm text-gray-400 mb-3" x-text="task.description"></p>

                                <!-- Метаданные -->
                                <div class="flex flex-wrap gap-4 text-sm text-gray-400">
                                    <div x-show="task.student_name">
                                        <i class="fas fa-user-graduate mr-1"></i>
                                        <span class="text-gray-200" x-text="task.student_name"></span>
                                    </div>
                                    <div x-show="task.teacher_name">
                                        <i class="fas fa-chalkboard-teacher mr-1"></i>
                                        <span class="text-gray-200" x-text="task.teacher_name"></span>
                                    </div>
                                    <div>
                                        <i class="fas fa-calendar mr-1"></i>
                                        <span class="text-gray-200" x-text="formatDate(task.due_date)"></span>
                                        <span x-show="isOverdue(task.due_date) && task.status !== 'completed'" class="ml-1 text-red-400 font-medium">
                                            (просрочено)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="flex space-x-2 ml-4">
                            <button @click="editTask(task)" class="p-2 text-indigo-400 hover:text-indigo-300 hover:bg-gray-700 rounded">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button @click="deleteTaskConfirm(task.id)" class="p-2 text-red-400 hover:text-red-300 hover:bg-gray-700 rounded">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Статус -->
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full"
                              :class="{
                                  'bg-yellow-500/20 text-yellow-400': task.status === 'pending',
                                  'bg-blue-500/20 text-blue-400': task.status === 'in_progress',
                                  'bg-green-500/20 text-green-400': task.status === 'completed'
                              }">
                            <template x-if="task.status === 'pending'">Ожидает</template>
                            <template x-if="task.status === 'in_progress'">В работе</template>
                            <template x-if="task.status === 'completed'">Завершена</template>
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <!-- Пустое состояние -->
        <div x-show="tasks.length === 0" class="bg-gray-800 border border-gray-700 rounded-lg shadow p-12 text-center">
            <i class="fas fa-tasks text-6xl text-gray-600 mb-4"></i>
            <p class="text-gray-400">Нет задач</p>
        </div>
    </div>

    <!-- Модальное окно -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white" x-text="modalMode === 'create' ? 'Добавить задачу' : 'Редактировать задачу'"></h3>
                    <button @click="showModal = false" class="text-gray-500 hover:text-gray-400">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveTask">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Название задачи *</label>
                            <input type="text" x-model="form.title" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Описание</label>
                            <textarea x-model="form.description" rows="3" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Срок выполнения *</label>
                            <input type="date" x-model="form.due_date" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Приоритет</label>
                                <select x-model="form.priority" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="low">Низкий</option>
                                    <option value="medium">Средний</option>
                                    <option value="high">Высокий</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Статус</label>
                                <select x-model="form.status" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="pending">Ожидает</option>
                                    <option value="in_progress">В работе</option>
                                    <option value="completed">Завершена</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Ученик</label>
                            <select x-model="form.student_id" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Не указан</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>"><?= e($student['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Преподаватель</label>
                            <select x-model="form.teacher_id" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Не указан</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= e($teacher['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
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
            </div>
        </div>
    </div>
</div>

<script>
function tasksApp() {
    return {
        tasks: [],
        filterStatus: '',
        showModal: false,
        modalMode: 'create',
        form: {
            id: null,
            title: '',
            description: '',
            due_date: new Date().toISOString().split('T')[0],
            priority: 'medium',
            status: 'pending',
            student_id: '',
            teacher_id: ''
        },

        init() {
            this.loadTasks();
        },

        async loadTasks() {
            try {
                const params = new URLSearchParams();
                if (this.filterStatus) params.append('status', this.filterStatus);

                const response = await fetch(`/api/tasks.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    this.tasks = data.data;
                }
            } catch (error) {
                console.error('Error loading tasks:', error);
                showNotification('Ошибка загрузки данных', 'error');
            }
        },

        resetForm() {
            this.form = {
                id: null,
                title: '',
                description: '',
                due_date: new Date().toISOString().split('T')[0],
                priority: 'medium',
                status: 'pending',
                student_id: '',
                teacher_id: ''
            };
        },

        editTask(task) {
            this.form = {
                id: task.id,
                title: task.title,
                description: task.description || '',
                due_date: task.due_date,
                priority: task.priority,
                status: task.status,
                student_id: task.student_id || '',
                teacher_id: task.teacher_id || ''
            };
            this.modalMode = 'edit';
            this.showModal = true;
        },

        async saveTask() {
            try {
                const method = this.modalMode === 'create' ? 'POST' : 'PUT';
                const response = await fetch('/api/tasks.php', {
                    method: method,
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.showModal = false;
                    this.loadTasks();
                } else {
                    showNotification(data.error || 'Ошибка сохранения', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка сохранения', 'error');
            }
        },

        async toggleTaskStatus(task) {
            const newStatus = task.status === 'completed' ? 'pending' : 'completed';
            
            try {
                const response = await fetch('/api/tasks.php', {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: task.id, status: newStatus})
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification('Статус обновлен', 'success');
                    this.loadTasks();
                } else {
                    showNotification('Ошибка обновления', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка обновления', 'error');
            }
        },

        async deleteTaskConfirm(id) {
            if (!confirmAction('Вы уверены, что хотите удалить задачу?')) return;

            try {
                const response = await fetch('/api/tasks.php', {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id})
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.loadTasks();
                } else {
                    showNotification(data.error || 'Ошибка удаления', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка удаления', 'error');
            }
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('ru-RU');
        },

        isOverdue(dueDate) {
            return new Date(dueDate) < new Date().setHours(0,0,0,0);
        }
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>