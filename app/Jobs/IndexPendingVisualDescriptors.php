<?php

namespace App\Jobs;

use App\Support\VisualIndexMaintenanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IndexPendingVisualDescriptors implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public int $tries = 2;

    public function __construct(public readonly int $limit = 15) {}

    public function handle(VisualIndexMaintenanceService $maintenance): void
    {
        $maintenance->process($this->limit);
    }
}
