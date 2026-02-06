@props([
    'wire:model' => null,
    'placeholder' => 'Select date...',
])

<div 
    x-data="{
        open: false,
        value: @entangle($attributes->wire('model')),
        viewYear: new Date().getFullYear(),
        viewMonth: new Date().getMonth(),
        days: [],
        monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        
        init() {
            if (this.value) {
                let parts = this.value.split('-');
                this.viewYear = parseInt(parts[0]);
                this.viewMonth = parseInt(parts[1]) - 1;
            }
            this.generateCalendar();
        },

        generateCalendar() {
            const firstDay = new Date(this.viewYear, this.viewMonth, 1).getDay();
            const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
            
            this.days = [];
            // Padding for empty start days
            for (let i = 0; i < firstDay; i++) {
                this.days.push(null);
            }
            // Actual days
            for (let i = 1; i <= daysInMonth; i++) {
                this.days.push(i);
            }
        },

        selectDate(day) {
            let m = String(this.viewMonth + 1).padStart(2, '0');
            let d = String(day).padStart(2, '0');
            this.value = `${this.viewYear}-${m}-${d}`;
            this.open = false;
        },

        isSelected(day) {
            if (!this.value) return false;
            let m = String(this.viewMonth + 1).padStart(2, '0');
            let d = String(day).padStart(2, '0');
            return this.value === `${this.viewYear}-${m}-${d}`;
        },

        isToday(day) {
            let now = new Date();
            return now.getFullYear() === this.viewYear && 
                   now.getMonth() === this.viewMonth && 
                   now.getDate() === day;
        },

        prevMonth() {
            if (this.viewMonth === 0) {
                this.viewMonth = 11;
                this.viewYear--;
            } else {
                this.viewMonth--;
            }
            this.generateCalendar();
        },

        nextMonth() {
            if (this.viewMonth === 11) {
                this.viewMonth = 0;
                this.viewYear++;
            } else {
                this.viewMonth++;
            }
            this.generateCalendar();
        }
    }"
    class="relative w-full max-w-xs"
>
    <button type="button" @click="open = !open" 
        class="flex items-center justify-between w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-sm shadow-sm hover:border-zinc-400 dark:hover:border-zinc-500 transition focus:outline-none focus:ring-2 focus:ring-zinc-800 dark:focus:ring-zinc-100"
    >
        <div class="flex items-center gap-2 overflow-hidden">
            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span x-text="value ? value : '{{ $placeholder }}'" :class="value ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400'" class="font-medium"></span>
        </div>
        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-zinc-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>

    <div x-show="open" @click.away="open = false" x-transition.opacity
        class="absolute z-50 mt-2 p-3 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-xl w-64"
    >
        <div class="flex items-center justify-between mb-4">
            <button type="button" @click="prevMonth()" class="p-1 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition">
                <svg class="w-4 h-4 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="text-xs font-bold text-zinc-800 dark:text-zinc-100 uppercase tracking-widest" x-text="monthNames[viewMonth] + ' ' + viewYear"></div>
            <button type="button" @click="nextMonth()" class="p-1 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md transition">
                <svg class="w-4 h-4 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <div class="grid grid-cols-7 mb-2 text-[10px] font-bold text-zinc-400 uppercase text-center tracking-tighter">
            <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
        </div>

        <div class="grid grid-cols-7 gap-1">
            <template x-for="(day, index) in days" :key="index">
                <div class="aspect-square">
                    <button type="button" x-show="day" @click="selectDate(day)"
                        :class="{
                            'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-sm': isSelected(day),
                            'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700': !isSelected(day),
                            'ring-1 ring-inset ring-zinc-900 dark:ring-zinc-100': isToday(day) && !isSelected(day)
                        }"
                        class="w-full h-full flex items-center justify-center text-xs font-medium rounded-lg transition-all"
                        x-text="day"
                    ></button>
                </div>
            </template>
        </div>
    </div>
</div>