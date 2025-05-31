// Main JavaScript File
document.addEventListener('DOMContentLoaded', function() {
// Search Panel Toggle
const searchToggle = document.getElementById('searchToggle');
const searchPanel = document.getElementById('searchPanel');
const searchClose = document.getElementById('searchClose');
const searchInput = document.getElementById('searchInput');

if (searchToggle && searchPanel && searchClose && searchInput) {
    searchToggle.addEventListener('click', (e) => {
        e.preventDefault();
        searchPanel.classList.add('active');
        document.body.classList.add('search-active');
        searchInput.focus(); // Auto focus the search input
    });

    searchClose.addEventListener('click', () => {
        searchPanel.classList.remove('active');
        document.body.classList.remove('search-active');
        searchInput.value = ''; // Clear search input when closing
    });

    // Close search panel on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && searchPanel.classList.contains('active')) {
            searchPanel.classList.remove('active');
            document.body.classList.remove('search-active');
            searchInput.value = '';
        }
    });

    // Close search panel when clicking outside
    searchPanel.addEventListener('click', (e) => {
        if (e.target === searchPanel) {
            searchPanel.classList.remove('active');
            document.body.classList.remove('search-active');
            searchInput.value = '';
        }
    });
}
    
    // Product quantity buttons
    const quantityBtns = document.querySelectorAll('.quantity-btn');
    
    if (quantityBtns.length > 0) {
        quantityBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                let value = parseInt(input.value);
                
                if (this.classList.contains('minus') && value > 1) {
                    value--;
                } else if (this.classList.contains('plus')) {
                    value++;
                }
                
                input.value = value;
                
                // If in cart page, trigger update
                if (document.querySelector('.cart-table')) {
                    const updateBtn = this.closest('tr').querySelector('.update-cart');
                    if (updateBtn) {
                        updateBtn.removeAttribute('disabled');
                    }
                }
            });
        });
    }
    
    // Product detail image gallery
    const productThumbnails = document.querySelectorAll('.product-detail-thumbnail');
    const productMainImage = document.querySelector('.product-detail-main-image img');
    
    if (productThumbnails.length > 0 && productMainImage) {
        productThumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                // Remove active class from all thumbnails
                productThumbnails.forEach(item => {
                    item.classList.remove('active');
                });
                
                // Add active class to clicked thumbnail
                this.classList.add('active');
                
                // Update main image
                const imgSrc = this.querySelector('img').getAttribute('src');
                productMainImage.setAttribute('src', imgSrc);
            });
        });
    }
    
    // Payment method toggle
    const paymentMethods = document.querySelectorAll('.payment-method-header input');
    
    if (paymentMethods.length > 0) {
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                // Hide all content
                document.querySelectorAll('.payment-method-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show selected content
                if (this.checked) {
                    const content = document.querySelector(`#${this.id}-content`);
                    if (content) {
                        content.classList.add('active');
                    }
                }
            });
        });
    }
    
    // Price range slider
    const rangeInput = document.querySelectorAll('.price-filter .range-input input');
    const priceInput = document.querySelectorAll('.price-filter .price-inputs input');
    const progress = document.querySelector('.price-filter .slider .progress');
    
    if (rangeInput.length > 0 && priceInput.length > 0 && progress) {
        let priceGap = 10;
        
        priceInput.forEach(input => {
            input.addEventListener('input', e => {
                // Get min and max values
                let minVal = parseInt(priceInput[0].value);
                let maxVal = parseInt(priceInput[1].value);
                
                if ((maxVal - minVal >= priceGap) && maxVal <= 1000) {
                    if (e.target.className === 'min-input') {
                        rangeInput[0].value = minVal;
                        let percent = (minVal / rangeInput[0].max) * 100;
                        progress.style.left = percent + '%';
                    } else {
                        rangeInput[1].value = maxVal;
                        let percent = 100 - (maxVal / rangeInput[1].max) * 100;
                        progress.style.right = percent + '%';
                    }
                }
            });
        });
        
        rangeInput.forEach(input => {
            input.addEventListener('input', e => {
                // Get min and max values
                let minVal = parseInt(rangeInput[0].value);
                let maxVal = parseInt(rangeInput[1].value);
                
                if (maxVal - minVal < priceGap) {
                    if (e.target.className === 'min-range') {
                        rangeInput[0].value = maxVal - priceGap;
                    } else {
                        rangeInput[1].value = minVal + priceGap;
                    }
                } else {
                    priceInput[0].value = minVal;
                    priceInput[1].value = maxVal;
                    let leftPercent = (minVal / rangeInput[0].max) * 100;
                    let rightPercent = 100 - (maxVal / rangeInput[1].max) * 100;
                    progress.style.left = leftPercent + '%';
                    progress.style.right = rightPercent + '%';
                }
            });
        });
    }
    
    // Admin dashboard sidebar toggle
    const adminToggle = document.querySelector('.admin-toggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    const adminMain = document.querySelector('.admin-main');
    
    if (adminToggle && adminSidebar && adminMain) {
        adminToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('collapsed');
            adminMain.classList.toggle('expanded');
        });
    }
    
    // Admin submenu toggle
    const adminMenuItems = document.querySelectorAll('.admin-menu-item');
    
    if (adminMenuItems.length > 0) {
        adminMenuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.nextElementSibling && this.nextElementSibling.classList.contains('admin-submenu')) {
                    e.preventDefault();
                    this.classList.toggle('active');
                    this.nextElementSibling.classList.toggle('show');
                }
            });
        });
    }
    
    // Auto-close alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    
    if (alerts.length > 0) {
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('fade');
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    }
    
    // Star rating in review form
    const starRating = document.querySelector('.star-rating');
    
    if (starRating) {
        const stars = starRating.querySelectorAll('input');
        const labels = starRating.querySelectorAll('label');
        
        stars.forEach((star, index) => {
            star.addEventListener('change', function() {
                for (let i = 0; i < labels.length; i++) {
                    if (i <= index) {
                        labels[i].style.color = '#FFD700';
                    } else {
                        labels[i].style.color = '#ddd';
                    }
                }
            });
        });
    }
    
    // Image preview on file input change
    const imageInput = document.querySelector('.image-input');
    const imagePreview = document.querySelector('.image-preview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (imagePreview.querySelector('img')) {
                        imagePreview.querySelector('img').src = e.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        imagePreview.innerHTML = '';
                        imagePreview.appendChild(img);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
