(() => {
    'use strict';

    document.documentElement.classList.add('auth-js');

    const clearServerError = (input) => {
        const field = input.closest('.auth-field');

        if (!field) {
            return;
        }

        field.classList.remove('auth-field-invalid');
        input.removeAttribute('aria-invalid');
        field.querySelectorAll('.auth-field-error').forEach((error) => {
            error.hidden = true;
        });

        const alert = input.form?.parentElement?.querySelector('.auth-alert');

        if (alert) {
            alert.hidden = true;
        }
    };

    const initializePasswordToggles = () => {
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            const input = document.getElementById(button.dataset.passwordToggle || '');

            if (!(input instanceof HTMLInputElement)) {
                button.hidden = true;
                return;
            }

            button.addEventListener('click', () => {
                const shouldShow = input.type === 'password';
                input.type = shouldShow ? 'text' : 'password';
                button.classList.toggle('auth-password-visible', shouldShow);
                button.setAttribute('aria-pressed', String(shouldShow));
                button.setAttribute(
                    'aria-label',
                    `${shouldShow ? 'Hide' : 'Show'} ${input.id === 'password_confirmation' ? 'password confirmation' : 'password'}`
                );
                input.focus({preventScroll: true});
            });
        });
    };

    const initializeCapsLockHints = () => {
        document.querySelectorAll('input[type="password"]').forEach((input) => {
            const field = input.closest('.auth-field');
            const hint = field?.querySelector('[data-caps-lock]');

            if (!hint) {
                return;
            }

            const updateHint = (event) => {
                const capsLockOn = typeof event.getModifierState === 'function'
                    && event.getModifierState('CapsLock');
                hint.hidden = !capsLockOn;
            };

            input.addEventListener('keydown', updateHint);
            input.addEventListener('keyup', updateHint);
            input.addEventListener('blur', () => {
                hint.hidden = true;
            });
        });
    };

    const initializeRegistrationFeedback = () => {
        const form = document.querySelector('[data-registration-form]');

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const password = form.querySelector('[data-new-password]');
        const confirmation = form.querySelector('[data-password-confirmation]');
        const meter = form.querySelector('[data-password-meter]');
        const feedback = form.querySelector('[data-password-feedback]');
        const match = form.querySelector('[data-password-match]');

        if (!(password instanceof HTMLInputElement)) {
            return;
        }

        const updateStrength = () => {
            const value = password.value;
            const score = [
                value.length >= 8 && value.length <= 72,
                /[a-z]/.test(value) && /[A-Z]/.test(value),
                /[0-9]/.test(value),
                value.length >= 12 || /[^A-Za-z0-9]/.test(value),
            ].filter(Boolean).length;

            if (meter) {
                meter.dataset.score = String(value === '' ? 0 : score);
            }

            if (feedback) {
                if (value === '') {
                    feedback.textContent = 'Use 8-72 characters with uppercase, lowercase, and a number.';
                } else if (score < 3) {
                    feedback.textContent = 'Add the missing length, letter case, or number requirement.';
                } else if (score === 3) {
                    feedback.textContent = 'Password meets the account requirements.';
                } else {
                    feedback.textContent = 'Strong password.';
                }
            }
        };

        const updateMatch = () => {
            if (!(confirmation instanceof HTMLInputElement) || !match) {
                return;
            }

            if (confirmation.value === '') {
                confirmation.setCustomValidity('');
                match.dataset.state = '';
                match.textContent = 'Enter the same password again.';
                return;
            }

            const matches = confirmation.value === password.value;
            confirmation.setCustomValidity(matches ? '' : 'Passwords do not match.');
            match.dataset.state = matches ? 'valid' : 'invalid';
            match.textContent = matches ? 'Passwords match.' : 'Passwords do not match yet.';
        };

        password.addEventListener('input', () => {
            updateStrength();
            updateMatch();
        });

        confirmation?.addEventListener('input', updateMatch);
        updateStrength();
        updateMatch();
    };

    const initializeForms = () => {
        document.querySelectorAll('[data-auth-form]').forEach((form) => {
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            form.querySelectorAll('input:not([type="hidden"])').forEach((input) => {
                input.addEventListener('input', () => clearServerError(input), {once: true});
            });

            form.addEventListener('submit', () => {
                const button = form.querySelector('[data-submit-button]');
                const label = form.querySelector('[data-submit-text]');

                if (!(button instanceof HTMLButtonElement) || !form.checkValidity()) {
                    return;
                }

                button.disabled = true;
                button.setAttribute('aria-busy', 'true');

                if (label) {
                    label.textContent = form.dataset.submitLabel || 'Please wait...';
                }
            });
        });
    };

    initializePasswordToggles();
    initializeCapsLockHints();
    initializeRegistrationFeedback();
    initializeForms();
})();
