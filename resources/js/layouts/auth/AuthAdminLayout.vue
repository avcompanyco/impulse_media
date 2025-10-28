<template>
    <div class="app-container">
        <img src="assets/img/logo.png" alt="Logo" class="logo-icon">

        <h1 class="login-title">ADMIN ACCESS</h1>

        <div class="form-container">
            <form id="adminLoginForm">
                <div class="form-group">
                    <label for="adminEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="adminEmail" placeholder="Your administrator email">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" placeholder="******************">
                </div>

                <a href="password.html" class="forgot-password-link">Forgot your password?</a>

                <button type="submit" class="btn login-button">LOG IN</button>
            </form>
        </div>
    </div>
</template>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #0A0A23;
    color: white;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.app-container {
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.logo-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 2rem;
}

.login-title {
    font-size: 2rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 4rem;
    letter-spacing: -0.5px;
}

.form-container {
    width: 100%;
}

.form-group {
    margin-bottom: 2rem;
}

.form-label {
    color: white;
    font-size: 1.25rem;
    font-weight: 500;
    margin-bottom: 0.875rem;
    display: block;
}

.form-control {
    background-color: #E8E8E8;
    border: none;
    border-radius: 30px;
    padding: 1rem 1.75rem;
    font-size: 1.1rem;
    height: auto;
    color: #333;
    width: 100%;
}

.form-control::placeholder {
    color: #666;
}

.form-control:focus {
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.25);
    background-color: #fff;
}

.forgot-password-link {
    display: block;
    text-align: right;
    margin-top: -1rem;
    margin-bottom: 2rem;
    font-size: 0.9rem;
    color: #ccc;
    text-decoration: none;
    transition: color 0.2s;
}

.forgot-password-link:hover {
    color: white;
    text-decoration: underline;
}

.login-button {
    background-color: #DC3545;
    color: white;
    border: none;
    border-radius: 30px;
    padding: 1rem 2rem;
    font-size: 1.125rem;
    font-weight: 500;
    width: 100%;
    max-width: 200px;
    display: block;
    margin: 3rem auto 0;
    transition: all 0.3s ease;
}

.login-button:hover {
    transform: scale(1.02);
    background-color: #bb2d3b;
}

/* Tablet */
@media (min-width: 768px) {
    .app-container {
        max-width: 480px;
    }

    .logo-icon {
        width: 56px;
        height: 56px;
        margin-bottom: 2.5rem;
    }

    .login-title {
        font-size: 2.25rem;
    }
}

/* Desktop */
@media (min-width: 1200px) {
    .app-container {
        max-width: 520px;
        padding: 0;
    }

    .logo-icon {
        width: 64px;
        height: 64px;
        margin-bottom: 3rem;
    }

    .login-title {
        font-size: 2.5rem;
        margin-bottom: 5rem;
    }

    .form-group {
        margin-bottom: 2.5rem;
    }

    .login-button {
        margin-top: 4rem;
    }
}

/* Mobile small */
@media (max-width: 400px) {
    .app-container {
        padding: 1.5rem;
    }

    .login-title {
        font-size: 1.75rem;
        margin-bottom: 3rem;
    }

    .form-control {
        padding: 0.875rem 1.5rem;
        font-size: 1rem;
    }

    .form-label {
        font-size: 1.125rem;
    }

    .login-button {
        margin-top: 2.5rem;
        padding: 0.875rem 1.75rem;
    }
}
</style>