// On-screen keyboard for kiosk/touchscreen.
// Activate by adding `data-osk="true"` to an input element.

let keyboard = null;
let activeInput = null;
let activeSubmitTarget = null;

function getKeyboardContainer() {
    return document.querySelector('.keyboard-container');
}

function getKeyboardElement() {
    return document.querySelector('.simple-keyboard');
}

function getKeyboardPreviewText() {
    return document.getElementById('keyboardPreviewText');
}

function updatePreviewText(value) {
    const previewEl = getKeyboardPreviewText();
    if (previewEl) {
        previewEl.textContent = value || '';
    }
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
            const oldValue = activeInput.value;
            activeInput.value = value;
            updatePreviewText(value);
            try {
                activeInput.dispatchEvent(new Event('input', { bubbles: true }));
            } catch (_) {}
            
            // Highlight the button if value changed
            if (value.length > oldValue.length) {
                const lastChar = value.charAt(value.length - 1);
                highlightKeyboardButton(lastChar);
            }
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
    const keyboardContainer = getKeyboardContainer();
    const kb = ensureKeyboard();
    if (!keyboardContainer || !kb) return;

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
    updatePreviewText(input.value || '');

    keyboardContainer.classList.add('active');
    const mainSection = getMainSection();
    if (mainSection) mainSection.style.paddingBottom = '480px';
}

function hideKeyboard() {
    const keyboardContainer = getKeyboardContainer();
    if (keyboardContainer) keyboardContainer.classList.remove('active');
    const mainSection = getMainSection();
    if (mainSection) mainSection.style.paddingBottom = '';
    updatePreviewText('');
    activeInput = null;
    activeSubmitTarget = null;
}

function highlightKeyboardButton(char) {
    if (!keyboard) return;
    
    const theKeyboard = getKeyboardElement();
    if (!theKeyboard) return;
    
    // Map characters to button data-skbtn attribute
    let buttonKey = char.toLowerCase();
    
    // Handle special characters
    if (char === ' ') buttonKey = '{space}';
    else if (char === '\n' || char === '\r') buttonKey = '{enter}';
    
    // Find the button
    const button = theKeyboard.querySelector(`[data-skbtn="${buttonKey}"]`);
    if (!button) return;
    
    // Add highlight class
    button.classList.add('hg-button-active-feedback');
    
    // Remove highlight after animation
    setTimeout(() => {
        button.classList.remove('hg-button-active-feedback');
    }, 200);
}

function isFocusableInput(el) {
    return !!el && el.matches && el.matches('input[data-osk="true"], textarea[data-osk="true"]');
}

document.addEventListener('focusin', (e) => {
    const target = e.target;
    if (!isFocusableInput(target)) return;
    showKeyboardForInput(target);
});

// Listen for actual keyboard input to highlight buttons
document.addEventListener('input', (e) => {
    const target = e.target;
    if (!isFocusableInput(target)) return;
    
    // Update preview text
    updatePreviewText(target.value);
    
    // Update keyboard display
    if (keyboard) {
        keyboard.setInput(target.value);
    }
    
    // Get the last character typed
    const inputValue = target.value;
    if (inputValue && inputValue.length > 0) {
        const lastChar = inputValue.charAt(inputValue.length - 1);
        highlightKeyboardButton(lastChar);
    }
});

// Listen for physical keyboard presses
document.addEventListener('keydown', (e) => {
    if (!activeInput) return;
    
    // Handle backspace
    if (e.key === 'Backspace') {
        highlightKeyboardButton('{bksp}');
        return;
    }
    
    // Handle enter
    if (e.key === 'Enter') {
        highlightKeyboardButton('{enter}');
        return;
    }
    
    // Handle regular characters
    if (e.key.length === 1) {
        highlightKeyboardButton(e.key);
    }
});

// Hide keyboard when clicking outside input and keyboard
document.addEventListener('pointerdown', (e) => {
    const keyboardContainer = getKeyboardContainer();
    if (!keyboardContainer) return;

    const target = e.target;
    const clickedInsideKeyboard = keyboardContainer.contains(target);
    const clickedOnInput = isFocusableInput(target);
    if (!clickedInsideKeyboard && !clickedOnInput) {
        hideKeyboard();
    }
});
