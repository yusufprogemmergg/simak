<?php

namespace App\Http\Controllers\Api;

use App\Exports\ProjectExport;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // GET all — hanya project milik owner yang login
    public function index()
    {
        $data = Project::with('plots')
            ->where('owner_id', auth()->id())
            ->latest()
            ->get();

        return response()->json($data);
    }

    // GET by id
    public function show($id)
    {
        $data = Project::with('plots')
            ->where('owner_id', auth()->id())
            ->findOrFail($id);

        return response()->json($data);
    }

    // CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string',
            'location'    => 'required|string',
            'notes'       => 'nullable|string',
            'total_units' => 'required|integer',
        ]);

        $data = Project::create([
            'owner_id'    => auth()->id(),
            'name'        => $validated['name'],
            'location'    => $validated['location'],
            'notes'       => $validated['notes'] ?? null,
            'total_units' => $validated['total_units'],
        ]);

        return response()->json([
            'message' => 'Project berhasil dibuat',
            'data'    => $data
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string',
            'location'    => 'sometimes|string',
            'notes'       => 'nullable|string',
            'total_units' => 'sometimes|integer',
        ]);

        $project = Project::where('id', $id)
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $project->update($validated);

        return response()->json([
            'message' => 'Project berhasil diupdate',
            'data'    => $project
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $project = Project::where('id', $id)
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $project->delete();

        return response()->json(['message' => 'Project berhasil dihapus']);
    }

    // EXPORT EXCEL
    public function exportExcel()
    {
        return (new ProjectExport(auth()->id()))->download();
    }
}