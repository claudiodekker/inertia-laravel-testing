<?php

namespace ClaudioDekker\Inertia;

class InertiaTesting
{
    /**
     * Whether Inertia page component file existence should be tested for.
     *
     * @var bool
     */
    protected static $pageShouldExist = true;

    /**
     * Whether Inertia page component file existence checking is enabled or not.
     *
     * @return bool
     */
    public static function shouldCheckForPageExistence()
    {
        return static::$pageShouldExist;
    }

    /**
     * Disable Inertia page component file existence check.
     *
     * @return void
     */
    public static function disablePageExistenceCheck()
    {
        static::$pageShouldExist = false;
    }

    /**
     * Enable Inertia page component file existence check.
     *
     * @return void
     */
    public static function enablePageExistenceCheck()
    {
        static::$pageShouldExist = true;
    }
}
