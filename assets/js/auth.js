// auth.js - Premium Authentication Interactions

document.addEventListener('DOMContentLoaded', () => {

    // 1. Mouse Parallax Effect (Smooth cursor interaction)
    const heroBg = document.querySelector('.auth-hero');
    const particles = document.querySelector('.particles');
    const authCard = document.querySelector('.auth-card');
    const statItems = document.querySelectorAll('.stat-item');

    if (window.innerWidth > 992) {
        document.addEventListener('mousemove', (e) => {
            const xAxis = (window.innerWidth / 2 - e.pageX) / 50;
            const yAxis = (window.innerHeight / 2 - e.pageY) / 50;

            if (heroBg) heroBg.style.transform = `scale(1.1) translate(${xAxis * 0.2}px, ${yAxis * 0.2}px)`;
            if (particles) particles.style.transform = `translate(${xAxis * -1}px, ${yAxis * -1}px)`;
            if (authCard) authCard.style.transform = `translate(${xAxis * -0.5}px, ${yAxis * -0.5}px)`;
            
            statItems.forEach((stat, index) => {
                const depth = (index + 1) * 0.3;
                stat.style.transform = `translate(${xAxis * depth}px, ${yAxis * depth}px)`;
            });
        });
        
        // Reset on mouse leave
        document.addEventListener('mouseleave', () => {
            if (heroBg) heroBg.style.transform = `scale(1.1) translate(0, 0)`;
            if (particles) particles.style.transform = `translate(0, 0)`;
            if (authCard) authCard.style.transform = `translate(0, 0)`;
            statItems.forEach(stat => {
                stat.style.transform = `translate(0, 0)`;
            });
        });
    }

    // 2. Animated Statistics (Count up from 0)
    const statValues = document.querySelectorAll('.stat-value');
    if (statValues.length > 0) {
        setTimeout(() => {
            statValues.forEach(stat => {
                const finalStr = stat.textContent;
                const finalNum = parseInt(finalStr.replace(/[^0-9]/g, ''));
                if (isNaN(finalNum)) return;
                
                const suffix = finalStr.replace(/[0-9]/g, '');
                const duration = 2500; // 2.5 seconds
                const startTime = performance.now();

                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    // Ease out expo
                    const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                    const currentNum = Math.floor(easeProgress * finalNum);
                    
                    stat.textContent = currentNum.toLocaleString() + suffix;

                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    } else {
                        stat.textContent = finalStr;
                    }
                }
                requestAnimationFrame(updateCounter);
            });
        }, 1400); // Wait for stagger animations to reveal stats
    }

    // 3. Password Visibility Toggle
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });

    // 4. Form Validation Shake & Loading State
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            
            const requiredInputs = this.querySelectorAll('input[required]');
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    hasError = true;
                    input.classList.remove('error');
                    void input.offsetWidth; 
                    input.classList.add('error');
                }
            });

            if (hasError) {
                e.preventDefault();
                return;
            }

            const btn = this.querySelector('.btn-auth');
            if (btn) {
                btn.classList.add('loading');
            }
        });
        
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
            });
        });
    });

    // 5. Premium Password Strength Indicator
    const pwdInput = document.getElementById('pwd-input');
    const pwdStrength = document.getElementById('pwd-strength');
    const pwdBar = document.getElementById('pwd-bar');
    const pwdText = document.getElementById('pwd-text');

    if (pwdInput && pwdStrength) {
        pwdInput.addEventListener('input', function() {
            const val = this.value;
            if (val.length === 0) {
                pwdStrength.style.display = 'none';
                pwdText.style.opacity = '0';
                return;
            }
            
            pwdStrength.style.display = 'block';
            pwdText.style.opacity = '1';
            let strength = 0;
            
            if (val.length >= 8) strength += 1;
            if (val.match(/[a-z]+/)) strength += 1;
            if (val.match(/[A-Z]+/)) strength += 1;
            if (val.match(/[0-9]+/)) strength += 1;
            if (val.match(/[$@#&!]+/)) strength += 1;

            pwdBar.className = 'pwd-bar';

            if (strength <= 2) {
                pwdBar.classList.add('weak');
                pwdText.textContent = 'Weak: Add numbers & symbols';
                pwdText.style.color = '#ef4444';
            } else if (strength === 3 || strength === 4) {
                pwdBar.classList.add('medium');
                pwdText.textContent = 'Good: Add uppercase & symbols';
                pwdText.style.color = '#f59e0b';
            } else if (strength >= 5) {
                pwdBar.classList.add('strong');
                pwdText.textContent = 'Strong password';
                pwdText.style.color = 'var(--primary)';
            }
        });
    }

    // 6. JS-Driven Role Selection Cards
    const roleCards = document.querySelectorAll('.selection-card');
    if (roleCards.length > 0) {
        roleCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected from all
                roleCards.forEach(c => c.classList.remove('selected'));
                // Add to clicked
                this.classList.add('selected');
                
                // Toggle hidden radio
                const targetRadioId = this.getAttribute('data-role');
                const radio = document.getElementById(targetRadioId);
                if (radio) {
                    radio.checked = true;
                }
            });
        });
    }

});
