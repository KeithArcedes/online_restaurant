// General scripts and sidebar functionality
$(document).ready(function() {
  // Initialize all tooltips
  $('[data-bs-toggle="tooltip"]').tooltip();
  
  // Toggle sidebar on mobile
  document.getElementById("sidebarToggle").addEventListener("click", function() {
      document.querySelector(".sidebar").classList.toggle("active");
  });

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", function(event) {
      const sidebar = document.querySelector(".sidebar");
      const sidebarToggle = document.getElementById("sidebarToggle");

      if (window.innerWidth <= 768 && 
          !sidebar.contains(event.target) && 
          event.target !== sidebarToggle && 
          !sidebarToggle.contains(event.target)) {
          sidebar.classList.remove("active");
      }
  });
});

