<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data = Buyer::where('owner_id', $user->id)->latest()->get();

        return response()->json($data);
    }

    public function show($id)
    {
        $user = auth()->user();
        $data = Buyer::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'nik'     => 'nullable|string|max:30',
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'name.max'       => 'Nama lengkap maksimal 255 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.max'      => 'Nomor telepon maksimal 20 karakter.',
            'email.email'    => 'Format email tidak valid.',
            'email.max'      => 'Email maksimal 255 karakter.',
            'nik.max'        => 'NIK maksimal 30 karakter.',
        ]);

        $user = auth()->user();

        $data = Buyer::create([
            'owner_id' => $user->id,
            'name'     => $validated['name'],
            'phone'    => $validated['phone'],
            'email'    => $validated['email'] ?? null,
            'address'  => $validated['address'] ?? null,
            'nik'      => $validated['nik'] ?? null,
        ]);

        return response()->json([
            'message' => 'Buyer berhasil dibuat',
            'data'    => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'    => 'sometimes|string|max:255',
            'phone'   => 'sometimes|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'nik'     => 'nullable|string|max:30',
        ], [
            'name.max'       => 'Nama lengkap maksimal 255 karakter.',
            'phone.max'      => 'Nomor telepon maksimal 20 karakter.',
            'email.email'    => 'Format email tidak valid.',
            'email.max'      => 'Email maksimal 255 karakter.',
            'nik.max'        => 'NIK maksimal 30 karakter.',
        ]);

        $user  = auth()->user();
        $buyer = Buyer::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $buyer->update($validated);

        return response()->json([
            'message' => 'Buyer berhasil diupdate',
            'data'    => $buyer
        ]);
    }

    public function destroy($id)
    {
        $user  = auth()->user();
        $buyer = Buyer::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $buyer->delete();

        return response()->json(['message' => 'Buyer berhasil dihapus']);
    }
}
