<?php

namespace App\Http\Controllers;

use App\Models\StudentSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function monthlyPdf(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $month = (int) $request->month;
        $year = (int) $request->year;

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $schedules = StudentSchedule::whereBetween('scheduled_date', [
            $startOfMonth->toDateString(),
            $endOfMonth->toDateString(),
        ])->orderBy('student_last_name')->orderBy('student_first_name')->get();

        $students = $schedules
            ->groupBy(fn ($s) => mb_strtolower($s->student_first_name) . '|' . mb_strtolower($s->student_last_name))
            ->map(fn ($group) => [
                'first_name' => mb_convert_case($group->first()->student_first_name, MB_CASE_TITLE),
                'last_name' => mb_convert_case($group->first()->student_last_name, MB_CASE_TITLE),
                'subjects' => $group->pluck('subject')->filter()->unique()->implode(', ') ?: '-',
                'tutors' => $group->map(fn ($s) => ['name' => $s->tutorName(), 'color' => $s->color])->unique('name')->values(),
                'hours' => $group->count(),
                'unpaid' => $group->where('paid', false)->count(),
            ])
            ->sortBy('last_name')
            ->values();

        $marinaHours = $schedules->where('color', 'coral')->count();
        $valentinaHours = $schedules->where('color', 'purple')->count();
        $totalHours = $marinaHours + $valentinaHours;
        $unpaidTotal = $schedules->where('paid', false)->count();

        $months = [
            1 => 'Siječanj', 2 => 'Veljača', 3 => 'Ožujak', 4 => 'Travanj',
            5 => 'Svibanj', 6 => 'Lipanj', 7 => 'Srpanj', 8 => 'Kolovoz',
            9 => 'Rujan', 10 => 'Listopad', 11 => 'Studeni', 12 => 'Prosinac',
        ];
        $monthName = $months[$month];

        $pdf = Pdf::loadView('reports.monthly', compact(
            'students', 'marinaHours', 'valentinaHours', 'totalHours',
            'unpaidTotal', 'monthName', 'month', 'year'
        ));

        return $pdf->download("libra-izvjestaj-{$monthName}-{$year}.pdf");
    }
}
