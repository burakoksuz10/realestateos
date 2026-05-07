@extends('layouts.admin')

@section('title', 'Görev Takvimi')

@section('content')
<div class="space-y-6" x-data="taskCalendar()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Görev Takvimi</h1>
            <p class="text-dark-400 mt-1" x-text="monthName + ' ' + currentYear"></p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="prevMonth()" class="p-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <button @click="goToday()" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white text-sm rounded-xl transition-colors">Bugün</button>
            <button @click="nextMonth()" class="p-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
            <a href="{{ route('admin.tasks.create') }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Yeni Görev
            </a>
        </div>
    </div>

    <div class="bg-dark-900 border border-dark-700/50 rounded-2xl overflow-hidden">
        <!-- Days header -->
        <div class="grid grid-cols-7 border-b border-dark-700/50">
            <template x-for="day in ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz']">
                <div class="py-3 text-center text-xs font-semibold text-dark-400 uppercase tracking-wider" x-text="day"></div>
            </template>
        </div>

        <!-- Calendar grid -->
        <div class="grid grid-cols-7">
            <template x-for="(day, index) in calendarDays" :key="index">
                <div class="min-h-24 p-2 border-b border-r border-dark-700/30 last:border-r-0"
                    :class="{ 'bg-dark-800/30': !day.currentMonth, 'bg-primary-500/5': day.isToday }">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium"
                            :class="day.isToday ? 'w-6 h-6 bg-primary-600 text-white rounded-full flex items-center justify-center text-xs' : (day.currentMonth ? 'text-white' : 'text-dark-600')"
                            x-text="day.date"></span>
                    </div>
                    <template x-for="task in day.tasks" :key="task.id">
                        <a :href="task.url" class="block mb-1 px-2 py-0.5 rounded text-xs truncate"
                            :class="task.priority === 'urgent' ? 'bg-red-500/20 text-red-400' : (task.priority === 'high' ? 'bg-orange-500/20 text-orange-400' : 'bg-primary-500/20 text-primary-400')"
                            x-text="task.title"></a>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function taskCalendar() {
    const tasks = @json($tasks ?? []);
    return {
        currentDate: new Date(),
        get currentYear() { return this.currentDate.getFullYear(); },
        get currentMonth() { return this.currentDate.getMonth(); },
        get monthName() {
            return ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'][this.currentMonth];
        },
        get calendarDays() {
            const year = this.currentYear, month = this.currentMonth;
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            let startDay = firstDay.getDay() - 1;
            if (startDay < 0) startDay = 6;
            const days = [];
            for (let i = startDay - 1; i >= 0; i--) {
                const d = new Date(year, month, -i);
                days.push({ date: d.getDate(), currentMonth: false, isToday: false, tasks: [] });
            }
            const today = new Date();
            for (let d = 1; d <= lastDay.getDate(); d++) {
                const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const dayTasks = tasks.filter(t => t.due_date && t.due_date.startsWith(dateStr));
                days.push({
                    date: d,
                    currentMonth: true,
                    isToday: today.getFullYear() === year && today.getMonth() === month && today.getDate() === d,
                    tasks: dayTasks
                });
            }
            const remaining = 42 - days.length;
            for (let d = 1; d <= remaining; d++) {
                days.push({ date: d, currentMonth: false, isToday: false, tasks: [] });
            }
            return days;
        },
        prevMonth() { this.currentDate = new Date(this.currentYear, this.currentMonth - 1, 1); },
        nextMonth() { this.currentDate = new Date(this.currentYear, this.currentMonth + 1, 1); },
        goToday() { this.currentDate = new Date(); },
    };
}
</script>
@endpush
@endsection
