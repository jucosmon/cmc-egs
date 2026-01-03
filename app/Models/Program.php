<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'degree_type',
        'total_units',
        'duration_years',
        'description',
        'is_active',
        'program_head_id',
        'department_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_units' => 'integer',
        'duration_years' => 'integer',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function programHead()
    {
        return $this->belongsTo(User::class, 'program_head_id');
    }

    public function curriculums()
    {
        return $this->hasMany(Curriculum::class);
    }

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDegreeType($query, $degreeType)
    {
        return $query->where('degree_type', $degreeType);
    }
}
