<?php declare(strict_types=1);

namespace Shopware\Core\Content\Media\Path\Contract\Event;

use Shopware\Core\Framework\Log\Package;

/**
 * @public
 *
 * @implements \IteratorAggregate<array-key, string>
 *
 * This event can be dispatch, to generate the path for a media afterward and store it in the database.
 * The `MediaSubscriber` will listen to this event and generate the path for the media.
 */
#[Package('core')]
class UpdateMediaPathEvent implements \IteratorAggregate
{
    /**
     * @param array<string> $ids
     */
    public function __construct(public readonly array $ids)
    {
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->ids);
    }
}
