<?php

namespace App\Http\Requests;

use App\Preset;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PresetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        $positions = implode(',', Preset::availablePositions());
        $units = implode(',', Preset::availableUnits());

        return [
            'name' => 'required',
            'filename_pattern' => 'required|in:original,replace,append,prepend',
            'filename' => Rule::requiredIf(function () use ($request) {
                return in_array($request->get('filename_pattern'), [
                    'replace', 'append', 'prepend'
                ]);
            }),

            'generate_small_image' => 'required',
            'sm_width' => 'required_if:generate_small_image,true',
            'sm_height' => 'required_if:generate_small_image,true',
            'sm_watermark' => 'required|boolean',
            'sm_should_upload' => 'required|boolean',
            'sm_company_id' => [
                'bail',
                Rule::requiredIf(function () use ($request) {
                    return ($request->get('sm_watermark') || $request->get('sm_should_upload'));
                }),
                'nullable',
                'exists:companies,id'
            ],
            'sm_wm_position' => 'required_if:sm_watermark,true|nullable|in:' . $positions,
            'sm_wm_unit' => 'required_if:sm_watermark,true|nullable|in:' . $units,
            'sm_wm_x_axis' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->get('sm_wm_unit'), ['px', 'percent']);
                }),
                'nullable', 'numeric'
            ],
            'sm_wm_y_axis' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->get('sm_wm_unit'), ['px', 'percent']);
                }),
                'nullable', 'numeric'
            ],

            'generate_large_image' => 'required',
            'lg_width' => 'required_if:generate_large_image,true',
            'lg_height' => 'required_if:generate_large_image,true',
            'lg_watermark' => 'required|boolean',
            'lg_should_upload' => 'required|boolean',
            'lg_company_id' => [
                'bail',
                Rule::requiredIf(function () use ($request) {
                    return ($request->get('lg_watermark') || $request->get('lg_should_upload'));
                }),
                'nullable',
                'exists:companies,id'
            ],
            'lg_wm_position' => 'required_if:lg_watermark,true|nullable|in:' . $positions,
            'lg_wm_unit' => 'required_if:lg_watermark,true|nullable|in:' . $units,
            'lg_wm_x_axis' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->get('lg_wm_unit'), ['px', 'percent']);
                }),
                'nullable', 'numeric'
            ],
            'lg_wm_y_axis' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->get('lg_wm_unit'), ['px', 'percent']);
                }),
                'nullable', 'numeric'
            ],

            'generate_gif' => 'required',
            'gif_interval' => 'required_if:generate_gif,true|numeric',
            'gif_width' => 'required_if:generate_gif,true',
            'gif_height' => 'required_if:generate_gif,true',
            'gif_watermark' => 'required|boolean',
            'gif_should_upload' => 'required|boolean',
            'gif_company_id' => [
                'bail',
                Rule::requiredIf(function () use ($request) {
                    return ($request->get('gif_watermark') || $request->get('gif_should_upload'));
                }),
                'nullable',
                'exists:companies,id'
            ],
            'gif_wm_position' => 'required_if:gif_watermark,true|nullable|in:' . $positions,
            'gif_wm_unit' => 'required_if:gif_watermark,true|nullable|in:' . $units,
            'gif_wm_x_axis' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->get('gif_wm_unit'), ['px', 'percent']);
                }),
                'nullable', 'numeric'
            ],
            'gif_wm_y_axis' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->get('gif_wm_unit'), ['px', 'percent']);
                }),
                'nullable', 'numeric'
            ],

            'generate_video' => 'required',
            'video_fps' => 'required_if:generate_video,true|numeric',
            'video_width' => 'required_if:generate_video,true',
            'video_height' => 'required_if:generate_video,true',
            'video_watermark' => 'required|boolean',
            'video_should_upload' => 'required|boolean',
            'upload_to_youtube' => 'required|boolean',
            'video_company_id' => [
                'bail',
                Rule::requiredIf(function () use ($request) {
                    return (
                        $request->get('video_watermark') ||
                        $request->get('video_should_upload') ||
                        $request->get('upload_to_youtube')
                    );
                }),
                'nullable',
                'exists:companies,id'
            ],
            'video_wm_position' => 'required_if:video_watermark,true|nullable|in:' . $positions,
            'video_wm_unit' => 'required_if:video_watermark,true|nullable|in:' . $units,
            'video_wm_x_axis' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->get('video_wm_unit'), ['px', 'percent']);
                }),
                'nullable', 'numeric'
            ],
            'video_wm_y_axis' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->get('video_wm_unit'), ['px', 'percent']);
                }),
                'nullable', 'numeric'
            ],
        ];
    }

    public function messages()
    {
        return [
            'sm_width.required_if' => 'Please provide the width.',
            'sm_height.required_if' => 'Please provide the Height.',
            'sm_company_id.required' => 'Please choose the company.',
            'sm_company_id.required_if' => 'You need to choose the company logo to watermark.',
            'sm_company_id.exists' => 'The given company does not exists.',
            'sm_wm_position.required_if' => 'Choose the position to place the watermark.',
            'sm_wm_position.in' => 'Invalid position value.',
            'sm_wm_unit.required_if' => 'Choose the watermark offset unit.',
            'sm_wm_x_axis.required_if' => 'Provide x offset for the watermark as per unit.',
            'sm_wm_y_axis.required_if' => 'Provide y offset for the watermark as per unit.',

            'lg_width.required_if' => 'Please provide the width.',
            'lg_height.required_if' => 'Please provide the Height.',
            'lg_company_id.required' => 'Please choose the company.',
            'lg_company_id.required_if' => 'You need to choose the company logo to watermark.',
            'lg_company_id.exists' => 'The given company does not exists.',
            'lg_wm_position.required_if' => 'Choose the position to place the watermark.',
            'lg_wm_position.in' => 'Invalid position value.',
            'lg_wm_unit.required_if' => 'Choose the watermark offset unit.',
            'lg_wm_x_axis.required_if' => 'Provide x offset for the watermark as per unit.',
            'lg_wm_y_axis.required_if' => 'Provide y offset for the watermark as per unit.',

            'gif_width.required_if' => 'Please provide the width.',
            'gif_height.required_if' => 'Please provide the Height.',
            'gif_interval.required_if' => 'Please provide the gif interval',
            'gif_interval.numeric' => 'The interval value must be numeric',
            'gif_company_id.required' => 'Please choose the company.',
            'gif_company_id.required_if' => 'You need to choose the company logo to watermark.',
            'gif_company_id.exists' => 'The given company does not exists.',
            'gif_wm_position.required_if' => 'Choose the position to place the watermark.',
            'gif_wm_position.in' => 'Invalid position value.',
            'gif_wm_unit.required_if' => 'Choose the watermark offset unit.',
            'gif_wm_x_axis.required_if' => 'Provide x offset for the watermark as per unit.',
            'gif_wm_y_axis.required_if' => 'Provide y offset for the watermark as per unit.',

            'video_width.required_if' => 'Please provide the width.',
            'video_height.required_if' => 'Please provide the Height.',
            'video_fps.required_if' => 'Please provide the video fps',
            'video_fps.numeric' => 'The fps value must be numeric',
            'video_company_id.required' => 'Please choose the company.',
            'video_company_id.required_if' => 'You need to choose the company logo to watermark.',
            'video_company_id.exists' => 'The given company does not exists.',
            'video_wm_position.required_if' => 'Choose the position to place the watermark.',
            'video_wm_position.in' => 'Invalid position value.',
            'video_wm_unit.required_if' => 'Choose the watermark offset unit.',
            'video_wm_x_axis.required_if' => 'Provide x offset for the watermark as per unit.',
            'video_wm_y_axis.required_if' => 'Provide y offset for the watermark as per unit.',
        ];
    }
}