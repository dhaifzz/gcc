window.addEventListener("load", () => {
       const cards = document.querySelectorAll(".card");
   
       const observer = new IntersectionObserver(
         (entries, observer) => {
           entries.forEach(entry => {
             if (entry.isIntersecting) {
               entry.target.classList.add("show"); // Add 'show' class
               observer.unobserve(entry.target); // Stop observing after animation starts
             }
           });
         },
         { root: null, threshold: 0.5 } 
       );
   
       cards.forEach(card => observer.observe(card));
     });
