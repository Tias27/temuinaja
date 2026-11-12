export function initializePhoneNumbers() {
    document.querySelectorAll('[data-phone-digits]').forEach((input) => {
        input.addEventListener('beforeinput', (event) => {
            if (event.inputType === 'insertText' && event.data && /[^0-9]/.test(event.data)) {
                event.preventDefault();
            }
        });

        input.addEventListener('input', () => {
            const digits = input.value.replace(/[^0-9]/g, '');
            if (digits === input.value) return;

            const cursor = input.selectionStart ?? input.value.length;
            const digitsBeforeCursor = input.value.slice(0, cursor).replace(/[^0-9]/g, '').length;
            input.value = digits;
            input.setSelectionRange(digitsBeforeCursor, digitsBeforeCursor);
        });
    });
}
