<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurriculumSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'year_level',
        'semester',
        'course_type',
        'has_laboratory',
        'subject_id',
        'curriculum_id',
    ];

    protected $casts = [
        'year_level' => 'integer',
        'has_laboratory' => 'boolean',
    ];

    // Relationships
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function prerequisites()
    {
        return $this->belongsToMany(
            CurriculumSubject::class,
            'prerequisites',
            'curriculum_subject_id',
            'prerequisite_id'
        );
    }

    public function dependentSubjects()
    {
        return $this->belongsToMany(
            CurriculumSubject::class,
            'prerequisites',
            'prerequisite_id',
            'curriculum_subject_id'
        );
    }

    public function scheduledSubjects()
    {
        return $this->hasMany(ScheduledSubject::class);
    }

    // Scopes
    public function scopeByYearLevel($query, $yearLevel)
    {
        return $query->where('year_level', $yearLevel);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeByCourseType($query, $courseType)
    {
        return $query->where('course_type', $courseType);
    }
}
