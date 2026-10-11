<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(Program::class); }
    public function enrollments(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(Enrollment::class); }
}
