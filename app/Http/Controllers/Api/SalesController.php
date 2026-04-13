<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SalesController extends Controller
{
    /**
     * GET ALL SALES (punya owner)
     */
    public function index()
    {
        $data = Sales::with('user')
            ->where('owner_id', auth()->id())
            ->latest()
            ->get();

        return response()->json($data);
    }

    /**
     * GET DETAIL
     */
    public function show($id)
    {
        $data = Sales::with('user')
            ->where('owner_id', auth()->id())
            ->findOrFail($id);

        return response()->json($data);
    }

    /**
     * CREATE SALES + USER
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string',
        ]);

        $owner = auth()->user();

        // 🔥 1. buat user (login)
        $user = User::create([
            'username' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'salesman'
        ]);

        // 🔥 2. buat sales
        $sales = Sales::create([
            'user_id' => $user->id,
            'owner_id' => $owner->id,
            'phone' => $validated['phone'] ?? null,
            'unit_sales' => 0,
            'total_revenue' => 0,
        ]);

        return response()->json([
            'message' => 'Sales berhasil dibuat',
            'data' => $sales->load('user')
        ]);
    }

    /**
     * UPDATE SALES + USER
     */
    public function update(Request $request, $id)
    {
        $sales = Sales::where('owner_id', auth()->id())
            ->with('user')
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,' . $sales->user_id,
            'password' => 'nullable|min:6',
            'phone' => 'nullable|string',
        ]);

        // 🔥 update user
        $sales->user->update([
            'name' => $validated['name'] ?? $sales->user->name,
            'email' => $validated['email'] ?? $sales->user->email,
            'password' => isset($validated['password']) 
                ? Hash::make($validated['password']) 
                : $sales->user->password,
        ]);

        // 🔥 update sales
        $sales->update([
            'phone' => $validated['phone'] ?? $sales->phone,
        ]);

        return response()->json([
            'message' => 'Sales berhasil diupdate',
            'data' => $sales->load('user')
        ]);
    }

    /**
     * DELETE SALES (hapus user juga)
     */
    public function destroy($id)
    {
        $sales = Sales::where('owner_id', auth()->id())
            ->findOrFail($id);

        // hapus user (auto cascade ke sales kalau FK benar)
        $sales->user->delete();

        return response()->json([
            'message' => 'Sales berhasil dihapus'
        ]);
    }
}