<?php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'email' => 'required|email|string|exists:users,email',
            'password' => 'required|string|min:8',
            'device_name' => 'nullable|string', // token name
        ];
    }
}
