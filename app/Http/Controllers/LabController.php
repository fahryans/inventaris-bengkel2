<?php

namespace App\Http\Controllers;

use App\Models\Laboratorium;

class LabController extends Controller
{
    public function show(Laboratorium $lab)
    {
        $lab->load(['teknisi', 'kalab']);
        $alat = $lab->alat()->with('unitAlat')->paginate(10);
        $bahan = $lab->bahan()->paginate(10);

        return view('lab.show', compact('lab', 'alat', 'bahan'));
    }
}
