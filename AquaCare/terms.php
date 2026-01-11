<?php
session_start();
$pageTitle = "Terms of Service | AquaCare";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .terms-header {
            background: linear-gradient(135deg, #17a2b8 0%, #1d6fa5 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 2rem;
            text-align: center;
        }
        .terms-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        .section-title {
            color: #17a2b8;
            border-bottom: 2px solid #e9f7fe;
            padding-bottom: 0.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .last-updated {
            background-color: #e9f7fe;
            border-left: 4px solid #17a2b8;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }
        .back-to-home {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            margin-top: 2rem;
        }
        .terms-list {
            list-style-type: none;
            padding-left: 0;
        }
        .terms-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .terms-list li:before {
            content: "•";
            color: #17a2b8;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
        }
        .contact-box {
            background-color: #e9f7fe;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        .note-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            border-radius: 5px;
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Terms Header Section -->
    <section class="terms-header">
        <div class="container">
            <h1 class="display-4"><i class="fas fa-scale-balanced"></i> Terms of Service</h1>
            <p class="lead">The rules and guidelines for using AquaCare services</p>
        </div>
    </section>

    <!-- Terms Content Section -->
    <div class="container">
        <div class="last-updated">
            <p class="mb-0"><strong>Last Updated:</strong> January 15, 2023</p>
        </div>

        <div class="terms-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="note-box">
                        <p class="mb-0"><strong>Please read these Terms of Service carefully before using our website.</strong></p>
                    </div>

                    <p class="lead">Welcome to AquaCare! These Terms of Service govern your use of our website and services. By accessing or using AquaCare, you agree to be bound by these Terms.</p>

                    <h3 class="section-title">1. Acceptance of Terms</h3>
                    <p>By accessing or using the AquaCare website ("Service"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using or accessing this site.</p>

                    <h3 class="section-title">2. Use License</h3>
                    <p>Permission is granted to temporarily use AquaCare for personal, non-commercial purposes only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                    <ul class="terms-list">
                        <li>Modify or copy the materials</li>
                        <li>Use the materials for any commercial purpose or for any public display</li>
                        <li>Attempt to reverse engineer any software contained on AquaCare's website</li>
                        <li>Remove any copyright or other proprietary notations from the materials</li>
                        <li>Transfer the materials to another person or "mirror" the materials on any other server</li>
                    </ul>
                    <p>This license shall automatically terminate if you violate any of these restrictions and may be terminated by AquaCare at any time.</p>

                    <h3 class="section-title">3. Account Registration</h3>
                    <p>To access certain features of our Service, you may be required to create an account. When you create an account, you agree to:</p>
                    <ul class="terms-list">
                        <li>Provide accurate, current, and complete information</li>
                        <li>Maintain the security of your password and accept all risks of unauthorized access</li>
                        <li>Notify us immediately if you discover or suspect any security breaches related to the Service</li>
                        <li>Take responsibility for all activities that occur under your account</li>
                    </ul>

                    <h3 class="section-title">4. User Content</h3>
                    <p>Our Service allows you to post, link, store, share and otherwise make available certain information, text, graphics, or other material ("Content"). You are responsible for the Content that you post to the Service, including its legality, reliability, and appropriateness.</p>
                    <p>By posting Content to the Service, you grant us the right and license to use, modify, publicly perform, publicly display, reproduce, and distribute such Content on and through the Service.</p>
                    <p>You retain any and all of your rights to any Content you submit, post or display on or through the Service and you are responsible for protecting those rights.</p>

                    <h3 class="section-title">5. Prohibited Uses</h3>
                    <p>You may use the Service only for lawful purposes and in accordance with these Terms. You agree not to use the Service:</p>
                    <ul class="terms-list">
                        <li>In any way that violates any applicable federal, state, local, or international law or regulation</li>
                        <li>To transmit, or procure the sending of, any advertising or promotional material without our prior written consent</li>
                        <li>To impersonate or attempt to impersonate AquaCare, an AquaCare employee, another user, or any other person or entity</li>
                        <li>To engage in any other conduct that restricts or inhibits anyone's use or enjoyment of the Service</li>
                        <li>To introduce any viruses, Trojan horses, worms, logic bombs, or other material that is malicious or technologically harmful</li>
                    </ul>

                    <h3 class="section-title">6. Intellectual Property Rights</h3>
                    <p>The Service and its original content, features, and functionality are and will remain the exclusive property of AquaCare and its licensors. The Service is protected by copyright, trademark, and other laws of both the United States and foreign countries.</p>
                    <p>Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of AquaCare.</p>

                    <h3 class="section-title">7. Disclaimer</h3>
                    <p>The materials on AquaCare's website are provided on an 'as is' basis. AquaCare makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
                    <p>Further, AquaCare does not warrant or make any representations concerning the accuracy, likely results, or reliability of the use of the materials on its website or otherwise relating to such materials or on any sites linked to this site.</p>
                    <div class="note-box">
                        <p class="mb-0"><strong>Important:</strong> AquaCare provides information for educational purposes only. We are not veterinarians or aquatic experts. Always consult with qualified professionals for specific advice about your aquarium and fish health.</p>
                    </div>

                    <h3 class="section-title">8. Limitations</h3>
                    <p>In no event shall AquaCare or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on AquaCare's website, even if AquaCare or an AquaCare authorized representative has been notified orally or in writing of the possibility of such damage.</p>

                    <h3 class="section-title">9. Accuracy of Materials</h3>
                    <p>The materials appearing on AquaCare's website could include technical, typographical, or photographic errors. AquaCare does not warrant that any of the materials on its website are accurate, complete or current. AquaCare may make changes to the materials contained on its website at any time without notice. However, AquaCare does not make any commitment to update the materials.</p>

                    <h3 class="section-title">10. Links to Other Websites</h3>
                    <p>AquaCare has not reviewed all of the sites linked to its website and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by AquaCare of the site. Use of any such linked website is at the user's own risk.</p>

                    <h3 class="section-title">11. Modifications to Terms of Service</h3>
                    <p>AquaCare may revise these Terms of Service for its website at any time without notice. By using this website you are agreeing to be bound by the then current version of these Terms of Service.</p>

                    <h3 class="section-title">12. Governing Law</h3>
                    <p>These terms and conditions are governed by and construed in accordance with the laws of the State of California and you irrevocably submit to the exclusive jurisdiction of the courts in that State or location.</p>

                    <h3 class="section-title">13. Termination</h3>
                    <p>We may terminate or suspend your account and bar access to the Service immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever and without limitation, including but not limited to a breach of the Terms.</p>
                    <p>All provisions of the Terms which by their nature should survive termination shall survive termination, including, without limitation, ownership provisions, warranty disclaimers, indemnity and limitations of liability.</p>

                    <h3 class="section-title">14. Indemnification</h3>
                    <p>You agree to defend, indemnify and hold harmless AquaCare and its licensee and licensors, and their employees, contractors, agents, officers and directors, from and against any and all claims, damages, obligations, losses, liabilities, costs or debt, and expenses (including but not limited to attorney's fees), resulting from or arising out of a) your use and access of the Service, or b) a breach of these Terms.</p>

                    <div class="contact-box">
                        <h3 class="section-title">15. Contact Information</h3>
                        <p>If you have any questions about these Terms of Service, please contact us:</p>
                        <ul class="terms-list">
                            <li>By email: legal@aquacare.example</li>
                            <li>By visiting this page on our website: <a href="http://localhost/aquacare/contact.php">Contact Us</a></li>
                            <li>By mail: AquaCare Inc., 123 Aquarium Street, Watertown, AW 12345</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="back-to-home">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="privacy_policy.php" class="btn btn-outline-primary ms-2">
                <i class="fas fa-shield-alt"></i> View Privacy Policy
            </a>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href^="#"]');
            
            for (const link of links) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 20,
                            behavior: 'smooth'
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>