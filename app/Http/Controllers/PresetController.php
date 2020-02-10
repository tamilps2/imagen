<?php

namespace App\Http\Controllers;

use App\Company;
use App\Http\Requests\PresetRequest;
use App\Preset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class PresetController extends Controller
{
    public function previewWatermark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'width' => 'required',
            'height' => 'required',
            'company' => 'required|exists:companies,id',
            'unit' => 'nullable',
            'position' => 'required',
            'xAxis' => Rule::requiredIf(function () use ($request) {
                return in_array($request->get('unit'), [
                    'px', 'percent'
                ]);
            }),
            'yAxis' => Rule::requiredIf(function () use ($request) {
                return in_array($request->get('unit'), [
                    'px', 'percent'
                ]);
            }),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }

        $watermarkWidth = config('imager.watermark.width', 100);
        $watermarkOpacity = config('imager.watermark.opacity', 100);

        $image = Image::canvas(request('width'), request('height'), '#cccccc');

        $company = Company::whereId(request('company'))->first();

        $watermark = Image::make($company->getCompanyLogo())
            ->resize($watermarkWidth, null, function ($constraint) {
                $constraint->aspectRatio();
            })->opacity($watermarkOpacity);

        if (request('unit') === 'auto') {
            $image->insert(
                $watermark,
                request('position')
            );
        } else if (request('unit') === 'percent') {
            $xAxis = round((((int)request('width') * (int)request('xAxis')) / 100));
            $yAxis = round((((int)request('height') * (int)request('yAxis')) / 100));

            $image->insert(
                $watermark,
                request('position'),
                $xAxis,
                $yAxis
            );
        } else {
            $image->insert(
                $watermark,
                request('position'),
                request('xAxis'),
                request('yAxis')
            );
        }

        return $image->response();
    }

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
