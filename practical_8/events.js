const list = document.querySelector("#events");
const status = document.querySelector("#status");
const search = document.querySelector("#search");
const category = document.querySelector("#category");
const sort = document.querySelector("#sort");
const previous = document.querySelector("#previous");
const next = document.querySelector("#next");
const pageLabel = document.querySelector("#page");
const pageSize = 4;
let events = [];
let page = 0;

function render() {
  const query = search.value.trim().toLowerCase();
  const results = events.filter((event) =>
    (!category.value || event.category === category.value) &&
    `${event.title} ${event.location} ${event.description}`.toLowerCase().includes(query)
  );
  results.sort((a, b) => sort.value === "title"
    ? a.title.localeCompare(b.title)
    : sort.value === "date-desc"
      ? b.date.localeCompare(a.date)
      : a.date.localeCompare(b.date));

  const pages = Math.max(1, Math.ceil(results.length / pageSize));
  page = Math.min(page, pages - 1);
  list.replaceChildren();
  results.slice(page * pageSize, (page + 1) * pageSize).forEach((event) => {
    const article = document.createElement("article");
    article.className = "event";
    const title = document.createElement("h2");
    title.textContent = event.title;
    const details = document.createElement("p");
    details.textContent = `${new Date(`${event.date}T00:00:00`).toLocaleDateString()} | ${event.category} | ${event.location}`;
    const description = document.createElement("p");
    description.textContent = event.description;
    const registerLink = document.createElement("a");
    registerLink.className = "register-link";
    registerLink.href = `event-registration.html?event=${encodeURIComponent(event.event_id)}`;
    registerLink.textContent = "Register for this event";
    article.append(title, details, description, registerLink);
    list.append(article);
  });

  status.textContent = results.length ? `${results.length} event${results.length === 1 ? "" : "s"}` : "No events found.";
  pageLabel.textContent = `${page + 1} / ${pages}`;
  previous.disabled = page === 0;
  next.disabled = page >= pages - 1;
}

fetch("events.php")
  .then((response) => {
    if (!response.ok) throw new Error("Could not load events.");
    return response.json();
  })
  .then((data) => {
    events = data;
    [...new Set(events.map((event) => event.category))].sort().forEach((name) => {
      const option = document.createElement("option");
      option.value = name;
      option.textContent = name;
      category.append(option);
    });
    render();
  })
  .catch(() => { status.textContent = "Could not load events. Start MySQL in XAMPP and import studenthub.sql using phpMyAdmin."; });

[search, category, sort].forEach((control) => control.addEventListener("input", () => { page = 0; render(); }));
previous.addEventListener("click", () => { page--; render(); });
next.addEventListener("click", () => { page++; render(); });
