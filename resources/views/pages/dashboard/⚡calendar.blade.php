<?php

use App\Models\StudentSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public int $currentYear;
    public int $currentMonth;
    public ?string $selectedDate = null;

    // Modal form fields
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $studentFirstName = '';
    public string $studentLastName = '';
    public string $subject = '';
    public string $note = '';
    public string $scheduledDate = '';
    public string $scheduledTime = '09:00';
    public string $color = 'coral';

    public static array $tutorColors = [
        'coral' => 'Marina',
        'purple' => 'Valentina',
    ];

    public function mount(): void
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
        $this->selectedDate = now()->toDateString();
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
    }

    public function goToToday(): void
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
        $this->selectedDate = now()->toDateString();
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
    }

    public function openCreateModal(?string $date = null): void
    {
        $this->resetForm();
        $this->scheduledDate = $date ?? $this->selectedDate ?? now()->toDateString();
        $this->showModal = true;
    }

    public function editSchedule(int $id): void
    {
        $schedule = StudentSchedule::findOrFail($id);
        $this->editingId = $id;
        $this->studentFirstName = $schedule->student_first_name;
        $this->studentLastName = $schedule->student_last_name;
        $this->subject = $schedule->subject ?? '';
        $this->note = $schedule->note ?? '';
        $this->scheduledDate = $schedule->scheduled_date->toDateString();
        $this->scheduledTime = $schedule->scheduled_time;
        $this->color = $schedule->color;
        $this->showModal = true;
    }

    public function togglePaid(int $id): void
    {
        $schedule = StudentSchedule::findOrFail($id);
        $schedule->update(['paid' => !$schedule->paid]);
        unset($this->monthSchedules, $this->selectedDaySchedules, $this->unpaidCount, $this->studentUnpaidSummary);
    }

    public function saveSchedule(): void
    {
        $validated = $this->validate([
            'studentFirstName' => 'required|string|max:255',
            'studentLastName' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
            'scheduledDate' => 'required|date',
            'scheduledTime' => 'required|string',
            'color' => 'required|in:coral,purple',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'student_first_name' => $this->studentFirstName,
            'student_last_name' => $this->studentLastName,
            'subject' => $this->subject ?: null,
            'note' => $this->note ?: null,
            'scheduled_date' => $this->scheduledDate,
            'scheduled_time' => $this->scheduledTime,
            'color' => $this->color,
        ];

        if ($this->editingId) {
            StudentSchedule::findOrFail($this->editingId)->update($data);
        } else {
            StudentSchedule::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        $this->selectedDate = $data['scheduled_date'];
        unset($this->monthSchedules, $this->selectedDaySchedules, $this->todayCount, $this->weekCount, $this->totalStudents, $this->marinaMonthlyHours, $this->valentinaMonthlyHours, $this->unpaidCount, $this->studentUnpaidSummary);
    }

    public function deleteSchedule(int $id): void
    {
        StudentSchedule::findOrFail($id)->delete();
        unset($this->monthSchedules, $this->selectedDaySchedules, $this->todayCount, $this->weekCount, $this->totalStudents, $this->marinaMonthlyHours, $this->valentinaMonthlyHours, $this->unpaidCount, $this->studentUnpaidSummary);
    }

    #[Computed]
    public function calendarDays(): array
    {
        $firstOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $lastOfMonth = $firstOfMonth->copy()->endOfMonth();

        // Monday = 1, Sunday = 7 (ISO)
        $startDay = $firstOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endDay = $lastOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $days = [];
        $current = $startDay->copy();
        while ($current <= $endDay) {
            $days[] = $current->copy();
            $current->addDay();
        }

        return $days;
    }

    #[Computed]
    public function monthSchedules(): \Illuminate\Support\Collection
    {
        $firstOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $startDay = $firstOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endDay = $firstOfMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        return StudentSchedule::with('user')
            ->whereBetween('scheduled_date', [$startDay->toDateString(), $endDay->toDateString()])
            ->orderBy('scheduled_time')
            ->get()
            ->groupBy(fn ($s) => $s->scheduled_date->toDateString());
    }

    #[Computed]
    public function selectedDaySchedules(): \Illuminate\Support\Collection
    {
        if (!$this->selectedDate) {
            return collect();
        }

        return $this->monthSchedules[$this->selectedDate] ?? collect();
    }

    #[Computed]
    public function todayCount(): int
    {
        return StudentSchedule::where('scheduled_date', now()->toDateString())->count();
    }

    #[Computed]
    public function weekCount(): int
    {
        return StudentSchedule::whereBetween('scheduled_date', [
            now()->startOfWeek(Carbon::MONDAY)->toDateString(),
            now()->endOfWeek(Carbon::SUNDAY)->toDateString(),
        ])->count();
    }

    #[Computed]
    public function totalStudents(): int
    {
        return StudentSchedule::query()
            ->selectRaw('DISTINCT LOWER(student_first_name), LOWER(student_last_name)')
            ->get()
            ->count();
    }

    #[Computed]
    public function marinaMonthlyHours(): int
    {
        return StudentSchedule::where('color', 'coral')
            ->whereMonth('scheduled_date', $this->currentMonth)
            ->whereYear('scheduled_date', $this->currentYear)
            ->count();
    }

    #[Computed]
    public function valentinaMonthlyHours(): int
    {
        return StudentSchedule::where('color', 'purple')
            ->whereMonth('scheduled_date', $this->currentMonth)
            ->whereYear('scheduled_date', $this->currentYear)
            ->count();
    }

    #[Computed]
    public function unpaidCount(): int
    {
        return StudentSchedule::where('paid', false)->count();
    }

    #[Computed]
    public function studentUnpaidSummary(): \Illuminate\Support\Collection
    {
        return StudentSchedule::where('paid', false)
            ->get()
            ->groupBy(fn ($s) => mb_strtolower($s->student_first_name) . ' ' . mb_strtolower($s->student_last_name))
            ->map(fn ($group) => [
                'name' => mb_convert_case($group->first()->student_first_name, MB_CASE_TITLE) . ' ' . mb_convert_case($group->first()->student_last_name, MB_CASE_TITLE),
                'count' => $group->count(),
            ])
            ->sortByDesc('count');
    }

    #[Computed]
    public function monthName(): string
    {
        $months = [
            1 => 'Siječanj', 2 => 'Veljača', 3 => 'Ožujak', 4 => 'Travanj',
            5 => 'Svibanj', 6 => 'Lipanj', 7 => 'Srpanj', 8 => 'Kolovoz',
            9 => 'Rujan', 10 => 'Listopad', 11 => 'Studeni', 12 => 'Prosinac',
        ];

        return $months[$this->currentMonth] . ' ' . $this->currentYear;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->studentFirstName = '';
        $this->studentLastName = '';
        $this->subject = '';
        $this->note = '';
        $this->scheduledDate = '';
        $this->scheduledTime = '09:00';
        $this->color = 'coral';
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-2">

        {{-- Stat Cards --}}
        <div class="grid gap-4 sm:grid-cols-3">
            {{-- Today's sessions --}}
            <div class="stat-card flex items-center gap-4 rounded-xl border border-libra-amber-100 bg-white p-5 shadow-sm">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-libra-amber-50">
                    <svg class="h-6 w-6 text-libra-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-libra-warm-text-secondary">Današnje sesije</p>
                    <p class="text-2xl font-bold text-libra-warm-text stat-number">{{ $this->todayCount }}</p>
                </div>
            </div>

            {{-- This week --}}
            <div class="stat-card flex items-center gap-4 rounded-xl border border-libra-coral-100 bg-white p-5 shadow-sm">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-libra-coral-50">
                    <svg class="h-6 w-6 text-libra-coral-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-libra-warm-text-secondary">Ovaj tjedan</p>
                    <p class="text-2xl font-bold text-libra-warm-text stat-number">{{ $this->weekCount }}</p>
                </div>
            </div>

            {{-- Unpaid sessions --}}
            <div class="stat-card flex items-center gap-4 rounded-xl border {{ $this->unpaidCount > 0 ? 'border-libra-red-400' : 'border-green-200' }} bg-white p-5 shadow-sm">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg {{ $this->unpaidCount > 0 ? 'bg-libra-red-50' : 'bg-green-50' }}">
                    @if ($this->unpaidCount > 0)
                        <svg class="h-6 w-6 text-libra-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    @else
                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium text-libra-warm-text-secondary">Neplaćene sesije</p>
                    <p class="text-2xl font-bold {{ $this->unpaidCount > 0 ? 'text-libra-red-500' : 'text-green-600' }} stat-number">{{ $this->unpaidCount }}</p>
                </div>
            </div>
        </div>

        {{-- Unpaid Student Summary --}}
        @if ($this->unpaidCount > 0)
            <div class="rounded-xl border border-libra-red-400 bg-libra-red-50 p-4 shadow-sm">
                <div class="mb-2 flex items-center gap-2">
                    <svg class="h-4 w-4 text-libra-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <span class="text-sm font-semibold text-libra-red-600">Neplaćene sesije po učeniku</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->studentUnpaidSummary as $student)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-libra-red-400 bg-white px-3 py-1 text-xs font-medium text-libra-red-600">
                            {{ $student['name'] }}
                            <span class="rounded-full bg-libra-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white leading-none">{{ $student['count'] }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Tutor Legend with Hours + PDF Download --}}
        <div class="flex flex-col gap-3 rounded-xl border border-libra-amber-100 bg-white px-5 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                <span class="text-sm font-medium text-libra-warm-text-secondary">Tutori:</span>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-libra-coral-400"></span>
                    <span class="text-sm font-semibold text-libra-warm-text">Marina</span>
                    <span class="rounded-full bg-libra-coral-100 px-2 py-0.5 text-xs font-semibold text-black">{{ $this->marinaMonthlyHours }} sati</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-libra-purple-400"></span>
                    <span class="text-sm font-semibold text-libra-warm-text">Valentina</span>
                    <span class="rounded-full bg-libra-purple-100 px-2 py-0.5 text-xs font-semibold text-libra-purple-700">{{ $this->valentinaMonthlyHours }} sati</span>
                </div>
            </div>
            <a
                href="{{ route('reports.monthly-pdf', ['month' => $this->currentMonth, 'year' => $this->currentYear]) }}"
                class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-libra-amber-200 px-3 py-1.5 text-sm font-medium text-libra-amber-700 hover:bg-libra-amber-50 transition-colors sm:w-auto sm:justify-start"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                PDF izvještaj
            </a>
        </div>

        {{-- Calendar + Day Detail --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Calendar Grid --}}
            <div class="lg:col-span-2 rounded-xl border border-libra-amber-100 bg-white p-4 shadow-sm">
                {{-- Month Navigation --}}
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button wire:click="previousMonth" class="rounded-lg p-2 text-libra-warm-text-secondary hover:bg-libra-amber-50 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <h2 class="text-lg font-semibold text-libra-warm-text min-w-[180px] text-center">{{ $this->monthName }}</h2>
                        <button wire:click="nextMonth" class="rounded-lg p-2 text-libra-warm-text-secondary hover:bg-libra-amber-50 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                    <button wire:click="goToToday" class="rounded-lg border border-libra-amber-200 px-3 py-1.5 text-sm font-medium text-libra-amber-700 hover:bg-libra-amber-50 transition-colors">
                        Danas
                    </button>
                </div>

                {{-- Day Headers --}}
                <div class="grid grid-cols-7 mb-1">
                    @foreach (['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'] as $day)
                        <div class="py-2 text-center text-xs font-semibold uppercase tracking-wide text-libra-warm-text-secondary">
                            {{ $day }}
                        </div>
                    @endforeach
                </div>

                {{-- Calendar Days Grid --}}
                <div class="grid grid-cols-7 gap-px rounded-lg bg-libra-amber-100/50 overflow-hidden border border-libra-amber-100/50">
                    @foreach ($this->calendarDays as $day)
                        @php
                            $dateStr = $day->toDateString();
                            $isToday = $day->isToday();
                            $isSelected = $dateStr === $this->selectedDate;
                            $isCurrentMonth = $day->month === $this->currentMonth;
                            $daySchedules = $this->monthSchedules[$dateStr] ?? collect();
                        @endphp
                        <button
                            wire:key="day-{{ $dateStr }}"
                            wire:click="selectDate('{{ $dateStr }}')"
                            class="calendar-day relative flex min-h-[80px] flex-col bg-white p-1.5 text-left transition-colors hover:bg-libra-amber-50/50
                                {{ $isToday ? 'calendar-day--today' : '' }}
                                {{ $isSelected ? 'calendar-day--selected' : '' }}
                                {{ !$isCurrentMonth ? 'calendar-day--outside' : '' }}"
                        >
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold
                                {{ $isToday ? 'bg-libra-amber-500 text-white' : '' }}
                                {{ $isSelected && !$isToday ? 'bg-libra-amber-100 text-libra-amber-700' : '' }}
                                {{ !$isCurrentMonth ? 'text-zinc-300' : 'text-libra-warm-text' }}">
                                {{ $day->day }}
                            </span>

                            @if ($daySchedules->count() > 0)
                                <div class="mt-1 flex flex-col gap-0.5 overflow-hidden">
                                    @foreach ($daySchedules->take(2) as $schedule)
                                        <span wire:key="pill-{{ $schedule->id }}" class="schedule-pill schedule-pill--{{ $schedule->color }} {{ !$schedule->paid ? 'schedule-pill--unpaid' : '' }} truncate rounded px-1 py-0.5 text-[11px] font-medium leading-tight">
                                            {{ !$schedule->paid ? '! ' : '' }}{{ $schedule->studentFullName() }}
                                        </span>
                                    @endforeach
                                    @if ($daySchedules->count() > 2)
                                        <span class="text-[10px] font-medium text-libra-warm-text-secondary px-1">
                                            +{{ $daySchedules->count() - 2 }} više
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Day Detail Panel --}}
            <div class="rounded-xl border border-libra-amber-100 bg-white p-4 shadow-sm">
                @if ($this->selectedDate)
                    @php
                        $selectedCarbon = \Carbon\Carbon::parse($this->selectedDate);
                        $croatianDays = ['Nedjelja', 'Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota'];
                        $dayName = $croatianDays[$selectedCarbon->dayOfWeek];
                    @endphp
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-libra-warm-text">{{ $dayName }}</h3>
                            <p class="text-sm text-libra-warm-text-secondary">{{ $selectedCarbon->format('d.m.Y.') }}</p>
                        </div>
                        <button
                            wire:click="openCreateModal('{{ $this->selectedDate }}')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-libra-amber-500 to-libra-coral-500 px-3 py-2 text-sm font-medium text-white shadow-sm hover:from-libra-amber-600 hover:to-libra-coral-600 transition-all"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Dodaj učenika
                        </button>
                    </div>

                    @if ($this->selectedDaySchedules->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-libra-amber-50">
                                <svg class="h-6 w-6 text-libra-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <p class="text-sm text-libra-warm-text-secondary">Nema zakazanih sesija</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($this->selectedDaySchedules as $schedule)
                                <div wire:key="detail-{{ $schedule->id }}" class="group relative rounded-lg border border-zinc-100 p-3 hover:border-libra-amber-200 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-3">
                                            <div class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full
                                                {{ $schedule->color === 'coral' ? 'bg-libra-coral-400' : 'bg-libra-purple-400' }}
                                            "></div>
                                            <div>
                                                <p class="text-sm font-semibold text-libra-warm-text">
                                                    {{ $schedule->studentFullName() }}
                                                </p>
                                                <p class="text-xs text-libra-warm-text-secondary">
                                                    {{ $schedule->scheduled_time }} &middot; {{ $schedule->color === 'coral' ? 'Marina' : 'Valentina' }}
                                                </p>
                                                @if ($schedule->note)
                                                    <p class="mt-1 text-xs text-libra-warm-text-secondary/80 line-clamp-2">
                                                        {{ $schedule->note }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button
                                                wire:click="togglePaid({{ $schedule->id }})"
                                                class="rounded-full px-2.5 py-1 text-[11px] font-semibold transition-colors
                                                    {{ $schedule->paid
                                                        ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                                        : 'bg-libra-red-50 text-libra-red-600 border border-libra-red-400 hover:bg-libra-red-50/80'
                                                    }}"
                                            >
                                                {{ $schedule->paid ? 'Plaćeno' : 'Neplaćeno' }}
                                            </button>
                                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button wire:click="editSchedule({{ $schedule->id }})" class="rounded p-1 text-libra-warm-text-secondary hover:bg-libra-amber-50 hover:text-libra-amber-600">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </button>
                                            <button wire:click="deleteSchedule({{ $schedule->id }})" wire:confirm="Jeste li sigurni da želite obrisati ovu sesiju?" class="rounded p-1 text-libra-warm-text-secondary hover:bg-libra-red-50 hover:text-libra-red-500">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <p class="text-sm text-libra-warm-text-secondary">Odaberite dan na kalendaru</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Schedule Modal --}}
        @if ($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
                <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl" @click.stop>
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-libra-warm-text">
                            {{ $editingId ? 'Uredi sesiju' : 'Zakaži učenika' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="rounded-lg p-1 text-libra-warm-text-secondary hover:bg-zinc-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="saveSchedule" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-libra-warm-text">Ime</label>
                                <input
                                    wire:model="studentFirstName"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text placeholder-zinc-400 focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                                    placeholder="Ime učenika"
                                />
                                @error('studentFirstName') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-libra-warm-text">Prezime</label>
                                <input
                                    wire:model="studentLastName"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text placeholder-zinc-400 focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                                    placeholder="Prezime učenika"
                                />
                                @error('studentLastName') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-libra-warm-text">Predmet</label>
                            <input
                                wire:model="subject"
                                type="text"
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text placeholder-zinc-400 focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                                placeholder="npr. Matematika, Engleski..."
                            />
                            @error('subject') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-libra-warm-text">Napomena</label>
                            <textarea
                                wire:model="note"
                                rows="3"
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text placeholder-zinc-400 focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                                placeholder="Što učenik treba? Ostali tutori će vidjeti ovu napomenu."
                            ></textarea>
                            @error('note') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-libra-warm-text">Datum</label>
                                <input
                                    wire:model="scheduledDate"
                                    type="date"
                                    required
                                    class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                                />
                                @error('scheduledDate') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-libra-warm-text">Vrijeme</label>
                                <select
                                    wire:model="scheduledTime"
                                    required
                                    class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                                >
                                    @for ($h = 8; $h <= 18; $h++)
                                        <option value="{{ sprintf('%02d:00', $h) }}">{{ sprintf('%02d:00', $h) }}</option>
                                        @if ($h < 18)
                                            <option value="{{ sprintf('%02d:30', $h) }}">{{ sprintf('%02d:30', $h) }}</option>
                                        @endif
                                    @endfor
                                </select>
                                @error('scheduledTime') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-libra-warm-text">Tutor</label>
                            <div class="flex gap-4">
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border-2 px-4 py-2.5 transition-all
                                    {{ $color === 'coral' ? 'border-libra-coral-400 bg-libra-coral-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                                    <input type="radio" wire:model.live="color" value="coral" class="sr-only" />
                                    <span class="h-3 w-3 rounded-full bg-libra-coral-400"></span>
                                    <span class="text-sm font-medium {{ $color === 'coral' ? 'text-libra-coral-600' : 'text-libra-warm-text-secondary' }}">Marina</span>
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border-2 px-4 py-2.5 transition-all
                                    {{ $color === 'purple' ? 'border-libra-purple-400 bg-libra-purple-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                                    <input type="radio" wire:model.live="color" value="purple" class="sr-only" />
                                    <span class="h-3 w-3 rounded-full bg-libra-purple-400"></span>
                                    <span class="text-sm font-medium {{ $color === 'purple' ? 'text-libra-purple-600' : 'text-libra-warm-text-secondary' }}">Valentina</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                wire:click="$set('showModal', false)"
                                class="rounded-lg border border-zinc-200 px-4 py-2 text-sm font-medium text-libra-warm-text-secondary hover:bg-zinc-50 transition-colors"
                            >
                                Odustani
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-gradient-to-r from-libra-amber-500 to-libra-coral-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:from-libra-amber-600 hover:to-libra-coral-600 transition-all"
                            >
                                Spremi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
</div>
