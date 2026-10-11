<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class CrudController extends Controller
{
    protected string $model;
    protected array $rules = [];
    public function index(Request $request)
    {
        $query = ($this->model)::query();
        $search = trim((string) $request->input('search', ''));
        $sorts = [
            'AcademicTerm' => ['academic_year', 'semester', 'start_date', 'end_date', 'status'],
            'CourseOffering' => ['section', 'capacity', 'status'],
            'Enrollment' => ['enrollment_date', 'status'],
            'Grade' => ['grade', 'created_at'],
        ];
        $modelName = class_basename($this->model);
        if ($search !== '') {
            if ($modelName === 'AcademicTerm') $query->where(fn ($q) => $q->where('academic_year', 'like', "%{$search}%")->orWhere('semester', 'like', "%{$search}%"));
            if ($modelName === 'CourseOffering') $query->where(fn ($q) => $q->where('section', 'like', "%{$search}%")->orWhereHas('course', fn ($c) => $c->where('course_code', 'like', "%{$search}%")->orWhere('course_title', 'like', "%{$search}%")));
            if ($modelName === 'Enrollment') $query->where(fn ($q) => (ctype_digit($search) ? $q->where('id', (int) $search)->orWhere('student_id', (int) $search) : $q)->orWhereHas('student', fn ($s) => $s->where('student_number', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]))->orWhereHas('courseOffering.course', fn ($c) => $c->where('course_code', 'like', "%{$search}%")->orWhere('course_title', 'like', "%{$search}%")));
            if ($modelName === 'Grade') $query->whereHas('enrollment', fn ($e) => $e->whereHas('student', fn ($s) => $s->where('student_number', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))->orWhereHas('courseOffering.course', fn ($c) => $c->where('course_code', 'like', "%{$search}%")->orWhere('course_title', 'like', "%{$search}%")));
        }
        foreach (['status', 'academic_term_id', 'course_id', 'instructor_id', 'course_offering_id'] as $filter) if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        if ($modelName === 'Grade' && $request->filled('grade')) $query->where('grade', $request->input('grade'));
        if ($modelName === 'Enrollment' && $request->boolean('without_grade')) $query->whereDoesntHave('grade');
        if ($modelName === 'CourseOffering') $query->with(['course', 'academicTerm', 'instructor']);
        if ($modelName === 'Enrollment') $query->with(['student.program', 'courseOffering.course', 'courseOffering.academicTerm', 'grade']);
        if ($modelName === 'Grade') $query->with(['enrollment.student', 'enrollment.courseOffering.course', 'enrollment.courseOffering.academicTerm']);
        $allowed = $sorts[$modelName] ?? ['id']; $sort = in_array($request->input('sort'), $allowed, true) ? $request->input('sort') : $allowed[0]; $direction = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'asc';
        return response()->json(['success' => true, 'data' => $query->orderBy($sort, $direction)->paginate(min((int) $request->input('per_page', 20), 100))]);
    }
    public function store(Request $request) { try { $item = ($this->model)::create($request->validate($this->rules)); return response()->json(['success' => true, 'data' => $item], 201); } catch (\Illuminate\Database\QueryException $e) { return response()->json(['success'=>false,'message'=>'Duplicate or conflicting record.'],409); } }
    public function show(string $id) { return response()->json(['success' => true, 'data' => ($this->model)::findOrFail($id)]); }
    public function update(Request $request, string $id) { $item = ($this->model)::findOrFail($id); $rules = array_map(fn($rule) => is_string($rule) ? preg_replace('/\|?unique:[^|]+/', '', $rule) : $rule, $this->rules); try { $item->update($request->validate($rules)); return response()->json(['success' => true, 'data' => $item]); } catch (\Illuminate\Database\QueryException $e) { return response()->json(['success'=>false,'message'=>'Duplicate or conflicting record.'],409); } }
    public function destroy(string $id)
    {
        try {
            ($this->model)::findOrFail($id)->delete();
        } catch (\Illuminate\Database\QueryException $exception) {
            if ((string) $exception->getCode() === '23000' && str_contains(strtolower($exception->getMessage()), 'foreign key constraint')) {
                return response()->json(['success' => false, 'message' => 'This record cannot be deleted because it is still in use.'], 409);
            }
            throw $exception;
        }
        return response()->noContent();
    }
}
