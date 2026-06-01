<?php

declare(strict_types=1);

namespace Ciki\Dataminer\Client;

use JsonSerializable;

/**
 * Postal address as extracted from the document.
 *
 * `country` is free-text as printed; `countryIsoCode` is the canonical ISO 3166-1 alpha-2 form
 * (or null when the LLM couldn't normalize it). See `OcrResponseSchema::addressSchema` for the
 * source-side normalization rules.
 */
final readonly class OcrAddress implements JsonSerializable
{
	public function __construct(
		public ?string $street,
		public ?string $city,
		public ?string $postalCode,
		public ?string $country,
		public ?string $countryIsoCode,
	) {}


	/**
	 * Reconstruct from the Schema-validated array shape (snake_case keys).
	 *
	 * @param array{
	 *     street: ?string,
	 *     city: ?string,
	 *     postal_code: ?string,
	 *     country: ?string,
	 *     country_iso_code: ?string,
	 * } $validated
	 */
	public static function fromValidatedArray(array $validated): self
	{
		return new self(
			street: $validated['street'],
			city: $validated['city'],
			postalCode: $validated['postal_code'],
			country: $validated['country'],
			countryIsoCode: $validated['country_iso_code'],
		);
	}


	/** @return array<string,?string> */
	public function jsonSerialize(): array
	{
		return [
			'street' => $this->street,
			'city' => $this->city,
			'postal_code' => $this->postalCode,
			'country' => $this->country,
			'country_iso_code' => $this->countryIsoCode,
		];
	}
}
