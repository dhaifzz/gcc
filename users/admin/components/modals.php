<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Edit User</h2>
        <form id="editUserForm">
            <input type="hidden" id="edit_user_id">
            <input type="text" id="edit_first_name" placeholder="First Name" required>
            <input type="text" id="edit_middle_name" placeholder="Middle Name">
            <input type="text" id="edit_last_name" placeholder="Last Name" required>
            <input type="email" id="edit_email" placeholder="Email" required>
            <input type="text" id="edit_wmsu_id" placeholder="WMSU ID">
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<!-- Delete User Modal -->
<div id="deleteUserModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Confirm Delete</h2>
        <p>Are you sure you want to delete user, <strong id="deleteUserName"></strong>?</p>
        <input type="hidden" id="delete_user_id">
        <button id="confirmDeleteBtn">Yes, Delete</button>
        <button class="cancel-btn">Cancel</button>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
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

/* Show Modal Effect (Don't touch display here) */
.modal.show {
    opacity: 1;
    pointer-events: all; /* Makes sure it's clickable */
}

/* When Modal is Shown */
.modal.show .modal-content {
    transform: scale(1);
    opacity: 1;
}
   /* Modal Content (Perfectly Centered) */
   .modal .modal-content {
       background: white;
       padding: 20px;
       width: 400px;
       max-width: 90%;
       border-radius: 10px;
       box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
       position: relative;
       text-align: center;
       transform: scale(0.9);
       transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
   }
   /* ============================= */
   /* Edit User Modal Styles */
   /* ============================= */
   #editUserModal .modal-content {
       border-top: 5px solid #218838;
   }
   
   /* Header */
   #editUserModal h2 {
       margin-bottom: 15px;
       color: #218838;
   }
   
   /* Input Fields */
   #editUserModal input {
       width: 100%;
       padding: 10px;
       margin: 5px 0;
       border: 1px solid #ccc;
       border-radius: 5px;
       font-size: 14px;
   }
   
   /* Save Button */
   #editUserModal button {
       width: 100%;
       padding: 10px;
       margin-top: 10px;
       border: none;
       border-radius: 5px;
       font-size: 16px;
       font-weight: bold;
       cursor: pointer;
       transition: all 0.3s ease-in-out;
       background: #218838;
       color: white;
   }
   
   #editUserModal button:hover {
       background: #19692c;
   }
   
   /* ============================= */
   /* Delete User Modal Styles */
   /* ============================= */
   #deleteUserModal .modal-content {
       border-top: 5px solid #dc3545;
   }
   
   /* Header */
   #deleteUserModal h2 {
       margin-bottom: 15px;
       color: #dc3545;
   }
   
   /* Confirmation Message */
   #deleteUserModal p {
       font-size: 16px;
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
       background: #b52b3a;
   }
   
   /* Cancel Button */
   #deleteUserModal button:last-child {
       background: #6c757d;
       color: white;
   }
   
   #deleteUserModal button:last-child:hover {
       background: #5a6268;
   }
   
   /* ============================= */
   /* Close Button (Black) */
   /* ============================= */
   .close-btn {
       position: absolute;
       top: 10px;
       right: 15px;
       color: black;
       border: 2px solid black;
       background: white;
       width: 32px;
       height: 32px;
       display: flex;
       align-items: center;
       justify-content: center;
       border-radius: 50%;
       font-size: 20px;
       font-weight: bold;
       cursor: pointer;
       transition: all 0.3s ease-in-out;
   }
   
   .close-btn:hover {
       background: black;
       color: white;
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