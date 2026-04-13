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
        $data = Project::with('kavling')
            ->where('owner_id', auth()->id())
            ->latest()
            ->get();

        return response()->json($data);
    }

    // GET by id
    public function show($id)
    {
        $data = Project::with('kavling')
            ->where('owner_id', auth()->id())
            ->findOrFail($id);

        return response()->json($data);
    }

    // CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_project' => 'required|string',
            'lokasi'       => 'required|string',
            'catatan'      => 'nullable|string',
            'total_unit'   => 'required|integer',
        ]);

        $user = auth()->user();

        $data = Project::create([
            'owner_id'     => $user->id,
            'nama_project' => $validated['nama_project'],
            'lokasi'       => $validated['lokasi'],
            'catatan'      => $validated['catatan'] ?? null,
            'total_unit'   => $validated['total_unit'],
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
            'nama_project' => 'sometimes|string',
            'lokasi'       => 'sometimes|string',
            'catatan'      => 'nullable|string',
            'total_unit'   => 'sometimes|integer',
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

    // DELETE — hanya bisa hapus project milik sendiri
    public function destroy($id)
    {
        $project = Project::where('id', $id)
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $project->delete();

        return response()->json([
            'message' => 'Project berhasil dihapus'
        ]);
    }

    // EXPORT EXCEL — download semua project milik owner
    public function exportExcel()
    {
        return (new ProjectExport(auth()->id()))->download();
    }
}