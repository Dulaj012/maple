<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequently Asked Questions - MapleCart</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- FAQ Section -->
    <section class="faq-section py-5">
        <div class="container">
            <div class="section-title text-center mb-5">
                <h1 class="mb-3">Frequently Asked Questions</h1>
                <p class="text-muted">Find answers to common questions about our products and services</p>
            </div>

            <!-- Search Box -->
            <div class="faq-search mb-5">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="faqSearch" class="form-control" placeholder="Search FAQ...">
                            <button class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Categories -->
            <div class="faq-categories mb-5">
                <div class="row g-4 justify-content-center">
                    <div class="col-md-3 col-sm-6">
                        <div class="faq-category-card" data-category="shipping">
                            <div class="icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <h4>Shipping & Delivery</h4>
                            <p>4 Questions</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="faq-category-card" data-category="orders">
                            <div class="icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h4>Orders & Returns</h4>
                            <p>5 Questions</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="faq-category-card" data-category="products">
                            <div class="icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h4>Products</h4>
                            <p>3 Questions</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="faq-category-card" data-category="account">
                            <div class="icon">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h4>Account & Payment</h4>
                            <p>4 Questions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Accordion -->
            <div class="accordion" id="faqAccordion">
                <!-- Shipping & Delivery -->
                <div class="faq-category" id="shipping">
                    <h3 class="mb-4">Shipping & Delivery</h3>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                What are your shipping rates?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>We offer free standard shipping on all orders over $50. For orders under $50, shipping rates are calculated based on your location:</p>
                                <ul>
                                    <li>Local (Colombo): $5</li>
                                    <li>Rest of Sri Lanka: $8</li>
                                    <li>Express Delivery: Additional $10</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                How long will it take to receive my order?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Delivery times vary based on your location and chosen shipping method:</p>
                                <ul>
                                    <li>Colombo: 1-2 business days</li>
                                    <li>Rest of Sri Lanka: 2-4 business days</li>
                                    <li>Express Delivery: Next business day (Colombo only)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders & Returns -->
                <div class="faq-category mt-5" id="orders">
                    <h3 class="mb-4">Orders & Returns</h3>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                What is your return policy?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>We accept returns within 30 days of purchase, provided that:</p>
                                <ul>
                                    <li>The item is unused and in its original packaging</li>
                                    <li>You have the original receipt or proof of purchase</li>
                                    <li>The item is not on our non-returnable items list</li>
                                </ul>
                                <p>Shipping costs for returns are the responsibility of the customer unless the item is defective.</p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                How can I track my order?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>You can track your order in several ways:</p>
                                <ol>
                                    <li>Tracking number will be provided through an email or whatsapp</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="faq-category mt-5" id="products">
                    <h3 class="mb-4">Products</h3>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Are your products authentic Canadian imports?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Yes, all our products are 100% authentic and imported directly from Canada. We work with authorized distributors and maintain proper documentation for all our imports. Each product comes with:</p>
                                <ul>
                                    <li>Original packaging and labels</li>
                                    <li>Import certification</li>
                                    <li>Quality assurance guarantee</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                How do you ensure product quality?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>We maintain strict quality control measures:</p>
                                <ul>
                                    <li>Regular quality inspections</li>
                                    <li>Temperature-controlled storage facilities</li>
                                    <li>Proper handling and transportation</li>
                                    <li>Regular staff training on product handling</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account & Payment -->
                <div class="faq-category mt-5" id="account">
                    <h3 class="mb-4">Account & Payment</h3>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>We accept only direct bank transfers:</p>
                                <ul>
                                    <li>Account Name: MapleCart</li>
                                    <li>Account Number: 22333333333</li>
                                    <li>Bank Name: HNB</li>
                                    <li>Branch Name: Negombo Metro</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                Is my payment information secure?
                            </button>
                        </h2>
                        <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Yes, we take security seriously.</p>
                                <ul>
                                    <li>SSL encryption for all transactions</li>
                                    <li>No storage of sensitive data</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Still Have Questions -->
            <div class="text-center mt-5">
                <h3>Still Have Questions?</h3>
                <p class="mb-4">Can't find the answer you're looking for? Please chat to our friendly team.</p>
                <a href="contact.php" class="btn btn-primary">Contact Us</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script>
        // FAQ Search Functionality
        document.getElementById('faqSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const questions = document.querySelectorAll('.accordion-item');
            
            questions.forEach(question => {
                const text = question.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    question.style.display = 'block';
                } else {
                    question.style.display = 'none';
                }
            });
        });

        // Category Filter
        document.querySelectorAll('.faq-category-card').forEach(card => {
            card.addEventListener('click', function() {
                const category = this.dataset.category;
                document.querySelectorAll('.faq-category').forEach(cat => {
                    if (cat.id === category) {
                        cat.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
        });
    </script>