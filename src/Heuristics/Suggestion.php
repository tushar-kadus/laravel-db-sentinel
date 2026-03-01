<?php

namespace Atmos\DbSentinel\Heuristics;

class Suggestion
{
    const CRITICAL = 'critical';
    const HIGH     = 'high';
    const MEDIUM   = 'medium';
    const LOW      = 'low';

    public $title;
    public $message;
    public $severity;
    public $handler; // Heuristic class name for debugging

    public function __construct(string $title, string $message, string $severity = self::MEDIUM)
    {
        $this->title = $title;
        $this->message = $message;
        $this->severity = $severity;
    }

    public static function make(string $title, string $message): self
    {
        return new static($title, $message);
    }

    public function setHandler(string $handler): self
    {
        $this->handler = $handler;
        return $this;
    }

    public function critical(): self
    {
        $this->severity = self::CRITICAL;
        return $this;
    }

    public function high(): self
    {
        $this->severity = self::HIGH;
        return $this;
    }

    public function medium(): self
    {
        $this->severity = self::MEDIUM;
        return $this;
    }

    public function low(): self
    {
        $this->severity = self::LOW;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'title'    => $this->title,
            'message'  => $this->message,
            'severity' => $this->severity,
        ];
    }
}
