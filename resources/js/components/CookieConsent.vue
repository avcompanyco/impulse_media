<script setup lang="ts">
import { ref, onMounted } from 'vue';

const showBanner = ref(false);
const showPreferences = ref(false);

const consent = ref({
    necessary: true,
    analytics: false,
    advertising: false,
});

onMounted(() => {
    const stored = localStorage.getItem('cookie_consent');
    if (!stored) {
        showBanner.value = true;
    }
});

function acceptAll() {
    consent.value.analytics = true;
    consent.value.advertising = true;
    saveConsent();
}

function rejectOptional() {
    consent.value.analytics = false;
    consent.value.advertising = false;
    saveConsent();
}

function savePreferences() {
    showPreferences.value = false;
    saveConsent();
}

function saveConsent() {
    localStorage.setItem('cookie_consent', JSON.stringify({
        ...consent.value,
        timestamp: new Date().toISOString(),
    }));
    showBanner.value = false;

    // Update Google consent mode if gtag exists
    if (typeof window !== 'undefined' && (window as any).gtag) {
        (window as any).gtag('consent', 'update', {
            'analytics_storage': consent.value.analytics ? 'granted' : 'denied',
            'ad_storage': consent.value.advertising ? 'granted' : 'denied',
            'ad_user_data': consent.value.advertising ? 'granted' : 'denied',
            'ad_personalization': consent.value.advertising ? 'granted' : 'denied',
        });
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition name="slide-up">
            <div v-if="showBanner" class="cookie-banner" role="dialog" aria-label="Cookie Consent">
                <div class="cookie-content">
                    <div class="cookie-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="cookie-text">
                        <h3>Your Privacy Matters</h3>
                        <p>
                            We use cookies to enhance your experience, analyze site traffic, and serve personalized ads.
                            By clicking "Accept All", you consent to our use of cookies.
                            <a href="/privacy-policy" class="cookie-link">Privacy Policy</a> |
                            <a href="/terms-of-service" class="cookie-link">Terms of Service</a>
                        </p>
                    </div>
                    <div class="cookie-actions">
                        <button @click="showPreferences = !showPreferences" class="btn-preferences">
                            <i class="fas fa-sliders-h"></i> Customize
                        </button>
                        <button @click="rejectOptional" class="btn-reject">
                            Reject Optional
                        </button>
                        <button @click="acceptAll" class="btn-accept">
                            <i class="fas fa-check"></i> Accept All
                        </button>
                    </div>
                </div>

                <Transition name="expand">
                    <div v-if="showPreferences" class="cookie-preferences">
                        <div class="preference-item">
                            <div class="preference-info">
                                <strong><i class="fas fa-lock"></i> Strictly Necessary</strong>
                                <p>Essential for the website to function. Cannot be disabled.</p>
                            </div>
                            <label class="toggle disabled">
                                <input type="checkbox" v-model="consent.necessary" disabled />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="preference-item">
                            <div class="preference-info">
                                <strong><i class="fas fa-chart-bar"></i> Analytics</strong>
                                <p>Help us understand how visitors interact with our website.</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" v-model="consent.analytics" />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="preference-item">
                            <div class="preference-info">
                                <strong><i class="fas fa-ad"></i> Advertising</strong>
                                <p>Used to deliver personalized ads and measure ad performance.</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" v-model="consent.advertising" />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <button @click="savePreferences" class="btn-save-prefs">
                            <i class="fas fa-save"></i> Save Preferences
                        </button>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 99999;
    background: linear-gradient(135deg, rgba(15, 15, 25, 0.98), rgba(25, 20, 40, 0.98));
    backdrop-filter: blur(20px);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 -4px 30px rgba(0, 0, 0, 0.4);
    padding: 20px 24px;
    font-family: 'Inter', -apple-system, sans-serif;
}

.cookie-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.cookie-icon {
    font-size: 28px;
    color: #e84c72;
    flex-shrink: 0;
}

.cookie-text {
    flex: 1;
    min-width: 280px;
}

.cookie-text h3 {
    margin: 0 0 6px 0;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
}

.cookie-text p {
    margin: 0;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.65);
    line-height: 1.5;
}

.cookie-link {
    color: #e84c72;
    text-decoration: none;
    font-weight: 500;
}
.cookie-link:hover {
    text-decoration: underline;
}

.cookie-actions {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
    flex-wrap: wrap;
}

.cookie-actions button {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    white-space: nowrap;
}

.btn-preferences {
    background: rgba(255, 255, 255, 0.06);
    color: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}
.btn-preferences:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
}

.btn-reject {
    background: rgba(255, 255, 255, 0.06);
    color: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}
.btn-reject:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
}

.btn-accept {
    background: linear-gradient(135deg, #e84c72, #c23a5c);
    color: #fff;
}
.btn-accept:hover {
    background: linear-gradient(135deg, #f05a80, #d44466);
    transform: translateY(-1px);
}

/* Preferences panel */
.cookie-preferences {
    max-width: 1200px;
    margin: 16px auto 0;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.preference-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.preference-info {
    flex: 1;
}

.preference-info strong {
    color: #fff;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.preference-info strong i {
    color: #e84c72;
    font-size: 14px;
}

.preference-info p {
    margin: 4px 0 0;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
}

/* Toggle switch */
.toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    cursor: pointer;
    flex-shrink: 0;
}
.toggle.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 24px;
    transition: 0.3s;
}
.toggle-slider::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    left: 3px;
    bottom: 3px;
    transition: 0.3s;
}

.toggle input:checked + .toggle-slider {
    background: #e84c72;
}
.toggle input:checked + .toggle-slider::before {
    transform: translateX(20px);
}

.btn-save-prefs {
    align-self: flex-end;
    padding: 10px 24px;
    background: linear-gradient(135deg, #e84c72, #c23a5c);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-save-prefs:hover {
    background: linear-gradient(135deg, #f05a80, #d44466);
    transform: translateY(-1px);
}

/* Transitions */
.slide-up-enter-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-up-leave-active { transition: all 0.3s ease-in; }
.slide-up-enter-from { transform: translateY(100%); opacity: 0; }
.slide-up-leave-to { transform: translateY(100%); opacity: 0; }

.expand-enter-active { transition: all 0.3s ease; }
.expand-leave-active { transition: all 0.2s ease; }
.expand-enter-from { opacity: 0; max-height: 0; overflow: hidden; }
.expand-leave-to { opacity: 0; max-height: 0; overflow: hidden; }
.expand-enter-to { max-height: 400px; }

@media (max-width: 768px) {
    .cookie-content {
        flex-direction: column;
        text-align: center;
    }
    .cookie-actions {
        justify-content: center;
        width: 100%;
    }
    .cookie-actions button {
        flex: 1;
        min-width: 0;
        padding: 10px 12px;
        font-size: 12px;
    }
}
</style>
