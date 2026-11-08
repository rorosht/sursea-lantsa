// passenger.js - passenger pages, login and register (plain JavaScript)

// ------------------------------------------------------------
// 1. Ticket pop-up: open / close
// ------------------------------------------------------------
document.querySelectorAll("[data-open-modal]").forEach(function (button) {
    button.addEventListener("click", function () {
        document.getElementById(button.dataset.openModal).classList.add("open");
    });
});

document.querySelectorAll(".modal").forEach(function (modal) {
    // X button
    modal
        .querySelectorAll("[data-close-modal]")
        .forEach(function (closeButton) {
            closeButton.addEventListener("click", function () {
                modal.classList.remove("open");
            });
        });
    // click on the dark background
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.classList.remove("open");
        }
    });
});

// Escape key
document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        document.querySelectorAll(".modal.open").forEach(function (modal) {
            modal.classList.remove("open");
        });
    }
});

// ------------------------------------------------------------
// 2. Decorative QR picture (drawn from the reference code)
//    NOTE: this is only a picture. A real QR code can be added later.
// ------------------------------------------------------------
function drawQr(box) {
    var size = 25;
    var code = box.dataset.code || "0";

    // turn the code text into a number, so each ticket looks different
    var seed = 1;
    for (var i = 0; i < code.length; i++) {
        seed = (seed * 31 + code.charCodeAt(i)) % 2147483647;
    }
    function nextRandom() {
        seed = (seed * 48271) % 2147483647;
        return seed;
    }

    // is this cell part of one of the 3 big corner squares?
    function finderCell(x, y) {
        var corners = [
            [0, 0],
            [size - 7, 0],
            [0, size - 7],
        ];
        for (var c = 0; c < corners.length; c++) {
            var dx = x - corners[c][0];
            var dy = y - corners[c][1];
            if (dx >= -1 && dx <= 7 && dy >= -1 && dy <= 7) {
                if (dx < 0 || dx > 6 || dy < 0 || dy > 6) {
                    return "white";
                }
                var ring = dx === 0 || dx === 6 || dy === 0 || dy === 6;
                var center = dx >= 2 && dx <= 4 && dy >= 2 && dy <= 4;
                return ring || center ? "black" : "white";
            }
        }
        return null;
    }

    var squares = "";
    for (var y = 0; y < size; y++) {
        for (var x = 0; x < size; x++) {
            var finder = finderCell(x, y);
            var black =
                finder === null ? nextRandom() % 2 === 0 : finder === "black";
            if (black) {
                squares +=
                    '<rect x="' + x + '" y="' + y + '" width="1" height="1"/>';
            }
        }
    }

    box.innerHTML =
        '<svg viewBox="0 0 ' +
        size +
        " " +
        size +
        '" shape-rendering="crispEdges" fill="#000">' +
        squares +
        "</svg>";
}

document.querySelectorAll(".qr").forEach(drawQr);

// ------------------------------------------------------------
// 3. Cargo lines: add / remove
// ------------------------------------------------------------
var cargoRows = document.getElementById("cargoRows");
var cargoTemplate = document.getElementById("cargoTemplate");
var addCargoButton = document.getElementById("addCargo");

function updateTrashButtons() {
    // when there is only one line, hide its trash button
    cargoRows.classList.toggle("single", cargoRows.children.length === 1);
}

if (cargoRows && cargoTemplate && addCargoButton) {
    addCargoButton.addEventListener("click", function () {
        cargoRows.appendChild(cargoTemplate.content.cloneNode(true));
        updateTrashButtons();
    });

    cargoRows.addEventListener("click", function (event) {
        var trash = event.target.closest("[data-remove-row]");
        if (trash && cargoRows.children.length > 1) {
            trash.closest(".cargo-row").remove();
            updateTrashButtons();
        }
    });

    updateTrashButtons();
}

// ------------------------------------------------------------
// 4. Login / Register: Passenger and Operator tabs
// ------------------------------------------------------------
var roleTabs = document.querySelectorAll("[data-role-tab]");

roleTabs.forEach(function (tab) {
    tab.addEventListener("click", function () {
        var role = tab.dataset.roleTab;

        roleTabs.forEach(function (other) {
            other.classList.toggle("active", other === tab);
        });
        document.getElementById("roleInput").value = role;

        // change the button text, e.g. "Log in as operator"
        var submitButton = document.getElementById("submitBtn");
        submitButton.textContent = submitButton.dataset[role + "Text"];

        var hint = document.getElementById("roleHint");
        if (hint) {
            hint.textContent = hint.dataset[role + "Text"];
        }

        // register page only: show the business fields for operators
        var operatorFields = document.getElementById("operatorFields");
        if (operatorFields) {
            operatorFields.classList.toggle("hidden", role !== "operator");
        }
    });
});

// ------------------------------------------------------------
// 5. Home page: "+ Add Details" for cargo
// ------------------------------------------------------------
var cargoToggle = document.getElementById("cargoToggle");
var cargoNote = document.getElementById("cargoNote");

if (cargoToggle && cargoNote) {
    cargoToggle.addEventListener("click", function () {
        cargoNote.classList.toggle("hidden");
        cargoToggle.textContent = cargoNote.classList.contains("hidden")
            ? "+ Add Details"
            : "− Hide Details";
    });
}

// ------------------------------------------------------------
// 6. Passenger Notifications page: category / unread filter tabs
// ------------------------------------------------------------
var notifFilterBtns = document.querySelectorAll("[data-notif-filter]");
var notifCards = document.querySelectorAll("#passengerNotifList .notif-card");

notifFilterBtns.forEach(function (btn) {
    btn.addEventListener("click", function () {
        var filter = btn.dataset.notifFilter;
        notifFilterBtns.forEach(function (other) {
            other.classList.toggle("active", other === btn);
        });
        notifCards.forEach(function (card) {
            var status = card.dataset.notifStatus;
            var type = card.dataset.notifType;
            var show =
                filter === "all" ||
                (filter === "Unread" && status === "Unread") ||
                type === filter;
            card.classList.toggle("hidden", !show);
        });
    });
});

// ------------------------------------------------------------
// 7. Passenger Profile page: module switcher tabs
// ------------------------------------------------------------
var profileTabBtns = document.querySelectorAll("[data-profile-tab]");

profileTabBtns.forEach(function (btn) {
    btn.addEventListener("click", function () {
        var tabName = btn.dataset.profileTab;
        profileTabBtns.forEach(function (other) {
            other.classList.toggle("active", other === btn);
        });
        document
            .querySelectorAll(".profile-module-panel")
            .forEach(function (panel) {
                panel.classList.toggle(
                    "hidden",
                    panel.id !== "profileTab-" + tabName
                );
            });
    });
});

// ------------------------------------------------------------
// 8. Passenger Profile Transactions: View Ticket modal
// ------------------------------------------------------------
function escapeHtmlText(text) {
    var div = document.createElement("div");
    div.textContent = text || "";
    return div.innerHTML;
}

var txTicketModal = document.getElementById("txTicketModal");

document.querySelectorAll("[data-open-tx-ticket]").forEach(function (btn) {
    btn.addEventListener("click", function () {
        if (!txTicketModal) return;

        var type = btn.dataset.openTxTicket;
        var ref = btn.dataset.ref || "0000-00001";
        var titleEl = document.getElementById("txModalTitle");
        var bodyEl = document.getElementById("txModalBody");
        var totalWrap = document.getElementById("txModalTotalWrap");
        var totalEl = document.getElementById("txModalTotal");
        var noteEl = document.getElementById("txModalNote");
        var qrEl = document.getElementById("txModalQr");

        if (type === "cargo") {
            titleEl.textContent = "Cargo Delivery Ticket";
            bodyEl.innerHTML =
                "<dt>Cargo Tracking ID:</dt><dd>" + escapeHtmlText(ref) + "</dd>" +
                "<dt>Sender name:</dt><dd>" + escapeHtmlText(btn.dataset.sender) + "</dd>" +
                "<dt>Sender contact number:</dt><dd>" + escapeHtmlText(btn.dataset.senderContact) + "</dd>" +
                "<dt>Receiver name:</dt><dd>" + escapeHtmlText(btn.dataset.receiver) + "</dd>" +
                "<dt>Receiver contact number:</dt><dd>" + escapeHtmlText(btn.dataset.receiverContact) + "</dd>" +
                "<dt>Cargo name:</dt><dd>" + escapeHtmlText(btn.dataset.items) + "</dd>" +
                "<dt>Cargo quantity:</dt><dd>" + escapeHtmlText(btn.dataset.qty) + "</dd>" +
                "<dt>Lantsa name:</dt><dd>" + escapeHtmlText(btn.dataset.lantsa) + "</dd>";
            totalWrap.classList.add("hidden");
            noteEl.textContent = "Please take a screenshot on this ticket for verification before delivery.";
        } else {
            titleEl.textContent = "Passenger Reservation Ticket";
            bodyEl.innerHTML =
                "<dt>Reservation reference code:</dt><dd>" + escapeHtmlText(ref) + "</dd>" +
                "<dt>Passenger full name:</dt><dd>" + escapeHtmlText(btn.dataset.name) + "</dd>" +
                "<dt>Number of passengers:</dt><dd>" + escapeHtmlText(btn.dataset.passengers) + "</dd>" +
                "<dt>Cargo quantity:</dt><dd>" + escapeHtmlText(btn.dataset.cargo || "0") + "</dd>" +
                "<dt>Route and departure date/time:</dt><dd>" + escapeHtmlText(btn.dataset.route) + "</dd>" +
                "<dt>Lantsa name:</dt><dd>" + escapeHtmlText(btn.dataset.lantsa) + "</dd>" +
                "<dt>Contact Number:</dt><dd>" + escapeHtmlText(btn.dataset.contact) + "</dd>";
            totalWrap.classList.remove("hidden");
            totalEl.textContent = btn.dataset.total || "150";
            noteEl.textContent = "Please take a screenshot on this ticket for verification before boarding";
        }

        if (qrEl) {
            qrEl.dataset.code = ref;
            drawQr(qrEl);
        }

        txTicketModal.classList.add("open");
    });
});
