<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('invoices');
require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

// Wait for fpdf to be fully extracted
$fpdf_path = __DIR__ . '/includes/fpdf/fpdf.php';
if (!file_exists($fpdf_path)) {
    // Check if it's inside a nested folder (fpdf186/fpdf.php)
    $dirs = glob(__DIR__ . '/includes/fpdf/fpdf*', GLOB_ONLYDIR);
    if (!empty($dirs)) {
        $fpdf_path = $dirs[0] . '/fpdf.php';
    }
}
require_once $fpdf_path;

if (!isset($_GET['order_id'])) {
    die("Order ID is required.");
}

$order_id = (int)$_GET['order_id'];
$db = get_db_connection();

try {
    // Get Order Details
    $stmt = $db->prepare("
        SELECT o.*, u.full_name, u.email, u.phone, 
               p.payment_method, p.payment_status,
               inv.invoice_number, inv.generated_at
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        LEFT JOIN payments p ON o.order_id = p.order_id
        LEFT JOIN invoices inv ON o.order_id = inv.order_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order not found.");
    }

    // Generate Invoice Record if it doesn't exist
    if (empty($order['invoice_number'])) {
        $invoice_number = 'DISCORA-INV-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
        $stmt = $db->prepare("INSERT INTO invoices (order_id, invoice_number) VALUES (?, ?)");
        $stmt->execute([$order_id, $invoice_number]);
        $order['invoice_number'] = $invoice_number;
        $order['generated_at'] = date('Y-m-d H:i:s');
    }

    // Get Order Items
    $stmt = $db->prepare("
        SELECT oi.*, pr.product_name, pr.price as unit_price
        FROM order_items oi
        JOIN products pr ON oi.product_id = pr.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create PDF
    class PDF extends FPDF {
        function Header() {
            // Logo
            $this->SetFont('Arial', 'B', 20);
            $this->Cell(80, 10, 'DISCORA', 0, 0, 'L');
            $this->SetFont('Arial', 'I', 10);
            $this->Cell(0, 10, 'Your Ultimate Gaming Store', 0, 1, 'R');
            $this->Ln(5);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();

    // Invoice Info
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(100, 10, 'INVOICE', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(90, 5, 'Invoice Number: ' . $order['invoice_number'], 0, 1, 'R');
    $pdf->Cell(190, 5, 'Order Date: ' . date('F j, Y', strtotime($order['order_date'])), 0, 1, 'R');
    $pdf->Cell(190, 5, 'Invoice Date: ' . date('F j, Y', strtotime($order['generated_at'])), 0, 1, 'R');
    
    $pdf->Ln(10);

    // Customer Info
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(95, 6, 'Billed To:', 0, 0);
    $pdf->Cell(95, 6, 'Shipped To:', 0, 1);
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(95, 5, $order['full_name'], 0, 0);
    $pdf->Cell(95, 5, $order['full_name'], 0, 1);
    $pdf->Cell(95, 5, $order['email'], 0, 0);
    $pdf->Cell(95, 5, $order['shipping_address'], 0, 1);
    $pdf->Cell(95, 5, 'Phone: ' . $order['phone'], 0, 0);
    $pdf->Cell(95, 5, $order['shipping_city'] . ', ' . $order['shipping_postal_code'], 0, 1);

    $pdf->Ln(10);

    // Order Summary
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(20, 8, 'Item', 1, 0, 'C', true);
    $pdf->Cell(90, 8, 'Description', 1, 0, 'L', true);
    $pdf->Cell(25, 8, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Unit Price', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Total', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    $i = 1;
    foreach ($items as $item) {
        $pdf->Cell(20, 8, $i++, 1, 0, 'C');
        $pdf->Cell(90, 8, $item['product_name'], 1, 0, 'L');
        $pdf->Cell(25, 8, $item['quantity'], 1, 0, 'C');
        $pdf->Cell(25, 8, 'Rs. ' . number_format($item['unit_price'], 2), 1, 0, 'R');
        $pdf->Cell(30, 8, 'Rs. ' . number_format($item['unit_price'] * $item['quantity'], 2), 1, 1, 'R');
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(160, 8, 'Subtotal', 0, 0, 'R');
    $pdf->Cell(30, 8, 'Rs. ' . number_format($order['total_amount'], 2), 1, 1, 'R');
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(160, 10, 'Total Due', 0, 0, 'R');
    $pdf->Cell(30, 10, 'Rs. ' . number_format($order['total_amount'], 2), 1, 1, 'R');

    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->MultiCell(0, 5, "Payment Method: " . ($order['payment_method'] ?? 'N/A') . "\nPayment Status: " . ($order['payment_status'] ?? 'N/A') . "\n\nThank you for shopping with Discora!", 0, 'C');

    $pdf->Output('I', $order['invoice_number'] . '.pdf');

} catch (Exception $e) {
    die("Error generating PDF: " . $e->getMessage());
}
?>
