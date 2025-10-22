<?php
require_once 'includes/header.php';

$students = db()->fetchAll("SELECT id, name FROM students WHERE status = 'active' ORDER BY name");
$teachers = db()->fetchAll("SELECT id, name FROM teachers WHERE status = 'active' ORDER BY name");
?>

<div x-data="financeApp()">
    <!-- Заголовок -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-3xl font-bold text-white">Финансы</h1>
            <p class="text-gray-400 mt-1">Учет доходов и расходов</p>
        </div>
        <button @click="showModal = true; resetForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Добавить транзакцию
        </button>
    </div>

    <!-- Фильтр по месяцу -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
            <label class="text-sm font-medium text-gray-200">Период:</label>
            <select x-model="filters.month" @change="loadData()" class="px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="01">Январь</option>
                <option value="02">Февраль</option>
                <option value="03">Март</option>
                <option value="04">Апрель</option>
                <option value="05">Май</option>
                <option value="06">Июнь</option>
                <option value="07">Июль</option>
                <option value="08">Август</option>
                <option value="09">Сентябрь</option>
                <option value="10">Октябрь</option>
                <option value="11">Ноябрь</option>
                <option value="12">Декабрь</option>
            </select>
            <select x-model="filters.year" @change="loadData()" class="px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
            </select>
        </div>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Доход</p>
                    <p class="text-3xl font-bold text-green-400 mt-2" x-text="formatMoney(stats.summary?.total_income || 0)"></p>
                </div>
                <div class="bg-green-500/20 rounded-full p-3">
                    <i class="fas fa-arrow-up text-2xl text-green-400"></i>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Расход</p>
                    <p class="text-3xl font-bold text-red-400 mt-2" x-text="formatMoney(stats.summary?.total_expense || 0)"></p>
                </div>
                <div class="bg-red-500/20 rounded-full p-3">
                    <i class="fas fa-arrow-down text-2xl text-red-400"></i>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Прибыль</p>
                    <p class="text-3xl font-bold mt-2"
                       :class="(stats.summary?.profit || 0) >= 0 ? 'text-green-400' : 'text-red-400'"
                       x-text="formatMoney(stats.summary?.profit || 0)"></p>
                </div>
                <div class="bg-blue-500/20 rounded-full p-3">
                    <i class="fas fa-wallet text-2xl text-blue-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Графики -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- График по категориям -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-white mb-4">По категориям</h3>
            <canvas id="categoryChart"></canvas>
        </div>

        <!-- График по предметам -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-white mb-4">Доход по предметам</h3>
            <canvas id="subjectChart"></canvas>
        </div>

        <!-- График динамики -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-6 lg:col-span-2">
            <h3 class="text-lg font-bold text-white mb-4">Динамика доходов и расходов</h3>
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Таблица транзакций -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-bold text-white">Транзакции</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Категория</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Описание</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Сумма</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <template x-for="transaction in transactions" :key="transaction.id">
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200" x-text="formatDate(transaction.transaction_date)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="transaction.type === 'income' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                      x-text="transaction.type === 'income' ? 'Доход' : 'Расход'">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200" x-text="transaction.category"></td>
                            <td class="px-6 py-4 text-sm text-gray-200">
                                <div x-text="transaction.description || '-'"></div>
                                <div class="text-xs text-gray-400 mt-1">
                                    <span x-show="transaction.student_name" x-text="'Ученик: ' + transaction.student_name"></span>
                                    <span x-show="transaction.teacher_name" x-text="'Преподаватель: ' + transaction.teacher_name"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :class="transaction.type === 'income' ? 'text-green-400' : 'text-red-400'"
                                x-text="(transaction.type === 'income' ? '+' : '-') + formatMoney(transaction.amount)">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="deleteTransactionConfirm(transaction.id)" class="text-red-400 hover:text-red-300">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Модальное окно -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">Добавить транзакцию</h3>
                    <button @click="showModal = false" class="text-gray-500 hover:text-gray-400">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="saveTransaction">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Тип *</label>
                            <select x-model="form.type" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="income">Доход</option>
                                <option value="expense">Расход</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Категория *</label>
                            <select x-model="form.category" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <template x-if="form.type === 'income'">
                                    <optgroup label="Доходы">
                                        <option value="Оплата урока">Оплата урока</option>
                                        <option value="Абонемент">Абонемент</option>
                                        <option value="Другое">Другое</option>
                                    </optgroup>
                                </template>
                                <template x-if="form.type === 'expense'">
                                    <optgroup label="Расходы">
                                        <option value="Зарплата">Зарплата</option>
                                        <option value="Аренда">Аренда</option>
                                        <option value="Коммунальные услуги">Коммунальные услуги</option>
                                        <option value="Материалы">Материалы</option>
                                        <option value="Реклама">Реклама</option>
                                        <option value="Другое">Другое</option>
                                    </optgroup>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Сумма (₽) *</label>
                            <input type="number" x-model="form.amount" step="0.01" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Дата *</label>
                            <input type="date" x-model="form.transaction_date" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div x-show="form.type === 'income'">
                            <label class="block text-sm font-medium text-gray-200 mb-2">Ученик</label>
                            <select x-model="form.student_id" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Не указан</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>"><?= e($student['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div x-show="form.type === 'expense' && form.category === 'Зарплата'">
                            <label class="block text-sm font-medium text-gray-200 mb-2">Преподаватель</label>
                            <select x-model="form.teacher_id" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Не указан</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= e($teacher['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Описание</label>
                            <textarea x-model="form.description" rows="2" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-200 hover:bg-gray-600">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Добавить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function financeApp() {
    return {
        transactions: [],
        stats: {},
        filters: {
            month: new Date().getMonth() + 1 < 10 ? '0' + (new Date().getMonth() + 1) : '' + (new Date().getMonth() + 1),
            year: new Date().getFullYear().toString()
        },
        showModal: false,
        form: {
            type: 'income',
            category: '',
            amount: '',
            transaction_date: new Date().toISOString().split('T')[0],
            student_id: '',
            teacher_id: '',
            description: ''
        },
        charts: {},

        init() {
            this.loadData();
        },

        async loadData() {
            await this.loadTransactions();
            await this.loadStats();
            this.renderCharts();
        },

        async loadTransactions() {
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/finance.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    this.transactions = data.data;
                }
            } catch (error) {
                console.error('Error loading transactions:', error);
            }
        },

        async loadStats() {
            try {
                const params = new URLSearchParams({...this.filters, stats: '1'});
                const response = await fetch(`/api/finance.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        renderCharts() {
            this.renderCategoryChart();
            this.renderSubjectChart();
            this.renderTrendChart();
        },

        renderCategoryChart() {
            const ctx = document.getElementById('categoryChart');
            if (this.charts.category) this.charts.category.destroy();

            const categories = this.stats.byCategory || [];
            const labels = categories.map(c => c.category);
            const data = categories.map(c => c.total);
            const colors = categories.map(c => c.type === 'income' ? '#10B981' : '#EF4444');

            this.charts.category = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Сумма',
                        data: data,
                        backgroundColor: colors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {display: false}
                    }
                }
            });
        },

        renderSubjectChart() {
            const ctx = document.getElementById('subjectChart');
            if (this.charts.subject) this.charts.subject.destroy();

            const subjects = this.stats.bySubject || [];
            const labels = subjects.map(s => s.subject);
            const data = subjects.map(s => s.total);

            this.charts.subject = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
        },

        renderTrendChart() {
            const ctx = document.getElementById('trendChart');
            if (this.charts.trend) this.charts.trend.destroy();

            const trend = this.stats.monthlyTrend || [];
            const labels = trend.map(t => t.month);
            const income = trend.map(t => t.income);
            const expense = trend.map(t => t.expense);

            this.charts.trend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Доход',
                            data: income,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Расход',
                            data: expense,
                            borderColor: '#EF4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
        },

        resetForm() {
            this.form = {
                type: 'income',
                category: '',
                amount: '',
                transaction_date: new Date().toISOString().split('T')[0],
                student_id: '',
                teacher_id: '',
                description: ''
            };
        },

        async saveTransaction() {
            try {
                console.log('Sending transaction data:', this.form);

                const response = await fetch('/api/finance.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.form)
                });

                console.log('Response status:', response.status);

                const data = await response.json();
                console.log('Response data:', data);

                if (data.success) {
                    showNotification(data.message, 'success');
                    this.showModal = false;
                    this.loadData();
                } else {
                    showNotification(data.error || 'Ошибка сохранения', 'error');
                    console.error('Server error:', data.error);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showNotification('Ошибка сохранения: ' + error.message, 'error');
            }
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 0
            }).format(amount);
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('ru-RU');
        }
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.showModal = false;
                    this.loadData();
                } else {
                    showNotification(data.error || 'Ошибка сохранения', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка сохранения', 'error');
            }
        },

        async deleteTransactionConfirm(id) {
            if (!confirmAction('Вы уверены, что хотите удалить транзакцию?')) return;

            try {
                const response = await fetch('/api/finance.php', {