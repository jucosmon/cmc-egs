<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prerequisite extends Model
{
    //
    protected $fillable = [
        'curriculum_subject_id',
        'prerequisite_id',
    ];

    // Relationships
    public function curriculumSubject()
    {
        return $this->belongsTo(CurriculumSubject::class, 'curriculum_subject_id');
    }
    public function prerequisiteSubject()
    {
        return $this->belongsTo(CurriculumSubject::class, 'prerequisite_id');
    }

}
