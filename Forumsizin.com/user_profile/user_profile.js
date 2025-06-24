document.getElementById("follow").addEventListener("click", function() {
    const button = this;
    const userId = button.getAttribute("data-user-id");
    const followerCountEl = document.getElementById("follower-count");
    let followerCount = parseInt(followerCountEl.innerText);

    fetch("follow.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "takip_edilecek_id=" + encodeURIComponent(userId),
        credentials: "same-origin"
    })
    .then(response => response.text())
    .then(data => {
        if (data === "takip_edildi") {
            button.innerText = "Takiptesin";
            followerCountEl.innerText = followerCount + 1;
        } else if (data === "takipten_cikildi") {
            button.innerText = "Takip Et";
            followerCountEl.innerText = followerCount - 1;
        } else if (data === "kendi_kendini_takip_edemez") {
            alert("Kendini takip edemezsin.");
        } else if (data === "giris_yok") {
            alert("Lütfen giriş yapınız.");
        } else {
            alert("İşlem gerçekleştirilemedi.");
        }
    })
    .catch(error => {
        console.error("Hata:", error);
        alert("İşlem gerçekleştirilemedi.");
    });
});

