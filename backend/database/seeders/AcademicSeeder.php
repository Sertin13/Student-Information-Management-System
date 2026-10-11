<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = collect(['Admin','Staff','Student','Instructor'])->mapWithKeys(fn($name) => [$name => \App\Models\Role::firstOrCreate(['name'=>$name])]);
        foreach (['admin@example.com'=>'Admin','staff@example.com'=>'Staff','registrar@example.com'=>'Staff','instructor@example.com'=>'Instructor','student@example.com'=>'Student'] as $email=>$role) \App\Models\User::firstOrCreate(['email'=>$email], ['name'=>$role.' Account','password'=>'password','role_id'=>$roles[$role]->id,'status'=>'Active']);
        $programs = collect(['BSIT','BSCS','BSIS'])->map(fn($code) => \App\Models\Program::firstOrCreate(['code'=>$code], ['name'=>$code.' Program','status'=>'Active']));
        $students = collect(range(1,100))->map(fn($i) => \App\Models\Student::firstOrCreate(['student_number'=>'2026-'.str_pad($i,5,'0',STR_PAD_LEFT)], ['first_name'=>fake()->firstName(),'last_name'=>fake()->lastName(),'program_id'=>$programs[$i%3]->id,'year_level'=>($i%4)+1,'status'=>'Regular']));
        $students->first()->update(['user_id' => \App\Models\User::where('email', 'student@example.com')->value('id')]);
        $courses = collect(range(1,20))->map(fn($i) => \App\Models\Course::firstOrCreate(['course_code'=>'IT'.str_pad($i,3,'0',STR_PAD_LEFT)], ['course_title'=>'Information Technology '.$i,'units'=>3,'status'=>'Active']));
        $terms = collect(['2026-2027|First','2026-2027|Second'])->map(function($term){[$year,$semester]=explode('|',$term); return \App\Models\AcademicTerm::firstOrCreate(['academic_year'=>$year,'semester'=>$semester],['start_date'=>'2026-08-01','end_date'=>'2026-12-31','status'=>'Active']);});
        foreach (range(0,19) as $i) { $offering=\App\Models\CourseOffering::firstOrCreate(['course_id'=>$courses[$i]->id,'academic_term_id'=>$terms[$i%2]->id,'section'=>'A'],['capacity'=>10,'status'=>'Active']); foreach(range(0,9) as $j){$enrollment=\App\Models\Enrollment::firstOrCreate(['student_id'=>$students[($i*10+$j)%100]->id,'course_offering_id'=>$offering->id],['enrollment_date'=>'2026-08-01','status'=>'Enrolled']); \App\Models\Grade::firstOrCreate(['enrollment_id'=>$enrollment->id],['grade'=>fake()->randomFloat(2,75,100),'remarks'=>'Passed']);}}
    }
}
