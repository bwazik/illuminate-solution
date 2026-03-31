<?php

namespace App\Relations;

use App\Models\Incident;
use App\Models\Neighborhood;
use Illuminate\Database\Eloquent\Collection;

class DonutRelation
{
    public function __construct(
        private readonly Neighborhood $neighborhood,
        private readonly float $innerRadius = 0.5,
        private readonly float $outerRadius = 2.0
    ) {
    }

    private function getCentroid(): array
    {
        $boundary = trim($this->neighborhood->boundary, '()');
        $points = explode('),(', $boundary);

        $latSum = $lonSum = 0;
        $count = count($points);

        foreach ($points as $point) {
            [$lat, $lon] = explode(',', trim($point, '()'));
            $latSum += (float) $lat;
            $lonSum += (float) $lon;
        }

        return [
            'lat' => $latSum / $count,
            'lon' => $lonSum / $count,
        ];
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function parseLocation(string $location): array
    {
        $location = trim($location, '()');
        [$lat, $lon] = explode(',', $location);
        return [(float) $lat, (float) $lon];
    }

    public function get(): Collection
    {
        $centroid = $this->getCentroid();

        $incidents = Incident::all()->map(function ($incident) use ($centroid) {
            [$lat, $lon] = $this->parseLocation($incident->location);
            $incident->distance_km = $this->haversine($centroid['lat'], $centroid['lon'], $lat, $lon);
            return $incident;
        })
            ->filter(fn($i) => $i->distance_km >= $this->innerRadius && $i->distance_km <= $this->outerRadius)
            ->sortBy('distance_km')
            ->values();

        return new Collection($incidents);
    }
}
