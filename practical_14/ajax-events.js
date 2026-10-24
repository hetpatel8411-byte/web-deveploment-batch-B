
const form = document.getElementById("eventForm");
const eventList = document.getElementById("eventList");
const message = document.getElementById("message");
const loading = document.getElementById("loading");

async function loadEvents() {
    loading.textContent = "Loading events...";

    try {
        const response = await fetch("api-events.php");
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Could not load events.");
        }

        eventList.innerHTML = "";

        result.data.forEach(event => {
            const row = document.createElement("tr");

            const values = [
                event.title,
                event.event_date,
                event.location,
                event.category,
                event.status
            ];

            values.forEach(value => {
                const cell = document.createElement("td");
                cell.textContent = value ?? "";
                row.appendChild(cell);
            });

            const actions = document.createElement("td");

            const editButton = document.createElement("button");
            editButton.textContent = "Edit";
            editButton.onclick = () => editEvent(event.id);

            const deleteButton = document.createElement("button");
            deleteButton.textContent = "Delete";
            deleteButton.onclick = () => deleteEvent(event.id);

            actions.appendChild(editButton);
            actions.appendChild(deleteButton);
            row.appendChild(actions);

            eventList.appendChild(row);
        });

        loading.textContent = result.data.length
            ? ""
            : "No events found.";
    } catch (error) {
        loading.textContent = error.message;
    }
}

form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const data = {
        id: document.getElementById("id").value,
        title: document.getElementById("title").value.trim(),
        description: document.getElementById("description").value.trim(),
        event_date: document.getElementById("event_date").value,
        location: document.getElementById("location").value.trim(),
        category: document.getElementById("category").value.trim(),
        status: document.getElementById("status").value
    };

    message.textContent = "Saving...";

    try {
        const response = await fetch("api-event-save.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Could not save event.");
        }

        message.textContent = result.message;
        form.reset();
        document.getElementById("id").value = "";

        await loadEvents();
    } catch (error) {
        message.textContent = error.message;
    }
});

async function editEvent(id) {
    try {
        const response = await fetch(
            "api-event-get.php?id=" + encodeURIComponent(id)
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Could not load event.");
        }

        const event = result.data;

        document.getElementById("id").value = event.id;
        document.getElementById("title").value = event.title;
        document.getElementById("description").value = event.description;
        document.getElementById("event_date").value = event.event_date;
        document.getElementById("location").value = event.location;
        document.getElementById("category").value = event.category;
        document.getElementById("status").value = event.status;

        message.textContent = "Editing event. Make your changes and save.";
        window.scrollTo({ top: 0, behavior: "smooth" });
    } catch (error) {
        message.textContent = error.message;
    }
}

async function deleteEvent(id) {
    if (!confirm("Are you sure you want to delete this event?")) {
        return;
    }

    message.textContent = "Deleting...";

    try {
        const response = await fetch("api-event-delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Could not delete event.");
        }

        message.textContent = result.message;
        await loadEvents();
    } catch (error) {
        message.textContent = error.message;
    }
}

document.getElementById("cancelButton").addEventListener("click", function () {
    form.reset();
    document.getElementById("id").value = "";
    message.textContent = "Edit cancelled.";
});

loadEvents();
