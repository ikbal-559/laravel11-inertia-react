<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Response;

class ProjectController extends Controller
{

    const PAR_PAGE = 20;

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $projects = Project::filterBy()->latest()->paginate(self::PAR_PAGE)->onEachSide(1);

        return inertia("Project/Index", [
            "projects" => ProjectResource::collection($projects),
            'queryParams' => request()->query() ?: null,
            'success' => session('success'),
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create() : Response
    {
         return inertia("Project/Create");
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(StoreProjectRequest $request) : RedirectResponse
    {
        $project = new Project();
        $project->fill($request->validated());
        $project->image_path = $this->manageImage();
        $project->save();

        return to_route('project.index')
            ->with('success', 'Project was created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): Response
    {
        $tasks = $project->tasks()
            ->filterBy()
            ->paginate(10)
            ->onEachSide(1);


        return inertia('Project/Show', [
            'project' => new ProjectResource($project),
            "tasks" => TaskResource::collection($tasks),
            'queryParams' => request()->query() ?: null,
            'success' => session('success'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): Response
    {
        return inertia('Project/Edit', [
            'project' => new ProjectResource($project),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->fill($request->validated());
        if (request()->hasFile('image')) {
            $project->image_path = $this->manageImage($project);
        }
        $project->save();
        return to_route('project.index')->with('success', "Project \"$project->name\" was updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $name = $project->name;
        $project->tasks()->delete();
        $project->delete();
        if ($project->image_path) {
            \Storage::disk('public')->deleteDirectory(dirname($project->image_path));
        }
        return to_route('project.index')
            ->with('success', "Project \"$name\" was deleted");
    }

    private function manageImage(Project $project = null)
    {
        if(!empty($project) && $project->image_path){
             \Storage::disk('public')->deleteDirectory(dirname($project->image_path));
        }
        return (request()->hasFile('image')) ?  request('image')->store( Project::imageDirectory . '/' . str::random(), 'public') : null;
    }
}
