document.addEventListener("DOMContentLoaded", function () {
  const rememberCheckbox = document.getElementById("remember");
  const cookieBanner = document.getElementById("cookie-banner");

  rememberCheckbox.addEventListener("change", function () {
    const cookiesAccepted = document.cookie.includes("cookies_accepted=true");

    if (this.checked && !cookiesAccepted) {
      cookieBanner.style.display = "block";
    } else {
      cookieBanner.style.display = "none";
    }
  });

  const acceptButton = document.querySelector("button[name='accept']");
  if (acceptButton) {
    acceptButton.addEventListener("click", function (e) {
      e.preventDefault();

      document.cookie =
        "cookies_accepted=true; path=/; max-age=" + 60 * 60 * 24 * 30;

      cookieBanner.style.display = "none";

      alert("Çerezler kabul edildi.");
    });
  }

  const rejectButton = document.querySelector("button[name='reject']");
  if (rejectButton) {
    rejectButton.addEventListener("click", function (e) {
      e.preventDefault();

      document.cookie =
        "cookies_accepted=false; path=/; max-age=" + 60 * 60 * 24 * 30;

      cookieBanner.style.display = "none";

      alert("Çerezler reddedildi.");

      rememberCheckbox.checked = false;
    });
  }

  document.querySelector("form").addEventListener("submit", function (event) {
    const rememberChecked = rememberCheckbox.checked;
    const cookiesAccepted = document.cookie.includes("cookies_accepted=true");

    if (rememberChecked && !cookiesAccepted) {
      alert(
        "'Beni Hatırla' özelliğini kullanabilmek için çerezleri kabul etmeniz gerekir."
      );
      event.preventDefault();
    }
  });
});
