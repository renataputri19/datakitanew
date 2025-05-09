// Animation for score circles
function animateScoreCircles() {
    const circles = document.querySelectorAll('.score-circle');
    circles.forEach(circle => {
        const progress = parseFloat(circle.getAttribute('data-progress'));
        const circumference = 2 * Math.PI * 16; // r = 16
        const offset = circumference - (progress / 100) * circumference;
        
        // Animate the circle
        circle.style.strokeDasharray = `${circumference} ${circumference}`;
        circle.style.strokeDashoffset = circumference;
        
        setTimeout(() => {
            circle.style.strokeDashoffset = offset;
        }, 100);
    });
}

// Intersection Observer for scroll animations
const observeElements = () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-slide-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.dashboard-card').forEach(card => {
        observer.observe(card);
    });
};

// Progress bar animations
function animateProgressBars() {
    const bars = document.querySelectorAll('.progress-bar-fill');
    bars.forEach(bar => {
        const percentage = bar.getAttribute('data-percentage');
        setTimeout(() => {
            bar.style.width = `${percentage}%`;
        }, 200);
    });
}

// Theme switcher handler
function handleThemeSwitch() {
    const isDark = document.documentElement.classList.contains('dark');
    
    // Update gradient colors based on theme
    const cards = document.querySelectorAll('.score-card');
    cards.forEach(card => {
        const gradientClass = isDark ? 'dark-gradient' : 'light-gradient';
        card.classList.remove('dark-gradient', 'light-gradient');
        card.classList.add(gradientClass);
    });
}

// Initialize everything when the page loads
document.addEventListener('DOMContentLoaded', () => {
    animateScoreCircles();
    observeElements();
    animateProgressBars();
    
    // Listen for theme changes
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                handleThemeSwitch();
            }
        });
    });
    
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
});

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});