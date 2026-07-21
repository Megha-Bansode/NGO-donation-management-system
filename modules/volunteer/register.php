<?php
// Volunteer Register Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Registration</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Volunteer CSS -->
    <link rel="stylesheet" href="../../assets/css/volunteer.css">
    
    <style>
        /* Form specific styles */
        .registration-form-container {
            background: var(--white);
            border-radius: var(--card-radius);
            box-shadow: var(--soft-shadow);
            padding: 32px;
            margin-top: 24px;
        }

        .form-section-title {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid #E2E8F0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 32px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-main);
        }

        .form-input-wrapper {
            position: relative;
        }

        .form-input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            outline: none;
            font-family: inherit;
            transition: border-color 0.2s;
            box-sizing: border-box;
            background-color: #F8FAFC;
        }
        
        .form-control.with-icon {
            padding-left: 40px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background-color: var(--white);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .radio-group {
            display: flex;
            gap: 16px;
            align-items: center;
            height: 100%;
        }

        .radio-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.95rem;
            color: var(--text-main);
            cursor: pointer;
        }

        .file-upload {
            border: 2px dashed #E2E8F0;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            background-color: #F8FAFC;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .file-upload:hover {
            border-color: var(--primary-color);
        }

        .file-upload input[type="file"] {
            display: none;
        }

        .file-upload i {
            font-size: 2rem;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #E2E8F0;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .registration-form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../../includes/header.php'; ?>
    
    <!-- Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-header-section">
            <div class="dashboard-titles">
                <h1>Volunteer Registration</h1>
                <p>Complete your profile to become an active NGO volunteer.</p>
            </div>
        </div>

        <div class="registration-form-container">
            <form id="volunteer-register-form" action="#" method="POST" enctype="multipart/form-data">
                
                <!-- Registration Success Message Container -->
                <div id="registration-success" style="display:none; background-color: #D1FAE5; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #34D399;">
                    <i class="fas fa-check-circle"></i> Registration successful! You can now log in.
                </div>
                
                <!-- Registration Error Message Container -->
                <div id="registration-error" style="display:none; background-color: #FEE2E2; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #F87171;">
                    <i class="fas fa-exclamation-circle"></i> <span id="registration-error-text">An error occurred.</span>
                </div>
                <!-- Personal Information -->
                <h3 class="form-section-title"><i class="fas fa-user"></i> Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-user-circle"></i>
                            <input type="text" name="first_name" class="form-control with-icon" placeholder="First name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-user-circle"></i>
                            <input type="text" name="last_name" class="form-control with-icon" placeholder="Last name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-control with-icon" placeholder="you@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-control with-icon" placeholder="Create a password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="phone" class="form-control with-icon" placeholder="+1 234 567 8900" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="date" name="date_of_birth" class="form-control with-icon">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="gender" value="male"> Male</label>
                            <label class="radio-label"><input type="radio" name="gender" value="female"> Female</label>
                            <label class="radio-label"><input type="radio" name="gender" value="other"> Other</label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Address</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" name="address" class="form-control with-icon" placeholder="Street Address">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">City</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-city"></i>
                            <input type="text" name="city" class="form-control with-icon" placeholder="City">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">State</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-map"></i>
                            <input type="text" name="state" class="form-control with-icon" placeholder="State">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">PIN Code</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-mail-bulk"></i>
                            <input type="text" name="pin" class="form-control with-icon" placeholder="PIN Code">
                        </div>
                    </div>
                </div>

                <!-- Volunteer Information -->
                <h3 class="form-section-title"><i class="fas fa-hands-helping"></i> Volunteer Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Skills (Multi-select) *</label>
                        <select name="skills[]" class="form-control" multiple required style="height: 100px;">
                            <option value="teaching">Teaching</option>
                            <option value="medical">Medical Assistance</option>
                            <option value="logistics">Logistics & Driving</option>
                            <option value="counseling">Counseling</option>
                            <option value="it">IT & Tech Support</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Areas of Interest</label>
                        <select class="form-control" multiple style="height: 100px;">
                            <option value="education">Education</option>
                            <option value="health">Health</option>
                            <option value="environment">Environment</option>
                            <option value="food">Food Distribution</option>
                            <option value="fundraising">Fundraising</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Previous Volunteering Experience</label>
                        <textarea class="form-control" placeholder="Briefly describe your previous experience..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Availability *</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-clock"></i>
                            <select name="availability" class="form-control with-icon" required>
                                <option value="">Select Availability</option>
                                <option value="weekdays">Weekdays</option>
                                <option value="weekends">Weekends</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Preferred Working Hours</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-hourglass-half"></i>
                            <input type="text" class="form-control with-icon" placeholder="e.g. 10 AM - 2 PM">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Emergency Contact Name</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-user-shield"></i>
                            <input type="text" class="form-control with-icon" placeholder="Contact Name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Emergency Contact Number</label>
                        <div class="form-input-wrapper">
                            <i class="fas fa-phone-alt"></i>
                            <input type="tel" class="form-control with-icon" placeholder="+1 234 567 8900">
                        </div>
                    </div>
                </div>

                <!-- Optional Documents -->
                <h3 class="form-section-title"><i class="fas fa-file-upload"></i> Optional Documents</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Upload Profile Photo</label>
                        <label class="file-upload">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to browse or drag and drop</p>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">JPG, PNG or GIF (Max. 2MB)</span>
                            <input type="file" accept="image/*">
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Upload ID Proof</label>
                        <label class="file-upload">
                            <i class="fas fa-id-card"></i>
                            <p>Click to browse or drag and drop</p>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">PDF, JPG or PNG (Max. 5MB)</span>
                            <input type="file">
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="reset" class="action-btn btn-outline"><i class="fas fa-undo"></i> Reset</button>
                    <button type="submit" class="action-btn btn-primary"><i class="fas fa-paper-plane"></i> Register</button>
                </div>

            </form>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <!-- JS -->
    <script src="../../assets/js/volunteer.js"></script>
</body>
</html>
