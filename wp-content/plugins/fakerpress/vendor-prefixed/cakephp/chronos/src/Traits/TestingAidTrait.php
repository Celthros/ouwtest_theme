<?php
declare(strict_types=1);

/**
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice. Provides various operator methods for datetime
 * objects.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @copyright     Copyright (c) Brian Nesbitt <brian@nesbot.com>
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace FakerPress\ThirdParty\Cake\Chronos\Traits;

use FakerPress\ThirdParty\Cake\Chronos\Chronos;
use FakerPress\ThirdParty\Cake\Chronos\ChronosInterface;

/**
 * Provides methods for setting a 'test' now. This lets you
 * retrieve pre-determined times with now().
 */
trait TestingAidTrait
{
    /**
     * Set the test now used by Date and Time classes provided by Chronos
     *
     * @see \FakerPress\ThirdParty\Cake\Chronos\Chronos::setTestNow()
     * @param \FakerPress\ThirdParty\Cake\Chronos\ChronosInterface|string|null $testNow The instance to use for all future instances.
     * @return void
     */
    public static function setTestNow($testNow = null): void
    {
        Chronos::setTestNow($testNow);
    }

    /**
     * Get the test instance stored in Chronos
     *
     * @see \FakerPress\ThirdParty\Cake\Chronos\Chronos::getTestNow()
     * @return \FakerPress\ThirdParty\Cake\Chronos\ChronosInterface|null the current instance used for testing or null.
     */
    public static function getTestNow(): ?ChronosInterface
    {
        return Chronos::getTestNow();
    }

    /**
     * Get whether or not Chronos has a test instance set.
     *
     * @see \FakerPress\ThirdParty\Cake\Chronos\Chronos::hasTestNow()
     * @return bool True if there is a test instance, otherwise false
     */
    public static function hasTestNow(): bool
    {
        return Chronos::hasTestNow();
    }
}
