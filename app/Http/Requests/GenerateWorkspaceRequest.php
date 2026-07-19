<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'template'          => 'required|image|mimes:png,jpg,jpeg,webp,gif,bmp',
            'csv_file'          => 'required|file|mimes:csv,txt',
            'x_pos'             => 'required|numeric',
            'y_pos'             => 'required|numeric',
            'format'            => 'required|in:png,jpg,pdf',
            'font_scale'        => 'required|numeric|min:25|max:300',
            'resolution_scale'  => 'required|numeric|min:25|max:300',
            'font_family'       => 'nullable|string',
            'row_start'         => 'nullable|integer|min:1',
            'row_end'           => 'nullable|integer|min:1',
            'row_exclude'       => 'nullable|string',
            'use_paper'         => 'nullable|string|in:y,n',
            'paper_size'        => 'nullable|string|in:A4,F4',
            'paper_orientation' => 'nullable|string|in:auto,L,P',
            'fit_mode'          => 'nullable|string|in:full,smaller',
            'margin'            => 'nullable|numeric|min:0|max:5',
            'img_x'             => 'nullable|numeric',
            'img_y'             => 'nullable|numeric',
            'img_w'             => 'nullable|numeric',
            'img_h'             => 'nullable|numeric',
            'progress_id'       => 'nullable|string',
        ];
    }
}
