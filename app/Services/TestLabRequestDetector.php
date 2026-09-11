<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class TestLabRequestDetector
{
    public function matches(Request $request): bool
    {
        if ($request->user()?->is_test_account) {
            return true;
        }

        if (filter_var($request->header('X-Firebase-Test-Lab'), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        if (! str_contains(strtolower((string) $request->userAgent()), 'okhttp')) {
            return false;
        }

        return IpUtils::checkIp($request->ip(), config('test_lab.ip_ranges', []));
    }
}
