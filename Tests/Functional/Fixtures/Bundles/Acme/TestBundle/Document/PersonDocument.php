<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ApiBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * Class Person Document.
 *
 * @ES\Document(type="person")
 */
class PersonDocument
{
    /**
     * @var string
     *
     * @ES\Property(name="name", type="string", index="not_analyzed")
     */
    protected $name;

    /**
     * @var string
     *
     * @ES\Property(name="surname", type="string", index="not_analyzed")
     */
    protected $surname;
}
