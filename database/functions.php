<?php

include_once "config.php";
require_once(__DIR__ . "/../includes/log_activity.php");


/**
 * Log user activity
 * @param int $user_id User ID
 * @param string $action Action performed
 * @param string $details Additional details
 * @return bool Success status
 */

function get_user_by_id($id)
{   
    global $conn;
    $result = $conn->query("SELECT * FROM users WHERE user_id = $id");
    return $result ? $result->fetch_assoc() : null;
}

function count_unread_notifications($user_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] ?? 0;
}

function get_recent_notifications($user_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY sent_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


function mark_notifications_as_read($user_id)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}


function get_organizations_by_status($status)
{
    global $conn;
    return $conn->query("SELECT * FROM organizations WHERE status = '$status'")->fetch_all(MYSQLI_ASSOC);
}

function format_date($date, $format = 'Y-m-d H:i:s')
{
    return date($format, strtotime($date));
}

/**
 * Get user by email
 * @param string $email User email
 * @return array|null User data or null if not found
 */
function getUserByEmail($email)
{
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

/**
 * Get all users with optional filters
 * @param array $filters Associative array of filters
 * @return array Users
 */
function getUsers($filters = [])
{
    global $conn;

    $sql = "SELECT u.user_id, u.full_name, u.email, u.role_id, u.status, 
           (SELECT GROUP_CONCAT(o.name SEPARATOR ', ') FROM organizations o 
            INNER JOIN user_organizations uo ON o.org_id = uo.organization_id 
            WHERE uo.user_id = u.user_id) as organizations 
           FROM users u WHERE 1=1";

    $types = "";
    $params = [];

    if (!empty($filters['search'])) {
        $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
        $types .= "ss";
        $search = "%{$filters['search']}%";
        $params[] = $search;
        $params[] = $search;
    }

    if (!empty($filters['role'])) {
        $sql .= " AND u.role_id = ?";
        $types .= "i";
        $params[] = $filters['role'];
    }

    if (!empty($filters['status'])) {
        $sql .= " AND u.status = ?";
        $types .= "s";
        $params[] = $filters['status'];
    }

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        // Convert role_id to role name
        $role_names = [
            1 => 'student',
            2 => 'officer',
            3 => 'admin'
        ];
        $row['role'] = isset($role_names[$row['role_id']]) ? $role_names[$row['role_id']] : 'unknown';

        $users[] = $row;
    }

    return $users;
}


/**
 * Get all organizations
 * @param string $status Filter by status (optional)
 * @return array Organizations
 */
function getOrganizations($status = null)
{
    global $conn;

    $sql = "SELECT o.*, u.full_name as leader_name, u.email as leader_email, 
           (SELECT COUNT(*) FROM user_organizations WHERE organization_id = o.org_id) as member_count 
           FROM organizations o 
           LEFT JOIN users u ON o.leader_id = u.user_id";  // FIXED COLUMN NAMES

    if ($status) {
        $sql .= " WHERE o.status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $status);
    } else {
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $organizations = [];
    while ($row = $result->fetch_assoc()) {
        $organizations[] = $row;
    }

    return $organizations;
}

/**
 * Get all events with optional filters
 * @param array $filters Associative array of filters
 * @return array Events
 */
function getEvents($filters = [])
{
    global $conn;

    $sql = "SELECT e.*, o.name as organization_name, u.name as created_by_name 
           FROM events e 
           LEFT JOIN organizations o ON e.organization_id = o.id 
           LEFT JOIN users u ON e.created_by = u.id 
           WHERE 1=1";

    $types = "";
    $params = [];

    if (!empty($filters['date'])) {
        $sql .= " AND DATE(e.date) = ?";
        $types .= "s";
        $params[] = $filters['date'];
    }

    if (!empty($filters['organization'])) {
        $sql .= " AND e.organization_id = ?";
        $types .= "i";
        $params[] = $filters['organization'];
    }

    if (!empty($filters['status'])) {
        $sql .= " AND e.status = ?";
        $types .= "s";
        $params[] = $filters['status'];
    }

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    return $events;
}

/**
 * Get activity logs with optional filters
 * @param array $filters Associative array of filters
 * @return array Activity logs
 */
function getActivityLogs($filters = [])
{
    global $conn;

    $sql = "SELECT l.*, u.name as user_name, u.email as user_email 
           FROM activity_logs l 
           LEFT JOIN users u ON l.user_id = u.id 
           WHERE 1=1";

    $types = "";
    $params = [];

    if (!empty($filters['user_id'])) {
        $sql .= " AND l.user_id = ?";
        $types .= "i";
        $params[] = $filters['user_id'];
    }

    if (!empty($filters['action'])) {
        $sql .= " AND l.action = ?";
        $types .= "s";
        $params[] = $filters['action'];
    }

    if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
        $sql .= " AND l.created_at BETWEEN ? AND ?";
        $types .= "ss";
        $params[] = $filters['date_from'] . ' 00:00:00';
        $params[] = $filters['date_to'] . ' 23:59:59';
    }

    $sql .= " ORDER BY l.created_at DESC";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    return $logs;
}

function getAnnouncements($conn, $user_id, $role)
{
    if ($role === 'admin') {
        $stmt = $conn->prepare("SELECT * FROM notifications ORDER BY sent_date DESC");
    } else {
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE sender_id = ? ORDER BY sent_date DESC");
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function sendAdminAnnouncement($conn, $message, $target_audience, $postData)
{
    if ($target_audience === 'all') {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, message, sent_date) SELECT user_id, ?, ?, NOW() FROM users WHERE role != 'admin'");
        $stmt->bind_param("is", $_SESSION['user_id'], $message);
        $stmt->execute();
    } elseif ($target_audience === 'organizations' && isset($postData['target_organizations'])) {
        foreach ($postData['target_organizations'] as $org_id) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, message, sent_date) SELECT user_id, ?, ?, NOW() FROM subscriptions WHERE org_id = ?");
            $stmt->bind_param("isi", $_SESSION['user_id'], $message, $org_id);
            $stmt->execute();
        }
    } elseif ($target_audience === 'roles' && isset($postData['target_roles'])) {
        foreach ($postData['target_roles'] as $role) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, message, sent_date) SELECT user_id, ?, ?, NOW() FROM users WHERE role = ?");
            $stmt->bind_param("iss", $_SESSION['user_id'], $message, $role);
            $stmt->execute();
        }
    }
}

function sendOrgLeaderAnnouncement($conn, $message, $user_id)
{
    $stmt = $conn->prepare("SELECT org_id FROM organizations WHERE leader_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $org_id = $row['org_id'];
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, message, sent_date) SELECT user_id, ?, ?, NOW() FROM subscriptions WHERE org_id = ?");
        $stmt->bind_param("isi", $user_id, $message, $org_id);
        $stmt->execute();
    }
}


function get_leader_organization($leader_id, $conn)
{
    $sql = "SELECT * FROM organizations WHERE leader_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $leader_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc(); // Return organization data
}


// Get total members in leader's organization (Based on membership)
function get_total_members($leader_id, $conn)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM membership 
        WHERE org_id = (SELECT org_id FROM membership WHERE user_id = ? AND role = 'officer')
    ");
    $stmt->bind_param("i", $leader_id);
    $stmt->execute();

    $count = 0;
    $stmt->bind_result($count);
    if (!$stmt->fetch()) {
        $count = 0;
    }

    $stmt->close();
    return $count;
}

// Get total events created by the leader's organization
function get_total_org_events($leader_id, $conn)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM events 
        WHERE org_id = (SELECT org_id FROM membership WHERE user_id = ? AND role = 'officer')
    ");
    $stmt->bind_param("i", $leader_id);
    $stmt->execute();

    $count = 0;
    $stmt->bind_result($count);
    if (!$stmt->fetch()) {
        $count = 0;
    }

    $stmt->close();
    return $count;
}

function get_all_org_events($leader_id, $conn)
{
    $sql = "SELECT e.* FROM events e
            JOIN organizations o ON e.org_id = o.org_id
            WHERE o.leader_id = ?
            ORDER BY e.date_time DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $leader_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    return $events;
}


function get_org_members($org_id, $conn)
{
    $stmt = $conn->prepare("
        SELECT m.membership_id, u.full_name, u.email, m.role, m.status 
        FROM membership m
        JOIN users u ON m.user_id = u.user_id
        WHERE m.org_id = ?
    ");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }

    $stmt->close();
    return $members;
}

function get_all_events($conn)
{
    $query = "SELECT id, event_name, event_date, status FROM events ORDER BY event_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_org_events($org_id, $conn)
{
    $stmt = $conn->prepare("
        SELECT event_id, title, description, date_time, venue, capacity, status 
        FROM events 
        WHERE org_id = ?
        ORDER BY date_time DESC
    ");

    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    $stmt->close();

    // Debugging: Print retrieved events
    if (empty($events)) {
        echo "No events found for Organization ID: $org_id.<br>";
    } else {
        echo "Retrieved " . count($events) . " events for Organization ID: $org_id.<br>";
    }

    return $events;
}


function get_user_organization_ids($user_id, $conn)
{
    $stmt = $conn->prepare("
        SELECT org_id FROM membership WHERE user_id = ? AND status = 'approved'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $org_ids = [];
    while ($row = $result->fetch_assoc()) {
        $org_ids[] = $row['org_id'];
    }

    $stmt->close();
    return $org_ids; // Returns an array of organization IDs
}


//student dashboard

function get_recent_org_events($student_id, $conn)
{
    $stmt = $conn->prepare("
        SELECT e.event_id, e.title, e.description, e.date_time, e.venue
        FROM events e
        JOIN membership m ON e.org_id = m.org_id
        WHERE m.user_id = ? 
        ORDER BY e.date_time DESC
        LIMIT 6
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    $stmt->close();
    return $events;
}


function get_recommended_organizations($student_id, $conn)
{
    $stmt = $conn->prepare("
        SELECT org_id, name, description, logo
        FROM organizations 
        WHERE status = 'approved'
        ORDER BY RAND()
        LIMIT 3
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    $recommended_orgs = [];
    while ($row = $result->fetch_assoc()) {
        $recommended_orgs[] = $row;
    }

    $stmt->close();
    return $recommended_orgs;
}

function get_user_events($student_id, $conn)
{
    $stmt = $conn->prepare("
        SELECT e.event_id, e.title, e.description, e.date_time, e.venue, e.capacity
        FROM events e
        JOIN membership m ON e.org_id = m.org_id
        WHERE m.user_id = ? AND e.date_time >= NOW()
        ORDER BY e.date_time ASC
        LIMIT 6
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    $stmt->close();
    return $events;
}

function get_user_organizations($student_id, $conn)
{
    $stmt = $conn->prepare("
        SELECT o.org_id, o.name, o.description, o.logo
        FROM organizations o
        JOIN membership m ON o.org_id = m.org_id
        WHERE m.user_id = ? AND m.status = 'approved'
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $organizations = [];
    while ($row = $result->fetch_assoc()) {
        $organizations[] = $row;
    }

    $stmt->close();
    return $organizations;
}
