<?php

namespace App\Http\Controllers;

use App\Models\Laboratorium;

class LabController extends Controller
{
    public function show(Laboratorium $lab)
    {
        $lab->load(['alat.unitAlat', 'bahan']);

        return view('lab.show', compact('lab'));
    }
}
