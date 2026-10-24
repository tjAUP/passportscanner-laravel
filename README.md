# PassportScanner for Laravel 12

This package connects the framework-independent
[`tjaup/passportscanner`](https://github.com/tjAUP/passportscanner) browser
scanner to Laravel 12. The core package supplies `dist/` and all local OCR
assets. This package registers a Blade directive and a read-only asset route,
so application views do not need to know the Composer installation path or
copy the OCR files.

It targets PHP 8.2 or later and Laravel `^12.0`, matching the declared
requirements of `travel-ideas-online/estores`. The scanner runs in the
browser; neither package saves a form or uploads a passport image.

## Install

Register both GitHub repositories in the Laravel application, then require
only this package. Composer installs the core package as a dependency:

```sh
composer config repositories.passportscanner vcs https://github.com/tjAUP/passportscanner
composer config repositories.passportscanner-laravel vcs https://github.com/tjAUP/passportscanner-laravel
composer require "tjaup/passportscanner-laravel:*"
```

The service provider is discovered automatically by Laravel. If the
application disables package discovery, add
`TjAup\PassportScanner\Laravel\PassportScannerServiceProvider::class` to its
providers. Both repository entries belong in the consuming application's
`composer.json` because Composer does not inherit repository settings from
dependencies.

`*` lets Composer select the latest compatible release available when you run
`composer require` or `composer update tjaup/passportscanner-laravel -W`.
`composer install` uses the versions in `composer.lock`; commit that file for
reproducible deployments. Review major upgrades before deploying them.

## Add to a Blade form

Place `@passportScannerAssets` once in the layout or page that contains
passport inputs, preferably inside `<head>`. It loads the scanner script with
`defer`. The package serves the script and local OCR assets through its own
Laravel route. No asset path is needed in the view.

```blade
@passportScannerAssets

<form method="post" enctype="multipart/form-data">
    @csrf
    @foreach ($travelers as $index => $traveler)
        <div class="traveler-row">
            <input name="travelers[{{ $index }}][firstName]"
                   class="traveler-first-name"
                   value="{{ old('travelers.' . $index . '.firstName', $traveler->firstName ?? '') }}">
            <input name="travelers[{{ $index }}][passport]"
                   class="js-tjaup-passportscanner-input"
                   data-field-root=".traveler-row"
                   data-field-first-name=".traveler-first-name"
                   value="{{ old('travelers.' . $index . '.passport', $traveler->passport ?? '') }}">
        </div>
    @endforeach
    <button type="submit">Save</button>
</form>
```

`data-field-root` scopes each mapping to its own row. Inputs added after page
load initialize automatically, and removing a row releases its scanner. If
a row is hidden but remains in the DOM, call
`PassportScanner.stop(passportInput)` when hiding it and
`PassportScanner.initFromElement(passportInput)` when showing it again.

The passport input receives a bubbling native `passportscanner:result` event
after a usable scan. For example, a delegated jQuery listener can read each
row's result:

```js
$(document).on('passportscanner:result', '.js-tjaup-passportscanner-input', function (event) {
    const result = event.originalEvent.detail;
    console.log(result.fields.passportNo, result.fields.firstName);
    // Ask the traveler to verify the values before saving.
});
```

jQuery is optional. The passport input is mapped automatically. Other field
attributes are `data-field-name`, `data-field-first-name`,
`data-field-last-name`, `data-field-title`, `data-field-gender`,
`data-field-dob`, `data-field-expiry`, `data-field-nationality`,
`data-field-passport-no`, `data-field-document-code`,
`data-field-issuing-country-code`, `data-field-nationality-code`,
`data-field-sex`, `data-field-optional-data`, and `data-field-passport-img`.
`passportImg` must point to a file input if the
selected or captured image should be attached to the form. Optional settings
include `data-date-format` (PHP `date_format()` style, default `Y-m-d`) and
`data-country-names` (a JSON map such as `{"MY":"Malaysia"}`).

If the form is submitted with Inertia's `useForm` or another component state
object, handle `passportscanner:result` and copy `result.fields` into that
state. The scanner fills DOM inputs and dispatches input and change events,
but a controlled form should also update the state it submits. The script can
be placed in the Inertia root Blade layout; scanner inputs added during
client-side navigation initialize automatically.

The result event includes `detail.fields`, `detail.checks`,
`detail.verifiedCount`, and `detail.complete`. The scanned passport number is
available as both `detail.fields.passport` and `detail.fields.passportNo`.
The MRZ also provides a document code, issuing country code, nationality code,
sex marker, and issuer-defined optional data when readable. Optional data has
no universal meaning. Verify the populated fields against the passport before
saving. The scanner does not submit the form or upload the image. More browser
configuration is documented in the
[core README](https://github.com/tjAUP/passportscanner#readme).

## Local OCR assets and privacy

The asset route only serves files inside the core package's `dist/` tree,
including its bundled Tesseract.js worker, core, and English language data.
It rejects paths outside that tree. OCR runs in the browser without an
external OCR service. Camera scanning requires HTTPS or localhost and browser
permission. A restrictive Content Security Policy must allow same-origin
scripts, workers, and WebAssembly. Keep the local OCR layout unchanged.

Third-party licenses and attribution are in the
[core notices](https://github.com/tjAUP/passportscanner/blob/main/THIRD_PARTY_NOTICES.md)
and `dist/ocr/licenses/`.

## Acknowledgment

This project was developed with assistance from OpenAI Codex.
