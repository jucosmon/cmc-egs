<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'dean_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function dean()
    {
        return $this->belongsTo(User::class, 'dean_id');
    }

    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

}
