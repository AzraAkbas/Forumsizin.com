document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('category').addEventListener('click', () => window.location.href = '../category/category.php');
    document.getElementById('save').addEventListener('click', () => window.location.href = '../save/save.php');
    document.getElementById('message').addEventListener('click', () => window.location.href = '../message/message.php');
});
