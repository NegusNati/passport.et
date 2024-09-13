/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx}"],
  theme: {
    screens: {
      xs: "480px",
      sm: "640px",
      md: "768px",
      lg: "1024px",
      xl: "1280px"
    },
    colors: {
      current: "currentColor",
      background1: "#060606",
      background2: "#0c0c0e",
      color1: "#edebe8",
      color2: "#999999",
      gray: "#cccccc",
      primary: "#4615b2",
      negus: "#00D8FF",
      border1: "#333333"
    },
    fontSize: {
      xs: ["12px", "15px"],
      s: ["14px", "17px"],
      m: ["16px", "20px"],
      l: ["18px", "22px"],
      xl: ["20px", "24px"],
      "2xl": ["24px", "28px"],
      "3xl": ["29px", "35px"],
      "4xl": ["35px", "43px"],
      "5xl": ["42px", "49px"],
      "6xl": ["49px", "56px"],
      "7xl": ["64px", "71px"]
    },
    fontFamily: {
      primary: ["-apple-system", "BlinkMacSystemFont", "Inter", "Helvetica", "Arial", "sans-serif"]
    },
    extend: {
      zIndex: {
        1: "1",
        2: "2",
        3: "3",
        4: "4",
        5: "5"
      },
      backgroundImage: {
        "gradient-radial":
          "radial-gradient(circle at center, #cccccc35 1px, transparent 0), radial-gradient(circle at center, #cccccc35 1px, transparent 0)",
        "linear-gradient": "linear-gradient(0deg, #060606 0%, transparent 65%)"
      }
    }
  },
  plugins: []
};
