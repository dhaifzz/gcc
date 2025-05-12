<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Edit User</h2>
        <form id="editUserForm" method="POST" action="backend/edit-user.php">
            <input type="hidden" id="edit_user_id" name="id">
            <input type="text" id="edit_first_name" name="first_name" placeholder="First Name" required>
            <input type="text" id="edit_middle_name" name="middle_name" placeholder="Middle Name">
            <input type="text" id="edit_last_name" name="last_name" placeholder="Last Name" required>
            <input type="email" id="edit_email" name="email" placeholder="Email" required>
            <input type="text" id="edit_wmsu_id" name="wmsu_id" placeholder="WMSU ID">
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<!-- Delete User Modal -->
<div id="deleteUserModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Confirm Delete</h2>
        <p style="margin: 18px 0 3px 0; padding: 0; font-size: 17px;">Are you sure you want to delete user</p>
        <p style="margin: 0 0 18px 0; padding: 0;"><strong id="deleteUserName"></strong>?</p>
        <input type="hidden" id="delete_user_id">
        <button id="confirmDeleteBtn" class="delete-confirm-btn">Yes, Delete</button>
        <button class="cancel-btn">Cancel</button>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Confirmation</h2>
        <p>Do you want to add an account?</p>
        <button id="confirmAddAccountBtn">Yes</button>
        <button class="cancel-btn">No</button>
    </div>
</div>


<style>
* {
    font-family: "Instrument Sans", sans-serif;
}

.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.modal.show {
    display: flex !important;
    opacity: 1;
}

.modal.closing {
    opacity: 0;
    pointer-events: none;
}

.modal-content {
    position: relative;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transform: scale(0.9);
    opacity: 0;
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

.modal.show .modal-content {
    transform: scale(1);
    opacity: 1;
}

.modal.closing .modal-content {
    transform: scale(0.9);
    opacity: 0;
}

.close-btn {
    float: right;
    font-size: 1.5rem;
    cursor: pointer;
}

/* ============================= */
/* Edit User Modal Styles */
/* ============================= */
#editUserModal .modal-content {
    border-left: 9px solid #0F6E39;
}

/* Header */
#editUserModal h2 {
    margin-bottom: 15px;
    font-weight: 600;
    text-decoration: underline;
    text-decoration-color: #0F6E39; 
    text-decoration-thickness: 3px; 
    text-underline-offset: 5px; 
}

/* Input Fields */
#editUserModal input {
    width: 96%;
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 17px;
}

/* Save Button */
#editUserModal button {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    background: #0F6E39;
    color: white;
}

#editUserModal button:hover {
    background:rgb(8, 138, 66);
}

/* ============================= */
/* Delete User Modal Styles */
/* ============================= */
#deleteUserModal .modal-content {
    border-left: 9px solid #dc3545;
}

/* Header */
#deleteUserModal h2 {
    margin-bottom: 15px;
    font-weight: 600;
    text-decoration: underline;
    text-decoration-color: #dc3545; 
    text-decoration-thickness: 3px; 
    text-underline-offset: 5px; 
}

/* Confirmation Message */
#deleteUserModal p {
    font-size: 18px;
    color: #333;
    margin-bottom: 20px;
}

/* Buttons */
#deleteUserModal button {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

/* Confirm Delete */
#confirmDeleteBtn {
    background: #dc3545;
    color: white;
}

#confirmDeleteBtn:hover {
    background-color:rgb(144, 18, 31);
    color: white;
}

/* Cancel Button */
#deleteUserModal button:last-child {
    color: black;
    border: solid 1px #6c757d;
    background-color: white;
}

#deleteUserModal button:last-child:hover {
    background:rgb(218, 218, 218);
}

/* ============================= */
/* Confirmation Modal Styles */
/* ============================= */

#confirmationModal .modal-content {
    border-left: 9px solid rgb(17, 118, 71);
}

/* Header */
#confirmationModal h2 {
    margin-bottom: 15px;
    font-weight: 600;
}

/* Confirmation Message */
#confirmationModal p {
    font-size: 20px;
    font-weight: 500;
    color: #333;
    margin-bottom: 20px;
}

/* Buttons */
#confirmationModal button {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

/* Confirm Add Account */
#confirmAddAccountBtn {
    background: rgb(17, 118, 71);
    color: white;
}

#confirmAddAccountBtn:hover {
    background-color: rgb(16, 97, 59);
    color: white;
}

/* Cancel Button */
#confirmationModal button:last-child {
    color: black;
    border: solid 1px #6c757d;
    background-color: white;
}

#confirmationModal button:last-child:hover {
    background:rgb(218, 218, 218);
}

/* ============================= */
/* Close Button (Black) */
/* ============================= */
.close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    color: black;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 30px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

.close-btn:hover {
    color: rgba(0, 0, 0, 0.65);
    transform: scale(1.1);
}

/* ============================= */
/* Mobile Responsive */
/* ============================= */
@media (max-width: 480px) {
    .modal .modal-content {
        width: 90%;
    }
}
</style>