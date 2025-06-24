
    fetch("../islemler php/kaydedilenler.php")
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const kaydedilenler = data.kaydedilenler.map(id => String(id));
                document.querySelectorAll(".bookmark-icon").forEach(function (bookmark) {
                    const postCard = bookmark.closest(".post");
                    const fotografId = postCard.querySelector(".comment-icon").getAttribute("data-fotograf-id");
                    if (kaydedilenler.includes(fotografId)) {
                        bookmark.textContent = "bookmark_added";
                        bookmark.classList.add("active-bookmark");
                    } else {
                        bookmark.textContent = "bookmark";
                        bookmark.classList.remove("active-bookmark");
                    }
                });
            }
        });

    document.querySelectorAll(".bookmark-icon").forEach(function (bookmark) {
        bookmark.style.cursor = "pointer";
        bookmark.addEventListener("click", function (e) {
            const postCard = e.target.closest(".post");
            const fotografId = postCard.querySelector(".comment-icon").getAttribute("data-fotograf-id");

            fetch("../islemler php/kaydet.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "fotograf_id=" + encodeURIComponent(fotografId)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (bookmark.textContent.trim() === "bookmark") {
                            bookmark.textContent = "bookmark_added";
                            bookmark.classList.add("active-bookmark");
                        } else {
                            bookmark.textContent = "bookmark";
                            bookmark.classList.remove("active-bookmark");
                        }
                    }
                })
                .catch(() => {
                    console.error("Kaydetme sırasında hata oluştu.");
                });
        });
    });

   
