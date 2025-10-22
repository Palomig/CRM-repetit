<?php
require_once 'includes/header.php';
?>

<div x-data="teachersApp()">
    <!-- Заголовок и кнопки -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold text-white">Преподаватели</h1>
            <p class="text-gray-400 mt-1">Управление преподавателями центра</p>
        </div>
        <button @click="showModal = true; modalMode = 'create'; resetForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Добавить преподавателя
        </button>
    </div>

    <!-- Карточки преподавателей -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="teacher in teachers" :key="teacher.id">
            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white" x-text="teacher.name"></h3>
                            <span class="inline-block mt-2 px-2 py-1 text-xs font-medium rounded-full"
                                  :class="teacher.status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'"
                                  x-text="teacher.status === 'active' ? 'Активен' : 'Неактивен'">
                            </span>
                        </div>
                        <div class="flex space-x-2">
                            <button @click="editTeacher(teacher)" class="text-indigo-400 hover:text-indigo-300">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button @click="deleteTeacherConfirm(teacher.id)" class="text-red-400 hover:text-red-300">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-400 mb-1">
                                <i class="fas fa-book mr-2"></i>Предметы:
                            </p>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="subject in teacher.subjects" :key="subject">
                                    <span class="px-2 py-1 bg-blue-500/20 text-blue-400 text-xs rounded-full" x-text="subject"></span>
                                </template>
                            </div>
                        </div>

                        <div x-show="teacher.phone" class="text-sm text-gray-400">
                            <i class="fas fa-phone mr-2"></i>
                            <a :href="'tel:' + teacher.phone" class="text-blue-400 hover:text-blue-300" x-text="teacher.phone"></a>
                        </div>

                        <div class="pt-3 border-t border-gray-700">
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div>
                                    <p class="text-2xl font-bold text-white" x-text="teacher.student_count"></p>
                                    <p class="text-xs text-gray-400">Индивид. занятий</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-white" x-text="teacher.group_count"></p>
                                    <p class="text-xs text-gray-400">Групп</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-700">
                            <p class="text-sm text-gray-400">Ставка за урок:</p>
                            <p class="text-lg font-bold text-green-400" x-text="formatMoney(teacher.salary_rate)"></p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Модальное окно -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white" x-text="modalMode === 'create' ? 'Добавить преподавателя' : 'Редактировать преподавателя'"></h3>
                    <button @click="showModal = false" class="text-gray-500 hover:text-gray-400">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveTeacher">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Имя *</label>
                            <input type="text" x-model="form.name" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Предметы (через запятую) *</label>
                            <input type="text" x-model="subjectsInput" required placeholder="Математика, Физика, Химия" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Введите предметы через запятую</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Телефон</label>
                            <input type="tel" x-model="form.phone" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Ставка за урок (₽)</label>
                            <input type="number" x-model="form.salary_rate" step="0.01" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Статус</label>
                            <select x-model="form.status" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="active">Активен</option>
                                <option value="inactive">Неактивен</option>
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
function teachersApp() {
    return {
        teachers: [],
        showModal: false,
        modalMode: 'create',
        subjectsInput: '',
        form: {
            id: null,
            name: '',
            subjects: [],
            phone: '',
            salary_rate: '',
            status: 'active'
        },

        init() {
            this.loadTeachers();
        },

        async loadTeachers() {
            try {
                const response = await fetch('/api/teachers.php');
                const data = await response.json();
                
                if (data.success) {
                    this.teachers = data.data;
                }
            } catch (error) {
                console.error('Error loading teachers:', error);
                showNotification('Ошибка загрузки данных', 'error');
            }
        },

        resetForm() {
            this.form = {
                id: null,
                name: '',
                subjects: [],
                phone: '',
                salary_rate: '',
                status: 'active'
            };
            this.subjectsInput = '';
        },

        editTeacher(teacher) {
            this.form = {
                id: teacher.id,
                name: teacher.name,
                subjects: teacher.subjects,
                phone: teacher.phone || '',
                salary_rate: teacher.salary_rate,
                status: teacher.status
            };
            this.subjectsInput = teacher.subjects.join(', ');
            this.modalMode = 'edit';
            this.showModal = true;
        },

        async saveTeacher() {
            this.form.subjects = this.subjectsInput.split(',').map(s => s.trim()).filter(s => s);

            try {
                const method = this.modalMode === 'create' ? 'POST' : 'PUT';
                const response = await fetch('/api/teachers.php', {
                    method: method,
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.showModal = false;
                    this.loadTeachers();
                } else {
                    showNotification(data.error || 'Ошибка сохранения', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка сохранения', 'error');
            }
        },

        async deleteTeacherConfirm(id) {
            if (!confirmAction('Вы уверены, что хотите удалить преподавателя?')) return;

            try {
                const response = await fetch('/api/teachers.php', {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id})
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.loadTeachers();
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