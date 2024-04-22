<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;

// use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'contact_number',
        'gender',
        'birthdate',
        'address',
        'role',
        'department_id',
        'position_id',
        'company_id',
        'email',
        'password',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed'
    ];
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }
    public function getLastNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function department()
    {

        return $this->belongsTo(Department::class);
    }

    public function position()
    {

        return $this->belongsTo(Position::class);
    }

    public function tasks()
    {

        return $this->belongsToMany(Task::class)->withPivot('started_at', 'submitted_at', 'verified_at')->withTimestamps();
    }

    protected $appends = ['in_progress_tasks_count'];

    public function getInProgressTasksCountAttribute()
    {
        return $this->tasks()->where('task_assigner_id', Auth::user()->id)
            ->where('status', 'in progress')->count();
    }
}
