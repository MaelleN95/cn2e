document.addEventListener("DOMContentLoaded", () => {
    const roleCheckboxes = document.querySelectorAll(
        '.js-user-form-roles input[type="checkbox"]'
    );

    const cn2eRoleField = document.querySelector(".js-user-form-cn2e-role");

    if (!roleCheckboxes.length || !cn2eRoleField) return;

    const toggle = () => {
        const isMember = Array.from(roleCheckboxes).some(
            (cb) => cb.checked && cb.value === "ROLE_CN2E_MEMBER"
        );

        cn2eRoleField.style.display = isMember ? "" : "none";
    };

    toggle();

    roleCheckboxes.forEach((cb) =>
        cb.addEventListener("change", toggle)
    );
});