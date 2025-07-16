document.addEventListener("DOMContentLoaded", () => {
  // Elementos DOM
  const navbar = document.querySelector(".navbar");
  const bioText = document.getElementById("bioText");
  const bioToggle = document.getElementById("bioToggle");
  const toggleBtns = document.querySelectorAll(".toggle-btn");
  const contentSections = document.querySelectorAll(".content-section");
  let lastScroll = 0;

  // Função para alternar a bio
  function toggleBio() {
    const bioText = document.getElementById("bioText");
    const bioToggle = document.getElementById("bioToggle");

    if (bioText.classList.contains("expanded")) {
      bioText.classList.remove("expanded");
      bioToggle.textContent = "Mostrar mais";
      // Scroll suave para a bio se necessário
      bioText.scrollIntoView({ behavior: "smooth", block: "nearest" });
    } else {
      bioText.classList.add("expanded");
      bioToggle.textContent = "Mostrar menos";
    }
  }

  // Event listener para o botão de bio
  bioToggle.addEventListener("click", toggleBio);

  // Controle da navbar durante o scroll
  window.addEventListener("scroll", () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll > lastScroll && currentScroll > 100) {
      navbar.style.transform = "translateY(-100%)";
    } else {
      navbar.style.transform = "translateY(0)";
    }
    navbar.style.transition = "transform 0.3s ease";
    lastScroll = currentScroll;
  });
});
