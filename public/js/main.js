

function logout() {
    localStorage.removeItem('apiToken');
    localStorage.removeItem('email');
    localStorage.removeItem('pseudo');
    window.location.href = '/login';
}

function updateHeader() {
    const token = localStorage.getItem('apiToken');
    const pseudo = localStorage.getItem('pseudo');

    const navLogin = document.getElementById('nav-login');
    const navUser = document.getElementById('nav-user');
    const navPseudo = document.getElementById('nav-pseudo');

    if (token && pseudo) {
        navLogin?.classList.add('d-none');
        navUser?.classList.remove('d-none');
        if (navPseudo) navPseudo.textContent = `Bonjour, ${pseudo}`;
    } else {
        navLogin?.classList.remove('d-none');
        navUser?.classList.add('d-none');
    }
}

function checkIfAlreadyLoggedIn() {
    const token = localStorage.getItem('apiToken');
    const email = localStorage.getItem('email');

    if (token && email) {
        document.getElementById('connectedMessage')?.classList.remove('d-none');
        document.getElementById('email').textContent = email;
        document.getElementById('loginForm')?.classList.add('d-none');
    }
}

function setupLoginForm() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const response = await fetch('/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        const resultDiv = document.getElementById('loginResult');
        resultDiv.classList.remove('d-none');

        if (response.ok) {
            const data = await response.json();
            localStorage.setItem('apiToken', data.apiToken);
            localStorage.setItem('email', data.user);
            localStorage.setItem('pseudo', data.pseudo);
            window.location.href = '/';
        } else {
            resultDiv.classList.remove('alert-success');
            resultDiv.classList.add('alert-danger');
            resultDiv.textContent = 'Échec de la connexion. Vérifiez vos identifiants.';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    updateHeader();
    checkIfAlreadyLoggedIn();
    setupLoginForm();

    document.getElementById('logout-link')?.addEventListener('click', logout);
});
