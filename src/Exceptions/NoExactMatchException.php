<?php

namespace ApiCheck\Api\Exceptions;

class NoExactMatchException extends \Exception
{
    /**
     * @var array
     */
    private $numberAdditions;

    public function __construct($message = '', $code = 0, ?\Throwable $previous = null, array $numberAdditions = [])
    {
        parent::__construct($message, $code, $previous);
        $this->numberAdditions = $numberAdditions;
    }

    /**
     * Get the suggested number additions returned by the API.
     *
     * @return array
     */
    public function getNumberAdditions(): array
    {
        return $this->numberAdditions;
    }
}
