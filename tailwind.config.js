import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'DM Sans', 'Inter', ...defaultTheme.fontFamily.sans],
                serif: ['"EB Garamond"', 'Fraunces', 'Georgia', ...defaultTheme.fontFamily.serif],
                'display-hero': ['"EB Garamond"', 'Georgia', 'serif'],
                'headline-lg': ['"EB Garamond"', 'Georgia', 'serif'],
                'headline-md': ['"EB Garamond"', 'Georgia', 'serif'],
                'headline-sm': ['"EB Garamond"', 'Georgia', 'serif'],
                'body-lg': ['"Plus Jakarta Sans"', 'sans-serif'],
                'body-md': ['"Plus Jakarta Sans"', 'sans-serif'],
                'body-sm': ['"Plus Jakarta Sans"', 'sans-serif'],
                'label-md': ['"Plus Jakarta Sans"', 'sans-serif'],
                'label-sm': ['"Plus Jakarta Sans"', 'sans-serif'],
                'title-card': ['"Plus Jakarta Sans"', 'sans-serif'],
            },
            colors: {
                // Scandinavian Daycare (Stitch) palette
                'canvas-cream': '#FBFBF7',
                'surface-mint': '#E8F1E4',
                'surface-sage': '#C8DEC2',
                'surface-forest': '#587E6C',
                'surface-card': '#FFFFFF',
                'forest-deep': '#496C5C',
                'forest-mid': '#5E8570',
                'forest-high': '#436556',
                'text-primary': '#2D3F35',
                'text-secondary': '#586B60',
                'text-inverse': '#FFFFFF',
                'border-subtle': '#D5E2D1',
                // Existing palette
                cream: {
                    DEFAULT: '#F5F0E8',
                    raised: '#FAF6F0',
                    inset: '#EDE8DF',
                    pressed: '#E5DFD4',
                },
                teal: {
                    DEFAULT: '#1A6B6B',
                    dark: '#14504F',
                    light: '#D0E8E8',
                    muted: '#2D8585',
                },
                brand: {
                    text: '#2C2C2C',
                    secondary: '#6B6560',
                    muted: '#9E9790',
                },
            },
            borderRadius: {
                '2xl': '16px',
                '3xl': '24px',
            },
            boxShadow: {
                'raised': '4px 4px 10px rgba(0,0,0,0.10), -2px -2px 6px rgba(255,255,255,0.70)',
                'raised-hover': '6px 6px 14px rgba(0,0,0,0.13), -3px -3px 8px rgba(255,255,255,0.75)',
                'inset-soft': 'inset 2px 2px 6px rgba(0,0,0,0.08), inset -2px -2px 4px rgba(255,255,255,0.60)',
                'modal': '0 20px 60px rgba(0,0,0,0.18)',
                'teal': '2px 4px 10px rgba(26,107,107,0.35)',
            },
        },
    },

    plugins: [forms],
};
