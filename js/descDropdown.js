// Multiple dropdowns
function toggleDropdown(index) {
       var content = document.querySelectorAll(".dropdown-content-form")[index];
       var button = document.querySelectorAll(".dropdown-btn-form")[index];

       if (content.style.display === "block") {
           content.style.display = "none";
           button.classList.remove("active");
       } else {
           content.style.display = "block";
           button.classList.add("active");
       }
   }

// One bai One dropdown
// function toggleDropdown(index) {
//        var contents = document.querySelectorAll(".dropdown-content");
//        var buttons = document.querySelectorAll(".dropdown-btn");

//        if (contents[index].style.display === "block") {
//            contents[index].style.display = "none";
//            buttons[index].classList.remove("active");
//        } else {
//            contents.forEach(content => content.style.display = "none");
//            buttons.forEach(button => button.classList.remove("active"));
           
//            contents[index].style.display = "block";
//            buttons[index].classList.add("active");
//        }
//    }

