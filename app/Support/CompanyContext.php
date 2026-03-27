<?php

namespace App\Support;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyContext
{
    public static function fromRequest(Request $request): ?Company
    {
        return $request->user()?->company;
    }

    public static function id(Request $request): ?int
    {
        return $request->user()?->company_id;
    }
}
