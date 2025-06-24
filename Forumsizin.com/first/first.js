document.addEventListener("DOMContentLoaded", () => {
  document
    .getElementById("login")
    .addEventListener(
      "click",
      () => (window.location.href = "../login/login.php")
    );
  document
    .getElementById("register")
    .addEventListener(
      "click",
      () => (window.location.href = "../register/register.php")
    );
  const loginModal = new bootstrap.Modal(
    document.getElementById("loginAlertModal")
  );

  document.querySelectorAll(".profile a").forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      loginModal.show();
    });
  });

  document.querySelectorAll(".icons span").forEach((icon) => {
    icon.addEventListener("click", (e) => {
      e.preventDefault();
      loginModal.show();
    });
  });
});
