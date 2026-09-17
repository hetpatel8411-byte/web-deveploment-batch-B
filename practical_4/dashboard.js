document.getElementById("menuButton").onclick = function () {
  document.getElementById("quickLinks").classList.toggle("show");
};

var themeButton = document.getElementById("themeButton");

if (localStorage.getItem("theme") === "dark") {
  document.body.classList.add("dark");
  themeButton.textContent = "☀️ Light Mode";
}

themeButton.onclick = function () {
  document.body.classList.toggle("dark");

  if (document.body.classList.contains("dark")) {
    localStorage.setItem("theme", "dark");
    themeButton.textContent = "☀️ Light Mode";
  } else {
    localStorage.setItem("theme", "light");
    themeButton.textContent = "🌙 Dark Mode";
  }
};

var popup = document.createElement("div");
popup.innerHTML = "<div class='popup-box' style='position:fixed;top:30px;right:30px;padding:20px;border:1px solid #16324f;box-shadow:0 2px 8px #888'>Welcome to your dashboard! <button>Close</button></div>";
document.body.appendChild(popup);
popup.querySelector("button").onclick = function () { popup.remove(); };
