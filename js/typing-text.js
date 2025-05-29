document.addEventListener("DOMContentLoaded", function () {
       console.log("DOM fully loaded"); // Check if JS runs
       const textElement = document.getElementById("welcome-text");
   
       if (textElement) {
           console.log("Element found:", textElement.textContent);
           const fullWidth = textElement.scrollWidth + "px";
           console.log("Full width:", fullWidth);
   
           textElement.style.transition = "max-width 3s ease-out";
           setTimeout(() => {
               textElement.style.maxWidth = fullWidth;
               console.log("Animation triggered!");
           }, 100);
       } else {
           console.error("Element #welcome-text NOT found.");
       }
   });

document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('reason_to_shift');
    const placeholderText = "Type your reason for shifting here...";
    let i = 0;
    let isDeleting = false;
    const typingSpeed = 70; // Faster speed (original was 100)
    const deletingSpeed = 40; // Even faster backspacing
    const pauseDuration = 600; // Slightly shorter pause (original was 800)

    function typeWriter() {
        const currentText = placeholderText.substring(0, i);
        textarea.setAttribute('placeholder', currentText);

        if (!isDeleting && i === placeholderText.length) {
            // Pause at full text (shorter pause)
            isDeleting = true;
            setTimeout(typeWriter, pauseDuration);
            return;
        } else if (isDeleting && i === 0) {
            // Restart typing faster
            isDeleting = false;
            setTimeout(typeWriter, typingSpeed);
            return;
        }

        // Use faster speed for deleting
        const speed = isDeleting ? deletingSpeed : typingSpeed;
        
        isDeleting ? i-- : i++;
        setTimeout(typeWriter, speed);
    }

    // Start animation when textarea is visible
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            typeWriter();
        }
    }, { threshold: 0.1 });
    
    observer.observe(textarea);
});
   