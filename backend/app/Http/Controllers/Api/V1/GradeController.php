<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends CrudController
{
    protected string $model = Grade::class;
    protected array $rules = ['enrollment_id'=>'required|exists:enrollments,id','grade'=>'nullable|numeric|min:0|max:100','remarks'=>'nullable|string|max:255'];
}
