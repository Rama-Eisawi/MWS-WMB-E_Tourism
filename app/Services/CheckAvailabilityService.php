<?php

namespace App\Services;

use App\Models\Tour;

class CheckAvailabilityService
{
    /**
     * Check if a resource is available (guide or driver).
     *
     * @param int $resourceId
     * @param string $resourceType ('guide' or 'driver')
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    private function isResourceAvailable(int $resourceId, string $resourceType, string $startDate, string $endDate): bool
    {
        $column = $resourceType . '_id'; // Determine the column name based on resource type

        return !Tour::where($column, $resourceId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->exists();
    }

    /**
     * Check if a guide is available.
     *
     * @param int $guideId
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function isGuideAvailable(int $guideId, string $startDate, string $endDate): bool
    {
        return $this->isResourceAvailable($guideId, 'guide', $startDate, $endDate);
    }

    /**
     * Check if a driver is available.
     *
     * @param int $driverId
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function isDriverAvailable(int $driverId, string $startDate, string $endDate): bool
    {
        return $this->isResourceAvailable($driverId, 'driver', $startDate, $endDate);
    }
}
