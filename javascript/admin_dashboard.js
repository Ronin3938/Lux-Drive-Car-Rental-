document.addEventListener('DOMContentLoaded', () => {
    const sections = document.querySelectorAll('.section');
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    const popupMessage = document.getElementById('popup-message');
    
    // Function to show a pop-up message for a duration
    const showPopupMessage = (message) => {
        if (message) {
            popupMessage.textContent = message;
            popupMessage.classList.add('show');
            setTimeout(() => {
                popupMessage.classList.remove('show');
                popupMessage.textContent = '';
            }, 2000); // Hide after 2 seconds
        }
    };

    // Check if there is an error message to display from the PHP session
    const errorMessage = popupMessage.getAttribute('data-message');
    if (errorMessage) {
        showPopupMessage(errorMessage);
    }

    // Function to show a specific dashboard section and highlight the active link
    const showSection = (sectionId) => {
        // Hide all sections
        sections.forEach(section => {
            section.classList.remove('active');
        });
        
        // Show the selected section
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.add('active');
        }

        // Deactivate all navigation links
        navLinks.forEach(link => {
            link.classList.remove('active');
        });
        
        // Activate the corresponding nav link
        const targetLink = document.querySelector(`a[data-section-id="${sectionId}"]`);
        if (targetLink) {
            targetLink.classList.add('active');
        }
    };

    // Attach click event listeners to nav links
    navLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const sectionId = link.getAttribute('data-section-id');
            showSection(sectionId);

            if (window.innerWidth <= 768) {
                document.getElementById("sidebar").classList.remove('show');
            }
        });
    });

    // Toggle menu functionality for mobile
const menuToggleBtn = document.querySelector('.menu-toggle');
const sidebar = document.getElementById('sidebar');

menuToggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('show');
});

    // Set the initial active section on page load
    const initialSectionId = navLinks[0] ? navLinks[0].getAttribute('data-section-id') : null;
    if (initialSectionId) {
        showSection(initialSectionId);
    }
});