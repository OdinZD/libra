<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_first_name',
        'student_last_name',
        'subject',
        'note',
        'scheduled_date',
        'scheduled_time',
        'color',
        'paid',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'paid' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studentFullName(): string
    {
        return $this->student_first_name . ' ' . $this->student_last_name;
    }

    public function tutorName(): string
    {
        return match ($this->color) {
            'coral' => 'Marina',
            'purple' => 'Valentina',
            default => 'Marina',
        };
    }

    public function colorClasses(): string
    {
        return match ($this->color) {
            'purple' => 'bg-libra-purple-100 text-libra-purple-700 border-libra-purple-200',
            default => 'bg-libra-coral-100 text-libra-coral-700 border-libra-coral-200',
        };
    }
}
