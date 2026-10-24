
const form = document.getElementById("registration-form");
const tokenInput = document.getElementById("csrf-token");
const status = document.getElementById("form-status");
const button = document.getElementById("submit-registration");

fetch("csrf.php", {
  credentials: "same-origin",
  cache: "no-store"
})
  .then(response => {
    if (!response.ok) {
      throw new Error("Could not load security token");
    }
    return response.json();
  })
  .then(data => {
    if (!data.csrf_token) {
      throw new Error("Security token is missing");
    }

    tokenInput.value = data.csrf_token;
    status.textContent = "Secure form is ready.";
    button.disabled = false;
  })
  .catch(() => {
    status.textContent = "Security setup failed. Reload the page.";
    button.disabled = true;
  });

form.addEventListener("submit", function(event) {
  if (!tokenInput.value) {
    event.preventDefault();
    status.textContent = "Security token missing. Reload the page.";
  }
});
