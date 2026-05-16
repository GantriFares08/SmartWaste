let currentStep = 1;

function goStep(n) {
    document.getElementById('panel-' + currentStep).classList.remove('active');
    document.getElementById('panel-' + n).classList.add('active');

    for (let i = 1; i <= 2; i++) {
        const c = document.getElementById('circle-' + i);
        const l = document.getElementById('label-' + i);
        c.className = 'step-circle';
        l.className = 'step-label';
        if (i < n) {
            c.classList.add('done');
            c.innerHTML = '<i class="ti ti-check" style="font-size:13px"></i>';
        } else if (i === n) {
            c.classList.add('active');
            c.textContent = i;
            l.classList.add('active');
        } else {
            c.textContent = i;
        }
    }

    document.getElementById('line-1').className = 'step-line' + (n > 1 ? ' done' : '');
    currentStep = n;
}

function validateStep1() {
    const prenom = document.getElementById('prenom').value.trim();
    const nom    = document.getElementById('nom').value.trim();
    const email  = document.getElementById('email').value.trim();
    if (!prenom || !nom || !email) {
        alert('Veuillez remplir tous les champs obligatoires.');
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Adresse email invalide.');
        return;
    }
    goStep(2);
}

function checkStrength(pw) {
    const fill  = document.getElementById('strength-fill');
    const label = document.getElementById('strength-label');
    let score = 0;
    if (pw.length >= 8)           score++;
    if (/[A-Z]/.test(pw))         score++;
    if (/[0-9]/.test(pw))         score++;
    if (/[^A-Za-z0-9]/.test(pw))  score++;
    const levels = [
        { w: '0%',   color: '#eee',    text: '—' },
        { w: '25%',  color: '#E24B4A', text: 'Faible' },
        { w: '50%',  color: '#BA7517', text: 'Moyen' },
        { w: '75%',  color: '#1D9E75', text: 'Fort' },
        { w: '100%', color: '#0F6E56', text: 'Très fort' }
    ];
    const lvl = levels[score];
    fill.style.width      = lvl.w;
    fill.style.background = lvl.color;
    label.textContent     = pw.length === 0 ? '—' : lvl.text;
    label.style.color     = lvl.color;
}

function togglePw(id, btn) {
    const inp  = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type       = 'text';
        icon.className = 'ti ti-eye-off';
    } else {
        inp.type       = 'password';
        icon.className = 'ti ti-eye';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const pw2Input = document.getElementById('password2');
    if(pw2Input) {
        pw2Input.addEventListener('input', function () {
            const pw1 = document.getElementById('password').value;
            const msg = document.getElementById('pw-match-msg');
            if (!this.value) { msg.textContent = ''; return; }
            if (pw1 === this.value) {
                msg.textContent = '✓ Les mots de passe correspondent';
                msg.style.color = '#1D9E75';
            } else {
                msg.textContent = '✗ Les mots de passe ne correspondent pas';
                msg.style.color = '#E24B4A';
            }
        });
    }
});

function handleRegister() {
    const pw1 = document.getElementById('password').value;
    const pw2 = document.getElementById('password2').value;
    const cgu = document.getElementById('cgu').checked;
    if (!pw1 || pw1.length < 6) {
        alert('Le mot de passe doit contenir au moins 6 caractères.');
        return;
    }
    if (pw1 !== pw2) {
        alert('Les mots de passe ne correspondent pas.');
        return;
    }
    if (!cgu) {
        alert("Vous devez accepter les conditions d'utilisation.");
        return;
    }
    document.getElementById('success-alert').style.display = 'block';
    setTimeout(function () {
        document.getElementById('registerForm').submit();
    }, 1500);
}
