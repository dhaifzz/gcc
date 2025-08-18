$(document).ready(function () {
    var table = $('#usersTable').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "lengthMenu": [5, 10, 25, 50],
        "pageLength": 10,
        "responsive": true,
        "language": {
            "search": "Search User:",
            "lengthMenu": "Display _MENU_",
            "info": "Showing _START_ to _END_ of _TOTAL_ users",
            "infoEmpty": "No users available",
            "infoFiltered": "(filtered from _MAX_ total users)",
            "paginate": {
                "first": "<i class='fa-solid fa-chevron-left'></i>",
                "last": "<i class='fa-solid fa-chevron-right'></i>",
                "next": "<i class='fa-solid fa-angles-right'></i>",
                "previous": "<i class='fa-solid fa-angles-left'></i>"
            },
            "zeroRecords": "No matching users found."
        }
    });

    function updatePaginationIcons() {
        $(".paginate_button.previous").html('<i class="fa-solid fa-angles-left"></i>');  // Previous
        $(".paginate_button.next").html('<i class="fa-solid fa-angles-right"></i>');      // Next
        $(".paginate_button.first").html('<i class="fa-solid fa-chevron-left"></i>');   // First
        $(".paginate_button.last").html('<i class="fa-solid fa-chevron-right"></i>');   // Last
    }

    updatePaginationIcons();

    table.on('draw', function () {
        updatePaginationIcons();
    });
});
