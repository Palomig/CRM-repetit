<?php
require_once 'includes/header.php';

// Получение родителей для выбора
$parents = db()->fetchAll("SELECT id, name, phone FROM parents ORDER BY name");

// Получение преподавателей
$teachers = db()->fetchAll("SELECT id, name FROM teachers WHERE status = 'active' ORDER BY name");

// Получение групп
$groups = db()->fetchAll("SELECT id, name, subject FROM `groups` WHERE status = 'active' ORDER BY name");
?>

<div x-data="studentsApp()">
    <!-- Заголовок и кнопки -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold text-white">Ученики</h1>
            <p class="text-gray-400 mt-1">Управление учениками центра</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
            <button @click="showModal = true; modalMode = 'create'; resetForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Добавить ученика
            </button>
            <button @click="showParentModal = true; resetParentForm()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-user-plus mr-2"></i>Добавить родителя
            </button>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Статус</label>
                <select x-model="filters.status" @change="loadStudents()" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Все</option>
                    <option value="active">Активные</option>
                    <option value="paused">Приостановлены</option>
                    <option value="inactive">Неактивные</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Тип обучения</label>
                <select x-model="filters.type" @change="loadStudents()" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Все</option>
                    <option value="individual">Индивидуальные</option>
                    <option value="group">Групповые</option>
                </select>
            </div>
            <div class="flex items-end">
                <button @click="filters = {status: '', type: ''}; loadStudents()" class="w-full px-4 py-2 bg-gray-700 text-gray-200 rounded-lg hover:bg-gray-600 transition-colors">
                    <i class="fas fa-redo mr-2"></i>Сбросить фильтры
                </button>
            </div>
        </div>
    </div>

    <!-- Таблица учеников -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table id="studentsTable" class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Ученик</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Класс</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Предмет</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Родитель</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <template x-for="student in students" :key="student.id">
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-white" x-text="student.name"></div>
                                <div class="text-sm text-gray-400" x-show="student.group_name" x-text="'Группа: ' + student.group_name"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200" x-text="student.class"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200" x-text="student.subject"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="student.type === 'individual' ? 'bg-blue-500/20 text-blue-400' : 'bg-purple-500/20 text-purple-400'"
                                      x-text="student.type === 'individual' ? 'Индивид.' : 'Группа'">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-200" x-text="student.parent_name"></div>
                                <div class="flex items-center space-x-2 mt-1">
                                    <a :href="'tel:' + student.parent_phone" class="text-blue-400 hover:text-blue-300">
                                        <i class="fas fa-phone text-xs"></i>
                                    </a>
                                    <a x-show="student.parent_whatsapp" :href="'https://wa.me/' + student.parent_whatsapp.replace(/[^0-9]/g, '')" target="_blank" class="text-green-400 hover:text-green-300">
                                        <i class="fab fa-whatsapp text-xs"></i>
                                    </a>
                                    <a x-show="student.parent_telegram" :href="'https://t.me/' + student.parent_telegram.replace('@', '')" target="_blank" class="text-blue-400 hover:text-blue-300">
                                        <i class="fab fa-telegram text-xs"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="{
                                          'bg-green-500/20 text-green-400': student.status === 'active',
                                          'bg-yellow-500/20 text-yellow-400': student.status === 'paused',
                                          'bg-gray-500/20 text-gray-400': student.status === 'inactive'
                                      }"
                                      x-text="student.status === 'active' ? 'Активен' : student.status === 'paused' ? 'Пауза' : 'Неактивен'">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="viewStudent(student)" class="text-blue-400 hover:text-blue-300 mr-3">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button @click="editStudent(student)" class="text-indigo-400 hover:text-indigo-300 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="deleteStudentConfirm(student.id)" class="text-red-400 hover:text-red-300">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Модальное окно ученика -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-w-2xl w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white" x-text="modalMode === 'create' ? 'Добавить ученика' : modalMode === 'edit' ? 'Редактировать ученика' : 'Информация об ученике'"></h3>
                    <button @click="showModal = false" class="text-gray-500 hover:text-gray-400">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveStudent" x-show="modalMode !== 'view'">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-200 mb-2">Имя ученика *</label>
                            <input type="text" x-model="form.name" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Класс *</label>
                            <input type="text" x-model="form.class" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Предмет *</label>
                            <input type="text" x-model="form.subject" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Родитель *</label>
                            <select x-model="form.parent_id" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите родителя</option>
                                <?php foreach ($parents as $parent): ?>
                                    <option value="<?= $parent['id'] ?>"><?= e($parent['name']) ?> (<?= e($parent['phone']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Тип обучения *</label>
                            <select x-model="form.type" @change="handleTypeChange()" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="individual">Индивидуальное</option>
                                <option value="group">Групповое</option>
                            </select>
                        </div>

                        <div x-show="form.type === 'group'">
                            <label class="block text-sm font-medium text-gray-200 mb-2">Группа</label>
                            <select x-model="form.group_id" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите группу</option>
                                <?php foreach ($groups as $group): ?>
                                    <option value="<?= $group['id'] ?>"><?= e($group['name']) ?> (<?= e($group['subject']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div x-show="form.type === 'individual'">
                            <label class="block text-sm font-medium text-gray-200 mb-2">Преподаватель</label>
                            <select x-model="form.teacher_id" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите преподавателя</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= e($teacher['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Стоимость урока (₽)</label>
                            <input type="number" x-model="form.price" step="0.01" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Статус</label>
                            <select x-model="form.status" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="active">Активен</option>
                                <option value="paused">Приостановлен</option>
                                <option value="inactive">Неактивен</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-200 mb-2">Примечания</label>
                            <textarea x-model="form.notes" rows="3" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
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
                            <p class="text-sm text-gray-400">Класс</p>
                            <p class="font-medium text-white" x-text="viewData.class"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Предмет</p>
                            <p class="font-medium text-white" x-text="viewData.subject"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Тип обучения</p>
                            <p class="font-medium text-white" x-text="viewData.type === 'individual' ? 'Индивидуальное' : 'Групповое'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Статус</p>
                            <p class="font-medium text-white" x-text="viewData.status === 'active' ? 'Активен' : viewData.status === 'paused' ? 'Пауза' : 'Неактивен'"></p>
                        </div>
                    </div>
                    <div class="border-t border-gray-700 pt-4">
                        <p class="text-sm text-gray-400 mb-2">Родитель</p>
                        <p class="font-medium text-white" x-text="viewData.parent_name"></p>
                        <div class="flex space-x-4 mt-2">
                            <a :href="'tel:' + viewData.parent_phone" class="text-blue-400 hover:text-blue-300">
                                <i class="fas fa-phone mr-1"></i><span x-text="viewData.parent_phone"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно родителя -->
    <div x-show="showParentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showParentModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">Добавить родителя</h3>
                    <button @click="showParentModal = false" class="text-gray-500 hover:text-gray-400">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveParent">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Имя *</label>
                            <input type="text" x-model="parentForm.name" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Телефон *</label>
                            <input type="tel" x-model="parentForm.phone" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">WhatsApp</label>
                            <input type="text" x-model="parentForm.whatsapp" placeholder="+79001234567" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Telegram</label>
                            <input type="text" x-model="parentForm.telegram" placeholder="@username" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showParentModal = false" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-200 hover:bg-gray-600">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Добавить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function studentsApp() {
    return {
        students: [],
        filters: {
            status: '',
            type: ''
        },
        showModal: false,
        showParentModal: false,
        modalMode: 'create',
        form: {
            id: null,
            name: '',
            class: '',
            parent_id: '',
            subject: '',
            type: 'individual',
            group_id: '',
            teacher_id: '',
            price: '',
            status: 'active',
            notes: ''
        },
        parentForm: {
            name: '',
            phone: '',
            whatsapp: '',
            telegram: ''
        },
        viewData: {},

        init() {
            this.loadStudents();
        },

        async loadStudents() {
            try {
                const params = new URLSearchParams();
                if (this.filters.status) params.append('status', this.filters.status);
                if (this.filters.type) params.append('type', this.filters.type);

                const response = await fetch(`/api/students.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    this.students = data.data;
                }
            } catch (error) {
                console.error('Error loading students:', error);
                showNotification('Ошибка загрузки данных', 'error');
            }
        },

        handleTypeChange() {
            if (this.form.type === 'individual') {
                this.form.group_id = '';
            } else {
                this.form.teacher_id = '';
            }
        },

        resetForm() {
            this.form = {
                id: null,
                name: '',
                class: '',
                parent_id: '',
                subject: '',
                type: 'individual',
                group_id: '',
                teacher_id: '',
                price: '',
                status: 'active',
                notes: ''
            };
        },

        resetParentForm() {
            this.parentForm = {
                name: '',
                phone: '',
                whatsapp: '',
                telegram: ''
            };
        },

        editStudent(student) {
            this.form = {
                id: student.id,
                name: student.name,
                class: student.class,
                parent_id: student.parent_id,
                subject: student.subject,
                type: student.type,
                group_id: student.group_id || '',
                teacher_id: student.teacher_id || '',
                price: student.price,
                status: student.status,
                notes: student.notes || ''
            };
            this.modalMode = 'edit';
            this.showModal = true;
        },

        async viewStudent(student) {
            try {
                const response = await fetch(`/api/students.php?id=${student.id}`);
                const data = await response.json();
                
                if (data.success) {
                    this.viewData = data.data;
                    this.modalMode = 'view';
                    this.showModal = true;
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка загрузки данных', 'error');
            }
        },

        async saveStudent() {
            try {
                const method = this.modalMode === 'create' ? 'POST' : 'PUT';
                const response = await fetch('/api/students.php', {
                    method: method,
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.showModal = false;
                    this.loadStudents();
                } else {
                    showNotification(data.error || 'Ошибка сохранения', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка сохранения', 'error');
            }
        },

        async deleteStudentConfirm(id) {
            if (!confirmAction('Вы уверены, что хотите удалить ученика?')) return;

            try {
                const response = await fetch('/api/students.php', {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id})
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.loadStudents();
                } else {
                    showNotification(data.error || 'Ошибка удаления', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка удаления', 'error');
            }
        },

        async saveParent() {
            try {
                const response = await fetch('/api/parents.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.parentForm)
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.showParentModal = false;
                    location.reload();
                } else {
                    showNotification(data.error || 'Ошибка сохранения', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка сохранения', 'error');
            }
        }
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>