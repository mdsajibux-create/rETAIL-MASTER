// server.js
const { createServer } = require("http"); // Use HTTP
const { parse } = require("url");
const next = require("next");

const port = process.env.PORT || 3003;
const dev = process.env.NODE_ENV !== "production";
const app = next({ dev });
const handle = app.getRequestHandler();

app.prepare().then(() => {
  createServer((req, res) => {
    const parsedUrl = parse(req.url, true);
    handle(req, res, parsedUrl);
  }).listen(port, (err) => {
    if (err) throw err;
    console.log(`> HTTP Server running at http://localhost:${port}`);
  });
});
