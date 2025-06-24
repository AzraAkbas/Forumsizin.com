
function validateForm() {
    let isValid = true;
    let errorMessages = '';

    document.getElementById("errorMessages").innerHTML = '';


    let email = document.getElementById("email").value;
    if (email === "" || email.indexOf("@") === -1 || email.indexOf(".") === -1) {
        errorMessages += "<p>Geçerli bir e-posta girin.</p>";
        isValid = false;
    }

    let username = document.getElementById("username").value;
    if (username.length < 3) {
        errorMessages += "<p>Kullanıcı adı en az 3 karakter olmalı.</p>";
        isValid = false;
    }


    var password = document.getElementById("password").value;
    if (password.length < 8) {
        errorMessages += "<p>Şifre en az 8 karakter olmalı.</p>";
        isValid = false;
    }

    var harfVar = false;
    var sayiVar = false;

    for (var i = 0; i < password.length; i++) {
        var ch = password[i];
        if ((ch >= "a" && ch <= "z") || (ch >= "A" && ch <= "Z")) {
            harfVar = true;
        }
        if (ch >= "0" && ch <= "9") {
            sayiVar = true;
        }
    }

    if (!harfVar || !sayiVar) {
        errorMessages += "<p>Şifre en az bir harf ve bir sayı içermelidir.</p>";
        isValid = false;
    }

    let birthdate = document.getElementById("birthdate").value;
    let parts = birthdate.split("/");

    if (parts.length !== 3) {
        errorMessages += "<p>Doğum tarihi doğru formatta değil (gg/aa/yyyy).</p>";
        isValid = false;
    } else {
        let gun = parseInt(parts[0]);
        let ay = parseInt(parts[1]) - 1; 
        let yil = parseInt(parts[2]);

        let dogumTarihi = new Date(yil, ay, gun);
        if (dogumTarihi.getFullYear() !== yil || dogumTarihi.getMonth() !== ay || dogumTarihi.getDate() !== gun) {
            errorMessages += "<p>Geçerli bir doğum tarihi girin.</p>";
            isValid = false;
        }

        let bugun = new Date();
        let yas = bugun.getFullYear() - yil;
        let m = bugun.getMonth() - ay;

        if (m < 0 || (m === 0 && bugun.getDate() < gun)) {
            yas--;
        }

        if (yas < 13) {
            errorMessages += "<p>13 yaşından küçükler kayıt olamaz.</p>";
            isValid = false;
        }
    }

    if (!isValid) {
        document.getElementById("errorMessages").innerHTML = errorMessages;
        document.getElementById("errorMessages").style.display = 'block'; 
        document.body.classList.add('error-active'); 
    } else {
        document.body.classList.remove('error-active');
    }

    return isValid;
}

function formatDate(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }
    if (value.length > 5) {
        value = value.substring(0, 5) + '/' + value.substring(5, 9);
    }
    input.value = value;
}

document.getElementById('registerForm').addEventListener('submit', function (e) {
    let birthdateInput = document.getElementById('birthdate');
    let parts = birthdateInput.value.split('/');

    if (parts.length === 3) {
        let gun = parts[0];
        let ay = parts[1];
        let yil = parts[2];

        birthdateInput.value = `${yil}-${ay}-${gun}`;
    }
});

