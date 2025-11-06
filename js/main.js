/**
 * Main JavaScript for MAKÜ Online Learning Platform
 * Author: MAKÜ IT Department
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.navmenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!navMenu.contains(event.target) && !navToggle.contains(event.target)) {
                navMenu.classList.remove('active');
            }
        });
    }
    
    // Password toggle visibility
    const togglePassword = document.querySelector('.toggle-password');
    const passwordField = document.querySelector('#password');
    
    if (togglePassword && passwordField) {
        togglePassword.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                togglePassword.textContent = '👁️‍🗨️';
            } else {
                passwordField.type = 'password';
                togglePassword.textContent = '👁️';
            }
        });
    }
    
    // Alert auto-dismiss after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        alerts.forEach(alert => {
            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
    }
    
    // Quiz options selection highlight
    const quizOptions = document.querySelectorAll('.quiz-options .option');
    if (quizOptions.length > 0) {
        quizOptions.forEach(option => {
            option.addEventListener('click', function() {
                // If not disabled and contains a radio input
                if (!option.closest('.quiz-options').classList.contains('disabled')) {
                    const radio = option.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                    }
                }
            });
        });
    }
    
    // Animate progress bars on page load
    const progressBars = document.querySelectorAll('.progress-bar');
    if (progressBars.length > 0) {
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            
            setTimeout(() => {
                bar.style.width = width;
            }, 300);
        });
    }
    
    // Tab functionality
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (tabLinks.length > 0 && tabContents.length > 0) {
        tabLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Remove active class from all tabs
                tabLinks.forEach(tab => tab.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to current tab
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                const activeTab = document.getElementById(tabId);
                if (activeTab) {
                    activeTab.classList.add('active');
                }
            });
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form');
    if (forms.length > 0) {
        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (field.value.trim() === '') {
                        isValid = false;
                        field.classList.add('error');
                        
                        // Add error message if not exists
                        const errorMessage = field.nextElementSibling;
                        if (!errorMessage || !errorMessage.classList.contains('error-message')) {
                            const message = document.createElement('div');
                            message.classList.add('error-message');
                            message.textContent = 'Bu alan zorunludur';
                            field.parentNode.insertBefore(message, field.nextSibling);
                        }
                    } else {
                        field.classList.remove('error');
                        
                        // Remove error message if exists
                        const errorMessage = field.nextElementSibling;
                        if (errorMessage && errorMessage.classList.contains('error-message')) {
                            errorMessage.remove();
                        }
                    }
                });
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
    }
    
    // Fixed header on scroll
    const header = document.querySelector('#header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
    
    // Input field animation
    const inputFields = document.querySelectorAll('.input-group input, .input-group textarea, .input-group select');
    if (inputFields.length > 0) {
        inputFields.forEach(field => {
            // Check if field already has value
            if (field.value !== '') {
                field.parentNode.classList.add('has-value');
            }
            
            field.addEventListener('focus', function() {
                this.parentNode.classList.add('focused');
            });
            
            field.addEventListener('blur', function() {
                this.parentNode.classList.remove('focused');
                if (this.value !== '') {
                    this.parentNode.classList.add('has-value');
                } else {
                    this.parentNode.classList.remove('has-value');
                }
            });
        });
    }
    
    // Search functionality
    const searchInputs = document.querySelectorAll('.search-input');
    if (searchInputs.length > 0) {
        searchInputs.forEach(input => {
            input.addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                const dataTable = this.closest('.content-card').querySelector('tbody');
                
                if (dataTable) {
                    const rows = dataTable.querySelectorAll('tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.indexOf(searchValue) > -1) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                }
            });
        });
    }
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.btn-delete');
    if (deleteButtons.length > 0) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                const confirmMessage = this.getAttribute('data-confirm') || 'Bu öğeyi silmek istediğinizden emin misiniz?';
                
                if (!confirm(confirmMessage)) {
                    event.preventDefault();
                }
            });
        });
    }
    
    // Course card hover effect
    const courseCards = document.querySelectorAll('.course-card');
    if (courseCards.length > 0) {
        courseCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        });
    }
    
    // Initialize tooltips for action buttons
    const actionButtons = document.querySelectorAll('[title]');
    if (actionButtons.length > 0) {
        actionButtons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                const title = this.getAttribute('title');
                if (title) {
                    const tooltip = document.createElement('div');
                    tooltip.classList.add('tooltip');
                    tooltip.textContent = title;
                    
                    document.body.appendChild(tooltip);
                    
                    const buttonRect = this.getBoundingClientRect();
                    tooltip.style.top = (buttonRect.top - tooltip.offsetHeight - 10) + 'px';
                    tooltip.style.left = (buttonRect.left + (buttonRect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
                    
                    this.addEventListener('mouseleave', function() {
                        tooltip.remove();
                    }, { once: true });
                }
            });
        });
    }
});
