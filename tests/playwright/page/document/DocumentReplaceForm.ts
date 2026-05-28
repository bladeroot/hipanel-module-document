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
    await this.page.locator("#document-attachment").setInputFiles(filePath);
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
    await expect(this.page.locator(".field-document-reason")).toHaveClass(/has-error/);
    await expect(this.page.locator(".field-document-reason .help-block")).toContainText("cannot be blank");
    await expect(this.page).toHaveURL(/\/document\/replace/);
  }

  async assertSuccessAndRedirect(viewUrl: string): Promise<void> {
    await expect(this.page).toHaveURL(viewUrl);
    await Alert.on(this.page).hasText("Document file was replaced");
  }
}
