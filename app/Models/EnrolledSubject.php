<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrolledSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'midterm_grade',
        'final_grade',
        'enrollment_id',
        'scheduled_subject_id',
    ];

    protected $casts = [
        'midterm_grade' => 'decimal:2',
        'final_grade' => 'decimal:2',
    ];

    // Relationships
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function scheduledSubject()
    {
        return $this->belongsTo(ScheduledSubject::class);
    }

    public function gradeChangeLogs()
    {
        return $this->hasMany(GradeChangeLog::class);
    }

    // Scopes
    public function scopeEnrolled($query)
    {
        return $query->where('status', 'enrolled');
    }

    public function scopeDropped($query)
    {
        return $query->where('status', 'dropped');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByEnrollment($query, $enrollmentId)
    {
        return $query->where('enrollment_id', $enrollmentId);
    }

    // Methods
    public function isPassed()
    {
        return $this->final_grade !== null && $this->final_grade >= 75;
    }

    public function isFailed()
    {
        return $this->final_grade !== null && $this->final_grade < 75;
    }
}
