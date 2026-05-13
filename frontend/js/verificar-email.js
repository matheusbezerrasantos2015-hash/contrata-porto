import { API_URL } from './config.js';
import { showToast } from './ui.js';

const email = sessionStorage.getItem('pending_verification_email');
const userEmailSpan = document.getElementById('userEmail');
const inputs = document.querySelectorAll('.code-input');
const btnVerify = document.getElementById('btnVerify');
const resendLink = document.getElementById('resendLink');
const resendTimer = document.getElementById('resendTimer');
const secondsSpan = document.getElementById('seconds');
const messageDiv = document.getElementById('message');

if (!email) {
    window.location.href = './login.html';
} else {
    userEmailSpan.textContent = email;
}

// Verifica se veio do login com mensagem de "não verificado"
const params = new URLSearchParams(window.location.search);
if (params.get('msg') === 'unverified') {
    showMessage('Você ainda não confirmou seu e-mail. Use o código enviado anteriormente ou solicite um novo.', 'error');
}

// Lógica de Inputs
inputs.forEach((input, index) => {
    input.addEventListener('keyup', (e) => {
        if (e.key >= 0 && e.key <= 9) {
            if (index < inputs.length - 1) inputs[index + 1].focus();
        } else if (e.key === 'Backspace') {
            if (index > 0) inputs[index - 1].focus();
        }
    });

    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasteData = e.clipboardData.getData('text').slice(0, 6).split('');
        pasteData.forEach((char, i) => {
            if (inputs[i]) inputs[i].value = char;
        });
        if (pasteData.length > 0) {
            const nextIndex = Math.min(pasteData.length, inputs.length - 1);
            inputs[nextIndex].focus();
        }
    });
});

// Timer de Reenvio
let timeLeft = 60;
const startTimer = () => {
    resendLink.style.display = 'none';
    resendTimer.style.display = 'inline';
    timeLeft = 60;
    const interval = setInterval(() => {
        timeLeft--;
        secondsSpan.textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(interval);
            resendTimer.style.display = 'none';
            resendLink.style.display = 'inline';
            resendLink.classList.remove('disabled');
        }
    }, 1000);
};
startTimer();

// Ações
btnVerify.addEventListener('click', async () => {
    const code = Array.from(inputs).map(i => i.value).join('');
    if (code.length !== 6) {
        showMessage('Digite o código de 6 dígitos.', 'error');
        return;
    }

    btnVerify.disabled = true;
    btnVerify.textContent = 'Verificando...';

    try {
        const res = await fetch(`${API_URL}/auth/verify-email`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, code })
        });

        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Erro ao verificar código.');

        showMessage('E-mail verificado com sucesso! Redirecionando...', 'success');
        
        // Salva credenciais (login automático)
        localStorage.setItem('token', json.data.auth.token);
        localStorage.setItem('user_id', json.data.usuario.id);
        localStorage.setItem('user_role', json.data.usuario.role.toLowerCase());
        localStorage.setItem('user', JSON.stringify(json.data.usuario));

        setTimeout(() => {
            const role = json.data.usuario.role.toLowerCase();
            window.location.href = role === 'empresa' ? './dashboard.html' : './candidate-dashboard.html';
        }, 2000);

    } catch (err) {
        showMessage(err.message, 'error');
        btnVerify.disabled = false;
        btnVerify.textContent = 'Confirmar código';
    }
});

resendLink.addEventListener('click', async () => {
    if (resendLink.classList.contains('disabled')) return;

    try {
        const res = await fetch(`${API_URL}/auth/resend-verification`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });

        const json = await res.json();
        if (!res.ok) throw new Error(json.message);

        showToast('Novo código enviado com sucesso!', 'success');
        startTimer();
    } catch (err) {
        showToast(err.message, 'error');
    }
});

function showMessage(text, type) {
    messageDiv.textContent = text;
    messageDiv.className = `message ${type}`;
}
