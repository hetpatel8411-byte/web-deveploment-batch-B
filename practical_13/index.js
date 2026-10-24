var images = [
  "https://lh3.googleusercontent.com/drive-storage/AJQWtBMmchzUR1TjaTceWOpblgUDOGKgN7w0AZ6xJyGkCjzSfVqHsiSxiC7jDwtl_FLhY0Ex5ONbs4q7z7QtUbsblOkzaUNa3QpeYfQrFCmBFzH87cw-9g=w969-h913?auditContext=forDisplay",
  "https://lh3.googleusercontent.com/drive-storage/AJQWtBOx_h44fYY9t5OqcdDoECjhYERsIOClJxY850GxqBD68rQfv6gmX6VJwpJdCsGL56bN0Q0QI_i57mlXMWNv_bYnVb8Eu_zSxVepjSsuEm9B6cynhw=w969-h913?auditContext=forDisplay",
  "https://lh3.googleusercontent.com/drive-storage/AJQWtBNvzlW57DvZ7d8UZVcGy0Ab-SlRl9moBC6qZUruilGn5ZxqzlUULVRvZ3doZhpyhu1DC8bPiilufpOxcXOTpdCHTgtiZf9S3d2lhdutzoCUzDh9Fg=w969-h913?auditContext=forDisplay"
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