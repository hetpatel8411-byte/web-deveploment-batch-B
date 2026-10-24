const form = document.querySelector("#registration-form");
const password = document.querySelector("#password");
const confirmPassword = document.querySelector("#confirm");
const csrfToken = document.querySelector("#csrf-token");
const submitButton = document.querySelector("#submit-registration");
const formStatus = document.querySelector("#form-status");

form.addEventListener("submit", (event) => {
	confirmPassword.setCustomValidity(password.value === confirmPassword.value ? "" : "Passwords do not match.");
	if (!form.reportValidity()) {
		event.preventDefault();
	}
});

form.addEventListener("input", () => {
	confirmPassword.setCustomValidity(password.value === confirmPassword.value ? "" : "Passwords do not match.");
});

fetch("csrf.php", { credentials: "same-origin", headers: { Accept: "application/json" } })
	.then((response) => {
		if (!response.ok) throw new Error("Could not prepare secure form.");
		return response.json();
	})
	.then((data) => {
		if (typeof data.csrf_token !== "string" || !data.csrf_token) {
			throw new Error("The server did not provide a security token.");
		}
		csrfToken.value = data.csrf_token;
		formStatus.setAttribute("role", "status");
		formStatus.textContent = "Secure form ready. Your information will be validated by the server.";
		submitButton.disabled = false;
	})
	.catch((error) => {
		formStatus.setAttribute("role", "alert");
		formStatus.replaceChildren(
			document.createTextNode(`${error.message} Registration saves only when served by PHP. Open `)
		);
		const serverLink = document.createElement("a");
		serverLink.href = "http://localhost/practical-8/register.html";
		serverLink.textContent = "http://localhost/practical-8/register.html";
		formStatus.append(serverLink, document.createTextNode(" after starting Apache in XAMPP."));
	});
