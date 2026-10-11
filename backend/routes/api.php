<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\{StudentController, ProgramController, CourseController, AcademicTermController, CourseOfferingController, EnrollmentController, GradeController, InstructorEnrollmentController};
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('my/course-offerings', function (\Illuminate\Http\Request $request) {
        abort_unless($request->user()->role?->name === 'Instructor', 403);
        return response()->json(['success'=>true,'data'=>\App\Models\CourseOffering::where('instructor_id',$request->user()->id)->paginate(20)]);
    });
    Route::get('my/student', function (\Illuminate\Http\Request $request) {
        abort_unless($request->user()->role?->name === 'Student', 403);
        return response()->json(['success'=>true,'data'=>\App\Models\Student::where('user_id',$request->user()->id)->firstOrFail()]);
    });
    Route::get('my/enrollments', function (\Illuminate\Http\Request $request) {
        abort_unless($request->user()->role?->name === 'Student', 403);
        return response()->json(['success'=>true,'data'=>\App\Models\Enrollment::with(['courseOffering.course', 'courseOffering.academicTerm', 'grade'])
            ->whereHas('student', fn ($query) => $query->where('user_id', $request->user()->id))->paginate(20)]);
    });
    Route::get('my/grades', function (\Illuminate\Http\Request $request) {
        abort_unless($request->user()->role?->name === 'Student', 403);
        return response()->json(['success'=>true,'data'=>\App\Models\Grade::with('enrollment.courseOffering.course')
            ->whereHas('enrollment.student', fn ($query) => $query->where('user_id', $request->user()->id))->paginate(20)]);
    });
    Route::get('students/{student}/academic-record', function (\App\Models\Student $student, \Illuminate\Http\Request $request) {
        $role = $request->user()->role?->name;
        abort_unless(in_array($role, ['Admin', 'Staff'], true) || ($role === 'Student' && $student->user_id === $request->user()->id), 403);

        $records = \App\Models\Enrollment::with(['courseOffering.course', 'courseOffering.academicTerm', 'grade'])
            ->where('student_id', $student->id)->get();
        return response()->json(['success'=>true,'data'=>['student'=>$student,'records'=>$records]]);
    });
});

Route::middleware(['auth:sanctum', 'role:Instructor'])->prefix('v1/my')->group(function () {
    Route::get('teaching-enrollments', [InstructorEnrollmentController::class, 'index']);
    Route::post('grades', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);
        $enrollment = \App\Models\Enrollment::with('courseOffering')->findOrFail($data['enrollment_id']);
        abort_unless($enrollment->courseOffering->instructor_id === $request->user()->id, 403);
        abort_if(\App\Models\Grade::where('enrollment_id', $enrollment->id)->exists(), 409, 'A grade already exists for this enrollment.');
        return response()->json(['success'=>true, 'data'=>\App\Models\Grade::create($data)], 201);
    });
    Route::put('grades/{grade}', function (\App\Models\Grade $grade, \Illuminate\Http\Request $request) {
        $grade->load('enrollment.courseOffering');
        abort_unless($grade->enrollment->courseOffering->instructor_id === $request->user()->id, 403);
        $data = $request->validate([
            'grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);
        $grade->update($data);
        return response()->json(['success'=>true, 'data'=>$grade->fresh()]);
    });
});

Route::middleware(['auth:sanctum','role:Admin,Staff'])->prefix('v1')->group(function () {
    Route::get('instructors', function () {
        return response()->json(['success'=>true,'data'=>\App\Models\User::whereHas('role', fn ($query) => $query->where('name', 'Instructor'))->where('status', 'Active')->orderBy('name')->get(['id','name','email','role_id'])]);
    });
    Route::apiResource('students', StudentController::class);
    Route::apiResource('programs', ProgramController::class);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('academic-terms', AcademicTermController::class);
    Route::apiResource('course-offerings', CourseOfferingController::class);
    Route::apiResource('enrollments', EnrollmentController::class);
    Route::apiResource('grades', GradeController::class)->except('destroy');
});
