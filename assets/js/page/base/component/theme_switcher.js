export const loadSavedTheme = () => {
    const savedTheme = localStorage.getItem("theme") || "default";
    document.body.classList.add(savedTheme);
};

export const initializeThemeSwitcher = () => {
    loadSavedTheme();
};
