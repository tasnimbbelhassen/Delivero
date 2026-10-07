
document.addEventListener('DOMContentLoaded', function() {
    
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('card-details');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'card') {
                cardDetails.style.display = 'block';
            } else {
                cardDetails.style.display = 'none';
            }
        });
    });
    

    const cardNumberInput = document.querySelector('input[placeholder*="Numéro de carte"]');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function() {
            let value = this.value.replace(/\s+/g, '').replace(/[^0-9]/g, '');
            value = value.match(/.{1,4}/g)?.join(' ') || '';
            this.value = value.substring(0, 19);
        });
    }
    
    const expiryInput = document.querySelector('input[placeholder*="Date d\'expiration"]');
    if (expiryInput) {
        expiryInput.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value.substring(0, 5);
        });
    }
    
    const cvvInput = document.querySelector('input[placeholder*="CVV"]');
    if (cvvInput) {
        cvvInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 3);
        });
    }
    
    
    const addressTextarea = document.querySelector('textarea[name="delivery_address"]');
    if (addressTextarea) {
        addressTextarea.addEventListener('blur', function() {
            if (this.value.trim().length >= 10) {
                validateAddress(this.value);
            }
        });
    }
    
    
    const checkoutForm = document.querySelector('#checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Traitement...';
            
       
            const isValid = validateCheckoutForm(this);
            
            if (isValid) {
                setTimeout(() => {
                    this.submit();
                }, 2000);
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
    
    function validateCheckoutForm(form) {
        let isValid = true;
        
      
        const address = form.querySelector('textarea[name="delivery_address"]');
        if (!address.value.trim()) {
            showFieldError(address, 'L\'adresse est requise');
            isValid = false;
        }
        
        
        const phone = form.querySelector('input[name="phone"]');
        if (!phone.value.trim()) {
            showFieldError(phone, 'Le téléphone est requis');
            isValid = false;
        }
        
      
        const selectedPayment = form.querySelector('input[name="payment_method"]:checked');
        if (selectedPayment.value === 'card') {
            const cardNumber = form.querySelector('input[placeholder*="Numéro de carte"]');
            const expiry = form.querySelector('input[placeholder*="Date d\'expiration"]');
            const cvv = form.querySelector('input[placeholder*="CVV"]');
            
            if (!validateCardNumber(cardNumber.value)) {
                showFieldError(cardNumber, 'Numéro de carte invalide');
                isValid = false;
            }
            
            if (!validateExpiryDate(expiry.value)) {
                showFieldError(expiry, 'Date d\'expiration invalide');
                isValid = false;
            }
            
            if (!cvv.value || cvv.value.length !== 3) {
                showFieldError(cvv, 'CVV invalide');
                isValid = false;
            }
        }
        
        return isValid;
    }
    

    function validateCardNumber(number) {
        number = number.replace(/\s+/g, '');
        if (!/^\d{13,19}$/.test(number)) return false;
        
        
        let sum = 0;
        let isEven = false;
        
        for (let i = number.length - 1; i >= 0; i--) {
            let digit = parseInt(number.charAt(i));
            
            if (isEven) {
                digit *= 2;
                if (digit > 9) digit -= 9;
            }
            
            sum += digit;
            isEven = !isEven;
        }
        
        return sum % 10 === 0;
    }
    
  
    function validateExpiryDate(expiry) {
        if (!/^\d{2}\/\d{2}$/.test(expiry)) return false;
        
        const [month, year] = expiry.split('/').map(Number);
        const now = new Date();
        const currentYear = now.getFullYear() % 100;
        const currentMonth = now.getMonth() + 1;
        
        if (month < 1 || month > 12) return false;
        if (year < currentYear) return false;
        if (year === currentYear && month < currentMonth) return false;
        
        return true;
    }

    function validateAddress(address) {
        fetch('ajax/validate_address.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ address: address })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.valid) {
                showNotification('warning', 'Vérifiez votre adresse de livraison');
            }
        });
    }
    
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let feedback = field.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = message;
        field.focus();
    }
    

    startOrderTimer();
});


function startOrderTimer() {
    const timerElement = document.getElementById('order-timer');
    if (!timerElement) return;
    
    let seconds = 0;
    
    setInterval(() => {
        seconds++;
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        
        timerElement.textContent = 
            `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }, 1000);
}


const checkoutSteps = {
    currentStep: 1,
    
    init() {
        this.updateStepIndicators();
        this.setupStepNavigation();
    },
    
    updateStepIndicators() {
        document.querySelectorAll('.step').forEach((step, index) => {
            const stepNumber = index + 1;
            
            if (stepNumber < this.currentStep) {
                step.classList.add('completed');
                step.classList.remove('active');
            } else if (stepNumber === this.currentStep) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });
    },
    
    setupStepNavigation() {
        document.querySelectorAll('.next-step').forEach(button => {
            button.addEventListener('click', () => this.nextStep());
        });
        
        document.querySelectorAll('.prev-step').forEach(button => {
            button.addEventListener('click', () => this.prevStep());
        });
    },
    
    nextStep() {
        if (this.validateCurrentStep()) {
            this.currentStep++;
            this.updateStepIndicators();
            this.scrollToCurrentStep();
        }
    },
    
    prevStep() {
        this.currentStep--;
        this.updateStepIndicators();
        this.scrollToCurrentStep();
    },
    
    validateCurrentStep() {
        switch(this.currentStep) {
            case 1:
                return this.validateAddressStep();
            case 2: 
                return this.validatePaymentStep();
            default:
                return true;
        }
    },
    
    validateAddressStep() {
        const address = document.querySelector('textarea[name="delivery_address"]');
        const phone = document.querySelector('input[name="phone"]');
        
        let isValid = true;
        
        if (!address.value.trim()) {
            this.showError(address, 'L\'adresse est requise');
            isValid = false;
        }
        
        if (!phone.value.trim()) {
            this.showError(phone, 'Le téléphone est requis');
            isValid = false;
        }
        
        return isValid;
    },
    
    validatePaymentStep() {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        
        if (!paymentMethod) {
            showNotification('error', 'Veuillez sélectionner un mode de paiement');
            return false;
        }
        
        if (paymentMethod.value === 'card') {
            return this.validateCardDetails();
        }
        
        return true;
    },
    
    validateCardDetails() {
        const cardNumber = document.querySelector('input[placeholder*="Numéro de carte"]');
        const expiry = document.querySelector('input[placeholder*="Date d\'expiration"]');
        const cvv = document.querySelector('input[placeholder*="CVV"]');
        
        let isValid = true;
        
        if (!cardNumber.value.trim() || cardNumber.value.replace(/\s+/g, '').length < 16) {
            this.showError(cardNumber, 'Numéro de carte invalide');
            isValid = false;
        }
        
        if (!expiry.value.trim() || !/^\d{2}\/\d{2}$/.test(expiry.value)) {
            this.showError(expiry, 'Date d\'expiration invalide');
            isValid = false;
        }
        
        if (!cvv.value.trim() || cvv.value.length !== 3) {
            this.showError(cvv, 'CVV invalide');
            isValid = false;
        }
        
        return isValid;
    },
    
    showError(element, message) {
        element.classList.add('is-invalid');
        
        let feedback = element.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            element.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = message;
        element.focus();
        
        
        element.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            feedback.textContent = '';
        }, { once: true });
    },
    
    scrollToCurrentStep() {
        const stepElement = document.querySelector(`.step:nth-child(${this.currentStep})`);
        if (stepElement) {
            stepElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
};


if (document.querySelector('.steps')) {
    checkoutSteps.init();
}