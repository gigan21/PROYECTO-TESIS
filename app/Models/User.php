<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'last_seen'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function studentProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function questionAnswers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentQuestionAnswer::class, 'student_id');
    }

    public function crosswordWords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CrosswordWord::class);
    }

    public function crosswordProgress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CrosswordProgress::class);
    }

    public function crosswordEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CrosswordEvent::class);
    }

    public function badges(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_student')
            ->withTimestamps()
            ->withPivot('unlocked_at');
    }

    public function isDocente(): bool
    {
        return $this->role === 'docente';
    }

    public function isEstudiante(): bool
    {
        return $this->role === 'estudiante';
    }

    public function isTeacher(): bool
    {
        return $this->isDocente();
    }

    public function isStudent(): bool
    {
        return $this->isEstudiante();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
      public function getIsActiveAttribute(): bool
{
    if (!$this->last_seen) {
        return false;
    }
    return $this->last_seen->diffInMinutes(now()) < 2;
}
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen' => 'datetime',
        ];
    }
  
}
