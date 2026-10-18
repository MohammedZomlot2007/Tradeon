import assert from 'node:assert/strict';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const baseURL = process.env.BASE_URL || 'http://127.0.0.1:8000';
const outputDir = path.resolve(process.env.SCREENSHOT_DIR || 'artifacts/screenshots');
await mkdir(outputDir, { recursive: true });

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({
  viewport: { width: 1440, height: 1000 },
  locale: 'en-US',
  colorScheme: 'light',
  reducedMotion: 'reduce',
});
const page = await context.newPage();
const captured = [];

async function capture(target, name, viewport = { width: 1440, height: 1000 }) {
  await page.setViewportSize(viewport);
  const response = await page.goto(new URL(target, baseURL).toString(), {
    waitUntil: 'domcontentloaded',
    timeout: 45_000,
  });
  await page.locator('body').waitFor({ state: 'visible', timeout: 15_000 });
  await page.waitForTimeout(900);

  if (!response) throw new Error(`No HTTP response for ${target}`);
  if (response.status() >= 500) {
    throw new Error(`Server returned ${response.status()} for ${target}`);
  }

  const bodyText = (await page.locator('body').innerText()).trim();
  assert.ok(bodyText.length > 20, `Rendered page is unexpectedly empty: ${target}`);

  const file = path.join(outputDir, name);
  await page.screenshot({ path: file, fullPage: true, animations: 'disabled' });
  captured.push({ route: target, file: path.relative(process.cwd(), file), status: response.status() });
  console.log(`Captured ${target} (${response.status()}) -> ${file}`);
}

try {
  // Genuine browser captures of the compiled Laravel + Inertia application.
  await capture('/', '01-landing-desktop.png');
  await capture('/login', '02-login-desktop.png');
  await capture('/register', '03-register-desktop.png');
  await capture('/explore', '04-explore-desktop.png');
  await capture('/pricing', '05-pricing-desktop.png');

  // Create an isolated, synthetic account using the real registration flow.
  await page.goto(new URL('/register', baseURL).toString(), { waitUntil: 'domcontentloaded', timeout: 45_000 });
  await page.locator('#name').fill('Tradeon CI Demo');
  await page.locator('#email').fill(`tradeon-ci-${process.env.GITHUB_RUN_ID || 'local'}-${process.env.GITHUB_RUN_ATTEMPT || '1'}@example.test`);
  await page.getByRole('button', { name: /^Manufacturer$/i }).click();
  await page.locator('#industry').selectOption({ label: 'Manufacturing' });
  await page.locator('#password').fill('Tradeon-CI-Demo-2026!');
  await page.locator('#password_confirmation').fill('Tradeon-CI-Demo-2026!');

  await Promise.all([
    page.waitForURL((url) => url.pathname.endsWith('/onboarding/company'), { timeout: 30_000 }),
    page.locator('form button[type="submit"]').click(),
  ]);
  await page.waitForTimeout(500);
  await page.screenshot({
    path: path.join(outputDir, '06-company-onboarding-desktop.png'),
    fullPage: true,
    animations: 'disabled',
  });
  captured.push({ route: '/onboarding/company', file: 'artifacts/screenshots/06-company-onboarding-desktop.png', status: 200 });

  await page.locator('#company_name').fill('Tradeon CI Sample Company');
  await page.locator('#country').fill('United States');
  await page.locator('#city').fill('Austin');
  await page.locator('#description').fill('Synthetic company record created only in the disposable CI database to verify onboarding and the business feed interface.');
  await Promise.all([
    page.waitForURL((url) => !url.pathname.endsWith('/onboarding/company'), { timeout: 30_000 }),
    page.locator('form button[type="submit"]').click(),
  ]);

  await page.goto(new URL('/feed', baseURL).toString(), { waitUntil: 'domcontentloaded', timeout: 45_000 });
  assert.ok(new URL(page.url()).pathname.endsWith('/feed'), `Expected authenticated feed, landed on ${page.url()}`);

  // Publish a fictional post through the real browser UI, so the feed screenshot
  // shows a product card rendered from the test database rather than a mockup.
  await page.getByRole('button', { name: /create business post/i }).click({ timeout: 15_000 });
  const dialog = page.getByRole('dialog');
  await dialog.locator('select').selectOption('business_update');
  await dialog.locator('input[type="text"]').fill('CI Demo — supplier partnership update');
  await dialog.locator('textarea').fill('Fictional QA content: Tradeon is validating its company feed, business updates, and sourcing workflows in a disposable test environment. No real companies or customers are represented.');
  await dialog.getByRole('button', { name: /publish post/i }).click();

  await page.waitForFunction(
    (title) => document.body.innerText.includes(title),
    'CI Demo — supplier partnership update',
    { timeout: 30_000 },
  );
  await capture('/feed', '07-business-feed-desktop.png');
  await capture('/requests', '08-business-requests-desktop.png');
  await capture('/billing', '09-billing-desktop.png');
  await capture('/feed', '10-business-feed-mobile.png', { width: 390, height: 844 });

  console.log(`Successfully captured ${captured.length} route screenshots.`);
  console.log(JSON.stringify(captured, null, 2));
} finally {
  await context.close();
  await browser.close();
}
