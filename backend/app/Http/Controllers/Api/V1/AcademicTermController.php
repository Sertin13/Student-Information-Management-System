<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;

class AcademicTermController extends CrudController
{
    protected string $model = AcademicTerm::class;
    protected array $rules = ['academic_year'=>'required|string|max:20','semester'=>'required|string|max:50','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','status'=>'required|in:Active,Inactive'];
}
