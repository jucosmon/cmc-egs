<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year',
        'semester',
        'start_date',
        'end_date',
        'start_enrollment',
        'end_enrollment',
        'start_mid_grade',
        'end_mid_grade',
        'start_final_grade',
        'end_final_grade',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_enrollment' => 'date',
        'end_enrollment' => 'date',
        'start_mid_grade' => 'date',
        'end_mid_grade' => 'date',
        'start_final_grade' => 'date',
        'end_final_grade' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function scheduledSubjects()
    {
        return $this->hasMany(ScheduledSubject::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    // Methods
    public function isEnrollmentOpen()
    {
        $today = now()->toDateString();
        return $today >= $this->start_enrollment && $today <= $this->end_enrollment;
    }

    public function isMidGradeOpen()
    {
        $today = now()->toDateString();
        return $today >= $this->start_mid_grade && $today <= $this->end_mid_grade;
    }

    public function isFinalGradeOpen()
    {
        $today = now()->toDateString();
        return $today >= $this->start_final_grade && $today <= $this->end_final_grade;
    }

}
