<?php

namespace ClaudioDekker\Inertia;

use Closure;

class TestResponseMacros
{
    public function assertInertia()
    {
        return function (Closure $callback = null) {
            $assert = Assert::fromTestResponse($this);

            if (is_null($callback)) {
                return $this;
            }

            $callback($assert);

            if (config('inertia.force_top_level_property_interaction', true)) {
                $assert->interacted();
            }

            return $this;
        };
    }
}
