// password toggle
function goBack() {
  window.location.href = "index.php"; // Routes to landing page
}

function togglePassword() {
  var passwordField = document.getElementById("password");
  var icon = document.querySelector(".toggle-password");

  if (passwordField.type === "password") {
    passwordField.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    passwordField.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}
//  Parallax effect
window.addEventListener("scroll", function () {
  let scrollPosition = window.scrollY * 0.25; // Subtle effect

  let heroSection = document.querySelector(".hero");
  let contactSection = document.querySelector(".contact-parallax");

  if (heroSection) {
    heroSection.style.backgroundPosition = `center ${Math.min(
      scrollPosition,
      60
    )}px`; // Limits movement
  }
  if (contactSection) {
    contactSection.style.backgroundPosition = `center ${Math.min(
      scrollPosition * 0.5, // Increased multiplier
      60
    )}px`; // Allows slight movement
  }
});

// Counter animation
document.addEventListener("DOMContentLoaded", () => {
  const counters = document.querySelectorAll(".counter");
  const duration = 2000; // Animation duration in milliseconds (5 seconds)

  const animateCounters = () => {
    counters.forEach((counter) => {
      const target = +counter.getAttribute("data-target");
      let count = 0;
      let startTime = null;

      const easeOutExpo = (t, b, c, d) => {
        return c * (-Math.pow(2, (-10 * t) / d) + 1) + b;
      };

      const updateCount = (timestamp) => {
        if (!startTime) startTime = timestamp;
        let elapsed = timestamp - startTime;

        if (elapsed < duration) {
          count = easeOutExpo(elapsed, 0, target, duration);
          counter.textContent = Math.ceil(count);
          requestAnimationFrame(updateCount);
        } else {
          counter.textContent = target; // Ensure it reaches the target
        }
      };

      requestAnimationFrame(updateCount);
    });
  };

  // Detect when the section is in viewport
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounters();
          observer.disconnect(); // Stops observing after animation starts
        }
      });
    },
    { threshold: 0.5 }
  );

  observer.observe(document.querySelector(".impact-section"));
});

// Mobile menu toggle
document.addEventListener("DOMContentLoaded", function () {
  const toggleBtn = document.querySelector(".mobile-menu-toggle");
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");

  if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("collapsed");
      mainContent.classList.toggle("expanded");
    });
  }
});

// Chart.js
document.addEventListener("DOMContentLoaded", function () {
  fetch("/pages/admin/dashboard.php") // Adjust path if needed
    .then((response) => response.json())
    .then((data) => {
      const ctx = document
        .getElementById("user-activity-chart")
        .getContext("2d");

      new Chart(ctx, {
        type: "line",
        data: {
          labels: Object.keys(data), // Month names (Jan, Feb, ...)
          datasets: [
            {
              label: "New Users",
              data: Object.values(data), // Count per month
              borderColor: "#4f46e5",
              backgroundColor: "rgba(79, 70, 229, 0.1)",
              tension: 0.3,
              fill: true,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: true,
            },
          },
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      });
    })
    .catch((error) =>
      console.error("Error fetching user activity data:", error)
    );
});
