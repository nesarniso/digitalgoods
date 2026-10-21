<?php

namespace App\Domain\Users\Services;

use App\Domain\Users\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AddressService
{
    public function create(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            $isDefault = $data['is_default'] ?? false;

            if ($isDefault) {
                $this->unsetExistingDefault($user);
            }

            if (! $isDefault && ! Address::query()->where('user_id', $user->id)->exists()) {
                $isDefault = true;
            }

            return Address::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'type' => $data['type'] ?? 'both',
                'label' => $data['label'] ?? null,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'country' => $data['country'] ?? 'Bangladesh',
                'division' => $data['division'] ?? null,
                'district' => $data['district'] ?? null,
                'upazila' => $data['upazila'] ?? null,
                'area' => $data['area'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'address_line' => $data['address_line'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'is_default' => $isDefault,
            ]);
        });
    }

    public function update(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data) {
            if (($data['is_default'] ?? false) && ! $address->is_default) {
                $this->unsetExistingDefault($address->user);
            }

            if (array_key_exists('is_default', $data) && ! $data['is_default'] && $address->is_default) {
                unset($data['is_default']);
            }

            $address->update($data);

            return $address;
        });
    }

    public function delete(Address $address): void
    {
        if ($address->is_default && Address::query()->where('user_id', $address->user_id)->count() > 1) {
            throw ValidationException::withMessages([
                'address' => ['Set another address as default before deleting this one.'],
            ]);
        }

        $address->delete();
    }

    private function unsetExistingDefault(User $user): void
    {
        Address::query()->where('user_id', $user->id)->where('is_default', true)->update(['is_default' => false]);
    }
}
