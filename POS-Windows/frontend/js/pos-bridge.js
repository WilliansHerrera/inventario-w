/**
 * Inventario-W POS Native Bridge
 * This script runs in the WebView and provides hooks for Tauri native commands.
 */

window.POS_NATIVE = {
    isTauri: !!window.__TAURI__,

    async closeWindow() {
        if (this.isTauri) {
            const { appWindow } = window.__TAURI__.window;
            await appWindow.close();
        } else {
            console.warn("Native command 'closeWindow' ignored: Not running in Tauri.");
        }
    },

    async printRaw(content) {
        if (this.isTauri) {
            // Placeholder for future native printing logic (Direct USB/Serial)
            console.log("Native printing requested:", content);
        }
    },

    async toggleFullscreen() {
        if (this.isTauri) {
            const { appWindow } = window.__TAURI__.window;
            const fullscreen = await appWindow.isFullscreen();
            await appWindow.setFullscreen(!fullscreen);
        }
    }
};

console.log("POS Native Bridge Initialized. Tauri detected:", window.POS_NATIVE.isTauri);
