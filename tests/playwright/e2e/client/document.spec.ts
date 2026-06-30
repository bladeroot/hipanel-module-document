import { test } from "@hipanel-core/fixtures";
import { expect } from "@playwright/test";
import DocumentView from "@hipanel-module-document/page/document/DocumentView";

test("Client cannot see Replace button or File replacement history section @hipanel-module-document @client @document", async ({ page }) => {
  const view = new DocumentView(page);
  if (!await view.gotoFirstDocumentOrSkip()) {
    test.skip(true, "No documents visible to this client");
    return;
  }
  await expect(view.replaceButton()).not.toBeVisible();
  await view.assertHistoryNotVisible();
});

test("Client is redirected when accessing replace URL directly @hipanel-module-document @client @document", async ({ page }) => {
  await page.goto("/document/document/index");
  const firstLink = page.locator('a[href*="/document/document/view"]').first();
  if (await firstLink.count() === 0) {
    test.skip(true, "No documents visible to this client");
    return;
  }

  const href = await firstLink.getAttribute("href");
  const docId = href?.match(/id=(\d+)/)?.[1];
  if (!docId) {
    test.skip(true, "Could not extract document ID");
    return;
  }

  await page.goto(`/document/document/replace?id=${docId}`);
  await expect(page).not.toHaveTitle("Replace document file");
});
