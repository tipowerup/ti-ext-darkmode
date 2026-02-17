<?php

declare(strict_types=1);

namespace Tipowerup\Darkmode\Http\Middleware;

use Closure;
use Igniter\Flame\Support\Facades\Igniter;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpFoundation\Response;
use Tipowerup\Darkmode\Models\Settings;

class InjectDarkmodeScript
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process standard HTTP responses, not binary/streamed responses
        if (!$response instanceof IlluminateResponse) {
            return $response;
        }

        if (!$this->shouldInject($response)) {
            return $response;
        }

        $this->injectAntiFlickerScript($response);

        return $response;
    }

    protected function shouldInject(IlluminateResponse $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return false;
        }

        if (!Settings::isEnabled()) {
            return false;
        }

        $isAdmin = Igniter::runningInAdmin();

        if ($isAdmin && !Settings::appliesToAdmin()) {
            return false;
        }

        if (!$isAdmin && !Settings::appliesToFrontend()) {
            return false;
        }

        return true;
    }

    protected function injectAntiFlickerScript(IlluminateResponse $response): void
    {
        $content = $response->getContent();

        // Verify content is valid and not false/empty
        if ($content === false || $content === '') {
            return;
        }

        $script = '<style id="ti-dm-af">html.ti-dm-pending{background:#181a1b!important;color-scheme:dark}html.ti-dm-pending body{visibility:hidden}</style>'
            .'<script>(function(){if(localStorage.getItem("ti_darkmode")==="on"){document.documentElement.classList.add("ti-dm-pending")}var t=setTimeout(function(){document.documentElement.classList.remove("ti-dm-pending");document.body&&(document.body.style.visibility="")},3000);window.__tiDmReady=function(){clearTimeout(t);document.documentElement.classList.remove("ti-dm-pending");document.body&&(document.body.style.visibility="")}})()</script>';

        $pos = stripos($content, '<head>');
        if ($pos !== false) {
            $content = substr($content, 0, $pos + 6).$script.substr($content, $pos + 6);
        }

        $response->setContent($content);
    }
}
