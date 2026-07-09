<?php

declare(strict_types=1);

namespace Ciki\Dataminer\Client;

use JsonSerializable;

/**
 * Single invoice / receipt line item.
 *
 * `itemNumber` collapses the schema's `string|int|null` union into `?string` for downstream
 * convenience - consumers only ever display the value or persist it as text. The int → string
 * cast is lossless; original LLM bytes (including int form) remain visible in
 * `ocr_requests.raw_provider_response` if prompt iteration ever needs them.
 *
 * Unit price is a without/with-tax PAIR because documents print either semantics: invoices
 * typically a net unit price, fuel/retail receipts a gross one. Extraction fills only the
 * printed field; the API's derive option can fill the missing counterpart arithmetically.
 */
final readonly class OcrLineItem implements JsonSerializable
{
	public function __construct(
		public ?string $itemNumber,
		public ?string $description,
		public ?float $quantity,
		public ?string $unit,
		public ?float $unitPriceWithoutTax,
		public ?float $unitPriceWithTax,
		public ?float $discountPercent,
		public ?float $taxRate,
		public ?float $taxAmount,
		public ?float $totalWithoutTax,
		public ?float $totalWithTax,
	) {}


	/**
	 * @param array{
	 *     item_number: string|int|null,
	 *     description: ?string,
	 *     quantity: ?float,
	 *     unit: ?string,
	 *     unit_price_without_tax?: ?float,
	 *     unit_price_with_tax?: ?float,
	 *     unit_price?: ?float,
	 *     discount_percent: ?float,
	 *     tax_rate: ?float,
	 *     tax_amount: ?float,
	 *     total_without_tax: ?float,
	 *     total_with_tax: ?float,
	 * } $validated
	 */
	public static function fromValidatedArray(array $validated): self
	{
		$itemNumber = $validated['item_number'];
		return new self(
			itemNumber: $itemNumber === null ? null : (string) $itemNumber,
			description: $validated['description'],
			quantity: $validated['quantity'],
			unit: $validated['unit'],
			// Legacy fallback: payloads stored before the pair split carry a single `unit_price`,
			// which consumers always treated as the net price - map it to withoutTax.
			unitPriceWithoutTax: $validated['unit_price_without_tax'] ?? $validated['unit_price'] ?? null,
			unitPriceWithTax: $validated['unit_price_with_tax'] ?? null,
			discountPercent: $validated['discount_percent'],
			taxRate: $validated['tax_rate'],
			taxAmount: $validated['tax_amount'],
			totalWithoutTax: $validated['total_without_tax'],
			totalWithTax: $validated['total_with_tax'],
		);
	}


	/** @return array<string,string|float|null> */
	public function jsonSerialize(): array
	{
		return [
			'item_number' => $this->itemNumber,
			'description' => $this->description,
			'quantity' => $this->quantity,
			'unit' => $this->unit,
			'unit_price_without_tax' => $this->unitPriceWithoutTax,
			'unit_price_with_tax' => $this->unitPriceWithTax,
			'discount_percent' => $this->discountPercent,
			'tax_rate' => $this->taxRate,
			'tax_amount' => $this->taxAmount,
			'total_without_tax' => $this->totalWithoutTax,
			'total_with_tax' => $this->totalWithTax,
		];
	}
}
