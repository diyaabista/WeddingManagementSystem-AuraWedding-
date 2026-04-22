<?php
requireLogin();

// Only users can access this page
if ($_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Handle booking edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_booking'])) {
    $bid = intval($_POST['booking_id']);
    
    // Verify ownership
    $verify = $conn->prepare("SELECT wedding_date FROM bookings WHERE id=? AND user_id=?");
    $verify->bind_param("ii", $bid, $uid);
    $verify->execute();
    $booking = $verify->get_result()->fetch_assoc();
    
    if ($booking) {
        // Check if within 5 hours of wedding date
        $weddingTime = strtotime($booking['wedding_date']);
        $currentTime = time();
        $timeToEvent = ($weddingTime - $currentTime) / 3600; // Convert to hours
        
        if ($timeToEvent <= 5) {
            echo "<script>alert('Cannot edit booking within 5 hours of the wedding date!');</script>";
        } else {
            // Process the edit
            $bride = sanitize($conn, $_POST['bride_name'] ?? '');
            $groom = sanitize($conn, $_POST['groom_name'] ?? '');
            $email = sanitize($conn, $_POST['email'] ?? '');
            $phone = sanitize($conn, $_POST['phone'] ?? '');
            $wedding_date = sanitize($conn, $_POST['wedding_date'] ?? '');
            $venue = sanitize($conn, $_POST['venue'] ?? '');
            $guest_count = intval($_POST['guest_count'] ?? 0);
            $budget = floatval(str_replace(',', '', $_POST['budget'] ?? 0)) ?: null;
            $special_requests = sanitize($conn, $_POST['special_requests'] ?? '');
            $ceremonies = $_POST['ceremonies'] ?? [];
            $ceremony_str = implode(',', array_map(fn($c) => sanitize($conn, $c), $ceremonies));
            
            if ($bride && $groom && $email && $phone && $wedding_date && $ceremony_str) {
                $stmt = $conn->prepare("UPDATE bookings SET bride_name=?, groom_name=?, email=?, phone=?, wedding_date=?, ceremony_types=?, venue=?, guest_count=?, special_requests=?, budget=? WHERE id=?");
                $stmt->bind_param("sssssssisdi", $bride, $groom, $email, $phone, $wedding_date, $ceremony_str, $venue, $guest_count, $special_requests, $budget, $bid);
                
                if ($stmt->execute()) {
                    echo "<script>alert('Booking updated successfully!'); window.location.href='index.php?page=my-bookings';</script>";
                } else {
                    echo "<script>alert('Failed to update booking');</script>";
                }
            }
        }
    }
}

$bookings = $conn->query("SELECT b.*, p.name as package_name FROM bookings b LEFT JOIN packages p ON b.package_id=p.id WHERE b.user_id=$uid ORDER BY b.created_at DESC");
?>

<div class="page-header">
    <h1>📋 My Bookings</h1>
    <p>Track all your wedding booking requests</p>
</div>

<section class="section">
    <div style="max-width:1100px; margin:0 auto">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; flex-wrap:wrap; gap:12px">
            <h2 style="font-size:1.4rem; font-weight:700; color:var(--dark)">Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h2>
            <a href="index.php?page=booking" class="btn btn-primary">+ New Booking</a>
        </div>

        <?php if ($bookings && $bookings->num_rows > 0): ?>
        <div style="display:flex; flex-direction:column; gap:20px">
            <?php while ($b = $bookings->fetch_assoc()):
                $statusColors = ['pending'=>'#fff3cd,#856404','confirmed'=>'#d1e7dd,#0f5132','rejected'=>'#f8d7da,#842029','completed'=>'#cfe2ff,#084298'];
                [$bg, $clr] = explode(',', $statusColors[$b['status']] ?? '#fff,#000');
            ?>
            <div style="background:#fff; border-radius:18px; padding:24px 28px; box-shadow:0 4px 20px rgba(0,0,0,0.07)">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px">
                    <div>
                        <div style="font-size:0.85rem; color:#888">Booking Code</div>
                        <div style="font-size:1.2rem; font-weight:800; color:var(--pink-main); letter-spacing:1px"><?= $b['booking_code'] ?></div>
                    </div>
                    <span style="background:<?= $bg ?>; color:<?= $clr ?>; padding:6px 16px; border-radius:20px; font-size:0.85rem; font-weight:600; text-transform:uppercase">
                        <?= $b['status'] ?>
                    </span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-top:20px">
                    <div>
                        <div style="font-size:0.8rem; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px">Couple</div>
                        <div style="font-weight:600; color:var(--dark)"><?= htmlspecialchars($b['bride_name']) ?> & <?= htmlspecialchars($b['groom_name']) ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.8rem; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px">Wedding Date</div>
                        <div style="font-weight:600; color:var(--dark)"><?= date('d M Y', strtotime($b['wedding_date'])) ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.8rem; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px">Ceremonies</div>
                        <div><?php
                        $icons = ['sangeet'=>'🎵','mehendi'=>'🌿','haldi'=>'🌻','vivaha'=>'👰'];
                        foreach (explode(',', $b['ceremony_types']) as $c):
                            echo ($icons[trim($c)] ?? '💍') . ' ' . ucfirst(trim($c)) . ' ';
                        endforeach;
                        ?></div>
                    </div>
                    <?php if ($b['venue']): ?>
                    <div>
                        <div style="font-size:0.8rem; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px">Venue</div>
                        <div style="color:var(--dark)"><?= htmlspecialchars($b['venue']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($b['package_name']): ?>
                    <div>
                        <div style="font-size:0.8rem; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px">Package</div>
                        <div style="color:var(--dark)"><?= htmlspecialchars($b['package_name']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div>
                        <div style="font-size:0.8rem; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px">Submitted</div>
                        <div style="color:var(--dark)"><?= date('d M Y', strtotime($b['created_at'])) ?></div>
                    </div>
                </div>

                <?php if ($b['admin_notes']): ?>
                <div style="margin-top:16px; background:var(--pink-light); border-radius:10px; padding:14px 18px">
                    <strong style="font-size:0.85rem; color:var(--pink-main)">📝 Planner's Note:</strong>
                    <p style="font-size:0.9rem; color:var(--text); margin-top:4px"><?= htmlspecialchars($b['admin_notes']) ?></p>
                </div>
                <?php endif; ?>

                <?php
                // Fetch user designs for this booking
                $designs = null;
                try {
                    $designsQuery = $conn->prepare("SELECT * FROM user_designs WHERE booking_id=? ORDER BY created_at DESC");
                    if ($designsQuery) {
                        $designsQuery->bind_param("i", $b['id']);
                        $designsQuery->execute();
                        $designs = $designsQuery->get_result();
                    }
                } catch (Exception $e) {
                    $designs = null;
                }
                ?>

                <?php if ($designs && $designs->num_rows > 0): ?>
                <div style="margin-top:16px; background:#f0f8ff; border-radius:10px; padding:14px 18px">
                    <strong style="font-size:0.85rem; color:#084298">🎨 Your Custom Designs:</strong>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:12px; margin-top:12px">
                        <?php while ($design = $designs->fetch_assoc()): ?>
                        <div style="background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1)">
                            <img src="<?= htmlspecialchars($design['image_url']) ?>" alt="Design" style="width:100%; height:120px; object-fit:cover">
                            <div style="padding:8px">
                                <div style="font-size:0.75rem; color:#666; text-transform:capitalize"><?= $design['design_type'] ?></div>
                                <div style="font-size:0.75rem; color:#999"><?= ucfirst($design['ceremony_type']) ?></div>
                                <?php if ($design['description']): ?>
                                <div style="font-size:0.7rem; color:#999; margin-top:4px; max-height:40px; overflow:hidden"><?= htmlspecialchars($design['description']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($b['status'] === 'confirmed'): ?>
                <div style="margin-top:16px; background:#d1e7dd; border-radius:10px; padding:14px 18px">
                    <p style="font-size:0.9rem; color:#0f5132">✅ <strong>Booking Confirmed!</strong> Our planner will contact you at <?= htmlspecialchars($b['phone']) ?> to discuss the final details.</p>
                </div>
                <?php endif; ?>

                <?php 
                $weddingTime = strtotime($b['wedding_date']);
                $currentTime = time();
                $hoursToEvent = ($weddingTime - $currentTime) / 3600;
                $canEdit = $hoursToEvent > 5 && $b['status'] !== 'rejected' && $b['status'] !== 'completed';
                ?>

                <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap">
                    <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-primary" onclick="editBooking(<?= $b['id'] ?>)" style="flex:1; max-width:200px">✏️ Edit Booking</button>
                    <?php elseif ($hoursToEvent <= 5): ?>
                    <div style="background:#fff3cd; border:2px solid #ffc107; color:#856404; padding:10px 14px; border-radius:8px; flex:1; font-size:0.9rem">
                        ⏱️ Cannot edit - Within 5 hours of wedding
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Edit Booking Modal -->
            <div id="editModal<?= $b['id'] ?>" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; overflow-y:auto">
                <div style="background:#fff; margin:40px auto; border-radius:18px; max-width:800px; padding:40px">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px">
                        <h3 style="font-size:1.4rem; font-weight:700; color:var(--dark)">Edit Booking</h3>
                        <button onclick="closeModal(<?= $b['id'] ?>)" style="background:none; border:none; font-size:2rem; cursor:pointer; color:#888">&times;</button>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="edit_booking" value="1">
                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Bride's Name *</label>
                                <input type="text" name="bride_name" value="<?= htmlspecialchars($b['bride_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Groom's Name *</label>
                                <input type="text" name="groom_name" value="<?= htmlspecialchars($b['groom_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($b['email']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number *</label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($b['phone']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Wedding Date *</label>
                                <input type="date" name="wedding_date" value="<?= $b['wedding_date'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Guest Count</label>
                                <input type="number" name="guest_count" value="<?= $b['guest_count'] ?: '' ?>" min="1">
                            </div>
                            <div class="form-group full">
                                <label>Venue / Location</label>
                                <input type="text" name="venue" value="<?= htmlspecialchars($b['venue'] ?? '') ?>" placeholder="Banquet hall name or city">
                            </div>
                            <div class="form-group full">
                                <label>Ceremonies *</label>
                                <div class="checkbox-group">
                                    <?php
                                    $ceremonies_list = ['sangeet'=>'🎵 Sangeet','mehendi'=>'🌿 Mehendi','haldi'=>'🌻 Haldi','vivaha'=>'👰 Vivaha'];
                                    $current_ceremonies = explode(',', $b['ceremony_types']);
                                    foreach ($ceremonies_list as $val => $label):
                                    ?>
                                    <label>
                                        <input type="checkbox" name="ceremonies[]" value="<?= $val ?>" <?= in_array($val, array_map('trim', $current_ceremonies)) ? 'checked' : '' ?>>
                                        <?= $label ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Budget (₹)</label>
                                <input type="text" name="budget" value="<?= $b['budget'] ? number_format($b['budget']) : '' ?>" placeholder="e.g. 150000">
                            </div>
                            <div class="form-group full">
                                <label>Special Requests / Notes</label>
                                <textarea name="special_requests" rows="3" placeholder="Any special requirements..."><?= htmlspecialchars($b['special_requests'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div style="margin-top:20px; display:flex; gap:12px">
                            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                            <button type="button" onclick="closeModal(<?= $b['id'] ?>)" class="btn btn-outline">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            function editBooking(id) {
                document.getElementById('editModal' + id).style.display = 'block';
            }
            function closeModal(id) {
                document.getElementById('editModal' + id).style.display = 'none';
            }
            window.onclick = function(e) {
                if (e.target.id.startsWith('editModal')) {
                    e.target.style.display = 'none';
                }
            }
            </script>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:18px; box-shadow:0 4px 20px rgba(0,0,0,0.06)">
            <div style="font-size:4rem; margin-bottom:20px">📋</div>
            <h3 style="color:var(--dark); margin-bottom:12px">No Bookings Yet</h3>
            <p style="color:#888; margin-bottom:28px">Start planning your dream wedding today!</p>
            <a href="index.php?page=booking" class="btn btn-primary">Book Your Wedding →</a>
        </div>
        <?php endif; ?>
    </div>
</section>
