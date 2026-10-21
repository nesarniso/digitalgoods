<?php

namespace App\Domain\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['uuid', 'name', 'country', 'division', 'district', 'area', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    public function specificity(): int
    {
        return collect([$this->country, $this->division, $this->district, $this->area])
            ->filter(fn ($v) => $v !== null)
            ->count();
    }

    public function matchesAddress(string $country, ?string $division, ?string $district, ?string $area): bool
    {
        if ($this->country !== null && ! $this->sameValue($this->country, $country)) {
            return false;
        }

        if ($this->division !== null && ! $this->sameValue($this->division, $division)) {
            return false;
        }

        if ($this->district !== null && ! $this->matchesAnyValue($this->district, $district)) {
            return false;
        }

        if ($this->area !== null && ! $this->sameValue($this->area, $area)) {
            return false;
        }

        return true;
    }

    private function sameValue(?string $configured, ?string $submitted): bool
    {
        return $submitted !== null
            && mb_strtolower(trim($configured ?? '')) === mb_strtolower(trim($submitted));
    }

    private function matchesAnyValue(?string $configured, ?string $submitted): bool
    {
        return collect(explode(',', (string) $configured))
            ->contains(fn (string $value) => $this->sameValue($value, $submitted));
    }
}
