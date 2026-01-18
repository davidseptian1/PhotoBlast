// On-screen keyboard for kiosk/touchscreen.
// Activate by adding `data-osk="true"` to an input element.

let keyboard = null;
let activeInput = null;
let activeSubmitTarget = null;

function getKeyboardElement() {
    return document.querySelector('.simple-keyboard');
}

function getMainSection() {
    return document.querySelector('section.flow-container') || document.querySelector('section.content');
}

function ensureKeyboard() {
    const theKeyboard = getKeyboardElement();
    if (!theKeyboard) return null;
    if (!window.SimpleKeyboard || !window.SimpleKeyboard.default) return null;

    if (keyboard) return keyboard;

    const Keyboard = window.SimpleKeyboard.default;
    keyboard = new Keyboard({
        onChange: (value) => {
            if (!activeInput) return;
            activeInput.value = value;
            try {
                activeInput.dispatchEvent(new Event('input', { bubbles: true }));
            } catch (_) {}
        },
        onKeyPress: (button) => {
            if (button === '{enter}') {
                if (activeSubmitTarget) {
                    activeSubmitTarget.click();
                    return;
                }
                if (activeInput && activeInput.form) {
                    activeInput.form.requestSubmit?.();
                    return;
                }
            }

            if (button === '{shift}' || button === '{lock}') {
                const current = keyboard.options.layoutName || 'default';
                keyboard.setOptions({ layoutName: current === 'default' ? 'shift' : 'default' });
            }
        },
    });

    return keyboard;
}

function showKeyboardForInput(input) {
    const theKeyboard = getKeyboardElement();
    const kb = ensureKeyboard();
    if (!theKeyboard || !kb) return;

    activeInput = input;

    // Optional: point enter key to a specific button.
    activeSubmitTarget = null;
    const submitTargetSelector = input.getAttribute('data-osk-enter-target');
    if (submitTargetSelector) {
        activeSubmitTarget = document.querySelector(submitTargetSelector);
    }

    // Email-friendly layout.
    const layoutType = input.getAttribute('data-osk-layout') || (input.type === 'email' ? 'email' : 'default');
    if (layoutType === 'email') {
        kb.setOptions({
            layoutName: 'default',
            layout: {
                default: [
                    '1 2 3 4 5 6 7 8 9 0 {bksp}',
                    'q w e r t y u i o p',
                    'a s d f g h j k l',
                    '{shift} z x c v b n m @ .',
                    '{space} {enter}',
                ],
                shift: [
                    '! @ # $ % ^ & * ( ) {bksp}',
                    'Q W E R T Y U I O P',
                    'A S D F G H J K L',
                    '{shift} Z X C V B N M _ -',
                    '{space} {enter}',
                ],
            },
        });
    } else {
        // Reset to default (SimpleKeyboard's built-in layout).
        kb.setOptions({ layout: undefined, layoutName: 'default' });
    }

    kb.setInput(input.value || '');

    theKeyboard.style.display = 'block';
    const mainSection = getMainSection();
    if (mainSection) mainSection.style.paddingBottom = '260px';
}

function hideKeyboard() {
    const theKeyboard = getKeyboardElement();
    if (theKeyboard) theKeyboard.style.display = 'none';
    const mainSection = getMainSection();
    if (mainSection) mainSection.style.paddingBottom = '';
    activeInput = null;
    activeSubmitTarget = null;
}

function isFocusableInput(el) {
    return !!el && el.matches && el.matches('input[data-osk="true"], textarea[data-osk="true"]');
}

document.addEventListener('focusin', (e) => {
    const target = e.target;
    if (!isFocusableInput(target)) return;
    showKeyboardForInput(target);
});

// Hide keyboard when clicking outside input and keyboard
document.addEventListener('pointerdown', (e) => {
    const theKeyboard = getKeyboardElement();
    if (!theKeyboard) return;

    const target = e.target;
    const clickedInsideKeyboard = theKeyboard.contains(target);
    const clickedOnInput = isFocusableInput(target);
    if (!clickedInsideKeyboard && !clickedOnInput) {
        hideKeyboard();
    }
});
