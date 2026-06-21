<?php
/**
 * Global Search API — Admin only
 * GET /api/search.php?q=keyword
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode(['bookings' => [], 'facilities' => [], 'users' => []]);
    exit;
}

$pdo    = getDB();
$search = '%' . $q . '%';
$limit  = 6;

// ── Search Bookings ──
$stmt = $pdo->prepare("
    SELECT b.id, b.booking_date, b.status,
           f.facility_name, u.full_name, u.email
    FROM bookings b
    JOIN facilities f ON b.facility_id = f.id
    JOIN users      u ON b.user_id     = u.id
    WHERE f.facility_name LIKE :s
       OR u.full_name     LIKE :s2
       OR u.email         LIKE :s3
       OR b.purpose       LIKE :s4
    ORDER BY b.created_at DESC
    LIMIT :lim
");
$stmt->bindValue(':s',   $search);
$stmt->bindValue(':s2',  $search);
$stmt->bindValue(':s3',  $search);
$stmt->bindValue(':s4',  $search);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Search Facilities ──
$stmt = $pdo->prepare("
    SELECT id, facility_name, facility_type, location, status
    FROM facilities
    WHERE facility_name LIKE :s
       OR location      LIKE :s2
       OR description   LIKE :s3
    ORDER BY facility_name ASC
    LIMIT :lim
");
$stmt->bindValue(':s',   $search);
$stmt->bindValue(':s2',  $search);
$stmt->bindValue(':s3',  $search);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Search Users ──
$stmt = $pdo->prepare("
    SELECT id, full_name, email, role, student_code
    FROM users
    WHERE full_name    LIKE :s
       OR email        LIKE :s2
       OR student_code LIKE :s3
    ORDER BY full_name ASC
    LIMIT :lim
");
$stmt->bindValue(':s',   $search);
$stmt->bindValue(':s2',  $search);
$stmt->bindValue(':s3',  $search);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'bookings'   => $bookings,
    'facilities' => $facilities,
    'users'      => $users,
]);
