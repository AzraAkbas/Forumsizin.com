document.addEventListener('DOMContentLoaded', () => {
    const profile = document.getElementById('profile');
    if (profile) {
        profile.addEventListener('click', () => {
            window.location.href = '../profile_settings/profile_settings.php';
        });
    }

    const category = document.getElementById('category');
    if (category) {
        category.addEventListener('click', () => {
            window.location.href = '../category/category.php';
        });
    }

    const save = document.getElementById('save');
    if (save) {
        save.addEventListener('click', () => {
            window.location.href = '../save/save.php';
        });
    }

    const message = document.getElementById('message');
    if (message) {
        message.addEventListener('click', () => {
            window.location.href = '../message/message.php';
        });
    }
   
});
