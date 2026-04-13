<?php

namespace App\Http\Controllers\Api;

use App\Exports\KavlingExport;
use App\Models\Kavling;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KavlingController extends Controller
{
    /**
     * Helper: ambil project IDs yang dimiliki owner yang login.
     */
    private function ownerProjectIds(): \Illuminate\Support\Collection
    {
        return Project::where('owner_id', auth()->id())->pluck('id');
    }

    // GET all — hanya kavling dari project milik owner
    public function index()
    {
        $data = Kavling::with('project')
            ->whereIn('project_id', $this->ownerProjectIds())
            ->latest()
            ->get();

        return response()->json($data);
    }

    // GET by id
    public function show($id)
    {
        $data = Kavling::with('project')
            ->whereIn('project_id', $this->ownerProjectIds())
            ->findOrFail($id);

        return response()->json($data);
    }

    // CREATE — validasi project_id milik owner
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'  => 'required|exists:project,id',
            'blok_nomor'  => 'required|string',
            'luas'        => 'required|numeric',
            'harga_dasar' => 'required|numeric',
            'status'      => 'required|in:available,sold,reserved,active',
        ]);

        // Pastikan project_id benar-benar milik owner yang login
        Project::where('id', $validated['project_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $data = Kavling::create($validated);

        return response()->json([
            'message' => 'Kavling berhasil dibuat',
            'data'    => $data
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $kavling = Kavling::whereIn('project_id', $this->ownerProjectIds())
            ->findOrFail($id);

        $validated = $request->validate([
            'project_id'  => 'sometimes|exists:project,id',
            'blok_nomor'  => 'sometimes|string',
            'luas'        => 'sometimes|numeric',
            'harga_dasar' => 'sometimes|numeric',
            'status'      => 'sometimes|in:available,sold,reserved,active',
        ]);

        // Jika project_id diubah, pastikan project tujuan juga milik owner
        if (isset($validated['project_id'])) {
            Project::where('id', $validated['project_id'])
                ->where('owner_id', auth()->id())
                ->firstOrFail();
        }

        $kavling->update($validated);

        return response()->json([
            'message' => 'Kavling berhasil diupdate',
            'data'    => $kavling
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $kavling = Kavling::whereIn('project_id', $this->ownerProjectIds())
            ->findOrFail($id);

        $kavling->delete();

        return response()->json([
            'message' => 'Kavling berhasil dihapus'
        ]);
    }

    // BONUS: update status cepat
    public function updateStatus(Request $request, $id)
    {
        $kavling = Kavling::whereIn('project_id', $this->ownerProjectIds())
            ->findOrFail($id);

        $request->validate([
            'status' => 'required|in:available,sold,reserved,active'
        ]);

        $kavling->updateStatus($request->status);

        return response()->json([
            'message' => 'Status kavling berhasil diupdate',
            'data'    => $kavling
        ]);
    }

    // EXPORT EXCEL — download semua kavling milik owner (opsional filter by project_id)
    public function exportExcel(Request $request)
    {
        $projectId = $request->query('project_id'); // opsional

        // Validasi project_id jika disertakan
        if ($projectId) {
            Project::where('id', $projectId)
                ->where('owner_id', auth()->id())
                ->firstOrFail();
        }

        return (new KavlingExport(auth()->id(), $projectId ? (int) $projectId : null))->download();
    }
}