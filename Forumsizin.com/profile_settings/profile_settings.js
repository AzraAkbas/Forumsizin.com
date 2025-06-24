function previewImage() {
  const profileImageInput = document.getElementById("profile_image");
  const profileImage = document.getElementById("profile-img");

  const file = profileImageInput.files[0];

  if (file) {
    const reader = new FileReader();

    reader.onload = function (e) {
      profileImage.src = e.target.result;
    };

    reader.readAsDataURL(file);
  }
}
