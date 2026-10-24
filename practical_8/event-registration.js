const form = document.querySelector("#event-registration-form");
const studentSelect = document.querySelector("#student");
const eventSelect = document.querySelector("#event");
const csrfToken = document.querySelector("#csrf-token");
const submitButton = document.querySelector("#submit-registration");
const formStatus = document.querySelector("#form-status");

function setStatus(message, isError = false) {
  formStatus.textContent = message;
  formStatus.dataset.state = isError ? "error" : "";
  formStatus.setAttribute("role", isError ? "alert" : "status");
}

Promise.all([
  fetch("students.php", { headers: { Accept: "application/json" } }),
  fetch("events.php", { headers: { Accept: "application/json" } }),
  fetch("csrf.php", { credentials: "same-origin", headers: { Accept: "application/json" } })
])
  .then(async ([studentsResponse, eventsResponse, csrfResponse]) => {
    if (!studentsResponse.ok || !eventsResponse.ok || !csrfResponse.ok) {
      throw new Error("Students or events could not be loaded. Check that MySQL is running.");
    }
    const [students, events, token] = await Promise.all([
      studentsResponse.json(),
      eventsResponse.json(),
      csrfResponse.json()
    ]);
    if (!Array.isArray(students) || !Array.isArray(events)) {
      throw new Error("The server returned invalid student or event data.");
    }
    if (typeof token.csrf_token !== "string" || !token.csrf_token) {
      throw new Error("The secure form could not be prepared. Reload the page and try again.");
    }
    return { students, events, token };
  })
  .then(({ students, events, token }) => {
    csrfToken.value = token.csrf_token;

    studentSelect.replaceChildren(new Option("Choose a student", ""));
    students.forEach((student) => {
      studentSelect.add(new Option(`${student.name} (Student #${student.student_id})`, student.student_id));
    });

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const upcomingEvents = events.filter((event) => new Date(`${event.date}T00:00:00`) >= today);
    eventSelect.replaceChildren(new Option("Choose an upcoming event", ""));
    upcomingEvents.forEach((event) => {
      const date = new Date(`${event.date}T00:00:00`).toLocaleDateString();
      eventSelect.add(new Option(`${event.title} — ${date}`, event.event_id));
    });

    studentSelect.disabled = students.length === 0;
    eventSelect.disabled = upcomingEvents.length === 0;
    submitButton.disabled = students.length === 0 || upcomingEvents.length === 0;

    if (students.length === 0) {
      setStatus("No students are available. Create a student account first.", true);
    } else if (upcomingEvents.length === 0) {
      setStatus("There are no upcoming events available for registration.");
    } else {
      setStatus("Select a student and an upcoming event.");
    }
  })
  .catch((error) => {
    setStatus(error.message, true);
    studentSelect.replaceChildren(new Option("Students are unavailable", ""));
    eventSelect.replaceChildren(new Option("Events are unavailable", ""));
  });

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  if (!form.reportValidity()) return;
  submitButton.disabled = true;
  setStatus("Saving registration...");
  try {
    const response = await fetch(form.action, {
      method: "POST",
      body: new FormData(form),
      credentials: "same-origin",
      headers: { Accept: "application/json" }
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.message || "Registration could not be completed.");
    window.alert(result.message);
    setStatus(result.message);
    form.reset();
    studentSelect.selectedIndex = 0;
    eventSelect.selectedIndex = 0;
  } catch (error) {
    setStatus(error.message || "Registration could not be completed. Please try again.", true);
  } finally {
    submitButton.disabled = studentSelect.disabled || eventSelect.disabled;
  }
});
