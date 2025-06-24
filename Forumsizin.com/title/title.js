 const searchInput = document.getElementById('search_input');
    const suggestionsBox = document.getElementById('suggestions');

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();

        if (query.length === 0) {
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
            return;
        }

        fetch(`?ajax=1&term=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                suggestionsBox.innerHTML = '';
                if (data.length === 0) {
                    suggestionsBox.style.display = 'none';
                    return;
                }

data.forEach(item => {
    const li = document.createElement('li');
    li.textContent = `[${item.type}] ${item.name}`;

    li.addEventListener('click', () => {
        let url = '';
        if (item.type === 'Kategori') {
            url = `../title/title.php?kategori_adi=${encodeURIComponent(item.name)}`;
        } else if (item.type === 'Başlık') {
            url = `../title post/title_post.php?baslik=${encodeURIComponent(item.name)}`;
        } else if (item.type === 'Kullanıcı') {
            url = `../user_profile/user_profile.php?kullanici_adi=${encodeURIComponent(item.name)}`;
        }
        window.location.href = url;
    });

    suggestionsBox.appendChild(li);
});


                suggestionsBox.style.display = 'block';
            })
            .catch(() => {
                suggestionsBox.style.display = 'none';
            });
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });