<!doctype html>
<html lang="en">
    <head>
        <title>I-SMS</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        
        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="img/text.png">

        <!-- Bootstrap CSS -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
        
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        
        <!-- Custom CSS -->
        <link rel="stylesheet" href="style.css">
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    </head>

    <body>
        <!-- Header -->
        <header class="site-header" role="banner">
            <nav class="navbar navbar-expand-lg fixed-top" aria-label="Main navigation">
                <div class="container-fluid">
                    <!-- Brand -->
                    <a class="navbar-brand" href="#" aria-label="I-SMS Home">
                        <img class="ms-3 me-2 brand" src="img/text.png" alt="I-SMS Logo">
                        <b>I-SMS</b>
                    </a>
            
                    <!-- Mobile Navigation Toggle -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#navbarNav" aria-controls="navbarNav" 
                            aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
            
                    <!-- Navigation Links -->
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav mx-auto">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="#hero">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#features">Features</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#connect">Contact</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="login/login.php">Login</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Institution Logo -->
                    <a href="#" class="navbar-brand d-none d-lg-flex" aria-label="Institution Logo">
                        <img class="ms-2 me-3 red-spartan" src="img/redspartan-logo.png" alt="Institution Logo">
                    </a>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
      <!-- Hero Section -->
<section id="hero" class="hero-section min-vh-100 d-flex align-items-center py-5">
    <div class="container text-center px-4">
        <h1 class="display-4 fw-bold mb-4" data-aos="fade-down">
            Welcome to <span class="text-danger text-emphasis">ISMS</span>
        </h1>
        
        <p class="lead mb-5" data-aos="fade-up" data-aos-delay="200">
            Instant SMS notifications make campus communication more effective and efficient.
            <span class="d-block mt-2">
                Delivering timely updates to students when it matters most.
            </span>
        </p>

        
    </div>
</section>

<!-- Features Section -->
<section id="features" class="features-section min-vh-100 d-flex align-items-center py-5">
    <div class="container text-center px-4">
        <h2 class="display-5 fw-bold mb-5" data-aos="fade-down">
            Enhancing School 
            <span class="text-danger">Communication</span>
            <span class="d-block mt-3">Through Innovation</span>
        </h2>

        <div class="features-container d-flex flex-wrap justify-content-center gap-4 mt-4">
            <!-- Why ISMS -->
            <div class="feature-card d-flex justify-content-center align-items-center p-4" 
                 data-aos="zoom-in" data-aos-delay="200">
                <h3 class="display-6 mb-0">
                    Why Choose
                    <span class="text-danger d-block fw-bold">I-SMS?</span>
                </h3>
            </div>

            <!-- Feature Cards -->
            <div class="feature-card p-4" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-icon mb-4">
                    <i class="bi bi-lightning-charge-fill text-danger fs-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Efficiency</h4>
                <p class="text-muted">
                    Automate announcement distribution to save time and reduce manual errors
                </p>
            </div>

            <div class="feature-card p-4" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-icon mb-4">
                    <i class="bi bi-shield-check text-danger fs-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Reliability</h4>
                <p class="text-muted">
                    Ensure prompt and consistent message delivery to all university members
                </p>
            </div>

            <div class="feature-card p-4" data-aos="fade-up" data-aos-delay="800">
                <div class="feature-icon mb-4">
                    <i class="bi bi-person-check text-danger fs-1"></i>
                </div>
                <h4 class="fw-bold mb-3">User-Friendly</h4>
                <p class="text-muted">
                    Intuitive interface for seamless information management and access
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Connect With Us Section -->
<section id="connect" class="connect-with-us-section min-vh-100 d-flex align-items-center py-5">
    <div class="container px-4">
        <div class="row justify-content-center align-items-center gy-4">
            <div class="col-md-6" data-aos="fade-right">
                <div class="img-container">
                    <img class="image img-fluid rounded" src="img/phone.jpg" alt="Mobile phone displaying I-SMS interface">
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <div class="description-container">
                    <h2 class="display-6 mb-4 fw-bold">We Make <span class="text-danger">Communication</span> Faster</h2>
                    <p class="lead mb-4">Connect. Communicate. Collaborate. With I-SMS, staying informed has never been easier.</p>
                </div>
                <div class="button-container">
                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=ismsbatstateu@gmail.com&su=Inquiry about I-SMS Services&body=Hello I-SMS Team,%0A%0AI would like to learn more about your services.%0A%0APlease contact me at your earliest convenience.%0A%0ABest regards."
                    class="btn btn-danger btn-lg" target="_blank">
                     <i class="bi bi-envelope-fill me-2"></i>Connect With Us
                 </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer mt-auto py-4 bg-light">
    <div class="container">
        <!-- Copyright -->
        <div class="footer-bottom text-center">
            <hr class="my-4">
            <p class="mb-0 text-muted">© 2024 I-SMS. All rights reserved.</p>
        </div>
    </div>
</footer>

        <!-- Scripts -->
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>

        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script>
            AOS.init({
                duration: 1200,
                once: true,
                offset: 100
            });
        </script>
    </body>
</html>