<?php

namespace App\Http\Controllers;

use App\Models\UnsurPenilaian;
use Illuminate\Http\Request;

class UnsurPenilaianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil data dengan parent relasi, urutkan berdasarkan kode nomor
        $unsurPenilaians = UnsurPenilaian::with('parent')
                            ->orderBy('kode_nomor', 'asc')
                            ->paginate(10);

        return view('pages.unsur-penilaian.index', compact('unsurPenilaians'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil hanya unsur yang merupakan header untuk dijadikan parent
        $parents = UnsurPenilaian::where('is_header', true)->get();
        
        return view('pages.unsur-penilaian.create', compact('parents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'kode_nomor' => 'required|string|max:10',
            'nama_unsur' => 'required|string',
            'parent_id' => 'nullable|exists:ms_unsur_penilaians,id',
            'is_header' => 'boolean', // Checkbox mengirim 1/0 atau on/off -> perlu handling view
        ]);

        // Checkbox handling: jika tidak diceklis, nilai null atau false tergantung HTML
        // Di blade: <input type="checkbox" name="is_header" value="1">
        // Validasi boolean: nilai "1", "true", "on", dan "yes" dianggap true.
        // Jika tidak dikirim (unchecked), request->boolean('is_header') akan false (bagus).

        $validatedData['is_header'] = $request->has('is_header');

        UnsurPenilaian::create($validatedData);

        return redirect()->route('unsur-penilaian.index')
                        ->with('success', 'Unsur Penilaian berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(UnsurPenilaian $unsurPenilaian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnsurPenilaian $unsurPenilaian)
    {
        $parents = UnsurPenilaian::where('is_header', true)
                                ->where('id', '!=', $unsurPenilaian->id) // Hindari self-parenting
                                ->get();

        return view('pages.unsur-penilaian.edit', compact('unsurPenilaian', 'parents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnsurPenilaian $unsurPenilaian)
    {
        $validatedData = $request->validate([
            'kode_nomor' => 'required|string|max:10',
            'nama_unsur' => 'required|string',
            'parent_id' => 'nullable|exists:ms_unsur_penilaians,id',
        ]);

        $validatedData['is_header'] = $request->has('is_header');

        $unsurPenilaian->update($validatedData);

        return redirect()->route('unsur-penilaian.index')
                        ->with('success', 'Unsur Penilaian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnsurPenilaian $unsurPenilaian)
    {
        // Cek apakah punya children? Jika cascade delete diatur di migration, aman.
        // Tapi validasi logic juga bagus.
        if ($unsurPenilaian->children()->exists()) {
             return back()->with('error', 'Gagal hapus: Unsur ini memiliki sub-unsur (children).');
        }

        $unsurPenilaian->delete();

        return redirect()->route('unsur-penilaian.index')
                        ->with('success', 'Unsur Penilaian berhasil dihapus.');
    }
}
