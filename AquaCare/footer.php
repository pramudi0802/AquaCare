<?php
// footer.php
?>
    </main> <!-- Closing tag for the main content opened in header.php -->

    <footer class="bg-dark text-white mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-fish"></i> AquaCare</h5>
                    <p class="">Your comprehensive aquarium management system for fish health and compatibility.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white"><i class="fas fa-home me-2"></i>Home</a></li>
                        <li class="mb-2"><a href="fish_encyclopedia.php" class="text-white"><i class="fas fa-book me-2"></i>Fish Encyclopedia</a></li>
                        <li class="mb-2"><a href="compatibility.php" class="text-white"><i class="fas fa-heart me-2"></i>Compatibility Checker</a></li>
                        <li class="mb-2"><a href="diagnosis.php" class="text-white"><i class="fas fa-heartbeat me-2"></i>Disease Diagnosis</a></li>
                        <li class="mb-2"><a href="feeding-guide.php" class="text-white"><i class="fas fa-utensils me-2"></i>Feeding Guide</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact & Support</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> support@aquacare.com</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +1 (555) 123-4567</li>
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Aquarium Way, Coral City</li>
                        <li class="mb-2"><i class="fas fa-clock me-2"></i> Mon-Fri: 9AM-5PM EST</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> FishCare. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <a href="privacy_policy.php" class="text-white me-3">Privacy Policy</a>
                        <a href="terms.php" class="text-white">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="btn btn-primary btn-lg back-to-top" role="button">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="js/scripts.js"></script>
    
    <script>
        // Back to top button
        $(document).ready(function(){
            $(window).scroll(function(){
                if ($(this).scrollTop() > 100) {
                    $('.back-to-top').fadeIn();
                } else {
                    $('.back-to-top').fadeOut();
                }
            });
            
            $('.back-to-top').click(function(){
                $('html, body').animate({scrollTop : 0}, 800);
                return false;
            });
        });
    </script>
</body>
</html>