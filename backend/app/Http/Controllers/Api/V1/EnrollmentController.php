<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends CrudController
{
    protected string $model = Enrollment::class;
    protected array $rules = ['student_id'=>'required|exists:students,id','course_offering_id'=>'required|exists:course_offerings,id','enrollment_date'=>'required|date','status'=>'required|in:Enrolled,Dropped,Completed'];
}
