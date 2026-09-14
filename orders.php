<?php
/**
 * Discora - Customer Order History Page
 */
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/functions.php';

require_login('login.php');

$page_title = "My Orders";
$user = current_user();
$db = get_db_connection();
$user_id = $_SESSION['user_id'];

// Fetch user orders
$stmt = $db->prepare("SELECT order_id, order_status, total_amount, order_date FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 20");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>
<style>
    body {
        background-color: #ffffff !important;
        color: #111827 !important;
    }
</style>

<div class="container my-5">
    <div class="row g-4">
        <!-- Account Sidebar -->
        <div class="col-md-4">
            <div class="card bg-white shadow-sm border-0 p-4 rounded-3 text-center">
                <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 80px; height: 80px; overflow: hidden;">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($user['avatar']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="bi bi-person fs-1"></i>
                    <?php endif; ?>
                </div>
                <h5 class="text-dark fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h5>
                <p class="text-muted small mb-3"><?= htmlspecialchars($user['email']) ?></p>
                <div class="list-group list-group-flush text-start">
                    <a href="<?= BASE_URL ?>account.php" class="list-group-item list-group-item-action bg-transparent text-dark border-bottom"><i class="bi bi-person me-2"></i>Profile Info</a>
                    <a href="<?= BASE_URL ?>orders.php" class="list-group-item list-group-item-action bg-transparent text-primary fw-bold border-bottom"><i class="bi bi-bag-check me-2"></i>My Orders</a>
                    <a href="<?= BASE_URL ?>wishlist.php" class="list-group-item list-group-item-action bg-transparent text-dark border-bottom"><i class="bi bi-heart me-2"></i>My Wishlist</a>
                    <a href="<?= BASE_URL ?>logout.php" class="list-group-item list-group-item-action bg-transparent text-danger border-0"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="col-md-8">
            <div class="card bg-white shadow-sm border-0 p-4 rounded-3">
                <h5 class="text-dark fw-bold mb-4">My Order History</h5>
                
                <?php if (empty($orders)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-box2 text-muted mb-3 d-block" style="font-size: 3rem;"></i>
                        <h5>No Orders Yet</h5>
                        <p class="text-muted">Looks like you haven't made any purchases yet.</p>
                        <a href="index.php" class="btn btn-outline-primary rounded-pill px-4 mt-2">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <?php
                                        $statusClass = match($order['order_status']) {
                                            'Pending' => 'bg-warning text-dark',
                                            'Processing' => 'bg-info text-dark',
                                            'Shipped' => 'bg-primary',
                                            'Delivered' => 'bg-success',
                                            'Cancelled' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                    <tr>
                                        <td class="fw-bold">#ORD-<?= htmlspecialchars($order['order_id']) ?></td>
                                        <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                        <td><span class="badge rounded-pill <?= $statusClass ?>"><?= htmlspecialchars($order['order_status']) ?></span></td>
                                        <td class="fw-bold"><?= format_price($order['total_amount']) ?></td>
                                        <td><a href="<?= BASE_URL ?>order-details.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
