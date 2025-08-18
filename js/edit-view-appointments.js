// Correct path to get-appointment.php
const APPOINTMENT_API = '../../../shared/main/appointment-pages/get-appointment.php';

// Modal control functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function toggleEditForm(show) {
    const editForm = document.getElementById('editForm');
    const actionButtons = document.getElementById('actionButtons');
    const appointmentDetails = document.getElementById('appointmentDetails');
    
    if (show) {
        editForm.style.display = 'block';
        actionButtons.style.display = 'none';
        appointmentDetails.style.display = 'none';
    } else {
        editForm.style.display = 'none';
        actionButtons.style.display = 'flex';
        appointmentDetails.style.display = 'block';
    }
}

async function loadAppointmentDetails() {
    try {
        // Show loading state
        const viewBtn = document.querySelector('button[onclick="loadAppointmentDetails()"]');
        const originalText = viewBtn.innerHTML;
        viewBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        viewBtn.disabled = true;

        const response = await fetch(APPOINTMENT_API, {
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`Server returned ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid response format');
        }

        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        if (!data.appointment_id) {
            throw new Error('No appointment data received');
        }

        // Display appointment details
        const detailsDiv = document.getElementById('appointmentDetails');
        detailsDiv.innerHTML = `
            <div class="appointment-card">
                <div class="appointment-field">
                    <span class="field-label">Appointment ID:</span>
                    <span class="field-value">${data.appointment_id}</span>
                </div>
                <div class="appointment-field">
                    <span class="field-label">Date:</span>
                    <span class="field-value">${new Date(data.requested_date).toLocaleDateString()}</span>
                </div>
                <div class="appointment-field">
                    <span class="field-label">Time Slot:</span>
                    <span class="field-value">${data.requested_time}</span>
                </div>
                <div class="appointment-field">
                    <span class="field-label">Status:</span>
                    <span class="status-badge status-${data.status.toLowerCase()}">
                        ${data.status}
                    </span>
                </div>
            </div>
        `;

        // Set edit form values
        document.getElementById('edit_appointment_id').value = data.appointment_id;
        document.getElementById('edit_date').value = data.requested_date;
        document.getElementById('edit_time').value = data.requested_time;

        openModal('appointmentModal');

    } catch (error) {
        console.error('Appointment Load Error:', error);
        showToast(`Error: ${error.message}`, 'error');
    } finally {
        const viewBtn = document.querySelector('button[onclick="loadAppointmentDetails()"]');
        if (viewBtn) {
            viewBtn.innerHTML = originalText;
            viewBtn.disabled = false;
        }
    }
}

// Add this if you're using the cancel functionality
async function cancelAppointment() {
    // Implement your cancel logic here
    console.log('Cancel appointment functionality');
}