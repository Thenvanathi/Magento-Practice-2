const { expect } = require('@playwright/test');

const PRODUCT_URL = '/floral-summer-dress.html';

const SHIPPING_ADDRESS = {
  company: 'Test Company',
  firstname: 'Test',
  lastname: 'User',
  street: '1 Test Street',
  country: 'India',
  region: 'Tamil Nadu',
  zipcode: '600001',
  city: 'Chennai',
  phone: '9999999999',
};

// The React checkout renders fields with ids like "shipping_address.firstname".
const field = (page, id) => page.locator(`[id="${id}"]`);

async function addProductToCart(page) {
  await page.goto(PRODUCT_URL, { waitUntil: 'networkidle' });
  await page
    .getByRole('button', { name: /add to cart/i })
    .first()
    .click();
  await page.waitForTimeout(3000);
}

async function openCheckout(page) {
  await page.goto('/checkout/', { waitUntil: 'networkidle' });
  await expect(field(page, 'shipping_address.firstname')).toBeVisible();
}

async function fillShippingAddress(page, address = SHIPPING_ADDRESS) {
  await field(page, 'shipping_address.company').fill(address.company);
  await field(page, 'shipping_address.firstname').fill(address.firstname);
  await field(page, 'shipping_address.lastname').fill(address.lastname);
  await field(page, 'shipping_address.street[0]').fill(address.street);
  await field(page, 'shipping_address.country').selectOption({ label: address.country });
  // The region list is loaded after the country changes.
  await page.waitForTimeout(1500);
  await field(page, 'shipping_address.region').selectOption({ label: address.region });
  await field(page, 'shipping_address.zipcode').fill(address.zipcode);
  await field(page, 'shipping_address.city').fill(address.city);
  await field(page, 'shipping_address.phone').fill(address.phone);
  await page.getByRole('button', { name: 'Save' }).first().click();
  await page.waitForTimeout(4000);
}

async function chooseFlatRateAndCashOnDelivery(page) {
  await field(page, 'shippingMethod_flatrate__flatrate').check();
  await page.waitForTimeout(2500);
  await field(page, 'paymentMethod_cashondelivery').check();
  await page.waitForTimeout(2500);
}

async function placeOrder(page) {
  await page.getByRole('button', { name: 'Place Order' }).click();
  await page.waitForURL(/success|onepage/, { timeout: 30_000 });
  await expect(page.locator('body')).toContainText(/thank you|your order/i);
}

module.exports = {
  addProductToCart,
  openCheckout,
  fillShippingAddress,
  chooseFlatRateAndCashOnDelivery,
  placeOrder,
  field,
};
