<?php

use App\Http\Controllers\AdminChatController;
use App\Http\Controllers\AdminEntradaPendienteController;
use App\Http\Controllers\AdminMaterialController;
use App\Http\Controllers\AdminProveedorController;
use App\Http\Controllers\AdminSalidaController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\DevolucionMermaController;
use App\Http\Controllers\EquipmentPackageController;
use App\Http\Controllers\EtiquetaController;
use App\Http\Controllers\FacturaXmlController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\IdentificadorVisualController;
use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialPhotoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SalidaMaterialController;
use App\Http\Controllers\TeamHubController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/buscar-global', GlobalSearchController::class)->name('buscar.global');
    Route::get('/notificaciones', [NotificationController::class, 'index'])
        ->middleware('throttle:180,1')
        ->name('notifications.index');
    Route::patch('/notificaciones/leer-todas', [NotificationController::class, 'readAll'])
        ->name('notifications.read-all');
    Route::patch('/notificaciones/{notification}/leer', [NotificationController::class, 'read'])
        ->name('notifications.read');
    Route::put('/preferencias', [UserPreferenceController::class, 'store'])
        ->middleware('throttle:120,1')
        ->name('preferences.store');
    Route::get('/equipo/presencia', [TeamHubController::class, 'presence'])
        ->middleware('throttle:600,1')
        ->name('team.presence');
    Route::get('/equipo/mensajes/{user}', [TeamHubController::class, 'messages'])
        ->middleware('throttle:600,1')
        ->name('team.messages');
    Route::post('/equipo/mensajes/{user}', [TeamHubController::class, 'send'])
        ->middleware('throttle:120,1')
        ->name('team.messages.send');
    Route::post('/equipo/escribiendo/{user}', [TeamHubController::class, 'typing'])
        ->middleware('throttle:300,1')
        ->name('team.typing');
    Route::patch('/equipo/estado', [TeamHubController::class, 'updateAvailability'])
        ->middleware('throttle:120,1')
        ->name('team.availability');
    Route::patch('/equipo/mensajes/{message}/fijar', [TeamHubController::class, 'pin'])
        ->middleware('throttle:120,1')
        ->name('team.messages.pin');
    Route::get('/equipo/conversaciones/{user}/descargar', [TeamHubController::class, 'export'])
        ->middleware('throttle:30,1')
        ->name('team.conversations.export');

    Route::get('materiales/buscar-por-codigo', [MaterialController::class, 'buscarPorCodigo'])
        ->name('materiales.buscarPorCodigo');

    Route::get('reportes/inventario.csv', [ReporteController::class, 'inventarioCsv'])
        ->name('reportes.inventario.csv');
    Route::get('reportes/inventario-pdf', [ReporteController::class, 'inventarioPdf'])
        ->name('reportes.inventario.pdf');
    Route::get('reportes/salidas.csv', [ReporteController::class, 'salidasCsv'])
        ->name('reportes.salidas.csv');
    Route::get('reportes/salidas-pdf', [ReporteController::class, 'salidasPdf'])
        ->name('reportes.salidas.pdf');
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');

    Route::post('materiales/{material}/generar-etiqueta', [EtiquetaController::class, 'generar'])
        ->name('materiales.etiqueta.generar');
    Route::get('materiales/{material}/etiqueta', [EtiquetaController::class, 'mostrar'])
        ->name('materiales.etiqueta');
    Route::get('materiales/etiquetas/lote', [EtiquetaController::class, 'lote'])
        ->name('materiales.etiquetas.lote');
    Route::patch('materiales/{material}/codigo-barras', [MaterialController::class, 'guardarCodigoBarras'])
        ->name('materiales.codigo.guardar');
    Route::post('materiales/{material}/fotografias', [MaterialPhotoController::class, 'store'])
        ->name('materiales.photos.store');
    Route::patch('materiales/{material}/fotografias/{photo}/principal', [MaterialPhotoController::class, 'primary'])
        ->name('materiales.photos.primary');
    Route::delete('materiales/{material}/fotografias/{photo}', [MaterialPhotoController::class, 'destroy'])
        ->name('materiales.photos.destroy');

    Route::get('materiales/importar-xml', [FacturaXmlController::class, 'create'])
        ->name('materiales.xml.create');
    Route::post('materiales/importar-xml/preview', [FacturaXmlController::class, 'preview'])
        ->name('materiales.xml.preview');
    Route::post('materiales/importar-xml/guardar', [FacturaXmlController::class, 'store'])
        ->name('materiales.xml.store');

    Route::get('materiales/identificador-visual', [IdentificadorVisualController::class, 'create'])
        ->name('materiales.visual.create');
    Route::post('materiales/identificador-visual/buscar', [IdentificadorVisualController::class, 'search'])
        ->name('materiales.visual.search');
    Route::post('materiales/identificador-visual/retroalimentacion', [IdentificadorVisualController::class, 'feedback'])
        ->middleware('throttle:120,1')
        ->name('materiales.visual.feedback');
    Route::post('materiales/identificador-visual/reparar-indice', [IdentificadorVisualController::class, 'repairIndex'])
        ->name('materiales.visual.repair');

    Route::get('equipos', [EquipmentPackageController::class, 'index'])
        ->name('equipos.index');
    Route::post('equipos', [EquipmentPackageController::class, 'store'])
        ->name('equipos.store');
    Route::post('equipos/importar-desde-materiales', [EquipmentPackageController::class, 'importFromMaterials'])
        ->name('equipos.importar-materiales');
    Route::get('equipos/retirar', [EquipmentPackageController::class, 'withdrawalsCreate'])
        ->name('equipos.withdrawals.create');
    Route::get('equipos/historial', [EquipmentPackageController::class, 'withdrawalsHistory'])
        ->name('equipos.withdrawals.history');
    Route::get('equipos/{equipo}', [EquipmentPackageController::class, 'show'])
        ->name('equipos.show');
    Route::post('equipos/{equipo}/piezas', [EquipmentPackageController::class, 'addItem'])
        ->name('equipos.items.store');
    Route::patch('equipos/{equipo}/piezas/{item}', [EquipmentPackageController::class, 'updateItem'])
        ->name('equipos.items.update');
    Route::delete('equipos/{equipo}/piezas/{item}', [EquipmentPackageController::class, 'deleteItem'])
        ->name('equipos.items.destroy');
    Route::post('equipos/{equipo}/retirar', [EquipmentPackageController::class, 'withdraw'])
        ->name('equipos.withdraw');

    Route::get('usuarios/permisos', [UserRoleController::class, 'index'])
        ->name('usuarios.roles.index');
    Route::patch('usuarios/{user}/permisos', [UserRoleController::class, 'update'])
        ->name('usuarios.roles.update');

    Route::get('admin/proveedores', [AdminProveedorController::class, 'index'])
        ->name('admin.proveedores.index');
    Route::get('admin/proveedores/comparador/precios', [ProcurementController::class, 'comparator'])
        ->name('admin.proveedores.comparador');
    Route::get('admin/proveedores/{proveedor}', [AdminProveedorController::class, 'show'])
        ->name('admin.proveedores.show');
    Route::get('admin/materiales-completo', [AdminMaterialController::class, 'index'])
        ->name('admin.materiales.index');
    Route::get('admin/materiales-completo/{material}/historial', [AdminMaterialController::class, 'historial'])
        ->name('admin.materiales.historial');
    Route::get('admin/salidas', [AdminSalidaController::class, 'index'])
        ->name('admin.salidas.index');
    Route::get('admin/entradas-pendientes', [AdminEntradaPendienteController::class, 'index'])
        ->name('admin.entradas.index');
    Route::get('admin/entradas-pendientes/{entrada}/editar', [AdminEntradaPendienteController::class, 'edit'])
        ->name('admin.entradas.edit');
    Route::patch('admin/entradas-pendientes/{entrada}', [AdminEntradaPendienteController::class, 'update'])
        ->name('admin.entradas.update');
    Route::patch('admin/entradas-pendientes/{entrada}/aprobar', [AdminEntradaPendienteController::class, 'approve'])
        ->name('admin.entradas.approve');
    Route::patch('admin/entradas-pendientes/{entrada}/rechazar', [AdminEntradaPendienteController::class, 'reject'])
        ->name('admin.entradas.reject');
    Route::get('admin/auditoria', [AuditLogController::class, 'index'])
        ->name('admin.auditoria.index');
    Route::delete('admin/auditoria/{auditLog}', [AuditLogController::class, 'destroy'])
        ->name('admin.auditoria.destroy');
    Route::delete('admin/auditoria', [AuditLogController::class, 'clear'])
        ->name('admin.auditoria.clear');
    Route::get('admin/chats', [AdminChatController::class, 'index'])
        ->name('admin.chats.index');
    Route::patch('admin/chats/retencion', [AdminChatController::class, 'updateRetention'])
        ->name('admin.chats.retention');
    Route::delete('admin/chats/antiguos', [AdminChatController::class, 'purgeExpired'])
        ->name('admin.chats.purge');
    Route::delete('admin/chats/conversaciones/{firstUser}/{secondUser}', [AdminChatController::class, 'destroyConversation'])
        ->name('admin.chats.conversations.destroy');
    Route::get('admin/chats/conversaciones/{firstUser}/{secondUser}/descargar', [AdminChatController::class, 'exportConversation'])
        ->name('admin.chats.conversations.export');
    Route::delete('admin/chats', [AdminChatController::class, 'clear'])
        ->name('admin.chats.clear');
    Route::get('admin/respaldos', [DatabaseBackupController::class, 'index'])
        ->name('admin.backups.index');
    Route::post('admin/respaldos', [DatabaseBackupController::class, 'store'])
        ->name('admin.backups.store');
    Route::get('admin/respaldos/{backup}/descargar', [DatabaseBackupController::class, 'download'])
        ->where('backup', '[A-Za-z0-9_.-]+\.sql')
        ->name('admin.backups.download');
    Route::post('admin/respaldos/restaurar/bloque', [DatabaseBackupController::class, 'uploadRestoreChunk'])
        ->middleware('throttle:180,1')
        ->name('admin.backups.restore.chunk');
    Route::post('admin/respaldos/restaurar', [DatabaseBackupController::class, 'restore'])
        ->name('admin.backups.restore');
    Route::get('admin/ordenes-compra', [PurchaseOrderController::class, 'index'])
        ->name('admin.ordenes.index');
    Route::post('admin/ordenes-compra', [PurchaseOrderController::class, 'store'])
        ->name('admin.ordenes.store');
    Route::patch('admin/ordenes-compra/{orden}/estado', [PurchaseOrderController::class, 'updateStatus'])
        ->name('admin.ordenes.status');
    Route::post('admin/ordenes-compra/{orden}/recibir', [PurchaseOrderController::class, 'receive'])
        ->name('admin.ordenes.receive');
    Route::patch('admin/ordenes-compra/{orden}/factura', [PurchaseOrderController::class, 'invoice'])
        ->name('admin.ordenes.invoice');
    Route::get('compras', [ProcurementController::class, 'index'])
        ->name('admin.compras.index');
    Route::post('compras/solicitudes', [ProcurementController::class, 'storeRequest'])
        ->name('admin.compras.requests.store');
    Route::post('compras/solicitar/{material}', [ProcurementController::class, 'quickRequest'])
        ->name('admin.compras.requests.quick');
    Route::patch('compras/solicitudes/{solicitud}/autorizar', [ProcurementController::class, 'authorizeRequest'])
        ->name('admin.compras.requests.authorize');
    Route::patch('compras/solicitudes/{solicitud}/rechazar', [ProcurementController::class, 'rejectRequest'])
        ->name('admin.compras.requests.reject');
    Route::post('compras/solicitudes/{solicitud}/orden', [ProcurementController::class, 'createOrder'])
        ->name('admin.compras.requests.order');
    Route::get('compras/inventario-historico', [ProcurementController::class, 'historicalInventory'])
        ->name('admin.compras.historical');
    Route::resource('admin/categorias', MaterialCategoryController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['categorias' => 'categoria'])
        ->names('admin.categorias');

    Route::get('materiales/salidas', [SalidaMaterialController::class, 'create'])
        ->name('materiales.salidas.create');
    Route::post('materiales/salidas', [SalidaMaterialController::class, 'store'])
        ->name('materiales.salidas.store');
    Route::get('materiales/devoluciones', [DevolucionMermaController::class, 'create'])
        ->name('materiales.devoluciones.create');
    Route::post('materiales/devoluciones', [DevolucionMermaController::class, 'store'])
        ->name('materiales.devoluciones.store');

    Route::resource('materiales', MaterialController::class)
        ->except(['show'])
        ->parameters(['materiales' => 'material']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
