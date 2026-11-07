<?php

namespace App\Services;

/**
 * Satu-satunya pintu pengecekan feature gate per server.
 *
 * Feature yang tidak tercantum pada COMPANY_FEATURES dianggap nonaktif
 * (fail closed). Route middleware, controller/Action, dan notification
 * memakai resolver yang sama supaya status yang dibagikan ke frontend tidak
 * pernah menjadi satu-satunya enforcement.
 */
class CompanyFeature
{
    /**
     * Apakah feature aktif pada server ini.
     */
    public function enabled(string $feature): bool
    {
        return in_array($feature, $this->active(), true);
    }

    /**
     * Feature aktif, sudah dinormalisasi menjadi daftar string unik.
     *
     * @return array<int, string>
     */
    public function active(): array
    {
        return collect((array) config('company.features', []))
            ->filter(fn ($feature): bool => is_string($feature) && $feature !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Status seluruh feature yang dikenal, untuk UX di frontend.
     *
     * @return array<string, bool>
     */
    public function known(): array
    {
        $statuses = [];

        foreach ((array) config('company.known_features', []) as $feature) {
            if (is_string($feature) && $feature !== '') {
                $statuses[$feature] = $this->enabled($feature);
            }
        }

        return $statuses;
    }
}
