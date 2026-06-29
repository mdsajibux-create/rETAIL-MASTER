// next.config.mjs
import createNextIntlPlugin from "next-intl/plugin";
import fs from "fs";
import path from "path";
import bundleAnalyzer from "@next/bundle-analyzer";

const withNextIntl = createNextIntlPlugin("./src/i18n.tsx");
const withBundleAnalyzer = bundleAnalyzer({
  enabled: process.env.ANALYZE === "true",
});
const imageHost = process.env.NEXT_PUBLIC_IMAGE_HOST ?? "192.168.88.225:8000";

export default withBundleAnalyzer(
  withNextIntl({
    reactStrictMode: false,
    trailingSlash: process.env.TRAILING_SLASH === "true",
    poweredByHeader: false,

    output: "standalone",

    distDir: "build",
    experimental: {
      staleTimes: {
        dynamic: 30,
      },
    },
    images: {
      unoptimized: true,
      remotePatterns: [
        {
          protocol: "https",
          hostname: "res.cloudinary.com",
          pathname: "/**",
        },
        {
          protocol: "https",
          hostname: imageHost,
          pathname: "/storage/**",
        },
        {
          protocol: "http",
          hostname: "localhost",
          pathname: "/storage/**",
        },
        {
          protocol: "http",
          hostname: "192.168.88.225",
          port: "8000",
          pathname: "/storage/**",
        },
      ],
    },

    pageExtensions: ["ts", "tsx", "js", "jsx", "md", "mdx"],
    compiler: {
      removeConsole: process.env.NODE_ENV === "production",
    },
  }),
);
