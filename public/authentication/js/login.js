let selectedRole = 'admin';

function selectRole(role) {
    selectedRole = role;
    document.getElementById('btn-admin').classList.toggle('active', role === 'admin');
    document.getElementById('btn-user').classList.toggle('active', role === 'user');
}

function handleLogin(event) {
    event.preventDefault();
    if (selectedRole === 'admin') {
        window.location.href = 'admin/index.html';
    } else {
        window.location.href = 'citoyen/index.html';
    }
}
