<?php

namespace App\Console\Commands;

use App\Models\Material;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarAlertasStockMinimo extends Command
{
    protected $signature = 'inventario:alertas-stock {--to= : Correo destino para la alerta}';

    protected $description = 'Envia un correo con materiales por debajo del stock minimo.';

    public function handle(): int
    {
        $destino = $this->option('to') ?: env('STOCK_ALERT_EMAIL');

        if (! $destino) {
            $this->warn('No se envio correo: configura STOCK_ALERT_EMAIL en .env o usa --to=correo@empresa.com');

            return self::SUCCESS;
        }

        $materiales = Material::query()
            ->where('es_plantilla_equipo', false)
            ->where('stock_minimo', '>', 0)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->orderBy('descripcion')
            ->get();

        if ($materiales->isEmpty()) {
            $this->info('Inventario sin alertas de stock minimo.');

            return self::SUCCESS;
        }

        $total = $materiales->count();
        $filas = $materiales->map(function (Material $material): string {
            $stock = number_format((int) $material->stock);
            $minimo = number_format((int) $material->stock_minimo);
            $nombre = e($material->descripcion);
            $apodo = $material->apodo ? '<br><small>Apodo: '.e($material->apodo).'</small>' : '';
            $parte = e($material->numero_parte ?: 'N/A');
            $almacen = e($material->almacen ?: 'Sin definir');

            return "<tr>
                <td><strong>{$nombre}</strong>{$apodo}</td>
                <td>{$parte}</td>
                <td>{$almacen}</td>
                <td style=\"text-align:center;color:#b91c1c;font-weight:800;\">{$stock} / {$minimo}</td>
            </tr>";
        })->implode('');

        $html = <<<HTML
        <div style="font-family:Segoe UI,Arial,sans-serif;background:#f6f8fb;padding:24px;color:#08233f;">
            <div style="max-width:760px;margin:auto;background:white;border:1px solid #dbeafe;border-radius:18px;overflow:hidden;">
                <div style="background:#0f5fb8;color:white;padding:22px 26px;">
                    <h1 style="margin:0;font-size:24px;">Alerta de stock minimo</h1>
                    <p style="margin:8px 0 0;">Los siguientes {$total} materiales necesitan reabastecimiento hoy.</p>
                </div>
                <div style="padding:22px;">
                    <table style="width:100%;border-collapse:collapse;font-size:14px;">
                        <thead>
                            <tr style="background:#eef6ff;color:#075985;text-align:left;">
                                <th style="padding:10px;">Material</th>
                                <th style="padding:10px;">No. parte</th>
                                <th style="padding:10px;">Almacen</th>
                                <th style="padding:10px;text-align:center;">Stock / Min.</th>
                            </tr>
                        </thead>
                        <tbody>{$filas}</tbody>
                    </table>
                    <p style="margin-top:18px;color:#58718a;font-size:13px;">Correo generado automaticamente por Inventario Lugarth.</p>
                </div>
            </div>
        </div>
        HTML;

        Mail::html($html, function ($message) use ($destino, $total): void {
            $message->to($destino)
                ->subject("Alerta: {$total} materiales necesitan reabastecimiento");
        });

        $this->info("Correo de alerta enviado a {$destino} con {$total} materiales.");

        return self::SUCCESS;
    }
}
