<?php declare(strict_types=1);

namespace Shopware\Storefront\Framework\Cache\ReverseProxy;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see https://github.com/varnish/varnish-modules/blob/master/src/vmod_xkey.vcc
 */
class VarnishReverseProxyGateway extends AbstractReverseProxyGateway
{
    /**
     * @var array<string>
     */
    private array $hosts;

    private Client $client;

    private int $concurrency;

    /**
     * @internal
     *
     * @param string[] $hosts
     */
    public function __construct(array $hosts, int $concurrency, Client $client)
    {
        $this->hosts = $hosts;
        $this->concurrency = $concurrency;
        $this->client = $client;
    }

    public function getDecorated(): AbstractReverseProxyGateway
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @param array<string> $tags
     *
     * @deprecated tag:v6.5.0 - Parameter $response will be required reason:class-hierarchy-change
     */
    public function tag(array $tags, string $url/*, Response $response */): void
    {
        /** @var Response|null $response */
        $response = \func_num_args() === 3 ? func_get_arg(2) : null;

        if ($response === null) {
            throw new \InvalidArgumentException('Parameter $response is required for VarnishReverseProxyGateway');
        }

        $response->headers->set('xkey', implode(' ', $tags));
    }

    public function invalidate(array $tags): void
    {
        $list = [];

        foreach ($this->hosts as $host) {
            $list[] = new Request('PURGE', $host, ['xkey' => implode(' ', $tags)]);
        }

        $pool = new Pool($this->client, $list, [
            'concurrency' => $this->concurrency,
            'rejected' => function (TransferException $reason): void {
                if ($reason instanceof ServerException) {
                    throw new \RuntimeException(sprintf('BAN request failed to %s failed with error: %s', $reason->getRequest()->getUri()->__toString(), $reason->getMessage()), 0, $reason);
                }

                throw $reason;
            },
        ]);

        $pool->promise()->wait();
    }

    public function ban(array $urls): void
    {
        $list = [];

        foreach ($urls as $url) {
            foreach ($this->hosts as $host) {
                $list[] = new Request('PURGE', $host . $url);
            }
        }

        $pool = new Pool($this->client, $list, [
            'concurrency' => $this->concurrency,
            'rejected' => function (TransferException $reason): void {
                if ($reason instanceof ServerException) {
                    throw new \RuntimeException(sprintf('BAN request failed to %s failed with error: %s', $reason->getRequest()->getUri()->__toString(), $reason->getMessage()), 0, $reason);
                }

                throw $reason;
            },
        ]);

        $pool->promise()->wait();
    }
}
