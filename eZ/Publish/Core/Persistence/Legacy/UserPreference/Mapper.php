<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\UserPreference;

use eZ\Publish\SPI\Persistence\UserPreference\UserPreference;

class Mapper
{
    /**
     * Extracts UserPreference objects from $rows.
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\UserPreference\UserPreference[]
     */
    public function extractUserPreferencesFromRows(array $rows): array
    {
        $userPreferences = [];
        foreach ($rows as $row) {
            $userPreferences[] = $this->extractUserPreferenceFromRow($row);
        }

        return $userPreferences;
    }

    /**
     * Extract UserPreference object from $row.
     *
     * @param array $row
     *
     * @return \eZ\Publish\SPI\Persistence\UserPreference\UserPreference
     */
    private function extractUserPreferenceFromRow(array $row): UserPreference
    {
        $userPreference = new UserPreference();
        $userPreference->id = (int)$row['id'];
        $userPreference->userId = (int)$row['user_id'];
        $userPreference->name = $row['name'];
        $userPreference->value = $row['value'];

        return $userPreference;
    }
}
