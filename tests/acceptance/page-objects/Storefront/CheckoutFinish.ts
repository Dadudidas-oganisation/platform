import type { Page, Locator } from '@playwright/test';
import type { PageObject } from '@fixtures/PageObject';

export class CheckoutFinishPage implements PageObject {
    public readonly headline: Locator;
    public readonly orderNumberText: Locator;
    public readonly grandTotalPrice: Locator;

    private readonly orderNumberRegex = /Your order number: #(\d+)/;

    constructor(public readonly page: Page) {
        this.headline = page.getByRole('heading', { name: 'Thank you for your order' });
        this.orderNumberText = page.getByText(this.orderNumberRegex);
        this.grandTotalPrice = page.locator('dt:has-text("Grand total") + dd');
    }

    async goTo() {
        console.error('The checkout finish page should only be navigated to via checkout action.')
    }

    async getOrderNumber() {
        const orderNumberText = await this.orderNumberText.textContent();
        const [, orderNumber] = orderNumberText.match(this.orderNumberRegex);

        return orderNumber;
    }

    getOrderId() {
        const url = this.page.url();
        const [, searchString] = url.split('?');
        const params = new URLSearchParams(searchString);

        return params.get('orderId');
    }
}
