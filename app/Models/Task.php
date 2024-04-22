<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_assigner_id',
        'task_name',
        'task_description',
        'status',
        'verified_at'
        
    ];

    public function users(){

        return $this->belongsToMany(User::class)->withPivot('started_at', 'submitted_at', 'verified_at')->withTimestamps();
    }

    public function department()
{
    return $this->belongsTo(Department::class, 'department_id');
}



}
