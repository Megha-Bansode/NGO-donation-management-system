<?php
$pageTitle = "Arohan Foundation - Empowering Lives, Building Sustainable Futures";
include __DIR__ . '/../../includes/header.php';
?>

<!-- ==========================================
     HERO SECTION
     ========================================== -->
<section class="hero-section" id="home">
    <div class="container">
        <div class="row align-items-center g-5">
            <!-- Hero Text Area -->
            <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                <span class="section-tag mb-3">
                    <i class="fa-solid fa-heart-pulse text-success me-1"></i> Making a Difference Today
                </span>
                <h1 class="display-4 fw-extrabold text-dark mb-4 lh-sm">
                    Transforming Lives Through <span class="text-primary">Care</span>, <span class="text-success">Education</span> & Hope
                </h1>
                <p class="lead text-muted mb-4 fs-5" style="line-height: 1.7;">
                    Arohan Foundation is a dedicated non-profit organization combating poverty, extending healthcare outreach, providing quality education for children, and building resilient community infrastructure globally.
                </p>
                
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="#donate" class="btn btn-success btn-lg rounded-pill px-4 py-3 fw-bold text-white shadow-sm hover-lift d-inline-flex align-items-center">
                        <i class="fa-solid fa-heart me-2"></i> Donate Now
                    </a>
                    <a href="#volunteers" class="btn btn-outline-primary btn-lg rounded-pill px-4 py-3 fw-bold hover-lift d-inline-flex align-items-center">
                        <i class="fa-solid fa-user-plus me-2"></i> Join as Volunteer
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="d-flex align-items-center gap-4 pt-3 border-top border-light">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-success fs-4"></i>
                        <span class="small fw-semibold text-secondary">100% Tax Deductible & Audited</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-globe text-primary fs-4"></i>
                        <span class="small fw-semibold text-secondary">150+ Villages Reached</span>
                    </div>
                </div>
            </div>

            <!-- Hero Image / Card -->
            <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                <div class="position-relative">
                    <div class="hero-image-container rounded-4 overflow-hidden shadow-lg border border-white border-4 position-relative">
                        <img src="<?php echo $basePath; ?>assets/images/hero_ngo_community.jpg" alt="Arohan Foundation Community Support" class="img-fluid w-100 object-fit-cover" style="min-height: 420px; max-height: 520px;" onerror="this.src='https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1200&q=80'">
                        <div class="hero-image-overlay position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(15,23,42,0.85) 100%);">
                            <h4 class="text-white fw-bold mb-1">Empowering Rural Communities</h4>
                            <p class="text-white-50 mb-0 small"><i class="fa-solid fa-location-dot me-1 text-success"></i> Grassroots Initiatives in Remote Regions</p>
                        </div>
                    </div>

                    <!-- Floating Glassmorphism Badge -->
                    <div class="glass-card position-absolute top-0 end-0 translate-middle-y me-3 mt-4 p-3 d-none d-sm-flex align-items-center gap-3 shadow-lg" data-aos="zoom-in" data-aos-delay="400">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.4rem;">
                            <i class="fa-solid fa-hand-holding-heart"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">25,000+</h5>
                            <small class="text-muted fw-medium">Lives Impacted</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     ABOUT SECTION
     ========================================== -->
<section class="py-5 bg-white" id="about">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <!-- Image Side -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="position-relative">
                    <img src="<?php echo $basePath; ?>assets/images/about_ngo_mission.jpg" alt="Arohan Foundation Mission" class="img-fluid rounded-4 shadow-lg w-100" style="object-fit: cover; max-height: 480px;" onerror="this.src='https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=800&q=80'">
                    <div class="position-absolute bottom-0 start-0 m-4 p-3 bg-primary text-white rounded-3 shadow-lg d-flex align-items-center gap-3" style="max-width: 260px;">
                        <span class="display-6 fw-bold">14+</span>
                        <span class="small fw-medium lh-sm">Years of Transparent Community Service</span>
                    </div>
                </div>
            </div>

            <!-- Text Content -->
            <div class="col-lg-6" data-aos="fade-left">
                <span class="section-tag mb-3">ABOUT AROHAN FOUNDATION</span>
                <h2 class="display-6 fw-bold mb-4 text-dark">
                    Dedicated to Creating Sustainable Change & Bright Futures
                </h2>
                <p class="text-muted mb-4" style="font-size: 1.05rem; line-height: 1.7;">
                    Founded with a vision to eliminate social inequality, <strong>Arohan Foundation</strong> acts as a catalyst for grassroots transformation. We work directly with underserved families, providing educational infrastructure, emergency medical care, clean water solutions, and vocational training.
                </p>

                <!-- Core Pillars List -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-circle-check text-success fs-5 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">100% Financial Transparency</h6>
                                <p class="small text-muted mb-0">Every dollar donated is tracked and audited publicly.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-circle-check text-success fs-5 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Community-Led Growth</h6>
                                <p class="small text-muted mb-0">Empowering local leaders to manage village development.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-circle-check text-success fs-5 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Rapid Disaster Relief</h6>
                                <p class="small text-muted mb-0">Deploying emergency teams within 24 hours of crises.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-circle-check text-success fs-5 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Women & Youth Empowerment</h6>
                                <p class="small text-muted mb-0">Vocational training and digital literacy programs.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="#services" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                    Explore Our Programs <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     SERVICES SECTION (3 FEATURE CARDS)
     ========================================== -->
<section class="py-5 bg-light" id="services">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5" data-aos="fade-up">
            <span class="section-tag section-tag-blue mb-2">OUR CORE SERVICES</span>
            <h2 class="display-6 fw-bold text-dark">Comprehensive Support Programs</h2>
            <p class="text-muted">We provide targeted interventions across education, healthcare, and emergency response to build resilient communities.</p>
        </div>

        <div class="row g-4">
            <!-- Card 1: Child Education -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="service-card p-4 d-flex flex-column h-100">
                    <div class="hover-zoom rounded-3 mb-4 overflow-hidden" style="height: 200px;">
                        <img src="<?php echo $basePath; ?>assets/images/service_education.jpg" alt="Quality Education Program" class="w-100 h-100 object-fit-cover" onerror="this.src='https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="service-icon-wrapper mb-3">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Quality Child Education</h4>
                    <p class="text-muted flex-grow-1 fs-6">
                        Providing underprivileged children with digital classrooms, school supplies, uniforms, and merit scholarships for higher education.
                    </p>
                    <a href="#donate" class="fw-semibold text-primary text-decoration-none mt-3 d-inline-flex align-items-center">
                        Sponsor a Student <i class="fa-solid fa-chevron-right ms-2 small"></i>
                    </a>
                </div>
            </div>

            <!-- Card 2: Medical Outreach -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="service-card p-4 d-flex flex-column h-100">
                    <div class="hover-zoom rounded-3 mb-4 overflow-hidden" style="height: 200px;">
                        <img src="<?php echo $basePath; ?>assets/images/service_healthcare.jpg" alt="Healthcare Outreach Program" class="w-100 h-100 object-fit-cover" onerror="this.src='https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="service-icon-wrapper mb-3" style="background: linear-gradient(135deg, #d1fae5 0%, #e0f2fe 100%); color: var(--arohan-green);">
                        <i class="fa-solid fa-user-nurse"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Healthcare & Medical Relief</h4>
                    <p class="text-muted flex-grow-1 fs-6">
                        Operating mobile health clinics, organizing free diagnostic camps, eye checkups, and distributing maternal & child health kits.
                    </p>
                    <a href="#donate" class="fw-semibold text-success text-decoration-none mt-3 d-inline-flex align-items-center">
                        Support Medical Camps <i class="fa-solid fa-chevron-right ms-2 small"></i>
                    </a>
                </div>
            </div>

            <!-- Card 3: Emergency & Relief -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="service-card p-4 d-flex flex-column h-100">
                    <div class="hover-zoom rounded-3 mb-4 overflow-hidden" style="height: 200px;">
                        <img src="<?php echo $basePath; ?>assets/images/service_disaster.jpg" alt="Disaster Relief Program" class="w-100 h-100 object-fit-cover" onerror="this.src='https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="service-icon-wrapper mb-3" style="background: linear-gradient(135deg, #fef3c7 0%, #d1fae5 100%); color: #d97706;">
                        <i class="fa-solid fa-hand-holding-droplet"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Disaster Relief & Clean Water</h4>
                    <p class="text-muted flex-grow-1 fs-6">
                        Installing clean water filtration units, delivering food aid packages during natural disasters, and leading reforestation drives.
                    </p>
                    <a href="#donate" class="fw-semibold text-warning text-decoration-none mt-3 d-inline-flex align-items-center" style="color: #d97706 !important;">
                        Fund Relief Efforts <i class="fa-solid fa-chevron-right ms-2 small"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     ANIMATED IMPACT STATISTICS COUNTERS
     ========================================== -->
<section class="stats-banner py-5 text-white" id="impact-stats">
    <div class="container py-4">
        <div class="row g-4 text-center">
            <div class="col-lg-3 col-sm-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-box">
                    <i class="fa-solid fa-users text-success display-5 mb-3"></i>
                    <h2 class="display-5 fw-extrabold mb-1 text-white stat-counter" data-target="25000">0</h2>
                    <p class="text-white-50 fw-medium mb-0">Lives Impacted</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-box">
                    <i class="fa-solid fa-house-chimney-window text-info display-5 mb-3"></i>
                    <h2 class="display-5 fw-extrabold mb-1 text-white stat-counter" data-target="150">0</h2>
                    <p class="text-white-50 fw-medium mb-0">Villages Reached</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-box">
                    <i class="fa-solid fa-hand-holding-dollar text-warning display-5 mb-3"></i>
                    <h2 class="display-5 fw-extrabold mb-1 text-white stat-counter" data-target="4200000">0</h2>
                    <p class="text-white-50 fw-medium mb-0">$ Raised & Deployed</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6" data-aos="zoom-in" data-aos-delay="400">
                <div class="stat-box">
                    <i class="fa-solid fa-hands-holding-child text-success display-5 mb-3"></i>
                    <h2 class="display-5 fw-extrabold mb-1 text-white stat-counter" data-target="1800">0</h2>
                    <p class="text-white-50 fw-medium mb-0">Active Volunteers</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     GALLERY SECTION (6 PLACEHOLDER IMAGES)
     ========================================== -->
<section class="py-5 bg-white" id="gallery">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5" data-aos="fade-up">
            <span class="section-tag mb-2">OUR IMPACT IN PICTURES</span>
            <h2 class="display-6 fw-bold text-dark">Moments of Transformation</h2>
            <p class="text-muted">A visual glimpse into our ground initiatives, community smiles, and active campaigns.</p>
        </div>

        <div class="row g-4">
            <!-- Image 1 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="gallery-card">
                    <img src="<?php echo $basePath; ?>assets/images/gallery_1_clean_water.jpg" alt="Clean Water Project" onerror="this.src='https://images.unsplash.com/photo-1538300342682-cf57afb97285?auto=format&fit=crop&w=800&q=80'">
                    <div class="gallery-overlay">
                        <span class="badge bg-success w-auto mb-2 align-self-start">Infrastructure</span>
                        <h5 class="text-white fw-bold mb-1">Clean Water Well Installation</h5>
                        <p class="text-white-50 small mb-0">Providing safe drinking water to 500+ households.</p>
                    </div>
                </div>
            </div>

            <!-- Image 2 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="gallery-card">
                    <img src="<?php echo $basePath; ?>assets/images/gallery_2_school_kits.jpg" alt="School Kit Distribution" onerror="this.src='https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80'">
                    <div class="gallery-overlay">
                        <span class="badge bg-primary w-auto mb-2 align-self-start">Education</span>
                        <h5 class="text-white fw-bold mb-1">School Supplies & Digital Kits</h5>
                        <p class="text-white-50 small mb-0">Distributing backpacks and tablets to students.</p>
                    </div>
                </div>
            </div>

            <!-- Image 3 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="gallery-card">
                    <img src="<?php echo $basePath; ?>assets/images/gallery_3_medical_camp.jpg" alt="Free Medical Camp" onerror="this.src='https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80'">
                    <div class="gallery-overlay">
                        <span class="badge bg-info w-auto mb-2 align-self-start">Healthcare</span>
                        <h5 class="text-white fw-bold mb-1">Free Rural Health & Eye Camp</h5>
                        <p class="text-white-50 small mb-0">Free medical consultations for rural elders.</p>
                    </div>
                </div>
            </div>

            <!-- Image 4 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="gallery-card">
                    <img src="<?php echo $basePath; ?>assets/images/gallery_4_food_drive.jpg" alt="Food Distribution Drive" onerror="this.src='https://images.unsplash.com/photo-1593113646773-028c64a8f1b8?auto=format&fit=crop&w=800&q=80'">
                    <div class="gallery-overlay">
                        <span class="badge bg-warning text-dark w-auto mb-2 align-self-start">Nutrition</span>
                        <h5 class="text-white fw-bold mb-1">Emergency Food & Ration Drive</h5>
                        <p class="text-white-50 small mb-0">Supplying monthly ration kits to needy families.</p>
                    </div>
                </div>
            </div>

            <!-- Image 5 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="gallery-card">
                    <img src="<?php echo $basePath; ?>assets/images/gallery_5_tree_plantation.jpg" alt="Environmental Plantation" onerror="this.src='https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=800&q=80'">
                    <div class="gallery-overlay">
                        <span class="badge bg-success w-auto mb-2 align-self-start">Environment</span>
                        <h5 class="text-white fw-bold mb-1">Green Earth Plantation Drive</h5>
                        <p class="text-white-50 small mb-0">Planted 10,000+ saplings across green belts.</p>
                    </div>
                </div>
            </div>

            <!-- Image 6 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="gallery-card">
                    <img src="<?php echo $basePath; ?>assets/images/gallery_6_women_empowerment.jpg" alt="Women Skill Development" onerror="this.src='https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=800&q=80'">
                    <div class="gallery-overlay">
                        <span class="badge bg-danger w-auto mb-2 align-self-start">Empowerment</span>
                        <h5 class="text-white fw-bold mb-1">Women Skill & Tailoring Center</h5>
                        <p class="text-white-50 small mb-0">Training women for self-reliant livelihoods.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     DONATE & VOLUNTEERS INTERACTIVE SECTION
     ========================================== -->
<section class="py-5 bg-light" id="donate">
    <div class="container py-4">
        <div class="row g-5 align-items-center">
            <!-- Donation Card -->
            <div class="col-lg-7" data-aos="fade-right">
                <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                    <span class="section-tag mb-3">MAKE AN IMPACT TODAY</span>
                    <h2 class="fw-bold text-dark mb-3">Your Support Saves Lives</h2>
                    <p class="text-muted mb-4">Choose a contribution amount to directly sponsor education, healthcare, or disaster relief efforts.</p>

                    <!-- Amount Selection Pills -->
                    <div class="row g-2 mb-4" id="donateAmounts">
                        <div class="col">
                            <button type="button" class="btn btn-outline-primary w-100 py-3 fw-bold rounded-3 amount-btn" onclick="selectAmount(25, this)">$25</button>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-outline-primary w-100 py-3 fw-bold rounded-3 amount-btn active" onclick="selectAmount(50, this)">$50</button>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-outline-primary w-100 py-3 fw-bold rounded-3 amount-btn" onclick="selectAmount(100, this)">$100</button>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-outline-primary w-100 py-3 fw-bold rounded-3 amount-btn" onclick="selectAmount(500, this)">$500</button>
                        </div>
                    </div>

                    <!-- Custom Amount & Frequency -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light text-dark fw-bold">$</span>
                                <input type="number" id="customDonationAmount" class="form-control bg-light fs-5 fw-bold" placeholder="Custom Amount" value="50">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select form-select-lg bg-light text-dark fw-medium" id="donationFrequency">
                                <option value="one-time">One-time Donation</option>
                                <option value="monthly">Monthly Support</option>
                                <option value="annually">Annual Contribution</option>
                            </select>
                        </div>
                    </div>

                    <a href="<?php echo $basePath; ?>login" class="btn btn-success btn-lg rounded-pill py-3 fw-bold text-white shadow-sm hover-lift d-block text-center">
                        <i class="fa-solid fa-heart me-2"></i> Proceed to Secure Donation
                    </a>
                </div>
            </div>

            <!-- Volunteer Callout Card -->
            <div class="col-lg-5" id="volunteers" data-aos="fade-left">
                <div class="p-4 p-md-5 rounded-4 bg-primary text-white shadow-lg position-relative overflow-hidden">
                    <span class="badge bg-white text-primary mb-3 px-3 py-2 fw-bold uppercase">JOIN US AS A VOLUNTEER</span>
                    <h3 class="fw-bold mb-3">Become the Hands & Heart of Change</h3>
                    <p class="text-white-50 mb-4" style="line-height: 1.6;">
                        Join 1,800+ passionate volunteers making a real difference in teaching, medical camps, event management, and relief operations.
                    </p>

                    <ul class="list-unstyled mb-4">
                        <li class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-check-circle text-success fs-5 me-3 bg-white rounded-circle p-1"></i>
                            <span>Flexible Weekend & Virtual Roles</span>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-check-circle text-success fs-5 me-3 bg-white rounded-circle p-1"></i>
                            <span>Official Certificate of Volunteer Service</span>
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="fa-solid fa-check-circle text-success fs-5 me-3 bg-white rounded-circle p-1"></i>
                            <span>Direct Community Field Impact</span>
                        </li>
                    </ul>

                    <a href="<?php echo $basePath; ?>login" class="btn btn-light btn-lg text-primary rounded-pill fw-bold w-100 py-3 hover-lift">
                        <i class="fa-solid fa-user-plus me-2"></i> Register as Volunteer
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     LATEST EVENTS SECTION (3 CARDS)
     ========================================== -->
<section class="py-5 bg-white" id="events">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-5" data-aos="fade-up">
            <div>
                <span class="section-tag section-tag-blue mb-2">UPCOMING ACTIVITIES</span>
                <h2 class="display-6 fw-bold text-dark mb-0">Join Our Latest Events</h2>
            </div>
            <a href="#contact" class="btn btn-outline-primary rounded-pill px-4 mt-3 mt-md-0 fw-semibold">
                View Event Calendar <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>
        </div>

        <div class="row g-4">
            <!-- Event 1 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="event-card h-100 d-flex flex-column">
                    <div class="position-relative" style="height: 220px;">
                        <img src="<?php echo $basePath; ?>assets/images/event_1_gala.jpg" alt="Annual Charity Gala" class="w-100 h-100 object-fit-cover" onerror="this.src='https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=800&q=80'">
                        <div class="event-date-badge">
                            <span class="d-block fw-bold fs-5 text-primary lh-1">15</span>
                            <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">AUG 2026</small>
                        </div>
                    </div>
                    <div class="p-4 flex-grow-1 d-flex flex-column">
                        <span class="small text-muted mb-2"><i class="fa-solid fa-location-dot me-1 text-danger"></i> City Convention Center</span>
                        <h5 class="fw-bold text-dark mb-2">Annual Charity & Hope Gala 2026</h5>
                        <p class="text-muted small flex-grow-1">An evening dedicated to celebrating our donors, sharing annual impact reports, and launching new child welfare funds.</p>
                        <a href="#contact" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold mt-3 align-self-start">RSVP / Get Pass</a>
                    </div>
                </div>
            </div>

            <!-- Event 2 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="event-card h-100 d-flex flex-column">
                    <div class="position-relative" style="height: 220px;">
                        <img src="<?php echo $basePath; ?>assets/images/event_2_health_drive.jpg" alt="Health & Cleanliness Drive" class="w-100 h-100 object-fit-cover" onerror="this.src='https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80'">
                        <div class="event-date-badge">
                            <span class="d-block fw-bold fs-5 text-success lh-1">02</span>
                            <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">SEP 2026</small>
                        </div>
                    </div>
                    <div class="p-4 flex-grow-1 d-flex flex-column">
                        <span class="small text-muted mb-2"><i class="fa-solid fa-location-dot me-1 text-danger"></i> Rural Primary Health Hub</span>
                        <h5 class="fw-bold text-dark mb-2">Community Health & Hygiene Drive</h5>
                        <p class="text-muted small flex-grow-1">Volunteer doctors and nurses conducting free checkups, blood donation, and hygiene kit distribution.</p>
                        <a href="#contact" class="btn btn-outline-success btn-sm rounded-pill fw-semibold mt-3 align-self-start">Join Medical Team</a>
                    </div>
                </div>
            </div>

            <!-- Event 3 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="event-card h-100 d-flex flex-column">
                    <div class="position-relative" style="height: 220px;">
                        <img src="<?php echo $basePath; ?>assets/images/event_3_youth_workshop.jpg" alt="Youth Skill Workshop" class="w-100 h-100 object-fit-cover" onerror="this.src='https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=800&q=80'">
                        <div class="event-date-badge">
                            <span class="d-block fw-bold fs-5 text-warning lh-1" style="color: #d97706 !important;">20</span>
                            <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">SEP 2026</small>
                        </div>
                    </div>
                    <div class="p-4 flex-grow-1 d-flex flex-column">
                        <span class="small text-muted mb-2"><i class="fa-solid fa-location-dot me-1 text-danger"></i> Arohan Tech Center</span>
                        <h5 class="fw-bold text-dark mb-2">Youth Digital Literacy Workshop</h5>
                        <p class="text-muted small flex-grow-1">Interactive hands-on coding and computer skill development sessions for rural high school students.</p>
                        <a href="#contact" class="btn btn-outline-warning btn-sm rounded-pill fw-semibold mt-3 align-self-start" style="color: #d97706 !important; border-color: #d97706 !important;">Become Mentor</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     CONTACT SECTION
     ========================================== -->
<section class="py-5 bg-light" id="contact">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5" data-aos="fade-up">
            <span class="section-tag mb-2">GET IN TOUCH</span>
            <h2 class="display-6 fw-bold text-dark">We Would Love to Hear From You</h2>
            <p class="text-muted">Have questions regarding donations, partnerships, or volunteer opportunities? Reach out to our dedicated support team.</p>
        </div>

        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7" data-aos="fade-right">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <form id="contactForm" onsubmit="handleContactSubmit(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="contactName" class="form-label fw-semibold text-dark">Full Name *</label>
                                <input type="text" id="contactName" class="form-control py-2 shadow-none" placeholder="John Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contactEmail" class="form-label fw-semibold text-dark">Email Address *</label>
                                <input type="email" id="contactEmail" class="form-control py-2 shadow-none" placeholder="john@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contactPhone" class="form-label fw-semibold text-dark">Phone Number</label>
                                <input type="tel" id="contactPhone" class="form-control py-2 shadow-none" placeholder="+1 (555) 000-0000">
                            </div>
                            <div class="col-md-6">
                                <label for="contactRole" class="form-label fw-semibold text-dark">I am inquiring as</label>
                                <select id="contactRole" class="form-select py-2 shadow-none">
                                    <option value="donor">Individual Donor</option>
                                    <option value="volunteer">Prospective Volunteer</option>
                                    <option value="corporate">Corporate Partner / Sponsor</option>
                                    <option value="general">General Inquiry</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="contactSubject" class="form-label fw-semibold text-dark">Subject *</label>
                                <input type="text" id="contactSubject" class="form-control py-2 shadow-none" placeholder="How can we help?" required>
                            </div>
                            <div class="col-12">
                                <label for="contactMessage" class="form-label fw-semibold text-dark">Your Message *</label>
                                <textarea id="contactMessage" class="form-control shadow-none" rows="4" placeholder="Write your message here..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold hover-lift shadow-sm">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id="contactFormAlert" class="alert alert-success mt-3 d-none rounded-3" role="alert"></div>
                </div>
            </div>

            <!-- Contact Details -->
            <div class="col-lg-5" data-aos="fade-left">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Headquarters & Support</h5>
                        
                        <div class="d-flex align-items-start mb-4">
                            <div class="rounded-circle bg-light text-primary p-3 me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                <i class="fa-solid fa-location-dot fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Office Address</h6>
                                <p class="text-muted small mb-0">123 Arohan Towers, Main Boulevard, City Center</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-4">
                            <div class="rounded-circle bg-light text-success p-3 me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                <i class="fa-solid fa-phone fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Phone & Toll-Free</h6>
                                <p class="text-muted small mb-0">+1 (800) 555-AROHAN / +1 (555) 019-2834</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-4">
                            <div class="rounded-circle bg-light text-info p-3 me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                <i class="fa-solid fa-envelope fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Email Inquiry</h6>
                                <p class="text-muted small mb-0">contact@arohanfoundation.org</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-4">
                            <div class="rounded-circle bg-light text-warning p-3 me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                <i class="fa-solid fa-clock fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Working Hours</h6>
                                <p class="text-muted small mb-0">Monday – Saturday: 8:00 AM – 6:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-3">Connect With Us</h6>
                        <div class="d-flex gap-2">
                            <a href="https://facebook.com" target="_blank" class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="https://twitter.com" target="_blank" class="btn btn-outline-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                            <a href="https://instagram.com" target="_blank" class="btn btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                            <a href="https://linkedin.com" target="_blank" class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                            <a href="https://youtube.com" target="_blank" class="btn btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Inline Interactive Script for Donation & Contact Form -->
<script>
function selectAmount(amt, btn) {
    document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('customDonationAmount').value = amt;
}

function handleContactSubmit(e) {
    e.preventDefault();
    const alert = document.getElementById('contactFormAlert');
    alert.innerText = "Thank you for contacting Arohan Foundation! Our team will respond within 24 hours.";
    alert.classList.remove('d-none');
    document.getElementById('contactForm').reset();
    setTimeout(() => {
        alert.classList.add('d-none');
    }, 6000);
}
</script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
