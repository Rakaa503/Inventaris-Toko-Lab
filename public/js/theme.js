(() => {
    "use strict";

    const storageKey = "labstock-theme";
    const root = document.documentElement;
    const button = document.getElementById("theme-toggle");
    const metaTheme = document.querySelector('meta[name="theme-color"]');

    if (!button) {
        return;
    }

    const getSavedTheme = () => {
        try {
            const saved = localStorage.getItem(storageKey);
            return saved === "dark" || saved === "light" ? saved : "light";
        } catch {
            return "light";
        }
    };

    const applyTheme = (theme) => {
        const isDark = theme === "dark";

        root.dataset.theme = theme;
        button.setAttribute("aria-pressed", String(isDark));
        button.setAttribute(
            "aria-label",
            isDark ? "Aktifkan light mode" : "Aktifkan dark mode"
        );

        const icon = button.querySelector(".theme-toggle-icon");
        const label = button.querySelector(".theme-toggle-label");

        if (icon) {
            icon.textContent = isDark ? "☀" : "◐";
        }

        if (label) {
            label.textContent = isDark ? "Mode terang" : "Mode gelap";
        }

        if (metaTheme) {
            metaTheme.setAttribute(
                "content",
                isDark ? "#171a1f" : "#ffffff"
            );
        }
    };

    applyTheme(getSavedTheme());

    button.addEventListener("click", () => {
        const nextTheme =
            root.dataset.theme === "dark" ? "light" : "dark";

        applyTheme(nextTheme);

        try {
            localStorage.setItem(storageKey, nextTheme);
        } catch {
            // Tema tetap dapat digunakan meskipun penyimpanan browser gagal.
        }
    });
})();