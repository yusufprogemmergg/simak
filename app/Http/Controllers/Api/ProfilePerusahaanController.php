<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfilePerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilePerusahaanController extends Controller
{
    /**
     * GET profile milik owner yang login
     */
    public function index()
    {
        $data = auth()->user()->profilePerusahaan;

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

    /**
     * CREATE / UPDATE profile perusahaan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string',
            'npwp'                => ['nullable', 'regex:/^\d{2}\.\d{3}\.\d{3}\.\d-\d{3}\.\d{3}$/'],
            'email'               => 'nullable|email',
            'telepon'             => 'nullable|string',
            'alamat'              => 'nullable|string',
            'logo'                => 'nullable|image|max:2048',
            'nama_ttd_admin'      => 'nullable|string',
            'catatan_kaki_cetakan'=> 'nullable|string',
            'format_faktur'       => ['required', 'string', 'regex:/\d{4}$/'],
            'format_kuitansi'     => ['required', 'string', 'regex:/\d{4}$/'],
        ]);

        $user = auth()->user();

        // Upload logo: hapus lama jika ada, simpan yang baru
        if ($request->hasFile('logo')) {
            if ($user->profilePerusahaan && $user->profilePerusahaan->logo) {
                Storage::disk('public')->delete($user->profilePerusahaan->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logo', 'public');
        }

        $data = ProfilePerusahaan::updateOrCreate(
            ['owner_id' => $user->id],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile perusahaan berhasil disimpan',
            'data'    => $data
        ]);
    }

    /**
     * UPDATE profile (via POST /{id})
     */
    public function update(Request $request, $id)
    {
        $user    = auth()->user();
        $profile = ProfilePerusahaan::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name'                => 'sometimes|string',
            'npwp'                => ['nullable', 'regex:/^\d{2}\.\d{3}\.\d{3}\.\d-\d{3}\.\d{3}$/'],
            'email'               => 'nullable|email',
            'telepon'             => 'nullable|string',
            'alamat'              => 'nullable|string',
            'logo'                => 'nullable|image|max:2048',
            'nama_ttd_admin'      => 'nullable|string',
            'catatan_kaki_cetakan'=> 'nullable|string',
            'format_faktur'       => ['sometimes', 'string', 'regex:/\d{4}$/'],
            'format_kuitansi'     => ['sometimes', 'string', 'regex:/\d{4}$/'],
        ]);

        if ($request->hasFile('logo')) {
            if ($profile->logo) {
                Storage::disk('public')->delete($profile->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logo', 'public');
        }

        $profile->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile perusahaan berhasil diupdate',
            'data'    => $profile
        ]);
    }

    /**
     * DELETE profile perusahaan (via DELETE /{id})
     */
    public function destroy($id)
    {
        $user    = auth()->user();
        $profile = ProfilePerusahaan::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        if ($profile->logo) {
            Storage::disk('public')->delete($profile->logo);
        }

        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profile perusahaan berhasil dihapus'
        ]);
    }

    /**
     * DELETE LOGO SAJA — hanya milik owner yang login
     */
    public function deleteLogo()
    {
        $user    = auth()->user();
        $profile = $user->profilePerusahaan;

        if ($profile && $profile->logo) {
            Storage::disk('public')->delete($profile->logo);
            $profile->update(['logo' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logo berhasil dihapus'
        ]);
    }

    /**
     * Generate nomor berdasarkan format (helper internal)
     */
    public function generateNomor($format, $nomorUrut)
    {
        return str_replace(
            ['{YYYY}', '{MM}', '{DD}', '{####}'],
            [
                now()->format('Y'),
                now()->format('m'),
                now()->format('d'),
                str_pad($nomorUrut, 4, '0', STR_PAD_LEFT)
            ],
            $format
        );
    }
}