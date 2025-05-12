document.addEventListener("DOMContentLoaded", function () {
    let deleteUserId = null;

    // Handle Edit Button Click
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            let userId = this.getAttribute("data-id");

            fetch(`/gcc/users/admin/backend/fetch-user.php?id=` + userId)
                .then(response => response.text())
                .then(data => {
                    console.log("Fetched Data:", data); 

                    let userData = data.split("|"); 

                    if (userData[0] === "Error") {
                        alert(userData[1]); // Show error message
                        return;
                    }

                    // Populate form fields
                    document.getElementById("edit_user_id").value = userId;
                    document.getElementById("edit_first_name").value = userData[1] || "";
                    document.getElementById("edit_middle_name").value = userData[2] || "";
                    document.getElementById("edit_last_name").value = userData[3] || "";
                    document.getElementById("edit_email").value = userData[4] || "";
                    document.getElementById("edit_wmsu_id").value = userData[5] || "";

                    openModal("editUserModal");
                })
                .catch(error => console.error("Fetch error:", error));
        });
    });

    document.getElementById("editUserForm").addEventListener("submit", function (event) {
        event.preventDefault();
        let formData = new FormData(this);

        fetch(`/gcc/users/admin/backend/edit-user.php`, {
            method: "POST",
            body: formData,
        })
        .then(response => response.text())
        .then(response => {
            alert(response);
            location.reload();
        })
        .catch(error => console.error("Edit error:", error));
    });

    // Handle Delete Button Click
// Delete User Functionality with AJAX
document.querySelectorAll(".delete-btn").forEach(button => {
    button.addEventListener("click", function() {
        const userId = this.getAttribute("data-id");
        const row = this.closest("tr"); // Get the table row
        
        // Show confirmation modal
        document.getElementById("deleteUserName").textContent = 
            this.closest("tr").querySelector("td:nth-child(3)").textContent;
        document.getElementById("delete_user_id").value = userId;
        openModal("deleteUserModal");
    });
});

// Confirm delete with AJAX
document.getElementById("confirmDeleteBtn").addEventListener("click", function() {
    const userId = document.getElementById("delete_user_id").value;
    const row = document.querySelector(`button[data-id="${userId}"]`).closest("tr");
    
    if (!userId) return;

    // Show loading state
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    this.disabled = true;

    fetch(`/gcc/users/admin/backend/delete-user.php`, {
        method: "POST",
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${userId}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(response => {
        // Remove the row from table
        row.remove();
        
        // Show success message (you can use a toast notification)
        alert(response);
        
        // Close modal
        closeModal("deleteUserModal");
    })
    .catch(error => {
        console.error("Delete error:", error);
        alert("Error deleting user: " + error.message);
    })
    .finally(() => {
        // Reset button state
        this.innerHTML = 'Yes, Delete';
        this.disabled = false;
    });
});


    document.getElementById("confirmAddAccountBtn").addEventListener("click", function () {
        window.location.href = "add-account.php";
    });

    document.querySelector(".add-btn").addEventListener("click", function () {
        openModal("confirmationModal");
    });    

    // Modal Functions
    function closeModal(modalId) {
        let modal = document.getElementById(modalId);
        modal.classList.remove("show");
        modal.classList.add("closing");

        setTimeout(() => {
            modal.style.display = "none";
            modal.classList.remove("closing");
        }, 300); // Match this with your CSS transition time
    }

    // Close modal when clicking outside
    function openModal(modalId) {
        let modal = document.getElementById(modalId);
        modal.style.display = "flex";
        setTimeout(() => {
            modal.classList.add("show");
        }, 10); // Small delay to trigger the CSS transition
    }

    // Add event listeners for close buttons in modals
    document.querySelectorAll(".close-btn").forEach(button => {
        button.addEventListener("click", function () {
            let modal = this.closest(".modal");
            closeModal(modal.id);
        });
    });

    // Handle Cancel button in Delete Modal
    document.querySelectorAll(".cancel-btn").forEach(button => {
        button.addEventListener("click", function () {
            let modal = this.closest(".modal");
            closeModal(modal.id);
        });
    });
});
