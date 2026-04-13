<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserLicense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminLicenseController extends Controller
{
    /**
     * GET semua license keys
     * Termasuk info siapa yang sudah pakai
     */
    public function index(Request $request): JsonResponse
    {
        $query = UserLicense::with('user:id,username,email');

        // Filter by status jika ada
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest()->get()->map(function ($lic) {
            return [
                'id'          => $lic->id,
                'license_key' => $lic->license_key,
                'note'        => $lic->note,
                'status'      => $lic->status,
                'is_used'     => $lic->isUsed(),
                'used_by'     => $lic->user ? [
                    'id'       => $lic->user->id,
                    'username' => $lic->user->username,
                    'email'    => $lic->user->email,
                ] : null,
                'start_date'  => $lic->start_date?->format('Y-m-d'),
                'created_at'  => $lic->created_at->format('Y-m-d H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'total'   => $data->count(),
            'data'    => $data,
        ]);
    }

    /**
     * POST buat license key baru
     * Bisa buat 1 atau lebih sekaligus (bulk)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'note'     => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:1|max:50',
        ]);

        $quantity = $validated['quantity'] ?? 1;
        $note     = $validated['note'] ?? null;

        $created = [];
        for ($i = 0; $i < $quantity; $i++) {
            $created[] = UserLicense::create([
                'license_key' => UserLicense::generateKey(),
                'note'        => $note,
                'status'      => 'available',
                'user_id'     => null,
                'start_date'  => null,
            ]);
        }

        return response()->json([
            'success'  => true,
            'message'  => $quantity . ' license key berhasil dibuat',
            'data'     => $created,
        ], 201);
    }

    /**
     * GET detail satu license
     */
    public function show($id): JsonResponse
    {
        $lic = UserLicense::with('user:id,username,email')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $lic->id,
                'license_key' => $lic->license_key,
                'note'        => $lic->note,
                'status'      => $lic->status,
                'is_used'     => $lic->isUsed(),
                'used_by'     => $lic->user ? [
                    'id'       => $lic->user->id,
                    'username' => $lic->user->username,
                    'email'    => $lic->user->email,
                ] : null,
                'start_date'  => $lic->start_date?->format('Y-m-d'),
                'created_at'  => $lic->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * UPDATE note atau status sebuah key
     * (misalnya revoke/reaktivasi)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $lic = UserLicense::findOrFail($id);

        $validated = $request->validate([
            'note'   => 'nullable|string|max:255',
            'status' => 'sometimes|in:available,active,revoked',
        ]);

        // Tidak boleh set available jika sudah dipakai owner
        if (isset($validated['status']) && $validated['status'] === 'available' && $lic->isUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'Key sudah dipakai oleh owner, tidak bisa dikembalikan ke available.',
            ], 422);
        }

        $lic->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'License berhasil diupdate',
            'data'    => $lic->fresh(),
        ]);
    }

    /**
     * DELETE — hanya bisa hapus key yang belum dipakai
     */
    public function destroy($id): JsonResponse
    {
        $lic = UserLicense::findOrFail($id);

        if ($lic->isUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus license yang sudah dipakai owner. Gunakan revoke.',
            ], 422);
        }

        $lic->delete();

        return response()->json([
            'success' => true,
            'message' => 'License key berhasil dihapus.',
        ]);
    }
}
