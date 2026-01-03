<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'day',
        'room',
        'time_start',
        'time_end',
        'academic_term_id',
        'block_id',
        'instructor_id',
        'curriculum_subject_id',
    ];

    protected $casts = [
        'time_start' => 'datetime:H:i',
        'time_end' => 'datetime:H:i',
    ];

    // Relationships
    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function curriculumSubject()
    {
        return $this->belongsTo(CurriculumSubject::class);
    }

    public function enrolledSubjects()
    {
        return $this->hasMany(EnrolledSubject::class);
    }

    // Scopes
    public function scopeByAcademicTerm($query, $termId)
    {
        return $query->where('academic_term_id', $termId);
    }

    public function scopeByBlock($query, $blockId)
    {
        return $query->where('block_id', $blockId);
    }

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }
}
