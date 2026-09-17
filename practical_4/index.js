var images = [
  "https://picsum.photos/id/180/1200/350",
  "https://picsum.photos/id/201/1200/350",
  "https://picsum.photos/id/24/1200/350"
];
var slideNumber = 0;
var slideImage = document.getElementById("slideImage");

document.getElementById("menuButton").onclick = function () {
  document.getElementById("mainMenu").classList.toggle("show");
};

document.getElementById("nextSlide").onclick = function () {
  slideNumber = (slideNumber + 1) % images.length;
  slideImage.src = images[slideNumber];
};

document.getElementById("previousSlide").onclick = function () {
  slideNumber = (slideNumber - 1 + images.length) % images.length;
  slideImage.src = images[slideNumber];
};

var questions = document.querySelectorAll(".question");
questions.forEach(function (question) {
  question.onclick = function () {
    question.nextElementSibling.classList.toggle("show");
  };
});

var popup = document.createElement("div");
popup.innerHTML = "<div style='position:fixed;top:30px;right:30px;padding:20px;background:white;border:1px solid #16324f;box-shadow:0 2px 8px #888'>Welcome to StudentHub! <button>Close</button></div>";
document.body.appendChild(popup);
popup.querySelector("button").onclick = function () { popup.remove(); };