<?php

namespace App\Observers;

use App\Models\Material;
use App\Support\VisualImageDescriptor;

class MaterialObserver
{
    public function __construct(private readonly VisualImageDescriptor $visualDescriptor) {}

    public function created(Material $material): void
    {
        if (filled($material->fotografia)) {
            $this->visualDescriptor->forMaterial($material, true);
        }
    }

    public function updated(Material $material): void
    {
        if ($material->wasChanged('fotografia')) {
            $this->visualDescriptor->forMaterial($material, true);
        }
    }
}
