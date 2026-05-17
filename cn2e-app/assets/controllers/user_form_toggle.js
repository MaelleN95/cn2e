document.addEventListener("DOMContentLoaded", () => {
    const memberCN2E = document.querySelector(
        '.js-user-form-cn2e-member input[type="checkbox"]',
    );
    const roleCN2E = document.querySelector(".js-user-form-cn2e-role");

    if (!memberCN2E || !roleCN2E) return;

    const toggleImage = () => {
        roleCN2E.style.display = memberCN2E.checked ? "" : "none";
    };

    toggleImage();
    memberCN2E.addEventListener("change", toggleImage);
});
