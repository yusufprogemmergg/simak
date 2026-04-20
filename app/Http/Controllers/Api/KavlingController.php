<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plot;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Exports\KavlingExport;

class KavlingController extends Controller
{
    private function ownerProjectIds(): \Illuminate\Support\Collection
    {
        return Project::where('owner_id', auth()->id())->pluck('id');
    }

    // GET all — hanya plot dari project milik owner
    public function index()
    {
        $data = Plot::with('project')
            ->whereIn('project_id', $this->ownerProjectIds())
            ->latest()
            ->get();

        return response()->json($data);
    }

    // GET by id
    public function show($id)
    {
        $data = Plot::with('project')
            ->whereIn('project_id', $this->ownerProjectIds())
            ->findOrFail($id);

        return response()->json($data);
    }

    // CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'plot_number' => [
                'required', 'string',
                // Unique per project, bukan global
                Rule::unique('plots')->where(fn ($q) => $q->where('project_id', $request->project_id)),
            ],
            'area'        => 'required|numeric',
            'base_price'  => 'required|numeric',
            'status'      => 'required|in:available,sold,reserved,active',
        ], [
            'plot_number.unique' => 'Nomor unit "' . $request->plot_number . '" sudah ada di project ini. Gunakan nomor unit yang berbeda.',
        ]);

        Project::where('id', $validated['project_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $data = Plot::create($validated);

        return response()->json([
            'message' => 'Kavling berhasil dibuat',
            'data'    => $data
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $plot = Plot::whereIn('project_id', $this->ownerProjectIds())->findOrFail($id);

        $projectId = $request->input('project_id', $plot->project_id);

        $validated = $request->validate([
            'project_id'  => 'sometimes|exists:projects,id',
            'plot_number' => [
                'sometimes', 'string',
                // Unique per project, ignore record saat ini
                Rule::unique('plots')->where(fn ($q) => $q->where('project_id', $projectId))->ignore($id),
            ],
            'area'        => 'sometimes|numeric',
            'base_price'  => 'sometimes|numeric',
            'status'      => 'sometimes|in:available,sold,reserved,active',
        ], [
            'plot_number.unique' => 'Nomor unit "' . $request->plot_number . '" sudah ada di project ini. Gunakan nomor unit yang berbeda.',
        ]);

        if (isset($validated['project_id'])) {
            Project::where('id', $validated['project_id'])
                ->where('owner_id', auth()->id())
                ->firstOrFail();
        }

        $plot->update($validated);

        return response()->json([
            'message' => 'Kavling berhasil diupdate',
            'data'    => $plot
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $plot = Plot::whereIn('project_id', $this->ownerProjectIds())->findOrFail($id);
        $plot->delete();

        return response()->json(['message' => 'Kavling berhasil dihapus']);
    }

    // EXPORT EXCEL
    public function exportExcel(Request $request)
    {
        $projectId = $request->query('project_id');

        if ($projectId) {
            Project::where('id', $projectId)
                ->where('owner_id', auth()->id())
                ->firstOrFail();
        }

        return (new KavlingExport(auth()->id(), $projectId ? (int) $projectId : null))->download();
    }
}