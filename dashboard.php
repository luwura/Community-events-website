<?php
session_start();

include "utilities.php";

// Check if the user is authenticated
if (!isset($_SESSION["authenticated"]) || !$_SESSION["authenticated"]) {
    header("Location: login.php");
    exit;
}

// Fetch events
$events = get_events($conn);

// Handle event search
if (isset($_POST['search'])) {
    $search_keyword = $_POST['search'];
    $events = search_event($conn, $search_keyword);
}

// Handle date filter
if (isset($_POST['date_filter'])) {
    $filter_date = $_POST['date_filter'];
    $events = search_event_date($conn, $filter_date);
}

// Fetch events of the logged-in user
$user_id = $_SESSION["user_id"];
$user_events = get_events_of_user($conn, $user_id);

// Handle event upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $upload_date = date("Y-m-d"); // Current date
    $event_date = $_POST['event_date'];
    add_event($conn, $user_id, $name, $description, $upload_date, $event_date);
    header("Location: dashboard.php");
    exit;
}

// Handle comment upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_comment'])) {
    $event_id = $_POST['event_id'];
    $comment_text = $_POST['comment_text'];
    $comment_date = date("Y-m-d");
    add_comment($conn, $user_id, $comment_text, $comment_date, $event_id);
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];
    delete_event($conn, $event_id);
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_event'])) {
    // Retrieve event details from the form
    $event_id = $_POST['event_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $upload_date = $_POST['upload_date'];
    $event_date = $_POST['event_date'];
    
    // Update the event in the database
    update_event($conn, $event_id, $name, $description, $upload_date, $event_date);

    // Refresh the page to update event lists
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
        }
        .event {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .comment {
            margin-top: 10px;
            margin-bottom: 5px;
            padding: 5px;
            background-color: #d3d3d3;
            border-radius: 3px;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Dashboard!</h1>
        <p>This is the secure area of the website.</p>
        <p><a href="logout.php">Logout</a></p>

        <!-- Event upload form -->
        <h2>Upload Event</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea><br>
            <label for="event_date">Event Date:</label>
            <input type="date" id="event_date" name="event_date" required><br>
            <input type="submit" name="upload" value="Upload">
        </form>

        <!-- Event search form -->
        <h2>Search Events</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="text" name="search" placeholder="Search events by name">
            <input type="submit" value="Search">
        </form>

        <!-- Date filter form -->
        <h2>Filter Events by Date</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="text" name="date_filter" placeholder="Filter events by date (YYYY-MM-DD)">
            <input type="submit" value="Filter">
        </form>

        <!-- Display events -->
        <h2>Events</h2>
        <?php if (!empty($events)) : ?>
            <?php foreach ($events as $event) : ?>
                <div class="event">
                    <h3><?php echo $event['name']; ?></h3>
                    <p><?php echo $event['description']; ?></p>
                    <p>Uploaded: <?php echo $event['upload_date']; ?></p>
                    <p>Takes place: <?php echo $event['event_date']; ?></p>

                    <!-- Check if the user is ID 1 to allow editing and deletion -->
                    <?php if ($user_id == 1) : ?>
                        <!-- Edit and Delete options -->
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <input type="text" name="name" value="<?php echo $event['name']; ?>" placeholder="Event Name">
                            <textarea name="description" placeholder="Event Description"><?php echo $event['description']; ?></textarea>
                            <input type="date" name="event_date" value="<?php echo $event['event_date']; ?>" placeholder="Event Date">
                            <input type="submit" name="update_event" value="Update">
                            <!-- Delete button -->
                            <input type="submit" name="delete_event" value="Delete">
                        </form>
                    <?php endif; ?>

                    <!-- Comment section -->
                    <h4>Comments</h4>
                    <?php
                    $comments = find_comments_of_event($conn, $event['id']);
                    foreach ($comments as $comment) {
                        $user_name = find_user_name_by_id($conn, $comment['user_id']);
                        echo "<div class='comment'>";
                        echo "<p><strong>$user_name:</strong> " . $comment['text'] . "</p>";
                        echo "<p>Uploaded: " . $comment['upload_date'] . "</p>";
                        echo "</div>";
                    }
                    ?>

                    <!-- Comment form -->
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        <textarea name="comment_text" placeholder="Add a comment" required></textarea><br>
                        <input type="submit" name="upload_comment" value="Post Comment">
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No events found.</p>
        <?php endif; ?>

        <!-- Display user's events -->
        <h2>Your Events</h2>
        <?php if (!empty($user_events)) : ?>
            <?php foreach ($user_events as $user_event) : ?>
                <div class="event">
                    <h3><?php echo $user_event['name']; ?></h3>
                    <p><?php echo $user_event['description']; ?></p>
                    <p>Uploaded: <?php echo $user_event['upload_date']; ?></p>
                    <p>Takes place: <?php echo $user_event['event_date']; ?></p>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="event_id" value="<?php echo $user_event['id']; ?>">
                        <input type="text" name="name" value="<?php echo $user_event['name']; ?>" placeholder="Event Name">
                        <textarea name="description" placeholder="Event Description"><?php echo $user_event['description']; ?></textarea>
                        <input type="date" name="event_date" value="<?php echo $user_event['event_date']; ?>" placeholder="Event Date">
                        <input type="submit" name="update_event" value="Update">
                    </form>
                    <!-- Delete button -->
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="event_id" value="<?php echo $user_event['id']; ?>">
                        <input type="submit" name="delete_event" value="Delete">
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No events found for you.</p>
        <?php endif; ?>
    </div>
</body>
</html>
