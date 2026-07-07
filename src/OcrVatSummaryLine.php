<?php

declare(strict_types=1);

namespace Ciki\Dataminer\Client;

use JsonSerializable;

/**
 * One row of the per-rate VAT recapitulation: for a single VAT rate, the taxable base and the
 * VAT amount.
 *
 * Populated only when the extraction source provides an authoritative per-rate breakdown -
 * today that is the Slovak eKasa OPD lookup (Finančná správa), whose `vatSummary` carries exact
 * base/amount per rate. The LLM path leaves `OcrData::$vatSummary` empty: weak OCR models are
 * unreliable at the arithmetic a per-rate split needs, so we never synthesize it from the prompt.
 *
 * All fields nullable for schema-normalization tolerance, though a real breakdown row always
 * carries all three.
 */
final readonly class OcrVatSummaryLine implements JsonSerializable
{
	public function __construct(
		public ?float $rate,
		public ?float $base,
		public ?float $amount,
	) {}


	/**
	 * @param array{rate: ?float, base: ?float, amount: ?float} $validated
	 */
	public static function fromValidatedArray(array $validated): self
	{
		return new self(
			rate: $validated['rate'],
			base: $validated['base'],
			amount: $validated['amount'],
		);
	}


	/** @return array<string,?float> */
	public function jsonSerialize(): array
	{
		return [
			'rate' => $this->rate,
			'base' => $this->base,
			'amount' => $this->amount,
		];
	}
}
