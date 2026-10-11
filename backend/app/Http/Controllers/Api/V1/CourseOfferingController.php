<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CourseOfferingController extends CrudController
{
    protected string $model = CourseOffering::class;
    protected array $rules = [
        'course_id' => 'required|exists:courses,id',
        'academic_term_id' => 'required|exists:academic_terms,id',
        'instructor_id' => 'nullable',
        'section' => 'required|string|max:50',
        'capacity' => 'required|integer|min:1',
        'status' => 'required|in:Active,Inactive',
        'schedule' => 'nullable|string',
        'room' => 'nullable|string',
    ];

    public function __construct()
    {
        $this->rules['instructor_id'] = [
            'nullable',
            Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn(
                'role_id',
                DB::table('roles')->select('id')->where('name', 'Instructor')
            )),
        ];
    }
}
