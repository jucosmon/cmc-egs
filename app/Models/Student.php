<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'year_level',
        'status',
        'block_id',
        'program_id',
        'user_id',
        'birth_place',
        'religion',
        'citizenship',
        'father_name',
        'mother_name',
        'elementary_school',
        'elementary_year',
        'secondary_school',
        'secondary_year',
    ];

    protected $casts = [
        'year_level' => 'integer',
        'birth_place' => 'string',
        'religion' => 'string',
        'citizenship' => 'string',
        'father_name' => 'string',
        'mother_name' => 'string',
        'elementary_school' => 'string',
        'elementary_year' => 'string',
        'secondary_school' => 'string',
        'secondary_year' => 'string',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Scopes
    public function scopeRegular($query)
    {
        return $query->where('status', 'regular');
    }

    public function scopeIrregular($query)
    {
        return $query->where('status', 'irregular');
    }

    public function scopeByYearLevel($query, $yearLevel)
    {
        return $query->where('year_level', $yearLevel);
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }
}
