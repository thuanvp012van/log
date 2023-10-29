<?php

namespace Penguin\Component\Logger;

class LogManager
{
    /**
     * Default driver
     */
    protected string $default = '';

    /**
     * @var \Penguin\Component\Logger\Logger[]
     */
    protected array $drivers = [];

    protected array $shareContext = [];

    public function __construct()
    {
        $this->default = config('logging.default');
        $this->driver($this->default);
    }

    public function driver(string $driver): Logger
    {
        if (!empty($this->drivers[$driver])) {
            return $this->drivers[$driver];
        }
        return $this->make($driver);
    }

    public function drivers(string ...$drivers): object
    {
        return new class($this, $drivers) {
            public function __construct(private LogManager $logManager, private array $drivers) {}

            public function __call($name, $arguments)
            {
                foreach ($this->drivers as $driver) {
                    $this->logManager->driver($driver)->$name(...$arguments);
                }
            }
        };
    }

    protected function make(string $driver): Logger
    {
        if (in_array($driver, array_keys((array)config('logging.drivers')))) {
            $config = config("logging.drivers.$driver");
            $logger = new Logger($config->channel);
            $params = $config->handler;
            $handler = $params->{0};
            unset($params->{0});
            $handler = new $handler(...(array)$params);
            foreach ($config->formatters as $formatter => $params) {
                $handler->setFormatter(new $formatter(...(array)$params));
            }
            $logger->pushHandler($handler);
            $this->drivers[$driver] = $logger;
            return $this->drivers[$driver];
        }
        throw new DriverNotFound("Driver $driver does not exist");
    }

    public function __call($name, $arguments): mixed
    {
        return $this->drivers[$this->default]->$name(...$arguments);
    }
}