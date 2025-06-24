    const commentModal = document.getElementById("commentModal");
    const commentList = document.getElementById("commentList");
    const commentInput = document.getElementById("commentInput");
    const commentSubmit = document.getElementById("commentSubmit");
    let aktifFotografId = null;


function loadComments(fotografId) {
        commentList.innerHTML = "<p>Yorumlar yükleniyor...</p>";
        fetch(`../islemler php/yorum_getir.php?fotograf_id=${fotografId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.yorumlar.length === 0) {
                        commentList.innerHTML = "<p>Henüz yorum yok.</p>";
                        return;
                    }
                    commentList.innerHTML = "";
                    data.yorumlar.forEach(yorum => {
                        const div = document.createElement("div");
                        div.classList.add("comment-item", "mb-2", "border-bottom", "pb-2");
                        div.innerHTML = `
                            <strong>${yorum.kullanici_adi}</strong> <small class="text-muted">${yorum.tarih}</small>
                            <span
                                class="material-symbols-outlined report-icon float-end yorum-bildir-btn"
                                data-yorum-id="${yorum.yorum_id}"
                                style="cursor:pointer"
                                title="Yorumu bildir"
                                data-bs-toggle="modal"
                                data-bs-target="#yorumReportModal"
                            >report_problem</span>
                            <p>${yorum.yorum_icerik}</p>
                        `;
                        commentList.appendChild(div);
                    });
                } else {
                    commentList.innerHTML = "<p>Yorumlar yüklenemedi.</p>";
                }
            })
            .catch(() => {
                commentList.innerHTML = "<p>Yorumlar yüklenemedi.</p>";
            });
    }

    commentModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        aktifFotografId = button ? button.getAttribute("data-fotograf-id") : null;
        commentInput.value = "";
        if (aktifFotografId) {
            loadComments(aktifFotografId);
        } else {
            commentList.innerHTML = "<p>Fotoğraf bilgisi bulunamadı.</p>";
        }
    });

    commentSubmit.addEventListener("click", function () {
        const yorum = commentInput.value.trim();
        if (yorum === "") {
            alert("Yorum boş olamaz.");
            return;
        }

        commentSubmit.disabled = true;
        fetch("../islemler php/yorum.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `fotograf_id=${encodeURIComponent(aktifFotografId)}&yorum=${encodeURIComponent(yorum)}`
        })
            .then(res => res.json())
            .then(data => {
                commentSubmit.disabled = false;
                if (data.success) {
                    commentInput.value = "";
                    loadComments(aktifFotografId);
                } else {
                    alert("Hata: " + data.message);
                }
            })
            .catch(() => {
                commentSubmit.disabled = false;
                alert("Yorum gönderilirken hata oluştu.");
            });
    });

   

