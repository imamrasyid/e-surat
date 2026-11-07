<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Preflight konfigurasi company sebelum `config:cache` dan `migrate`.
 *
 * Dijalankan pada CI dan setiap deploy. Exit code 1 menghentikan promosi
 * artifact, sehingga feature/ruleset yang tidak dikenal atau feature aktif
 * tanpa konfigurasi wajib tidak pernah sampai ke production.
 */
class ValidateCompanyConfigCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'company:validate-config';

    /**
     * @var string
     */
    protected $description = 'Validasi COMPANY_KEY, feature, dan ruleset sebelum deploy';

    public function handle(): int
    {
        $errors = $this->collectErrors();

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }

            $this->newLine();
            $this->line('Konfigurasi company ditolak. Hentikan deploy.');

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Konfigurasi company valid: key=%s ruleset=%s features=%s',
            (string) config('company.key'),
            (string) config('company.ruleset'),
            implode(',', $this->activeFeatures()) ?: '-',
        ));

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function collectErrors(): array
    {
        $errors = [];

        if (blank(config('company.key'))) {
            $errors[] = 'COMPANY_KEY wajib diisi.';
        }

        $ruleset = (string) config('company.ruleset');
        $rulesets = $this->stringList(config('company.rulesets', []));

        if (! in_array($ruleset, $rulesets, true)) {
            $errors[] = sprintf(
                'COMPANY_RULESET [%s] tidak dikenal. Ruleset yang terdaftar: %s.',
                $ruleset,
                implode(', ', $rulesets) ?: '-'
            );
        }

        $knownFeatures = $this->stringList(config('company.known_features', []));
        $requirements = (array) config('company.feature_requirements', []);

        foreach ($this->activeFeatures() as $feature) {
            if (! in_array($feature, $knownFeatures, true)) {
                $errors[] = sprintf(
                    'COMPANY_FEATURES memuat feature tidak dikenal [%s]. Feature yang dikenal: %s.',
                    $feature,
                    implode(', ', $knownFeatures) ?: '-'
                );

                continue;
            }

            $rules = $requirements[$feature] ?? null;

            if (! is_array($rules) || $rules === []) {
                $errors[] = sprintf('Feature [%s] tidak memiliki aturan konfigurasi wajib.', $feature);

                continue;
            }

            foreach ((array) ($rules['required'] ?? []) as $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }

                if (blank(config($path))) {
                    $errors[] = sprintf('Feature [%s] aktif tetapi %s belum diisi.', $feature, $path);
                }
            }

            foreach ((array) ($rules['not_in'] ?? []) as $path => $disallowed) {
                if (! is_string($path) || $path === '') {
                    continue;
                }

                $value = config($path);

                if (in_array($value, (array) $disallowed, true)) {
                    $errors[] = sprintf(
                        'Feature [%s] aktif tetapi %s bernilai [%s] dan tidak diperbolehkan.',
                        $feature,
                        $path,
                        is_scalar($value) ? (string) $value : get_debug_type($value)
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * @return array<int, string>
     */
    private function activeFeatures(): array
    {
        return $this->stringList(config('company.features', []));
    }

    /**
     * @return array<int, string>
     */
    private function stringList(mixed $value): array
    {
        return array_values(array_filter(
            (array) $value,
            fn ($item): bool => is_string($item) && trim($item) !== ''
        ));
    }
}
