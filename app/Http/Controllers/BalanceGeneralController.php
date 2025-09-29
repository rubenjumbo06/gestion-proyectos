<?php

namespace App\Http\Controllers;

use App\Models\BalanceGeneral;
use Illuminate\Http\Request;

class BalanceGeneralController extends Controller
{
    public function index()
    {
        $balanceGenerals = BalanceGeneral::all();
        return view('balance_generals.index', compact('balanceGenerals'));
    }

    public function create()
    {
        return view('balance_generals.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'total_servicios' => 'required|numeric|min:0',
            'egresos' => 'required|numeric|min:0',
        ]);

        BalanceGeneral::create($request->all());
        return redirect()->route('balance_generals.index')->with('success', 'Balance general creado exitosamente.');
    }

    public function show(BalanceGeneral $balanceGeneral)
    {
        return view('balance_generals.show', compact('balanceGeneral'));
    }

    public function edit(BalanceGeneral $balanceGeneral)
    {
        return view('balance_generals.edit', compact('balanceGeneral'));
    }

    public function update(Request $request, BalanceGeneral $balanceGeneral)
    {
        $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'total_servicios' => 'required|numeric|min:0',
            'egresos' => 'required|numeric|min:0',
        ]);

        $balanceGeneral->update($request->all());
        return redirect()->route('balance_generals.index')->with('success', 'Balance general actualizado exitosamente.');
    }

    public function destroy(BalanceGeneral $balanceGeneral)
    {
        $balanceGeneral->delete();
        return redirect()->route('balance_generals.index')->with('success', 'Balance general eliminado exitosamente.');
    }
}
