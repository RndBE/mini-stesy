<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
            // 'email' => ['required', 'string', 'email'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        // $this->ensureIsNotRateLimited();

        // if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
        //     RateLimiter::hit($this->throttleKey());

        //     throw ValidationException::withMessages([
        //         'email' => trans('auth.failed'),
        //     ]);
        // }

        // RateLimiter::clear($this->throttleKey());
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        // Cek status akun setelah berhasil login
        $user = Auth::user();
        if ($user) {
            $status = strtolower((string) ($user->status ?? 'aktif'));

            if ($status === 'non-aktif') {
                Auth::logout();
                // Flash ke session agar modal muncul di halaman login
                session()->flash('nonaktif_reason', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator untuk informasi lebih lanjut.');
                session()->flash('nonaktif_username', $this->input('username'));
                throw ValidationException::withMessages([
                    'username' => 'Akun Anda tidak aktif.',
                ]);
            }

            if ($status === 'suspend') {
                $reason = $user->suspend_reason ?? 'Akun Anda sedang di-suspend. Harap selesaikan administrasi terlebih dahulu.';
                Auth::logout();
                // Simpan pesan suspend ke session agar bisa ditampilkan di modal
                session()->flash('suspended_reason', $reason);
                session()->flash('suspended_username', $this->input('username'));
                throw ValidationException::withMessages([
                    'username' => 'Akun Anda sedang di-suspend.',
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        // return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
        return Str::transliterate(Str::lower($this->string('username')) . '|' . $this->ip());
    }
}
