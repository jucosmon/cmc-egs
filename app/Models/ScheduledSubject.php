<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'midterm_submitted_at',
        'final_submitted_at',
        'midterm_submitted_by',
        'final_submitted_by',
    ];

    protected $casts = [
        'time_start' => 'datetime:H:i',
        'time_end' => 'datetime:H:i',
        'midterm_submitted_at' => 'datetime',
        'final_submitted_at' => 'datetime',
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

    public function midtermSubmittedBy()
    {
        return $this->belongsTo(User::class, 'midterm_submitted_by');
    }

    public function finalSubmittedBy()
    {
        return $this->belongsTo(User::class, 'final_submitted_by');
    }

    public function isMidtermSubmitted(): bool
    {
        return $this->midterm_submitted_at !== null;
    }

    public function isFinalSubmitted(): bool
    {
        return $this->final_submitted_at !== null;
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
