const calendarDays = document.getElementById('calendarDays');
const calendarMonth = document.getElementById('calendarMonth');
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
const today = new Date();

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
        const formattedDate = `${year}-${(month + 1).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`;
        
        if (dayOfWeek === 0 || dayOfWeek === 6 || cellDate < today) {
            dayCell.classList.add('disabled');
        } else if (fullyBookedDates.includes(formattedDate)) {
            dayCell.classList.add('fully-booked');
            dayCell.onmouseenter = (e) => showDayTooltip('Day is fully booked, please choose another day.', e.target);
            dayCell.onmouseleave = hideDayTooltip;
        } else {
            dayCell.onclick = () => selectDay(i);
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

function updateTimeSlotsAvailability(date) {
    // Reset all time slots
    const timeSlots = document.querySelectorAll('.time-slot');
    timeSlots.forEach(slot => {
        slot.classList.remove('booked');
        slot.style.pointerEvents = 'auto';
        slot.style.cursor = 'pointer';
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
            }
        });
    }
}

function selectTimeSlot(time) {
    if (!isDaySelected) {
        const timeSlots = document.querySelectorAll('.time-slot');
        let clickedElement = null;
        timeSlots.forEach(slot => {
            if (slot.getAttribute('data-time') === time) {
                clickedElement = slot;
            }
        });
        
        if (clickedElement) {
            showTooltip('Please select a date first!', clickedElement);
        }
        return;
    }

    const clickedSlot = document.querySelector(`.time-slot[data-time="${time}"]`);
    if (clickedSlot.classList.contains('booked')) {
        showTooltip('This time slot is already booked!', clickedSlot);
        return;
    }

    // Remove selection from previously selected time slot
    if (selectedTimeSlot) {
        selectedTimeSlot.classList.remove('selected');
        selectedTimeSlot.style.backgroundColor = '';
        selectedTimeSlot.style.color = '';
    }

    // Select the new time slot
    clickedSlot.classList.add('selected');
    clickedSlot.style.backgroundColor = '#11AD64';
    clickedSlot.style.color = 'white';
    selectedTimeSlot = clickedSlot;

    document.getElementById('requested_time').value = time;
    hideTooltip();
}

function clearTimeSelection() {
    if (selectedTimeSlot) {
        selectedTimeSlot.classList.remove('selected');
        selectedTimeSlot.style.backgroundColor = '';
        selectedTimeSlot.style.color = '';
        selectedTimeSlot = null;
    }
    document.getElementById('requested_time').value = '';
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

document.getElementById('appointmentForm').addEventListener('submit', function(event) {
    const dateSelected = document.getElementById('requested_date').value;
    const timeSelected = document.getElementById('requested_time').value;
    
    if (!dateSelected || !timeSelected) {
        alert('Please select both a date and a time for your appointment.');
        event.preventDefault(); 
    }
});

generateCalendar(currentMonth, currentYear);