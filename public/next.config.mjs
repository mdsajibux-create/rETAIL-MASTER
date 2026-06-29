import fs from "fs";
import path from "path";
import createNextIntlPlugin from "next-intl/plugin";

const withNextIntl = createNextIntlPlugin("./src/i18n.tsx");

if (!process.env.NEXT_PUBLIC_IMAGE_HOST) {
  throw new Error("NEXT_PUBLIC_IMAGE_HOST is not defined in environment variables");
}

const imageHost = process.env.NEXT_PUBLIC_IMAGE_HOST
  .replace(/^https?:\/\//, "")
  .replace(/\/$/, "");

const imageConfig = {
  remotePatterns: [
    {
      protocol: "https",
      hostname: "res.cloudinary.com",
      port: "",
      pathname: "**",
    },
    {
      protocol: "https",
      hostname: imageHost,
      pathname: "/storage/uploads/media-uploader/default/**",
    },
    {
      protocol: "https",
      hostname: imageHost,
      pathname: "/storage/uploads/**",
    },
    {
      protocol: "http",
      hostname: "192.168.88.225",
      port: "8000",
      pathname: "/storage/uploads/media-uploader/**",
    },
    {
      protocol: "https",
      hostname: imageHost,
      pathname: "**",
    },
  ],
  minimumCacheTTL: 60,
  formats: ["image/webp"],
  deviceSizes: [640, 750, 828, 1080, 1200, 1920, 2048, 3840],
  imageSizes: [16, 32, 48, 64, 96, 128, 256, 384],
  dangerouslyAllowSVG: false,
  contentSecurityPolicy: "default-src 'self'; script-src 'none'; sandbox;",
};

export default 
  withNextIntl({
    trailingSlash: process.env.TRAILING_SLASH === "true",
    experimental: {
      staleTimes: {
        dynamic: 0,
      },
    },
    images: imageConfig,
    output: "standalone",
    distDir: "build",
    logging: {
      fetches: {
        fullUrl: true,
      },
    },
    headers: async () => {
      return [
        {
          source: "/(.*)",
          headers: [
            {
              key: "X-DNS-Prefetch-Control",
              value: "on",
            },
            {
              key: "Strict-Transport-Security",
              value: "max-age=63072000; includeSubDomains; preload",
            },
          ],
        },
      ];
    },
    reactStrictMode: true,
    compress: true,

    // 👇 Correct place for Webpack hook
    webpack: (config) => {
      const swEnvFile = path.resolve("./public/firebase-env.js");

      const envConfig = `
        self.firebaseConfig = {
          apiKey: "${process.env.NEXT_PUBLIC_FIREBASE_API_KEY}",
          authDomain: "${process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN}",
          projectId: "${process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID}",
          storageBucket: "${process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET}",
          messagingSenderId: "${process.env.NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID}",
          appId: "${process.env.NEXT_PUBLIC_FIREBASE_APP_ID}",
          measurementId: "${process.env.NEXT_PUBLIC_FIREBASE_MEASUREMENT_ID}",
        };
      `;

      fs.writeFileSync(swEnvFile, envConfig);
      return config;
    },
  })
;
