var popup = document.createElement("div");
popup.innerHTML = "<div style='position:fixed;top:30px;right:30px;padding:20px;background:white;border:1px solid #16324f;box-shadow:0 2px 8px #888'>Please enter your login details. <button>Close</button></div>";
document.body.appendChild(popup);
popup.querySelector("button").onclick = function () { popup.remove(); };
