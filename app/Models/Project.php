<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\ProjectObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use MongoDB\Driver\Query;

#[ObservedBy([ProjectObserver::class])]
class Project extends Model
{
    use HasFactory;

    const imageDirectory = 'project';

    protected $fillable = ['image_path', 'name', 'description', 'status', 'due_date', 'created_by', 'updated_by'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
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
