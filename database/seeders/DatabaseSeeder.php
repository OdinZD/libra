<?php

namespace Database\Seeders;

use App\Models\StudentSchedule;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        StudentSchedule::factory(15)->create([
            'user_id' => $user->id,
        ]);

        Workshop::create([
            'naziv' => 'Matematička pripremljenost',
            'opis' => 'Brojanje, uzorci, oblici i rane vještine rješavanja problema kroz igru i praktične aktivnosti.',
            'dobna_skupina' => 'Dob 4-7',
            'ikona' => 'calculator',
            'boja' => 'amber',
            'sort_order' => 1,
        ]);

        Workshop::create([
            'naziv' => 'Čitanje i pisanje',
            'opis' => 'Prepoznavanje slova, fonika, pripovijedanje i fine motoričke vještine za izgradnju čvrstih temelja pismenosti.',
            'dobna_skupina' => 'Dob 4-7',
            'ikona' => 'book',
            'boja' => 'coral',
            'sort_order' => 2,
        ]);

        Workshop::create([
            'naziv' => 'Istraživanje znanosti',
            'opis' => 'Jednostavni eksperimenti, promatranje prirode i STEM koncepti koji bude znatiželju o svijetu oko nas.',
            'dobna_skupina' => 'Dob 5-8',
            'ikona' => 'flask',
            'boja' => 'purple',
            'sort_order' => 3,
        ]);

        Workshop::create([
            'naziv' => 'Kreativne umjetnosti',
            'opis' => 'Crtanje, slikanje, glazba i kreativno izražavanje koje razvija maštu i fine motoričke vještine.',
            'dobna_skupina' => 'Dob 3-6',
            'ikona' => 'brush',
            'boja' => 'red',
            'sort_order' => 4,
        ]);

        Workshop::create([
            'naziv' => 'Socijalne vještine',
            'opis' => 'Timski rad, dijeljenje, komunikacija i emocionalna inteligencija kroz grupne aktivnosti i vođenu igru.',
            'dobna_skupina' => 'Dob 3-7',
            'ikona' => 'people',
            'boja' => 'amber',
            'sort_order' => 5,
        ]);

        Workshop::create([
            'naziv' => 'Priprema za školu',
            'opis' => 'Sveobuhvatni program spremnosti koji kombinira sve ključne vještine: rutine, samostalnost, koncentraciju i akademske temelje.',
            'dobna_skupina' => 'Dob 5-7',
            'ikona' => 'graduation',
            'boja' => 'coral',
            'sort_order' => 6,
        ]);
    }
}
