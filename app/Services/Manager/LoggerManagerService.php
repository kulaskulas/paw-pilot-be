<?php

namespace App\Services\Manager;
use App\Services\Logging\Auth\AuthenticationLoggerService;

class LoggerManagerService {

    private array $services = [];

    public function __construct(
        private AuthenticationLoggerService $authLoggerService
    )
    {
        $this->services = [
            'auth' => $this->authLoggerService,
        ];
    }


    public function getLoggerService(string $channel, $type, $data): void
    {
        $this->services[$channel]->{$type}($data) ?? null;
    }
}
