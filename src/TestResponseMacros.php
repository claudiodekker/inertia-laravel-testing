<?php

namespace ClaudioDekker\Inertia;

use Closure;

class TestResponseMacros
{
    public function assertInertia()
    {
        return function (Closure $callback = null) {
            $assert = Assert::fromTestResponse($this);

            if (! is_null($callback)) {
                $callback($assert);

                $assert->interacted();
            }

            return $this;
        };
    }
}
