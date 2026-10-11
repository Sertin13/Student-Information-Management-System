<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends CrudController
{
    protected string $model = Student::class;
    protected array $rules = ['student_number'=>'required|string|max:50|unique:students,student_number','first_name'=>'required|string|max:255','middle_name'=>'nullable|string|max:255','last_name'=>'required|string|max:255','email'=>'nullable|email','program_id'=>'required|exists:programs,id','year_level'=>'required|integer|min:1|max:6','status'=>'required|in:Regular,Irregular,Graduated,Inactive','contact_number'=>'nullable|string|max:50','address'=>'nullable|string'];

    public function index(Request $request)
    {
        $queryData = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'year_level' => ['nullable', 'integer', 'min:1', 'max:6'],
            'status' => ['nullable', 'in:Regular,Irregular,Graduated,Inactive'],
            'sort' => ['nullable', 'in:student_number,first_name,middle_name,last_name,email,year_level,status,created_at'],
            'direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Student::query();
        if (! empty($queryData['search'])) {
            $search = '%'.$queryData['search'].'%';
            $query->where(function ($builder) use ($search) {
                $builder->where('student_number', 'like', $search)
                    ->orWhere('first_name', 'like', $search)
                    ->orWhere('middle_name', 'like', $search)
                    ->orWhere('last_name', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }
        foreach (['program_id', 'year_level', 'status'] as $filter) {
            if (isset($queryData[$filter]) && $queryData[$filter] !== '') $query->where($filter, $queryData[$filter]);
        }

        $query->orderBy($queryData['sort'] ?? 'id', $queryData['direction'] ?? 'asc');
        $perPage = min((int) ($queryData['per_page'] ?? 20), 100);

        return response()->json(['success' => true, 'data' => $query->paginate($perPage)]);
    }
}
