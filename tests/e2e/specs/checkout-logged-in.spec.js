const { test } = require('@playwright/test');
const h = require('./helpers');

const EMAIL = process.env.CUSTOMER_EMAIL || 'codilar.e2e@example.com';
const PASSWORD = process.env.CUSTOMER_PASSWORD || 'Codilar@12345';

test('logged-in customer can place an order with flat rate and cash on delivery', async ({
  page,
}) => {
  // The header also contains a login form, so target the main form's ids.
  await page.goto('/customer/account/login/', { waitUntil: 'networkidle' });
  await page.locator('#email').fill(EMAIL);
  await page.locator('#pass').fill(PASSWORD);
  await Promise.all([
    page.waitForURL(/customer\/account(?!\/login)/),
    page.click('button[name="send"]'),
  ]);

  await h.addProductToCart(page);
  await h.openCheckout(page);
  // A logged-in customer has no email step: the account email is used.
  await h.fillShippingAddress(page);
  await h.chooseFlatRateAndCashOnDelivery(page);
  await h.placeOrder(page);
});
