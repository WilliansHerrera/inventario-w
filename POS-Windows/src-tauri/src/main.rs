#![cfg_attr(
    all(not(debug_assertions), target_os = "windows"),
    windows_subsystem = "windows"
)]

use tauri::{generate_context, Builder};

fn main() {
    Builder::default()
        .run(generate_context!())
        .expect("error while running tauri application");
}
