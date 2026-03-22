<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'admission_year',
        'status',
        'program_id',
        'max_students',
    ];

    protected $casts = [
        'admission_year' => 'integer',
        'max_students' => 'integer',
    ];

    // Relationships
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function scheduledSubjects()
    {
        return $this->hasMany(ScheduledSubject::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('admission_year', $year);
    }

    public function archive(): bool
    {
        $this->status = 'inactive';

        if (Schema::hasColumn($this->getTable(), 'archived_at')) {
            $this->setAttribute('archived_at', now());
        }

        return $this->save();
    }
}
