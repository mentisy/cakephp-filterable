<?php
declare(strict_types=1);

namespace Avolle\Filterable\Test;

use Cake\Http\ServerRequest;

/**
 * Trait RequestTrait
 */
trait RequestTrait
{
    /**
     * Create a server request
     *
     * @param string $url Url
     * @param array $query Query parameters
     * @return \Cake\Http\ServerRequest
     */
    protected function makeRequest(string $url = '/', array $query = []): ServerRequest
    {
        $request = [
            'url' => $url,
            'query' => $query,
            'params' => [
                'plugin' => null,
                'controller' => 'tools',
                'action' => 'index',
            ],
        ];

        return new ServerRequest($request);
    }
}
