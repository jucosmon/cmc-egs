<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrolled_subject_id',
        'grade_period',
        'old_grade',
        'new_grade',
        'reason',
        'attachment_path',
        'modified_by',
    ];

    protected $casts = [
        'old_grade' => 'decimal:2',
        'new_grade' => 'decimal:2',
    ];

    public function enrolledSubject()
    {
        return $this->belongsTo(EnrolledSubject::class);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
