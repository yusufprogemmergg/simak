<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    // GET all
    public function index()
    {
        $user = auth()->user();
        $data = Buyer::where('owner_id', $user->id)->latest()->get();

        return response()->json($data);
    }

    // GET by id
    public function show($id)
    {
        $user = auth()->user();
        $data = Buyer::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        return response()->json($data);
    }

    // CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'nik' => 'nullable|string|max:30',
        ]);

        $user = auth()->user();

        $data = Buyer::create([
            'owner_id' => $user->id,
            'username' => $validated['username'],
            'no_telepon' => $validated['no_telepon'],
            'email' => $validated['email'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'nik' => $validated['nik'] ?? null,
        ]);

        return response()->json([
            'message' => 'Buyer berhasil dibuat',
            'data' => $data
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'username' => 'sometimes|string|max:255',
            'no_telepon' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'nik' => 'nullable|string|max:30',
        ]);

        $user = auth()->user();

        $buyer = Buyer::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $buyer->update($validated);

        return response()->json([
            'message' => 'Buyer berhasil diupdate',
            'data' => $buyer
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $user = auth()->user();
        $buyer = Buyer::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $buyer->delete();

        return response()->json([
            'message' => 'Buyer berhasil dihapus'
        ]);
    }
}
