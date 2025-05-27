<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class AnggotaExport implements FromCollection
{
    public function collection()
    {
        return User::where('role', 'anggota')->get();
    }
}
