const calendarDays = document.getElementById('calendarDays');
const calendarMonth = document.getElementById('calendarMonth');
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

// Set today to start of day for proper comparison
const today = new Date();
today.setHours(0, 0, 0, 0);

// Get tomorrow's date for minimum booking
const tomorrow = new Date(today);
tomorrow.setDate(tomorrow.getDate() + 1);

let isDaySelected = false;
let selectedTimeSlot = null;
let selectedDate = null;

function generateCalendar(month, year) {
    const date = new Date(year, month, 1);
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const firstDay = date.getDay();
    calendarDays.innerHTML = '';

    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    weekdays.forEach(day => {
        const dayCell = document.createElement('div');
        dayCell.textContent = day;
        dayCell.classList.add('weekdays');
        calendarDays.appendChild(dayCell);
    });

    for (let i = 0; i < firstDay; i++) {
        const emptyCell = document.createElement('div');
        calendarDays.appendChild(emptyCell);
    }

    for (let i = 1; i <= daysInMonth; i++) {
        const dayCell = document.createElement('div');
        dayCell.textContent = i;
        const dayOfWeek = new Date(year, month, i).getDay();
        const cellDate = new Date(year, month, i);
        cellDate.setHours(0, 0, 0, 0); // Set to start of day for proper comparison
        const formattedDate = `${year}-${(month + 1).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`;
        
        // Check if date is before tomorrow, weekend, or fully booked
        if (dayOfWeek === 0 || dayOfWeek === 6 || cellDate < tomorrow) {
            dayCell.classList.add('disabled');
            dayCell.title = dayOfWeek === 0 || dayOfWeek === 6 ? 'Weekends are not available' : 'Must book at least one day in advance';
        } else if (fullyBookedDates.includes(formattedDate)) {
            dayCell.classList.add('fully-booked');
            dayCell.title = 'All time slots are booked for this day';
            dayCell.onmouseenter = (e) => showDayTooltip('All time slots are booked for this day', e.target);
            dayCell.onmouseleave = hideDayTooltip;
        } else {
            dayCell.classList.add('available');
            dayCell.onclick = () => selectDay(i);
            
            // Show number of available slots if any are booked
            if (bookedAppointments[formattedDate]) {
                const bookedCount = bookedAppointments[formattedDate].length;
                const availableCount = allTimeSlots.length - bookedCount;
                dayCell.title = `${availableCount} time slots available`;
            }
        }
        calendarDays.appendChild(dayCell);
    }

    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];
    calendarMonth.innerHTML = `${monthNames[month]}<br><span style="color:rgb(17, 147, 87); font-size: 22px;">${year}</span>`;
}

function selectDay(day) {
    clearTimeSelection();
    
    const selectedDay = document.querySelector('.calendar-grid .selected');
    if (selectedDay) {
        selectedDay.classList.remove('selected');
    }
    
    const dayCells = calendarDays.children;
    for (let cell of dayCells) {
        if (cell.textContent == day && !cell.classList.contains('disabled') && !cell.classList.contains('fully-booked')) {
            cell.classList.add('selected');
            selectedDate = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            document.getElementById('requested_date').value = selectedDate;
            isDaySelected = true;
            hideTooltip();
            
            // Update time slots availability for the selected date
            updateTimeSlotsAvailability(selectedDate);
            break;
        }
    }
}

function clearTimeSelection() {
    if (selectedTimeSlot) {
        selectedTimeSlot.classList.remove('selected');
        selectedTimeSlot.style.backgroundColor = '';
        selectedTimeSlot.style.color = '';
        selectedTimeSlot = null;
        document.getElementById('requested_time').value = '';
    }
}

function updateTimeSlotsAvailability(date) {
    // Reset all time slots
    const timeSlots = document.querySelectorAll('.time-slot');
    timeSlots.forEach(slot => {
        slot.classList.remove('booked', 'selected');
        slot.style.pointerEvents = 'auto';
        slot.style.cursor = 'pointer';
        slot.style.backgroundColor = '';
        slot.style.color = '';
        slot.title = 'Click to select this time slot';
    });

    // If there are booked appointments for this date, disable those time slots
    if (bookedAppointments[date]) {
        const bookedTimes = bookedAppointments[date];
        timeSlots.forEach(slot => {
            const slotTime = slot.getAttribute('data-time');
            if (bookedTimes.includes(slotTime)) {
                slot.classList.add('booked');
                slot.style.pointerEvents = 'none';
                slot.style.cursor = 'not-allowed';
                slot.style.backgroundColor = '#f8d7da';
                slot.style.color = '#721c24';
                slot.title = 'This time slot is already booked';
            }
        });
    }
}

function selectTimeSlot(time) {
    if (!isDaySelected) {
        showPopupMessage('Please select a date first!');
        return;
    }
    
    const clickedSlot = document.querySelector(`.time-slot[data-time="${time}"]`);
    if (clickedSlot.classList.contains('booked')) {
        showPopupMessage('This time slot is already booked!');
        return;
    }
    
    // Remove selection from previously selected time slot
    clearTimeSelection();

    // Select the new time slot
    clickedSlot.classList.add('selected');
    clickedSlot.style.backgroundColor = '#11AD64';
    clickedSlot.style.color = 'white';
    selectedTimeSlot = clickedSlot;

    document.getElementById('requested_time').value = time;
    hideTooltip();
}

function showTooltip(message, element) {
    let tooltip = document.getElementById('timeSlotTooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'timeSlotTooltip';
        document.body.appendChild(tooltip);
    }

    tooltip.textContent = message;
    tooltip.style.display = 'block';

    const rect = element.getBoundingClientRect();
    tooltip.style.top = `${rect.top - 40}px`;
    tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
}

function showDayTooltip(message, element) {
    let tooltip = document.getElementById('dayTooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'dayTooltip';
        document.body.appendChild(tooltip);
    }

    tooltip.textContent = message;
    tooltip.style.display = 'block';

    const rect = element.getBoundingClientRect();
    tooltip.style.top = `${rect.top - 40}px`;
    tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
}

function hideTooltip() {
    const tooltip = document.getElementById('timeSlotTooltip');
    if (tooltip) {
        tooltip.style.display = 'none';
    }
}

function hideDayTooltip() {
    const tooltip = document.getElementById('dayTooltip');
    if (tooltip) {
        tooltip.style.display = 'none';
    }
}

function prevMonth() {
    if (currentMonth > 0) {
        currentMonth--;
    } else {
        currentMonth = 11;
        currentYear--;
    }
    if (currentYear >= today.getFullYear()) {
        generateCalendar(currentMonth, currentYear);
    }
}

function nextMonth() {
    if (currentMonth < 11) {
        currentMonth++;
    } else {
        currentMonth = 0;
        currentYear++;
    }
    generateCalendar(currentMonth, currentYear);
}

function showPopupMessage(message) {
    let popup = document.getElementById('popupMessage');
    
    // Create the popup if it doesn't exist
    if (!popup) {
        popup = document.createElement('div');
        popup.id = 'popupMessage';
        popup.style.position = 'fixed';
        popup.style.top = '30px';
        popup.style.left = '50%';
        popup.style.transform = 'translateX(-50%)';
        popup.style.background = '#cc3131';
        popup.style.color = '#fff';
        popup.style.padding = '14px 32px';
        popup.style.borderRadius = '8px';
        popup.style.fontSize = '1em';
        popup.style.zIndex = '3000';
        popup.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
        popup.style.opacity = '0';
        popup.style.transition = 'opacity 0.3s ease-in-out';
        popup.style.pointerEvents = 'none'; // Prevent interaction during fade
        document.body.appendChild(popup);
    }
    
    // Set the message and make visible
    popup.textContent = message;
    popup.style.display = 'block';
    
    // Trigger fade in (needs a small timeout to work properly)
    setTimeout(() => {
        popup.style.opacity = '1';
    }, 10);
    
    // Set up fade out and removal
    setTimeout(() => {
        popup.style.opacity = '0';
        
        // Remove after fade out completes
        setTimeout(() => {
            popup.style.display = 'none';
        }, 300); // Matches the transition duration
    }, 1500);
}

document.getElementById('appointmentForm')?.addEventListener('submit', function(event) {
    const dateSelected = document.getElementById('requested_date').value;
    const timeSelected = document.getElementById('requested_time').value;
    
    if (!dateSelected || !timeSelected) {
        showPopupMessage('Please select both a date and a time for your appointment.');
        event.preventDefault(); 
    }
});

// WAG GALAWIN TO:
generateCalendar(currentMonth, currentYear);