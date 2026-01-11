<?php
session_start();
$pageTitle = "Privacy Policy | AquaCare";
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
        .privacy-header {
            background: linear-gradient(135deg, #17a2b8 0%, #1d6fa5 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 2rem;
            text-align: center;
        }
        .privacy-content {
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
        .privacy-list {
            list-style-type: none;
            padding-left: 0;
        }
        .privacy-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .privacy-list li:before {
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
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Privacy Header Section -->
    <section class="privacy-header">
        <div class="container">
            <h1 class="display-4"><i class="fas fa-shield-alt"></i> Privacy Policy</h1>
            <p class="lead">How we protect and use your information at AquaCare</p>
        </div>
    </section>

    <!-- Privacy Content Section -->
    <div class="container">
        <div class="last-updated">
            <p class="mb-0"><strong>Last Updated:</strong> January 15, 2023</p>
        </div>

        <div class="privacy-content">
            <div class="row">
                <div class="col-lg-12">
                    <p class="lead">At AquaCare, we take your privacy seriously. This Privacy Policy describes how we collect, use, and protect your personal information when you use our aquarium management services.</p>

                    <h3 class="section-title">1. Information We Collect</h3>
                    <p>We collect information to provide better services to all our users. The types of information we collect include:</p>
                    <ul class="privacy-list">
                        <li><strong>Account Information:</strong> When you create an account, we collect your name, email address, and password.</li>
                        <li><strong>Aquarium Data:</strong> Information about your aquariums, fish, water parameters, and maintenance schedules.</li>
                        <li><strong>Usage Information:</strong> How you use our services, including pages visited and features used.</li>
                        <li><strong>Device Information:</strong> Information about the device you use to access our services, including hardware model, operating system, and browser type.</li>
                    </ul>

                    <h3 class="section-title">2. How We Use Your Information</h3>
                    <p>We use the information we collect for the following purposes:</p>
                    <ul class="privacy-list">
                        <li>To provide, maintain, and improve our services</li>
                        <li>To personalize your experience and provide customized content</li>
                        <li>To communicate with you about products, services, offers, and events</li>
                        <li>To monitor and analyze trends, usage, and activities</li>
                        <li>To detect, prevent, and address technical issues and security vulnerabilities</li>
                    </ul>

                    <h3 class="section-title">3. Information Sharing</h3>
                    <p>We do not sell your personal information to third parties. We may share your information in the following circumstances:</p>
                    <ul class="privacy-list">
                        <li>With your consent</li>
                        <li>With service providers who perform services on our behalf</li>
                        <li>To comply with legal obligations or protect against legal liability</li>
                        <li>In connection with a merger, sale, or acquisition of all or a portion of our business</li>
                    </ul>

                    <h3 class="section-title">4. Data Security</h3>
                    <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p>
                    <ul class="privacy-list">
                        <li>Encryption of data in transit and at rest</li>
                        <li>Regular security assessments and vulnerability testing</li>
                        <li>Access controls to restrict who can access your information</li>
                        <li>Regular backups to prevent data loss</li>
                    </ul>

                    <h3 class="section-title">5. Your Rights</h3>
                    <p>You have the following rights regarding your personal information:</p>
                    <ul class="privacy-list">
                        <li><strong>Access:</strong> You can request a copy of the personal information we hold about you.</li>
                        <li><strong>Correction:</strong> You can request that we correct any inaccurate or incomplete information.</li>
                        <li><strong>Deletion:</strong> You can request that we delete your personal information.</li>
                        <li><strong>Objection:</strong> You can object to our processing of your personal information.</li>
                        <li><strong>Data Portability:</strong> You can request a structured, commonly used, and machine-readable format of your personal information.</li>
                    </ul>
                    <p>To exercise any of these rights, please contact us using the information provided in the Contact section.</p>

                    <h3 class="section-title">6. Cookies and Tracking Technologies</h3>
                    <p>We use cookies and similar tracking technologies to track activity on our service and hold certain information. Cookies are files with a small amount of data which may include an anonymous unique identifier.</p>
                    <p>You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, you may not be able to use some portions of our service.</p>

                    <h3 class="section-title">7. Third-Party Links</h3>
                    <p>Our service may contain links to third-party websites or services that are not operated by us. If you click on a third-party link, you will be directed to that third party's site. We strongly advise you to review the Privacy Policy of every site you visit.</p>
                    <p>We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party sites or services.</p>

                    <h3 class="section-title">8. Children's Privacy</h3>
                    <p>Our service does not address anyone under the age of 13. We do not knowingly collect personally identifiable information from children under 13. If you are a parent or guardian and you are aware that your child has provided us with personal information, please contact us. If we become aware that we have collected personal information from a child under age 13 without verification of parental consent, we take steps to remove that information from our servers.</p>

                    <h3 class="section-title">9. Changes to This Privacy Policy</h3>
                    <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date at the top of this Privacy Policy.</p>
                    <p>You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>

                    <div class="contact-box">
                        <h3 class="section-title">10. Contact Us</h3>
                        <p>If you have any questions about this Privacy Policy, please contact us:</p>
                        <ul class="privacy-list">
                            <li>By email: privacy@aquacare.example</li>
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
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple script to highlight the current section in the table of contents
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('.section-title');
            const navLinks = document.querySelectorAll('.privacy-nav a');
            
            window.addEventListener('scroll', function() {
                let current = '';
                
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.clientHeight;
                    
                    if (pageYOffset >= (sectionTop - 100)) {
                        current = section.getAttribute('id');
                    }
                });
                
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href').substring(1) === current) {
                        link.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>