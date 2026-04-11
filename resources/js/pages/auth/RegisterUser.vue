<script setup lang="ts">
import RegisterUserController from '@/actions/App/Http/Controllers/Auth/User/RegisterUserController';
import AuthUserLayout from '@/layouts/auth/AuthUserLayout.vue';
import TextLink from '@/components/TextLink.vue';
import InputField from '@/components/form/InputField.vue';
import { user as loginUser } from '@/routes/login';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const page = usePage();
const spectatorTerms = computed(() => (page.props as any).spectatorTerms);
const creatorTerms = computed(() => (page.props as any).creatorTerms);

// ─── Step Management ───
const currentStep = ref(1); // 1: Choose type, 2: Form, 3: Terms
const selectedType = ref<'spectator' | 'creator' | ''>('');
const acceptedTerms = ref(false);
const termsScrolledToBottom = ref(false);

// ─── Reactive form using useForm to persist data across steps ───
const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    user_type: '' as string,
    accept_terms: '0' as string,
});

// Keep form.user_type in sync with selectedType
watch(selectedType, (val) => {
    form.user_type = val;
});

// Keep form.accept_terms in sync with acceptedTerms
watch(acceptedTerms, (val) => {
    form.accept_terms = val ? '1' : '0';
});

// ─── Step Navigation ───
function selectType(type: 'spectator' | 'creator') {
    selectedType.value = type;
    currentStep.value = 2;
}

function goToTermsStep() {
    currentStep.value = 3;
    acceptedTerms.value = false;
    termsScrolledToBottom.value = false;
}

function goBackToForm() {
    currentStep.value = 2;
}

function goBackToTypeSelection() {
    currentStep.value = 1;
    selectedType.value = '';
}

// ─── Form Submission ───
function submitForm() {
    form.post(RegisterUserController.url(), {
        preserveScroll: true,
        onError: () => {
            // Stay on step 2 so user doesn't have to re-fill
            currentStep.value = 2;
        },
    });
}

// ─── Terms Scroll Check ───
function handleTermsScroll(event: Event) {
    const el = event.target as HTMLElement;
    const threshold = 50;
    if (el.scrollHeight - el.scrollTop - el.clientHeight <= threshold) {
        termsScrolledToBottom.value = true;
    }
}

// ─── Active Terms Content ───
const activeTerms = computed(() => {
    if (selectedType.value === 'spectator') return spectatorTerms.value;
    if (selectedType.value === 'creator') return creatorTerms.value;
    return null;
});

const stepTitle = computed(() => {
    switch (currentStep.value) {
        case 1: return 'Choose Your Account Type';
        case 2: return 'Create Your Account';
        case 3: return 'Terms & Conditions';
        default: return '';
    }
});
</script>

<template>
    <Head title="Register" />

    <div class="auth-page">
        <!-- Background -->
        <div class="auth-background">
            <img src="/background.gif" alt="Background" class="auth-background-img">
            <div class="auth-background-overlay"></div>
        </div>

        <!-- Container (wider for step 1 & 3) -->
        <div class="register-container" :class="{ 'register-container--wide': currentStep === 1 || currentStep === 3 }">
            <img src="/images/logo.png" alt="Logo" class="register-logo">

            <!-- Step Indicator -->
            <div class="step-indicators">
                <div class="step-dot" :class="{ active: currentStep >= 1 }"></div>
                <div class="step-line" :class="{ active: currentStep >= 2 }"></div>
                <div class="step-dot" :class="{ active: currentStep >= 2 }"></div>
                <div class="step-line" :class="{ active: currentStep >= 3 }"></div>
                <div class="step-dot" :class="{ active: currentStep >= 3 }"></div>
            </div>

            <h1 class="register-title">{{ stepTitle }}</h1>

            <!-- ═══════════════════════════════════════════
                 STEP 1: Choose Account Type
                 ═══════════════════════════════════════════ -->
            <div v-if="currentStep === 1" class="type-selection">
                <div class="type-card" @click="selectType('spectator')">
                    <div class="type-icon"><i class="fas fa-tv"></i></div>
                    <h3 class="type-name">Spectator</h3>
                    <p class="type-desc">
                        Watch movies, series, and shorts. Subscribe to enjoy unlimited content.
                    </p>
                    <ul class="type-features">
                        <li>✓ Stream all content</li>
                        <li>✓ Build your watchlist</li>
                        <li>✓ Follow creators</li>
                    </ul>
                    <span class="type-price">From $10/mo</span>
                </div>

                <div class="type-card" @click="selectType('creator')">
                    <div class="type-icon"><i class="fas fa-video"></i></div>
                    <h3 class="type-name">Creator</h3>
                    <p class="type-desc">
                        Upload your movies, series, and shorts. Build your audience and earn revenue.
                    </p>
                    <ul class="type-features">
                        <li>✓ Upload content</li>
                        <li>✓ Your own channel</li>
                        <li>✓ Earn revenue</li>
                    </ul>
                    <span class="type-price">Free to start</span>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════
                 STEP 2: Registration Form
                 ═══════════════════════════════════════════ -->
            <div v-if="currentStep === 2" class="form-step">
                <div class="selected-type-badge">
                    <span class="badge-icon"><i :class="selectedType === 'spectator' ? 'fas fa-tv' : 'fas fa-video'"></i></span>
                    <span class="badge-label">{{ selectedType === 'spectator' ? 'Spectator' : 'Creator' }} Account</span>
                    <button class="badge-change" @click="goBackToTypeSelection">Change</button>
                </div>

                <form @submit.prevent="submitForm" class="register-form">

                    <div class="grid gap-5">
                        <InputField
                            name="name"
                            label="Full Name"
                            placeholder="Full name"
                            type="text"
                            autocomplete="name"
                            autofocus
                            v-model="form.name"
                            :error="form.errors.name" />
                        <InputField
                            name="username"
                            label="Username"
                            placeholder="Username"
                            type="text"
                            autocomplete="username"
                            v-model="form.username"
                            :error="form.errors.username" />
                        <InputField
                            name="email"
                            label="Email address"
                            placeholder="Email"
                            type="email"
                            autocomplete="email"
                            v-model="form.email"
                            :error="form.errors.email" />
                        <InputField
                            name="password"
                            label="Password"
                            placeholder="******************"
                            type="password"
                            autocomplete="new-password"
                            v-model="form.password"
                            :error="form.errors.password" />
                        <InputField
                            name="password_confirmation"
                            label="Confirm password"
                            placeholder="Confirm password"
                            type="password"
                            autocomplete="new-password"
                            v-model="form.password_confirmation"
                            :error="form.errors.password_confirmation" />

                        <div v-if="form.errors.accept_terms" class="field-error">{{ form.errors.accept_terms }}</div>
                        <div v-if="form.errors.user_type" class="field-error">{{ form.errors.user_type }}</div>
                    </div>

                    <!-- "Continue to Terms" button if not yet accepted -->
                    <button v-if="!acceptedTerms" type="button" class="btn continue-btn" @click="goToTermsStep">
                        Continue → Accept Terms
                    </button>

                    <!-- Submit button (only after terms accepted) -->
                    <button v-else type="submit" class="btn signup-button" :disabled="form.processing">
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="form.processing"></i>
                        Create Account
                    </button>

                    <div class="text-center text-sm text-muted-foreground my-3" style="color: rgba(255,255,255,0.6);">
                        Already have an account?
                        <TextLink :href="loginUser()" class="underline underline-offset-4">Log in</TextLink>
                    </div>
                </form>
            </div>

            <!-- ═══════════════════════════════════════════
                 STEP 3: Terms & Conditions
                 ═══════════════════════════════════════════ -->
            <div v-if="currentStep === 3" class="terms-step">
                <div class="terms-scroll-container" @scroll="handleTermsScroll">
                    <div v-if="activeTerms" v-html="activeTerms.content" class="terms-content"></div>
                    <div v-else class="terms-content">
                        <p>Terms & Conditions are being prepared. Please try again later.</p>
                    </div>
                </div>

                <div class="terms-actions">
                    <label class="terms-checkbox" :class="{ disabled: !termsScrolledToBottom }">
                        <input
                            type="checkbox"
                            v-model="acceptedTerms"
                            :disabled="!termsScrolledToBottom"
                        >
                        <span>I have read and accept the Terms & Conditions</span>
                    </label>

                    <p v-if="!termsScrolledToBottom" class="scroll-hint">
                        ↓ Please scroll to the bottom to read all terms
                    </p>

                    <div class="terms-buttons">
                        <button type="button" class="btn back-btn" @click="goBackToForm">← Back</button>
                        <button
                            type="button"
                            class="btn accept-btn"
                            :disabled="!acceptedTerms"
                            @click="goBackToForm"
                        >
                            Accept & Continue
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="currentStep === 1" class="register-footer">
                Already have an account?
                <TextLink :href="loginUser()" class="underline">Log in</TextLink>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ─── Page ─── */
.auth-page {
    position: relative;
    min-height: 100vh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: auto;
    padding: 2rem 1rem;
}

.auth-background {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0;
}
.auth-background-img {
    width: 100%; height: 100%; object-fit: cover; filter: blur(4px); transform: scale(1.05);
}
.auth-background-overlay {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    background-color: rgba(0, 0, 0, 0.4);
}

/* ─── Container ─── */
.register-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 460px;
    margin: 0 auto;
    padding: 2.5rem 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    background: rgba(10, 10, 35, 0.78);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(124, 58, 237, 0.18);
    border-radius: 24px;
    box-shadow: 0 0 60px rgba(124, 58, 237, 0.08), 0 24px 64px rgba(0, 0, 0, 0.6);
    transition: max-width 0.3s ease;
}
.register-container--wide {
    max-width: 680px;
}

.register-logo {
    width: 52px; height: 52px; object-fit: contain; margin-bottom: 1rem;
    cursor: pointer; transition: transform 0.2s;
}
.register-logo:hover { transform: scale(1.08); }

/* ─── Step Indicators ─── */
.step-indicators {
    display: flex; align-items: center; gap: 0; margin-bottom: 1.5rem;
}
.step-dot {
    width: 12px; height: 12px; border-radius: 50%;
    background: rgba(255,255,255,0.2); transition: background 0.3s;
}
.step-dot.active { background: #e8445a; }
.step-line {
    width: 40px; height: 2px; background: rgba(255,255,255,0.15); transition: background 0.3s;
}
.step-line.active { background: #e8445a; }

.register-title {
    font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: 2rem;
    color: #fff; letter-spacing: -0.3px;
}

/* ─── Step 1: Type Selection ─── */
.type-selection {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; width: 100%;
}
.type-card {
    background: rgba(255,255,255,0.05); border: 1.5px solid rgba(255,255,255,0.12);
    border-radius: 16px; padding: 1.5rem; cursor: pointer; text-align: center;
    transition: all 0.25s ease; display: flex; flex-direction: column; align-items: center;
}
.type-card:hover {
    border-color: #e8445a; background: rgba(232, 68, 90, 0.08);
    transform: translateY(-3px); box-shadow: 0 8px 32px rgba(232, 68, 90, 0.15);
}
.type-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.type-name { font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem; }
.type-desc {
    font-size: 0.85rem; color: rgba(255,255,255,0.6); line-height: 1.4; margin-bottom: 1rem;
}
.type-features {
    list-style: none; padding: 0; margin: 0 0 1rem; text-align: left; width: 100%;
}
.type-features li {
    font-size: 0.8rem; color: rgba(255,255,255,0.75); padding: 0.2rem 0;
}
.type-price {
    font-size: 0.85rem; font-weight: 600; color: #e8445a; margin-top: auto;
    background: rgba(232,68,90,0.12); padding: 0.4rem 1rem; border-radius: 20px;
}

/* ─── Step 2: Form ─── */
.form-step { width: 100%; }
.selected-type-badge {
    display: flex; align-items: center; gap: 0.5rem;
    background: rgba(232,68,90,0.1); border: 1px solid rgba(232,68,90,0.25);
    border-radius: 12px; padding: 0.6rem 1rem; margin-bottom: 1.5rem;
}
.badge-icon { font-size: 1.2rem; }
.badge-label { color: #fff; font-weight: 600; font-size: 0.9rem; flex: 1; }
.badge-change {
    background: none; border: 1px solid rgba(255,255,255,0.3); color: rgba(255,255,255,0.7);
    padding: 0.25rem 0.75rem; border-radius: 8px; font-size: 0.75rem; cursor: pointer;
    transition: all 0.2s;
}
.badge-change:hover { border-color: #fff; color: #fff; }

.register-form { width: 100%; }

.field-error {
    color: #f87171; font-size: 0.85rem; margin-top: 0.25rem;
}

.continue-btn, .signup-button {
    display: block; width: 100%; max-width: 100%; margin-top: 1.5rem;
    background-color: #e8445a; color: white; border: none; border-radius: 12px;
    padding: 0.875rem 2rem; font-size: 1rem; font-weight: 600; cursor: pointer;
    transition: all 0.3s ease;
}
.continue-btn:hover, .signup-button:hover {
    background-color: #d03050; transform: scale(1.01);
}
.signup-button:disabled { opacity: 0.5; cursor: not-allowed; }

/* ─── Step 3: Terms ─── */
.terms-step { width: 100%; }

.terms-scroll-container {
    max-height: 350px; overflow-y: auto; background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;
    padding: 1.25rem; margin-bottom: 1rem;
}
.terms-scroll-container::-webkit-scrollbar { width: 6px; }
.terms-scroll-container::-webkit-scrollbar-track { background: transparent; }
.terms-scroll-container::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.15); border-radius: 3px;
}

.terms-content {
    color: rgba(255,255,255,0.85); font-size: 0.85rem; line-height: 1.6;
}
.terms-content :deep(h2) { font-size: 1.2rem; color: #fff; margin-bottom: 0.5rem; }
.terms-content :deep(h3) { font-size: 1rem; color: #e8445a; margin: 1rem 0 0.4rem; }
.terms-content :deep(p) { margin-bottom: 0.6rem; }
.terms-content :deep(ul) { padding-left: 1.25rem; margin-bottom: 0.6rem; }
.terms-content :deep(li) { margin-bottom: 0.3rem; }
.terms-content :deep(strong) { color: #fff; }

.terms-actions { text-align: center; }

.terms-checkbox {
    display: flex; align-items: center; gap: 0.5rem; justify-content: center;
    color: rgba(255,255,255,0.8); font-size: 0.85rem; margin-bottom: 0.75rem; cursor: pointer;
}
.terms-checkbox.disabled { opacity: 0.4; cursor: not-allowed; }
.terms-checkbox input { accent-color: #e8445a; width: 18px; height: 18px; }

.scroll-hint {
    color: #e8445a; font-size: 0.8rem; margin-bottom: 0.75rem;
    animation: bounce 1.5s infinite;
}
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(4px); }
}

.terms-buttons {
    display: flex; gap: 0.75rem; justify-content: center;
}
.back-btn {
    background: transparent; border: 1.5px solid rgba(255,255,255,0.3);
    color: #fff; padding: 0.65rem 1.5rem; border-radius: 10px; font-size: 0.9rem;
    font-weight: 500; cursor: pointer; transition: all 0.2s;
}
.back-btn:hover { border-color: #fff; background: rgba(255,255,255,0.05); }

.accept-btn {
    background: #e8445a; border: none; color: #fff; padding: 0.65rem 1.5rem;
    border-radius: 10px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
    transition: all 0.2s;
}
.accept-btn:hover { background: #d03050; }
.accept-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* ─── Footer ─── */
.register-footer {
    margin-top: 1.5rem; color: rgba(255,255,255,0.6); font-size: 0.85rem; text-align: center;
}

/* ─── Responsive ─── */
@media (max-width: 600px) {
    .type-selection { grid-template-columns: 1fr; gap: 1rem; }
    .register-container--wide { max-width: 460px; }
    .register-title { font-size: 1.25rem; }
    .terms-scroll-container { max-height: 280px; }
}
</style>