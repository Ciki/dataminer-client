<?php

declare(strict_types=1);

namespace Ciki\Dataminer\Client;

use JsonSerializable;

/**
 * Top-level value object representing the OCR-extracted data for a single document.
 *
 * Single source of truth for the shape that flows between the dataminer.sk OCR API and its
 * consumers (today: ekonix). Constructed from the array produced by
 * `OcrResponseSchema::build()->process(...)` via `OcrData::fromValidatedArray()`.
 *
 * The schema-validated array shape uses snake_case keys; VO properties are camelCase per PHP
 * convention. `jsonSerialize()` emits the snake_case shape back so the HTTP API response stays
 * byte-identical to the pre-VO contract.
 *
 * Date fields stay as ISO strings (YYYY-MM-DD) for Iter 1 - the schema already enforces the
 * pattern and consumers can cheaply `DateTimeImmutable::createFromFormat('Y-m-d', ...)` when
 * they need a typed value. Adding typed dates here would force the JsonSerializable path to
 * format back exactly the same string, adding failure surface for zero consumer benefit today.
 */
final readonly class OcrData implements JsonSerializable
{
	public const string DOCUMENT_TYPE_INVOICE = 'invoice';
	public const string DOCUMENT_TYPE_RECEIPT = 'receipt';
	public const string DOCUMENT_TYPE_OTHER = 'other';
	public const string PAYMENT_METHOD_CATEGORY_TRANSFER = 'transfer';
	public const string PAYMENT_METHOD_CATEGORY_CASH = 'cash';
	public const string PAYMENT_METHOD_CATEGORY_CARD = 'card';
	public const string PAYMENT_METHOD_CATEGORY_POD = 'pod';
	public const string PAYMENT_METHOD_CATEGORY_PAYPAL = 'paypal';
	public const string PAYMENT_METHOD_CATEGORY_ADVANCE = 'advance';
	public const string PAYMENT_METHOD_CATEGORY_CREDIT = 'credit';


	/**
	 * @param list<OcrLineItem> $items
	 * @param array<string,mixed> $additionalInfo LLM-extension bucket declared in schema as `Expect::array([])` - free-form, no validation, but always emitted by the prompt
	 */
	public function __construct(
		public string $documentType,
		public ?string $language,
		public ?string $documentNumber,
		public ?string $issueDate,
		public ?string $dueDate,
		public ?string $deliveryDate,
		public ?string $taxableSupplyDate,
		public ?string $paymentMethod,
		public ?string $paymentMethodCategory,
		public ?string $currency,
		public OcrParty $supplier,
		public OcrParty $customer,
		public array $items,
		public OcrTotals $totals,
		public ?string $notes,
		public ?string $summary,
		public ?string $paymentReference,
		public ?string $variableSymbol,
		public ?string $constantSymbol,
		public ?string $specificSymbol,
		public array $additionalInfo,
	) {}


	/**
	 * Construct from the schema-validated payload (snake_case array as produced by
	 * `OcrResponseSchema::build()->process(...)`).
	 *
	 * Schema-tolerated unknown top-level keys (from `otherItems(Expect::mixed())`) are dropped
	 * here on purpose - the prompt explicitly forbids extras ("Return ONLY the JSON object" +
	 * declared key list), so any extras are LLM misbehavior. They stay visible for prompt
	 * iteration in `ocr_requests.raw_provider_response` (full pre-schema bytes) but don't
	 * pollute the typed VO surface.
	 *
	 * @param array<string,mixed> $validated
	 */
	public static function fromValidatedArray(array $validated): self
	{
		/** @var list<array{item_number: string|int|null, description: ?string, quantity: ?float, unit: ?string, unit_price: ?float, discount_percent: ?float, tax_rate: ?float, tax_amount: ?float, total_without_tax: ?float, total_with_tax: ?float}> $itemsRaw */
		$itemsRaw = $validated['items'];
		/** @var array<string,mixed> $additionalInfo */
		$additionalInfo = $validated['additional_info'] ?? [];

		return new self(
			documentType: $validated['document_type'],
			language: $validated['language'],
			documentNumber: $validated['document_number'],
			issueDate: $validated['issue_date'],
			dueDate: $validated['due_date'],
			deliveryDate: $validated['delivery_date'],
			taxableSupplyDate: $validated['taxable_supply_date'],
			paymentMethod: $validated['payment_method'],
			paymentMethodCategory: $validated['payment_method_category'],
			currency: $validated['currency'],
			supplier: OcrParty::fromValidatedArray($validated['supplier']),
			customer: OcrParty::fromValidatedArray($validated['customer']),
			items: array_map(OcrLineItem::fromValidatedArray(...), $itemsRaw),
			totals: OcrTotals::fromValidatedArray($validated['totals']),
			notes: $validated['notes'],
			summary: $validated['summary'],
			paymentReference: $validated['payment_reference'],
			variableSymbol: $validated['variable_symbol'],
			constantSymbol: $validated['constant_symbol'],
			specificSymbol: $validated['specific_symbol'],
			additionalInfo: $additionalInfo,
		);
	}


	/** @return array<string,mixed> */
	public function jsonSerialize(): array
	{
		return [
			'document_type' => $this->documentType,
			'language' => $this->language,
			'document_number' => $this->documentNumber,
			'issue_date' => $this->issueDate,
			'due_date' => $this->dueDate,
			'delivery_date' => $this->deliveryDate,
			'taxable_supply_date' => $this->taxableSupplyDate,
			'payment_method' => $this->paymentMethod,
			'payment_method_category' => $this->paymentMethodCategory,
			'currency' => $this->currency,
			'supplier' => $this->supplier->jsonSerialize(),
			'customer' => $this->customer->jsonSerialize(),
			'items' => array_map(static fn(OcrLineItem $i): array => $i->jsonSerialize(), $this->items),
			'totals' => $this->totals->jsonSerialize(),
			'notes' => $this->notes,
			'summary' => $this->summary,
			'payment_reference' => $this->paymentReference,
			'variable_symbol' => $this->variableSymbol,
			'constant_symbol' => $this->constantSymbol,
			'specific_symbol' => $this->specificSymbol,
			'additional_info' => $this->additionalInfo,
		];
	}
}
