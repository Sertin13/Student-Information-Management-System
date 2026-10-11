<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseOffering extends Model
{
    public function enrollments(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(Enrollment::class); }
    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(Course::class); }
    public function academicTerm(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(AcademicTerm::class); }
    public function instructor(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(User::class, 'instructor_id'); }
}
