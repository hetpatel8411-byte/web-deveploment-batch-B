<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/session.php";

$events = [];
$totalRegistrations = 0;
$loadError = false;

try {
    $statement = get_db_connection()->query(
        "SELECT e.event_id, e.title, e.event_date, e.category, e.location,
                COUNT(CASE WHEN r.status = 'registered' THEN 1 END) AS registration_count
         FROM events e
         LEFT JOIN registrations r ON r.event_id = e.event_id
         GROUP BY e.event_id, e.title, e.event_date, e.category, e.location
         ORDER BY e.event_date, e.event_id"
    );
    $events = $statement->fetchAll();
    foreach ($events as $event) {
        $totalRegistrations += (int) $event["registration_count"];
    }
} catch (PDOException $exception) {
    error_log("StudentHub registration summary query failed: " . $exception->getMessage());
    $loadError = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Event Registration Summary - StudentHub</title>
  <link rel="stylesheet" href="events.css">
  <style>
    .summary { display: flex; flex-wrap: wrap; gap: 12px; margin: 20px 0; }
    .summary-card { flex: 1 1 180px; padding: 16px; background: #fff; border: 1px solid #dce5ea; }
    .summary-card strong { display: block; margin-top: 6px; font-size: 24px; }
    .event .event-count { color: #075d72; font-weight: 700; }
  </style>
</head>
<body>
  <nav aria-label="Main navigation">
    <a href="index.php">Home</a> |
    <a href="events.html">Events</a> |
    <a href="event-registration.html">Register for an event</a>
  </nav>
  <main>
    <h1>Event registration summary</h1>
    <p>Registration totals for StudentHub events. Student details are not shown here.</p>

    <?php if ($loadError): ?>
      <p role="alert">The summary could not be loaded. Check that MySQL is running and studenthub.sql has been imported.</p>
    <?php else: ?>
      <section class="summary" aria-label="Registration totals">
        <div class="summary-card">
          Events
          <strong><?= count($events) ?></strong>
        </div>
        <div class="summary-card">
          Active registrations
          <strong><?= $totalRegistrations ?></strong>
        </div>
      </section>

      <section class="events" aria-label="Registration counts by event">
        <?php if (!$events): ?>
          <p>No events are available yet.</p>
        <?php endif; ?>
        <?php foreach ($events as $event): ?>
          <?php $isUpcoming = $event["event_date"] >= date("Y-m-d"); ?>
          <article class="event">
            <h2><?= escape_html($event["title"]) ?></h2>
            <p><?= escape_html(date("F j, Y", strtotime($event["event_date"]))) ?> | <?= escape_html($event["category"]) ?> | <?= escape_html($event["location"]) ?></p>
            <p class="event-count"><?= (int) $event["registration_count"] ?> active registration<?= (int) $event["registration_count"] === 1 ? "" : "s" ?></p>
            <?php if ($isUpcoming): ?>
              <a href="event-registration.html?event=<?= (int) $event["event_id"] ?>">Register for this event</a>
            <?php else: ?>
              <p>Registration closed — this event has passed.</p>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
