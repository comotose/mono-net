<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'notify_on_message' => ['nullable', 'boolean'],
            'notify_on_follow' => ['nullable', 'boolean'],
            'notify_on_like' => ['nullable', 'boolean'],
            'notify_on_comment' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'имя',
            'email' => 'email',
            'bio' => 'о себе',
            'avatar' => 'аватар',
            'notify_on_message' => 'уведомления о сообщениях',
            'notify_on_follow' => 'уведомления о подписках',
            'notify_on_like' => 'уведомления о лайках',
            'notify_on_comment' => 'уведомления о комментариях',
        ];
    }
}
