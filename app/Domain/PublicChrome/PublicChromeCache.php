<?php
namespace App\Domain\PublicChrome;
use Illuminate\Support\Facades\Cache; use Throwable;
class PublicChromeCache
{
    public const BRANDING='public.chrome.v1.branding'; public const HEADER='public.chrome.v1.header'; public const PRIMARY='public.chrome.v1.navigation.primary'; public const FOOTER='public.chrome.v1.footer';
    public static function forgetAll(): void { foreach ([self::BRANDING,self::HEADER,self::PRIMARY,self::FOOTER] as $key) { try { Cache::forget($key); } catch (Throwable) {} } }
}
