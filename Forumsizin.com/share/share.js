function previewImage() {
    const preview = document.getElementById('preview');
    const file = document.getElementById('imageUpload').files[0];
    if (file) {
        preview.src = URL.createObjectURL(file);
    }
}
