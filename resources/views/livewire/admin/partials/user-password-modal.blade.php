<x-modal show="showPasswordForm" max-width="sm" title="Ganti Kata Sandi">
  <form wire:submit="savePassword" class="space-y-4">
    <x-password-input name="newPassword" label="Kata Sandi Baru" autocomplete="new-password" />
    <x-password-input name="newPasswordConfirmation" label="Konfirmasi Kata Sandi Baru" autocomplete="new-password" />
    <x-form-actions show="showPasswordForm" />
  </form>
</x-modal>
