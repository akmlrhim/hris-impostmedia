<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Models\OfficeLocation as OfficeLocationModel;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Lokasi Kantor')]
#[Layout('components.layouts.admin')]
class OfficeLocation extends Component
{
	use HandlesAdminActions, WithPagination;

	public bool $showForm = false;

	public ?int $editingId = null;

	public string $name = '';

	public string $address = '';

	public string $latitude = '';

	public string $longitude = '';

	public int $radius_meters = 100;

	public bool $is_active = true;

	public function rules(): array
	{
		return [
			'name' => 'required|string|max:100',
			'address' => 'nullable|string|max:255',
			'latitude' => 'required|numeric|between:-90,90',
			'longitude' => 'required|numeric|between:-180,180',
			'radius_meters' => 'required|integer|min:10|max:5000',
			'is_active' => 'boolean',
		];
	}

	public function open(?int $id = null): void
	{
		$this->reset(['name', 'address', 'latitude', 'longitude', 'radius_meters', 'is_active', 'editingId']);
		$this->resetValidation();
		$this->radius_meters = 100;
		$this->is_active = true;

		if ($id) {
			$location = OfficeLocationModel::findOrFail($id);
			$this->editingId = $id;
			$this->name = $location->name;
			$this->address = (string) $location->address;
			$this->latitude = (string) $location->latitude;
			$this->longitude = (string) $location->longitude;
			$this->radius_meters = (int) $location->radius_meters;
			$this->is_active = $location->is_active;
		}

		$this->showForm = true;
	}

	public function save(): void
	{
		$data = $this->validate();

		$this->safeAction(function () use ($data) {
			if ($this->editingId) {
				OfficeLocationModel::findOrFail($this->editingId)->update($data);
				$this->toast('success', 'Lokasi kantor diperbarui.');
			} else {
				OfficeLocationModel::create($data);
				$this->toast('success', 'Lokasi kantor ditambahkan.');
			}

			$this->showForm = false;
			$this->editingId = null;
		}, permission: 'manage_office_locations', genericError: 'Gagal menyimpan lokasi kantor.');
	}

	public function toggleActive(int $id): void
	{
		$this->safeAction(function () use ($id) {
			$location = OfficeLocationModel::findOrFail($id);
			$location->update(['is_active' => ! $location->is_active]);
			$this->toast('success', $location->is_active ? 'Lokasi diaktifkan.' : 'Lokasi dinonaktifkan.');
		}, permission: 'manage_office_locations', genericError: 'Gagal mengubah status lokasi.');
	}

	public function delete(int $id): void
	{
		$this->safeAction(function () use ($id) {
			$loc = OfficeLocationModel::findOrFail($id);
			$snapshot = $loc->only(['id', 'name', 'latitude', 'longitude']);
			$loc->delete();
			$this->logActivity('office_location.deleted', "Menghapus lokasi kantor {$snapshot['name']}", null, $snapshot);
			$this->toast('success', 'Lokasi kantor dihapus.');
		}, permission: 'manage_office_locations', genericError: 'Gagal menghapus lokasi kantor.');
	}

	public function mount(): void
	{
		Gate::authorize('manage_office_locations');
	}

	public function render(): mixed
	{
		return view('livewire.admin.office-location', [
			'locations' => OfficeLocationModel::orderBy('name')->paginate(15),
		]);
	}
}
