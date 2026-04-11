<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:60',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'bio' => ['nullable', 'string', 'max:600'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:3072'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
            'social_links' => ['nullable', 'array'],
            'social_links.website' => ['nullable', 'url', 'max:255'],
            'social_links.x' => ['nullable', 'url', 'max:255'],
            'social_links.github' => ['nullable', 'url', 'max:255'],
            'social_links.linkedin' => ['nullable', 'url', 'max:255'],
            'social_links.youtube' => ['nullable', 'url', 'max:255'],
            'topic_badges' => ['nullable', 'string', 'max:300'],
            'pinned_posts' => ['nullable', 'array', 'max:3'],
            'pinned_posts.*' => [
                'integer',
                Rule::exists('posts', 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()->id)
                    ->where('is_public', true)),
            ],
        ];
    }
}
