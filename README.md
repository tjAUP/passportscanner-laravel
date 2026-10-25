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

## Complete Laravel Blade example

Place `@passportScannerAssets` once in the layout or page that contains
passport inputs, preferably inside `<head>`. It loads the scanner script with
`defer`. The package serves the script and local OCR assets through its own
Laravel route. No asset path is needed in the view.

In the layout's `<head>`:

```blade
@passportScannerAssets
```

In the Blade form, keep the passport number input separate from the disabled
scanner input. The matching `data-field-*` attributes tell the scanner which
fields to fill in the same traveler row:

```blade
<form method="post" enctype="multipart/form-data">
    @csrf
    @foreach ($travelers as $index => $traveler)
        @php
            $oldKey = 'travelers.' . $index . '.';
        @endphp
        <div class="traveler-row">
            <label>Full name
                <input name="travelers[{{ $index }}][name]" class="traveler-name"
                       value="{{ old($oldKey . 'name', $traveler->name ?? '') }}">
            </label>
            <label>Given names
                <input name="travelers[{{ $index }}][firstName]" class="traveler-first-name"
                       value="{{ old($oldKey . 'firstName', $traveler->firstName ?? '') }}">
            </label>
            <label>Surname
                <input name="travelers[{{ $index }}][lastName]" class="traveler-last-name"
                       value="{{ old($oldKey . 'lastName', $traveler->lastName ?? '') }}">
            </label>
            <label>Title
                <select name="travelers[{{ $index }}][title]" class="traveler-title">
                    <option value="">Select</option>
                    <option value="MR" @selected(old($oldKey . 'title', $traveler->title ?? '') === 'MR')>Mr</option>
                    <option value="MS" @selected(old($oldKey . 'title', $traveler->title ?? '') === 'MS')>Ms</option>
                </select>
            </label>
            <label>Gender
                <select name="travelers[{{ $index }}][gender]" class="traveler-gender">
                    <option value="">Select</option>
                    <option value="male" @selected(old($oldKey . 'gender', $traveler->gender ?? '') === 'male')>Male</option>
                    <option value="female" @selected(old($oldKey . 'gender', $traveler->gender ?? '') === 'female')>Female</option>
                </select>
            </label>
            <label for="traveler-{{ $index }}-passport-no">Passport number</label>
            <input id="traveler-{{ $index }}-passport-no"
                   name="travelers[{{ $index }}][passportNo]"
                   class="traveler-passport-no"
                   value="{{ old($oldKey . 'passportNo', $traveler->passportNo ?? '') }}">
            <input type="hidden" name="travelers[{{ $index }}][passport_scan]"
                   class="js-tjaup-passportscanner-input" disabled
                   data-field-root=".traveler-row"
                   data-field-passport-no=".traveler-passport-no"
                   data-field-name=".traveler-name"
                   data-field-first-name=".traveler-first-name"
                   data-field-last-name=".traveler-last-name"
                   data-field-title=".traveler-title"
                   data-field-gender=".traveler-gender"
                   data-field-dob=".traveler-dob"
                   data-field-expiry=".traveler-expiry"
                   data-field-nationality=".traveler-nationality"
                   data-field-nationality-code=".traveler-nationality-code"
                   data-field-document-code=".traveler-document-code"
                   data-field-issuing-country-code=".traveler-issuing-country-code"
                   data-field-sex=".traveler-sex"
                   data-field-optional-data=".traveler-optional-data"
                   data-field-passport-img=".traveler-passport-image"
                   data-date-format="Y-m-d"
                   data-auto-capture-count="1"
                   data-auto-capture-hold-seconds="0"
                   data-log-results="false"
                   data-country-names='{"MY":"Malaysia","SG":"Singapore","US":"United States"}'>
            <label>Date of birth
                <input type="date" name="travelers[{{ $index }}][dob]" class="traveler-dob"
                       value="{{ old($oldKey . 'dob', $traveler->dob ?? '') }}">
            </label>
            <label>Expiry date
                <input type="date" name="travelers[{{ $index }}][expiry]" class="traveler-expiry"
                       value="{{ old($oldKey . 'expiry', $traveler->expiry ?? '') }}">
            </label>
            <label>Nationality
                <select name="travelers[{{ $index }}][nationality]" class="traveler-nationality">
                    <option value="">Select</option>
                    @foreach (['Malaysia', 'Singapore', 'United States'] as $country)
                        <option value="{{ $country }}" @selected(old($oldKey . 'nationality', $traveler->nationality ?? '') === $country)>{{ $country }}</option>
                    @endforeach
                </select>
            </label>
            <label>Nationality code
                <input name="travelers[{{ $index }}][nationalityCode]" class="traveler-nationality-code"
                       value="{{ old($oldKey . 'nationalityCode', $traveler->nationalityCode ?? '') }}">
            </label>
            <label>Document code
                <input name="travelers[{{ $index }}][documentCode]" class="traveler-document-code"
                       value="{{ old($oldKey . 'documentCode', $traveler->documentCode ?? '') }}">
            </label>
            <label>Issuing country code
                <input name="travelers[{{ $index }}][issuingCountryCode]" class="traveler-issuing-country-code"
                       value="{{ old($oldKey . 'issuingCountryCode', $traveler->issuingCountryCode ?? '') }}">
            </label>
            <label>MRZ sex marker
                <input name="travelers[{{ $index }}][sex]" class="traveler-sex"
                       value="{{ old($oldKey . 'sex', $traveler->sex ?? '') }}">
            </label>
            <label>Issuer optional data
                <input name="travelers[{{ $index }}][optionalData]" class="traveler-optional-data"
                       value="{{ old($oldKey . 'optionalData', $traveler->optionalData ?? '') }}">
            </label>
            <label>Passport image
                <input type="file" name="travelers[{{ $index }}][passportImg]"
                       class="traveler-passport-image" accept="image/jpeg,image/png,image/webp">
            </label>
        </div>
    @endforeach
    <button type="submit">Save after checking each passport</button>
</form>
```

Set the form's `action` to your application's save route. The scanner
does not submit it. The mapped input named `passportNo` is filled after its
MRZ check passes; the scanner input stays empty and is not submitted.
`detail.fields.passport` and `detail.fields.passportNo` contain the same
verified number. The nationality dropdown accepts the country names listed
in `data-country-names`, while `nationalityCode` keeps the three-letter MRZ
code when no option matches. Issuer optional data can be empty.
The scanner fills the mapped inputs directly. No page-level JavaScript or
jQuery handler is needed to transfer the passport number, dates, or other
details into the form.

`data-auto-capture-count` controls automatic camera frames from 1 to 10,
including the first readable frame. The example sets the count to 1 and the
hold time to 0 for immediate capture. Set both attributes to `4` and `3` for
four frames over three seconds, which is also the default when both are
omitted. If you set only `data-auto-capture-hold-seconds` (0–30 seconds), the
scanner takes roughly one additional frame per second, up to 10 total. The
manual **Capture & scan** button and uploaded images each use one frame.

`data-field-root` scopes each mapping to its own row. Inputs added after page
load initialize automatically, and removing a row releases its scanner. If
a row is hidden but remains in the DOM, call
`PassportScanner.stop(scannerInput)` when hiding it and
`PassportScanner.initFromElement(scannerInput)` when showing it again.

### Optional result event

The scanner input also emits a bubbling native `passportscanner:result` event
after a usable scan. Listen only if your application needs an extra reaction
after the inputs are filled. For example, a delegated jQuery listener can read
each row's result:

```js
$(document).on('passportscanner:result', '.js-tjaup-passportscanner-input', function (event) {
    const { fields, checks } = event.originalEvent.detail;
    const passportNo = checks.passport ? fields.passportNo : undefined;
    const firstName = fields.firstName;
    // Use these values in your form state after the traveler verifies them.
});
```

jQuery is optional. Map the passport number field explicitly. Other field
attributes are `data-field-name`, `data-field-first-name`,
`data-field-last-name`, `data-field-title`, `data-field-gender`,
`data-field-dob`, `data-field-expiry`, `data-field-nationality`,
`data-field-passport-no`, `data-field-document-code`,
`data-field-issuing-country-code`, `data-field-nationality-code`,
`data-field-sex`, `data-field-optional-data`, and `data-field-passport-img`.
`passportImg` must point to a file input if the
selected or captured image should be attached to the form. Optional settings
include `data-date-format` (PHP `date_format()` style, default `Y-m-d`),
`data-auto-capture-count`, `data-auto-capture-hold-seconds`,
`data-country-names` (a JSON map such as `{"MY":"Malaysia"}`), and
`data-log-results`. The complete example sets each of these. For debugging,
change `data-log-results="false"` to `"true"`. A console log labeled
`PassportScanner scan results` then shows an array with one object containing
every MRZ field. Missing or unverified values are `null`, such as
`expiry: null`; failed scans include `accepted`, `verifiedCount`, and `checks`
with all field values `null`. The image and raw OCR text are excluded. Leave
logging off outside debugging because the console displays accepted passport
details.

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
