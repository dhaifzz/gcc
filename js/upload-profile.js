document.getElementById('fileInput').addEventListener('change', function () {
       const form = document.getElementById('uploadForm');
       const formData = new FormData(form);
   
       fetch(form.action, {
           method: 'POST',
           body: formData
       })
       .then(response => response.text())
       .then(data => {
           const [message, newProfileImage] = data.split('|');
           const messageDiv = document.getElementById('message');
   
           messageDiv.innerText = message;
           messageDiv.style.color = message.includes("successfully") ? "green" : "red";
   
           if (newProfileImage && newProfileImage.trim() !== "") {
               document.getElementById('profileImage').src = `/gcc/img/profiles/${newProfileImage.trim()}?t=${new Date().getTime()}`;
           }
   
           setTimeout(() => { messageDiv.innerText = ''; }, 2000);
       })
       .catch(error => console.error('Error:', error));
   });