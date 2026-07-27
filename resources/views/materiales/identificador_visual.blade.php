<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Identificador visual - Inventario Lugarth</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        :root {
            --visual-ink: #092743;
            --visual-muted: #61788e;
            --visual-line: #d6e3ed;
            --visual-blue: #1769d2;
            --visual-blue-dark: #0f55ad;
            --visual-blue-soft: #eaf4ff;
            --visual-green: #079669;
            --visual-green-soft: #eafbf4;
            --visual-red: #dc2626;
            --visual-shadow: 0 16px 38px rgba(19, 57, 87, .08);
        }

        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: var(--visual-ink); background: #f2f7fa; font-family: "Segoe UI", Tahoma, sans-serif; }
        button, input { font: inherit; }
        [hidden] { display: none !important; }
        .app-shell { display: flex; min-height: 100vh; }
        .visual-page.app-content { min-width: 0; flex: 1; padding: 34px clamp(18px, 3vw, 48px) 80px; overflow: visible; }
        .visual-workspace { width: min(1440px, 100%); margin: 0 auto; display: grid; gap: 16px; }
        .visual-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 22px 24px;
            background: #fff;
            border: 1px solid var(--visual-line);
            border-radius: 8px;
            box-shadow: var(--visual-shadow);
        }
        .visual-title { display: flex; align-items: center; gap: 14px; }
        .visual-title-mark {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--visual-blue);
            background: var(--visual-blue-soft);
            border: 1px solid #b7d9fb;
            border-radius: 8px;
        }
        .visual-title-mark svg { width: 25px; height: 25px; }
        .visual-header h1 { margin: 0 0 5px; font-size: 34px; }
        .visual-header p { margin: 0; color: var(--visual-muted); font-size: 13px; font-weight: 600; }
        .visual-state {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 11px;
            color: #075e47;
            background: var(--visual-green-soft);
            border: 1px solid #a7ebd1;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }
        .visual-state i { width: 9px; height: 9px; background: #10b981; border-radius: 50%; }
        .visual-state.is-limited { color: #8a4b08; background: #fff7e8; border-color: #f2c879; }
        .visual-state.is-limited i { background: #e67e00; }
        .visual-alert { padding: 13px 15px; color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid var(--visual-red); border-radius: 7px; font-size: 13px; font-weight: 750; }
        .visual-alert.is-warning { color: #71420a; background: #fff9ec; border-color: #f3d59b; border-left-color: #e67e00; }
        .capture-card, .results-card { padding: 0; background: transparent; border: 0; box-shadow: none; }
        .capture-grid { display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 16px; }
        .upload-stage {
            position: relative;
            min-height: 380px;
            display: grid;
            place-items: center;
            overflow: hidden;
            padding: 24px;
            background: #f8fbfd;
            border: 2px dashed #9bc9ed;
            border-radius: 8px;
            outline: none;
            transition: border-color .16s ease, background .16s ease, box-shadow .16s ease;
        }
        .upload-stage:hover, .upload-stage:focus-visible, .upload-stage.is-dragging { background: #f0f8ff; border-color: var(--visual-blue); box-shadow: inset 0 0 0 3px rgba(23, 105, 210, .08); }
        .upload-placeholder { width: min(520px, 100%); display: grid; justify-items: center; gap: 8px; text-align: center; }
        .upload-placeholder-mark {
            width: 68px;
            height: 68px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--visual-blue);
            background: #fff;
            border: 1px solid #b9d8f3;
            border-radius: 8px;
            box-shadow: 0 9px 22px rgba(23, 105, 210, .1);
        }
        .upload-placeholder-mark svg { width: 32px; height: 32px; }
        .upload-placeholder strong { margin-top: 4px; font-size: 19px; }
        .upload-placeholder p { margin: 0; color: var(--visual-muted); font-size: 12px; line-height: 1.5; }
        .upload-actions { width: 100%; display: flex; flex-wrap: wrap; justify-content: center; gap: 9px; margin-top: 10px; }
        .visual-button {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 15px;
            color: #fff;
            background: var(--button-color, var(--visual-blue));
            border: 1px solid var(--button-color, var(--visual-blue));
            border-radius: 7px;
            box-shadow: 0 8px 18px rgba(23, 105, 210, .14);
            cursor: pointer;
            font-size: 12px;
            font-weight: 850;
            text-decoration: none;
            transition: filter .16s ease, transform .16s ease;
        }
        .visual-button:hover { filter: brightness(.92); transform: translateY(-1px); }
        .visual-button:focus-visible { outline: 3px solid rgba(23, 105, 210, .24); outline-offset: 2px; }
        .visual-button svg { width: 18px; height: 18px; }
        .visual-button-green { --button-color: var(--visual-green); }
        .visual-button-light { color: var(--visual-blue-dark); background: #fff; border-color: #9cc9f1; box-shadow: none; }
        .main-preview {
            width: 100%;
            height: min(520px, 62vh);
            object-fit: contain;
            background: #eef4f8;
            border-radius: 7px;
            cursor: zoom-in;
        }
        .preview-actions {
            position: absolute;
            right: 12px;
            bottom: 12px;
            left: 12px;
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 9px;
            background: rgba(255, 255, 255, .94);
            border: 1px solid rgba(185, 205, 222, .9);
            border-radius: 7px;
            box-shadow: 0 10px 24px rgba(12, 42, 67, .12);
            backdrop-filter: blur(10px);
        }
        .side-panel { display: grid; align-content: start; gap: 12px; }
        .status-box { min-width: 0; padding: 17px; background: #f8fbfd; border: 1px solid var(--visual-line); border-top: 3px solid var(--status-color, var(--visual-blue)); border-radius: 8px; }
        .status-box strong { display: block; margin-bottom: 8px; color: var(--visual-muted); font-size: 10px; text-transform: uppercase; }
        .status-box span { display: block; color: var(--visual-ink); font-size: 14px; font-weight: 800; line-height: 1.4; overflow-wrap: anywhere; }
        .status-box small { display: block; margin-top: 6px; color: var(--visual-muted); font-size: 10px; line-height: 1.4; }
        .visual-tip { padding: 14px; color: #35536e; background: var(--visual-blue-soft); border: 1px solid #b9daf8; border-radius: 8px; font-size: 11px; line-height: 1.5; }
        .loading-layer {
            position: absolute;
            z-index: 3;
            inset: 0;
            display: none;
            place-content: center;
            justify-items: center;
            gap: 10px;
            color: var(--visual-blue-dark);
            background: #f8fbfd;
            text-align: center;
            font-size: 13px;
            font-weight: 850;
        }
        .upload-stage.is-loading .loading-layer { display: grid; }
        .loading-spinner { width: 34px; height: 34px; border: 4px solid #cfe5f8; border-top-color: var(--visual-blue); border-radius: 50%; animation: visual-spin .7s linear infinite; }
        .results-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 15px; }
        .results-heading h2 { margin: 0; font-size: 21px; }
        .results-heading span { color: var(--visual-muted); font-size: 11px; font-weight: 700; }
        .result-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(420px, 520px)); gap: 12px; }
        .result-card {
            min-width: 0;
            display: grid;
            grid-template-columns: 124px minmax(0, 1fr);
            gap: 13px;
            padding: 13px;
            background: #fff;
            border: 1px solid var(--visual-line);
            border-radius: 8px;
            transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }
        .result-card:hover { border-color: #8abfed; box-shadow: 0 12px 28px rgba(23, 105, 210, .1); transform: translateY(-2px); }
        .result-card:first-child { border-color: #75cdae; box-shadow: 0 12px 28px rgba(7, 150, 105, .1); }
        .result-photo-button { width: 124px; height: 124px; padding: 0; overflow: hidden; background: #eef4f8; border: 0; border-radius: 7px; cursor: zoom-in; }
        .result-photo { width: 100%; height: 100%; object-fit: contain; }
        .result-info { min-width: 0; display: grid; align-content: start; gap: 6px; }
        .result-badges { min-width: 0; display: flex; flex-wrap: wrap; align-items: center; gap: 5px; }
        .category-badge { width: fit-content; max-width: 100%; padding: 4px 7px; overflow: hidden; color: #0759ac; background: var(--visual-blue-soft); border: 1px solid #b9d9f7; border-radius: 6px; font-size: 9px; font-weight: 850; text-overflow: ellipsis; text-transform: uppercase; white-space: nowrap; }
        .result-title { color: var(--visual-ink); font-size: 13px; font-weight: 850; line-height: 1.3; overflow-wrap: anywhere; }
        .result-meta { display: grid; gap: 2px; color: var(--visual-muted); font-size: 10px; line-height: 1.35; }
        .result-meta b { color: #35536e; }
        .score-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 4px; }
        .score-summary { min-width: 0; flex: 1; display: grid; gap: 4px; }
        .score-heading { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .score-label { color: #087f5b; font-size: 11px; font-weight: 900; white-space: nowrap; }
        .score { color: #087f5b; font-size: 11px; font-weight: 900; white-space: nowrap; }
        .score-meter { width: 100%; height: 5px; overflow: hidden; background: #dce8e3; border-radius: 999px; }
        .score-meter i { display: block; height: 100%; background: #0aa675; border-radius: inherit; }
        .result-link { min-height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0 10px; color: var(--visual-blue-dark); background: #fff; border: 1px solid #9cc9f1; border-radius: 6px; font-size: 10px; font-weight: 850; text-decoration: none; white-space: nowrap; }
        .result-link:hover { color: #fff; background: var(--visual-blue); border-color: var(--visual-blue); }
        .empty-result { padding: 28px 18px; color: var(--visual-muted); background: #f8fbfd; border: 1px dashed #b8cddd; border-radius: 8px; text-align: center; font-size: 12px; font-weight: 700; line-height: 1.55; }
        .camera-modal { position: fixed; z-index: 2600; inset: 0; display: grid; place-items: center; padding: 18px; background: rgba(5, 20, 34, .88); backdrop-filter: blur(8px); }
        .camera-dialog { width: min(760px, 100%); max-height: calc(100dvh - 36px); display: grid; grid-template-rows: auto minmax(0, 1fr) auto; overflow: hidden; background: #fff; border: 1px solid #b7cad9; border-radius: 8px; box-shadow: 0 28px 80px rgba(0, 0, 0, .34); }
        .camera-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--visual-line); }
        .camera-header div { display: grid; gap: 2px; }
        .camera-header strong { font-size: 15px; }
        .camera-header small { color: var(--visual-muted); font-size: 10px; }
        .camera-close { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; color: #5a7186; background: #f0f4f7; border: 0; border-radius: 7px; cursor: pointer; }
        .camera-close:hover { color: #fff; background: var(--visual-red); }
        .camera-close svg { width: 18px; height: 18px; }
        .camera-stage { position: relative; min-height: 0; display: grid; place-items: center; overflow: hidden; background: #06121d; }
        #videoElement { width: 100%; height: 100%; max-height: 68dvh; object-fit: contain; transform: scale(var(--camera-zoom, 1)); transition: transform .12s ease; }
        .camera-guide { position: absolute; z-index: 2; inset: 0; display: grid; place-items: center; pointer-events: none; }
        .camera-guide-frame { position: relative; width: min(76%, 560px); height: min(72%, 440px); }
        .camera-corner { position: absolute; width: 42px; height: 42px; opacity: .88; filter: drop-shadow(0 2px 5px rgba(0, 0, 0, .5)); }
        .camera-corner::before, .camera-corner::after { content: ""; position: absolute; background: #fff; border-radius: 2px; }
        .camera-corner::before { width: 42px; height: 4px; }
        .camera-corner::after { width: 4px; height: 42px; }
        .camera-corner.top-left { top: 0; left: 0; }
        .camera-corner.top-right { top: 0; right: 0; transform: scaleX(-1); }
        .camera-corner.bottom-left { bottom: 0; left: 0; transform: scaleY(-1); }
        .camera-corner.bottom-right { right: 0; bottom: 0; transform: scale(-1); }
        .camera-instruction { position: absolute; right: 16px; bottom: 18px; left: 16px; display: flex; justify-content: center; }
        .camera-instruction span { max-width: 92%; padding: 8px 12px; color: #fff; background: rgba(3, 14, 24, .76); border: 1px solid rgba(255, 255, 255, .28); border-radius: 7px; box-shadow: 0 7px 18px rgba(0, 0, 0, .22); font-size: 11px; font-weight: 800; text-align: center; backdrop-filter: blur(5px); }
        .camera-controls { display: grid; gap: 11px; padding: 13px 16px 16px; border-top: 1px solid var(--visual-line); }
        .zoom-control { display: grid; grid-template-columns: auto minmax(100px, 1fr) 42px; align-items: center; gap: 10px; color: #47627a; font-size: 10px; font-weight: 800; }
        .zoom-control input { width: 100%; accent-color: var(--visual-blue); }
        .camera-actions { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 9px; }
        .crop-modal { position: fixed; z-index: 2700; inset: 0; display: grid; place-items: center; padding: 18px; background: rgba(5, 20, 34, .92); backdrop-filter: blur(8px); }
        .crop-dialog { width: min(920px, 100%); max-height: calc(100dvh - 36px); display: grid; grid-template-rows: auto minmax(0, 1fr) auto; overflow: hidden; background: #fff; border: 1px solid #b7cad9; border-radius: 8px; box-shadow: 0 28px 80px rgba(0, 0, 0, .38); }
        .crop-stage { position: relative; min-height: 430px; display: grid; place-items: center; overflow: hidden; padding: 18px; background: #071521; cursor: crosshair; touch-action: none; }
        .crop-source { display: block; width: auto; height: auto; max-width: 100%; max-height: min(68dvh, 680px); object-fit: contain; user-select: none; pointer-events: none; -webkit-user-drag: none; }
        .crop-selection { position: absolute; z-index: 2; min-width: 24px; min-height: 24px; border: 3px solid #25a7ff; border-radius: 6px; box-shadow: 0 0 0 9999px rgba(2, 12, 21, .66), 0 0 0 1px #fff inset; pointer-events: none; }
        .crop-selection::before, .crop-selection::after { content: ""; position: absolute; width: 13px; height: 13px; background: #fff; border: 3px solid #1769d2; border-radius: 50%; }
        .crop-selection::before { top: -8px; left: -8px; }
        .crop-selection::after { right: -8px; bottom: -8px; }
        .crop-controls { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 12px; padding: 13px 16px 16px; border-top: 1px solid var(--visual-line); }
        .crop-help { display: grid; gap: 3px; color: var(--visual-muted); font-size: 10px; line-height: 1.4; }
        .crop-help strong { color: var(--visual-ink); font-size: 12px; }
        .crop-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; }
        .visual-button-orange { --button-color: #e66d00; }
        .ai-engine-badge { display: inline-flex; align-items: center; gap: 5px; width: fit-content; padding: 4px 7px; color: #075e47; background: #eafbf4; border: 1px solid #a7ebd1; border-radius: 6px; font-size: 9px; font-weight: 850; text-transform: uppercase; }

        @media (max-width: 1024px) {
            .capture-grid { grid-template-columns: minmax(0, 1fr) 240px; }
        }

        @media (max-width: 820px) {
            .visual-page.app-content { padding: 78px 12px 96px !important; }
            .visual-header { align-items: flex-start; padding: 18px; }
            .capture-grid { grid-template-columns: 1fr; }
            .side-panel { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .visual-tip { grid-column: 1 / -1; }
        }

        @media (max-width: 620px) {
            .visual-page.app-content { padding-inline: 9px !important; }
            .visual-workspace { gap: 10px; }
            .visual-header { display: grid; gap: 12px; }
            .visual-title { align-items: flex-start; }
            .visual-title-mark { width: 44px; height: 44px; flex-basis: 44px; }
            .visual-header h1 { font-size: 26px !important; line-height: 1.08; }
            .visual-state { justify-self: start; white-space: normal; }
            .capture-card, .results-card { padding: 12px !important; }
            .upload-stage { min-height: 390px; padding: 14px; }
            .main-preview { height: min(520px, 58dvh); }
            .preview-actions { flex-direction: column; }
            .preview-actions .visual-button { width: 100%; }
            .upload-actions { display: grid; grid-template-columns: 1fr; }
            .upload-actions .visual-button { width: 100%; }
            .side-panel { grid-template-columns: 1fr 1fr; gap: 8px; }
            .status-box { padding: 13px; }
            .result-grid { grid-template-columns: 1fr; }
            .result-card { grid-template-columns: 108px minmax(0, 1fr); padding: 11px; }
            .result-photo-button { width: 108px; height: 118px; }
            .result-title { font-size: 14px; }
            .result-meta { font-size: 11px; }
            .score-row { display: grid; grid-template-columns: 1fr; }
            .result-link { width: 100%; }
            .camera-modal { padding: 0; }
            .camera-dialog { width: 100%; height: 100dvh; max-height: none; border: 0; border-radius: 0; }
            .crop-modal { padding: 0; }
            .crop-dialog { width: 100%; height: 100dvh; max-height: none; border: 0; border-radius: 0; }
            .crop-stage { min-height: 0; padding: 10px; }
            .crop-source { max-height: 100%; }
            .crop-controls { grid-template-columns: 1fr; padding-bottom: max(14px, env(safe-area-inset-bottom)); }
            .crop-actions { display: grid; grid-template-columns: 1fr 1fr; }
            .crop-actions .visual-button:first-child { grid-column: 1 / -1; }
            .crop-actions .visual-button { width: 100%; }
            .camera-header { padding-top: max(12px, env(safe-area-inset-top)); }
            #videoElement { max-height: none; }
            .camera-controls { padding-bottom: max(14px, env(safe-area-inset-bottom)); }
            .camera-actions { grid-template-columns: 1fr; }
            .camera-actions .visual-button { width: 100%; }
        }

        @media (max-width: 390px) {
            .side-panel { grid-template-columns: 1fr; }
            .visual-tip { grid-column: auto; }
            .result-card { grid-template-columns: 82px minmax(0, 1fr); }
            .result-photo-button { width: 82px; height: 98px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
        }

        @keyframes visual-spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')

    <main class="app-content visual-page">
        <div class="visual-workspace">
            <header class="visual-header">
                <div class="visual-title">
                    <span class="visual-title-mark">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.5 4 16 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3l1.5-3h5ZM12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                    </span>
                    <div>
                        <h1>Identificador visual</h1>
                        <p>Toma o selecciona una foto y compárala con las piezas reales del inventario.</p>
                    </div>
                </div>
                <span class="visual-state {{ ($iaActiva ?? false) ? '' : 'is-limited' }}">
                    <i></i>
                    {{ ($iaActiva ?? false) ? 'Análisis inteligente activo' : 'Motor inteligente sin conexión' }}
                </span>
            </header>

            @if($errors->any())
                <div class="visual-alert" role="alert">{{ $errors->first() }}</div>
            @endif
            @if($motorWarning ?? null)
                <div class="visual-alert is-warning" role="status">{{ $motorWarning }}</div>
            @endif

            <section class="capture-card">
                <form action="{{ route('materiales.visual.search') }}" method="POST" enctype="multipart/form-data" id="visualForm">
                    @csrf
                    <input type="file" name="fotografia" id="fotografia" accept="image/jpeg,image/png,image/webp" hidden>

                    <div class="capture-grid">
                        <div class="upload-stage" id="dropArea" tabindex="0" role="group" aria-label="Seleccionar fotografía de una pieza">
                            @if($preview)
                                <img
                                    src="{{ $preview }}"
                                    class="main-preview"
                                    alt="Foto analizada"
                                    data-workspace-lightbox
                                    data-lightbox-title="Foto analizada"
                                    data-lightbox-caption="Imagen utilizada para buscar coincidencias"
                                >
                                <div class="preview-actions">
                                    <button type="button" class="visual-button" id="openCamera">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.5 4 16 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3l1.5-3h5ZM12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                                        Tomar otra foto
                                    </button>
                                    <button type="button" class="visual-button visual-button-green" id="openFile">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg>
                                        Elegir otra imagen
                                    </button>
                                </div>
                            @else
                                <div class="upload-placeholder">
                                    <span class="upload-placeholder-mark">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.5 4 16 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3l1.5-3h5ZM12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                                    </span>
                                    <strong>Fotografía de la pieza</strong>
                                    <p>Procura centrar la pieza y usar buena iluminación. No necesitas ingresar medidas ni datos.</p>
                                    <div class="upload-actions">
                                        <button type="button" class="visual-button" id="openCamera">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.5 4 16 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3l1.5-3h5ZM12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                                            Usar cámara
                                        </button>
                                        <button type="button" class="visual-button visual-button-green" id="openFile">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg>
                                            Subir imagen
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <div class="loading-layer" aria-live="polite">
                                <span class="loading-spinner"></span>
                                <strong>Analizando la pieza...</strong>
                                <span>Esto puede tardar unos segundos.</span>
                            </div>
                        </div>

                        <aside class="side-panel">
                            <article class="status-box" style="--status-color:#1769d2">
                                <strong>Lectura actual</strong>
                                <span>{{ $analisis ? 'Imagen procesada' : 'Esperando una imagen' }}</span>
                                <small>
                                    {{ $analisis['motor'] ?? (($iaActiva ?? false) ? 'Motor local preparado' : 'Motor inteligente no disponible') }}.
                                    La fotografía se procesa dentro de este equipo.
                                </small>
                            </article>
                            <article class="status-box" style="--status-color:#079669">
                                <strong>Resultado</strong>
                                <span>
                                    @if(!$busquedaRealizada)
                                        Sin búsqueda todavía
                                    @elseif($resultados->count() === 1)
                                        1 coincidencia
                                    @else
                                        {{ $resultados->count() }} coincidencias
                                    @endif
                                </span>
                                <small>Los resultados dudosos se descartan automáticamente.</small>
                            </article>
                            <div class="visual-tip">Antes de analizar, encierra únicamente la pieza. El taller, piso y objetos cercanos quedarán fuera de la comparación.</div>
                        </aside>
                    </div>
                </form>
            </section>

            <section class="results-card">
                <div class="results-heading">
                    <h2>Mejores coincidencias</h2>
                    <span>
                        @if(!$busquedaRealizada)
                            Aún no se ha analizado una foto
                        @elseif($resultados->count() === 1)
                            1 resultado encontrado
                        @else
                            {{ $resultados->count() }} resultados encontrados
                        @endif
                    </span>
                </div>

                @if($busquedaRealizada && $resultados->isEmpty())
                    <div class="empty-result">
                        No encontramos una pieza con suficiente confianza. Acércate un poco más, encierra solamente el objeto e intenta de nuevo.
                    </div>
                @elseif(!$busquedaRealizada)
                    <div class="empty-result">
                        Las piezas parecidas aparecerán aquí después de tomar o seleccionar una fotografía.
                    </div>
                @else
                    <div class="result-grid">
                        @foreach($resultados as $material)
                            @php
                                $materialInventoryUrl = route('materiales.index', [
                                    'material_id' => $material->id,
                                    'buscar' => $material->numero_parte ?: $material->descripcion,
                                    'destacar' => $material->id,
                                ]) . '#material-' . $material->id;
                                $confidenceLabel = match (true) {
                                    $material->puntaje_visual >= 96 => 'Coincidencia exacta',
                                    $material->puntaje_visual >= 86 => 'Coincidencia muy alta',
                                    $material->puntaje_visual >= 78 => 'Coincidencia alta',
                                    default => 'Posible coincidencia',
                                };
                            @endphp
                            <article class="result-card">
                                <button
                                    type="button"
                                    class="result-photo-button"
                                    data-workspace-lightbox
                                    data-lightbox-title="{{ $material->descripcion }}"
                                    data-lightbox-caption="{{ $material->categoria ?: 'Sin categoría' }} · {{ $material->puntaje_visual }}% de similitud"
                                >
                                    <img src="{{ asset('storage/' . $material->fotografia) }}" class="result-photo" alt="{{ $material->descripcion }}">
                                </button>
                                <div class="result-info">
                                    <div class="result-badges">
                                        <span class="category-badge">{{ $material->categoria ?: 'Sin categoría' }}</span>
                                        @if(($material->motor_visual ?? null) === 'ia')
                                            <span class="ai-engine-badge">Analizado con IA</span>
                                        @endif
                                    </div>
                                    <strong class="result-title">{{ $material->descripcion }}</strong>
                                    <div class="result-meta">
                                        <span><b>No. parte:</b> {{ $material->numero_parte ?: 'N/A' }}</span>
                                        @if($material->apodo)<span><b>Apodo:</b> {{ $material->apodo }}</span>@endif
                                        <span><b>Marca:</b> {{ $material->marca ?: 'Sin marca' }}</span>
                                        <span><b>Almacén:</b> {{ $material->almacen ?: 'Sin definir' }}</span>
                                        <span><b>Stock:</b> {{ $material->stock }} pzas</span>
                                    </div>
                                    <div class="score-row">
                                        <div class="score-summary">
                                            <div class="score-heading">
                                                <span class="score-label">{{ $confidenceLabel }}</span>
                                                <span class="score">{{ $material->puntaje_visual }}%</span>
                                            </div>
                                            <span class="score-meter" role="meter" aria-label="Similitud visual" aria-valuenow="{{ $material->puntaje_visual }}" aria-valuemin="0" aria-valuemax="100">
                                                <i style="width: {{ $material->puntaje_visual }}%"></i>
                                            </span>
                                        </div>
                                        <a href="{{ $materialInventoryUrl }}" class="result-link">Ver en inventario</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </main>
</div>

<section class="camera-modal" id="cameraModal" hidden role="dialog" aria-modal="true" aria-labelledby="cameraTitle">
    <div class="camera-dialog">
        <header class="camera-header">
            <div>
                <strong id="cameraTitle">Fotografiar pieza</strong>
                <small>Centra la pieza antes de capturar</small>
            </div>
            <button type="button" class="camera-close" id="closeCamera" aria-label="Cerrar cámara" title="Cerrar cámara">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </header>
        <div class="camera-stage">
            <video id="videoElement" autoplay playsinline muted></video>
            <canvas id="canvasElement" hidden></canvas>
            <div class="camera-guide" aria-hidden="true">
                <div class="camera-guide-frame">
                    <span class="camera-corner top-left"></span>
                    <span class="camera-corner top-right"></span>
                    <span class="camera-corner bottom-left"></span>
                    <span class="camera-corner bottom-right"></span>
                </div>
                <div class="camera-instruction"><span>Centra la pieza dentro de las guías</span></div>
            </div>
        </div>
        <div class="camera-controls">
            <label class="zoom-control" for="cameraZoom">
                <span>Zoom</span>
                <input id="cameraZoom" type="range" min="1" max="3" step="0.1" value="1">
                <output id="cameraZoomValue">1.0x</output>
            </label>
            <div class="camera-actions">
                <button type="button" class="visual-button visual-button-green" id="capturePhoto">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M14.5 4 16 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3l1.5-3h5Z"/></svg>
                    Capturar y analizar
                </button>
                <button type="button" class="visual-button visual-button-light" id="cancelCamera">Cancelar</button>
            </div>
        </div>
    </div>
</section>

<section class="crop-modal" id="cropModal" hidden role="dialog" aria-modal="true" aria-labelledby="cropTitle">
    <div class="crop-dialog">
        <header class="camera-header">
            <div>
                <strong id="cropTitle">Encierra la pieza</strong>
                <small>Dibuja un cuadro alrededor del objeto que quieres identificar</small>
            </div>
            <button type="button" class="camera-close" id="closeCrop" aria-label="Cerrar recorte" title="Cerrar recorte">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </header>
        <div class="crop-stage" id="cropStage">
            <img class="crop-source" id="cropSource" alt="Fotografía para recortar">
            <div class="crop-selection" id="cropSelection"></div>
        </div>
        <div class="crop-controls">
            <div class="crop-help">
                <strong>Deja la menor cantidad de fondo posible</strong>
                <span>Puedes volver a dibujar el cuadro cuantas veces necesites.</span>
            </div>
            <div class="crop-actions">
                <button type="button" class="visual-button visual-button-green" id="analyzeCrop">
                    Analizar esta pieza
                </button>
                <button type="button" class="visual-button visual-button-orange" id="useFullImage">
                    Usar foto completa
                </button>
                <button type="button" class="visual-button visual-button-light" id="cancelCrop">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</section>

<script>
    (() => {
        const form = document.getElementById('visualForm');
        const input = document.getElementById('fotografia');
        const dropArea = document.getElementById('dropArea');
        const openCamera = document.getElementById('openCamera');
        const openFile = document.getElementById('openFile');
        const modal = document.getElementById('cameraModal');
        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('canvasElement');
        const zoom = document.getElementById('cameraZoom');
        const zoomValue = document.getElementById('cameraZoomValue');
        const cropModal = document.getElementById('cropModal');
        const cropStage = document.getElementById('cropStage');
        const cropSource = document.getElementById('cropSource');
        const cropSelection = document.getElementById('cropSelection');
        let stream = null;
        let previewUrl = null;
        let cropUrl = null;
        let crop = { x: .06, y: .06, width: .88, height: .88 };
        let cropStart = null;
        let cropBeforeDraw = null;
        let activePointer = null;

        const beginAnalysis = () => {
            dropArea.classList.add('is-loading');
            window.setTimeout(() => form.submit(), 120);
        };

        const validImage = (file) => {
            if (file.size > 8 * 1024 * 1024) {
                window.alert('La imagen no debe pesar más de 8 MB.');
                input.value = '';
                return false;
            }

            if (!file.type.startsWith('image/')) {
                window.alert('Selecciona una fotografía JPG, PNG o WEBP.');
                input.value = '';
                return false;
            }

            return true;
        };

        const imageBounds = () => ({
            imageRect: cropSource.getBoundingClientRect(),
            stageRect: cropStage.getBoundingClientRect(),
        });

        const renderCrop = () => {
            if (!cropSource.naturalWidth) return;

            const { imageRect, stageRect } = imageBounds();
            cropSelection.style.left = `${imageRect.left - stageRect.left + (crop.x * imageRect.width)}px`;
            cropSelection.style.top = `${imageRect.top - stageRect.top + (crop.y * imageRect.height)}px`;
            cropSelection.style.width = `${crop.width * imageRect.width}px`;
            cropSelection.style.height = `${crop.height * imageRect.height}px`;
        };

        const pointInsideImage = (event) => {
            const { imageRect } = imageBounds();
            const clamp = (value) => Math.max(0, Math.min(1, value));

            return {
                x: clamp((event.clientX - imageRect.left) / Math.max(1, imageRect.width)),
                y: clamp((event.clientY - imageRect.top) / Math.max(1, imageRect.height)),
            };
        };

        const closeCropper = (clearFile = true) => {
            cropModal.hidden = true;
            document.body.style.overflow = '';
            activePointer = null;
            cropStart = null;

            if (clearFile) input.value = '';
        };

        const openCropper = (file) => {
            if (!file || !validImage(file)) return;

            if (cropUrl) URL.revokeObjectURL(cropUrl);
            cropUrl = URL.createObjectURL(file);
            crop = { x: .06, y: .06, width: .88, height: .88 };
            cropSource.onload = () => window.requestAnimationFrame(renderCrop);
            cropSource.src = cropUrl;
            cropModal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.getElementById('analyzeCrop')?.focus();
        };

        const assignFileAndAnalyze = (file) => {
            try {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
            } catch (_) {
                window.alert('El navegador no pudo preparar el recorte. Actualiza el navegador e intenta nuevamente.');
                return;
            }

            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(file);
            const currentPreview = dropArea.querySelector('.main-preview');
            if (currentPreview) currentPreview.src = previewUrl;
            closeCropper(false);
            beginAnalysis();
        };

        const analyzeCrop = () => {
            const naturalWidth = cropSource.naturalWidth;
            const naturalHeight = cropSource.naturalHeight;

            if (!naturalWidth || !naturalHeight || crop.width < .025 || crop.height < .025) {
                window.alert('Dibuja un cuadro más grande alrededor de la pieza.');
                return;
            }

            const sourceX = Math.round(crop.x * naturalWidth);
            const sourceY = Math.round(crop.y * naturalHeight);
            const sourceWidth = Math.max(1, Math.round(crop.width * naturalWidth));
            const sourceHeight = Math.max(1, Math.round(crop.height * naturalHeight));
            const maxSize = 1600;
            const scale = Math.min(1, maxSize / Math.max(sourceWidth, sourceHeight));
            const outputWidth = Math.max(1, Math.round(sourceWidth * scale));
            const outputHeight = Math.max(1, Math.round(sourceHeight * scale));
            const output = document.createElement('canvas');
            output.width = outputWidth;
            output.height = outputHeight;
            const context = output.getContext('2d', { alpha: false });
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, outputWidth, outputHeight);
            context.drawImage(
                cropSource,
                sourceX,
                sourceY,
                sourceWidth,
                sourceHeight,
                0,
                0,
                outputWidth,
                outputHeight,
            );

            output.toBlob((blob) => {
                if (!blob) {
                    window.alert('No se pudo preparar el recorte. Intenta nuevamente.');
                    return;
                }

                assignFileAndAnalyze(new File(
                    [blob],
                    `pieza_recortada_${Date.now()}.jpg`,
                    { type: 'image/jpeg' },
                ));
            }, 'image/jpeg', .88);
        };

        const chooseFile = (capture = false) => {
            input.value = '';
            if (capture) {
                input.setAttribute('capture', 'environment');
            } else {
                input.removeAttribute('capture');
            }
            input.click();
        };

        const stopCamera = () => {
            stream?.getTracks().forEach((track) => track.stop());
            stream = null;
            video.srcObject = null;
            modal.hidden = true;
            document.body.style.overflow = '';
            zoom.value = '1';
            zoomValue.value = '1.0x';
            video.style.setProperty('--camera-zoom', '1');
            openCamera?.focus();
        };

        const startWebCamera = async () => {
            if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
                chooseFile(true);
                return;
            }

            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                    },
                    audio: false,
                });
                video.srcObject = stream;
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
                await video.play();
                document.getElementById('closeCamera')?.focus();
            } catch (_) {
                stopCamera();
                chooseFile(true);
            }
        };

        const capture = () => {
            if (!video.videoWidth || !video.videoHeight) {
                window.alert('La cámara todavía se está preparando. Intenta nuevamente.');
                return;
            }

            const zoomLevel = Number(zoom.value || 1);
            const sourceWidth = video.videoWidth / zoomLevel;
            const sourceHeight = video.videoHeight / zoomLevel;
            const sourceX = (video.videoWidth - sourceWidth) / 2;
            const sourceY = (video.videoHeight - sourceHeight) / 2;
            const maxWidth = 1600;
            const scale = Math.min(1, maxWidth / sourceWidth);
            canvas.width = Math.max(1, Math.round(sourceWidth * scale));
            canvas.height = Math.max(1, Math.round(sourceHeight * scale));
            canvas.getContext('2d').drawImage(
                video,
                sourceX,
                sourceY,
                sourceWidth,
                sourceHeight,
                0,
                0,
                canvas.width,
                canvas.height,
            );

            canvas.toBlob((blob) => {
                if (!blob) {
                    window.alert('No se pudo preparar la fotografía. Intenta nuevamente.');
                    return;
                }

                const file = new File(
                    [blob],
                    `pieza_${Date.now()}.jpg`,
                    { type: 'image/jpeg' },
                );
                stopCamera();
                openCropper(file);
            }, 'image/jpeg', .84);
        };

        input.addEventListener('change', () => openCropper(input.files?.[0]));
        openFile?.addEventListener('click', () => chooseFile(false));
        openCamera?.addEventListener('click', startWebCamera);
        document.getElementById('capturePhoto')?.addEventListener('click', capture);
        document.getElementById('closeCamera')?.addEventListener('click', stopCamera);
        document.getElementById('cancelCamera')?.addEventListener('click', stopCamera);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) stopCamera();
        });
        zoom.addEventListener('input', () => {
            const value = Number(zoom.value).toFixed(1);
            zoomValue.value = `${value}x`;
            video.style.setProperty('--camera-zoom', value);
        });
        cropStage.addEventListener('pointerdown', (event) => {
            if (!cropSource.naturalWidth) return;

            event.preventDefault();
            activePointer = event.pointerId;
            cropStage.setPointerCapture?.(event.pointerId);
            cropBeforeDraw = { ...crop };
            cropStart = pointInsideImage(event);
            crop = { x: cropStart.x, y: cropStart.y, width: 0, height: 0 };
            renderCrop();
        });
        cropStage.addEventListener('pointermove', (event) => {
            if (activePointer !== event.pointerId || !cropStart) return;

            event.preventDefault();
            const point = pointInsideImage(event);
            crop = {
                x: Math.min(cropStart.x, point.x),
                y: Math.min(cropStart.y, point.y),
                width: Math.abs(point.x - cropStart.x),
                height: Math.abs(point.y - cropStart.y),
            };
            renderCrop();
        });

        const finishDrawing = (event) => {
            if (activePointer !== event.pointerId) return;

            if (crop.width < .025 || crop.height < .025) {
                crop = cropBeforeDraw ?? { x: .06, y: .06, width: .88, height: .88 };
                renderCrop();
            }

            activePointer = null;
            cropStart = null;
            cropStage.releasePointerCapture?.(event.pointerId);
        };

        cropStage.addEventListener('pointerup', finishDrawing);
        cropStage.addEventListener('pointercancel', finishDrawing);
        document.getElementById('analyzeCrop')?.addEventListener('click', analyzeCrop);
        document.getElementById('useFullImage')?.addEventListener('click', () => {
            crop = { x: 0, y: 0, width: 1, height: 1 };
            renderCrop();
            analyzeCrop();
        });
        document.getElementById('closeCrop')?.addEventListener('click', () => closeCropper());
        document.getElementById('cancelCrop')?.addEventListener('click', () => closeCropper());
        cropModal.addEventListener('click', (event) => {
            if (event.target === cropModal) closeCropper();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) stopCamera();
            if (event.key === 'Escape' && !cropModal.hidden) closeCropper();
        });
        window.addEventListener('pagehide', stopCamera);
        window.addEventListener('resize', renderCrop);

        dropArea.addEventListener('dragover', (event) => {
            event.preventDefault();
            dropArea.classList.add('is-dragging');
        });
        dropArea.addEventListener('dragleave', () => dropArea.classList.remove('is-dragging'));
        dropArea.addEventListener('drop', (event) => {
            event.preventDefault();
            dropArea.classList.remove('is-dragging');
            const file = event.dataTransfer?.files?.[0];
            if (!file) return;
            openCropper(file);
        });
    })();
</script>
</body>
</html>
