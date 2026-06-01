<?php

declare(strict_types=1);

namespace Ciki\Dataminer\Client;

use JsonSerializable;

/**
 * Per-document amount totals as extracted from the source.
 *
 * All fields are nullable: not every document carries every total (eg. simple receipts often
 * omit `total_without_tax`). Consumers should treat null as "not stated on the document",
 * not as zero.
 */
final readonly class OcrTotals implements JsonSerializable
{
	public function __construct(
		public ?float $totalWithoutTax,
		public ?float $totalTax,
		public ?float $totalWithTax,
		public ?float $amountPaid,
		public ?float $amountDue,
	) {}


	/**
	 * @param array{
	 *     total_without_tax: ?float,
	 *     total_tax: ?float,
	 *     total_with_tax: ?float,
	 *     amount_paid: ?float,
	 *     amount_due: ?float,
	 * } $validated
	 */
	public static function fromValidatedArray(array $validated): self
	{
		return new self(
			totalWithoutTax: $validated['total_without_tax'],
			totalTax: $validated['total_tax'],
			totalWithTax: $validated['total_with_tax'],
			amountPaid: $validated['amount_paid'],
			amountDue: $validated['amount_due'],
		);
	}


	/** @return array<string,?float> */
	public function jsonSerialize(): array
	{
		return [
			'total_without_tax' => $this->totalWithoutTax,
			'total_tax' => $this->totalTax,
			'total_with_tax' => $this->totalWithTax,
			'amount_paid' => $this->amountPaid,
			'amount_due' => $this->amountDue,
		];
	}
}
