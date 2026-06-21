<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Review.php';

requireLogin();
$bookingId    = intval($_GET['booking_id'] ?? 0);
$bookingModel = new Booking();
$reviewModel  = new Review();

$booking = $bookingModel->findById($bookingId);
if (!$booking || $booking['user_id'] != $_SESSION['user_id'] || $booking['status'] !== 'completed') {
    flashMessage('danger','You cannot review this booking.'); redirect(APP_URL.'/views/bookings/index.php');
}
if (!$reviewModel->canReview($bookingId, $_SESSION['user_id'])) {
    flashMessage('info','You have already reviewed this booking.'); redirect(APP_URL.'/views/bookings/view.php?id='.$bookingId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating  = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($rating < 1 || $rating > 5) { flashMessage('danger','Please select a rating 1–5.'); }
    else {
        $reviewModel->create(['booking_id'=>$bookingId,'user_id'=>$_SESSION['user_id'],'rating'=>$rating,'comment'=>$comment]);
        flashMessage('success','Thank you for your review!');
        redirect(APP_URL.'/views/bookings/view.php?id='.$bookingId);
    }
}
$pageTitle = 'Write a Review';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="app-shell">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="main-content" id="mainContent">
<?php include __DIR__ . '/../../includes/navbar.php'; ?>
<div class="page-content">

<div class="page-header">
  <div class="page-header-left">
    <h1><i class="fas fa-star me-2 text-warning"></i>Write a Review</h1>
  </div>
</div>

<?= displayFlash() ?>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <i class="fas fa-building me-2 text-primary"></i><?= sanitize($booking['facility_name']) ?>
        <span class="text-muted small ms-2"><?= formatDate($booking['booking_date'], 'd M Y') ?></span>
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="mb-4 text-center">
            <label class="form-label fw-bold mb-2">Overall Rating <span class="text-danger">*</span></label>
            <div class="star-rating justify-content-center" id="starRating">
              <?php for($i=5;$i>=1;$i--): ?>
              <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
              <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i>1?'s':'' ?>">★</label>
              <?php endfor; ?>
            </div>
            <div class="small text-muted mt-1" id="ratingLabel">Click to rate</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Your Comments (optional)</label>
            <textarea class="form-control" name="comment" rows="4"
                      placeholder="Share your experience — cleanliness, equipment quality, overall satisfaction…"></textarea>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning flex-1 text-white fw-bold">
              <i class="fas fa-paper-plane me-2"></i>Submit Review
            </button>
            <a href="<?= APP_URL ?>/views/bookings/view.php?id=<?= $bookingId ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
</div>
</div>
<script>
const APP_URL = "<?= APP_URL ?>";
const labels = ['','Poor','Fair','Good','Great','Excellent'];
document.querySelectorAll('input[name="rating"]').forEach(input => {
  input.addEventListener('change', () => {
    document.getElementById('ratingLabel').textContent = labels[input.value] + ' (' + input.value + '/5)';
  });
});
</script>
