<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use App\Models\VisualSearchFeedback;
use App\Support\VisualConfidenceCalibrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisualConfidenceCalibratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_labels_a_score_as_similarity_until_there_are_human_validations(): void
    {
        $result = app(VisualConfidenceCalibrator::class)->estimate(0.82);

        $this->assertFalse($result['calibrated']);
        $this->assertSame('Similitud del motor', $result['label']);
        $this->assertSame(82.0, $result['value']);
    }

    public function test_it_calibrates_the_score_with_nearby_human_feedback(): void
    {
        $user = User::factory()->create();
        $material = Material::create([
            'descripcion' => 'Pieza para calibracion',
            'stock' => 1,
            'es_plantilla_equipo' => false,
        ]);

        foreach (range(1, 8) as $index) {
            VisualSearchFeedback::create([
                'user_id' => $user->id,
                'suggested_material_id' => $material->id,
                'was_correct' => $index <= 6,
                'confidence' => 0.81,
            ]);
        }

        $result = app(VisualConfidenceCalibrator::class)->estimate(0.82);

        $this->assertTrue($result['calibrated']);
        $this->assertSame('Confianza validada', $result['label']);
        $this->assertSame(8, $result['samples']);
        $this->assertGreaterThan(60, $result['value']);
        $this->assertLessThan(100, $result['value']);
    }
}
