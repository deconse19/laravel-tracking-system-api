<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_assigner_id',
        'task_name',
        'task_description',
        'status'
        
    ];

    public function users(){

        return $this->belongsToMany(User::class);
    }

//    public function assigner(){

//     return $this->belongsToMany(User::class);
//    }

}
