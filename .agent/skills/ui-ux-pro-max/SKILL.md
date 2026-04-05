---
name: ui-ux-pro-max
description: "Comprehensive design guide for web and mobile applications. Use when designing new UI components or pages, choosing color palettes and typography, or reviewing code for UX issues."
risk: unknown
source: community
date_added: "2026-04-04"
---

# UI/UX Pro Max - Design Intelligence

Comprehensive design guide for web and mobile applications. Contains 50+ styles, 97 color palettes, 57 font pairings, 99 UX guidelines, and 25 chart types across 9 technology stacks.

## Rules for Premium UI

### 1. Indicators & Visual Feedback
- **Status Dots**: Use pulses (`animate-pulse`) for active/open states.
- **Micro-interactions**: Scale transforms (0.95 -> 1.05) on hover.
- **Glassmorphism**: Use `backdrop-blur-md` and `bg-white/10` or `bg-slate-900/10`.

### 2. Information Hierarchy
- **Typography**: Contrast between `font-black` (headings) and `font-medium` (body).
- **Spacing**: Consistent `gap-4` or `gap-6` in dashboards.
- **Audit Trails**: Color-code entries (Emerald for entry, Rose for exit).

### 3. Iconography
- **HeroIcons**: Use consistent `24x24` icons.
- **No emojis**: Never use emojis for UI icons.

## Checklist for Caja/Cashier Module
- [ ] Indicador visual de estado (Abierta/Cerrada) con colores vibrantes.
- [ ] Tarjetas de resumen con métricas (Saldo actual).
- [ ] Selector de caja con diseño de dropdown premium (no selector nativo).
- [ ] Historial de movimientos con legibilidad mejorada (Fuentes mono para montos).
