<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\RolePermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Hak Akses')]
#[Layout('components.layouts.admin')]
class AccessControl extends Component
{
	use HandlesAdminActions;

	public array $matrix = [];

	public function mount(): void
	{
		Gate::authorize('manage_users');
		$this->loadMatrix();
	}

	private function loadMatrix(): void
	{
		$stored = RolePermission::matrix();

		$this->matrix = [];
		foreach ($this->editableRoles() as $role) {
			$this->matrix[$role->value] = [];
			foreach (Permission::configurable() as $permission) {
				$this->matrix[$role->value][$permission->value] =
					isset($stored[$role->value][$permission->value]);
			}
		}
	}

	public function toggle(string $role, string $permission): void
	{
		if (isset($this->matrix[$role][$permission])) {
			$this->matrix[$role][$permission] = ! $this->matrix[$role][$permission];
		}
	}

	public function save(): void
	{
		$this->safeAction(function () {
			DB::transaction(function () {
				RolePermission::whereIn('role', array_map(fn($r) => $r->value, $this->editableRoles()))->delete();

				$rows = [];
				$now = now();
				foreach ($this->matrix as $role => $permissions) {
					foreach ($permissions as $permission => $allowed) {
						if ($allowed) {
							$rows[] = [
								'role' => $role,
								'permission' => $permission,
								'created_at' => $now,
								'updated_at' => $now,
							];
						}
					}
				}

				if ($rows) {
					RolePermission::insert($rows);
				}
			});

			$this->logActivity('access_control.updated', 'Mengubah konfigurasi hak akses peran', null, ['matrix' => $this->matrix]);
			$this->toast('success', 'Konfigurasi hak akses berhasil disimpan.');
		}, permission: 'manage_users', genericError: 'Gagal menyimpan konfigurasi hak akses.');
	}

	/** Roles that can be configured (Admin always has all, Employee has none). */
	private function editableRoles(): array
	{
		return [UserRole::HR];
	}

	public function render(): mixed
	{
		return view('livewire.admin.access-control', [
			'permissions' => Permission::configurable(),
			'editableRoles' => $this->editableRoles(),
		]);
	}
}
