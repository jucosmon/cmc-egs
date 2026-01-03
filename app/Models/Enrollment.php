<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'year_level',
        'enrolled_at',
        'academic_term_id',
        'block_id',
        'student_id',
    ];

    protected $casts = [
        'year_level' => 'integer',
        'enrolled_at' => 'datetime',
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

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enrolledSubjects()
    {
        return $this->hasMany(EnrolledSubject::class);
    }

    // Scopes
    public function scopeEnrolled($query)
    {
        return $query->where('status', 'enrolled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByAcademicTerm($query, $termId)
    {
        return $query->where('academic_term_id', $termId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
