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
   