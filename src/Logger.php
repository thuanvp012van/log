<?php

namespace Penguin\Component\Logger;

use DateTimeImmutable;
use Monolog\Level;

class Logger extends \Monolog\Logger
{
    protected array $shareContext = [];

    public function shareContext(array $context): void
    {
        $this->shareContext = $context;
    }

    public function addRecord(int|Level $level, string $message, array $context = [], DateTimeImmutable $datetime = null): bool
    {
        $context = array_merge($this->shareContext, $context);
        return parent::addRecord($level, $message, $context, $datetime);
    }
}