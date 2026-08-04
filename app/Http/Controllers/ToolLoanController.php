<?php

namespace App\Http\Controllers;

use App\Models\ToolLoan;
use App\Models\ToolLoanItem;
use App\Support\AuditLogger;
use App\Support\ImageStorage;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ToolLoanController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeToolLoans($request);

        $status = (string) $request->query('estado', 'activos');
        $status = in_array($status, ['activos', 'reparacion', 'devueltos', 'todos'], true) ? $status : 'activos';
        $search = trim((string) $request->query('buscar', ''));

        $loans = ToolLoan::query()
            ->with(['loanedBy:id,name', 'items'])
            ->when($status === 'activos', fn ($query) => $query->where('status', 'activo'))
            ->when($status === 'reparacion', fn ($query) => $query->where('status', 'reparacion'))
            ->when($status === 'devueltos', fn ($query) => $query->where('status', 'devuelto'))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('employee_area', 'like', "%{$search}%")
                        ->orWhereHas('items', fn ($items) => $items
                            ->where('tool_name', 'like', "%{$search}%")
                            ->orWhere('tool_detail', 'like', "%{$search}%"));
                });
            })
            ->latest('taken_at')
            ->paginate(15)
            ->withQueryString();

        return view('prestamos.index', [
            'loans' => $loans,
            'status' => $status,
            'search' => $search,
            'statusCounts' => [
                'activos' => ToolLoan::query()->where('status', 'activo')->count(),
                'reparacion' => ToolLoan::query()->where('status', 'reparacion')->count(),
                'devueltos' => ToolLoan::query()->where('status', 'devuelto')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeToolLoans($request);

        $data = $request->validate([
            'employee_name' => ['required', 'string', 'max:150'],
            'employee_code' => ['nullable', 'string', 'max:80'],
            'employee_area' => ['nullable', 'string', 'max:120'],
            'taken_at' => ['required', 'date'],
            'expected_return_at' => ['nullable', 'date', 'after_or_equal:taken_at'],
            'notes' => ['nullable', 'string', 'max:1500'],
            'evidence_out' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'evidence_out_camera' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'items' => ['required', 'array', 'min:1', 'max:40'],
            'items.*.tool_name' => ['required', 'string', 'max:180'],
            'items.*.tool_detail' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ], [
            'employee_name.required' => 'Escribe el nombre de quien recibe las herramientas.',
            'taken_at.required' => 'Indica la fecha y hora de entrega.',
            'expected_return_at.after_or_equal' => 'La fecha estimada de regreso no puede ser anterior a la entrega.',
            'evidence_out.image' => 'La evidencia de entrega debe ser una imagen valida.',
            'evidence_out_camera.image' => 'La foto tomada debe ser una imagen valida.',
            'items.required' => 'Agrega al menos una herramienta al prestamo.',
            'items.*.tool_name.required' => 'Escribe el nombre de cada herramienta.',
            'items.*.quantity.min' => 'Cada cantidad debe ser de al menos 1.',
        ]);

        $evidenceFile = $request->file('evidence_out_camera') ?: $request->file('evidence_out');
        if (! $evidenceFile) {
            throw ValidationException::withMessages(['evidence_out' => 'Toma una foto o sube una imagen como evidencia de la entrega.']);
        }

        $evidence = ImageStorage::storeOptimized($evidenceFile, 'prestamos-herramientas/entregas', 1600, 72);

        try {
            $loan = DB::transaction(function () use ($data, $request, $evidence): ToolLoan {
                $loan = ToolLoan::create([
                    'loaned_by' => $request->user()?->id,
                    'employee_name' => trim($data['employee_name']),
                    'employee_code' => $this->optionalText($data, 'employee_code'),
                    'employee_area' => $this->optionalText($data, 'employee_area'),
                    'taken_at' => $data['taken_at'],
                    'expected_return_at' => $data['expected_return_at'] ?? null,
                    'status' => 'activo',
                    'evidence_out' => $evidence,
                    'notes' => $this->optionalText($data, 'notes'),
                ]);

                foreach ($data['items'] as $itemData) {
                    $loan->items()->create([
                        'tool_name' => trim($itemData['tool_name']),
                        'tool_detail' => $this->optionalText($itemData, 'tool_detail'),
                        'quantity_loaned' => (int) $itemData['quantity'],
                    ]);
                }

                return $loan;
            });
        } catch (\Throwable $exception) {
            ImageStorage::delete($evidence);
            throw $exception;
        }

        AuditLogger::registrar('Prestamos', 'Herramientas prestadas', "Registro el prestamo #{$loan->id} a {$loan->employee_name}.", [
            'prestamo_id' => $loan->id,
            'empleado' => $loan->employee_name,
            'herramientas' => count($data['items']),
        ], $request);

        return redirect()->route('prestamos.index')->with('success', "Prestamo #{$loan->id} registrado correctamente.");
    }

    public function returnTools(Request $request, ToolLoan $loan): RedirectResponse
    {
        $this->authorizeToolLoans($request);

        if ($loan->status !== 'activo') {
            return back()->withErrors(['prestamo' => 'Este prestamo ya no tiene herramientas pendientes de regreso.']);
        }

        $data = $request->validate([
            'returned_at' => ['required', 'date'],
            'return_notes' => ['nullable', 'string', 'max:1500'],
            'evidence_return' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'evidence_return_camera' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'returns' => ['required', 'array'],
            'returns.*.quantity' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'returns.*.condition' => ['nullable', Rule::in(['bueno', 'reparacion', 'perdida'])],
        ], [
            'returned_at.required' => 'Indica la fecha y hora en que regresaron las herramientas.',
        ]);

        if (Carbon::parse($data['returned_at'])->lt($loan->taken_at)) {
            throw ValidationException::withMessages(['returned_at' => 'La fecha de regreso no puede ser anterior a la entrega.']);
        }

        $returnEvidenceFile = $request->file('evidence_return_camera') ?: $request->file('evidence_return');
        $evidence = $returnEvidenceFile
            ? ImageStorage::storeOptimized($returnEvidenceFile, 'prestamos-herramientas/devoluciones', 1600, 72)
            : null;

        try {
            $summary = DB::transaction(function () use ($loan, $data, $evidence): array {
                $lockedLoan = ToolLoan::query()->whereKey($loan->id)->lockForUpdate()->firstOrFail();
                if ($lockedLoan->status !== 'activo') {
                    throw ValidationException::withMessages(['prestamo' => 'El prestamo ya fue actualizado por otro usuario.']);
                }

                $processed = 0;
                foreach ($data['returns'] as $itemId => $returnData) {
                    $quantity = (int) ($returnData['quantity'] ?? 0);
                    if ($quantity <= 0) {
                        continue;
                    }

                    $condition = $returnData['condition'] ?? null;
                    if (! $condition) {
                        throw ValidationException::withMessages(["returns.{$itemId}.condition" => 'Selecciona el estado de cada herramienta que regresa.']);
                    }

                    $item = ToolLoanItem::query()->whereKey($itemId)->where('tool_loan_id', $lockedLoan->id)->lockForUpdate()->first();
                    if (! $item) {
                        throw ValidationException::withMessages(['returns' => 'Una herramienta de este prestamo ya no esta disponible.']);
                    }
                    if ($quantity > $item->pendingQuantity()) {
                        throw ValidationException::withMessages(["returns.{$itemId}.quantity" => "Solo quedan {$item->pendingQuantity()} pendientes de {$item->tool_name}."]);
                    }

                    if ($condition === 'bueno') {
                        $item->increment('quantity_returned', $quantity);
                    } elseif ($condition === 'reparacion') {
                        $item->increment('quantity_repair', $quantity);
                    } else {
                        $item->increment('quantity_lost', $quantity);
                    }
                    $item->update(['last_return_condition' => $condition]);
                    $processed++;
                }

                if ($processed === 0) {
                    throw ValidationException::withMessages(['returns' => 'Escribe al menos una cantidad que haya regresado.']);
                }

                $lockedLoan->load('items');
                $pending = $lockedLoan->items->sum(fn (ToolLoanItem $item): int => $item->pendingQuantity());
                $repair = $lockedLoan->items->sum(fn (ToolLoanItem $item): int => $item->pendingRepairQuantity());
                $status = $pending > 0 ? 'activo' : ($repair > 0 ? 'reparacion' : 'devuelto');

                $lockedLoan->update([
                    'status' => $status,
                    'returned_at' => $pending === 0 ? $data['returned_at'] : null,
                    'evidence_return' => $evidence ?: $lockedLoan->evidence_return,
                    'return_notes' => $this->optionalText($data, 'return_notes') ?: $lockedLoan->return_notes,
                    'repair_notes' => $repair > 0 ? $this->optionalText($data, 'return_notes') : $lockedLoan->repair_notes,
                ]);

                return ['processed' => $processed, 'status' => $status];
            });
        } catch (\Throwable $exception) {
            ImageStorage::delete($evidence);
            throw $exception;
        }

        AuditLogger::registrar('Prestamos', 'Herramientas recibidas', "Registro regreso de herramientas del prestamo #{$loan->id}.", [
            'prestamo_id' => $loan->id,
            'herramientas_actualizadas' => $summary['processed'],
            'estado' => $summary['status'],
        ], $request);

        $message = $summary['status'] === 'reparacion'
            ? 'Regreso registrado. Hay herramientas pendientes de reparacion.'
            : ($summary['status'] === 'devuelto'
                ? 'Prestamo cerrado correctamente.'
                : 'Regreso parcial registrado. El prestamo sigue abierto.');

        return redirect()->route('prestamos.index', ['estado' => $summary['status'] === 'devuelto' ? 'devueltos' : 'activos'])->with('success', $message);
    }

    public function completeRepair(Request $request, ToolLoan $loan): RedirectResponse
    {
        $this->authorizeToolLoans($request);

        if ($loan->status !== 'reparacion') {
            return back()->withErrors(['prestamo' => 'Este prestamo no tiene herramientas pendientes de reparacion.']);
        }

        $data = $request->validate(['repair_notes' => ['nullable', 'string', 'max:1500']]);

        $repaired = DB::transaction(function () use ($loan, $data): int {
            $lockedLoan = ToolLoan::query()->whereKey($loan->id)->lockForUpdate()->firstOrFail();
            $items = ToolLoanItem::query()->where('tool_loan_id', $lockedLoan->id)->lockForUpdate()->get();
            $total = 0;

            foreach ($items as $item) {
                $quantity = $item->pendingRepairQuantity();
                if ($quantity <= 0) {
                    continue;
                }
                $item->increment('quantity_repaired', $quantity);
                $total += $quantity;
            }

            if ($total === 0) {
                throw ValidationException::withMessages(['prestamo' => 'No hay herramientas pendientes de reparacion.']);
            }

            $lockedLoan->update([
                'status' => 'devuelto',
                'returned_at' => $lockedLoan->returned_at ?: now(),
                'repair_notes' => $this->optionalText($data, 'repair_notes') ?: $lockedLoan->repair_notes,
            ]);

            return $total;
        });

        AuditLogger::registrar('Prestamos', 'Reparacion finalizada', "Marco como reparadas {$repaired} herramientas del prestamo #{$loan->id}.", [
            'prestamo_id' => $loan->id,
            'cantidad' => $repaired,
        ], $request);

        return redirect()->route('prestamos.index', ['estado' => 'devueltos'])->with('success', "Reparacion registrada. {$repaired} herramientas quedaron disponibles para futuros prestamos.");
    }

    public function reportCsv(Request $request): StreamedResponse
    {
        $this->authorizeToolLoans($request);

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Prestamo', 'Estado', 'Empleado', 'No. empleado', 'Area', 'Entrega', 'Regreso final', 'Herramienta', 'Detalle', 'Prestadas', 'Devueltas', 'En reparacion', 'Reparadas', 'Perdidas', 'Registrado por', 'Notas']);

            ToolLoan::query()->with(['loanedBy:id,name', 'items'])->orderByDesc('taken_at')->chunk(100, function ($loans) use ($output): void {
                foreach ($loans as $loan) {
                    foreach ($loan->items as $item) {
                        fputcsv($output, [$loan->id, $loan->status, $loan->employee_name, $loan->employee_code, $loan->employee_area, $loan->taken_at?->format('d/m/Y H:i'), $loan->returned_at?->format('d/m/Y H:i'), $item->tool_name, $item->tool_detail, $item->quantity_loaned, $item->quantity_returned, $item->quantity_repair, $item->quantity_repaired, $item->quantity_lost, $loan->loanedBy?->name, $loan->notes]);
                    }
                }
            });

            fclose($output);
        }, 'reporte_prestamos_herramientas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function authorizeToolLoans(Request $request): void
    {
        abort_unless($request->user()?->puedeMoverStock(), 403, 'No tienes permiso para registrar prestamos de herramientas.');
    }

    private function optionalText(array $data, string $key): ?string
    {
        $value = trim((string) ($data[$key] ?? ''));

        return $value !== '' ? $value : null;
    }
}
