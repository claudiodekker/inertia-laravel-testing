<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    |
    | The values described here are used to locate Inertia components on the
    | filesystem. For instance, when using `assertInertia`, the assertion
    | attempts to locate the component as a file relative to any of the
    | paths AND with any of the extensions specified here.
    |
    */

    'page' => [

        /**
         * Determines whether assertions should check that Inertia page components
         * actually exist on the filesystem instead of just checking responses.
         */
        'should_exist' => true,

        /*
         * A list of root paths to your Inertia page components.
         */
        'paths' => [

            resource_path('js/Pages'),

        ],

        /*
         * A list of valid Inertia page component extensions.
         */
        'extensions' => [

            'vue',
            'svelte',

        ],

    ],

];
