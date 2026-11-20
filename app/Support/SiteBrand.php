<?php

namespace App\Support;

use App\Domain\Settings\Services\SettingService;
use Illuminate\Support\Facades\Config;

class SiteBrand
{
    public function name(): string
    {
        $name = trim((string) app(SettingService::class)->get('system.site_name', config('app.name')));

        if ($name !== '') {
            Config::set('app.name', $name);

            return $name;
        }

        return (string) config('app.name');
    }

    public function text(?string $text): string
    {
        return $this->replaceOutsideUrls($text ?? '', $this->name());
    }

    public function html(string $html): string
    {
        $protected = [];
        $protectedHtml = preg_replace_callback(
            '~<(script|style)\b[^>]*>.*?</\1\s*>~is',
            static function (array $matches) use (&$protected): string {
                $key = 'SITE_BRAND_PROTECTED_'.bin2hex(random_bytes(12)).'_END';
                $protected[$key] = $matches[0];

                return $key;
            },
            $html,
        );
        if ($protectedHtml === null) {
            throw new \RuntimeException('Unable to protect scripts and styles while rendering site branding.');
        }
        $html = $protectedHtml;

        $brandedText = preg_replace_callback(
            '~(^|>)([^<>]+)(?=<|$)~',
            fn (array $matches): string => $matches[1].$this->replaceOutsideUrls(
                $matches[2],
                htmlspecialchars($this->name(), ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            ),
            $html,
        );
        if ($brandedText === null) {
            throw new \RuntimeException('Unable to update HTML text while rendering site branding.');
        }
        $html = $brandedText;

        $brandedAttributes = preg_replace_callback(
            '~\b(alt|title|aria-label)\s*=\s*(["\'])(.*?)\2~is',
            fn (array $matches): string => $matches[1].'='.$matches[2].$this->replaceOutsideUrls(
                $matches[3],
                htmlspecialchars($this->name(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            ).$matches[2],
            $html,
        );
        if ($brandedAttributes === null) {
            throw new \RuntimeException('Unable to update HTML labels while rendering site branding.');
        }

        return strtr($brandedAttributes, $protected);
    }

    private function replaceOutsideUrls(string $text, string $replacement): string
    {
        $parts = preg_split('~(https?://[^\s<>"\']+)~i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            throw new \RuntimeException('Unable to split URLs while rendering site branding.');
        }

        foreach ($parts as $index => $part) {
            if ($index % 2 === 0) {
                $parts[$index] = str_ireplace('WorkLinc', $replacement, $part);
            }
        }

        return implode('', $parts);
    }
}
