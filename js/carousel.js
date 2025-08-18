let currentIndex = 0;
const items = document.querySelectorAll('.carousel-item');
const itemCount = items.length;

function slideItems() {
    const carouselInner = document.querySelector('.carousel-inner');
    carouselInner.style.transition = 'transform 1s ease-in-out'; 
    carouselInner.style.transform = `translateX(-${currentIndex * 100}%)`; // Move to the next image
}

function showNextItem() {
    currentIndex = (currentIndex + 1) % itemCount;
    slideItems();
}

function showPreviousItem() {
    currentIndex = (currentIndex - 1 + itemCount) % itemCount; 
    slideItems();
}

setInterval(showNextItem, 3000); 