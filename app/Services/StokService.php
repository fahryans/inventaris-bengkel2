<?php

namespace App\Services;

use App\Models\UnitAlat;

class StokService
{
    public function updateUnitStatus(UnitAlat $unit, string $status): bool
    {
        if (!in_array($status, ['tersedia', 'dipinjam', 'rusak', 'maintenance'])) {
            throw new \Exception('Status unit tidak valid');
        }

        $unit->update(['status' => $status]);

        return true;
    }

    public function getAvailableAlatQuantity(Alat $alat): int
    {
        return $alat->getAvailableQuantity();
    }
}
