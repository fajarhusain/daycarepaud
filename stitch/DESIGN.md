# Design System Specification: "be kids" Day Care
## 1. Color Palette Tokens
| Token | Hex | Usage |
|---|---|---|
| `--color-canvas` | `#FBFBF7` | Main warm cream background |
| `--color-surface-mint` | `#E8F1E4` | Secondary organic hill sections |
| `--color-surface-sage` | `#C8DEC2` | Medium sage background bands |
| `--color-surface-forest` | `#587E6C` | Dark sage contrast background (Testimonials) |
| `--color-surface-card` | `#FFFFFF` | Testimonial card surface |
| `--color-text-primary` | `#2D3F35` | Main headings & dominant copy |
| `--color-text-secondary` | `#586B60` | Secondary description copy |
| `--color-text-inverse` | `#FFFFFF` | Text over dark forest containers |
| `--color-accent` | `#668F74` | Buttons, CTAs, highlight pills |
| `--color-border-subtle` | `#D5E2D1` | 1px border lines and card outlines |
## 2. Typography
- **Display / Headings**: `Fraunces` or `Cormorant Garamond` (Weights: 400, 500, 600)
- **Body & Interface**: `Plus Jakarta Sans` (Weights: 400, 500, 600)
- **Scale**:
  - Display Hero: `3.75rem` (60px), `line-height: 1.1`
  - Section Title (H2): `2.25rem` (36px), `line-height: 1.25`
  - Card Title (H3): `1.125rem` (18px), `line-height: 1.4`
  - Body Text: `0.9375rem` (15px), `line-height: 1.6`
  - Micro / Labels: `0.8125rem` (13px), `font-weight: 500`
## 3. Elevation & Radii
- **Border Radius**: 
  - Containers / Cards: `1.25rem` (20px)
  - Pill Buttons & Badges: `9999px` (Full rounded)
  - Speech bubbles: `16px` with custom SVG pointer notch
- **Box Shadows**:
  - `shadow-subtle`: `0 2px 8px -2px rgba(45, 63, 53, 0.05)`
  - `shadow-card`: `0 8px 20px -4px rgba(45, 63, 53, 0.08)`