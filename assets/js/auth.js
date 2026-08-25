/**
 * Discora - Authentication Modal Controller & GSAP Animations
 * Handles Login/Register Popup, Smooth Transitions, Validation & AJAX Authentication
 */

document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // 1. Elements Cache
    // -------------------------------------------------------------
    const overlay = document.getElementById('discoraAuthOverlay');
    const modal = document.getElementById('discoraAuthModal');
    const closeBtn = document.getElementById('authModalCloseBtn');
    
    const loginView = document.getElementById('loginView');
    const registerView = document.getElementById('registerView');
    const goToRegisterBtn = document.getElementById('goToRegisterBtn');
    const goToLoginBtn = document.getElementById('goToLoginBtn');
    
    const loginForm = document.getElementById('discoraLoginForm');
    const registerForm = document.getElementById('discoraRegisterForm');
    const alertBox = document.getElementById('authAlertBox');
    
    let isModalOpen = false;
    let currentTab = 'login'; // 'login' or 'register'
    let isTransitioning = false;

    // -------------------------------------------------------------
    // 2. Helper: Reset View Styles (Prevents GSAP inline opacity leaks)
    // -------------------------------------------------------------
    function resetViewStyles(view) {
        if (!view) return;
        if (typeof gsap !== 'undefined') {
            gsap.killTweensOf(view);
        }
        view.style.opacity = '1';
        view.style.transform = 'none';
        view.style.visibility = 'visible';

        const children = view.querySelectorAll('.auth-header, .auth-field-group, .btn-auth-primary, .auth-switch-prompt, .demo-creds-box, .row');
        children.forEach(el => {
            if (typeof gsap !== 'undefined') {
                gsap.killTweensOf(el);
            }
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
    }

    // -------------------------------------------------------------
    // 3. Alert Notification Helper
    // -------------------------------------------------------------
    function showAlert(type, message) {
        if (!alertBox) return;
        alertBox.className = `auth-alert auth-alert-${type}`;
        const icon = alertBox.querySelector('.auth-alert-icon');
        const text = alertBox.querySelector('.auth-alert-text');
        
        if (icon) {
            icon.className = type === 'success' 
                ? 'auth-alert-icon bi bi-check-circle-fill me-2' 
                : (type === 'warning' ? 'auth-alert-icon bi bi-exclamation-triangle-fill me-2' : 'auth-alert-icon bi bi-exclamation-circle-fill me-2');
        }
        if (text) text.textContent = message;
        alertBox.classList.remove('d-none');

        if (type === 'success') {
            setTimeout(() => {
                alertBox.classList.add('d-none');
            }, 5000);
        }
    }

    function hideAlert() {
        if (alertBox) alertBox.classList.add('d-none');
    }

    // -------------------------------------------------------------
    // 4. Clear Inline Validation Errors
    // -------------------------------------------------------------
    function clearValidationErrors(form) {
        if (!form) return;
        form.querySelectorAll('.auth-input').forEach(input => input.classList.remove('is-invalid'));
        form.querySelectorAll('.auth-field-error').forEach(err => {
            err.textContent = '';
            err.classList.remove('show');
        });
        hideAlert();
    }

    function setFieldError(fieldId, errorMsg) {
        const input = document.getElementById(fieldId);
        const errorElem = document.getElementById(fieldId + 'Error');
        if (input) input.classList.add('is-invalid');
        if (errorElem) {
            errorElem.textContent = errorMsg;
            errorElem.classList.add('show');
        }
    }

    // -------------------------------------------------------------
    // 5. Tab State Synchronization
    // -------------------------------------------------------------
    function setInitialTab(tab) {
        currentTab = tab;
        isTransitioning = false;
        
        resetViewStyles(loginView);
        resetViewStyles(registerView);

        if (tab === 'register') {
            loginView?.classList.add('d-none');
            registerView?.classList.remove('d-none');
            if (registerView) {
                registerView.style.opacity = '1';
                registerView.style.transform = 'none';
            }
        } else {
            registerView?.classList.add('d-none');
            loginView?.classList.remove('d-none');
            if (loginView) {
                loginView.style.opacity = '1';
                loginView.style.transform = 'none';
            }
        }
    }

    // -------------------------------------------------------------
    // 6. GSAP Modal Open & Close Animations
    // -------------------------------------------------------------
    window.openDiscoraAuthModal = function(tab = 'login') {
        isModalOpen = true;
        isTransitioning = false;
        document.body.style.overflow = 'hidden';

        // Prepare correct tab and reset all styles
        setInitialTab(tab);
        clearValidationErrors(loginForm);
        clearValidationErrors(registerForm);

        if (!overlay || !modal) return;
        overlay.classList.add('active');

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (typeof gsap !== 'undefined' && !prefersReducedMotion) {
            gsap.killTweensOf([overlay, modal]);
            
            gsap.set(overlay, { opacity: 0 });
            gsap.set(modal, { scale: 0.92, opacity: 0, y: 15 });

            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

            tl.to(overlay, { opacity: 1, duration: 0.25 })
              .to(modal, { 
                  scale: 1, 
                  opacity: 1, 
                  y: 0, 
                  duration: 0.35, 
                  clearProps: 'transform' 
              }, '-=0.15');

            const activePane = currentTab === 'register' ? registerView : loginView;
            if (activePane) {
                resetViewStyles(activePane);
                tl.fromTo(activePane.querySelectorAll('.auth-header, .auth-field-group, .btn-auth-primary, .auth-switch-prompt, .row'), 
                    { opacity: 0, y: 10 },
                    { 
                        opacity: 1, 
                        y: 0, 
                        duration: 0.3, 
                        stagger: 0.03, 
                        ease: 'power2.out',
                        clearProps: 'all'
                    },
                    '-=0.15'
                );
            }
        } else {
            overlay.style.opacity = '1';
            modal.style.transform = 'none';
            modal.style.opacity = '1';
        }

        // Auto-focus first input field
        setTimeout(() => {
            const firstInput = currentTab === 'login' 
                ? document.getElementById('loginEmail') 
                : document.getElementById('regFirstName');
            firstInput?.focus();
        }, 250);
    };

    window.closeDiscoraAuthModal = function() {
        if (!isModalOpen || !overlay || !modal) return;
        isModalOpen = false;
        isTransitioning = false;

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (typeof gsap !== 'undefined' && !prefersReducedMotion) {
            gsap.killTweensOf([overlay, modal]);
            const tl = gsap.timeline({
                defaults: { ease: 'power2.in' },
                onComplete: () => {
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                    modal.style.transform = '';
                    resetViewStyles(loginView);
                    resetViewStyles(registerView);
                }
            });

            tl.to(modal, { scale: 0.94, opacity: 0, y: 10, duration: 0.2 })
              .to(overlay, { opacity: 0, duration: 0.18 }, '-=0.08');
        } else {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
            resetViewStyles(loginView);
            resetViewStyles(registerView);
        }
    };

    // -------------------------------------------------------------
    // 7. Login <-> Register GSAP Form Transition
    // -------------------------------------------------------------
    function switchAuthTab(targetTab) {
        if (currentTab === targetTab) return;
        isTransitioning = true;
        hideAlert();

        const outgoingView = currentTab === 'login' ? loginView : registerView;
        const incomingView = targetTab === 'login' ? loginView : registerView;
        const direction = targetTab === 'register' ? -1 : 1;

        clearValidationErrors(outgoingView);
        clearValidationErrors(incomingView);

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (typeof gsap !== 'undefined' && !prefersReducedMotion && outgoingView && incomingView) {
            gsap.killTweensOf([outgoingView, incomingView]);

            // Slide out outgoing form
            gsap.to(outgoingView, {
                x: direction * 25,
                opacity: 0,
                duration: 0.18,
                ease: 'power2.in',
                onComplete: () => {
                    outgoingView.classList.add('d-none');
                    resetViewStyles(outgoingView);

                    incomingView.classList.remove('d-none');
                    resetViewStyles(incomingView);

                    // Slide in incoming form
                    gsap.fromTo(incomingView, 
                        { x: -direction * 25, opacity: 0 },
                        {
                            x: 0,
                            opacity: 1,
                            duration: 0.26,
                            ease: 'power3.out',
                            clearProps: 'all',
                            onComplete: () => {
                                currentTab = targetTab;
                                isTransitioning = false;
                                resetViewStyles(incomingView);

                                const focusTarget = targetTab === 'register' 
                                    ? document.getElementById('regFirstName') 
                                    : document.getElementById('loginEmail');
                                focusTarget?.focus();
                            }
                        }
                    );
                }
            });
        } else {
            outgoingView?.classList.add('d-none');
            resetViewStyles(outgoingView);
            incomingView?.classList.remove('d-none');
            resetViewStyles(incomingView);
            currentTab = targetTab;
            isTransitioning = false;
        }
    }

    // -------------------------------------------------------------
    // 8. Event Listeners for Open / Close / Switch
    // -------------------------------------------------------------
    closeBtn?.addEventListener('click', window.closeDiscoraAuthModal);

    overlay?.addEventListener('click', (e) => {
        if (e.target === overlay) {
            window.closeDiscoraAuthModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isModalOpen) {
            window.closeDiscoraAuthModal();
        }
    });

    goToRegisterBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        switchAuthTab('register');
    });

    goToLoginBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        switchAuthTab('login');
    });

    // Attach click triggers to all account buttons/links on the page
    document.querySelectorAll('[data-auth-modal], a[href$="login.php"], a[href$="register.php"]').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && (href.includes('account.php') || href.includes('orders.php') || href.includes('logout.php'))) {
                return;
            }

            e.preventDefault();
            const targetTab = (this.getAttribute('data-auth-tab') === 'register' || (href && href.includes('register.php'))) 
                ? 'register' 
                : 'login';
            window.openDiscoraAuthModal(targetTab);
        });
    });

    // -------------------------------------------------------------
    // 9. Password Visibility Show / Hide Toggle
    // -------------------------------------------------------------
    document.querySelectorAll('.btn-password-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (!targetInput || !icon) return;

            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.className = 'bi bi-eye-slash';
                this.setAttribute('aria-label', 'Hide password');
            } else {
                targetInput.type = 'password';
                icon.className = 'bi bi-eye';
                this.setAttribute('aria-label', 'Show password');
            }
        });
    });

    // Realtime error clearing on input
    document.querySelectorAll('.auth-input').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const errorElem = document.getElementById(this.id + 'Error');
            if (errorElem) {
                errorElem.textContent = '';
                errorElem.classList.remove('show');
            }
        });
    });

    // -------------------------------------------------------------
    // 10. Client-Side Validation Rules
    // -------------------------------------------------------------
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }

    function validatePhone(phone) {
        const digits = phone.replace(/\D/g, '');
        return digits.length >= 7;
    }

    function validateLoginForm() {
        let isValid = true;
        clearValidationErrors(loginForm);

        const emailInput = document.getElementById('loginEmail');
        const passwordInput = document.getElementById('loginPassword');

        const emailVal = emailInput?.value.trim() || '';
        const passwordVal = passwordInput?.value || '';

        if (!emailVal) {
            setFieldError('loginEmail', 'Please enter your email address or username.');
            isValid = false;
        }

        if (!passwordVal) {
            setFieldError('loginPassword', 'Please enter your password.');
            isValid = false;
        }

        return isValid;
    }

    function validateRegisterForm() {
        let isValid = true;
        clearValidationErrors(registerForm);

        const firstName = document.getElementById('regFirstName')?.value.trim() || '';
        const lastName = document.getElementById('regLastName')?.value.trim() || '';
        const email = document.getElementById('regEmail')?.value.trim() || '';
        const phone = document.getElementById('regPhone')?.value.trim() || '';
        const password = document.getElementById('regPassword')?.value || '';
        const confirmPassword = document.getElementById('regConfirmPassword')?.value || '';
        const terms = document.getElementById('regTerms');

        if (!firstName) {
            setFieldError('regFirstName', 'First name is required.');
            isValid = false;
        }

        if (!lastName) {
            setFieldError('regLastName', 'Last name is required.');
            isValid = false;
        }

        if (!email) {
            setFieldError('regEmail', 'Email address is required.');
            isValid = false;
        } else if (!validateEmail(email)) {
            setFieldError('regEmail', 'Please enter a valid email address.');
            isValid = false;
        }

        if (!phone) {
            setFieldError('regPhone', 'Phone number is required.');
            isValid = false;
        } else if (!validatePhone(phone)) {
            setFieldError('regPhone', 'Please enter a valid contact phone number.');
            isValid = false;
        }

        if (!password) {
            setFieldError('regPassword', 'Password is required.');
            isValid = false;
        } else if (password.length < 6) {
            setFieldError('regPassword', 'Password must be at least 6 characters.');
            isValid = false;
        }

        if (!confirmPassword) {
            setFieldError('regConfirmPassword', 'Please confirm your password.');
            isValid = false;
        } else if (password !== confirmPassword) {
            setFieldError('regConfirmPassword', 'Passwords do not match.');
            isValid = false;
        }

        if (terms && !terms.checked) {
            setFieldError('regTerms', 'You must agree to the Terms of Service & Privacy Policy.');
            isValid = false;
        }

        return isValid;
    }

    // -------------------------------------------------------------
    // 11. AJAX Form Submissions to Backend (PHP / MySQL)
    // -------------------------------------------------------------
    function handleAuthSubmit(form, validateFn, btnId, isRegistration = false) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!validateFn()) {
                return;
            }

            const submitBtn = document.getElementById(btnId);
            const btnText = submitBtn?.querySelector('.btn-text');
            const btnIcon = submitBtn?.querySelector('.btn-icon');
            const spinner = submitBtn?.querySelector('.spinner-border');

            if (submitBtn) submitBtn.disabled = true;
            if (btnIcon) btnIcon.classList.add('d-none');
            if (spinner) spinner.classList.remove('d-none');
            hideAlert();

            try {
                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action');
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON from', form.action, 'Response:', text);
                    showAlert('danger', `404/Error at ${form.action} - Response: ` + text.substring(0, 80));
                    return;
                }

                if (data.success) {
                    showAlert('success', data.message || 'Authentication successful!');

                    if (isRegistration) {
                        form.reset();
                        setTimeout(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                switchAuthTab('login');
                                showAlert('success', 'Account created! Please sign in with your credentials.');
                            }
                        }, 1000);
                    } else {
                        setTimeout(() => {
                            window.location.href = data.redirect || window.location.href;
                        }, 800);
                    }
                } else {
                    showAlert('danger', data.message || 'Authentication failed. Please check your details.');
                }
            } catch (err) {
                console.error('Authentication error:', err);
                showAlert('danger', 'A server error occurred. Please try again or submit directly.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
                if (btnIcon) btnIcon.classList.remove('d-none');
                if (spinner) spinner.classList.add('d-none');
            }
        });
    }

    if (loginForm) {
        handleAuthSubmit(loginForm, validateLoginForm, 'loginSubmitBtn', false);
    }

    if (registerForm) {
        handleAuthSubmit(registerForm, validateRegisterForm, 'registerSubmitBtn', true);
    }
});
