const form = document.querySelector("#registration-form");
const password = document.querySelector("#password");
const confirmPassword = document.querySelector("#confirm");

form.addEventListener("submit", (event) => {
	event.preventDefault();
	const checks = [
		["#name", /^(?=.{3,30}$)[A-Za-z][A-Za-z .'-]*$/, "Name must be 3-30 characters."],
		["#email", /^[^\s@]+@[^\s@]+\.[^\s@]+$/, "Enter a valid email."],
		["#mobile", /^\d{10}$/, "Enter a 10-digit mobile number."],
		["#address", /^(?=(?:[\s\S]*[A-Za-z]){10})[\s\S]+$/, "Address must contain at least 10 letters."],
		["#password", /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/, "Use 8+ characters with uppercase, lowercase, number, and special character."]
	];

	checks.forEach(([selector, regex, message]) => {
		const field = document.querySelector(selector);
		field.setCustomValidity(regex.test(field.value.trim()) ? "" : message);
	});
	confirmPassword.setCustomValidity(password.value === confirmPassword.value ? "" : "Passwords do not match.");

	if (form.reportValidity()) alert("Registration successful!");
});

form.addEventListener("input", () => {
	form.querySelectorAll("input, textarea").forEach((field) => field.setCustomValidity(""));
});
