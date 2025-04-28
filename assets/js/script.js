
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const body = document.body;
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('expanded');
            body.classList.toggle('collapsed-sidebar');
        });
    }
    
    function handleResponsive() {
        if (window.innerWidth <= 992) {
            sidebar.classList.add('collapsed');
            body.classList.add('collapsed-sidebar');
        } else {
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('expanded');
            body.classList.remove('collapsed-sidebar');
        }
    }
    
    handleResponsive();
    
    window.addEventListener('resize', handleResponsive);
    
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modalCloseButtons = document.querySelectorAll('[data-modal-close]');
    const modals = document.querySelectorAll('.modal-backdrop');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('show');
            }
        });
    });
    
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal-backdrop');
            if (modal) {
                modal.classList.remove('show');
            }
        });
    });
    
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    });
    
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    const errorMessage = field.getAttribute('data-error-message') || 'Este campo é obrigatório';
                    
                    let errorElement = field.nextElementSibling;
                    if (!errorElement || !errorElement.classList.contains('error-message')) {
                        errorElement = document.createElement('div');
                        errorElement.classList.add('error-message');
                        field.parentNode.insertBefore(errorElement, field.nextSibling);
                    }
                    
                    errorElement.textContent = errorMessage;
                } else {
                    field.classList.remove('error');
                    const errorElement = field.nextElementSibling;
                    if (errorElement && errorElement.classList.contains('error-message')) {
                        errorElement.remove();
                    }
                }
            });
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
    
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            const tooltipEl = document.createElement('div');
            tooltipEl.classList.add('tooltip');
            tooltipEl.textContent = text;
            
            document.body.appendChild(tooltipEl);
            
            const rect = this.getBoundingClientRect();
            tooltipEl.style.top = `${rect.top - tooltipEl.offsetHeight - 5}px`;
            tooltipEl.style.left = `${rect.left + (rect.width / 2) - (tooltipEl.offsetWidth / 2)}px`;
            
            setTimeout(() => {
                tooltipEl.classList.add('show');
            }, 10);
        });
        
        tooltip.addEventListener('mouseleave', function() {
            const tooltipEl = document.querySelector('.tooltip');
            if (tooltipEl) {
                tooltipEl.classList.remove('show');
                setTimeout(() => {
                    tooltipEl.remove();
                }, 300);
            }
        });
    });
    
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const message = this.getAttribute('data-confirm') || 'Tem certeza que deseja continuar?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });
    
    const alerts = document.querySelectorAll('.alert:not(.alert-persistent)');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const confirmMessage = this.getAttribute('data-confirm-message') || 'Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.';
            if (!confirm(confirmMessage)) {
                event.preventDefault();
            }
        });
    });
    
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        const fileInputContainer = document.createElement('div');
        fileInputContainer.classList.add('file-input-container');
        
        const fileNameDisplay = document.createElement('div');
        fileNameDisplay.classList.add('file-name');
        fileNameDisplay.textContent = 'Nenhum arquivo selecionado';
        
        const browseButton = document.createElement('button');
        browseButton.classList.add('btn', 'btn-primary');
        browseButton.textContent = 'Procurar';
        browseButton.type = 'button';
        
        input.parentNode.insertBefore(fileInputContainer, input);
        fileInputContainer.appendChild(fileNameDisplay);
        fileInputContainer.appendChild(browseButton);
        fileInputContainer.appendChild(input);
        
        input.style.display = 'none';
        
        browseButton.addEventListener('click', function() {
            input.click();
        });
        
        input.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = 'Nenhum arquivo selecionado';
            }
        });
    });
    
    function animateElements() {
        const elements = document.querySelectorAll('.animate-on-load');
        elements.forEach((element, index) => {
            setTimeout(() => {
                element.classList.add('fade-in');
            }, index * 100);
        });
    }
    
    animateElements();
});