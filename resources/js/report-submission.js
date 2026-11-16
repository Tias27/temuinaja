export function initializeReportSubmission(form) {
    const notice = form.querySelector('[data-report-errors]');
    const errorFields = [...form.querySelectorAll('[data-field-error]')];
    const submit = form.querySelector('button[type="submit"]');
    if (!notice || !submit) return;

    const originalLabel = submit.textContent;
    let submitting = false;
    const showNotice = (message) => {
        notice.textContent = message;
        notice.hidden = false;
    };
    const showErrors = (errors) => {
        let firstInvalid;
        errorFields.forEach((element) => {
            const name = element.dataset.fieldError;
            const messages = Object.entries(errors)
                .filter(([key]) => key === name || key.startsWith(`${name}.`))
                .flatMap(([, values]) => values)
                .filter((value) => typeof value === 'string');
            element.textContent = messages.join(' ');
            element.hidden = !messages.length;
            const field = element.closest('.field').querySelector('input, textarea');
            field.setAttribute('aria-invalid', String(Boolean(messages.length)));
            const descriptions = new Set(
                (field.getAttribute('aria-describedby') || '').split(' ').filter(Boolean),
            );
            descriptions.add(element.id);
            field.setAttribute('aria-describedby', [...descriptions].join(' '));
            if (messages.length && !firstInvalid) firstInvalid = field;
        });
        firstInvalid?.focus();
        return Boolean(firstInvalid);
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submitting) return;
        submitting = true;
        submit.disabled = true;
        submit.textContent = 'Mengirim laporan...';
        form.setAttribute('aria-busy', 'true');
        notice.hidden = true;
        showErrors({});

        try {
            // Keep the current document and FileList intact when server validation fails.
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                redirect: 'error',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (response.status === 422) {
                const payload = await response.json();
                showNotice(
                    'Periksa kolom yang ditandai. Foto tetap terpilih, tidak perlu diunggah ulang',
                );
                if (!showErrors(payload.errors || {})) notice.scrollIntoView({ block: 'center' });
                return;
            }
            if (response.status === 419) {
                const freshForm = await fetch(form.action, {
                    credentials: 'same-origin',
                    redirect: 'error',
                    headers: { Accept: 'text/html' },
                });
                if (!freshForm.ok) throw new Error('Session refresh failed');
                const document = new DOMParser().parseFromString(
                    await freshForm.text(),
                    'text/html',
                );
                const token = document.querySelector('[data-report-form] input[name="_token"]');
                if (!token?.value) throw new Error('Missing CSRF token');
                form.elements.namedItem('_token').value = token.value;
                showNotice('Sesi sudah diperbarui. Klik Kirim laporan lagi, foto tetap terpilih');
                return;
            }
            if (response.status === 429) {
                showNotice(
                    'Terlalu banyak pengiriman. Tunggu sebentar lalu coba lagi, foto tetap terpilih',
                );
                return;
            }
            if (!response.ok) throw new Error('Report submission failed');
            const payload = await response.json();
            if (typeof payload.redirect !== 'string') throw new Error('Missing redirect');
            const destination = new URL(payload.redirect, window.location.href);
            if (destination.origin !== window.location.origin) throw new Error('Invalid redirect');
            window.location.assign(destination.href);
        } catch {
            showNotice(
                'Pengiriman belum bisa dikonfirmasi. Foto tetap terpilih; cek Riwayat sebelum mencoba lagi',
            );
        } finally {
            submitting = false;
            submit.disabled = false;
            submit.textContent = originalLabel;
            form.removeAttribute('aria-busy');
            if (!notice.hidden) notice.scrollIntoView({ block: 'nearest' });
        }
    });
}
