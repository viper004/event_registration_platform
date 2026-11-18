// Simple JavaScript Form Validation & Utilities

document.addEventListener("DOMContentLoaded", function () {
    const regForm = document.getElementById("registrationForm");

    if (regForm) {
        regForm.addEventListener("submit", function (e) {
            const nameInput = document.getElementById("name");
            const emailInput = document.getElementById("email");
            const eventInput = document.getElementById("event");

            const name = nameInput.value.trim();
            const email = emailInput.value.trim();
            const event = eventInput.value.trim();

            if (name === "" || email === "" || event === "") {
                alert("Please fill in all required fields.");
                e.preventDefault();
                return false;
            }

            // Simple email validation regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert("Please enter a valid email address (e.g. name@example.com).");
                e.preventDefault();
                return false;
            }
        });
    }
});

// Confirmation for delete actions
function confirmDelete(type) {
    return confirm("Are you sure you want to delete this " + (type || "item") + "?");
}

// Client-side quick filter for attendees table
function filterAttendeeTable(input, tableId) {
    const filter = input.value.toLowerCase().trim();
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.getElementsByTagName("tr");
    for (let i = 1; i < rows.length; i++) {
        const rowText = rows[i].textContent || rows[i].innerText;
        if (rowText.toLowerCase().indexOf(filter) > -1) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
}
