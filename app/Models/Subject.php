<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'units',
    ];

    protected $casts = [
        'units' => 'integer',
    ];

    // Relationships
    public function curriculumSubjects()
    {
        return $this->hasMany(CurriculumSubject::class);
    }
}
