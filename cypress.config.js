const dotenv = require("dotenv");

dotenv.config({ path: ".env" });
dotenv.config();

const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    baseUrl: process.env.APP_URL,
    env: {
        "apiUrl": process.env.API_URL,
        "token": process.env.API_TOKEN,
    },
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
