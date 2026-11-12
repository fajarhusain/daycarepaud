/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.{html,js}"],
  theme: {
    extend: {
      container: {
        center: true,
        screens: {
          xl: '1290px',
          lg: '1024px',
          md: "768px",
          sm: "640px"
        },
        padding: "5px"
      },
      spacing: {
        "2.5": "10px",
        "4.5": "18px",
        "7.5": "30px",
        "12.5": "50px",
        "15": "60px",
        "25": "100px"
      },
      backgroundImage: {
        'testimonial-banner': "url('/assets/images/testimonial/bg-img.png')",
        'newsletter-banner': "url('/assets/images/newsletter/bg-img.png')"
      },
      backgroundSize: {
        '50%': '50%'
      },
      boxShadow: {
        "sm": "0px 0px 10px 0px rgba(0, 0, 0, 0.2)",
        "3xl": "0px 4.8px 24.4px -6px rgba(19, 16, 34, 0.10)",
        "4xl": "0px 4.4px 20px -1px rgba(19, 16, 34, 0.05)"
      },
      colors: {
        background: {
          DEFAULT: "var(--background)",
        },
        primary: {
          DEFAULT: "var(--primary)",
          foreground: "var(--primary-foreground)",
        },

        secondary: {
          DEFAULT: "var(--secondary)",
          foreground: "var(--secondary-foreground)",
        },

        destructive: {
          DEFAULT: "var(--destructive)",
          foreground: "var(--destructive-foreground)",
        },

        green: {
          DEFAULT: "var(--green)",
          foreground: "var(--green-foreground)",
        },

        warm: {
          DEFAULT: "var(--warm)",
        },

        cream: {
          DEFAULT: "",
          foreground: "var(--cream-foreground)",
        },

        muted: {
          DEFAULT: "var(--muted)",
          foreground: "var(--muted-foreground)",
        },

      },
      fontFamily: {
        "bubblegum-sans": "Bubblegum Sans",
        "jost": "Jost",
        "nunito": "Nunito"
      },
      keyframes: {
        "left-right": {
          "50%": {
            'transform': `translateX(14px)`
          }
        },
        "left-right-2": {
          "50%": {
            'transform': `translateX(-40px)`
          }
        },
        'up-down': {
          "50%": {
            'transform': 'translateY(-10px)'
          }
        },
        'skw': {
          "50%": {
            'transform': 'skewX(5deg)'
          }
        },
        'expend-width-height': {
          "100%": {
            'width': '56%',
            'height': '56%',
          }
        },

      },
      animation: {
        'left-right': 'left-right 2s linear infinite',
        'left-right-2': 'left-right-2 4s linear infinite;',
        'up-down': 'up-down 2s linear infinite',
        'skw': 'skw 2s linear infinite',
        'expend-width-height': 'expend-width-height 2s linear infinite',
      }
    },
  },
  plugins: [],
}

