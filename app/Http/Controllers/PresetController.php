<?php

namespace App\Http\Controllers;

use App\Company;
use App\Http\Requests\PresetRequest;
use App\Preset;
use Illuminate\Http\Request;

class PresetController extends Controller
{

    public function index()
    {
        $presets = Preset::all();

        return view('preset.index', [
            'presets' => $presets
        ]);
    }

    public function create()
    {
        return view('preset.create');
    }

    public function show(Preset $preset)
    {
        return view('preset.edit', compact('preset'));
    }

    public function store(PresetRequest $request)
    {
        $preset = Preset::create($request->validated());

        if ($preset) {
            return response()->json([
                'preset' => $preset,
                'status' => true
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to create preset'
        ]);
    }

    public function edit(Preset $preset)
    {
        return view('preset.edit', compact('preset'));
    }

    public function update(PresetRequest $request, Preset $preset)
    {
        $preset->update($request->validated());

        return response()->json([
            'preset' => $preset,
            'status' => true
        ]);
    }

    public function destroy(Preset $preset)
    {
        $preset->jobs()->detach();
        $preset->delete();

        session()->flash('status', 'Preset successfully deleted');

        return redirect(route('presets'));
    }
}
