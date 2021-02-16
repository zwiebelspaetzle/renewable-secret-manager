<?php
namespace RenewableSecretManager\SecretManager;

class SecretManager
{
    private $secrets = [];

    public function getSecret(string $key)
    {
        return $this->secrets[$key]->retrieve();
    }

    public function newStaticSecret(string $key, $value)
    {
        $this->secrets[$key] = new StaticSecret($value);
    }

    public function newRenewableSecret(string $key, Callable $fetcher, Callable $checker = null)
    {
        $this->secrets[$key] = new RenewableSecret($fetcher, $checker);
    }
}

class StaticSecret
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function retrieve()
    {
        return $this->value;
    }
}

class RenewableSecret
{
    private $value = null;
    protected $fetcher;  // Callable
    protected $checker;  // Callable

    public function __construct(callable $fetcher, callable $checker)
    {
        $this->fetcher = $fetcher;
        $this->checker = $checker;
    }

    public function retrieve()
    {
        if ($this->value === null || \Closure::fromCallable($this->checker)($this->value) == false) {
            $this->value = \Closure::fromCallable($this->fetcher)();
        } else {
        }
        return $this->value;
    }
}
