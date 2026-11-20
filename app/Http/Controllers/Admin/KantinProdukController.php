<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KantinProduk;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KantinProdukController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth('admin')->user();
        $allowedTokos = $admin->getAllowedTokos();

        // Kasir satu toko: langsung redirect ke tokonya
        if (count($allowedTokos) === 1) {
            $selectedToko = $allowedTokos[0];
        } else {
            $selectedToko = $request->get('toko', 'all');
            if ($selectedToko !== 'all' && !in_array($selectedToko, $allowedTokos)) {
                $selectedToko = 'all';
            }
        }

        $counts = [];
        foreach ($allowedTokos as $toko) {
            $counts[$toko] = KantinProduk::where('toko', $toko)->count();
        }

        $query = KantinProduk::whereIn('toko', $allowedTokos);

        if ($selectedToko !== 'all') {
            $query->where('toko', $selectedToko);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('kode_produk', 'like', "%{$search}%");
            });
        }

        $produks = $query->orderBy('nama_produk')->paginate(20)->withQueryString();

        return view('admin.kantin.produk.index', compact('produks', 'counts', 'selectedToko', 'allowedTokos'));
    }

    public function create(Request $request)
    {
        $admin = auth('admin')->user();
        $allowedTokos = $admin->getAllowedTokos();

        $selectedToko = $request->get('toko', $allowedTokos[0] ?? 'aqumart');
        if (!in_array($selectedToko, $allowedTokos)) {
            $selectedToko = $allowedTokos[0] ?? 'aqumart';
        }
        $autoKode = KantinProduk::generateKodeProduk($selectedToko);

        return view('admin.kantin.produk.create', compact('autoKode', 'selectedToko', 'allowedTokos'));
    }

    public function generateKode(Request $request)
    {
        $toko = $request->get('toko', 'aqumart');
        if (!in_array($toko, ['kantin_1', 'kantin_2', 'aqumart'])) {
            $toko = 'aqumart';
        }
        $kode = KantinProduk::generateKodeProduk($toko);

        return response()->json([
            'success' => true,
            'toko'    => $toko,
            'kode'    => $kode,
        ]);
    }

    public function store(Request $request)
    {
        $kodeProduk = trim((string) $request->kode_produk);
        if (empty($kodeProduk)) {
            $kodeProduk = KantinProduk::generateKodeProduk($request->toko);
            $request->merge(['kode_produk' => $kodeProduk]);
        }

        $request->validate([
            'toko'        => ['required', 'in:kantin_1,kantin_2,aqumart'],
            'nama_produk' => ['required', 'string', 'max:255'],
            'kode_produk' => ['required', 'string', 'max:50', 'unique:kantin_produks,kode_produk'],
            'harga_modal' => ['required', 'numeric', 'min:0'],
            'harga_jual'  => ['required', 'numeric', 'min:0'],
            'stok'        => ['required', 'integer', 'min:0'],
            'deskripsi'   => ['nullable', 'string'],
            'foto'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('produk-kantin', 'public');
        }

        $produk = KantinProduk::create([
            'toko'        => $request->toko,
            'nama_produk' => $request->nama_produk,
            'kode_produk' => $kodeProduk,
            'harga_modal' => $request->harga_modal,
            'harga_jual'  => $request->harga_jual,
            'stok'        => $request->stok,
            'deskripsi'   => $request->deskripsi,
            'foto_produk' => $fotoPath,
            'is_active'   => true,
        ]);

        AdminActivityLogger::log(
            'create_produk',
            "Menambahkan produk '{$produk->nama_produk}' ke {$produk->toko_label} (Harga: Rp " . number_format($produk->harga_jual, 0, ',', '.') . ")",
            'KantinProduk',
            $produk->id
        );

        return redirect()->route('admin.kantin.produk.index', ['toko' => $produk->toko])
            ->with('success', "Produk '{$produk->nama_produk}' berhasil ditambahkan.");
    }

    public function edit($id)
    {
        $produk = KantinProduk::findOrFail($id);
        return view('admin.kantin.produk.edit', compact('produk'));
    }

    public function update(Request $request, $id)
    {
        $produk = KantinProduk::findOrFail($id);

        $kodeProduk = trim((string) $request->kode_produk);
        if (empty($kodeProduk)) {
            $kodeProduk = $produk->kode_produk ?: KantinProduk::generateKodeProduk($request->toko);
            $request->merge(['kode_produk' => $kodeProduk]);
        }

        $request->validate([
            'toko'        => ['required', 'in:kantin_1,kantin_2,aqumart'],
            'nama_produk' => ['required', 'string', 'max:255'],
            'kode_produk' => ['required', 'string', 'max:50', 'unique:kantin_produks,kode_produk,' . $id],
            'harga_modal' => ['required', 'numeric', 'min:0'],
            'harga_jual'  => ['required', 'numeric', 'min:0'],
            'stok'        => ['required', 'integer', 'min:0'],
            'deskripsi'   => ['nullable', 'string'],
            'foto'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $fotoPath = $produk->foto_produk;
        if ($request->hasFile('foto')) {
            if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto')->store('produk-kantin', 'public');
        }

        $produk->update([
            'toko'        => $request->toko,
            'nama_produk' => $request->nama_produk,
            'kode_produk' => $kodeProduk,
            'harga_modal' => $request->harga_modal,
            'harga_jual'  => $request->harga_jual,
            'stok'        => $request->stok,
            'deskripsi'   => $request->deskripsi,
            'foto_produk' => $fotoPath,
            'is_active'   => $request->has('is_active') ? (bool)$request->is_active : $produk->is_active,
        ]);

        AdminActivityLogger::log(
            'update_produk',
            "Memperbarui produk '{$produk->nama_produk}'",
            'KantinProduk',
            $produk->id
        );

        return redirect()->route('admin.kantin.produk.index', ['toko' => $produk->toko])
            ->with('success', "Produk '{$produk->nama_produk}' berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $produk = KantinProduk::findOrFail($id);
        $nama = $produk->nama_produk;
        $toko = $produk->toko;

        if ($produk->foto_produk && Storage::disk('public')->exists($produk->foto_produk)) {
            Storage::disk('public')->delete($produk->foto_produk);
        }

        $produk->delete();

        AdminActivityLogger::log(
            'delete_produk',
            "Menghapus produk '{$nama}'",
            'KantinProduk',
            $id
        );

        return redirect()->route('admin.kantin.produk.index', ['toko' => $toko])
            ->with('success', "Produk '{$nama}' berhasil dihapus.");
    }
}
