const dotenv = require("dotenv");

dotenv.config({ path: ".env" });
dotenv.config();

const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    testIsolation: false,
    baseUrl: process.env.CYPRESS_BASE_URL,
    env: {
        "baseUrl": process.env.CYPRESS_BASE_URL,
        "ecasUrl": process.env.CYPRESS_ECAS_URL,
        "ecasUser": process.env.CYPRESS_ECAS_USERNAME,
        "ecasPass": process.env.CYPRESS_ECAS_PASSWORD,
        "apiUrl": process.env.API_URL,
        "token": process.env.API_TOKEN,
    },
    setupNodeEvents(on, config) {
      // on('before:browser:launch', (browser, launchOptions) => {
      //   if (browser.family === 'chromium') {
      //     launchOptions.preferences.default.cookies = {
      //       // Preserve cookies across sessions
      //       behavior: 'allow'
      //     };
      //   }

      //   return launchOptions;
      // });
    },
  },
});
