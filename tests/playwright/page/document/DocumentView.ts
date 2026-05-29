import { expect, Locator, Page } from "@playwright/test";

export default class DocumentView {
  private readonly detailMenu: Locator;
  private readonly historyBoxLocator: Locator;

  constructor(private readonly page: Page) {
    this.detailMenu = page.locator(".profile-usermenu .nav");
    this.historyBoxLocator = page.locator('.box:has(.box-title:has-text("File replacement history"))');
  }

  async gotoFirstDocument(): Promise<void> {
    await this.page.goto("/document/document/index");
    await this.page.locator('a[href*="/document/document/view"]').first().click();
    await this.page.waitForURL("**/document/document/view**");
  }

  async gotoFirstDocumentOrSkip(): Promise<boolean> {
    await this.page.goto("/document/document/index");
    const firstLink = this.page.locator('a[href*="/document/document/view"]').first();
    if (await firstLink.count() === 0) {
      return false;
    }
    await firstLink.click();
    await this.page.waitForURL("**/document/document/view**");
    return true;
  }

  replaceButton(): Locator {
    return this.detailMenu.locator('a:has-text("Replace")');
  }

  async clickReplace(): Promise<void> {
    await this.replaceButton().click();
  }

  historyBox(): Locator {
    return this.historyBoxLocator;
  }

  historyTable(): Locator {
    return this.historyBoxLocator.locator("table");
  }

  async assertHistoryVisible(): Promise<void> {
    await expect(this.historyBoxLocator).toBeVisible();
  }

  async assertHistoryNotVisible(): Promise<void> {
    await expect(this.historyBoxLocator).not.toBeVisible();
  }

  async assertHistoryColumns(): Promise<void> {
    const thead = this.historyTable().locator("thead th");
    await expect(thead.nth(0)).toContainText("File");
    await expect(thead.nth(1)).toContainText("Size");
    await expect(thead.nth(2)).toContainText("Valid till");
    await expect(thead.nth(3)).toContainText("Replaced by");
    await expect(thead.nth(4)).toContainText("Reason");
  }

  async assertHistoryRow(nth: number, data: { filename?: string; reason?: string }): Promise<void> {
    const row = this.historyTable().locator("tbody tr").nth(nth);
    await expect(row).toBeVisible();
    if (data.filename) {
      await expect(row.locator("td").nth(0)).toContainText(data.filename);
    }
    if (data.reason) {
      await expect(row.locator("td").nth(4)).toContainText(data.reason);
    }
  }

  async assertDownloadLinkVisible(nth: number = 0): Promise<void> {
    const link = this.historyTable().locator("tbody tr").nth(nth).locator('a:has-text("Download")');
    await expect(link).toBeVisible();
    await expect(link).toHaveAttribute("href", /\/file\/get/);
  }

  async currentFilename(): Promise<string> {
    return (await this.page.locator("p.text-center.text-muted.small").textContent())?.trim() ?? "";
  }
}
