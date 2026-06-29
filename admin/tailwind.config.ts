import type { Config } from "tailwindcss";

const config = {
  darkMode: ["class"],
  content: [
    "./pages/**/*.{js,ts,ts,tsx}",
    "./components/**/*.{js,ts,jsx,tsx}",
    "./app/**/*.{js,ts,ts,tsx}",
    "./src/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  safelist: [
    "pl-[0px]",
    "pl-[24px]",
    "pl-[48px]",
    "pl-[72px]",
    "pl-[96px]",
    "pl-[120px]",
    "pl-[144px]",
    "pl-[168px]",
  ],
  prefix: "",
  theme: {
    container: {
      center: true,
      padding: "1rem",
    },
    extend: {
      zIndex: {
        "60": "60",
        "70": "70",
        "80": "80",
        "90": "90",
        "100": "100",
      },
      boxShadow: {
        custom: "0px 4px 10px rgba(0, 0, 0, 0.1)", // Custom shadow
      },
      blur: {
        xs: "2px",
      },
      modal_top: {
        "50p": "50%",
      },
      backgroundImage: {
        "blue-angle":
          "linear-gradient(129.64deg, #2B8EFF 8.24%, #1F59C7 61.59%, #1F40C7 78.3%)",
        "become-a-seller-contact": "url('/images/reg_bg.png')",
        "green-angle":
          "linear-gradient(200.56deg, #32FFA3 7.03%, #058C4F 89.99%)",
      },
      inset: {
        "0p": "0%",
        "5p": "5%",
        "6p": "6%",
        "7p": "7%",
        "8p": "8%",
        "10p": "10%",
        "11p": "11%",
        "12p": "12%",
        "13p": "13%",
        "14p": "14%",
        "15p": "15%",
        "16p": "16%",
        "17p": "17%",
        "18p": "18%",
        "19p": "19%",
        "20p": "20%",
        "21p": "21%",
        "22p": "22%",
        "23p": "23%",
        "25p": "25%",
        "30p": "30%",
        "31p": "31%",
        "32p": "32%",
        "33p": "33%",
        "34p": "34%",
        "35p": "35%",
        "36p": "36%",
        "37p": "37%",
        "38p": "38%",
        "40p": "40%",
        "50p": "50%",
      },
      colors: {
        border: "#DCE3EA",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        title: "#647482",
        subtitle: "#8F9499",
        primary: {
          DEFAULT: "#3AD38F",
          foreground: "hsl(var(--primary-foreground))",
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
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
        info: {
          50: "#ebf8ff",
          100: "#bee3f8",
          200: "#90cdf4",
          300: "#63b3ed",
          400: "#4299e1",
          500: "#3182ce", // your main info color
          600: "#2b6cb0",
          700: "#2c5282",
          800: "#2a4365",
          900: "#1A365D",
        },
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
    },
  },
  plugins: [require("tailwindcss-animate")],
} satisfies Config;

export default config;
