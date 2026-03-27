<?php

namespace App\Actions;

use App\Models\Company;
use App\Models\Service;
use App\Services\AvailabilityService;

class GenerateAvailableSlotsAction
{
    public function __construct(private readonly AvailabilityService $availabilityService)
    {
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function execute(Company $company, int $serviceId, string $date): array
    {
        $service = Service::query()
            ->forCompany($company->id)
            ->where('is_active', true)
            ->findOrFail($serviceId);

        return $this->availabilityService->generateSlots($company, $service, $date);
    }
}
