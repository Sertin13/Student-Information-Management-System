<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends CrudController
{
    protected string $model = Course::class;
    protected array $rules = ['course_code'=>'required|string|max:50|unique:courses,course_code','course_title'=>'required|string|max:255','units'=>'required|integer|min:1|max:10','description'=>'nullable|string','status'=>'required|in:Active,Inactive'];

    public function index(Request $request)
    {
        $data = $request->validate(['search'=>['nullable','string','max:255'],'status'=>['nullable','in:Active,Inactive'],'sort'=>['nullable','in:course_code,course_title,units,status'],'direction'=>['nullable','in:asc,desc'],'per_page'=>['nullable','integer','min:1']]);
        $query = Course::query();
        if (! empty($data['search'])) $query->where(fn ($q) => $q->where('course_code','like','%'.$data['search'].'%')->orWhere('course_title','like','%'.$data['search'].'%'));
        if (! empty($data['status'])) $query->where('status', $data['status']);
        $query->orderBy($data['sort'] ?? 'course_code', $data['direction'] ?? 'asc');
        return response()->json(['success'=>true,'data'=>$query->paginate(min((int)($data['per_page'] ?? 20),100))]);
    }
}
