<?php
require_once 'includes/header.php';

// Получение статистики
$studentsStats = getStudentsStats();
$financeStats = getFinanceStats();
$tasks = getTasksForDashboard('pending', 5);

// Получение групп с заполненностью
$groups = db()->fetchAll("
    SELECT 
        g.*,
        t.name as teacher_name,
        r.name as room_name,
        (SELECT COUNT(*) FROM students WHERE group_id = g.id AND status = 'active') as current_students
    FROM `groups` g
    LEFT JOIN teachers t ON g.teacher_id = t.id
    LEFT JOIN rooms r ON g.room_id = r.id
    WHERE g.status = 'active'
    ORDER BY g.name
");

// Получение предстоящих уроков
$upcomingLessons = db()->fetchAll("
    SELECT 
        l.*,
        COALESCE(s.name, 'Группа') as student_name,
        g.name as group_name,
        t.name as teacher_name,
        r.name as room_name
    FROM lessons l
    LEFT JOIN students s ON l.student_id = s.id
    LEFT JOIN `groups` g ON l.group_id = g.id
    LEFT JOIN teachers t ON l.teacher_id = t.id
    LEFT JOIN rooms r ON l.room_id = r.id
    WHERE l.lesson_date >= CURDATE() 
    AND l.status = 'scheduled'
    ORDER BY l.lesson_date, l.lesson_time
    LIMIT 10
");
?>

<div x-data="dashboard()">
    <!-- Заголовок -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-white">Панель управления</h1>
        <p class="text-gray-400 mt-1">Обзор деятельности центра</p>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Активные ученики -->
        <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Активные ученики</p>
                    <p class="text-3xl font-bold text-white mt-2"><?= $studentsStats['active'] ?></p>
                    <p class="text-xs text-gray-500 mt-1">из <?= $studentsStats['total'] ?> всего</p>
                </div>
                <div class="bg-blue-500/20 rounded-full p-3">
                    <i class="fas fa-user-graduate text-2xl text-blue-400"></i>
                </div>
            </div>
        </div>

        <!-- Доход за месяц -->
        <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Доход за месяц</p>
                    <p class="text-3xl font-bold text-green-400 mt-2"><?= formatMoney($financeStats['income']) ?></p>
                    <p class="text-xs text-gray-500 mt-1">Прибыль: <?= formatMoney($financeStats['profit']) ?></p>
                </div>
                <div class="bg-green-500/20 rounded-full p-3">
                    <i class="fas fa-wallet text-2xl text-green-400"></i>
                </div>
            </div>
        </div>

        <!-- Группы -->
        <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Активные группы</p>
                    <p class="text-3xl font-bold text-white mt-2"><?= count($groups) ?></p>
                    <p class="text-xs text-gray-500 mt-1">Учеников в группах: <?= $studentsStats['group'] ?></p>
                </div>
                <div class="bg-purple-500/20 rounded-full p-3">
                    <i class="fas fa-users text-2xl text-purple-400"></i>
                </div>
            </div>
        </div>

        <!-- Задачи -->
        <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Задачи</p>
                    <p class="text-3xl font-bold text-orange-400 mt-2"><?= count($tasks) ?></p>
                    <p class="text-xs text-gray-500 mt-1">Требуют внимания</p>
                </div>
                <div class="bg-orange-500/20 rounded-full p-3">
                    <i class="fas fa-tasks text-2xl text-orange-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Два столбца -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Предстоящие уроки -->
        <div class="bg-gray-800 rounded-lg shadow border border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-calendar-check text-blue-400 mr-2"></i>
                        Предстоящие уроки
                    </h2>
                    <a href="/schedule.php" class="text-sm text-blue-400 hover:text-blue-300">
                        Все уроки <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-6">
                <?php if (empty($upcomingLessons)): ?>
                    <p class="text-gray-400 text-center py-8">Нет запланированных уроков</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($upcomingLessons as $lesson): ?>
                            <div class="border border-gray-700 rounded-lg p-4 hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <span class="font-medium text-white">
                                                <?= e($lesson['student_name']) ?>
                                                <?php if ($lesson['group_name']): ?>
                                                    (<?= e($lesson['group_name']) ?>)
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-400">
                                            <i class="fas fa-chalkboard-teacher mr-1"></i>
                                            <?= e($lesson['teacher_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-400">
                                            <i class="fas fa-door-open mr-1"></i>
                                            <?= e($lesson['room_name']) ?>
                                        </div>
                                    </div>
                                    <div class="text-right ml-4">
                                        <div class="text-sm font-medium text-white">
                                            <?= formatDate($lesson['lesson_date']) ?>
                                        </div>
                                        <div class="text-sm text-gray-400">
                                            <?= formatTime($lesson['lesson_time']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Группы -->
        <div class="bg-gray-800 rounded-lg shadow border border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-users text-purple-400 mr-2"></i>
                        Группы
                    </h2>
                    <a href="/groups.php" class="text-sm text-blue-400 hover:text-blue-300">
                        Все группы <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-6">
                <?php if (empty($groups)): ?>
                    <p class="text-gray-400 text-center py-8">Нет активных групп</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($groups as $group): ?>
                            <div class="border border-gray-700 rounded-lg p-4 hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-medium text-white"><?= e($group['name']) ?></h3>
                                    <span class="text-sm px-2 py-1 rounded-full <?= $group['current_students'] >= $group['max_students'] ? 'bg-red-500/20 text-red-400' : 'bg-green-500/20 text-green-400' ?>">
                                        <?= $group['current_students'] ?>/<?= $group['max_students'] ?>
                                    </span>
                                </div>
                                <div class="text-sm text-gray-400 space-y-1">
                                    <div>
                                        <i class="fas fa-book mr-1"></i>
                                        <?= e($group['subject']) ?>
                                    </div>
                                    <div>
                                        <i class="fas fa-chalkboard-teacher mr-1"></i>
                                        <?= e($group['teacher_name']) ?>
                                    </div>
                                    <div>
                                        <i class="fas fa-door-open mr-1"></i>
                                        <?= e($group['room_name']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Задачи -->
    <div class="bg-gray-800 rounded-lg shadow mb-6 border border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-tasks text-orange-400 mr-2"></i>
                    Активные задачи
                </h2>
                <a href="/tasks.php" class="text-sm text-blue-400 hover:text-blue-300">
                    Все задачи <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="p-6">
            <?php if (empty($tasks)): ?>
                <p class="text-gray-400 text-center py-8">Нет активных задач</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($tasks as $task): ?>
                        <div class="border border-gray-700 rounded-lg p-4 hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="font-medium text-white"><?= e($task['title']) ?></span>
                                        <span class="text-xs px-2 py-1 rounded-full <?= $task['priority'] === 'high' ? 'bg-red-500/20 text-red-400' : ($task['priority'] === 'medium' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-gray-600 text-gray-300') ?>">
                                            <?= $task['priority'] === 'high' ? 'Высокий' : ($task['priority'] === 'medium' ? 'Средний' : 'Низкий') ?>
                                        </span>
                                    </div>
                                    <?php if ($task['description']): ?>
                                        <p class="text-sm text-gray-400 mb-2"><?= e($task['description']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($task['student_name']): ?>
                                        <div class="text-sm text-gray-400">
                                            <i class="fas fa-user-graduate mr-1"></i>
                                            <?= e($task['student_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="text-sm font-medium text-white">
                                        <?= formatDate($task['due_date']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function dashboard() {
    return {
        init() {
            console.log('Dashboard initialized');
        }
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>