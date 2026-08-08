<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tizimga kirish</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f0f2f5; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { max-width: 900px; width: 100%; }
        .box { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        h1 { margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .btn-primary { background: #4CAF50; color: white; }
        .auth-section { display: flex; gap: 20px; }
        .auth-section .box { flex: 1; }
        .message { padding: 10px; border-radius: 4px; margin-bottom: 10px; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        @media (max-width: 600px) { .auth-section { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align:center;">  Rasm Yuklash Tizimi</h1>
        <div id="messageBox"></div>

        <div class="auth-section">
            <div class="box">
                <h2>Ro'yxatdan o'tish</h2>
                <form id="registerForm">
                    <div class="form-group"><input type="text" id="regName" placeholder="Ism" required></div>
                    <div class="form-group"><input type="email" id="regEmail" placeholder="Email" required></div>
                    <div class="form-group"><input type="password" id="regPassword" placeholder="Parol (min 6)" required></div>
                    <button type="submit" class="btn btn-primary">Ro'yxatdan o'tish</button>
                </form>
            </div>

            <div class="box">
                <h2>Tizimga kirish</h2>
                <form id="loginForm">
                    <div class="form-group"><input type="email" id="loginEmail" placeholder="Email" required></div>
                    <div class="form-group"><input type="password" id="loginPassword" placeholder="Parol" required></div>
                    <button type="submit" class="btn btn-primary">Kirish</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const API = '/api';

        function showMessage(text, type = 'success') {
            const box = document.getElementById('messageBox');
            box.innerHTML = `<div class="message ${type}">${text}</div>`;
            setTimeout(() => box.innerHTML = '', 4000);
        }

        function handleAuthSuccess(data) {
            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            showMessage(data.message);
            setTimeout(() => window.location.href = '/upload', 1000);
        }

        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            btn.disabled = true; btn.textContent = ' ...';

            try {
                const fd = new FormData();
                fd.append('name', document.getElementById('regName').value);
                fd.append('email', document.getElementById('regEmail').value);
                fd.append('password', document.getElementById('regPassword').value);

                const res = await fetch(API + '/register', { method: 'POST', body: fd });
                
                if (!res.ok) {
                    const text = await res.text();
                    throw new Error(`Server xatosi (${res.status}): ${text.substring(0, 100)}`);
                }

                const data = await res.json();

                if (data.success) {
                    handleAuthSuccess(data);
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('Xatolik: ' + error.message, 'error');
            } finally {
                btn.disabled = false; btn.textContent = 'Ro\'yxatdan o\'tish';
            }
        });

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            btn.disabled = true; btn.textContent = ' ...';

            try {
                const fd = new FormData();
                fd.append('email', document.getElementById('loginEmail').value);
                fd.append('password', document.getElementById('loginPassword').value);

                const res = await fetch(API + '/login', { method: 'POST', body: fd });
                
                if (!res.ok) {
                    const text = await res.text();
                    throw new Error(`Server xatosi (${res.status}): ${text.substring(0, 100)}`);
                }

                const data = await res.json();

                if (data.success) {
                    handleAuthSuccess(data);
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('Xatolik: ' + error.message, 'error');
            } finally {
                btn.disabled = false; btn.textContent = 'Kirish';
            }
        });
    </script>
</body>
</html>