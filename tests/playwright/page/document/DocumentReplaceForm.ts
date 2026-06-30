import fs from "fs";
import path from "path";
import { expect, Page } from "@playwright/test";
import { Alert } from "@hipanel-core/shared/ui/components";

export default class DocumentReplaceForm {
  constructor(private readonly page: Page) {}

  async assertOnPage(): Promise<void> {
    await expect(this.page).toHaveTitle("Replace document file");
    await expect(this.page.locator(".box-title:has-text('Current file')")).toBeVisible();
  }

  async currentFilename(): Promise<string> {
    return (await this.page.locator("p.text-center.text-muted.small").textContent())?.trim() ?? "";
  }

  async uploadFile(filePath: string): Promise<void> {
    const buffer = Buffer.concat([
      fs.readFileSync(filePath),
      Buffer.from(`\n% unique:${Date.now()}\n`),
    ]);
    await this.page.locator("#document-attachment").setInputFiles({
      name: path.basename(filePath),
      mimeType: "application/pdf",
      buffer,
    });
  }

  async fillReason(reason: string): Promise<void> {
    await this.page.locator("#document-reason").fill(reason);
  }

  async submit(): Promise<void> {
    await this.page.locator("button.btn-warning").click();
  }

  async cancel(): Promise<void> {
    await this.page.locator('a.btn-default:has-text("Cancel")').click();
  }

  async assertReasonRequired(): Promise<void> {
    await expect(this.page.locator("#document-reason:invalid")).toBeAttached();
    await expect(this.page).toHaveURL(/\/document\/replace/);
  }

  async assertSuccessAndRedirect(viewUrl: string): Promise<void> {
    await expect(this.page).toHaveURL(viewUrl);
    await Alert.on(this.page).hasText("Document file was replaced");
  }
}
