document.addEventListener("DOMContentLoaded", () => {
    const roleCheckboxes = document.querySelectorAll(
        '.js-user-form-roles input[type="checkbox"]'
    );

    const cn2eRoleField = document.querySelector(".js-user-form-cn2e-role");

    if (!roleCheckboxes.length || !cn2eRoleField) return;

    const cn2eRoles = [
        "ROLE_CN2E_MEMBER",
        "ROLE_CN2E_ADMIN",
        "ROLE_SUPER_ADMIN",
    ];

    const toggle = () => {
        const selectedRoles = Array.from(roleCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        const isMember = selectedRoles.some(role =>
            cn2eRoles.includes(role)
        );

        cn2eRoleField.style.display = isMember ? "" : "none";
    };

    toggle();

    roleCheckboxes.forEach(cb =>
        cb.addEventListener("change", toggle)
    );
});