<?php

namespace App\Livewire\Employee\Profile;

use App\Models\WebauthnCredential;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Sidik Jari')]
class Fingerprint extends Component
{
    public bool $hasFingerprint = false;

    public string $deviceName = '';

    public function mount(): void
    {
        $user = auth()->user();
        $credential = WebauthnCredential::where('user_id', $user->id)->first();
        $this->hasFingerprint = $credential !== null;
        $this->deviceName = $credential?->device_name ?? '';
    }

    /** @return array<string, mixed> */
    public function getRegistrationOptions(): array
    {
        $user = auth()->user();
        $challenge = $this->base64urlEncode(random_bytes(32));
        session(['webauthn_reg_challenge' => $challenge]);

        return [
            'challenge' => $challenge,
            'rp' => [
                'name' => config('app.name'),
                'id' => parse_url(config('app.url'), PHP_URL_HOST),
            ],
            'user' => [
                'id' => $this->base64urlEncode((string) $user->id),
                'name' => $user->email,
                'displayName' => $user->name,
            ],
            'pubKeyCredParams' => [
                ['alg' => -7, 'type' => 'public-key'],
                ['alg' => -257, 'type' => 'public-key'],
            ],
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'userVerification' => 'required',
                'requireResidentKey' => false,
            ],
            'timeout' => 60000,
            'attestation' => 'none',
        ];
    }

    public function storeCredential(string $credentialId, string $deviceName = 'Perangkat ini'): void
    {
        $user = auth()->user();

        WebauthnCredential::where('user_id', $user->id)->delete();

        WebauthnCredential::create([
            'user_id' => $user->id,
            'credential_id' => $credentialId,
            'device_name' => $deviceName ?: 'Perangkat ini',
        ]);

        $this->hasFingerprint = true;
        $this->deviceName = $deviceName ?: 'Perangkat ini';

        $this->dispatch('notify', type: 'success', message: 'Sidik jari berhasil didaftarkan.');
    }

    public function deleteCredential(): void
    {
        $user = auth()->user();

        WebauthnCredential::where('user_id', $user->id)->delete();

        $this->hasFingerprint = false;
        $this->deviceName = '';

        $this->dispatch('notify', type: 'success', message: 'Sidik jari dihapus.');
    }

    public function render(): mixed
    {
        return view('livewire.employee.profile.fingerprint');
    }

    private function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
