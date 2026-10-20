
document.addEventListener("DOMContentLoaded", function () {
    const themeToggle = document.getElementById("theme-toggle");
    const themeIcon = document.getElementById("theme-icon");

    function applyTheme(theme) {
        document.body.setAttribute("data-theme", theme);

        if (themeIcon) {
            themeIcon.className =
                theme === "dark"
                    ? "bi bi-sun"
                    : "bi bi-moon-stars";
        }

        if (themeToggle) {
            const label =
                theme === "dark"
                    ? "Enable light mode"
                    : "Enable dark mode";

            themeToggle.setAttribute("aria-label", label);
            themeToggle.setAttribute("title", label);
        }
    }

    const savedTheme = localStorage.getItem("eventflow-theme");
    applyTheme(savedTheme === "dark" ? "dark" : "light");

    if (themeToggle) {
        themeToggle.addEventListener("click", function () {
            const currentTheme =
                document.body.getAttribute("data-theme") || "light";

            const newTheme =
                currentTheme === "dark" ? "light" : "dark";

            applyTheme(newTheme);
            localStorage.setItem("eventflow-theme", newTheme);
        });
    }
});

// Update the dashboard date and time when those elements exist.
function updateDashboardDateTime() {
    const dateElement = document.getElementById('current-date');
    const timeElement = document.getElementById('current-time');

    if (!dateElement || !timeElement) {
        return;
    }

    const now = new Date();

    dateElement.textContent = new Intl.DateTimeFormat('en-GB', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        timeZone: 'Asia/Colombo'
    }).format(now);

    timeElement.textContent = new Intl.DateTimeFormat('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
        timeZone: 'Asia/Colombo'
    }).format(now);
}

updateDashboardDateTime();
setInterval(updateDashboardDateTime, 1000);
