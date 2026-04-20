<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesStaff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SalesController extends Controller
{
    public function index()
    {
        $data = SalesStaff::with('user')
            ->where('owner_id', auth()->id())
            ->latest()
            ->get();

        return response()->json($data);
    }

    public function show($id)
    {
        $data = SalesStaff::with('user')
            ->where('owner_id', auth()->id())
            ->findOrFail($id);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone'    => 'nullable|string',
        ]);

        $owner = auth()->user();

        $user = User::create([
            'username' => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'salesman'
        ]);

        $salesStaff = SalesStaff::create([
            'user_id'         => $user->id,
            'owner_id'        => $owner->id,
            'name'            => $validated['name'],
            'phone'           => $validated['phone'] ?? null,
            'total_units_sold' => 0,
            'total_revenue'   => 0,
        ]);

        return response()->json([
            'message' => 'Sales berhasil dibuat',
            'data'    => $salesStaff->load('user')
        ]);
    }

    public function update(Request $request, $id)
    {
        $salesStaff = SalesStaff::where('owner_id', auth()->id())
            ->with('user')
            ->findOrFail($id);

        $validated = $request->validate([
            'name'     => 'nullable|string',
            'email'    => 'nullable|email|unique:users,email,' . $salesStaff->user_id,
            'password' => 'nullable|min:6',
            'phone'    => 'nullable|string',
        ]);

        $salesStaff->user->update([
            'username' => $validated['name'] ?? $salesStaff->user->username,
            'email'    => $validated['email'] ?? $salesStaff->user->email,
            'password' => isset($validated['password'])
                ? Hash::make($validated['password'])
                : $salesStaff->user->password,
        ]);

        $salesStaff->update([
            'phone' => $validated['phone'] ?? $salesStaff->phone,
        ]);

        return response()->json([
            'message' => 'Sales berhasil diupdate',
            'data'    => $salesStaff->load('user')
        ]);
    }

    public function destroy($id)
    {
        $salesStaff = SalesStaff::where('owner_id', auth()->id())->findOrFail($id);
        $salesStaff->user->delete();

        return response()->json(['message' => 'Sales berhasil dihapus']);
    }
}