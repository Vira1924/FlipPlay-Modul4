<?php

namespace App\Http\Requests;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
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
        $isUpdate = $this->isMethod('PATCH') || $this->isMethod('PUT');
        $media = $this->route('media');
        $mediaId = $media instanceof Media ? $media->id : $media;

        return [
            'title' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('media', 'title')->ignore($mediaId),
            ],
            'type' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:100',
            ],
            'genre' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'episodes' => ['nullable', 'integer', 'min:0'],
            'format' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul media wajib diisi',
            'title.string' => 'Judul media harus berupa teks',
            'title.max' => 'Judul media maksimal 255 karakter',
            'title.unique' => 'Judul media sudah terdaftar',
            'type.required' => 'Jenis media wajib diisi',
            'type.string' => 'Jenis media harus berupa teks',
            'episodes.integer' => 'Jumlah episode harus berupa angka',
            'episodes.min' => 'Jumlah episode tidak boleh kurang dari 0',
        ];
    }
}
