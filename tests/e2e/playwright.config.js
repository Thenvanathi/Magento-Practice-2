// @ts-check
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './specs',
  timeout: 90_000,
  workers: 1,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: process.env.BASE_URL || 'http://magento.local',
    viewport: { width: 1280, height: 1800 },
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
  },
});
