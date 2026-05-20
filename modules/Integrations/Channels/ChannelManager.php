<?php

namespace Modules\Integrations\Channels;

use Illuminate\Contracts\Foundation\Application;

/**
 * Kanal kayıt + çözümleme noktası.
 *
 * Service provider'lar kendi kanallarını register() ile bağlar,
 * controller'lar get() ile çözer.
 */
class ChannelManager
{
    /** @var array<string, class-string<ChannelInterface>> */
    protected array $channels = [];

    public function __construct(protected Application $app)
    {
    }

    public function register(string $name, string $channelClass): void
    {
        $this->channels[$name] = $channelClass;
    }

    public function has(string $name): bool
    {
        return isset($this->channels[$name]);
    }

    public function get(string $name): ChannelInterface
    {
        if (!isset($this->channels[$name])) {
            throw new \InvalidArgumentException("Channel not registered: {$name}");
        }

        return $this->app->make($this->channels[$name]);
    }

    /**
     * @return array<int, array{name:string,enabled:bool,class:string}>
     */
    public function all(): array
    {
        $out = [];
        foreach ($this->channels as $name => $class) {
            try {
                $instance = $this->app->make($class);
                $out[] = [
                    'name' => $name,
                    'enabled' => $instance->isEnabled(),
                    'class' => $class,
                ];
            } catch (\Throwable $e) {
                $out[] = ['name' => $name, 'enabled' => false, 'class' => $class];
            }
        }
        return $out;
    }
}
