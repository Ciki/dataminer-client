dataminer-client
================

Typed value objects + (eventually) HTTP client for the dataminer.sk OCR API.

Today lives as a subtree inside the dataminer repo at `client-package/src/`
under the `Ciki\Dataminer\Client\` namespace, wired via composer.json
`autoload.psr-4`. Designed for extraction into a standalone Composer package
`ciki/dataminer-client` once the surface stabilizes.

Consumer-facing surface (Iter 1):

- `OcrData` - top-level extracted document data (constructed from validated payload)
- `OcrParty` - supplier or customer block
- `OcrAddress` - postal address block
- `OcrLineItem` - single invoice line
- `OcrTotals` - per-document totals (amounts + tax)

All VOs are `readonly`, implement `JsonSerializable` emitting snake_case keys
that match the API JSON contract exactly. Factory: `OcrData::fromValidatedArray()`
consumes the array produced by `OcrResponseSchema::build()->process(...)`.
