<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrolledSubject extends Model
{
    use HasFactory;

    protected const DEFAULT_STATUS_GRADE_DISPLAY = [
        'dropped' => 'DRP',
    ];

    protected const EXCLUDED_FROM_GPA_CODES = ['INC', 'INP', 'DRP', 'W', 'P', 'AU'];

    protected const GPA_EQUIVALENT_CODES = [
        'UD' => 5.0,
        'FDA' => 5.0,
    ];

    protected const COMPLETION_DEADLINE_CODES = ['INC', 'INP'];

    protected $fillable = [
        'status',
        'midterm_grade',
        'final_grade',
        'final_grade_code',
        'completion_due_at',
        'enrollment_id',
        'scheduled_subject_id',
    ];

    protected $casts = [
        'midterm_grade' => 'decimal:2',
        'final_grade' => 'decimal:2',
        'completion_due_at' => 'date',
    ];

    protected $appends = [
        'midterm_grade_display',
        'final_grade_display',
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
    public function getMidtermGradeDisplayAttribute(): string
    {
        return $this->resolveGradeDisplay($this->midterm_grade);
    }

    public function getFinalGradeDisplayAttribute(): string
    {
        if (!empty($this->final_grade_code)) {
            return strtoupper((string) $this->final_grade_code);
        }

        return $this->resolveGradeDisplay($this->final_grade);
    }

    public function hasNumericFinalGrade(): bool
    {
        return $this->getFinalGradeForGpa() !== null;
    }

    public function getFinalGradeForGpa(): ?float
    {
        if (is_numeric($this->final_grade)) {
            return (float) $this->final_grade;
        }

        $code = strtoupper((string) ($this->final_grade_code ?? ''));

        if (isset(self::GPA_EQUIVALENT_CODES[$code])) {
            return self::GPA_EQUIVALENT_CODES[$code];
        }

        return null;
    }

    public function isExcludedFromGpa(): bool
    {
        $code = strtoupper((string) ($this->final_grade_code ?? ''));

        if (in_array($code, self::EXCLUDED_FROM_GPA_CODES, true)) {
            return true;
        }

        return $this->getFinalGradeForGpa() === null;
    }

    public function needsCompletionDeadline(): bool
    {
        if (is_numeric($this->final_grade) && (float) $this->final_grade === 4.0) {
            return true;
        }

        $code = strtoupper((string) ($this->final_grade_code ?? ''));

        return in_array($code, self::COMPLETION_DEADLINE_CODES, true);
    }

    public function isPassed()
    {
        return $this->hasNumericFinalGrade() && (float) $this->final_grade <= 3.0;
    }

    public function isFailed()
    {
        return $this->hasNumericFinalGrade() && (float) $this->final_grade > 3.0;
    }

    private function resolveGradeDisplay($grade): string
    {
        if (is_numeric($grade)) {
            return number_format((float) $grade, 2, '.', '');
        }

        $status = strtolower((string) $this->status);

        if (isset(self::DEFAULT_STATUS_GRADE_DISPLAY[$status])) {
            return self::DEFAULT_STATUS_GRADE_DISPLAY[$status];
        }

        return '-';
    }
}
