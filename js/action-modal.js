document.addEventListener("DOMContentLoaded", function () {
    let deleteUserId = null;

    // Handle Edit Button Click
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            let userId = this.getAttribute("data-id");

            fetch(`/gcc/users/admin/backend/fetch-user.php?id=` + userId)
                .then(response => response.text())
                .then(data => {
                    console.log("Fetched Data:", data); // Debugging output

                    let userData = data.split("|"); // Split text response into an array

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
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function () {
            let userId = this.getAttribute("data-id");

            // Fetch user's details
            fetch(`/gcc/users/admin/backend/fetch-user.php?id=` + userId)
                .then(response => response.text())
                .then(data => {
                    console.log("Fetched Data:", data); // Debugging output

                    let userData = data.split("|"); // Split response into array

                    if (userData[0] === "Error") {
                        alert("User not found!");
                        return;
                    }

                    // Extract full name (First Middle Last)
                    let fullName = `${userData[1]} ${userData[2]} ${userData[3]}`.trim();

                    // Update modal text with the full name
                    document.getElementById("deleteUserName").textContent = fullName;

                    // Store the user ID for deletion
                    deleteUserId = userId;
                    document.getElementById("delete_user_id").value = deleteUserId;

                    // Show the modal
                    openModal("deleteUserModal");
                })
                .catch(error => console.error("Fetch error:", error));
        });
    });

    document.getElementById("confirmDeleteBtn").addEventListener("click", function () {
        if (!deleteUserId) return;

        fetch(`/gcc/users/admin/backend/delete-user.php`, {
            method: "POST",
            body: new URLSearchParams({ id: deleteUserId }),
        })
        .then(response => response.text())
        .then(response => {
            alert(response);
            document.querySelector(`button[data-id='${deleteUserId}']`).closest("tr").remove();
            closeModal("deleteUserModal");
        })
        .catch(error => console.error("Delete error:", error));
    });

    // Modal Functions
    function openModal(modalId) {
        let modal = document.getElementById(modalId);
        modal.style.display = "block";
        setTimeout(() => {
            modal.classList.add("show");
        }, 10);
    }

    function closeModal(modalId) {
        let modal = document.getElementById(modalId);
        modal.classList.remove("show");
        setTimeout(() => {
            modal.style.display = "none";
        }, 300); // Ensure fade-out transition happens before hiding
    }

    // Close modal when clicking outside
    document.querySelectorAll(".modal").forEach(modal => {
        modal.addEventListener("click", function (event) {
            if (event.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    // Add event listeners for close buttons in modals
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            let modal = this.closest('.modal');
            closeModal(modal.id);
        });
    });

    // Handle Cancel button in Delete Modal
    document.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            let modal = this.closest('.modal');
            closeModal(modal.id);
        });
    });
});
