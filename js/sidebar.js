function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    sidebar.classList.toggle('active');
    
    if (!overlay) {
        const newOverlay = document.createElement('div');
        newOverlay.className = 'sidebar-overlay';
        document.body.appendChild(newOverlay);
        newOverlay.addEventListener('click', toggleSidebar);
    } else {
        overlay.classList.toggle('active');
    }
    
document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : 'auto';
}

document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth < 768) {
            toggleSidebar();
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
        const dropdowns = document.querySelectorAll(".dropdown-btn");

    dropdowns.forEach(btn => {
                btn.addEventListener("click", function () {
                    const parent = btn.parentElement;
                    parent.classList.toggle("active");
        
             });
        });
    });
