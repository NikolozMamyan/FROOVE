document.addEventListener("DOMContentLoaded", function () {
    let lastScrollTop = 0;
    const navbarHeader = document.querySelector(".navbar-header");

   const delta = 10; // Tolérance
window.addEventListener("scroll", function () {
const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

if (Math.abs(lastScrollTop - currentScroll) <= delta) {
    return;
}

if (currentScroll > lastScrollTop) {
    navbarHeader.classList.add("hidden");
} else {
    navbarHeader.classList.remove("hidden");
}

lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
});

});