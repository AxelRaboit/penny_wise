const activateThemeSwitcher = () => {
    const themeSwitcher = document.getElementById("theme-switcher");

    if (themeSwitcher) {
        const body = document.body;
        themeSwitcher.value = localStorage.getItem("theme") || "default";

        themeSwitcher.addEventListener("change", function () {
            body.classList.remove(
                "default",
                "charcoal",
                "slate",
                "graphite",
                "midnight",
                "mystic",
                "forest",
                "dark",
                "dimmed",
            );

            const newTheme = themeSwitcher.value;

            body.classList.add(newTheme);

            localStorage.setItem("theme", newTheme);
        });
    } else {
        console.warn("No theme selector found on this page.");
    }
};

export const initializeThemeSwitcher = () => {
    activateThemeSwitcher();
};