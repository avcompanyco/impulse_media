<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Privacy Policy" />
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
            <h1><i class="fas fa-shield-alt"></i> Privacy Policy</h1>
            <p class="last-updated">Last updated: April 2, 2026</p>

            <section>
                <h2>1. Introduction</h2>
                <p>Impulse Media ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website <strong>impulsemedia.me</strong> and use our streaming platform services.</p>
            </section>

            <section>
                <h2>2. Information We Collect</h2>
                <h3>2.1 Personal Information</h3>
                <p>We may collect personal information that you voluntarily provide when you:</p>
                <ul>
                    <li>Create an account (name, email address, username, password)</li>
                    <li>Subscribe to our services (payment information processed securely via Stripe)</li>
                    <li>Upload content as a Creator (videos, images, descriptions)</li>
                    <li>Contact us for support</li>
                </ul>

                <h3>2.2 Automatically Collected Information</h3>
                <p>When you access our platform, we may automatically collect:</p>
                <ul>
                    <li>Device and browser information</li>
                    <li>IP address and geolocation data</li>
                    <li>Usage data (pages visited, watch time, interactions)</li>
                    <li>Cookies and similar tracking technologies</li>
                </ul>
            </section>

            <section>
                <h2>3. How We Use Your Information</h2>
                <p>We use collected information to:</p>
                <ul>
                    <li>Provide and maintain our streaming services</li>
                    <li>Process subscriptions and payments</li>
                    <li>Personalize your content experience</li>
                    <li>Send administrative and promotional communications</li>
                    <li>Analyze usage patterns to improve our platform</li>
                    <li>Comply with legal obligations</li>
                    <li>Detect and prevent fraud</li>
                </ul>
            </section>

            <section>
                <h2>4. Cookies and Tracking Technologies</h2>
                <p>We use cookies and similar technologies for:</p>
                <ul>
                    <li><strong>Strictly Necessary Cookies:</strong> Essential for site functionality (authentication, security).</li>
                    <li><strong>Analytics Cookies:</strong> Help us understand user behavior via Google Analytics.</li>
                    <li><strong>Advertising Cookies:</strong> Used by Google Ads to serve relevant advertisements and measure campaign performance.</li>
                </ul>
                <p>You can manage your cookie preferences through our cookie consent banner or your browser settings.</p>
            </section>

            <section>
                <h2>5. Third-Party Services</h2>
                <p>We integrate with the following third-party services:</p>
                <ul>
                    <li><strong>Stripe:</strong> Payment processing. <a href="https://stripe.com/privacy" target="_blank" rel="noopener">Stripe Privacy Policy</a></li>
                    <li><strong>Google Analytics:</strong> Website analytics. <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google Privacy Policy</a></li>
                    <li><strong>Google Ads:</strong> Advertising services. <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">Google Ads Policy</a></li>
                    <li><strong>Amazon Web Services (AWS):</strong> Cloud hosting and storage. <a href="https://aws.amazon.com/privacy/" target="_blank" rel="noopener">AWS Privacy Policy</a></li>
                </ul>
            </section>

            <section>
                <h2>6. Data Sharing and Disclosure</h2>
                <p>We do not sell your personal information. We may share data with:</p>
                <ul>
                    <li>Service providers who assist in operating our platform</li>
                    <li>Law enforcement when required by law</li>
                    <li>Business partners with your consent</li>
                </ul>
            </section>

            <section>
                <h2>7. Data Security</h2>
                <p>We implement industry-standard security measures including SSL/TLS encryption, secure payment processing through Stripe, and access controls to protect your data. However, no method of transmission over the Internet is 100% secure.</p>
            </section>

            <section>
                <h2>8. Your Rights</h2>
                <p>Depending on your jurisdiction, you may have the right to:</p>
                <ul>
                    <li>Access the personal data we hold about you</li>
                    <li>Request correction of inaccurate data</li>
                    <li>Request deletion of your personal data</li>
                    <li>Object to or restrict processing of your data</li>
                    <li>Data portability</li>
                    <li>Withdraw consent at any time</li>
                </ul>
                <p>To exercise these rights, contact us at <a href="mailto:support@impulsemedia.me">support@impulsemedia.me</a>.</p>
            </section>

            <section>
                <h2>9. Children's Privacy</h2>
                <p>Our services are not intended for users under the age of 13 (or applicable age of consent). We do not knowingly collect data from children.</p>
            </section>

            <section>
                <h2>10. Changes to This Policy</h2>
                <p>We may update this Privacy Policy periodically. We will notify you of significant changes through our platform or via email.</p>
            </section>

            <section>
                <h2>11. Contact Us</h2>
                <p>If you have questions about this Privacy Policy, contact us at:</p>
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
