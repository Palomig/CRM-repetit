<?php
require_once 'includes/header.php';

$teachers = db()->fetchAll("SELECT id, name FROM teachers WHERE status = 'active' ORDER BY name");
$rooms = db()->fetchAll("SELECT id, name FROM rooms WHERE status = 'active' ORDER BY name");
?>

<div x-data="groupsApp()">
    <!-- Заголовок и кнопки -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Группы</h1>
            <p class="text-gray-600 mt-1">Управление учебными группами</p>
        </div>
        <button @click="showModal = true; modalMode = 'create'; resetForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Создать группу
        </button>
    </div>

    <!-- Карточки групп -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="group in groups" :key="group.id">
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900" x-text="group.name"></h3>
                            <p class="text-sm text-gray-600 mt-1" x-text="group.subject"></p>
                        </div>
                        <div class="flex space-x-2">
                            <button @click="editGroup(group)" class="text-indigo-600 hover:text-indigo-900">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button @click="deleteGroupConfirm(group.id)" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Заполненность -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Заполненность:</span>
                            <span class="text-sm font-bold" 
                                  :class="group.current_students >= group.max_students ? 'text-red-600' : 'text-green-600'"
                                  x-text="group.current_students + '/' + group.max_students">
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all" 
                                 :class="group.current_students >= group.max_students ? 'bg-red-600' : 'bg-green-600'"
                                 :style="'width: ' + (group.current_students / group.max_students * 100) + '%'">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-chalkboard-teacher mr-2"></i>
                            <span x-text="group.teacher_name"></span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-door-open mr-2"></i>
                            <span x-text="group.room_name"></span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-ruble-sign mr-2"></i>
                            <span x-text="formatMoney(group.price) + ' / урок'"></span>
                        </div>
                    </div>

                    <!-- Список учеников -->
                    <div x-show="group.students && group.students.length > 0" class="pt-4 border-t border-gray-200">
                        <p class="text-xs font-medium text-gray-600 mb-2">Ученики:</p>
                        <div class="space-y-1">
                            <template x-for="student in group.students" :key="student.id">
                                <div class="text-xs text-gray-700 flex items-center">
                                    <i class="fas fa-user-graduate mr-2 text-gray-400"></i>
                                    <span x-text="student.name + ' (' + student.class + ')'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Статус -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <span class="inline-block px-2 py-1 text-xs font-medium rounded-full" 
                              :class="group.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                              x-text="group.status === 'active' ? 'Активна' : 'Неактивна'">
                        </span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Модальное окно -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900" x-text="modalMode === 'create' ? 'Создать группу' : 'Редактировать группу'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveGroup">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Название группы *</label>
                            <input type="text" x-model="form.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Предмет *</label>
                            <input type="text" x-model="form.subject" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
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

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Максимум учеников</label>
                                <input type="number" x-model="form.max_students" min="1" max="12" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Стоимость (₽)</label>
                                <input type="number" x-model="form.price" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                            <select x-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="active">Активна</option>
                                <option value="inactive">Неактивна</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <span x-text="modalMode === 'create' ? 'Создать' : 'Сохранить'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function groupsApp() {
    return {
        groups: [],
        showModal: false,
        modalMode: 'create',
        form: {
            id: null,
            name: '',
            subject: '',
            teacher_id: '',
            room_id: '',
            max_students: 6,
            price: '',
            status: 'active'
        },

        init() {
            this.loadGroups();
        },

        async loadGroups() {
            try {
                const response = await fetch('/api/groups.php');
                const data = await response.json();
                
                if (data.success) {
                    this.groups = data.data;
                }
            } catch (error) {
                console.error('Error loading groups:', error);
                showNotification('Ошибка загрузки данных', 'error');
            }
        },

        resetForm() {
            this.form = {
                id: null,
                name: '',
                subject: '',
                teacher_id: '',
                room_id: '',
                max_students: 6,
                price: '',
                status: 'active'
            };
        },

        editGroup(group) {
            this.form = {
                id: group.id,
                name: group.name,
                subject: group.subject,
                teacher_id: group.teacher_id,
                room_id: group.room_id,
                max_students: group.max_students,
                price: group.price,
                status: group.status
            };
            this.modalMode = 'edit';
            this.showModal = true;
        },

        async saveGroup() {
            try {
                const method = this.modalMode === 'create' ? 'POST' : 'PUT';
                const response = await fetch('/api/groups.php', {
                    method: method,
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.showModal = false;
                    this.loadGroups();
                } else {
                    showNotification(data.error || 'Ошибка сохранения', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка сохранения', 'error');
            }
        },

        async deleteGroupConfirm(id) {
            if (!confirmAction('Вы уверены, что хотите удалить группу?')) return;

            try {
                const response = await fetch('/api/groups.php', {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id})
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.loadGroups();
                } else {
                    showNotification(data.error || 'Ошибка удаления', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка удаления', 'error');
            }
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 0
            }).format(amount);
        }
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>