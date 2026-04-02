<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Terms of Service" />
    <div class="legal-page">
        <header class="legal-header">
            <Link href="/" class="logo-link">
                <img src="/images/logo/impulse-logo.svg" alt="Impulse Media" class="legal-logo" />
            </Link>
            <nav class="legal-nav">
                <Link href="/login" class="nav-link">Log In</Link>
                <Link href="/register" class="nav-link nav-link-primary">Sign Up</Link>
            </nav>
        </header>

        <main class="legal-content">
            <h1><i class="fas fa-file-contract"></i> Terms of Service</h1>
            <p class="last-updated">Last updated: April 2, 2026</p>

            <section>
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing or using Impulse Media ("the Platform"), available at <strong>impulsemedia.me</strong>, you agree to be bound by these Terms of Service. If you do not agree, please do not use the Platform.</p>
            </section>

            <section>
                <h2>2. Description of Service</h2>
                <p>Impulse Media is a streaming platform that allows:</p>
                <ul>
                    <li><strong>Spectators:</strong> Subscribe to watch movies, series, shorts, and other media content.</li>
                    <li><strong>Creators:</strong> Upload, manage, and monetize original video content.</li>
                </ul>
                <p>Access to content requires a valid paid subscription. Some features may vary based on your subscription plan.</p>
            </section>

            <section>
                <h2>3. Account Registration</h2>
                <ul>
                    <li>You must be at least 13 years old to create an account.</li>
                    <li>You are responsible for maintaining the confidentiality of your credentials.</li>
                    <li>You must provide accurate and complete information during registration.</li>
                    <li>You are responsible for all activities under your account.</li>
                    <li>Notify us immediately of any unauthorized use at <a href="mailto:support@impulsemedia.me">support@impulsemedia.me</a>.</li>
                </ul>
            </section>

            <section>
                <h2>4. Subscriptions and Payments</h2>
                <h3>4.1 Billing</h3>
                <ul>
                    <li>Subscriptions are billed on a recurring monthly basis.</li>
                    <li>Payment is processed securely through Stripe.</li>
                    <li>By subscribing, you authorize us to charge your payment method on each billing cycle.</li>
                </ul>

                <h3>4.2 Free Trial</h3>
                <p>Some plans may include a free trial period. If you do not cancel before the trial ends, your subscription will automatically convert to a paid plan.</p>

                <h3>4.3 Cancellation</h3>
                <ul>
                    <li>You may cancel your subscription at any time from your account settings.</li>
                    <li>Cancellation takes effect at the end of the current billing period.</li>
                    <li>No refunds are provided for partial billing periods.</li>
                </ul>
            </section>

            <section>
                <h2>5. User Content</h2>
                <h3>5.1 Creator Responsibilities</h3>
                <ul>
                    <li>You retain ownership of content you upload.</li>
                    <li>By uploading, you grant Impulse Media a non-exclusive, worldwide license to host, display, and distribute your content on the Platform.</li>
                    <li>You must own or have rights to all content you upload.</li>
                    <li>Content must not violate any applicable laws or regulations.</li>
                </ul>

                <h3>5.2 Prohibited Content</h3>
                <p>You may not upload content that:</p>
                <ul>
                    <li>Infringes intellectual property rights</li>
                    <li>Contains illegal, harmful, or offensive material</li>
                    <li>Promotes violence, hatred, or discrimination</li>
                    <li>Contains malware or harmful code</li>
                    <li>Violates the privacy of third parties</li>
                </ul>
            </section>

            <section>
                <h2>6. Acceptable Use</h2>
                <p>You agree not to:</p>
                <ul>
                    <li>Use the Platform for any illegal purpose</li>
                    <li>Attempt to gain unauthorized access to the Platform or other accounts</li>
                    <li>Use automated means to scrape or download content</li>
                    <li>Redistribute, copy, or share subscription content outside the Platform</li>
                    <li>Interfere with the proper functioning of the Platform</li>
                </ul>
            </section>

            <section>
                <h2>7. Intellectual Property</h2>
                <p>The Platform, including its design, code, branding, and original content, is the property of Impulse Media and is protected by copyright and trademark laws. You may not reproduce, modify, or distribute any part of the Platform without written consent.</p>
            </section>

            <section>
                <h2>8. Disclaimer of Warranties</h2>
                <p>The Platform is provided "AS IS" and "AS AVAILABLE" without warranties of any kind. We do not guarantee uninterrupted or error-free service.</p>
            </section>

            <section>
                <h2>9. Limitation of Liability</h2>
                <p>To the maximum extent permitted by law, Impulse Media shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the Platform.</p>
            </section>

            <section>
                <h2>10. Termination</h2>
                <p>We reserve the right to suspend or terminate your account if you violate these Terms. Upon termination, your right to access the Platform ceases immediately.</p>
            </section>

            <section>
                <h2>11. Governing Law</h2>
                <p>These Terms shall be governed by and construed in accordance with applicable laws. Any disputes shall be resolved through binding arbitration or in the courts of the applicable jurisdiction.</p>
            </section>

            <section>
                <h2>12. Changes to Terms</h2>
                <p>We may modify these Terms at any time. Continued use of the Platform after changes constitutes acceptance of the updated Terms. We will notify users of significant changes via email or platform notification.</p>
            </section>

            <section>
                <h2>13. Contact</h2>
                <p>For questions about these Terms, contact us at:</p>
                <ul>
                    <li>Email: <a href="mailto:support@impulsemedia.me">support@impulsemedia.me</a></li>
                    <li>Website: <a href="https://impulsemedia.me">impulsemedia.me</a></li>
                </ul>
            </section>
        </main>

        <footer class="legal-footer">
            <p>&copy; 2026 Impulse Media. All rights reserved.</p>
            <div class="footer-links">
                <Link href="/privacy-policy">Privacy Policy</Link>
                <Link href="/terms-of-service">Terms of Service</Link>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.legal-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #0a0a14 0%, #111126 100%);
    color: rgba(255, 255, 255, 0.85);
    font-family: 'Inter', -apple-system, sans-serif;
}

.legal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 40px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.legal-logo {
    height: 36px;
}

.legal-nav {
    display: flex;
    gap: 12px;
}

.nav-link {
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    color: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.2s;
}
.nav-link:hover {
    color: #fff;
    border-color: rgba(255, 255, 255, 0.2);
}
.nav-link-primary {
    background: linear-gradient(135deg, #e84c72, #c23a5c);
    color: #fff;
    border: none;
}
.nav-link-primary:hover {
    background: linear-gradient(135deg, #f05a80, #d44466);
}

.legal-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 60px 24px;
}

.legal-content h1 {
    font-size: 32px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.legal-content h1 i {
    color: #e84c72;
}

.last-updated {
    color: rgba(255, 255, 255, 0.4);
    font-size: 13px;
    margin-bottom: 40px;
}

.legal-content h2 {
    font-size: 20px;
    font-weight: 600;
    color: #fff;
    margin: 36px 0 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.legal-content h3 {
    font-size: 16px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9);
    margin: 20px 0 8px;
}

.legal-content p {
    font-size: 14px;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.65);
    margin-bottom: 12px;
}

.legal-content ul {
    padding-left: 20px;
    margin-bottom: 12px;
}

.legal-content li {
    font-size: 14px;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.65);
    margin-bottom: 6px;
}

.legal-content a {
    color: #e84c72;
    text-decoration: none;
}
.legal-content a:hover {
    text-decoration: underline;
}

section {
    margin-bottom: 8px;
}

.legal-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    padding: 24px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.legal-footer p {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.4);
    margin: 0;
}

.footer-links {
    display: flex;
    gap: 20px;
}

.footer-links a {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    text-decoration: none;
}
.footer-links a:hover {
    color: #e84c72;
}

@media (max-width: 768px) {
    .legal-header {
        padding: 16px 20px;
    }
    .legal-content {
        padding: 40px 20px;
    }
    .legal-content h1 {
        font-size: 24px;
    }
    .legal-footer {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
}
</style>
