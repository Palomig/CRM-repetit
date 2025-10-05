import React, { useState, useEffect } from 'react';
import { LayoutDashboard, Users, GraduationCap, Calendar, DollarSign, CheckSquare, Menu, X, Plus, Search, Phone, MessageCircle } from 'lucide-react';

// API Configuration
const API_URL = 'https://cw95865.tmweb.ru/api';  // Измените после деплоя

// Mock Data для демонстрации
const mockDashboardData = {
  active_students: 42,
  total_groups: 8,
  total_teachers: 6,
  pending_tasks: 12,
  monthly_income: 185000,
  monthly_expenses: 78000,
  monthly_profit: 107000,
  lessons_this_week: 34,
  groups_stats: [
    { id: 1, name: 'Математика 9А', subject: 'Математика', students_count: 5, max_students: 6, teacher: 'Иванова М.И.', room: 'Кабинет 1' },
    { id: 2, name: 'Физика 11Б', subject: 'Физика', students_count: 4, max_students: 6, teacher: 'Петров А.С.', room: 'Кабинет 2' }
  ],
  subjects_stats: [
    { subject: 'Математика', students: 18, income: 72000 },
    { subject: 'Физика', students: 12, income: 48000 },
    { subject: 'Русский язык', students: 8, income: 32000 }
  ],
  grades_stats: [
    { grade: 9, students: 15, income: 60000 },
    { grade: 10, students: 12, income: 48000 },
    { grade: 11, students: 15, income: 60000 }
  ]
};

const mockStudents = [
  { id: 1, name: 'Алексей Смирнов', grade: 9, subject: 'Математика', learning_type: 'group', status: 'active', parent_name: 'Смирнова Е.В.', parent_phone: '+7 912 345-67-89', teacher_name: 'Иванова М.И.', group_name: 'Математика 9А', price_per_lesson: 1500, last_lesson_date: '2025-10-03' },
  { id: 2, name: 'Мария Петрова', grade: 11, subject: 'Физика', learning_type: 'individual', status: 'active', parent_name: 'Петрова А.И.', parent_phone: '+7 923 456-78-90', teacher_name: 'Петров А.С.', group_name: null, price_per_lesson: 2000, last_lesson_date: '2025-10-04' },
  { id: 3, name: 'Иван Козлов', grade: 10, subject: 'Русский язык', learning_type: 'group', status: 'active', parent_name: 'Козлов В.П.', parent_phone: '+7 934 567-89-01', teacher_name: 'Сидорова О.Л.', group_name: 'Русский 10А', price_per_lesson: 1200, last_lesson_date: '2025-10-02' }
];

const mockTasks = [
  { id: 1, title: 'Прозвонить родителей Смирнова', student_name: 'Алексей Смирнов', due_date: '2025-10-06', status: 'pending', priority: 'high' },
  { id: 2, title: 'Подготовить материалы к уроку', teacher_name: 'Иванова М.И.', due_date: '2025-10-07', status: 'pending', priority: 'medium' },
  { id: 3, title: 'Обсудить успеваемость с родителями', student_name: 'Мария Петрова', due_date: '2025-10-05', status: 'pending', priority: 'high' }
];

const mockLessons = [
  { id: 1, datetime: '2025-10-07T10:00:00', student_name: 'Алексей Смирнов', teacher_name: 'Иванова М.И.', room_name: 'Кабинет 1', lesson_type: 'group', status: 'scheduled', duration: 60 },
  { id: 2, datetime: '2025-10-07T11:30:00', student_name: 'Мария Петрова', teacher_name: 'Петров А.С.', room_name: 'Кабинет 2', lesson_type: 'individual', status: 'scheduled', duration: 60 },
  { id: 3, datetime: '2025-10-07T14:00:00', group_name: 'Русский 10А', teacher_name: 'Сидорова О.Л.', room_name: 'Кабинет 1', lesson_type: 'group', status: 'scheduled', duration: 90 }
];

const App = () => {
  const [currentView, setCurrentView] = useState('dashboard');
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);
  const [dashboardData, setDashboardData] = useState(mockDashboardData);
  const [students, setStudents] = useState(mockStudents);
  const [tasks, setTasks] = useState(mockTasks);
  const [lessons, setLessons] = useState(mockLessons);
  const [searchQuery, setSearchQuery] = useState('');

  const navigation = [
    { id: 'dashboard', name: 'Dashboard', icon: LayoutDashboard },
    { id: 'students', name: 'Ученики', icon: Users },
    { id: 'teachers', name: 'Преподаватели', icon: GraduationCap },
    { id: 'schedule', name: 'Расписание', icon: Calendar },
    { id: 'finance', name: 'Финансы', icon: DollarSign },
    { id: 'tasks', name: 'Задачи', icon: CheckSquare }
  ];

  const StatCard = ({ title, value, subtitle, trend }) => (
    <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
      <div className="text-gray-400 text-sm font-medium mb-2">{title}</div>
      <div className="text-3xl font-bold text-white mb-1">{value}</div>
      {subtitle && <div className="text-gray-500 text-sm">{subtitle}</div>}
      {trend && (
        <div className={`text-sm mt-2 ${trend > 0 ? 'text-green-400' : 'text-red-400'}`}>
          {trend > 0 ? '↑' : '↓'} {Math.abs(trend)}%
        </div>
      )}
    </div>
  );

  const DashboardView = () => (
    <div className="space-y-6">
      <h1 className="text-3xl font-bold text-white">Dashboard</h1>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard title="Активных учеников" value={dashboardData.active_students} />
        <StatCard title="Групп" value={dashboardData.total_groups} />
        <StatCard title="Преподавателей" value={dashboardData.total_teachers} />
        <StatCard title="Задач в работе" value={dashboardData.pending_tasks} />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <StatCard 
          title="Доход за месяц" 
          value={`${dashboardData.monthly_income.toLocaleString()} ₽`} 
          subtitle="Октябрь 2025"
        />
        <StatCard 
          title="Расходы за месяц" 
          value={`${dashboardData.monthly_expenses.toLocaleString()} ₽`}
          subtitle="Зарплаты и прочее"
        />
        <StatCard 
          title="Прибыль" 
          value={`${dashboardData.monthly_profit.toLocaleString()} ₽`}
          subtitle="Чистая прибыль"
          trend={12}
        />
      </div>

      <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-xl font-bold text-white">Группы</h2>
          <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <Plus size={18} />
            Создать группу
          </button>
        </div>
        <div className="space-y-3">
          {dashboardData.groups_stats.map(group => (
            <div key={group.id} className="bg-gray-900 rounded-lg p-4 flex justify-between items-center">
              <div>
                <div className="text-white font-medium">{group.name}</div>
                <div className="text-gray-400 text-sm">{group.teacher} • {group.room}</div>
              </div>
              <div className="flex items-center gap-4">
                <div className="text-gray-300">
                  <span className="text-white font-medium">{group.students_count}</span>
                  <span className="text-gray-500">/{group.max_students}</span>
                </div>
                <div className={`px-3 py-1 rounded-full text-sm ${
                  group.students_count === group.max_students 
                    ? 'bg-red-900 text-red-200' 
                    : 'bg-green-900 text-green-200'
                }`}>
                  {group.students_count === group.max_students ? 'Полная' : 'Есть места'}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
          <h2 className="text-xl font-bold text-white mb-4">По предметам</h2>
          <div className="space-y-3">
            {dashboardData.subjects_stats.map((stat, idx) => (
              <div key={idx} className="flex justify-between items-center">
                <div>
                  <div className="text-white">{stat.subject}</div>
                  <div className="text-gray-400 text-sm">{stat.students} учеников</div>
                </div>
                <div className="text-green-400 font-medium">{stat.income.toLocaleString()} ₽</div>
              </div>
            ))}
          </div>
        </div>

        <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
          <h2 className="text-xl font-bold text-white mb-4">По классам</h2>
          <div className="space-y-3">
            {dashboardData.grades_stats.map((stat, idx) => (
              <div key={idx} className="flex justify-between items-center">
                <div>
                  <div className="text-white">{stat.grade} класс</div>
                  <div className="text-gray-400 text-sm">{stat.students} учеников</div>
                </div>
                <div className="text-green-400 font-medium">{stat.income.toLocaleString()} ₽</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );

  const StudentsView = () => {
    const filteredStudents = students.filter(s => 
      s.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      s.parent_name.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold text-white">Ученики</h1>
          <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <Plus size={18} />
            Добавить ученика
          </button>
        </div>

        <div className="relative">
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
          <input
            type="text"
            placeholder="Поиск по имени ученика или родителя..."
            className="w-full bg-gray-800 text-white pl-10 pr-4 py-3 rounded-lg border border-gray-700 focus:border-blue-500 focus:outline-none"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>

        <div className="grid grid-cols-1 gap-4">
          {filteredStudents.map(student => (
            <div key={student.id} className="bg-gray-800 rounded-lg p-6 border border-gray-700 hover:border-gray-600 transition-colors">
              <div className="flex justify-between items-start">
                <div className="flex-1">
                  <div className="flex items-center gap-3 mb-3">
                    <h3 className="text-xl font-bold text-white">{student.name}</h3>
                    <span className="px-3 py-1 bg-blue-900 text-blue-200 text-sm rounded-full">
                      {student.grade} класс
                    </span>
                    <span className={`px-3 py-1 text-sm rounded-full ${
                      student.learning_type === 'individual' 
                        ? 'bg-purple-900 text-purple-200' 
                        : 'bg-green-900 text-green-200'
                    }`}>
                      {student.learning_type === 'individual' ? 'Индивид.' : 'Группа'}
                    </span>
                  </div>
                  
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                      <div className="text-gray-400">Предмет:</div>
                      <div className="text-white font-medium">{student.subject}</div>
                    </div>
                    <div>
                      <div className="text-gray-400">Преподаватель:</div>
                      <div className="text-white font-medium">{student.teacher_name}</div>
                    </div>
                    {student.group_name && (
                      <div>
                        <div className="text-gray-400">Группа:</div>
                        <div className="text-white font-medium">{student.group_name}</div>
                      </div>
                    )}
                    <div>
                      <div className="text-gray-400">Стоимость урока:</div>
                      <div className="text-green-400 font-medium">{student.price_per_lesson} ₽</div>
                    </div>
                  </div>

                  <div className="mt-4 pt-4 border-t border-gray-700">
                    <div className="text-gray-400 text-sm mb-2">Родитель:</div>
                    <div className="flex items-center gap-4">
                      <span className="text-white font-medium">{student.parent_name}</span>
                      <span className="text-gray-400">{student.parent_phone}</span>
                      <div className="flex gap-2">
                        <button className="text-green-400 hover:text-green-300 transition-colors">
                          <Phone size={18} />
                        </button>
                        <button className="text-blue-400 hover:text-blue-300 transition-colors">
                          <MessageCircle size={18} />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <button className="text-gray-400 hover:text-white transition-colors">
                  <Menu size={20} />
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  };

  const ScheduleView = () => {
    return (
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold text-white">Расписание</h1>
          <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <Plus size={18} />
            Добавить урок
          </button>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 className="text-xl font-bold text-white mb-4">Кабинет 1</h2>
            <div className="space-y-3">
              {lessons.filter(l => l.room_name === 'Кабинет 1').map(lesson => {
                const date = new Date(lesson.datetime);
                return (
                  <div key={lesson.id} className="bg-gray-900 rounded-lg p-4">
                    <div className="flex justify-between items-start mb-2">
                      <div className="text-white font-medium">
                        {date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })}
                      </div>
                      <span className={`px-2 py-1 text-xs rounded-full ${
                        lesson.lesson_type === 'individual'
                          ? 'bg-purple-900 text-purple-200'
                          : 'bg-green-900 text-green-200'
                      }`}>
                        {lesson.lesson_type === 'individual' ? 'Индивид.' : 'Группа'}
                      </span>
                    </div>
                    <div className="text-white">{lesson.student_name || lesson.group_name}</div>
                    <div className="text-gray-400 text-sm mt-1">{lesson.teacher_name}</div>
                    <div className="text-gray-500 text-xs mt-1">{lesson.duration} мин</div>
                  </div>
                );
              })}
            </div>
          </div>

          <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 className="text-xl font-bold text-white mb-4">Кабинет 2</h2>
            <div className="space-y-3">
              {lessons.filter(l => l.room_name === 'Кабинет 2').map(lesson => {
                const date = new Date(lesson.datetime);
                return (
                  <div key={lesson.id} className="bg-gray-900 rounded-lg p-4">
                    <div className="flex justify-between items-start mb-2">
                      <div className="text-white font-medium">
                        {date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })}
                      </div>
                      <span className={`px-2 py-1 text-xs rounded-full ${
                        lesson.lesson_type === 'individual'
                          ? 'bg-purple-900 text-purple-200'
                          : 'bg-green-900 text-green-200'
                      }`}>
                        {lesson.lesson_type === 'individual' ? 'Индивид.' : 'Группа'}
                      </span>
                    </div>
                    <div className="text-white">{lesson.student_name || lesson.group_name}</div>
                    <div className="text-gray-400 text-sm mt-1">{lesson.teacher_name}</div>
                    <div className="text-gray-500 text-xs mt-1">{lesson.duration} мин</div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    );
  };

  const TasksView = () => {
    const handleCompleteTask = (taskId) => {
      setTasks(tasks.map(t => 
        t.id === taskId ? { ...t, status: 'completed' } : t
      ));
    };

    const pendingTasks = tasks.filter(t => t.status === 'pending');
    const completedTasks = tasks.filter(t => t.status === 'completed');

    return (
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold text-white">Задачи</h1>
          <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <Plus size={18} />
            Новая задача
          </button>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 className="text-xl font-bold text-white mb-4">В работе ({pendingTasks.length})</h2>
            <div className="space-y-3">
              {pendingTasks.map(task => {
                const isOverdue = new Date(task.due_date) < new Date();
                return (
                  <div key={task.id} className="bg-gray-900 rounded-lg p-4">
                    <div className="flex items-start gap-3">
                      <input
                        type="checkbox"
                        onChange={() => handleCompleteTask(task.id)}
                        className="mt-1 w-5 h-5 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500"
                      />
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-2">
                          <div className="text-white font-medium">{task.title}</div>
                          <span className={`px-2 py-1 text-xs rounded-full ${
                            task.priority === 'high' 
                              ? 'bg-red-900 text-red-200'
                              : task.priority === 'medium'
                              ? 'bg-yellow-900 text-yellow-200'
                              : 'bg-gray-700 text-gray-300'
                          }`}>
                            {task.priority === 'high' ? 'Высокий' : task.priority === 'medium' ? 'Средний' : 'Низкий'}
                          </span>
                        </div>
                        <div className="text-gray-400 text-sm">
                          {task.student_name || task.teacher_name}
                        </div>
                        <div className={`text-sm mt-1 ${isOverdue ? 'text-red-400' : 'text-gray-500'}`}>
                          Срок: {new Date(task.due_date).toLocaleDateString('ru-RU')}
                          {isOverdue && ' (Просрочено)'}
                        </div>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 className="text-xl font-bold text-white mb-4">Выполнено ({completedTasks.length})</h2>
            <div className="space-y-3">
              {completedTasks.map(task => (
                <div key={task.id} className="bg-gray-900 rounded-lg p-4 opacity-60">
                  <div className="flex items-start gap-3">
                    <input
                      type="checkbox"
                      checked
                      disabled
                      className="mt-1 w-5 h-5 rounded border-gray-600 bg-gray-700"
                    />
                    <div className="flex-1">
                      <div className="text-white font-medium line-through">{task.title}</div>
                      <div className="text-gray-400 text-sm">{task.student_name || task.teacher_name}</div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    );
  };

  const FinanceView = () => {
    return (
      <div className="space-y-6">
        <h1 className="text-3xl font-bold text-white">Финансы</h1>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <StatCard 
            title="Доход за месяц" 
            value={`${dashboardData.monthly_income.toLocaleString()} ₽`} 
            subtitle="Октябрь 2025"
            trend={8}
          />
          <StatCard 
            title="Расходы" 
            value={`${dashboardData.monthly_expenses.toLocaleString()} ₽`}
            subtitle="Зарплаты"
          />
          <StatCard 
            title="Чистая прибыль" 
            value={`${dashboardData.monthly_profit.toLocaleString()} ₽`}
            trend={12}
          />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 className="text-xl font-bold text-white mb-4">Доход по предметам</h2>
            <div className="space-y-4">
              {dashboardData.subjects_stats.map((stat, idx) => {
                const percentage = (stat.income / dashboardData.monthly_income) * 100;
                return (
                  <div key={idx}>
                    <div className="flex justify-between mb-2">
                      <span className="text-white">{stat.subject}</span>
                      <span className="text-green-400 font-medium">{stat.income.toLocaleString()} ₽</span>
                    </div>
                    <div className="w-full bg-gray-700 rounded-full h-2">
                      <div 
                        className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        style={{ width: `${percentage}%` }}
                      />
                    </div>
                    <div className="text-gray-400 text-sm mt-1">{stat.students} учеников • {percentage.toFixed(1)}%</div>
                  </div>
                );
              })}
            </div>
          </div>

          <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 className="text-xl font-bold text-white mb-4">Доход по классам</h2>
            <div className="space-y-4">
              {dashboardData.grades_stats.map((stat, idx) => {
                const percentage = (stat.income / dashboardData.monthly_income) * 100;
                return (
                  <div key={idx}>
                    <div className="flex justify-between mb-2">
                      <span className="text-white">{stat.grade} класс</span>
                      <span className="text-green-400 font-medium">{stat.income.toLocaleString()} ₽</span>
                    </div>
                    <div className="w-full bg-gray-700 rounded-full h-2">
                      <div 
                        className="bg-green-600 h-2 rounded-full transition-all duration-300"
                        style={{ width: `${percentage}%` }}
                      />
                    </div>
                    <div className="text-gray-400 text-sm mt-1">{stat.students} учеников • {percentage.toFixed(1)}%</div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>

        <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-xl font-bold text-white">Последние транзакции</h2>
            <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
              <Plus size={18} />
              Добавить
            </button>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-gray-700">
                  <th className="text-left py-3 px-4 text-gray-400 font-medium">Дата</th>
                  <th className="text-left py-3 px-4 text-gray-400 font-medium">Описание</th>
                  <th className="text-left py-3 px-4 text-gray-400 font-medium">Тип</th>
                  <th className="text-right py-3 px-4 text-gray-400 font-medium">Сумма</th>
                </tr>
              </thead>
              <tbody>
                <tr className="border-b border-gray-700 hover:bg-gray-700/50">
                  <td className="py-3 px-4 text-gray-300">05.10.2025</td>
                  <td className="py-3 px-4 text-white">Оплата за октябрь - Смирнов А.</td>
                  <td className="py-3 px-4">
                    <span className="px-2 py-1 bg-green-900 text-green-200 text-sm rounded-full">Доход</span>
                  </td>
                  <td className="py-3 px-4 text-right text-green-400 font-medium">+6,000 ₽</td>
                </tr>
                <tr className="border-b border-gray-700 hover:bg-gray-700/50">
                  <td className="py-3 px-4 text-gray-300">04.10.2025</td>
                  <td className="py-3 px-4 text-white">Зарплата - Иванова М.И.</td>
                  <td className="py-3 px-4">
                    <span className="px-2 py-1 bg-red-900 text-red-200 text-sm rounded-full">Расход</span>
                  </td>
                  <td className="py-3 px-4 text-right text-red-400 font-medium">-25,000 ₽</td>
                </tr>
                <tr className="border-b border-gray-700 hover:bg-gray-700/50">
                  <td className="py-3 px-4 text-gray-300">03.10.2025</td>
                  <td className="py-3 px-4 text-white">Оплата за октябрь - Петрова М.</td>
                  <td className="py-3 px-4">
                    <span className="px-2 py-1 bg-green-900 text-green-200 text-sm rounded-full">Доход</span>
                  </td>
                  <td className="py-3 px-4 text-right text-green-400 font-medium">+8,000 ₽</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    );
  };

  const TeachersView = () => {
    const mockTeachers = [
      { id: 1, name: 'Иванова Мария Ивановна', subjects: ['Математика', 'Алгебра'], students_count: 18, groups_count: 3, phone: '+7 912 111-22-33' },
      { id: 2, name: 'Петров Александр Сергеевич', subjects: ['Физика'], students_count: 12, groups_count: 2, phone: '+7 923 222-33-44' },
      { id: 3, name: 'Сидорова Ольга Леонидовна', subjects: ['Русский язык', 'Литература'], students_count: 15, groups_count: 2, phone: '+7 934 333-44-55' }
    ];

    return (
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold text-white">Преподаватели</h1>
          <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <Plus size={18} />
            Добавить преподавателя
          </button>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {mockTeachers.map(teacher => (
            <div key={teacher.id} className="bg-gray-800 rounded-lg p-6 border border-gray-700 hover:border-gray-600 transition-colors">
              <div className="flex items-start justify-between mb-4">
                <div>
                  <h3 className="text-lg font-bold text-white mb-2">{teacher.name}</h3>
                  <div className="flex flex-wrap gap-2">
                    {teacher.subjects.map((subject, idx) => (
                      <span key={idx} className="px-2 py-1 bg-blue-900 text-blue-200 text-xs rounded-full">
                        {subject}
                      </span>
                    ))}
                  </div>
                </div>
              </div>

              <div className="space-y-2 mb-4">
                <div className="flex justify-between text-sm">
                  <span className="text-gray-400">Учеников:</span>
                  <span className="text-white font-medium">{teacher.students_count}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-gray-400">Групп:</span>
                  <span className="text-white font-medium">{teacher.groups_count}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-gray-400">Телефон:</span>
                  <span className="text-white">{teacher.phone}</span>
                </div>
              </div>

              <button className="w-full bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
                Посмотреть расписание
              </button>
            </div>
          ))}
        </div>
      </div>
    );
  };

  const renderView = () => {
    switch (currentView) {
      case 'dashboard':
        return <DashboardView />;
      case 'students':
        return <StudentsView />;
      case 'teachers':
        return <TeachersView />;
      case 'schedule':
        return <ScheduleView />;
      case 'finance':
        return <FinanceView />;
      case 'tasks':
        return <TasksView />;
      default:
        return <DashboardView />;
    }
  };

  return (
    <div className="flex h-screen bg-gray-900">
      <div className={`${isSidebarOpen ? 'w-64' : 'w-20'} bg-gray-800 border-r border-gray-700 transition-all duration-300 flex flex-col`}>
        <div className="p-4 border-b border-gray-700 flex items-center justify-between">
          {isSidebarOpen && <h1 className="text-xl font-bold text-white">CRM Репетиторы</h1>}
          <button 
            onClick={() => setIsSidebarOpen(!isSidebarOpen)}
            className="text-gray-400 hover:text-white p-2"
          >
            {isSidebarOpen ? <X size={24} /> : <Menu size={24} />}
          </button>
        </div>

        <nav className="flex-1 p-4">
          {navigation.map(item => {
            const Icon = item.icon;
            return (
              <button
                key={item.id}
                onClick={() => setCurrentView(item.id)}
                className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-colors ${
                  currentView === item.id
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-400 hover:bg-gray-700 hover:text-white'
                }`}
              >
                <Icon size={20} />
                {isSidebarOpen && <span>{item.name}</span>}
              </button>
            );
          })}
        </nav>

        {isSidebarOpen && (
          <div className="p-4 border-t border-gray-700">
            <div className="text-gray-400 text-sm">
              <div className="font-medium text-white mb-1">Администратор</div>
              <div>admin@tutor.com</div>
            </div>
          </div>
        )}
      </div>

      <div className="flex-1 overflow-auto">
        <div className="p-8">
          {renderView()}
        </div>
      </div>
    </div>
  );
};

export default App;