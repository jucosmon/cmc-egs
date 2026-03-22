<?php

namespace App\Models;

use App\Models\Traits\SoftArchive;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, SoftArchive;

    protected $fillable = [
        'code',
        'title',
        'description',
        'units',
        'is_active',
    ];

    protected $casts = [
        'units' => 'integer',
        'is_active' => 'boolean',
        'archived_at' => 'datetime',
    ];

    // Relationships
    public function curriculumSubjects()
    {
        return $this->hasMany(CurriculumSubject::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
