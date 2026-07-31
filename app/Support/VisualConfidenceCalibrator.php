<?php

namespace App\Support;

use App\Models\VisualSearchFeedback;

class VisualConfidenceCalibrator
{
    private const MINIMUM_SAMPLES = 8;

    /**
     * Convierte una similitud del motor en una probabilidad empirica cuando ya
     * existen suficientes respuestas humanas cercanas a ese mismo puntaje.
     *
     * @return array{value:float,calibrated:bool,samples:int,label:string}
     */
    public function estimate(float $rawScore): array
    {
        $raw = max(0.0, min(1.0, $rawScore));
        $feedback = VisualSearchFeedback::query()
            ->whereNotNull('confidence')
            ->latest()
            ->limit(1200)
            ->get(['confidence', 'was_correct']);
        $nearby = $feedback->filter(function (VisualSearchFeedback $item) use ($raw): bool {
            return abs((float) $item->confidence - $raw) <= 0.075;
        });
        $samples = $nearby->count();

        if ($samples < self::MINIMUM_SAMPLES) {
            return [
                'value' => round($raw * 100, 1),
                'calibrated' => false,
                'samples' => $samples,
                'label' => 'Similitud del motor',
            ];
        }

        // Suavizado bayesiano: evita afirmar 100% con pocas confirmaciones.
        $correct = $nearby->where('was_correct', true)->count();
        $value = (($correct + 3) / ($samples + 6)) * 100;

        return [
            'value' => round($value, 1),
            'calibrated' => true,
            'samples' => $samples,
            'label' => 'Confianza validada',
        ];
    }
}
