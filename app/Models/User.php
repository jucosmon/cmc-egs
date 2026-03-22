<?php

namespace App\Models;

use App\Models\Traits\SoftArchive;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftArchive;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'password',
        'email',
        'personal_email',
        'avatar',
        'role',
        'official_id',
        'first_name',
        'last_name',
        'middle_name',
        'phone',
        'address',
        'date_of_birth',
        'sex',
        'profile_picture',
        'is_active',
    ];

    protected $appends = [
        'full_name',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function instructor()
    {
        return $this->hasOne(Instructor::class);
    }

    public function departmentAsDean()
    {
        return $this->hasOne(Department::class, 'dean_id');
    }

    public function programAsHead()
    {
        return $this->hasOne(Program::class, 'program_head_id');
    }

    public function departmentAsProgramHead()
    {
        return $this->hasOneThrough(
            Department::class,
            Program::class,
            'program_head_id',
            'id',
            'id',
            'department_id'
        );
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function getAvatarAttribute()
    {
        return $this->attributes['avatar'] ?? $this->profile_picture;
    }

    public function setAvatarAttribute($value): void
    {
        if (Schema::hasColumn('users', 'avatar')) {
            $this->attributes['avatar'] = $value;
        }

        $this->attributes['profile_picture'] = $value;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset(Storage::url($this->avatar));
        }

        return asset('images/default-avatar.svg');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

}
