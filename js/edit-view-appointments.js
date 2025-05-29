// API Endpoints
const APPOINTMENT_API = '../../../shared/main/appointment-pages/get-appointment.php';
const CANCEL_API = '../../../shared/main/appointment-pages/cancel-appointment.php';
const UPDATE_API = '../../../shared/main/appointment-pages/update-appointment.php';
const CHECK_API = '../../../shared/main/appointment-pages/check-availability.php';
const SLIP_API = '../../../shared/main/appointment-pages/generate-slip.php';
const WORKING_HOURS = ['8am - 9am', '9am - 10am', '10am - 11am', '2pm - 3pm', '3pm - 4pm', '4pm - 5pm'];
const CONFIRMATION_MODAL = 'confirmationModal';
const WS_URL = 'ws://' + window.location.hostname + ':8080'; // WebSocket URL

// Global variables
let currentAppointment = null;
let pendingCancelType = null;
let ws = null;
let currentViewDate = null;

// Initialize WebSocket connection
function initWebSocket() {
    ws = new WebSocket(WS_URL);

    ws.onopen = () => {
        console.log('WebSocket Connected');
        // Subscribe to booking updates
        if (currentAppointment?.type) {
            ws.send(JSON.stringify({
                action: 'subscribe',
                appointmentType: currentAppointment.type
            }));
        }
    };

    ws.onmessage = (event) => {
        const data = JSON.parse(event.data);
        handleBookingUpdate(data);
    };

    ws.onclose = () => {
        console.log('WebSocket Disconnected');
        // Attempt to reconnect after 5 seconds
        setTimeout(initWebSocket, 5000);
    };

    ws.onerror = (error) => {
        console.error('WebSocket Error:', error);
    };
}

// Handle booking updates received through WebSocket
function handleBookingUpdate(data) {
    if (!data.date || !data.time || !data.type) return;

    // If we're currently viewing the calendar and the update is for the same appointment type
    if (currentViewDate && data.type === currentAppointment?.type) {
        // Update the time slots if we're viewing the affected date
        if (currentViewDate === data.date) {
            updateTimeSlotOptions(data.date);
        }

        // Show a toast notification about the booking
        showToast(`Time slot ${data.time} on ${formatDisplayDate(data.date)} has been ${data.action}`, 'info');
        
        // If all slots are now booked for this date, update the calendar UI
        if (data.allSlotsBooked) {
            const dateInput = document.getElementById('edit_date');
            if (dateInput && dateInput.value === data.date) {
                dateInput.setCustomValidity('No available slots on this day');
                const timeSelect = document.getElementById('edit_time');
                if (timeSelect) {
                    timeSelect.innerHTML = '<option value="" disabled>All time slots are booked for this day</option>';
                    timeSelect.disabled = true;
                }
                const submitBtn = document.querySelector('#updateAppointmentForm button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;
            }
        }
    }
}

// Initialize when DOM loads
document.addEventListener('DOMContentLoaded', () => {
    // Initialize WebSocket connection
    initWebSocket();

    // Attach form submit handler
    const updateForm = document.getElementById('updateAppointmentForm');
    if (updateForm) {
        updateForm.addEventListener('submit', handleUpdateSubmit);
        
        // Add real-time validation
        document.getElementById('edit_time').addEventListener('change', checkAvailability);
    }
    
    // Attach cancel confirmation handler
    document.getElementById('confirmCancelBtn')?.addEventListener('click', handleCancelAppointment);
    
    const dateInput = document.getElementById('edit_date');
    if (dateInput) {
        // Get tomorrow's date
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        // Initialize Flatpickr
        flatpickr(dateInput, {
            enableTime: false,
            dateFormat: "Y-m-d",
            minDate: tomorrow,
            disable: [
                function(date) {
                    // Disable weekends
                    return (date.getDay() === 0 || date.getDay() === 6);
                }
            ],
            onChange: function(selectedDates, dateStr) {
                if (selectedDates[0]) {
                    currentViewDate = dateStr; // Store current view date
                    checkDayAvailability(dateStr);
                    updateTimeSlotOptions(dateStr);
                    checkAvailability();
                }
            }
        });
    }
}); 

async function checkDayAvailability(dateString) {
    if (!dateString) return;
    
    const date = new Date(dateString);
    const dateInput = document.getElementById('edit_date');
    const timeSelect = document.getElementById('edit_time');
    const submitBtn = document.querySelector('#updateAppointmentForm button[type="submit"]');
    
    // Disable weekends
    if (date.getDay() === 0 || date.getDay() === 6) {
        dateInput.setCustomValidity('Weekends are not available');
        showToast('Weekends are not available for appointments', 'error');
        timeSelect.disabled = true;
        submitBtn.disabled = true;
        dateInput.value = ''; // Clear the input
        return false;
    }
    
    // Check if date is in the past
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    if (date < tomorrow) {
        dateInput.setCustomValidity('Must book at least one day in advance');
        showToast('Must book at least one day in advance', 'error');
        timeSelect.disabled = true;
        submitBtn.disabled = true;
        dateInput.value = ''; // Clear the input
        return false;
    }
    
    try {
        const response = await fetch(CHECK_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 
                date: dateString,
                appointment_type: currentAppointment?.type || '',
                check_day_availability: true 
            }),
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.error) {
            showToast(data.error, 'error');
            timeSelect.disabled = true;
            submitBtn.disabled = true;
            return false;
        }

        if (data.available_slots === 0) {
            dateInput.setCustomValidity('No available slots on this day');
            showToast('This day is fully booked', 'error');
            timeSelect.innerHTML = '<option value="" disabled>All time slots are booked for this day</option>';
            timeSelect.disabled = true;
            submitBtn.disabled = true;
            return false;
        }

        dateInput.setCustomValidity('');
        timeSelect.disabled = false;
        submitBtn.disabled = false;
        return true;
        
    } catch (error) {
        console.error('Day availability check failed:', error);
        showToast('Error checking availability', 'error');
        timeSelect.disabled = true;
        submitBtn.disabled = true;
        return false;
    }
}

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

async function updateTimeSlotOptions(dateString) {
    const timeSelect = document.getElementById('edit_time');
    const loadingElement = document.querySelector('.time-slot-loading');
    
    if (!dateString || !timeSelect) return;

    try {
        // Get available slots for selected date
        const response = await fetch(CHECK_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 
                date: dateString,
                appointment_type: currentAppointment?.type || '',
                appointment_id: currentAppointment?.id || '',
                check_time_slots: true 
            }),
            credentials: 'include'
        });
        
        const data = await response.json();
        
        // Clear existing options
        timeSelect.innerHTML = '<option value="" disabled selected>Select a time slot</option>';
        
        if (!data.available_slots || data.available_slots === 0) {
            timeSelect.innerHTML = '<option value="" disabled>All time slots are booked for this day</option>';
            timeSelect.disabled = true;
            const submitBtn = document.querySelector('#updateAppointmentForm button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            return;
        }

        // Add time slots with appropriate styling and status indicators
        WORKING_HOURS.forEach(slot => {
            const option = document.createElement('option');
            option.value = slot;
            
            // Check various slot states
            const isCurrentSelection = currentAppointment?.originalTime === slot;
            const isUsedSlot = currentAppointment?.previousTimeSlots?.some(prev => 
                prev.time === slot && prev.date === dateString
            );
            const isBookedByOthers = data.booked_slots && data.booked_slots.some(booking => 
                booking.time === slot && 
                booking.type === currentAppointment?.type &&
                booking.client_id !== currentAppointment?.client_id
            );
            
            // Set appropriate text and styling based on slot state
            if (isCurrentSelection) {
                option.textContent = `${slot} (Current Selection)`;
                option.style.color = '#0056b3';
                option.style.backgroundColor = '#e7f3ff';
                option.style.fontWeight = 'bold';
            } else if (isUsedSlot) {
                option.textContent = `${slot} (Previously Used)`;
                option.disabled = true;
                option.style.color = '#721c24';
                option.style.backgroundColor = '#f8d7da';
            } else if (isBookedByOthers) {
                option.textContent = `${slot} (Booked)`;
                option.disabled = true;
                option.style.color = '#721c24';
                option.style.backgroundColor = '#f8d7da';
            } else {
                option.textContent = `${slot} (Available)`;
                option.style.color = '#155724';
                option.style.backgroundColor = '#d4edda';
            }
            
            timeSelect.appendChild(option);
        });
        
        timeSelect.disabled = false;
        const submitBtn = document.querySelector('#updateAppointmentForm button[type="submit"]');
        if (submitBtn) submitBtn.disabled = false;
        
    } catch (error) {
        console.error('Error updating time slots:', error);
        timeSelect.innerHTML = '<option value="" disabled>Error loading time slots</option>';
        timeSelect.disabled = true;
    }
}

function toggleEditForm(show) {
    const editForm = document.getElementById('editForm');
    const appointmentDetails = document.getElementById('appointmentDetails');
    const actionButtons = document.getElementById('actionButtons');
    
    if (show) {
        editForm.style.display = 'block';
        appointmentDetails.style.display = 'none';
        actionButtons.style.display = 'none';
        
        // Reset time slots when opening edit form
        const currentDate = document.getElementById('edit_date').value;
        if (currentDate) {
            updateTimeSlotOptions(currentDate);
        }
    } else {
        editForm.style.display = 'none';
        appointmentDetails.style.display = 'block';
        actionButtons.style.display = 'flex';
    }
}

// Update WebSocket subscription when appointment type changes
function updateWebSocketSubscription(appointmentType) {
    if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({
            action: 'subscribe',
            appointmentType: appointmentType
        }));
    }
}

// Modify loadAppointmentDetails to update WebSocket subscription
async function loadAppointmentDetails(appointmentType = 'counseling') {
    const validTypes = ['counseling', 'assessment'];
    if (!validTypes.includes(appointmentType)) {
        console.error('Invalid appointment type:', appointmentType);
        showToast('Invalid appointment type selected', 'error');
        return;
    }

    try {
        const viewBtn = document.querySelector(`button[onclick="loadAppointmentDetails('${appointmentType}')"]`);
        if (viewBtn) viewBtn.disabled = true;

        const response = await fetch(`${APPOINTMENT_API}?type=${encodeURIComponent(appointmentType)}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`Server error: ${response.status}`);
        }

        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        if (!data.appointment_id) {
            throw new Error('No appointment data received');
        }

        // Store more complete appointment information
        currentAppointment = {
            id: data.appointment_id,
            type: appointmentType,
            originalDate: data.requested_date,
            originalTime: data.requested_time,
            status: data.status,
            client_id: data.client_id,
            previousTimeSlots: data.previous_time_slots || [],
            availableSlots: data.available_slots || WORKING_HOURS
        };

        // Update WebSocket subscription with new appointment type
        updateWebSocketSubscription(appointmentType);

        displayAppointmentDetails(data);
        
        // Set the date and trigger time slot update
        const dateInput = document.getElementById('edit_date');
        dateInput.value = data.requested_date;
        currentViewDate = data.requested_date; // Store current view date
        await updateTimeSlotOptions(data.requested_date);
        
        document.getElementById('edit_appointment_id').value = data.appointment_id;
        openModal('appointmentModal');

    } catch (error) {
        console.error('Failed to load appointment:', error);
        showToast(`Failed to load appointment: ${error.message}`, 'error');
    } finally {
        document.querySelectorAll('button[onclick^="loadAppointmentDetails"]').forEach(btn => btn.disabled = false);
    }
}

// New function to populate time slots
function populateTimeSlots(availableSlots, selectedTime = '') {
    const timeSelect = document.getElementById('edit_time');
    timeSelect.innerHTML = ''; // Clear existing options
    
    // Add default option
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Select time slot';
    defaultOption.disabled = true;
    defaultOption.selected = true;
    timeSelect.appendChild(defaultOption);
    
    // Add available time slots
    availableSlots.forEach(slot => {
        const option = document.createElement('option');
        option.value = slot;
        option.textContent = slot;
        if (slot === selectedTime) {
            option.selected = true;
        }
        timeSelect.appendChild(option);
    });
}

function getStatusStyle(status) {
    const statusStyles = {
        pending: {
            bgColor: '#fff3cd',
            color: '#856404',
            border: '1px solid rgb(255, 223, 126)',
            icon: '<i class="fas fa-spinner fa-spin" style="color: #856404; margin-right: 5px;"></i>'
        },
        approved: {
            bgColor: '#d4edda',
            color: '#155724',
            border: '1px solid rgb(112, 221, 137)',
            icon: '<i class="fa-solid fa-check-double" style="color: #155724; margin-right: 5px;"></i>'
        },
        completed: {
            bgColor: '#a5b1fd',
            color: '#1743b9',
            border: '1px solid rgba(137, 169, 255, 0.75)',
            icon: '<i class="fa-solid fa-circle-check" style="color: #1743b9; margin-right: 5px;"></i>'
        },
        cancelled: {
            bgColor: '#fb6363',
            color: '#800000',
            border: '1px solid rgba(175, 49, 49, 0.51)',
            icon: '<i class="fas fa-times-circle" style="color: #800000; margin-right: 5px;"></i>'
        },
        declined: {
            bgColor: '#f8d7da',
            color: '#721c24',
            border: '1px solid rgb(241, 166, 174)',
            icon: '<i class="fa-solid fa-ban" style="color: #721c24; margin-right: 5px;"></i>'
        },
        rescheduled: {
            bgColor: '#f8d7da',
            color: '#721c24',
            border: '1px solid rgb(241, 166, 174)',
            icon: '<i class="fa-solid fa-calendar-xmark" style="color: #721c24; margin-right: 5px;"></i>'
        },
        evaluated: {
            bgColor: '#e2f7e2',
            color: '#218838',
            border: '1px solid rgba(156, 255, 177, 0.7)',
            icon: '<i class="fa-solid fa-check" style="color: #218838; margin-right: 5px;"></i>'
        }
    };

    return statusStyles[status.toLowerCase()] || {
        bgColor: '#e2e3e5',
        color: '#383d41',
        border: '1px solid #d6d8db',
        icon: ''
    };
}

function displayAppointmentDetails(data) {
    const detailsDiv = document.getElementById('appointmentDetails');
    const slipButton = document.getElementById('slipButton');
    if (!detailsDiv) return;

    const statusStyle = getStatusStyle(data.status);
    const status = data.status.toLowerCase();

    detailsDiv.innerHTML = `
        <div class="appointment-card">
            <div class="appointment-field">
                <span class="field-label">Appointment ID:</span>
                <span class="field-value">${data.appointment_id}</span>
            </div>
            <div class="appointment-field">
                <span class="field-label">Requested Date:</span>
                <span class="field-value" style="font-weight: bold;">${formatDisplayDate(data.requested_date)}</span>
            </div>
            <div class="appointment-field">
                <span class="field-label">Time Slot:</span>
                <span class="field-value" style="font-weight: bold;">${data.requested_time || 'Not selected'}</span>
            </div>
            <div class="appointment-field">
                <span class="field-label">Status:</span>
                <span class="status-badge" style="
                    background-color: ${statusStyle.bgColor};
                    color: ${statusStyle.color};
                    border: ${statusStyle.border};
                    padding: 8px 12px;
                    border-radius: 999px;
                    display: inline-flex;
                    align-items: center;
                    font-weight: 500;
                ">
                    ${statusStyle.icon}
                    ${formatStatus(data.status)}
                </span>
            </div>
        </div>
        ${status !== 'pending' && status !== 'rescheduled' ? `
        <div class="alert alert-info" style="
            margin-top: 15px;
            padding: 10px 15px;
            border-radius: 4px;
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        ">
            <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
            ${status === 'rescheduled' ? 
              'Please select a new time slot for your appointment.' :
              'You can only edit or cancel appointments when they are in Pending status.'}
        </div>
        ` : ''}
    `;

    // Show/hide slip button based on status
    if (slipButton) {
        slipButton.style.display = status === 'approved' ? 'block' : 'none';
    }

    // Update modal header color based on status
    const modalHeader = document.querySelector('#appointmentModal h2');
    if (modalHeader) {
        modalHeader.style.color = statusStyle.color;
    }

    // Show/hide action buttons based on status
    const actionButtons = document.getElementById('actionButtons');
    if (actionButtons) {
        const canModify = status === 'pending' || status === 'rescheduled';
        
        const editBtn = actionButtons.querySelector('.edit-btn');
        const cancelBtn = actionButtons.querySelector('.cancel-btn');
        
        if (editBtn) {
            editBtn.style.display = canModify ? 'block' : 'none';
            editBtn.title = canModify ? 'Edit appointment' : 'Cannot edit this appointment';
        }
        
        if (cancelBtn) {
            cancelBtn.style.display = status === 'pending' ? 'block' : 'none';
            cancelBtn.title = status === 'pending' ? 'Cancel appointment' : 'Cannot cancel this appointment';
        }

        // If no buttons are visible, hide the container
        actionButtons.style.display = canModify ? 'flex' : 'none';
    }
}

// Function to view appointment slip
function viewAppointmentSlip() {
    if (!currentAppointment?.id) {
        showToast('No appointment selected', 'error');
        return;
    }

    // Open slip in new window
    const slipUrl = `${SLIP_API}?appointment_id=${currentAppointment.id}`;
    window.open(slipUrl, '_blank', 'width=800,height=800');
}

// Add styles for the slip button
const style = document.createElement('style');
style.textContent = `
    .slip-btn {
        background-color: #16633F;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .slip-btn:hover {
        background-color: #124F32;
    }
    .slip-btn i {
        font-size: 16px;
    }
`;
document.head.appendChild(style);

// Appointment Editing Functions
async function handleUpdateSubmit(event) {
    event.preventDefault();
    
    // Check if appointment is still pending
    if (currentAppointment?.status.toLowerCase() !== 'pending') {
        showToast('You can only edit appointments that are in pending status', 'error');
        return;
    }
    
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.innerHTML;
    const loadingSpinner = submitBtn.querySelector('.loading-spinner');
    const btnTextSpan = submitBtn.querySelector('.btn-text');
    
    try {
        // Get form values
        const appointmentId = document.getElementById('edit_appointment_id').value;
        const newDate = document.getElementById('edit_date').value;
        const newTime = document.getElementById('edit_time').value;
        
        // Validate inputs
        const validationError = validateEditInputs(newDate, newTime);
        if (validationError) {
            showToast(validationError, 'error');
            return;
        }
        
        // Set loading state
        submitBtn.disabled = true;
        if (loadingSpinner && btnTextSpan) {
            btnTextSpan.style.display = 'none';
            loadingSpinner.style.display = 'inline-block';
        } else {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        }
        
        // Prepare form data
        const formData = new URLSearchParams();
        formData.append('appointment_id', appointmentId);
        formData.append('requested_date', newDate);
        formData.append('requested_time', newTime);
        
        // Artificial delay for loading state
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Send update request
        const response = await fetch(UPDATE_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData,
            credentials: 'include'
        });
        
        // Check for HTTP errors
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Server responded with ${response.status}: ${errorText}`);
        }
        
        // Parse JSON response
        const result = await response.json();
        
        // Check for application-level errors
        if (!result.success) {
            throw new Error(result.error || 'Update failed without specific error');
        }
        
        // Success handling
        showToast('Appointment updated successfully', 'success');
        toggleEditForm(false);
        
        // Refresh the appointment details
        await loadAppointmentDetails(currentAppointment.type);
        
    } catch (error) {
        console.error('Update failed:', error);
        showToast(`Update error: ${error.message}`, 'error');
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        if (loadingSpinner && btnTextSpan) {
            btnTextSpan.style.display = 'inline';
            loadingSpinner.style.display = 'none';
        } else {
            submitBtn.innerHTML = btnText;
        }
    }
}

// Real-time availability check
async function checkAvailability() {
    const dateInput = document.getElementById('edit_date');
    const timeSelect = document.getElementById('edit_time');
    const loadingElement = document.querySelector('.time-slot-loading');
    const submitBtn = document.querySelector('#updateAppointmentForm button[type="submit"]');
    
    if (!dateInput.value || !timeSelect.value) return;
    
    // Show loading animation
    loadingElement.style.display = 'flex';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch(CHECK_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                date: dateInput.value,
                time: timeSelect.value,
                appointment_id: currentAppointment?.id || '',
                appointment_type: currentAppointment?.type || ''
            })
        });

        const data = await response.json();
        
        if (data.error) {
            showToast(data.error, 'error');
            submitBtn.disabled = true;
            return;
        }

        if (!data.available) {
            showToast('This time slot is already booked', 'error');
            submitBtn.disabled = true;
            return;
        }

        // Enable submit button if slot is available
        submitBtn.disabled = false;

    } catch (error) {
        console.error('Availability check failed:', error);
        showToast('Error checking availability', 'error');
        submitBtn.disabled = true;
    } finally {
        // Hide loading animation
        loadingElement.style.display = 'none';
    }
}

function createErrorDisplay() {
    const timeSelect = document.getElementById('edit_time');
    const errorDisplay = document.createElement('div');
    errorDisplay.id = 'timeSlotError';
    errorDisplay.style.color = '#dc3545';
    errorDisplay.style.marginTop = '5px';
    errorDisplay.style.display = 'none';
    timeSelect.parentNode.appendChild(errorDisplay);
}

function cancelAppointment(appointmentType = 'counseling') {
    if (currentAppointment?.status.toLowerCase() !== 'pending') {
        showToast('You can only cancel appointments that are in pending status', 'error');
        return;
    }

    const validTypes = ['counseling', 'assessment'];
    if (!validTypes.includes(appointmentType)) {
        console.error('Invalid appointment type:', appointmentType);
        return;
    }
    pendingCancelType = appointmentType;
    openModal('confirmationModal');
}


async function handleCancelAppointment() {
    try {
        if (!currentAppointment?.id) throw new Error('No appointment selected');

        const response = await fetch(CANCEL_API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: `appointment_id=${currentAppointment.id}&type=${pendingCancelType}`,
            credentials: 'include'
        });
        
        if (!response.ok) throw new Error(`Server error: ${response.status}`);
        const data = await response.json();
        if (data.error) throw new Error(data.error);
        
        showToast('Appointment cancelled successfully', 'success');
        closeModal('appointmentModal');
        closeModal('confirmationModal');
        
        // Full page refresh after cancellation
        window.location.reload();
        
    } catch (error) {
        console.error('Cancellation Error:', error);
        showToast(`Error: ${error.message}`, 'error');
        closeModal('confirmationModal');
    }
}

// Helper Functions
function validateEditInputs(newDate, newTime) {
    if (!newDate || !newTime) return 'Please fill all fields';
    
    const selectedDate = new Date(newDate);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) return 'Cannot select a past date';
    if (selectedDate.getDay() === 0 || selectedDate.getDay() === 6) {
        return 'Weekend dates are not available';
    }
    
    return null;
}

function isDifferentFromCurrent(newDate, newTime) {
    return (
        newDate !== currentAppointment?.originalDate || 
        newTime !== currentAppointment?.originalTime
    );
}

function formatDisplayDate(dateString) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function formatStatus(status) {
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function checkTimeSlotAvailability(date, time, appointmentType) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'check_timeslot.php',
            method: 'POST',
            data: {
                date: date,
                time: time,
                appointment_type: appointmentType
            },
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    if (data.appointments && data.appointments.length > 0) {
                        // Check if all appointments for this slot are declined
                        const hasNonDeclinedAppointments = data.appointments.some(appointment => 
                            appointment.status.toLowerCase() !== 'declined'
                        );
                        resolve(!hasNonDeclinedAppointments);
                    } else {
                        resolve(true);
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    reject(e);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking time slot:', error);
                reject(error);
            }
        });
    });
}