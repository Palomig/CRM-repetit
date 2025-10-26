<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(SITE_NAME) ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-900">
    <!-- Мобильное меню overlay -->
    <div x-data="{ mobileMenuOpen: false }">
        <div 
            x-show="mobileMenuOpen" 
            @click="mobileMenuOpen = false"
            x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
        ></div>

        <!-- Навигация -->
        <nav class="bg-gray-800 border-b border-gray-700 shadow-lg sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Мобильная кнопка меню -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="lg:hidden p-2 rounded-md text-gray-200 hover:bg-gray-700"
                        >
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        
                        <!-- Логотип -->
                        <a href="/" class="flex items-center ml-2 lg:ml-0">
                            <i class="fas fa-graduation-cap text-2xl text-blue-500"></i>
                            <span class="ml-2 text-xl font-bold text-white hidden sm:block">CRM Центр</span>
                        </a>
                    </div>

                    <!-- Десктопное меню -->
                    <div class="hidden lg:flex lg:items-center lg:space-x-1">
                        <a href="/" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'index' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-home mr-2"></i>Главная
                        </a>
                        <a href="/students.php" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'students' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-user-graduate mr-2"></i>Ученики
                        </a>
                        <a href="/teachers.php" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'teachers' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-chalkboard-teacher mr-2"></i>Преподаватели
                        </a>
                        <a href="/groups.php" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'groups' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-users mr-2"></i>Группы
                        </a>
                        <a href="/schedule.php" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'schedule' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-calendar-alt mr-2"></i>Расписание
                        </a>
                        <a href="/finance.php" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'finance' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-wallet mr-2"></i>Финансы
                        </a>
                        <a href="/tasks.php" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'tasks' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-tasks mr-2"></i>Задачи
                        </a>
                    </div>
                </div>
            </div>

            <!-- Мобильное меню -->
            <div
                x-show="mobileMenuOpen"
                x-cloak
                class="lg:hidden fixed inset-y-0 left-0 w-64 bg-gray-800 shadow-xl z-50 transform transition-transform duration-300"
            >
                <div class="p-4">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-graduation-cap text-2xl text-blue-500"></i>
                            <span class="ml-2 text-lg font-bold text-white">CRM Центр</span>
                        </div>
                        <button @click="mobileMenuOpen = false" class="p-2 rounded-md text-gray-200 hover:bg-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <nav class="space-y-1">
                        <a href="/" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors <?= $currentPage === 'index' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-home w-6"></i>
                            <span class="ml-3">Главная</span>
                        </a>
                        <a href="/students.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors <?= $currentPage === 'students' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-user-graduate w-6"></i>
                            <span class="ml-3">Ученики</span>
                        </a>
                        <a href="/teachers.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors <?= $currentPage === 'teachers' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-chalkboard-teacher w-6"></i>
                            <span class="ml-3">Преподаватели</span>
                        </a>
                        <a href="/groups.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors <?= $currentPage === 'groups' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-users w-6"></i>
                            <span class="ml-3">Группы</span>
                        </a>
                        <a href="/schedule.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors <?= $currentPage === 'schedule' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-calendar-alt w-6"></i>
                            <span class="ml-3">Расписание</span>
                        </a>
                        <a href="/finance.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors <?= $currentPage === 'finance' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-wallet w-6"></i>
                            <span class="ml-3">Финансы</span>
                        </a>
                        <a href="/tasks.php" class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors <?= $currentPage === 'tasks' ? 'bg-gray-700 text-blue-400' : 'text-gray-200 hover:bg-gray-700' ?>">
                            <i class="fas fa-tasks w-6"></i>
                            <span class="ml-3">Задачи</span>
                        </a>
                    </nav>
                </div>
            </div>
        </nav>
    </div>

    <!-- Основной контент -->
    <main class="<?= $currentPage === 'schedule' ? 'w-full px-2 sm:px-4 py-6' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6' ?>">