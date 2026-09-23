// Success message fade-out effect
if (document.querySelector('.success-box')) {
    setTimeout(() => {
        const box = document.getElementById("successBox");
        if (box) {
            box.style.transition = "opacity 0.5s ease";
            box.style.opacity = "0";
            setTimeout(() => box.remove(), 500); // remove after fade-out
        }
    }, 2000); // <-- Change this value to 2000 for a 2-second delay
}

// Slideshow functionality
const slideshow = document.querySelector('.slideshow');
const slides = document.querySelectorAll(".slideshow img");
let slideIndex = 0;

function showSlides() {
    slides.forEach(slide => slide.classList.remove("active"));
    slideIndex++;
    if (slideIndex > slides.length) slideIndex = 1;
    slides[slideIndex - 1].classList.add("active");
}

showSlides();
setInterval(showSlides, 3000);

// Shrink slideshow on scroll
window.addEventListener('scroll', () => {
    if (window.scrollY > 100) {
        slideshow.classList.add('shrink');
    } else {
        slideshow.classList.remove('shrink');
    }
});