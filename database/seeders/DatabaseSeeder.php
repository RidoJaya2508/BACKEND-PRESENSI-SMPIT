<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Subject;
use App\Models\ClassGroup;
use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Budi Santoso, S.Pd',
            'email' => 'admin@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Create Teacher Users
        $siti = User::create([
            'name' => 'Siti Guru, S.Si',
            'email' => 'siti@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
        ]);

        $joko = User::create([
            'name' => 'Joko Wijaya, S.Pd',
            'email' => 'joko@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'homeroom',
        ]);

        $ani = User::create([
            'name' => 'Ani Kusuma, M.Pd',
            'email' => 'ani@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
        ]);

        // Create Default Subjects
        $subjects = [
            ['name' => 'Matematika Wajib', 'code' => 'MTK-W-10'],
            ['name' => 'Fisika Dasar', 'code' => 'FIS-10'],
            ['name' => 'Bahasa Indonesia', 'code' => 'IND-10'],
            ['name' => 'Biologi', 'code' => 'BIO-10'],
            ['name' => 'Kimia', 'code' => 'KIM-10'],
            ['name' => 'Sejarah', 'code' => 'SJH-10'],
        ];

        $createdSubjects = [];
        foreach ($subjects as $subject) {
            $createdSubjects[] = Subject::create($subject);
        }

        // Create Default Classes
        $class1 = ClassGroup::create([
            'name' => 'X-IPA-1',
            'level' => '10',
            'homeroom_teacher_id' => $joko->id,
        ]);

        $class2 = ClassGroup::create([
            'name' => 'X-IPA-2',
            'level' => '10',
            'homeroom_teacher_id' => $siti->id,
        ]);

        $class3 = ClassGroup::create([
            'name' => 'XI-IPS-1',
            'level' => '11',
            'homeroom_teacher_id' => $ani->id,
        ]);

        // Create Default Students
        $students = [
            ['name' => 'Ahmad Dani', 'nis' => '2024001', 'parent_name' => 'Dani Sr.', 'parent_telegram_id' => '123456789', 'class_group_id' => $class1->id],
            ['name' => 'Siti Aminah', 'nis' => '2024002', 'parent_name' => 'Amin', 'parent_telegram_id' => '987654321', 'class_group_id' => $class1->id],
            ['name' => 'Rudi Hartono', 'nis' => '2024003', 'parent_name' => 'Hartono', 'parent_telegram_id' => '112233445', 'class_group_id' => $class2->id],
            ['name' => 'Dewi Persik', 'nis' => '2024004', 'parent_name' => 'Persik', 'parent_telegram_id' => '445566778', 'class_group_id' => $class2->id],
            ['name' => 'Bambang Pamungkas', 'nis' => '2024005', 'parent_name' => 'Pamungkas', 'parent_telegram_id' => '778899001', 'class_group_id' => $class3->id],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }

        // Create Default Schedules for X-IPA-1
        Schedule::create([
            'subject_id' => $createdSubjects[0]->id, // Matematika
            'teacher_id' => $admin->id,
            'class_group_id' => $class1->id,
            'day_of_week' => 1, // Monday
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'room' => 'X-IPA-1',
            'is_active' => true,
        ]);

        Schedule::create([
            'subject_id' => $createdSubjects[1]->id, // Fisika
            'teacher_id' => $siti->id,
            'class_group_id' => $class1->id,
            'day_of_week' => 1, // Monday
            'start_time' => '08:45:00',
            'end_time' => '10:15:00',
            'room' => 'X-IPA-1',
            'is_active' => true,
        ]);

        Schedule::create([
            'subject_id' => $createdSubjects[3]->id, // Biologi
            'teacher_id' => $joko->id,
            'class_group_id' => $class1->id,
            'day_of_week' => 2, // Tuesday
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'room' => 'Lab Biologi',
            'is_active' => true,
        ]);

        Schedule::create([
            'subject_id' => $createdSubjects[4]->id, // Kimia
            'teacher_id' => $ani->id,
            'class_group_id' => $class1->id,
            'day_of_week' => 2, // Tuesday
            'start_time' => '08:45:00',
            'end_time' => '10:15:00',
            'room' => 'Lab Kimia',
            'is_active' => true,
        ]);

        // Schedules for X-IPA-2
        Schedule::create([
            'subject_id' => $createdSubjects[0]->id, // Matematika
            'teacher_id' => $admin->id,
            'class_group_id' => $class2->id,
            'day_of_week' => 1, // Monday
            'start_time' => '10:30:00',
            'end_time' => '12:00:00',
            'room' => 'X-IPA-2',
            'is_active' => true,
        ]);

        Schedule::create([
            'subject_id' => $createdSubjects[2]->id, // Bahasa Indonesia
            'teacher_id' => $siti->id,
            'class_group_id' => $class2->id,
            'day_of_week' => 3, // Wednesday
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'room' => 'X-IPA-2',
            'is_active' => true,
        ]);

        // Schedules for XI-IPS-1
        Schedule::create([
            'subject_id' => $createdSubjects[5]->id, // Sejarah
            'teacher_id' => $ani->id,
            'class_group_id' => $class3->id,
            'day_of_week' => 1, // Monday
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'room' => 'XI-IPS-1',
            'is_active' => true,
        ]);

        Schedule::create([
            'subject_id' => $createdSubjects[2]->id, // Bahasa Indonesia
            'teacher_id' => $joko->id,
            'class_group_id' => $class3->id,
            'day_of_week' => 4, // Thursday
            'start_time' => '10:30:00',
            'end_time' => '12:00:00',
            'room' => 'XI-IPS-1',
            'is_active' => true,
        ]);
    }
}

