<script setup lang="ts">
import { dashboard } from '@/routes';
import ShowRegisterUserController from '@/actions/App/Http/Controllers/Auth/User/ShowRegisterUserController';
import { user as loginUser } from '@/routes/login';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>

    <Head :title="`Welcome - ${$page.props.name || 'Impulsemedia'}`">
    </Head>
    <div class="app-container">
        <Link :href="loginUser()" class="btn home-login-button" role="button">Log In</Link>

        <img src="/images/logo.png" alt="Logo" class="logo-icon">

        <h1 class="welcome-title">The best content</h1>
        <h2 class="welcome-subtitle">ALL IN ONE</h2>

        <Link :href="ShowRegisterUserController()" class="btn start-button" role="button">Start now</Link>
    </div>
</template>

<style scoped>
.app-container {
    position: relative;
    width: 100% !important;
    max-width: 100% !important;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.home-login-button {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: white;
    color: #0A0A23;
    border: none;
    border-radius: 30px;
    padding: 0.5rem 2rem;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.home-login-button:hover {
    transform: scale(1.05);
    background-color: white;
    color: #0A0A23;
}

.logo-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 2.5rem;
}

.welcome-title {
    font-size: 2rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 0.5rem;
    letter-spacing: -0.5px;
}

.welcome-subtitle {
    font-size: 1.75rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 3rem;
    letter-spacing: -0.5px;
}

.start-button {
    background-color: #DC3545;
    color: white;
    border: none;
    border-radius: 30px;
    padding: 0.875rem 3rem;
    font-size: 1.1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.start-button:hover {
    transform: scale(1.05);
    background-color: #bb2d3b;
    color: white;
}

/* Tablet */
@media (min-width: 768px) {
    .logo-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 3rem;
    }

    .welcome-title {
        font-size: 2.5rem;
    }

    .welcome-subtitle {
        font-size: 2.25rem;
    }

    .start-button {
        padding: 1rem 3.5rem;
        font-size: 1.2rem;
    }
}

/* Desktop */
@media (min-width: 1200px) {
    .logo-icon {
        width: 72px;
        height: 72px;
        margin-bottom: 3.5rem;
    }

    .welcome-title {
        font-size: 3rem;
    }

    .welcome-subtitle {
        font-size: 2.75rem;
    }

    .start-button {
        padding: 1.125rem 4rem;
        font-size: 1.25rem;
    }

    .home-login-button {
        padding: 0.625rem 2.5rem;
        font-size: 1.1rem;
    }
}

/* Mobile small */
@media (max-width: 400px) {
    .app-container {
        padding: 1.5rem;
    }

    .welcome-title {
        font-size: 1.75rem;
    }

    .welcome-subtitle {
        font-size: 1.5rem;
    }

    .home-login-button {
        padding: 0.4rem 1.75rem;
        font-size: 0.9rem;
    }
}
</style>
