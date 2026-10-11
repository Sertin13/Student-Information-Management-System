<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public function courseOfferings(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(CourseOffering::class); }
}
