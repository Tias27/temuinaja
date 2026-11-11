export function initializeReportForm() {
    const input = document.querySelector('[data-report-form] input[type="file"]');
    const previews = document.querySelector('[data-upload-previews]');
    const error = document.querySelector('[data-upload-error]');
    if (!input || !previews || !error) return;

    let imageUrls = [];
    const clearPreviews = () => {
        imageUrls.forEach((url) => URL.revokeObjectURL(url));
        imageUrls = [];
        previews.replaceChildren();
    };

    input.addEventListener('change', () => {
        clearPreviews();
        const files = Array.from(input.files);
        let message = '';
        if (files.length > 3) message = 'Pilih maksimal 3 foto';
        else if (files.some((file) => file.size > 5 * 1024 * 1024))
            message = 'Ukuran tiap foto maksimal 5 MB';
        else if (
            files.some((file) => !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))
        )
            message = 'Gunakan foto JPG, PNG, atau WebP';

        input.setCustomValidity(message);
        input.setAttribute('aria-invalid', String(Boolean(message)));
        error.textContent = message;
        if (message) return;

        files.forEach((file) => {
            const url = URL.createObjectURL(file);
            imageUrls.push(url);
            const figure = document.createElement('figure');
            const image = document.createElement('img');
            image.src = url;
            image.alt = 'Pratinjau ' + file.name;
            const caption = document.createElement('figcaption');
            caption.textContent = file.name;
            figure.append(image, caption);
            previews.append(figure);
        });
    });
    window.addEventListener('pagehide', clearPreviews);
}
