<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/pdf_basic.php';
require_login();
$pdo = db();
$u = current_user();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT t.*, trips.origin, trips.destination, trips.departure_ts, firms.name as firm_name
                       FROM tickets t 
                       JOIN trips ON trips.id=t.trip_id
                       JOIN firms ON firms.id=trips.firm_id
                       WHERE t.id=? AND t.user_id=?");
$stmt->execute([$id, $u['id']]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$ticket){ die("Bilet bulunamadı."); }

$title = "Bilet #" . $ticket['id'] . " - " . $ticket['firm_name'];
$lines = [
    "Yolcu: " . $u['name'],
    "Sefer: " . $ticket['origin'] . " -> " . $ticket['destination'],
    "Kalkış: " . ts_to_local((int)$ticket['departure_ts']),
    "Koltuk: #" . $ticket['seat_no'],
    "Durum: " . $ticket['status'],
    "Ödenen: " . number_format($ticket['price_paid']/100,2,',','.') . " TL",
    "Satın Alma: " . $ticket['purchased_at'],
];
$pdf = simple_pdf($title, $lines);
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="bilet-'.$ticket['id'].'.pdf"');
echo $pdf;
