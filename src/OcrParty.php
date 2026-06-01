<?php

declare(strict_types=1);

namespace Ciki\Dataminer\Client;

use JsonSerializable;

/**
 * Supplier or customer party as extracted from the document.
 *
 * Shape is uniform across both roles - bank fields (iban / swift / bankName / bankAccount) are
 * typically populated only on the supplier side of an invoice, but the keys are always present
 * so consumers don't need to branch on role when reading. Null is the canonical "not stated on
 * the document" signal. See `OcrResponseSchema::partySchema` for the source-side guarantees.
 */
final readonly class OcrParty implements JsonSerializable
{
	public function __construct(
		public ?string $name,
		public ?OcrAddress $address,
		public ?string $in,
		public ?string $tin,
		public ?string $vatin,
		public ?string $bankAccount,
		public ?string $bankName,
		public ?string $iban,
		public ?string $swift,
	) {}


	/**
	 * @param array{
	 *     name: ?string,
	 *     address: ?array{street: ?string, city: ?string, postal_code: ?string, country: ?string, country_iso_code: ?string},
	 *     in: ?string,
	 *     tin: ?string,
	 *     vatin: ?string,
	 *     bank_account: ?string,
	 *     bank_name: ?string,
	 *     iban: ?string,
	 *     swift: ?string,
	 * } $validated
	 */
	public static function fromValidatedArray(array $validated): self
	{
		return new self(
			name: $validated['name'],
			address: $validated['address'] === null ? null : OcrAddress::fromValidatedArray($validated['address']),
			in: $validated['in'],
			tin: $validated['tin'],
			vatin: $validated['vatin'],
			bankAccount: $validated['bank_account'],
			bankName: $validated['bank_name'],
			iban: $validated['iban'],
			swift: $validated['swift'],
		);
	}


	/** @return array<string,mixed> */
	public function jsonSerialize(): array
	{
		return [
			'name' => $this->name,
			'address' => $this->address?->jsonSerialize(),
			'in' => $this->in,
			'tin' => $this->tin,
			'vatin' => $this->vatin,
			'bank_account' => $this->bankAccount,
			'bank_name' => $this->bankName,
			'iban' => $this->iban,
			'swift' => $this->swift,
		];
	}
}
