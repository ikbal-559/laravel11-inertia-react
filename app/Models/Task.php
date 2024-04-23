<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * @var int|mixed|string|null
     */
    protected $fillable = [
        'name',
        'description',
        'image_path',
        'status',
        'priority',
        'due_date',
        'assigned_user_id',
        'created_by',
        'updated_by',
        'project_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeFilterBy($query)
    {
        $query->when( request('name'), function ($query) {
            return $query->where('name','like','%'. request('name'));
        })
            ->when( request('status'), function ($query) {
                return $query->where("status", request("status"));
            })
            ->when( (request('sort_field') && request('sort_direction')), function ($query) {
                return $query->orderBy(request("sort_field", 'created_at'), request("sort_direction", "desc"));
            });

        return  $query;
    }

}
