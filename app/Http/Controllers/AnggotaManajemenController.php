<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AnggotaExport;

class AnggotaManajemenController extends Controller
{
    // PENGURUS: Menampilkan semua anggota
    public function index()
    {
        $anggota = User::where('role', 'anggota')
            ->select('id', 'nama', 'no_telepon', 'nip', 'tempat_lahir', 'tanggal_lahir', 'alamat_rumah', 'unit_kerja', 'sk_perjanjian_kerja', 'status')
            ->get();
        return response()->json($anggota);
    }

    // PENGURUS: Tambah anggota
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_telepon' => 'required|string|unique:users',
            'password' => 'required|min:6',
            'nip' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'alamat_rumah' => 'nullable|string',
            'unit_kerja' => 'nullable|string',
            'sk_perjanjian_kerja' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'anggota';
        $validated['status'] = 'aktif';

        $anggota = User::create($validated);

        return response()->json(['message' => 'Anggota berhasil ditambahkan', 'data' => $anggota]);
    }

    // PENGURUS: Edit anggota
    public function update(Request $request, $id)
    {
        $anggota = User::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'no_telepon' => 'sometimes|string|unique:users,no_telepon,' . $id,
            'password' => 'nullable|min:6',
            'nip' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'alamat_rumah' => 'nullable|string',
            'unit_kerja' => 'nullable|string',
            'sk_perjanjian_kerja' => 'nullable|string',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $anggota->update($validated);

        return response()->json(['message' => 'Data anggota diperbarui', 'data' => $anggota]);
    }

    // PENGURUS: Hapus anggota
    public function destroy($id)
    {
        $anggota = User::findOrFail($id);
        $anggota->delete();

        return response()->json(['message' => 'Anggota dihapus']);
    }

    // PENGAWAS: Lihat detail anggota
    public function show($id)
    {
        $anggota = User::findOrFail($id);
        return response()->json($anggota);
    }

    // PENGAWAS: Export Excel
    // public function exportExcel()
    // {
    //     return Excel::download(new AnggotaExport, 'data_anggota.xlsx');
    // }

    // // PENGAWAS: Export PDF
    // public function exportPDF()
    // {
    //     $anggota = User::all();
    //     $pdf = Pdf::loadView('pdf.anggota', compact('anggota'));
    //     return $pdf->download('data_anggota.pdf');
    // }
}
