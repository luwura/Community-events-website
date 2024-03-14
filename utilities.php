<?php
include "database.php";
function add_user($conn, $name, $email, $password) {

    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);

    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully";
        //-------add a page telling you to sign in
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    //-----------add a new error page----------
    }
}

function get_events($conn){
    $events = array();
    $sql = "SELECT * FROM events";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    mysqli_free_result($result);
    //mysqli_close($conn);
    return $events;
}


function check_credentials($conn, $email, $password) {
    $sql = "SELECT email, password FROM users WHERE email = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $num_rows = mysqli_stmt_num_rows($stmt);
    mysqli_stmt_free_result($stmt);
    mysqli_stmt_close($stmt);
    //mysqli_close($conn);
    return $num_rows > 0;
}


function find_id_by_password($conn,$password){
    $query = "SELECT id FROM users WHERE password = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $password);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return isset($id) ? $id : null;
}


function search_event($conn, $name) {
    $events = array();
    $name = mysqli_real_escape_string($conn, $name);
    $sql = "SELECT * FROM events WHERE name LIKE '%$name%'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
        mysqli_free_result($result);
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    //mysqli_close($conn);

    return $events;
}

function search_event_date($conn, $date) {
    $events = array();
    $sql = "SELECT * FROM events WHERE event_date = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    return $events;
}

function get_id_of_user($conn, $email, $password){
    $id = null;
    $sql = "SELECT id FROM users WHERE email = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_free_result($stmt);
    mysqli_stmt_close($stmt);
    return $id;
}



function get_events_of_user($conn, $id) {
    $events = array();
    // Ensure $id is properly escaped to prevent SQL injection
    $id = mysqli_real_escape_string($conn, $id);
    $sql = "SELECT * FROM events WHERE user_id = '$id'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
        mysqli_free_result($result);
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    return $events;
}

function add_event($conn, $id, $name, $description, $upload_date, $event_date) {
    $sql = "INSERT INTO events (user_id, name, description, upload_date, event_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issss", $id, $name, $description, $upload_date, $event_date);
    $success = mysqli_stmt_execute($stmt);
    if ($success) {
        return true;
    } else {
        echo "Error: " . mysqli_error($conn);
        return false;
    }
    mysqli_stmt_close($stmt);
}

function delete_event($conn, $id){
    $id = mysqli_real_escape_string($conn, $id);
    $sql = "DELETE FROM events WHERE id = '$id'";
    if (mysqli_query($conn, $sql)) {
        echo "Event deleted successfully";
    } else {
        echo "Error deleting event: " . mysqli_error($conn);
    }
}

function update_event($conn, $id, $name, $description, $upload_date, $event_date) {
    $sql = "UPDATE events SET name=?, description=?, upload_date=?, event_date=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $name, $description, $upload_date, $event_date, $id);
    if (mysqli_stmt_execute($stmt)) {
        echo "Event updated successfully";
    } else {
        echo "Error updating event: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

function add_comment($conn, $user_id, $text, $upload_date, $event_id){
    $sql = "INSERT INTO comments (user_id, text, upload_date, event_id) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $text, $upload_date, $event_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "Comment added successfully";
    } else {
        echo "Error adding comment: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}


function find_user_name_by_id($conn, $user_id){
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $username);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $username;
}


function find_comments_of_event($conn, $event_id){
    $comments = array();

    // Prepare and execute the query
    $sql = "SELECT c.id, c.user_id, c.text, c.upload_date, u.name, c.event_id 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.event_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Fetch comments and store them in an array
    while ($row = mysqli_fetch_assoc($result)) {
        $comment = array(
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'name' => $row['name'],
            'text' => $row['text'],
            'upload_date' => $row['upload_date']
        );
        $comments[] = $comment;
    }

    // Close statement and return comments
    mysqli_stmt_close($stmt);
    return $comments;
}

?>