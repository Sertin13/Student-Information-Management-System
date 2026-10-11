<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class InstructorEnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);
        $enrollments = Enrollment::with([
            'student.program',
            'courseOffering.course',
            'courseOffering.academicTerm',
            'grade',
        ])->whereHas('courseOffering', fn ($query) => $query->where('instructor_id', $request->user()->id))
            ->paginate($perPage);

        $enrollments->through(fn (Enrollment $enrollment) => [
            'id' => $enrollment->id,
            'enrollment_date' => $enrollment->enrollment_date,
            'status' => $enrollment->status,
            'student' => [
                'id' => $enrollment->student->id,
                'student_number' => $enrollment->student->student_number,
                'first_name' => $enrollment->student->first_name,
                'last_name' => $enrollment->student->last_name,
                'program' => [
                    'id' => $enrollment->student->program->id,
                    'code' => $enrollment->student->program->code,
                    'name' => $enrollment->student->program->name,
                ],
            ],
            'course_offering' => [
                'id' => $enrollment->courseOffering->id,
                'section' => $enrollment->courseOffering->section,
                'course' => [
                    'id' => $enrollment->courseOffering->course->id,
                    'course_code' => $enrollment->courseOffering->course->course_code,
                    'course_title' => $enrollment->courseOffering->course->course_title,
                ],
                'academic_term' => [
                    'id' => $enrollment->courseOffering->academicTerm->id,
                    'academic_year' => $enrollment->courseOffering->academicTerm->academic_year,
                    'semester' => $enrollment->courseOffering->academicTerm->semester,
                ],
            ],
            'grade' => $enrollment->grade ? [
                'id' => $enrollment->grade->id,
                'grade' => $enrollment->grade->grade,
                'remarks' => $enrollment->grade->remarks,
            ] : null,
        ]);

        return response()->json(['success' => true, 'data' => $enrollments]);
    }
}
