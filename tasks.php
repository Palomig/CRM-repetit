<?php
require_once 'includes/header.php';

$students = db()->fetchAll("SELECT id, name FROM students WHERE status = 'active' ORDER BY name");
$teachers = db()->fetchAll("SELECT id, name FROM teachers WHERE status = 'active' ORDER BY name");
?>

<!-- SortableJS for drag & drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div x-data="kanbanApp()" class="h-full">
    <!-- Заголовок и кнопки -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold text-white">Канбан доски</h1>
            <p class="text-gray-400 mt-1">Управление задачами в стиле Monday.com</p>
        </div>
        <div class="flex space-x-3">
            <button @click="openBoardModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Новая доска
            </button>
            <button @click="openTaskModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Добавить задачу
            </button>
        </div>
    </div>

    <!-- Вкладки досок -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow mb-6">
        <div class="flex items-center overflow-x-auto">
            <template x-for="board in boards" :key="board.id">
                <button
                    @click="switchBoard(board.id)"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium transition-colors border-b-2 whitespace-nowrap"
                    :class="currentBoardId === board.id ? 'border-blue-500 text-blue-400 bg-gray-700' : 'border-transparent text-gray-400 hover:text-gray-200 hover:bg-gray-750'">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    <span x-text="board.name"></span>
                    <span class="ml-2 px-2 py-0.5 text-xs bg-gray-600 rounded-full" x-text="getBoardTaskCount(board.id)"></span>
                </button>
            </template>
            <button
                @click="openBoardModal()"
                class="flex-shrink-0 px-4 py-4 text-sm font-medium text-gray-500 hover:text-gray-300 transition-colors">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>

    <!-- Управление доской -->
    <div x-show="currentBoard" class="mb-4 flex justify-end space-x-2">
        <button @click="editCurrentBoard()" class="px-3 py-1.5 text-sm bg-gray-700 text-gray-300 rounded hover:bg-gray-600">
            <i class="fas fa-edit mr-1"></i>Редактировать доску
        </button>
        <button @click="deleteCurrentBoard()" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">
            <i class="fas fa-trash mr-1"></i>Удалить доску
        </button>
    </div>

    <!-- Канбан доска -->
    <div x-show="currentBoard" class="overflow-x-auto pb-4">
        <div class="flex space-x-6 min-w-full">
            <!-- Колонка: Ожидают -->
            <div class="flex-1 min-w-[350px] bg-gray-800 border border-gray-700 rounded-lg shadow">
                <div class="p-4 border-b border-gray-700 bg-yellow-500/10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-yellow-400 flex items-center">
                            <i class="fas fa-clock mr-2"></i>
                            Ожидают
                        </h3>
                        <span class="px-2 py-1 text-xs bg-yellow-500/20 text-yellow-300 rounded-full font-medium" x-text="getColumnTaskCount('pending')"></span>
                    </div>
                </div>
                <div
                    class="p-4 space-y-3 min-h-[500px]"
                    :id="'kanban-pending'"
                    data-status="pending">
                    <template x-for="task in getTasksByStatus('pending')" :key="task.id">
                        <div :data-task-id="task.id" class="task-card bg-gray-750 border border-gray-600 rounded-lg p-4 cursor-move hover:border-yellow-500 transition-all shadow-sm hover:shadow-md">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="text-white font-medium flex-1 pr-2" x-text="task.title"></h4>
                                <div class="flex space-x-1">
                                    <button @click.stop="editTask(task)" class="p-1 text-indigo-400 hover:text-indigo-300 hover:bg-gray-700 rounded">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button @click.stop="deleteTaskConfirm(task.id)" class="p-1 text-red-400 hover:text-red-300 hover:bg-gray-700 rounded">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <p x-show="task.description" class="text-sm text-gray-400 mb-3" x-text="task.description"></p>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 rounded-full"
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
                                <div class="text-gray-400">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <span x-text="formatDate(task.due_date)"></span>
                                    <span x-show="isOverdue(task.due_date)" class="ml-1 text-red-400">!</span>
                                </div>
                            </div>
                            <div x-show="task.student_name || task.teacher_name" class="mt-2 pt-2 border-t border-gray-600 text-xs text-gray-400">
                                <div x-show="task.student_name" class="flex items-center">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span x-text="task.student_name"></span>
                                </div>
                                <div x-show="task.teacher_name" class="flex items-center mt-1">
                                    <i class="fas fa-chalkboard-teacher mr-1"></i>
                                    <span x-text="task.teacher_name"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Колонка: В работе -->
            <div class="flex-1 min-w-[350px] bg-gray-800 border border-gray-700 rounded-lg shadow">
                <div class="p-4 border-b border-gray-700 bg-blue-500/10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-blue-400 flex items-center">
                            <i class="fas fa-spinner mr-2"></i>
                            В работе
                        </h3>
                        <span class="px-2 py-1 text-xs bg-blue-500/20 text-blue-300 rounded-full font-medium" x-text="getColumnTaskCount('in_progress')"></span>
                    </div>
                </div>
                <div
                    class="p-4 space-y-3 min-h-[500px]"
                    :id="'kanban-in_progress'"
                    data-status="in_progress">
                    <template x-for="task in getTasksByStatus('in_progress')" :key="task.id">
                        <div :data-task-id="task.id" class="task-card bg-gray-750 border border-gray-600 rounded-lg p-4 cursor-move hover:border-blue-500 transition-all shadow-sm hover:shadow-md">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="text-white font-medium flex-1 pr-2" x-text="task.title"></h4>
                                <div class="flex space-x-1">
                                    <button @click.stop="editTask(task)" class="p-1 text-indigo-400 hover:text-indigo-300 hover:bg-gray-700 rounded">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button @click.stop="deleteTaskConfirm(task.id)" class="p-1 text-red-400 hover:text-red-300 hover:bg-gray-700 rounded">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <p x-show="task.description" class="text-sm text-gray-400 mb-3" x-text="task.description"></p>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 rounded-full"
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
                                <div class="text-gray-400">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <span x-text="formatDate(task.due_date)"></span>
                                    <span x-show="isOverdue(task.due_date)" class="ml-1 text-red-400">!</span>
                                </div>
                            </div>
                            <div x-show="task.student_name || task.teacher_name" class="mt-2 pt-2 border-t border-gray-600 text-xs text-gray-400">
                                <div x-show="task.student_name" class="flex items-center">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span x-text="task.student_name"></span>
                                </div>
                                <div x-show="task.teacher_name" class="flex items-center mt-1">
                                    <i class="fas fa-chalkboard-teacher mr-1"></i>
                                    <span x-text="task.teacher_name"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Колонка: Завершены -->
            <div class="flex-1 min-w-[350px] bg-gray-800 border border-gray-700 rounded-lg shadow">
                <div class="p-4 border-b border-gray-700 bg-green-500/10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-green-400 flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            Завершены
                        </h3>
                        <span class="px-2 py-1 text-xs bg-green-500/20 text-green-300 rounded-full font-medium" x-text="getColumnTaskCount('completed')"></span>
                    </div>
                </div>
                <div
                    class="p-4 space-y-3 min-h-[500px]"
                    :id="'kanban-completed'"
                    data-status="completed">
                    <template x-for="task in getTasksByStatus('completed')" :key="task.id">
                        <div :data-task-id="task.id" class="task-card bg-gray-750 border border-gray-600 rounded-lg p-4 cursor-move hover:border-green-500 transition-all shadow-sm hover:shadow-md opacity-75">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="text-white font-medium flex-1 pr-2 line-through" x-text="task.title"></h4>
                                <div class="flex space-x-1">
                                    <button @click.stop="editTask(task)" class="p-1 text-indigo-400 hover:text-indigo-300 hover:bg-gray-700 rounded">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button @click.stop="deleteTaskConfirm(task.id)" class="p-1 text-red-400 hover:text-red-300 hover:bg-gray-700 rounded">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <p x-show="task.description" class="text-sm text-gray-400 mb-3" x-text="task.description"></p>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 rounded-full"
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
                                <div class="text-gray-400">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <span x-text="formatDate(task.due_date)"></span>
                                </div>
                            </div>
                            <div x-show="task.student_name || task.teacher_name" class="mt-2 pt-2 border-t border-gray-600 text-xs text-gray-400">
                                <div x-show="task.student_name" class="flex items-center">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span x-text="task.student_name"></span>
                                </div>
                                <div x-show="task.teacher_name" class="flex items-center mt-1">
                                    <i class="fas fa-chalkboard-teacher mr-1"></i>
                                    <span x-text="task.teacher_name"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Пустое состояние -->
    <div x-show="boards.length === 0" class="bg-gray-800 border border-gray-700 rounded-lg shadow p-12 text-center">
        <i class="fas fa-clipboard-list text-6xl text-gray-600 mb-4"></i>
        <p class="text-gray-400 mb-4">У вас еще нет досок</p>
        <button @click="openBoardModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Создать первую доску
        </button>
    </div>

    <!-- Модальное окно для задачи -->
    <div x-show="showTaskModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showTaskModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white" x-text="taskModalMode === 'create' ? 'Добавить задачу' : 'Редактировать задачу'"></h3>
                    <button @click="showTaskModal = false" class="text-gray-500 hover:text-gray-400">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveTask">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Название задачи *</label>
                            <input type="text" x-model="taskForm.title" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Описание</label>
                            <textarea x-model="taskForm.description" rows="3" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Срок выполнения *</label>
                            <input type="date" x-model="taskForm.due_date" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Приоритет</label>
                                <select x-model="taskForm.priority" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="low">Низкий</option>
                                    <option value="medium">Средний</option>
                                    <option value="high">Высокий</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Статус</label>
                                <select x-model="taskForm.status" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="pending">Ожидает</option>
                                    <option value="in_progress">В работе</option>
                                    <option value="completed">Завершена</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Ученик</label>
                            <select x-model="taskForm.student_id" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Не указан</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>"><?= e($student['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Преподаватель</label>
                            <select x-model="taskForm.teacher_id" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Не указан</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= e($teacher['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showTaskModal = false" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-200 hover:bg-gray-600">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <span x-text="taskModalMode === 'create' ? 'Добавить' : 'Сохранить'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно для доски -->
    <div x-show="showBoardModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showBoardModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white" x-text="boardModalMode === 'create' ? 'Создать доску' : 'Редактировать доску'"></h3>
                    <button @click="showBoardModal = false" class="text-gray-500 hover:text-gray-400">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveBoard">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Название доски *</label>
                            <input type="text" x-model="boardForm.name" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Описание</label>
                            <textarea x-model="boardForm.description" rows="3" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showBoardModal = false" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-200 hover:bg-gray-600">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <span x-text="boardModalMode === 'create' ? 'Создать' : 'Сохранить'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function kanbanApp() {
    return {
        boards: [],
        tasks: [],
        currentBoardId: null,
        showTaskModal: false,
        showBoardModal: false,
        taskModalMode: 'create',
        boardModalMode: 'create',
        sortableInstances: {},
        taskForm: {
            id: null,
            title: '',
            description: '',
            due_date: new Date().toISOString().split('T')[0],
            priority: 'medium',
            status: 'pending',
            student_id: '',
            teacher_id: '',
            board_id: null
        },
        boardForm: {
            id: null,
            name: '',
            description: ''
        },

        get currentBoard() {
            return this.boards.find(b => b.id === this.currentBoardId);
        },

        async init() {
            await this.loadBoards();
            if (this.boards.length > 0) {
                this.switchBoard(this.boards[0].id);
            }
        },

        async loadBoards() {
            try {
                const response = await fetch('/api/boards.php');
                const data = await response.json();

                if (data.success) {
                    this.boards = data.data;
                }
            } catch (error) {
                console.error('Error loading boards:', error);
                showNotification('Ошибка загрузки досок', 'error');
            }
        },

        async loadTasks() {
            if (!this.currentBoardId) return;

            try {
                const response = await fetch(`/api/tasks.php?board_id=${this.currentBoardId}`);
                const data = await response.json();

                if (data.success) {
                    this.tasks = data.data;
                    // Reinitialize sortable after tasks are loaded
                    this.$nextTick(() => this.initSortable());
                }
            } catch (error) {
                console.error('Error loading tasks:', error);
                showNotification('Ошибка загрузки задач', 'error');
            }
        },

        switchBoard(boardId) {
            this.currentBoardId = boardId;
            this.loadTasks();
        },

        getTasksByStatus(status) {
            return this.tasks.filter(task => task.status === status);
        },

        getColumnTaskCount(status) {
            return this.getTasksByStatus(status).length;
        },

        getBoardTaskCount(boardId) {
            return this.tasks.filter(task => task.board_id == boardId).length;
        },

        initSortable() {
            const statuses = ['pending', 'in_progress', 'completed'];

            // Destroy existing instances
            Object.values(this.sortableInstances).forEach(instance => {
                if (instance) instance.destroy();
            });
            this.sortableInstances = {};

            statuses.forEach(status => {
                const el = document.getElementById(`kanban-${status}`);
                if (el) {
                    this.sortableInstances[status] = Sortable.create(el, {
                        group: 'kanban',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        dragClass: 'shadow-2xl',
                        onEnd: (evt) => this.handleDrop(evt)
                    });
                }
            });
        },

        async handleDrop(evt) {
            const taskId = evt.item.dataset.taskId;
            const newStatus = evt.to.dataset.status;
            const newPosition = evt.newIndex;

            try {
                const response = await fetch('/api/tasks.php', {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        id: taskId,
                        status: newStatus,
                        position: newPosition
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('Задача перемещена', 'success');
                    await this.loadTasks();
                } else {
                    showNotification('Ошибка перемещения', 'error');
                    await this.loadTasks();
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка перемещения', 'error');
                await this.loadTasks();
            }
        },

        openTaskModal() {
            if (!this.currentBoardId) {
                showNotification('Сначала создайте или выберите доску', 'warning');
                return;
            }
            this.resetTaskForm();
            this.taskModalMode = 'create';
            this.showTaskModal = true;
        },

        openBoardModal() {
            this.resetBoardForm();
            this.boardModalMode = 'create';
            this.showBoardModal = true;
        },

        resetTaskForm() {
            this.taskForm = {
                id: null,
                title: '',
                description: '',
                due_date: new Date().toISOString().split('T')[0],
                priority: 'medium',
                status: 'pending',
                student_id: '',
                teacher_id: '',
                board_id: this.currentBoardId
            };
        },

        resetBoardForm() {
            this.boardForm = {
                id: null,
                name: '',
                description: ''
            };
        },

        editTask(task) {
            this.taskForm = {
                id: task.id,
                title: task.title,
                description: task.description || '',
                due_date: task.due_date,
                priority: task.priority,
                status: task.status,
                student_id: task.student_id || '',
                teacher_id: task.teacher_id || '',
                board_id: task.board_id
            };
            this.taskModalMode = 'edit';
            this.showTaskModal = true;
        },

        editCurrentBoard() {
            if (!this.currentBoard) return;

            this.boardForm = {
                id: this.currentBoard.id,
                name: this.currentBoard.name,
                description: this.currentBoard.description || ''
            };
            this.boardModalMode = 'edit';
            this.showBoardModal = true;
        },

        async saveTask() {
            try {
                const method = this.taskModalMode === 'create' ? 'POST' : 'PUT';
                const response = await fetch('/api/tasks.php', {
                    method: method,
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.taskForm)
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');
                    this.showTaskModal = false;
                    await this.loadTasks();
                } else {
                    showNotification(data.error || 'Ошибка сохранения', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка сохранения', 'error');
            }
        },

        async saveBoard() {
            try {
                const method = this.boardModalMode === 'create' ? 'POST' : 'PUT';
                const response = await fetch('/api/boards.php', {
                    method: method,
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.boardForm)
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message || 'Доска сохранена', 'success');
                    this.showBoardModal = false;
                    await this.loadBoards();

                    if (this.boardModalMode === 'create') {
                        this.switchBoard(data.id);
                    }
                } else {
                    showNotification(data.error || 'Ошибка сохранения', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка сохранения', 'error');
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
                    await this.loadTasks();
                } else {
                    showNotification(data.error || 'Ошибка удаления', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка удаления', 'error');
            }
        },

        async deleteCurrentBoard() {
            if (!this.currentBoard) return;

            if (!confirmAction('Вы уверены, что хотите удалить доску? Все задачи на этой доске будут удалены.')) return;

            try {
                const response = await fetch('/api/boards.php', {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: this.currentBoard.id})
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');
                    this.currentBoardId = null;
                    await this.loadBoards();

                    if (this.boards.length > 0) {
                        this.switchBoard(this.boards[0].id);
                    }
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

<style>
[x-cloak] { display: none !important; }
.task-card {
    transition: all 0.2s ease;
}
.sortable-ghost {
    opacity: 0.4;
}
</style>

<?php require_once 'includes/footer.php'; ?>
