<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Rules\PublicSiteUrl;
use App\Support\Http\LocalRedirect;
use App\Support\Http\PublicSiteUrl as PublicSiteUrlSanitizer;
use App\Support\Media\SvgSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SiteHeaderController extends Controller
{
    public function update(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        $validated = $request->validateWithBag('updateSiteHeader', [
            'brand_name' => ['nullable', 'string', 'max:255'],
            'show_brand_name' => ['nullable', 'boolean'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'show_tagline' => ['nullable', 'boolean'],
            'logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'remove_logo' => ['nullable', 'boolean'],
            'logo_alt' => ['nullable', 'string', 'max:255'],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'cta_href' => ['nullable', 'string', 'max:255', new PublicSiteUrl()],
            'show_cta' => ['nullable', 'boolean'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $settings = $site->headerSettings()->firstOrNew();
        $disk = $this->disk();
        $currentLogoPath = $settings->logo_path;
        $newLogoPath = $currentLogoPath;

        if ((bool) ($validated['remove_logo'] ?? false)) {
            if ($currentLogoPath) {
                Storage::disk($disk)->delete($currentLogoPath);
            }

            $newLogoPath = null;
        }

        $uploadedLogo = $request->file('logo_upload');

        if ($uploadedLogo) {
            if ($currentLogoPath) {
                Storage::disk($disk)->delete($currentLogoPath);
            }

            $newLogoPath = $this->storeLogo($uploadedLogo, $site, $disk);
        }

        $settings->fill([
            'brand_name' => $this->nullableText($validated['brand_name'] ?? null),
            'show_brand_name' => (bool) ($validated['show_brand_name'] ?? false),
            'tagline' => $this->nullableText($validated['tagline'] ?? null),
            'show_tagline' => (bool) ($validated['show_tagline'] ?? false),
            'logo_path' => $newLogoPath,
            'logo_alt' => $this->nullableText($validated['logo_alt'] ?? null),
            'cta_label' => $this->nullableText($validated['cta_label'] ?? null),
            'cta_href' => PublicSiteUrlSanitizer::sanitize($validated['cta_href'] ?? null),
            'show_cta' => (bool) ($validated['show_cta'] ?? false),
        ]);

        $site->headerSettings()->save($settings);

        $redirectTo = LocalRedirect::sanitize($validated['redirect_to'] ?? null);

        return redirect()
            ->to($redirectTo ?? route('cms.sites.global.section', [$site, 'header']))
            ->with('status', 'Headeren er opdateret.');
    }

    private function nullableText(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function disk(): string
    {
        return (string) config('filesystems.site_media_disk', 'public');
    }

    private function storeLogo(UploadedFile $uploadedLogo, Site $site, string $disk): string
    {
        $directory = "site-media/{$site->slug}/header";
        $extension = strtolower($uploadedLogo->getClientOriginalExtension());

        if ($extension === 'svg') {
            $sanitizedSvg = $this->sanitizeSvg($uploadedLogo);
            $path = "{$directory}/".Str::uuid().'.svg';

            Storage::disk($disk)->put($path, $sanitizedSvg);

            return $path;
        }

        return $uploadedLogo->store($directory, $disk);
    }

    private function sanitizeSvg(UploadedFile $uploadedLogo): string
    {
        $contents = $uploadedLogo->get();

        if ($contents === false) {
            throw ValidationException::withMessages([
                'logo_upload' => 'SVG-filen kunne ikke laeses.',
            ])->errorBag('updateSiteHeader');
        }

        try {
            return (new SvgSanitizer())->sanitize($contents);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'logo_upload' => $exception->getMessage(),
            ])->errorBag('updateSiteHeader');
        }
    }
}
