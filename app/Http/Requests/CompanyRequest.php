<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class CompanyRequest extends FormRequest
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
        return [
            'name' => 'required',
            'logo' => [
                ($request->route('company') ? 'sometimes' : 'required'),
                'image'
            ],
            'ftp_host' => 'sometimes|nullable',
            'ftp_username' => Rule::requiredIf(function () use ($request) {
                return !empty($request->ftp_host);
            }),
            'ftp_password' => Rule::requiredIf(function () use ($request) {
                return !empty($request->ftp_host);
            }),
            'ftp_upload_path' => 'sometimes|nullable',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Company name is required.',
            'logo.required' => 'Choose a company logo.',
            'logo.image' => 'The logo must be an image.'
        ];
    }
}
