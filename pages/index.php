    <?php
    session_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>School Org System</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- AOS Animation CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">

        <!-- Custom CSS -->
        <link rel="stylesheet" href="/assets/css/index.css">

        <script src="/assets/js/script.js"></script>

    </head>

    <body>
        <div>
            <!-- Navigation -->
            <!-- Navigation -->
            <nav class="navbar navbar-expand-lg shadow-sm">
                <div class="container">
                    <a class="navbar-brand" href="index.php">
                        <img src="/assets/images/logo.png" alt="Logo" style="height: 40px;">
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                            <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                            <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
                            <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                            <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                            <li class="nav-item"><a class="btn btn-primary ms-2" href="login.php">Login</a></li>
                        </ul>
                    </div>
                </div>
            </nav>


            <!-- Hero Section -->
            <div class="container-fluid hero text-center" id="home">
                <div class="container py-5">
                    <h1 class="display-3 fw-bold" data-aos="fade-up"> <img src="../assets/images/logo1.png" style="height: 100px;">
                    </h1>
                    <h1 class="display-3 fw-bold" data-aos="fade-up">School Organization Management System</h1>
                    <p class="lead" data-aos="fade-up" data-aos-delay="200">A comprehensive platform for managing school clubs and organizations.</p>
                    <div class="d-flex justify-content-center gap-3 mt-4" data-aos="fade-up" data-aos-delay="400">
                        <a href="login.php" class="btn btn-lg btn-primary px-4">Get Started</a>
                        <a href="#features" class="btn btn-lg btn-outline-light px-4">Learn More</a>
                    </div>
                </div>
            </div>

            <div class="container text-center py-5 impact-section">
                <h2 class="fw-bold section-title" data-aos="fade-up">Our Impact</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="200">
                    Discover how our platform is empowering student organizations!
                </p>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="impact-box" data-aos="fade-up" data-aos-delay="300">
                            <h3 class="fw-bold text-primary counter" data-target="87">0</h3>
                            <p>Active Organizations</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="impact-box" data-aos="fade-up" data-aos-delay="400">
                            <h3 class="fw-bold text-success counter" data-target="5769">0</h3>
                            <p>Registered Students</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="impact-box" data-aos="fade-up" data-aos-delay="500">
                            <h3 class="fw-bold text-danger counter" data-target="566">0</h3>
                            <p>Successful Events</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="impact-box" data-aos="fade-up" data-aos-delay="600">
                            <h3 class="fw-bold text-warning counter" data-target="6442">0</h3>
                            <p>Event Participants</p>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Role Selection Cards -->
            <div class="container-fluid role-section text-white py-5" id="roles">
                <div class="container">
                    <div class="text-center mb-5">
                        <h1 class="fw-bold" data-aos="fade-up">Choose Your Role</h2>
                            <p class="text-muted text-light" data-aos="fade-up" data-aos-delay="200">
                                Our platform offers tailored experiences for different user roles
                            </p>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                            <div class="card h-100 shadow-sm card-hover">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-user-shield fs-1 text-danger mb-3"></i>
                                    <h3 class="card-title h5">Administrator</h3>
                                    <p class="card-text">Manage organizations, approve requests, and oversee user accounts</p>
                                    <a href="login.php?role=admin" class="btn btn-outline-danger">Login as Admin</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="400">
                            <div class="card h-100 shadow-sm card-hover">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-user-graduate fs-1 text-warning mb-3"></i>
                                    <h3 class="card-title h5">Student</h3>
                                    <p class="card-text">Browse and join organizations, track memberships, and view upcoming events</p>
                                    <a href="login.php?role=student" class="btn btn-outline-warning">Login as Student</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="600">
                            <div class="card h-100 shadow-sm card-hover">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-user-tie fs-1 text-success mb-3"></i>
                                    <h3 class="card-title h5">Organization Leader</h3>
                                    <p class="card-text">Manage members, schedule events, and update organization details</p>
                                    <a href="login.php?role=leader" class="btn btn-outline-success">Login as Leader</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About Section -->
            <div class="container py-5 " style="margin-top: 30px;" id="about">
                <div class="row align-items-center ">
                    <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                        <div id="aboutCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="../assets/images/aboutus1.png" class="d-block w-100 rounded-4">
                                </div>
                                <div class="carousel-item">
                                    <img src="../assets/images/aboutus2.png" class="d-block w-100 rounded-4">
                                </div>
                                <div class="carousel-item">
                                    <img src="../assets/images/aboutus3.png" class="d-block w-100 rounded-4">
                                </div>
                                <div class="carousel-item">
                                    <img src="../assets/images/aboutus4.png" class="d-block w-100 rounded-4">
                                </div>
                                <div class="carousel-item">
                                    <img src="../assets/images/aboutus5.png" class="d-block w-100 rounded-4">
                                </div>
                            </div>

                            <!-- Carousel Controls with Circular Buttons -->
                            <button class="carousel-control-prev custom-carousel-btn" type="button" data-bs-target="#aboutCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next custom-carousel-btn" type="button" data-bs-target="#aboutCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>

                            <!-- Carousel Indicators -->
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="0" class="active"></button>
                                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="1"></button>
                                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="2"></button>
                                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="3"></button>
                                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="4"></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 order-lg-1">
                        <h1 class="fw-bold mb-4">About UMUnity</h1>
                        <p class="lead mb-4">
                            UMUnity is a platform built to simplify and enhance the management of school organizations.
                            Designed with efficiency in mind, it bridges the gap between students, organization leaders, and administrators.
                        </p>

                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle">
                                    <i class="fas fa-users text-primary"></i>
                                </div>
                                <span>Streamlined organization registration and approval process</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="icon-circle">
                                    <i class="fas fa-user-check text-primary"></i>
                                </div>
                                <span>Efficient member management and communication</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="icon-circle">
                                    <i class="fas fa-calendar-alt text-primary"></i>
                                </div>
                                <span>Comprehensive event planning and attendance tracking</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="bg-white py-5" id="features">
                <div class="container py-5">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold" data-aos="fade-up">Key Features</h2>
                        <p class="text-muted" data-aos="fade-up" data-aos-delay="200">Discover what makes our platform powerful and easy to use</p>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                            <div class="card border-0 h-100 shadow-lg feature-card">
                                <div class="card-body text-center p-4">
                                    <div class="icon-container bg-primary bg-opacity-10">
                                        <i class="fas fa-users-cog fs-3 text-primary"></i>
                                    </div>
                                    <h3 class="card-title h5 mb-3">Role-Based Access</h3>
                                    <p class="card-text">Personalized dashboards and features for administrators, students, and organization leaders</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                            <div class="card border-0 h-100 shadow-lg feature-card">
                                <div class="card-body text-center p-4">
                                    <div class="icon-container bg-success bg-opacity-10">
                                        <i class="fas fa-sitemap fs-3 text-success"></i>
                                    </div>
                                    <h3 class="card-title h5 mb-3">Organization Management</h3>
                                    <p class="card-text">Create, join, and manage school clubs and organizations with ease</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                            <div class="card border-0 h-100 shadow-lg feature-card">
                                <div class="card-body text-center p-4">
                                    <div class="icon-container bg-info bg-opacity-10">
                                        <i class="fas fa-calendar-alt fs-3 text-info"></i>
                                    </div>
                                    <h3 class="card-title h5 mb-3">Event Scheduling</h3>
                                    <p class="card-text">Plan and manage events, track attendance, and send notifications</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="600">
                            <div class="card border-0 h-100 shadow-lg feature-card">
                                <div class="card-body text-center p-4">
                                    <div class="icon-container bg-warning bg-opacity-10">
                                        <i class="fas fa-bell fs-3 text-warning"></i>
                                    </div>
                                    <h3 class="card-title h5 mb-3">Real-time Notifications</h3>
                                    <p class="card-text">Stay updated with real-time notifications for important events and actions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="700">
                            <div class="card border-0 h-100 shadow-lg feature-card">
                                <div class="card-body text-center p-4">
                                    <div class="icon-container bg-danger bg-opacity-10">
                                        <i class="fas fa-chart-bar fs-3 text-danger"></i>
                                    </div>
                                    <h3 class="card-title h5 mb-3">Analytics Dashboard</h3>
                                    <p class="card-text">Visualize organization growth, event attendance, and member engagement</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="800">
                            <div class="card border-0 h-100 shadow-lg feature-card">
                                <div class="card-body text-center p-4">
                                    <div class="icon-container bg-success bg-opacity-10">
                                        <i class="fas fa-check-circle fs-3 text-success"></i>
                                    </div>
                                    <h3 class="card-title h5 mb-3">Attendance Tracking</h3>
                                    <p class="card-text">Easily track event attendance with automated check-ins and reports.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <!-- Success Stories & Testimonials -->
            <div class="testimonial-section py-5" id="testimonials">
                <div class="container">
                    <h2 class="fw-bold text-center text-light" data-aos="fade-up">Success Stories</h2>
                    <p class="text-center text-light opacity-75 mb-4" data-aos="fade-up" data-aos-delay="200">
                        Hear from students and organization leaders using UMUnity
                    </p>

                    <div id="testimonialCarousel" class="carousel slide carousel-dark" data-bs-ride="carousel">
                        <!-- Dots/Indicators -->
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="1"></button>
                            <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="2"></button>
                            <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="3"></button>
                            <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="4"></button>
                            <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="5"></button>
                        </div>

                        <!-- Carousel Inner -->
                        <div class="carousel-inner text-center">
                            <div class="carousel-item active">
                                <div class="testimonial-card mx-auto">
                                    <img src="/assets/images/testimonial1.png" class="testimonial-image" alt="John Doe">
                                    <p>"Joining clubs has never been easier! <strong>UMUnity</strong> helped me find the perfect organization for my interests."</p>
                                    <h5 class="text-warning">- John Doe, Student</h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-card mx-auto">
                                    <img src="/assets/images/testimonial2.png" class="testimonial-image" alt="Jane Smith">
                                    <p>"As an organization leader, <strong>UMUnity</strong> streamlined our event planning and member tracking!"</p>
                                    <h5 class="text-warning">- Jane Smith, Organization Leader</h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-card mx-auto">
                                    <img src="/assets/images/testimonial3.png" class="testimonial-image" alt="Mark Johnson">
                                    <p>"Managing multiple organizations has never been this easy! <strong>UMUnity</strong> makes everything accessible in one place."</p>
                                    <h5 class="text-warning">- Mark Johnson, Administrator</h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-card mx-auto">
                                    <img src="/assets/images/testimonial4.png" class="testimonial-image" alt="Emily Carter">
                                    <p>"I used to struggle with finding events, but now <strong>UMUnity</strong> keeps me updated in real-time!"</p>
                                    <h5 class="text-warning">- Emily Carter, Student</h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-card mx-auto">
                                    <img src="/assets/images/testimonial5.png" class="testimonial-image" alt="David Lee">
                                    <p>"Our organization gained more visibility and engagement thanks to <strong>UMUnity</strong>. Highly recommend it!"</p>
                                    <h5 class="text-warning">- David Lee, Club President</h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-card mx-auto">
                                    <img src="/assets/images/testimonial6.png" class="testimonial-image" alt="Sarah Miller">
                                    <p>"The event scheduling and attendance tracking features are game changers. <strong>UMUnity</strong> makes our lives easier!"</p>
                                    <h5 class="text-warning">- Sarah Miller, Event Coordinator</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Carousel Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!--  Frequently Asked Questions (FAQ) -->
            <div class="container py-5" id="faq">
                <h2 class="fw-bold text-center">Frequently Asked Questions</h2>
                <p class="text-center text-muted">Find answers to the most common questions about UMUnity.</p>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I join an organization?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Simply sign up, browse organizations, and click "Join" to send a membership request.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Can I manage multiple organizations?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, if you are assigned as a leader of multiple organizations, you can manage them under your dashboard.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How can I create a new organization?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Organization creation must be approved by an administrator. Submit a request through your dashboard, and an admin will review and approve it.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Is there a limit to the number of organizations I can join?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                No, you can join as many organizations as you like! However, each organization may have its own approval process.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                How do I know when an event is happening?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                All upcoming events will be listed on the Events page. You can also RSVP to events and receive notifications and reminders.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                Can I cancel my membership in an organization?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, you can leave an organization at any time from your profile page under "My Organizations."
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                How can I contact the organization leaders?
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Each organization page has a "Contact" section where you can message the leaders or find their contact details.
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Contact Section -->

            <div class="contact-parallax" id="contact">
                <div class="container py-5">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 text-center">
                            <h2 class="fw-bold mb-4">Contact Us</h2>
                            <p class="text-muted mb-5">Have questions or need assistance? Reach out to our team!</p>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-lg-6">
                            <form id="contact-form" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <div class="invalid-feedback">Please enter your name.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                    <div class="invalid-feedback">Please enter a subject.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                    <div class="invalid-feedback">Please enter your message.</div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Bootstrap JS & AOS Animation -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
            <script>
                AOS.init();
            </script>
    </body>
    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> SchoolOrgs. All rights reserved.</p>
    </footer>

    </html>