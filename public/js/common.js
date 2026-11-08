
function closeDropdowns(exceptMenu) {
    document.querySelectorAll(".dropdown-menu.open").forEach(function (menu) {
        if (menu !== exceptMenu) {
            menu.classList.remove("open");
        }
    });
}

document.querySelectorAll("[data-dropdown-btn]").forEach(function (button) {
    button.addEventListener("click", function (event) {
        event.stopPropagation();
        var menu = document.getElementById(button.dataset.dropdownBtn);
        if (menu) {
            closeDropdowns(menu);
            menu.classList.toggle("open");
        }
    });
});

// Prevent clicks inside the dropdown from closing it unexpectedly, unless a link or submit button is clicked
document.querySelectorAll(".dropdown-menu").forEach(function (menu) {
    menu.addEventListener("click", function (event) {
        if (event.target.closest("a, button[type='submit']")) {
            return;
        }
        event.stopPropagation();
    });
});

// Clicking anywhere else or pressing Escape closes the menus
document.addEventListener("click", function () {
    closeDropdowns(null);
});

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        closeDropdowns(null);
    }
});
