
document.querySelectorAll('.toggle-password').forEach(toggle => {
    toggle.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
const container = document.querySelector('.upload-image-container');
const fileInput = document.getElementById('profilePicture');
const preview = document.getElementById('image-preview');
const uploadInterface = document.getElementById('upload-interface');
const deleteBtn = document.getElementById('delete-image');
const uploadBtn = document.getElementById('file-upload-btn');

// Handle file upload button click
uploadBtn.addEventListener('click', () => fileInput.click());

// Handle file selection
fileInput.addEventListener('change', handleFileSelect);

// Handle drag and drop
container.addEventListener('dragover', (e) => {
    e.preventDefault();
    container.classList.add('dragover');
});

container.addEventListener('dragleave', () => {
    container.classList.remove('dragover');
});

container.addEventListener('drop', (e) => {
    e.preventDefault();
    container.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length) {
        fileInput.files = files;
        handleFileSelect({ target: fileInput });
    }
});

// Handle delete button
deleteBtn.addEventListener('click', () => {
    preview.style.display = 'none';
    uploadInterface.style.display = 'block';
    deleteBtn.style.display = 'none';
    fileInput.value = '';
});

function handleFileSelect(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            uploadInterface.style.display = 'none';
            deleteBtn.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}
});


