<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilePerusahaanController extends Controller
{
    /**
     * GET profile milik owner yang login
     */
    public function index()
    {
        $data = auth()->user()->companyProfile;

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

    /**
     * CREATE / UPDATE company profile
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string',
            'npwp'                 => ['nullable', 'regex:/^\d{2}\.\d{3}\.\d{3}\.\d-\d{3}\.\d{3}$/'],
            'email'                => 'nullable|email',
            'phone'                => 'nullable|string',
            'address'              => 'nullable|string',
            'logo_path'            => 'nullable|image|max:2048',
            'admin_signature_name' => 'nullable|string',
            'print_footer'         => 'nullable|string',
            'invoice_format'       => ['required', 'string'],
            'receipt_format'       => ['required', 'string'],
        ], [
            'name.required'           => 'Nama perusahaan wajib diisi.',
            'npwp.regex'              => 'Format NPWP tidak valid (contoh: 00.000.000.0-000.000).',
            'email.email'             => 'Format email tidak valid.',
            'logo_path.image'         => 'Logo harus berupa gambar.',
            'logo_path.max'           => 'Ukuran logo maksimal 2MB.',
            'invoice_format.required' => 'Format faktur wajib diisi.',
            'receipt_format.required' => 'Format kuitansi wajib diisi.',
        ]);

        $user = auth()->user();

        if ($request->hasFile('logo_path')) {
            if ($user->companyProfile && $user->companyProfile->logo_path) {
                Storage::disk('public')->delete($user->companyProfile->logo_path);
            }
            $validated['logo_path'] = $request->file('logo_path')->store('logo', 'public');
        }

        $data = CompanyProfile::updateOrCreate(
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
        $profile = CompanyProfile::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name'                 => 'sometimes|string',
            'npwp'                 => ['nullable', 'regex:/^\d{2}\.\d{3}\.\d{3}\.\d-\d{3}\.\d{3}$/'],
            'email'                => 'nullable|email',
            'phone'                => 'nullable|string',
            'address'              => 'nullable|string',
            'logo_path'            => 'nullable|image|max:2048',
            'admin_signature_name' => 'nullable|string',
            'print_footer'         => 'nullable|string',
            'invoice_format'       => ['sometimes', 'string'],
            'receipt_format'       => ['sometimes', 'string'],
        ], [
            'npwp.regex'      => 'Format NPWP tidak valid (contoh: 00.000.000.0-000.000).',
            'email.email'     => 'Format email tidak valid.',
            'logo_path.image' => 'Logo harus berupa gambar.',
            'logo_path.max'   => 'Ukuran logo maksimal 2MB.',
        ]);

        if ($request->hasFile('logo_path')) {
            if ($profile->logo_path) {
                Storage::disk('public')->delete($profile->logo_path);
            }
            $validated['logo_path'] = $request->file('logo_path')->store('logo', 'public');
        }

        $profile->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile perusahaan berhasil diupdate',
            'data'    => $profile
        ]);
    }

    /**
     * DELETE profile perusahaan
     */
    public function destroy($id)
    {
        $user    = auth()->user();
        $profile = CompanyProfile::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        if ($profile->logo_path) {
            Storage::disk('public')->delete($profile->logo_path);
        }

        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profile perusahaan berhasil dihapus'
        ]);
    }

    /**
     * DELETE LOGO SAJA
     */
    public function deleteLogo()
    {
        $user    = auth()->user();
        $profile = $user->companyProfile;

        if ($profile && $profile->logo_path) {
            Storage::disk('public')->delete($profile->logo_path);
            $profile->update(['logo_path' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logo berhasil dihapus'
        ]);
    }

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