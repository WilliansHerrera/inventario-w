#![cfg_attr(
    all(not(debug_assertions), target_os = "windows"),
    windows_subsystem = "windows"
)]

use tauri::{generate_handler, generate_context, Builder};

#[tauri::command]
fn greet(name: &str) -> String {
    format!("Hello, {}! You've been greeted from Rust!", name)
}

fn main() {
    Builder::default()
        .plugin(tauri_plugin_sql::Builder::default().build())
        .invoke_handler(generate_handler![greet])
        .run(generate_context!())
        .expect("error while running tauri application");
}
