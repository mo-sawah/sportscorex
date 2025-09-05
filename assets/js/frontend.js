/**
 * SportScoreX Frontend JavaScript - 2025 Edition
 */

class SportScoreX {
  constructor() {
    this.widgets = new Map();
    this.refreshIntervals = new Map();
    this.init();
  }

  init() {
    // Initialize when DOM is ready
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", () =>
        this.initializeWidgets()
      );
    } else {
      this.initializeWidgets();
    }

    // Initialize theme system
    this.initTheme();
  }

  initializeWidgets() {
    // Find and initialize all SportScoreX widgets
    const widgets = document.querySelectorAll(".sportscorex-widget");
    widgets.forEach((widget) => this.initWidget(widget));
  }

  initWidget(widget) {
    const widgetId =
      widget.id || `widget-${Math.random().toString(36).substr(2, 9)}`;
    widget.id = widgetId;

    // Store widget reference
    this.widgets.set(widgetId, widget);

    // Initialize widget-specific functionality
    if (widget.classList.contains("sportscorex-live-scores")) {
      this.initLiveScoresWidget(widgetId);
    }

    // Initialize theme toggle for this widget
    this.initWidgetThemeToggle(widget);
  }

  initLiveScoresWidget(widgetId) {
    const widget = this.widgets.get(widgetId);
    const refreshInterval = parseInt(widget.dataset.refreshInterval) || 30;

    // Set up auto-refresh
    const intervalId = setInterval(() => {
      this.refreshLiveScores(widgetId);
    }, refreshInterval * 1000);

    this.refreshIntervals.set(widgetId, intervalId);

    // Initial load
    this.refreshLiveScores(widgetId);
  }

  async refreshLiveScores(widgetId) {
    const widget = this.widgets.get(widgetId);
    const sport = widget.dataset.sport || "football";
    const league = widget.dataset.league || "";

    try {
      // Show loading state
      this.showLoading(widget);

      // Fetch data from REST API
      const response = await fetch(
        `${sportscorex.api_url}live-scores?sport=${sport}&league=${league}`,
        {
          headers: {
            "X-WP-Nonce": sportscorex.nonce,
          },
        }
      );

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      this.renderLiveScores(widget, data);
    } catch (error) {
      console.error("Error fetching live scores:", error);
      this.showError(widget, "Failed to load live scores");
    }
  }

  renderLiveScores(widget, data) {
    const container = widget.querySelector(".sportscorex-live-scores");

    if (!data || data.length === 0) {
      container.innerHTML =
        '<p class="sportscorex-no-data">No live matches at the moment.</p>';
      return;
    }

    const matchesHTML = data
      .map(
        (match) => `
      <div class="sportscorex-match-card sportscorex-fade-in">
        <div class="sportscorex-team home">${this.escapeHtml(
          match.home_team
        )}</div>
        <div class="sportscorex-score">${match.home_score} - ${
          match.away_score
        }</div>
        <div class="sportscorex-team away">${this.escapeHtml(
          match.away_team
        )}</div>
        <div class="sportscorex-match-time">
          <span class="sportscorex-status ${match.status.toLowerCase()}">${
          match.status
        }</span>
          ${match.time ? `<span class="time">${match.time}'</span>` : ""}
        </div>
      </div>
    `
      )
      .join("");

    container.innerHTML = matchesHTML;
  }

  showLoading(widget) {
    const container = widget.querySelector(".sportscorex-live-scores");
    container.innerHTML = `
      <div class="sportscorex-loading">
        <div class="sportscorex-spinner"></div>
        <div class="sportscorex-loading-text">Loading live scores...</div>
      </div>
    `;
  }

  showError(widget, message) {
    const container = widget.querySelector(".sportscorex-live-scores");
    container.innerHTML = `
      <div class="sportscorex-error">
        <p>⚠️ ${this.escapeHtml(message)}</p>
      </div>
    `;
  }

  initTheme() {
    // Detect system preference
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)");

    // Apply initial theme
    this.applyTheme(this.getThemePreference());

    // Listen for system theme changes
    prefersDark.addEventListener("change", (e) => {
      if (this.getThemePreference() === "auto") {
        this.applyTheme("auto");
      }
    });
  }

  initWidgetThemeToggle(widget) {
    const toggle = widget.querySelector(".sportscorex-theme-toggle");
    if (toggle) {
      toggle.addEventListener("click", () => {
        this.toggleTheme();
      });
    }
  }

  toggleTheme() {
    const current = this.getThemePreference();
    let next;

    switch (current) {
      case "light":
        next = "dark";
        break;
      case "dark":
        next = "auto";
        break;
      default:
        next = "light";
    }

    this.setThemePreference(next);
    this.applyTheme(next);
  }

  applyTheme(theme) {
    const widgets = document.querySelectorAll(".sportscorex-widget");

    widgets.forEach((widget) => {
      // Remove existing theme classes
      widget.removeAttribute("data-ssx-theme");

      if (theme === "auto") {
        // Let CSS prefers-color-scheme handle it
        return;
      }

      widget.setAttribute("data-ssx-theme", theme);
    });
  }

  getThemePreference() {
    return localStorage.getItem("sportscorex-theme") || "auto";
  }

  setThemePreference(theme) {
    localStorage.setItem("sportscorex-theme", theme);
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // Cleanup method
  destroy() {
    // Clear all intervals
    this.refreshIntervals.forEach((intervalId) => {
      clearInterval(intervalId);
    });
    this.refreshIntervals.clear();
    this.widgets.clear();
  }
}

// Initialize SportScoreX when DOM is ready
const sportscorexInstance = new SportScoreX();

// Cleanup on page unload
window.addEventListener("beforeunload", () => {
  sportscorexInstance.destroy();
});
