const { test } = require('@playwright/test');
const h = require('./helpers');

test('guest can place an order with flat rate and cash on delivery', async ({ page }) => {
  await h.addProductToCart(page);
  await h.openCheckout(page);

  await h.field(page, 'login.email').fill(process.env.GUEST_EMAIL || 'guest.test@example.com');
  await page.getByRole('button', { name: 'Update' }).first().click();
  await page.waitForTimeout(1500);

  await h.fillShippingAddress(page);
  await h.chooseFlatRateAndCashOnDelivery(page);
  await h.placeOrder(page);
});
