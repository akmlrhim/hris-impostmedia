<?php

namespace App\Livewire\Admin;

use App\Models\OfficeLocation as OfficeLocationModel;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class OfficeLocation extends Component
{
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

        if ($this->editingId) {
            OfficeLocationModel::findOrFail($this->editingId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'Lokasi kantor diperbarui.');
        } else {
            OfficeLocationModel::create($data);
            $this->dispatch('notify', type: 'success', message: 'Lokasi kantor ditambahkan.');
        }

        $this->showForm = false;
        $this->editingId = null;
    }

    public function toggleActive(int $id): void
    {
        $location = OfficeLocationModel::findOrFail($id);
        $location->update(['is_active' => ! $location->is_active]);
        $this->dispatch('notify', type: 'success', message: $location->is_active ? 'Lokasi diaktifkan.' : 'Lokasi dinonaktifkan.');
    }

    public function delete(int $id): void
    {
        OfficeLocationModel::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Lokasi kantor dihapus.');
    }

    public function render(): mixed
    {
        return view('livewire.admin.office-location', [
            'locations' => OfficeLocationModel::orderBy('name')->get(),
        ]);
    }
}
