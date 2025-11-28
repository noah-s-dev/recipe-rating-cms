/**
 * Rating System JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const ratingForm = document.getElementById('ratingForm');
    const ratingMessage = document.getElementById('ratingMessage');
    const starRating = document.querySelector('.star-rating');
    
    // Handle star rating interactions
    if (starRating) {
        const stars = starRating.querySelectorAll('input[type="radio"]');
        const labels = starRating.querySelectorAll('.star-label');
        
        // Add hover effects
        labels.forEach((label, index) => {
            label.addEventListener('mouseenter', function() {
                highlightStars(index + 1);
            });
            
            label.addEventListener('click', function() {
                const rating = index + 1;
                selectRating(rating);
            });
        });
        
        // Reset on mouse leave
        starRating.addEventListener('mouseleave', function() {
            const checkedStar = starRating.querySelector('input[type="radio"]:checked');
            if (checkedStar) {
                highlightStars(parseInt(checkedStar.value));
            } else {
                highlightStars(0);
            }
        });
        
        // Initialize with current rating
        const checkedStar = starRating.querySelector('input[type="radio"]:checked');
        if (checkedStar) {
            highlightStars(parseInt(checkedStar.value));
        }
    }
    
    // Handle form submission
    if (ratingForm) {
        ratingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(ratingForm);
            const submitButton = ratingForm.querySelector('button[type="submit"]');
            
            // Validate rating selection
            const selectedRating = formData.get('rating');
            if (!selectedRating) {
                showMessage('Please select a rating', 'error');
                return;
            }
            
            // Disable submit button
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
            
            // Submit rating via AJAX
            fetch('submit_rating.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    // Update button text
                    submitButton.textContent = 'Update Rating';
                    // Optionally reload page to show updated ratings
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while submitting your rating', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                submitButton.disabled = false;
                if (submitButton.textContent === 'Submitting...') {
                    submitButton.textContent = 'Submit Rating';
                }
            });
        });
    }
    
    /**
     * Highlight stars up to the given rating
     */
    function highlightStars(rating) {
        if (!starRating) return;
        
        const labels = starRating.querySelectorAll('.star-label');
        labels.forEach((label, index) => {
            if (index < rating) {
                label.classList.add('highlighted');
            } else {
                label.classList.remove('highlighted');
            }
        });
    }
    
    /**
     * Select a rating
     */
    function selectRating(rating) {
        if (!starRating) return;
        
        const radioButton = starRating.querySelector(`input[value="${rating}"]`);
        if (radioButton) {
            radioButton.checked = true;
        }
        highlightStars(rating);
    }
    
    /**
     * Show message to user
     */
    function showMessage(message, type) {
        if (!ratingMessage) return;
        
        ratingMessage.textContent = message;
        ratingMessage.className = `message ${type}`;
        ratingMessage.style.display = 'block';
        
        // Hide message after 5 seconds
        setTimeout(() => {
            ratingMessage.style.display = 'none';
        }, 5000);
    }
});

