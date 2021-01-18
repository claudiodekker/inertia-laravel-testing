<?php

namespace ClaudioDekker\Inertia\Macros;

/**
 * This class can be used by mixin it in:
 *   Assert::mixin(new VarDumper());.
 *
 * @see ClaudioDekker\Inertia\Assert
 */
class VarDumper
{
    public function dd()
    {
        return function (string $prop = null) {
            dd($this->prop($prop));
        };
    }

    public function dump()
    {
        return function (string $prop = null) {
            dump($this->prop($prop));

            return $this;
        };
    }
}
