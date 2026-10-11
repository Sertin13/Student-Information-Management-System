<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(Student::class); }
    public function courseOffering(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(CourseOffering::class); }
    public function grade(): \Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(Grade::class); }
}
