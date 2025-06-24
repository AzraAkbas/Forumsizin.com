
    window.toggleEmojiBox = function (fotografId, iconElement) {
        const boxId = `emojiBox-${fotografId}`;
        const box = document.getElementById(boxId);

        if (box.style.display === "block") {
            box.style.display = "none";
            return;
        }

        box.innerHTML = "";
        box.style.display = "block";

        fetch("../islemler php/get_emoji.php")
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) {
                    box.innerHTML = "<span>Hiç emoji yok</span>";
                    return;
                }

                data.forEach(emoji => {
                    const span = document.createElement("span");
                    span.textContent = emoji.simge;
                    span.className = "emoji";
                    span.style.fontSize = "24px";
                    span.style.margin = "5px";
                    span.style.cursor = "pointer";
                    span.onclick = () => {
                        iconElement.textContent = emoji.simge;
                        box.style.display = "none";

                        fetch("../islemler php/emoji.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `fotograf_id=${fotografId}&emoji_id=${emoji.emoji_id}`
                        })
                            .then(response => response.text())
                            .then(text => {
                                try {
                                    const result = JSON.parse(text);
                                    if (result.success) {
                                        console.log("Emoji kaydedildi");
                                    } else {
                                        console.warn("Emoji kaydedilemedi:", result.message);
                                    }
                                } catch (e) {
                                    console.error("Sunucudan geçersiz yanıt alındı:", text);
                                }
                            })
                            .catch(error => {
                                console.error("Emoji kayıt hatası:", error);
                            });
                    };
                    box.appendChild(span);
                });
            })
            .catch(error => {
                console.error("Emoji verisi alınamadı", error);
                box.innerHTML = "<span>Emoji yüklenemedi</span>";
            });
    }