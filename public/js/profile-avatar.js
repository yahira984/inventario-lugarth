(() => {
    'use strict';

    const form = document.getElementById('profileInformationForm');
    const input = document.getElementById('avatar');
    const status = document.getElementById('avatarProcessingStatus');
    const preview = document.querySelector('.profile-avatar-button img');
    const submit = form?.querySelector('button[type="submit"]');

    if (!form || !input) return;

    const setStatus = (message, tone = 'normal') => {
        if (!status) return;
        status.textContent = message;
        status.style.color = tone === 'error' ? '#b42318' : tone === 'success' ? '#067647' : '#64748b';
    };

    const loadImage = (file) => new Promise((resolve, reject) => {
        const image = new Image();
        const url = URL.createObjectURL(file);
        image.onload = () => {
            URL.revokeObjectURL(url);
            resolve(image);
        };
        image.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('No pudimos leer esta imagen. En iPhone, selecciona una foto compatible o toma una nueva.'));
        };
        image.src = url;
    });

    const toBlob = (canvas) => new Promise((resolve) => {
        canvas.toBlob(resolve, 'image/jpeg', 0.78);
    });

    input.addEventListener('change', async () => {
        const file = input.files?.[0];
        if (!file) {
            setStatus('');
            return;
        }

        if (!file.type.startsWith('image/')) {
            input.value = '';
            setStatus('Selecciona una fotografía válida.', 'error');
            return;
        }

        if (submit) submit.disabled = true;
        setStatus('Preparando y reduciendo la fotografía...');

        try {
            const image = await loadImage(file);
            const maxSide = 1600;
            const scale = Math.min(1, maxSide / Math.max(image.naturalWidth, image.naturalHeight));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
            canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
            const context = canvas.getContext('2d', { alpha: false });
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);

            const blob = await toBlob(canvas);
            if (!blob) throw new Error('No se pudo optimizar la fotografía.');

            const optimized = new File([blob], 'foto-perfil.jpg', {
                type: 'image/jpeg',
                lastModified: Date.now(),
            });
            const transfer = new DataTransfer();
            transfer.items.add(optimized);
            input.files = transfer.files;

            if (preview) {
                const previousPreview = preview.dataset.localPreview;
                if (previousPreview) URL.revokeObjectURL(previousPreview);
                const previewUrl = URL.createObjectURL(optimized);
                preview.dataset.localPreview = previewUrl;
                preview.src = previewUrl;
            }

            const sizeKb = Math.max(1, Math.round(optimized.size / 1024));
            setStatus(`Foto lista y optimizada (${sizeKb} KB).`, 'success');
        } catch (error) {
            input.value = '';
            setStatus(error.message || 'No se pudo preparar la fotografía.', 'error');
        } finally {
            if (submit) submit.disabled = false;
        }
    });
})();
