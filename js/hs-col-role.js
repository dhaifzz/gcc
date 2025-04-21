document.addEventListener("DOMContentLoaded", function () {
       const courseGradeSelect = document.getElementById("course-grade");
       const roleInput = document.createElement("input");
       roleInput.type = "hidden";
       roleInput.name = "role";
       document.querySelector("form").appendChild(roleInput);
   
       function updateRole() {
           const selectedValue = courseGradeSelect.value;
   
           if (selectedValue === "Junior High" || selectedValue === "Senior High") {
               roleInput.value = "High School Student";
           } else if (selectedValue === "None") {
               roleInput.value = "Outside Client";
           } else {
               roleInput.value = "College Student";
           }
       }
   
       // Set the role on page load
       updateRole();
   
       // Update the role whenever the course-grade dropdown changes
       courseGradeSelect.addEventListener("change", updateRole);
   });