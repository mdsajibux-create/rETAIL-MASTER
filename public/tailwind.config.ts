import type { Config } from "tailwindcss";
import plugin from "tailwindcss/plugin";

const config: Config = {
  darkMode: "class",
  content: [
    "./pages/**/*.{ts,tsx}",
    "./components/**/*.{ts,tsx}",
    "./app/**/*.{ts,tsx}",
    "./src/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  prefix: "",
  theme: {
    container: {
      center: true,
      // padding: "1rem",
    },
    extend: {
      fontFamily: {
        // Sans-Serif options
        inter: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"],
        nunito: ["Nunito", "ui-sans-serif", "system-ui", "sans-serif"],
        poppins: ["Poppins", "ui-sans-serif", "system-ui", "sans-serif"],
        roboto: ["Roboto", "ui-sans-serif", "system-ui", "sans-serif"],

        // Serif options
        lora: ["Lora", "ui-serif", "Georgia", "serif"],
        "playfair-display": [
          "Playfair Display",
          "ui-serif",
          "Georgia",
          "serif",
        ],

        // You can keep your custom font class for "minimal" theme
        "luckiest-guy": ["Luckiest Guy", "cursive"],
      },
      boxShadow: {
        custom: "-1px 1px 12px 0px #0000000F",
        complex: `
          -2px 7px 4.29px 2px #1C627D04,
          -2px 7px 8.09px 2px #1C627D07,
          -2px 7px 13.54px 2px #1C627D09,
          -2px 7px 22.75px 2px #1C627D0A,
          -2px 7px 37.87px 2px #1C627D0C,
          -2px 7px 61.03px 2px #1C627D0E,
          -2px 7px 94.36px 2px #1C627D11,
          -2px 7px 140px -2px #1C627D14
        `,
      },
      blur: {
        xs: "2px",
      },
      inset: {
        "0p": "0%",
        "10p": "10%",
        "15p": "15%",
        "20p": "20%",
        "30p": "30%",
        "35p": "35%",
        "38p": "38%",
        "40p": "40%",
        "50p": "50%",
      },
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        title: {
          DEFAULT: "#222C2D",
        },
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
        },
        primaryLight: {
          DEFAULT: "#0550570e",
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))",
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))",
        },
        accent: {
          DEFAULT: "hsl(var(--accent))",
          foreground: "hsl(var(--accent-foreground))",
        },
        popover: {
          DEFAULT: "hsl(var(--popover))",
          foreground: "hsl(var(--popover-foreground))",
        },
        card: {
          DEFAULT: "hsl(var(--card))",
          foreground: "hsl(var(--card-foreground))",
        },
        "custom-dark-blue": "#102A43",
      },
      borderRadius: {
        lg: "var(--radius)",
        md: "calc(var(--radius) - 2px)",
        sm: "calc(var(--radius) - 4px)",
      },
      keyframes: {
        "accordion-down": {
          from: { height: "0" },
          to: { height: "var(--radix-accordion-content-height)" },
        },
        "accordion-up": {
          from: { height: "var(--radix-accordion-content-height)" },
          to: { height: "0" },
        },
      },
      animation: {
        "accordion-down": "accordion-down 0.2s ease-out",
        "accordion-up": "accordion-up 0.2s ease-out",
        "spin-slow": "spin 2s linear infinite",
      },
      backgroundImage: {
        "blue-angle":
          "linear-gradient(129.64deg, color-mix(in srgb, var(--primary) 80%, white) 8.24%, var(--primary) 61.59%, var(--primary) 78.3%)",
      },
      screens: {
        xs: "400px",
      },
    },
  },
  plugins: [
    require("tailwindcss-animate"),
    plugin(function ({ addUtilities }) {
      addUtilities({
        ".scrollbar-hide": {
          "-ms-overflow-style": "none",
          "scrollbar-width": "none",
          "&::-webkit-scrollbar": {
            display: "none",
          },
        },
      });
    }),
  ],
};

export default config;
