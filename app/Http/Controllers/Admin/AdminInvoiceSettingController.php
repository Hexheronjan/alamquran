<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSetting;
use App\Models\School;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminInvoiceSettingController extends Controller
{
    public function index()
    {
        $setting = InvoiceSetting::getSetting();
        $schools = School::all();

        return view('admin.invoice_setting.index', compact('setting', 'schools'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_lembaga'          => 'required|string|max:200',
            'tagline_kop'           => 'nullable|string|max:255',
            'alamat'                => 'nullable|string|max:500',
            'telepon'               => 'nullable|string|max:100',
            'email'                 => 'nullable|string|max:100',
            'website'               => 'nullable|string|max:150',
            'nama_bendahara'        => 'nullable|string|max:150',
            'jabatan_penandatangan' => 'nullable|string|max:100',
            'catatan_bawah'         => 'nullable|string|max:500',
            'logo'                  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $setting = InvoiceSetting::getSetting();

        if ($request->hasFile('logo')) {
            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }
            $validated['logo'] = $request->file('logo')->store('invoice_logo', 'public');
        }

        $setting->update($validated);

        AdminActivityLogger::log('update_invoice_setting', 'Memperbarui pengaturan kop dan format invoice pesantren');

        return redirect()->route('admin.invoice_setting.index')
            ->with('success', 'Pengaturan Kop dan Format Invoice berhasil disimpan.');
    }

    public function copyFromSchool(Request $request)
    {
        $schoolId = $request->get('school_id');
        $school = $schoolId ? School::find($schoolId) : School::first();

        if (!$school) {
            return redirect()->back()->with('error', 'Data madrasah tidak ditemukan.');
        }

        $setting = InvoiceSetting::getSetting();
        $setting->update([
            'nama_lembaga' => $school->name,
            'alamat'       => $school->address,
            'telepon'      => $school->phone,
            'email'        => $school->email ?? $setting->email,
        ]);

        return redirect()->route('admin.invoice_setting.index')
            ->with('success', "Kop invoice berhasil disinkronkan dari Data Madrasah: {$school->name}");
    }
}
