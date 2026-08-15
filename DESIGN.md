# Design System

<!-- impeccable:design-schema 1 -->

## Visual World

TechStore utilizes an elevated modern technology e-commerce aesthetic: deep navy obsidian neutrals (`#0f172a`, `#1e293b`), crisp surface layers (`#ffffff`, `#f8fafc`), high-trust Electric Cobalt blue (`#1d4ed8`) as primary brand color, and vibrant accent markers (Emerald green for stock/trust `#059669`, Crimson red for urgent sale badges `#dc2626`, and Amber for ratings `#d97706`).

## Typography

- **Primary Body Font:** `Be Vietnam Pro`, sans-serif (weights: 400, 500, 600, 700, 800) - optimized for Vietnamese text readability and clean digital commerce.
- **Headings & Badges:** `Outfit`, sans-serif (weights: 600, 700, 800, 900) - punchy, modern geometric tech personality.

## Color Tokens

- **Primary:** `--primary: #1d4ed8;` (Hover: `#1e40af`, Light: `#eff6ff`, Glow: `rgba(29, 78, 216, 0.22)`)
- **Accent:** `--accent: #7c3aed;` (Hover: `#6d28d9`, Glow: `rgba(124, 58, 237, 0.2)`)
- **Success:** `--success: #059669;` (Light: `#ecfdf5`)
- **Warning:** `--warning: #d97706;` (Light: `#fffbeb`)
- **Danger:** `--danger: #dc2626;` (Light: `#fef2f2`)
- **Dark Surface:** `--bg-dark: #0f172a;` (Card: `#1e293b`)
- **Light Surface:** `--bg-body: #f8fafc;` (Surface: `#ffffff`)

## Elevation & Shadows

- Multi-tier ambient shadows: `--shadow-xs`, `--shadow-sm`, `--shadow-md`, `--shadow-lg`, `--shadow-xl`
- Product Card Hover: `--shadow-card-hover: 0 20px 30px -10px rgba(29, 78, 216, 0.12), 0 10px 15px -5px rgba(15, 23, 42, 0.04);`
- Glassmorphism: `backdrop-filter: blur(16px);` with `rgba(255, 255, 255, 0.94)` background on sticky navigation and mobile dock.

## Components & Micro-interactions

- **Announcement Bar:** Subtle pulsating status dot with real-time promo banner.
- **Header:** Sticky glassmorphic navbar with smart search suggestions dropdown, dynamic compare pill badge, cart item badge, and user avatar dropdown.
- **Product Card:** Hover lift (`translateY(-6px)`), subtle colored glow shadow, image scale zoom (`1.08`), floating badge group (New/Hot/Discount), quick action overlay (Compare, Quick View), and full-width CTA button.
- **Value Proposition:** 4 high-trust feature boxes with colorful icon containers.
- **Quick Categories:** Pill/card grid with hover scale and direct filter navigation.
- **Mobile Dock:** Frosted glass bottom bar with active indicator and badges.
