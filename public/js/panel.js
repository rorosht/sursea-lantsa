

// 1. Search box + status filter for tables
var searchInput = document.getElementById("tableSearch");
var statusSelect = document.getElementById("tableStatus");
var tableRows = document.querySelectorAll("#filterTable tbody tr");

function filterTable() {
    var text = searchInput ? searchInput.value.toLowerCase() : "";
    var status = statusSelect ? statusSelect.value : "";

    tableRows.forEach(function (row) {
        var textMatch = row.textContent.toLowerCase().indexOf(text) !== -1;

        var badges = row.querySelectorAll(".badge");
        var statusMatch = status === "";
        badges.forEach(function (b) {
            if (b.textContent.trim() === status) {
                statusMatch = true;
            }
        });

        row.classList.toggle("hidden", !(textMatch && statusMatch));
    });
}

if (searchInput) {
    searchInput.addEventListener("input", filterTable);
    if (searchInput.value.trim() !== "") {
        filterTable();
    }
}
if (statusSelect) {
    statusSelect.addEventListener("change", filterTable);
}

// 2. Notifications: "Mark all as read"--
var markReadButton = document.getElementById("markAllRead");

if (markReadButton) {
    markReadButton.addEventListener("click", function () {
        document
            .querySelectorAll("#filterTable .badge")
            .forEach(function (badge) {
                if (badge.textContent.trim() === "Unread") {
                    badge.textContent = "Read";
                    badge.className = "badge grey";
                }
            });
        var unreadStat = document.getElementById("unreadStatCount");
        if (unreadStat) {
            unreadStat.textContent = "0";
        }
    });
}


// 3. Review operator: "Approve" works only when all 3 checks are ticked
var checkBoxes = document.querySelectorAll('input[name="checks[]"]');
var approveButton = document.getElementById("approveBtn");

function updateApproveButton() {
    var allChecked = true;
    checkBoxes.forEach(function (box) {
        if (!box.checked) {
            allChecked = false;
        }
    });
    approveButton.disabled = !allChecked;
}

if (approveButton && checkBoxes.length > 0) {
    checkBoxes.forEach(function (box) {
        box.addEventListener("change", updateApproveButton);
    });
    updateApproveButton();
}

// 4. Toggle collapsible form boxes (Schedule crossing, Add passenger, Log cargo)
document.querySelectorAll("[data-toggle-box]").forEach(function (btn) {
    btn.addEventListener("click", function () {
        var box = document.getElementById(btn.dataset.toggleBox);
        if (box) {
            box.classList.toggle("hidden");
            if (!box.classList.contains("hidden")) {
                var firstInput = box.querySelector("input:not([type=hidden]), select");
                if (firstInput) {
                    firstInput.focus();
                }
            }
        }
    });
});

// 5. QR Ticket preview modal for Manifest, Check-in, and Cargo
function drawPanelQr(box, codeText) {
    var size = 25;
    var code = codeText || "0000-00001";
    var seed = 1;
    for (var i = 0; i < code.length; i++) {
        seed = (seed * 31 + code.charCodeAt(i)) % 2147483647;
    }
    function nextRandom() {
        seed = (seed * 48271) % 2147483647;
        return seed;
    }
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

function escapeHtml(text) {
    var div = document.createElement("div");
    div.textContent = text || "";
    return div.innerHTML;
}

var ticketModal = document.getElementById("panelTicketModal");

document.querySelectorAll("[data-preview-ticket]").forEach(function (button) {
    button.addEventListener("click", function () {
        if (!ticketModal) return;

        var type = button.dataset.previewTicket;
        var titleEl = document.getElementById("ptTitle");
        var bodyEl = document.getElementById("ptBody");
        var totalWrap = document.getElementById("ptTotalWrap");
        var totalEl = document.getElementById("ptTotal");
        var noteEl = document.getElementById("ptNote");
        var qrEl = document.getElementById("ptQr");

        var ref = button.dataset.ref || "0000-00001";

        if (type === "cargo") {
            titleEl.textContent = "Cargo Delivery Ticket";
            bodyEl.innerHTML =
                "<dt>Cargo Tracking ID:</dt><dd>" + escapeHtml(ref) + "</dd>" +
                "<dt>Sender name:</dt><dd>" + escapeHtml(button.dataset.sender) + "</dd>" +
                "<dt>Sender contact number:</dt><dd>" + escapeHtml(button.dataset.senderContact) + "</dd>" +
                "<dt>Receiver name:</dt><dd>" + escapeHtml(button.dataset.receiver) + "</dd>" +
                "<dt>Receiver contact number:</dt><dd>" + escapeHtml(button.dataset.receiverContact) + "</dd>" +
                "<dt>Cargo name:</dt><dd>" + escapeHtml(button.dataset.items) + "</dd>" +
                "<dt>Cargo quantity:</dt><dd>" + escapeHtml(button.dataset.qty) + "</dd>" +
                "<dt>Lantsa name:</dt><dd>" + escapeHtml(button.dataset.lantsa) + "</dd>";
            totalWrap.classList.add("hidden");
            noteEl.textContent = "Please take a screenshot on this ticket for verification before delivery.";
        } else {
            titleEl.textContent = "Passenger Reservation Ticket";
            bodyEl.innerHTML =
                "<dt>Reservation reference code:</dt><dd>" + escapeHtml(ref) + "</dd>" +
                "<dt>Passenger full name:</dt><dd>" + escapeHtml(button.dataset.name) + "</dd>" +
                "<dt>Number of passengers:</dt><dd>" + escapeHtml(button.dataset.passengers) + "</dd>" +
                "<dt>Cargo quantity:</dt><dd>" + escapeHtml(button.dataset.cargo || "0") + "</dd>" +
                "<dt>Route and departure date/time:</dt><dd>" + escapeHtml(button.dataset.route) + "</dd>" +
                "<dt>Lantsa name:</dt><dd>" + escapeHtml(button.dataset.lantsa) + "</dd>" +
                "<dt>Contact Number:</dt><dd>" + escapeHtml(button.dataset.contact) + "</dd>";
            totalWrap.classList.remove("hidden");
            totalEl.textContent = button.dataset.total || "150";
            noteEl.textContent = "Please take a screenshot on this ticket for verification before boarding";
        }

        drawPanelQr(qrEl, ref);
        ticketModal.classList.add("open");
    });
});

document.querySelectorAll(".modal").forEach(function (modal) {
    modal.querySelectorAll("[data-close-modal]").forEach(function (closeBtn) {
        closeBtn.addEventListener("click", function () {
            modal.classList.remove("open");
        });
    });
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.classList.remove("open");
        }
    });
});

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        document.querySelectorAll(".modal.open").forEach(function (modal) {
            modal.classList.remove("open");
        });
    }
});

// 6. Document preview in Operator Approval Review
document.querySelectorAll("[data-doc-preview]").forEach(function (link) {
    link.addEventListener("click", function (event) {
        event.preventDefault();
        alert("Verified Document Preview:\n" + link.dataset.docPreview + "\n\nStatus: Verified by SURSEA Maritime Compliance");
    });
});

// 7. Dynamic Cargo Rows (Operator Cargo & Admin Cargo)-
var panelCargoRows = document.getElementById("cargoRows");
var panelCargoTemplate = document.getElementById("cargoTemplate");
var panelAddCargoBtn = document.getElementById("addCargo");

function updatePanelTrashButtons() {
    if (!panelCargoRows) return;
    panelCargoRows.classList.toggle("single", panelCargoRows.children.length === 1);
}

if (panelCargoRows && panelCargoTemplate && panelAddCargoBtn) {
    panelAddCargoBtn.addEventListener("click", function () {
        panelCargoRows.appendChild(panelCargoTemplate.content.cloneNode(true));
        updatePanelTrashButtons();
    });

    panelCargoRows.addEventListener("click", function (event) {
        var trash = event.target.closest("[data-remove-row]");
        if (trash && panelCargoRows.children.length > 1) {
            trash.closest(".cargo-row").remove();
            updatePanelTrashButtons();
        }
    });

    updatePanelTrashButtons();
}

// 8. QR Ticket Scanner (Operator Check-in & Admin Manifest)
var qrStream = null;
var startQrCamBtn = document.getElementById("startQrCamBtn");
var stopQrCamBtn = document.getElementById("stopQrCamBtn");
var qrVideo = document.getElementById("qrVideo");
var qrPlaceholder = document.getElementById("qrPlaceholder");
var qrImageInput = document.getElementById("qrImageInput");
var qrStatusBadge = document.getElementById("qrScannerStatusBadge");
var qrScannedInput = document.getElementById("qrScannedInput");
var qrTicketTypeSelect = document.getElementById("qrTicketTypeSelect");
var qrResultCode = document.getElementById("qrResultCode");
var qrResultCategory = document.getElementById("qrResultCategory");
var qrResultHolder = document.getElementById("qrResultHolder");
var qrResultRoute = document.getElementById("qrResultRoute");

function populateScannedQr(code, category, holder, route) {
    if (qrScannedInput) qrScannedInput.value = code;
    if (qrResultCode) qrResultCode.textContent = code;
    if (qrResultCategory) qrResultCategory.textContent = category || "Scanned QR Ticket";
    if (qrResultHolder) qrResultHolder.textContent = holder || ("Reference " + code);
    if (qrResultRoute) qrResultRoute.textContent = route || "Ready for port verification";
    if (qrStatusBadge) {
        qrStatusBadge.textContent = "QR Decoded: " + code;
        qrStatusBadge.className = "badge green";
    }
    if (qrTicketTypeSelect) {
        if (category && category.toLowerCase().indexOf("cargo") !== -1) {
            qrTicketTypeSelect.value = "cargo";
        } else if (category && category.toLowerCase().indexOf("waiting") !== -1) {
            qrTicketTypeSelect.value = "waitlist";
        } else {
            qrTicketTypeSelect.value = "passenger";
        }
    }
}

document.querySelectorAll("[data-simulate-qr]").forEach(function (btn) {
    btn.addEventListener("click", function () {
        populateScannedQr(
            btn.dataset.simulateQr,
            btn.dataset.qrCategory,
            btn.dataset.qrHolder,
            btn.dataset.qrRoute
        );
    });
});

document.querySelectorAll("[data-qr-type]").forEach(function (tab) {
    tab.addEventListener("click", function () {
        document.querySelectorAll("[data-qr-type]").forEach(function (other) {
            other.classList.toggle("active", other === tab);
        });
        var t = tab.dataset.qrType;
        if (t === "cargo") {
            populateScannedQr("0000-00111", "Cargo Delivery Ticket", "Robert Zabala → Earl Fermillan (9 Items)", "Dinagat → Surigao City · MV Isla Norte");
        } else if (t === "waitlist") {
            populateScannedQr("0000-00005", "Waiting List Ticket", "Pedro Lim (2 Passengers · Waitlist #1)", "San Jose → Surigao City · MV Marajaw");
        } else if (t === "passenger") {
            populateScannedQr("0000-00003", "Passenger Ticket", "Juan Reyes (3 Passengers · 2 Cargo)", "Surigao City → Dinagat · MV Isla Norte");
        } else if (qrTicketTypeSelect) {
            qrTicketTypeSelect.value = "auto";
        }
    });
});

if (startQrCamBtn && stopQrCamBtn && qrVideo) {
    startQrCamBtn.addEventListener("click", function () {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices
                .getUserMedia({ video: { facingMode: "environment" } })
                .then(function (stream) {
                    qrStream = stream;
                    qrVideo.srcObject = stream;
                    qrVideo.play();
                    qrVideo.classList.remove("hidden");
                    if (qrPlaceholder) qrPlaceholder.classList.add("hidden");
                    startQrCamBtn.classList.add("hidden");
                    stopQrCamBtn.classList.remove("hidden");
                    if (qrStatusBadge) {
                        qrStatusBadge.textContent = "Camera Scanning...";
                        qrStatusBadge.className = "badge blue";
                    }
                })
                .catch(function () {
                    populateScannedQr(
                        "0000-00003",
                        "Passenger Ticket (Simulated Camera Scan)",
                        "Juan Reyes (3 Passengers · 2 Cargo)",
                        "Surigao City → Dinagat · MV Isla Norte"
                    );
                });
        } else {
            populateScannedQr(
                "0000-00003",
                "Passenger Ticket (Simulated Camera Scan)",
                "Juan Reyes (3 Passengers · 2 Cargo)",
                "Surigao City → Dinagat · MV Isla Norte"
            );
        }
    });

    stopQrCamBtn.addEventListener("click", function () {
        if (qrStream) {
            qrStream.getTracks().forEach(function (track) {
                track.stop();
            });
            qrStream = null;
        }
        qrVideo.classList.add("hidden");
        if (qrPlaceholder) qrPlaceholder.classList.remove("hidden");
        stopQrCamBtn.classList.add("hidden");
        startQrCamBtn.classList.remove("hidden");
        if (qrStatusBadge) {
            qrStatusBadge.textContent = "Scanner Ready";
            qrStatusBadge.className = "badge teal";
        }
    });
}

if (qrImageInput) {
    qrImageInput.addEventListener("change", function () {
        if (qrImageInput.files && qrImageInput.files[0]) {
            var fileName = qrImageInput.files[0].name;
            var codeMatch = fileName.match(/0000-\d{5}/);
            var detectedCode = codeMatch ? codeMatch[0] : "0000-00003";
            populateScannedQr(
                detectedCode,
                "Uploaded QR Screenshot (" + fileName + ")",
                "Verified Ticket Holder",
                "Surigao City Port Terminal"
            );
        }
    });
}

// 9. Operator Edit Route / Edit Trip / Set Cargo Amount / Edit Cargo
document.querySelectorAll("[data-edit-op-route]").forEach(function (btn) {
    btn.addEventListener("click", function () {
        var box = document.getElementById("editOperatorRouteBox");
        if (!box) return;
        document.getElementById("edit_route_id").value = btn.dataset.id || "";
        document.getElementById("edit_route_name").value = btn.dataset.route || "";
        document.getElementById("edit_route_port").value = btn.dataset.port || "";
        document.getElementById("edit_route_fare").value = btn.dataset.fare || "";
        document.getElementById("edit_route_cargo_fee").value = btn.dataset.cargoFee || "";
        document.getElementById("edit_route_type").value = btn.dataset.type || "Passenger & Cargo";
        document.getElementById("edit_route_status").value = btn.dataset.status || "Active";
        box.classList.remove("hidden");
        box.scrollIntoView({ behavior: "smooth", block: "center" });
    });
});

document.querySelectorAll("[data-edit-op-trip]").forEach(function (btn) {
    btn.addEventListener("click", function () {
        var box = document.getElementById("opEditTripBox");
        if (!box) return;
        document.getElementById("edit_trip_id").value = btn.dataset.id || "";
        document.getElementById("edit_trip_route").value = btn.dataset.route || "";
        document.getElementById("edit_trip_port").value = btn.dataset.port || "";
        document.getElementById("edit_trip_departure").value = btn.dataset.departure || "";
        document.getElementById("edit_trip_capacity").value = btn.dataset.capacity || "40";
        document.getElementById("edit_trip_reserved").value = btn.dataset.reserved || "0";
        document.getElementById("edit_trip_status").value = btn.dataset.status || "Available";
        box.classList.remove("hidden");
        box.scrollIntoView({ behavior: "smooth", block: "center" });
    });
});

document.querySelectorAll("[data-set-cargo-amount]").forEach(function (btn) {
    btn.addEventListener("click", function () {
        var box = document.getElementById("opSetAmountCargoBox");
        if (!box) return;
        document.getElementById("amount_cargo_ref").value = btn.dataset.ref || "";
        document.getElementById("amount_cargo_display").value =
            (btn.dataset.ref || "") + " · " + (btn.dataset.sender || "");
        document.getElementById("amount_cargo_fee").value = btn.dataset.fee || "50";
        box.classList.remove("hidden");
        box.scrollIntoView({ behavior: "smooth", block: "center" });
    });
});

document.querySelectorAll("[data-edit-op-cargo]").forEach(function (btn) {
    btn.addEventListener("click", function () {
        var box = document.getElementById("opEditCargoBox");
        if (!box) return;
        document.getElementById("edit_cargo_ref").value = btn.dataset.ref || "";
        document.getElementById("edit_cargo_sender").value = btn.dataset.sender || "";
        document.getElementById("edit_cargo_sender_contact").value = btn.dataset.senderContact || "";
        document.getElementById("edit_cargo_receiver").value = btn.dataset.receiver || "";
        document.getElementById("edit_cargo_receiver_contact").value = btn.dataset.receiverContact || "";
        document.getElementById("edit_cargo_items").value = btn.dataset.items || "";
        document.getElementById("edit_cargo_qty").value = btn.dataset.qty || "1";
        document.getElementById("edit_cargo_fee").value = btn.dataset.fee || "25";
        document.getElementById("edit_cargo_status").value = btn.dataset.status || "Pending";
        box.classList.remove("hidden");
        box.scrollIntoView({ behavior: "smooth", block: "center" });
    });
});

// 10. Clickable Notification Rows (Admin & Operator)
document.querySelectorAll(".clickable-notif-row[data-href]").forEach(function (row) {
    row.addEventListener("click", function (event) {
        if (event.target.closest("button, a, form")) {
            return;
        }
        window.location.href = row.dataset.href;
    });
});

// 11. Module Switcher Tabs (Operator Profile, Admin Profile, Settings)
var panelTabs = document.querySelectorAll("[data-panel-tab]");

panelTabs.forEach(function (tab) {
    tab.addEventListener("click", function () {
        var targetId = tab.dataset.panelTab;
        panelTabs.forEach(function (other) {
            other.classList.toggle("active", other === tab);
        });
        document.querySelectorAll(".panel-tab-pane").forEach(function (pane) {
            pane.classList.toggle("active", pane.id === targetId);
        });
    });
});

// 12. Responsive Mobile Sidebar Drawer (Admin & Operator)
var sidebarToggleBtn = document.getElementById("sidebarToggle");
var sidebarCloseBtn = document.getElementById("sidebarClose");
var sidebarBackdropEl = document.getElementById("sidebarBackdrop");
var panelSidebarEl = document.getElementById("panelSidebar");

function setSidebarOpen(isOpen) {
    document.body.classList.toggle("sidebar-open", isOpen);
    if (sidebarToggleBtn) {
        sidebarToggleBtn.setAttribute("aria-expanded", isOpen ? "true" : "false");
    }
}

if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener("click", function () {
        setSidebarOpen(!document.body.classList.contains("sidebar-open"));
    });
}
if (sidebarCloseBtn) {
    sidebarCloseBtn.addEventListener("click", function () {
        setSidebarOpen(false);
    });
}
if (sidebarBackdropEl) {
    sidebarBackdropEl.addEventListener("click", function () {
        setSidebarOpen(false);
    });
}
if (panelSidebarEl) {
    panelSidebarEl.querySelectorAll("a.side-link").forEach(function (link) {
        link.addEventListener("click", function () {
            if (window.innerWidth <= 1000) {
                setSidebarOpen(false);
            }
        });
    });
}
document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && document.body.classList.contains("sidebar-open")) {
        setSidebarOpen(false);
    }
});
window.addEventListener("resize", function () {
    if (window.innerWidth > 1000 && document.body.classList.contains("sidebar-open")) {
        setSidebarOpen(false);
    }
});
