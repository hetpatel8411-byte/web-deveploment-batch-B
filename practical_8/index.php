<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>StudentHub - Home</title>
  <link rel="stylesheet" href="index.css">
</head>
<body>
  <button class="menu-button" id="menuButton" type="button" aria-expanded="false" aria-controls="mainMenu">☰ Menu</button>
  <nav id="mainMenu" aria-label="Main navigation">
    <a href="index.php">Home</a>
    <a href="events.html">Events</a>
    <a href="studenthub.php">Event registrations</a>
    <a href="about.html">About</a>
    <a href="login.html">Login</a>
    <a href="register.html">Register</a>
    <a href="dashboard.html">Dashboard</a>
    <a href="courses.html">Courses</a>
    <a href="course-details.html">Course Details</a>
    <a href="assignment.html">Assignments</a>
    <a href="attendence.html">Attendance</a>
    <a href="notice.html">Notices</a>
  </nav>

  <header>
    <h1>Welcome to StudentHub</h1>
    <p>Explore campus events, register for activities, and find your student resources in one place.</p>
    <p><a href="events.html">Browse events</a> | <a href="event-registration.html">Register for an event</a></p>
  </header>

  <section class="slider" aria-label="Student life">
    <img id="slideImage" src="https://picsum.photos/id/180/1200/350" alt="Student studying">
    <button id="previousSlide" type="button" aria-label="Previous image">❮</button>
    <button id="nextSlide" type="button" aria-label="Next image">❯</button>
  </section>

  <h2>Frequently Asked Questions</h2>
  <div class="faq">
    <button class="question" type="button">How do I find upcoming events?</button>
    <p class="answer">Open the Events page to browse, search, and filter events.</p>
    <button class="question" type="button">How do I register for an event?</button>
    <p class="answer">Choose a student and an upcoming event on the Event Registration page.</p>
    <button class="question" type="button">Where can I see registration totals?</button>
    <p class="answer">Open the Event Registrations page for a summary by event.</p>
  </div>

  <hr>
  <footer>
    <p>2026 StudentHub. All rights reserved.</p>
  </footer>
  <script src="index.js"></script>
</body>
</html>
