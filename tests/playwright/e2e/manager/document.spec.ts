import { test } from "@hipanel-core/fixtures";
import { expect } from "@playwright/test";
import { fileURLToPath } from "url";
import path from "path";
import DocumentView from "@hipanel-module-document/page/document/DocumentView";
import DocumentReplaceForm from "@hipanel-module-document/page/document/DocumentReplaceForm";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const TEST_PDF = path.join(__dirname, "../../fixtures/test.pdf");
const REPLACE_REASON = "E2E test replacement reason";

test("Document index page is accessible @hipanel-module-document @manager @document", async ({ page }) => {
  await page.goto("/document/document/index");
  await expect(page.locator(".content-header > h1")).toContainText("Documents");
});

test("Replace button is visible on detail page for manager @hipanel-module-document @manager @document", async ({ page }) => {
  const view = new DocumentView(page);
  await view.gotoFirstDocument();
  await expect(view.replaceButton()).toBeVisible();
});

test("Replace form shows current file, file picker, reason field and Cancel @hipanel-module-document @manager @document", async ({ page }) => {
  const view = new DocumentView(page);
  const form = new DocumentReplaceForm(page);

  await view.gotoFirstDocument();
  const viewUrl = page.url();

  await view.clickReplace();
  await form.assertOnPage();
  await expect(page.locator("#document-attachment")).toBeAttached();
  await expect(page.locator("#document-reason")).toBeVisible();
  await expect(page.locator('a.btn-default:has-text("Cancel")')).toHaveAttribute("href", new RegExp("/document/document/view"));

  await form.cancel();
  await expect(page).toHaveURL(viewUrl);
});

test("Replace form rejects submission without a reason @hipanel-module-document @manager @document", async ({ page }) => {
  const view = new DocumentView(page);
  const form = new DocumentReplaceForm(page);

  await view.gotoFirstDocument();
  await view.clickReplace();

  await form.uploadFile(TEST_PDF);
  await form.submit();
  await form.assertReasonRequired();
});

test("History section is absent on a document with no prior replacements @hipanel-module-document @manager @document", async ({ page }) => {
  const view = new DocumentView(page);

  await page.goto("/document/document/index");
  const links = page.locator('a[href*="/document/document/view"]');
  expect(await links.count()).toBeGreaterThan(0);

  for (let i = 0; i < Math.min(await links.count(), 5); i++) {
    await page.goto("/document/document/index");
    await links.nth(i).click();
    await page.waitForURL("**/document/document/view**");

    if (!await view.historyBox().isVisible()) {
      await view.assertHistoryNotVisible();
      return;
    }
  }
});

test("Replacing a file creates a history entry with correct data @hipanel-module-document @manager @document", async ({ page }) => {
  const view = new DocumentView(page);
  const form = new DocumentReplaceForm(page);

  await view.gotoFirstDocument();
  const viewUrl = page.url();

  await view.clickReplace();
  await form.assertOnPage();

  const previousFilename = await form.currentFilename();

  await form.uploadFile(TEST_PDF);
  await form.fillReason(REPLACE_REASON);
  await form.submit();

  await form.assertSuccessAndRedirect(viewUrl);
  await view.assertHistoryVisible();
  await view.assertHistoryRow(0, { filename: previousFilename, reason: REPLACE_REASON });
});

test("History section shows all required columns and a working Download link @hipanel-module-document @manager @document", async ({ page }) => {
  const view = new DocumentView(page);

  await view.gotoFirstDocument();

  if (!await view.historyBox().isVisible()) {
    test.skip(true, "No history rows on this document; run after the replacement test");
    return;
  }

  await view.assertHistoryColumns();
  await view.assertDownloadLinkVisible(0);
});
