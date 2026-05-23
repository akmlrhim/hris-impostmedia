<?php

namespace App\Concerns;

use App\Models\ActivityLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Mixin for admin Livewire components. Provides a `safeAction()` wrapper
 * that converts authorization failures and unhandled exceptions into toast
 * notifications instead of breaking the page.
 */
trait HandlesAdminActions
{
    protected function toast(string $type, string $message): void
    {
        $this->dispatch('notify', type: $type, message: $message);
    }

    /**
     * Wrap an action body. If $permission is given, re-checks the gate first.
     * Returns the closure's return value on success, or null when blocked/failed.
     */
    protected function safeAction(\Closure $action, ?string $permission = null, string $genericError = 'Terjadi kesalahan, coba lagi.'): mixed
    {
        try {
            if ($permission !== null && Gate::denies($permission)) {
                $this->toast('danger', 'Anda tidak memiliki akses untuk tindakan ini.');

                return null;
            }

            return $action();
        } catch (AuthorizationException) {
            $this->toast('danger', 'Anda tidak memiliki akses untuk tindakan ini.');

            return null;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Admin action failed', [
                'component' => static::class,
                'message' => $e->getMessage(),
            ]);
            $this->toast('danger', $genericError);

            return null;
        }
    }

    /**
     * Catat aktivitas sensitif ke activity_logs.
     *
     * @param  array<string, mixed>  $properties
     */
    protected function logActivity(
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = []
    ): void {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'description' => $description,
                'properties' => $properties ?: null,
                'ip_address' => request()->ip(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Gagal menulis activity log', [
                'message' => $e->getMessage(),
                'action' => $action,
            ]);
        }
    }
}
