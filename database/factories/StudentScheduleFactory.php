<?php

namespace Database\Factories;

use App\Models\StudentSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentSchedule>
 */
class StudentScheduleFactory extends Factory
{
    protected $model = StudentSchedule::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'student_first_name' => fake()->firstName(),
            'student_last_name' => fake()->lastName(),
            'subject' => fake()->randomElement(['Matematika', 'Fizika', 'Kemija', 'Engleski', 'Hrvatski', 'Biologija']),
            'note' => fake()->optional(0.7)->sentence(),
            'scheduled_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'scheduled_time' => fake()->randomElement([
                '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
                '11:00', '11:30', '12:00', '13:00', '13:30', '14:00',
                '14:30', '15:00', '15:30', '16:00', '16:30', '17:00',
            ]),
            'color' => fake()->randomElement(['coral', 'purple']),
            'paid' => fake()->boolean(85),
        ];
    }
}
