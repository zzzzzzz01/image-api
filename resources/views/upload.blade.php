<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rasm Yuklash</title>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .box { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        h1 { margin-bottom: 15px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-primary { background: #4CAF50; color: white; }
        .btn-danger { background: #f44336; color: white; }
        .btn-secondary { background: #666; color: white; }
        .message { padding: 10px; border-radius: 4px; margin-bottom: 10px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        #imageGrid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .image-card { background: white; border-radius: 8px; overflow: hidden; }
        .image-card img { width: 100%; height: 150px; object-fit: cover; }
        .image-card .info { padding: 10px; }
        .image-card .info button { width: 100%; margin-top: 5px; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .user-info { display: flex; justify-content: space-between; align-items: center; }
        .upload-area { border: 2px dashed #ccc; padding: 40px; text-align: center; border-radius: 8px; cursor: pointer; }
        .upload-area input { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <!-- 📸 o'rniga ikonka -->
            <h1><i class="fa-solid fa-camera"></i> Rasm Yuklash</h1>
            <div class="user-info">
                <!-- ✅ o'rniga ikonka -->
                <span id="userStatus"><i class="fa-solid fa-circle-check" style="color: #4CAF50;"></i> Tizimdasiz</span>
                <button class="btn btn-secondary" id="logoutBtn"><i class="fa-solid fa-right-from-bracket"></i> Chiqish</button>
            </div>
        </div>

        <div id="messageBox"></div>

        <div class="box" id="uploadSection">
            <h2>Rasm yuklash</h2>
            <div class="upload-area" id="dropArea">
                <p><i class="fa-regular fa-image" style="font-size: 28px;"></i><br>Rasmni shu yerga tashlang yoki bosing</p>
                <p style="font-size:12px;color:#999;">PNG, JPEG - 5MB gacha</p>
                <input type="file" id="fileInput" accept=".png,.jpg,.jpeg">
            </div>
            <div id="fileInfo" style="margin-top: 10px; font-weight: bold;"></div>
            <button class="btn btn-primary" id="uploadBtn" style="display:none;margin-top:10px;"><i class="fa-solid fa-cloud-arrow-up"></i> Yuklash</button>
        </div>

        <div class="box" id="imagesSection">
            <h2>Mening rasmlarim</h2>
            <div id="imageGrid"></div>
        </div>
    </div>

    <script>
        const API = '/api'; 
        const MAX_SIZE = 5 * 1024 * 1024;
        const ALLOWED = ['image/png', 'image/jpeg', 'image/jpg'];

        function getToken() { return localStorage.getItem('token'); }
        function getUser() { return JSON.parse(localStorage.getItem('user')); }

        (function checkAuth() {
            if (!getToken()) {
                window.location.href = '/'; 
            }
        })();

        function showMessage(text, type = 'success') {
            const box = document.getElementById('messageBox');
            box.innerHTML = `<div class="message ${type}">${text}</div>`;
            setTimeout(() => box.innerHTML = '', 4000);
        }

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            try {
                const res = await fetch(API + '/logout', { 
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + getToken() }
                });
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/';
            } catch (error) {
                showMessage('Xatolik: ' + error.message, 'error');
            }
        });

        const fileInput = document.getElementById('fileInput');
        const dropArea = document.getElementById('dropArea');
        const fileInfo = document.getElementById('fileInfo');
        const uploadBtn = document.getElementById('uploadBtn');
        let selectedFile = null;

        dropArea.addEventListener('click', () => fileInput.click());
        dropArea.addEventListener('dragover', (e) => e.preventDefault());
        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFile(e.dataTransfer.files[0]);
            }
        });
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) handleFile(e.target.files[0]);
        });

        function handleFile(file) {
            if (!ALLOWED.includes(file.type)) {
                showMessage('Faqat PNG va JPEG rasm yuklash mumkin!', 'error');
                return;
            }
            if (file.size > MAX_SIZE) {
                showMessage('Rasm 5MB dan katta bo\'lmasligi kerak!', 'error');
                return;
            }
            selectedFile = file;
            // 📎 o'rniga ikonka
            fileInfo.innerHTML = `<i class="fa-solid fa-paperclip"></i> ${file.name} (${(file.size/1024).toFixed(2)} KB)`;
            uploadBtn.style.display = 'inline-block';
        }

        uploadBtn.addEventListener('click', async () => {
            if (!selectedFile) return;
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Yuklanmoqda...';

            try {
                const fd = new FormData();
                fd.append('image', selectedFile);

                const res = await fetch(API + '/upload', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + getToken() },
                    body: fd
                });

                const data = await res.json();

                if (data.success) {
                    showMessage(data.message);
                    fileInfo.innerHTML = '';
                    uploadBtn.style.display = 'none';
                    selectedFile = null;
                    fileInput.value = '';
                    loadImages();
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('Xatolik: ' + error.message, 'error');
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Yuklash';
            }
        });

        async function loadImages() {
            try {
                const res = await fetch(API + '/images', {
                    headers: { 'Authorization': 'Bearer ' + getToken() }
                });

                const data = await res.json();
                const grid = document.getElementById('imageGrid');

                if (data.success && data.images && data.images.length > 0) {
                    grid.innerHTML = data.images.map(img => `
                        <div class="image-card">
                            <img src="${img.url}" alt="${img.name}">
                            <div class="info">
                                <p><strong>${img.name}</strong></p>
                                <!-- 📦 o'rniga ikonka, 📅 o'rniga ikonka -->
                                <p><i class="fa-solid fa-weight-hanging"></i> ${img.size}</p>
                                <p><i class="fa-regular fa-calendar"></i> ${img.date}</p>
                                <button class="btn btn-danger" onclick="deleteImage(${img.id})"><i class="fa-solid fa-trash-can"></i> O'chirish</button>
                            </div>
                        </div>
                    `).join('');
                } else {
                    grid.innerHTML = '<p style="text-align:center;padding:40px;color:#999;">Hali rasm yuklanmagan</p>';
                }
            } catch (error) {
                document.getElementById('imageGrid').innerHTML = '<p style="text-align:center;padding:40px;color:#999;">Xatolik yuz berdi</p>';
            }
        }

        window.deleteImage = async function(id) {
            if (!confirm('Rasmni o\'chirishga ishonchingiz komilmi?')) return;

            try {
                const res = await fetch(API + '/delete/' + id, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + getToken() }
                });

                const data = await res.json();

                if (data.success) {
                    showMessage(data.message);
                    loadImages();
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('Xatolik: ' + error.message, 'error');
            }
        };

        loadImages();
    </script>
</body>
</html>