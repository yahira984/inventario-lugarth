<?php

namespace App\Observers;

use App\Models\Material;
use App\Support\VisualEmbeddingService;
use App\Support\VisualImageDescriptor;

class MaterialObserver
{
    public function __construct(
        private readonly VisualImageDescriptor $visualDescriptor,
        private readonly VisualEmbeddingService $visualAi
    ) {}

    public function created(Material $material): void
    {
        if (filled($material->fotografia)) {
            $this->visualDescriptor->forMaterial($material, true);
            $this->indexWithAi($material);
        }
    }

    public function updated(Material $material): void
    {
        if ($material->wasChanged('fotografia')) {
            $this->visualDescriptor->forMaterial($material, true);
            $this->indexWithAi($material);
        }
    }

    private function indexWithAi(Material $material): void
    {
        if (app()->runningUnitTests() || ! $this->visualAi->isReady()) {
            return;
        }

        $this->visualAi->indexMaterial($material);
    }
}
