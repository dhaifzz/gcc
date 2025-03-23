function showFileName(inputId) {
       var input = document.getElementById(inputId);
       var fileName = input.files[0].name;
       var fileNameElement = document.getElementById(inputId + '-file-name');
       fileNameElement.textContent = fileName;
   }