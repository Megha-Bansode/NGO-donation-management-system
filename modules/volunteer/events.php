<?php
// Volunteer Events Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Application - Volunteer Dashboard</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Volunteer CSS -->
    <link rel="stylesheet" href="../../assets/css/volunteer.css">
</head>
<body>
    <!-- Header -->
    <?php include '../../includes/header.php'; ?>
    
    <!-- Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> <span>Event Application</span>
        </div>

        <!-- Page Header -->
        <div class="dashboard-header-section">
            <div class="dashboard-titles">
                <h1>Event Application</h1>
                <p>Browse available NGO events and apply to participate.</p>
            </div>
        </div>

        <!-- Featured Event Banner -->
        <div class="featured-event">
            <div class="featured-image">
                <img src="../../assets/images/events/blood_donation.png" alt="Featured Event">
                <div class="featured-badge">Featured</div>
            </div>
            <div class="featured-content">
                <h2>City-wide Mega Blood Donation Drive</h2>
                <p>Join us in our biggest blood donation drive of the year. Help us save lives across multiple city hospitals.</p>
                <div class="featured-meta">
                    <span><i class="fas fa-calendar"></i> Nov 20, 2026</span>
                    <span><i class="fas fa-map-marker-alt"></i> Multiple Locations</span>
                </div>
                <button class="action-btn btn-primary" data-event-id="feat-1" data-category="Blood Donation" data-location="Mumbai"><i class="fas fa-hand-holding-medical"></i> Apply Now</button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-item search-filter">
                <i class="fas fa-search"></i>
                <input type="text" id="event-search" placeholder="Search events...">
            </div>
            <div class="filter-item">
                <select id="event-category">
                    <option value="all">All Categories</option>
                    <option value="Education">Education</option>
                    <option value="Health">Health</option>
                    <option value="Environment">Environment</option>
                    <option value="Food Distribution">Food Distribution</option>
                    <option value="Blood Donation">Blood Donation</option>
                    <option value="Fundraising">Fundraising</option>
                    <option value="Animal Welfare">Animal Welfare</option>
                </select>
            </div>
            <div class="filter-item">
                <select id="event-location">
                    <option value="all">All Locations</option>
                    <option value="Pune">Pune</option>
                    <option value="Mumbai">Mumbai</option>
                    <option value="Nashik">Nashik</option>
                    <option value="Nagpur">Nagpur</option>
                </select>
            </div>
            <div class="filter-item">
                <input type="date" id="event-date">
            </div>
            <div class="filter-item">
                <select id="event-status">
                    <option value="all">Any Availability</option>
                    <option value="open">Open</option>
                    <option value="closing">Closing Soon</option>
                    <option value="full">Full</option>
                </select>
            </div>
            <div class="filter-actions">
                <button id="filter-btn" class="action-btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <button id="reset-filter-btn" class="action-btn btn-outline"><i class="fas fa-undo"></i> Reset</button>
            </div>
        </div>

        <!-- Events Grid -->
        <div class="events-grid" id="events-grid">
            
            <!-- Event 1 -->
            <div class="event-card" data-category="Environment" data-location="Pune" data-status="open">
                <div class="event-banner">
                    <img src="../../assets/images/events/tree_plantation.png" alt="Tree Plantation">
                    <span class="badge badge-category">Environment</span>
                </div>
                <div class="event-body">
                    <h3>Green Earth Tree Plantation</h3>
                    <p class="event-desc">Help us plant 500 saplings to restore the local park's greenery.</p>
                    <div class="event-details">
                        <div class="detail-row"><i class="fas fa-calendar-day"></i> <span>Oct 15, 2026</span></div>
                        <div class="detail-row"><i class="fas fa-clock"></i> <span>09:00 AM - 02:00 PM</span></div>
                        <div class="detail-row"><i class="fas fa-map-marker-alt"></i> <span>Pune</span></div>
                        <div class="detail-row"><i class="fas fa-hourglass-half"></i> <span>Duration: 5 hours</span></div>
                        <div class="detail-row"><i class="fas fa-users"></i> <span>Volunteers Needed: 50</span></div>
                        <div class="detail-row"><i class="fas fa-user-friends"></i> <span>Seats: 45/50 (Open)</span></div>
                        <div class="detail-row"><i class="fas fa-user-tie"></i> <span>Org: NGO Green India</span></div>
                        <div class="detail-row"><i class="fas fa-laptop-house"></i> <span>Offline Event</span></div>
                    </div>
                    <div class="event-skills">
                        <span class="skill-tag">Plantation</span>
                        <span class="skill-tag">Physical Labor</span>
                    </div>
                </div>
                <div class="event-footer">
                    <span class="status-badge status-open">Open</span>
                    <button class="action-btn btn-primary apply-btn" data-event-id="1" data-category="Environment" data-location="Pune">Apply Now</button>
                </div>
            </div>

            <!-- Event 2 -->
            <div class="event-card" data-category="Food Distribution" data-location="Mumbai" data-status="closing">
                <div class="event-banner">
                    <img src="../../assets/images/events/food_drive.png" alt="Food Drive">
                    <span class="badge badge-category">Food Distribution</span>
                </div>
                <div class="event-body">
                    <h3>Downtown Food Drive</h3>
                    <p class="event-desc">Distribute fresh meals to homeless shelters in the downtown area.</p>
                    <div class="event-details">
                        <div class="detail-row"><i class="fas fa-calendar-day"></i> <span>Oct 18, 2026</span></div>
                        <div class="detail-row"><i class="fas fa-clock"></i> <span>12:00 PM - 04:00 PM</span></div>
                        <div class="detail-row"><i class="fas fa-map-marker-alt"></i> <span>Mumbai</span></div>
                        <div class="detail-row"><i class="fas fa-hourglass-half"></i> <span>Duration: 4 hours</span></div>
                        <div class="detail-row"><i class="fas fa-users"></i> <span>Volunteers Needed: 20</span></div>
                        <div class="detail-row"><i class="fas fa-user-friends"></i> <span>Seats: 18/20 (Closing Soon)</span></div>
                        <div class="detail-row"><i class="fas fa-user-tie"></i> <span>Org: Hope Foundation</span></div>
                        <div class="detail-row"><i class="fas fa-laptop-house"></i> <span>Offline Event</span></div>
                    </div>
                    <div class="event-skills">
                        <span class="skill-tag">Logistics</span>
                        <span class="skill-tag">Communication</span>
                    </div>
                </div>
                <div class="event-footer">
                    <span class="status-badge status-closing">Closing Soon</span>
                    <button class="action-btn btn-primary apply-btn" data-event-id="2" data-category="Food Distribution" data-location="Mumbai">Apply Now</button>
                </div>
            </div>

            <!-- Event 3 -->
            <div class="event-card" data-category="Health" data-location="Nashik" data-status="full">
                <div class="event-banner">
                    <img src="../../assets/images/events/medical_camp.png" alt="Medical Camp">
                    <span class="badge badge-category">Health</span>
                </div>
                <div class="event-body">
                    <h3>Rural Medical Checkup Camp</h3>
                    <p class="event-desc">Assist doctors and nurses with organizing patient files and managing queues.</p>
                    <div class="event-details">
                        <div class="detail-row"><i class="fas fa-calendar-day"></i> <span>Oct 25, 2026</span></div>
                        <div class="detail-row"><i class="fas fa-clock"></i> <span>10:00 AM - 05:00 PM</span></div>
                        <div class="detail-row"><i class="fas fa-map-marker-alt"></i> <span>Nashik</span></div>
                        <div class="detail-row"><i class="fas fa-hourglass-half"></i> <span>Duration: 7 hours</span></div>
                        <div class="detail-row"><i class="fas fa-users"></i> <span>Volunteers Needed: 30</span></div>
                        <div class="detail-row"><i class="fas fa-user-friends"></i> <span>Seats: 30/30 (Full)</span></div>
                        <div class="detail-row"><i class="fas fa-user-tie"></i> <span>Org: Care Medical NGO</span></div>
                        <div class="detail-row"><i class="fas fa-laptop-house"></i> <span>Offline Event</span></div>
                    </div>
                    <div class="event-skills">
                        <span class="skill-tag">Medical Support</span>
                        <span class="skill-tag">Admin</span>
                    </div>
                </div>
                <div class="event-footer">
                    <span class="status-badge status-full">Full</span>
                    <button class="action-btn btn-secondary apply-btn" disabled data-event-id="3" data-category="Health" data-location="Nashik">Registration Closed</button>
                </div>
            </div>

            <!-- Event 4 -->
            <div class="event-card" data-category="Education" data-location="Nagpur" data-status="open">
                <div class="event-banner">
                    <img src="../../assets/images/events/online_tutoring.png" alt="Tutoring">
                    <span class="badge badge-category">Education</span>
                </div>
                <div class="event-body">
                    <h3>Online Math Tutoring Session</h3>
                    <p class="event-desc">Teach basic mathematics to underprivileged children via Zoom.</p>
                    <div class="event-details">
                        <div class="detail-row"><i class="fas fa-calendar-day"></i> <span>Oct 28, 2026</span></div>
                        <div class="detail-row"><i class="fas fa-clock"></i> <span>05:00 PM - 07:00 PM</span></div>
                        <div class="detail-row"><i class="fas fa-map-marker-alt"></i> <span>Nagpur (Virtual)</span></div>
                        <div class="detail-row"><i class="fas fa-hourglass-half"></i> <span>Duration: 2 hours</span></div>
                        <div class="detail-row"><i class="fas fa-users"></i> <span>Volunteers Needed: 15</span></div>
                        <div class="detail-row"><i class="fas fa-user-friends"></i> <span>Seats: 5/15 (Open)</span></div>
                        <div class="detail-row"><i class="fas fa-user-tie"></i> <span>Org: Teach For All</span></div>
                        <div class="detail-row"><i class="fas fa-laptop-house"></i> <span>Online Event</span></div>
                    </div>
                    <div class="event-skills">
                        <span class="skill-tag">Teaching</span>
                        <span class="skill-tag">Mathematics</span>
                    </div>
                </div>
                <div class="event-footer">
                    <span class="status-badge status-open">Open</span>
                    <button class="action-btn btn-primary apply-btn" data-event-id="4" data-category="Education" data-location="Nagpur">Apply Now</button>
                </div>
            </div>

            <!-- Event 5 -->
            <div class="event-card" data-category="Animal Welfare" data-location="Pune" data-status="open">
                <div class="event-banner">
                    <img src="../../assets/images/events/animal_shelter.png" alt="Animal Shelter">
                    <span class="badge badge-category">Animal Welfare</span>
                </div>
                <div class="event-body">
                    <h3>Weekend Animal Shelter Help</h3>
                    <p class="event-desc">Help clean cages, walk dogs, and feed animals at the city shelter.</p>
                    <div class="event-details">
                        <div class="detail-row"><i class="fas fa-calendar-day"></i> <span>Nov 01, 2026</span></div>
                        <div class="detail-row"><i class="fas fa-clock"></i> <span>08:00 AM - 12:00 PM</span></div>
                        <div class="detail-row"><i class="fas fa-map-marker-alt"></i> <span>Pune</span></div>
                        <div class="detail-row"><i class="fas fa-hourglass-half"></i> <span>Duration: 4 hours</span></div>
                        <div class="detail-row"><i class="fas fa-users"></i> <span>Volunteers Needed: 25</span></div>
                        <div class="detail-row"><i class="fas fa-user-friends"></i> <span>Seats: 10/25 (Open)</span></div>
                        <div class="detail-row"><i class="fas fa-user-tie"></i> <span>Org: Paws Rescue</span></div>
                        <div class="detail-row"><i class="fas fa-laptop-house"></i> <span>Offline Event</span></div>
                    </div>
                    <div class="event-skills">
                        <span class="skill-tag">Animal Handling</span>
                    </div>
                </div>
                <div class="event-footer">
                    <span class="status-badge status-open">Open</span>
                    <button class="action-btn btn-primary apply-btn" data-event-id="5" data-category="Animal Welfare" data-location="Pune">Apply Now</button>
                </div>
            </div>

            <!-- Event 6 -->
            <div class="event-card" data-category="Fundraising" data-location="Mumbai" data-status="closing">
                <div class="event-banner">
                    <img src="../../assets/images/events/charity_gala.png" alt="Charity Gala">
                    <span class="badge badge-category">Fundraising</span>
                </div>
                <div class="event-body">
                    <h3>Annual Charity Gala Setup</h3>
                    <p class="event-desc">Assist with venue decoration and guest registration for our major fundraising gala.</p>
                    <div class="event-details">
                        <div class="detail-row"><i class="fas fa-calendar-day"></i> <span>Nov 10, 2026</span></div>
                        <div class="detail-row"><i class="fas fa-clock"></i> <span>04:00 PM - 11:00 PM</span></div>
                        <div class="detail-row"><i class="fas fa-map-marker-alt"></i> <span>Mumbai</span></div>
                        <div class="detail-row"><i class="fas fa-hourglass-half"></i> <span>Duration: 7 hours</span></div>
                        <div class="detail-row"><i class="fas fa-users"></i> <span>Volunteers Needed: 40</span></div>
                        <div class="detail-row"><i class="fas fa-user-friends"></i> <span>Seats: 38/40 (Closing Soon)</span></div>
                        <div class="detail-row"><i class="fas fa-user-tie"></i> <span>Org: Giving Hands</span></div>
                        <div class="detail-row"><i class="fas fa-laptop-house"></i> <span>Hybrid Event</span></div>
                    </div>
                    <div class="event-skills">
                        <span class="skill-tag">Event Mgt</span>
                        <span class="skill-tag">Hospitality</span>
                    </div>
                </div>
                <div class="event-footer">
                    <span class="status-badge status-closing">Closing Soon</span>
                    <button class="action-btn btn-primary apply-btn" data-event-id="6" data-category="Fundraising" data-location="Mumbai">Apply Now</button>
                </div>
            </div>

            <!-- Event 7 -->
            <div class="event-card" data-category="Environment" data-location="Nashik" data-status="open">
                <div class="event-banner">
                    <img src="../../assets/images/events/river_cleanup.png" alt="River Cleanup">
                    <span class="badge badge-category">Environment</span>
                </div>
                <div class="event-body">
                    <h3>Godavari River Cleanup</h3>
                    <p class="event-desc">Join the community effort to clean up plastic waste from the river banks.</p>
                    <div class="event-details">
                        <div class="detail-row"><i class="fas fa-calendar-day"></i> <span>Nov 15, 2026</span></div>
                        <div class="detail-row"><i class="fas fa-clock"></i> <span>07:00 AM - 11:00 AM</span></div>
                        <div class="detail-row"><i class="fas fa-map-marker-alt"></i> <span>Nashik</span></div>
                        <div class="detail-row"><i class="fas fa-hourglass-half"></i> <span>Duration: 4 hours</span></div>
                        <div class="detail-row"><i class="fas fa-users"></i> <span>Volunteers Needed: 100</span></div>
                        <div class="detail-row"><i class="fas fa-user-friends"></i> <span>Seats: 15/100 (Open)</span></div>
                        <div class="detail-row"><i class="fas fa-user-tie"></i> <span>Org: Eco Warriors</span></div>
                        <div class="detail-row"><i class="fas fa-laptop-house"></i> <span>Offline Event</span></div>
                    </div>
                    <div class="event-skills">
                        <span class="skill-tag">Physical Labor</span>
                        <span class="skill-tag">Teamwork</span>
                    </div>
                </div>
                <div class="event-footer">
                    <span class="status-badge status-open">Open</span>
                    <button class="action-btn btn-primary apply-btn" data-event-id="7" data-category="Environment" data-location="Nashik">Apply Now</button>
                </div>
            </div>

            <!-- Event 8 -->
            <div class="event-card" data-category="Education" data-location="Nagpur" data-status="full">
                <div class="event-banner">
                    <img src="../../assets/images/events/school_renovation.png" alt="School Renovation">
                    <span class="badge badge-category">Education</span>
                </div>
                <div class="event-body">
                    <h3>Village School Painting & Renovation</h3>
                    <p class="event-desc">Help us paint classrooms and repair desks at a local village school.</p>
                    <div class="event-details">
                        <div class="detail-row"><i class="fas fa-calendar-day"></i> <span>Nov 22, 2026</span></div>
                        <div class="detail-row"><i class="fas fa-clock"></i> <span>09:00 AM - 04:00 PM</span></div>
                        <div class="detail-row"><i class="fas fa-map-marker-alt"></i> <span>Nagpur</span></div>
                        <div class="detail-row"><i class="fas fa-hourglass-half"></i> <span>Duration: 7 hours</span></div>
                        <div class="detail-row"><i class="fas fa-users"></i> <span>Volunteers Needed: 25</span></div>
                        <div class="detail-row"><i class="fas fa-user-friends"></i> <span>Seats: 25/25 (Full)</span></div>
                        <div class="detail-row"><i class="fas fa-user-tie"></i> <span>Org: Build The Future</span></div>
                        <div class="detail-row"><i class="fas fa-laptop-house"></i> <span>Offline Event</span></div>
                    </div>
                    <div class="event-skills">
                        <span class="skill-tag">Painting</span>
                        <span class="skill-tag">Carpentry</span>
                    </div>
                </div>
                <div class="event-footer">
                    <span class="status-badge status-full">Full</span>
                    <button class="action-btn btn-secondary apply-btn" disabled data-event-id="8" data-category="Education" data-location="Nagpur">Registration Closed</button>
                </div>
            </div>

        </div>

        <!-- Empty State -->
        <div class="empty-state" id="empty-state" style="display: none;">
            <i class="fas fa-folder-open"></i>
            <h3>No events found.</h3>
            <p>Try adjusting your search or filter criteria to find more events.</p>
        </div>

    </main>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <!-- JS -->
    <script src="../../assets/js/volunteer.js"></script>
</body>
</html>
