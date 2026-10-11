<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends CrudController
{
    protected string $model = Program::class;
    protected array $rules = ['code'=>'required|string|max:50|unique:programs,code','name'=>'required|string|max:255','description'=>'nullable|string','status'=>'required|in:Active,Inactive'];

    public function index(Request $request)
    {
        $data = $request->validate(['search'=>['nullable','string','max:255'],'status'=>['nullable','in:Active,Inactive'],'sort'=>['nullable','in:code,name,status'],'direction'=>['nullable','in:asc,desc'],'per_page'=>['nullable','integer','min:1']]);
        $query = Program::query();
        if (! empty($data['search'])) $query->where(fn ($q) => $q->where('code','like','%'.$data['search'].'%')->orWhere('name','like','%'.$data['search'].'%'));
        if (! empty($data['status'])) $query->where('status', $data['status']);
        $query->orderBy($data['sort'] ?? 'code', $data['direction'] ?? 'asc');
        return response()->json(['success'=>true,'data'=>$query->paginate(min((int)($data['per_page'] ?? 20),100))]);
    }
}
