document.addEventListener("DOMContentLoaded", function () {
  const audienceSelect = document.getElementById("target_audience");
  const orgContainer = document.getElementById("organizations_container");
  const roleContainer = document.getElementById("roles_container");

  audienceSelect.addEventListener("change", function () {
    if (this.value === "organizations") {
      orgContainer.classList.remove("d-none");
      roleContainer.classList.add("d-none");
    } else if (this.value === "roles") {
      orgContainer.classList.add("d-none");
      roleContainer.classList.remove("d-none");
    } else {
      orgContainer.classList.add("d-none");
      roleContainer.classList.add("d-none");
    }
  });

  // Preview announcement
  document.getElementById("previewBtn").addEventListener("click", function () {
    const title =
      document.getElementById("title").value || "Announcement Title";
    const message =
      document.getElementById("message").value ||
      "Your announcement message will appear here...";
    const target = audienceSelect.value;

    let targetText = "All Users";
    if (target === "organizations") {
      let selectedOrgs = [];
      document
        .querySelectorAll("input[name='target_organizations[]']:checked")
        .forEach((checkbox) => {
          selectedOrgs.push(checkbox.nextElementSibling.textContent.trim());
        });
      targetText = selectedOrgs.length
        ? "Organizations: " + selectedOrgs.join(", ")
        : "No organizations selected";
    } else if (target === "roles") {
      let selectedRoles = [];
      document
        .querySelectorAll("input[name='target_roles[]']:checked")
        .forEach((checkbox) => {
          selectedRoles.push(checkbox.nextElementSibling.textContent.trim());
        });
      targetText = selectedRoles.length
        ? "Roles: " + selectedRoles.join(", ")
        : "No roles selected";
    }

    document.getElementById("preview-title").textContent = title;
    document.getElementById("preview-target").textContent = targetText;
    document.getElementById("preview-message").textContent = message;
    document.getElementById("preview-tab").click();
  });
});

document
  .getElementById("announcementForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    let formData = new FormData(this);

    fetch("../pages/notifications/send_announcement.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        alert(data.message);
        if (data.status === "success") location.reload();
      })
      .catch((error) => console.error("Error:", error));
  });
