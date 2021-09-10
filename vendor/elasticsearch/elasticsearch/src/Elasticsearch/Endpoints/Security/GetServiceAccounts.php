<?php
/**
 * Elasticsearch PHP client
 *
 * @link      https://github.com/elastic/elasticsearch-php/
 * @copyright Copyright (c) Elasticsearch B.V (https://www.elastic.co)
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license   https://www.gnu.org/licenses/lgpl-2.1.html GNU Lesser General Public License, Version 2.1 
 * 
 * Licensed to Elasticsearch B.V under one or more agreements.
 * Elasticsearch B.V licenses this file to you under the Apache 2.0 License or
 * the GNU Lesser General Public License, Version 2.1, at your option.
 * See the LICENSE file in the project root for more information.
 */
declare(strict_types = 1);

namespace Elasticsearch\Endpoints\Security;

use Elasticsearch\Endpoints\AbstractEndpoint;

/**
 * Class GetServiceAccounts
 * Elasticsearch API name security.get_service_accounts
 *
 * NOTE: this file is autogenerated using util/GenerateEndpoints.php
 * and Elasticsearch 7.13.1 (9a7758028e4ea59bcab41c12004603c5a7dd84a9)
 */
class GetServiceAccounts extends AbstractEndpoint
{
    protected $namespace;
    protected $service;

    public function getURI(): string
    {
        $namespace = $this->namespace ?? null;
        $service = $this->service ?? null;

        if (isset($namespace) && isset($service)) {
            return "/_security/service/$namespace/$service";
        }
        if (isset($namespace)) {
            return "/_security/service/$namespace";
        }
        return "/_security/service";
    }

    public function getParamWhitelist(): array
    {
        return [
            
        ];
    }

    public function getMethod(): string
    {
        return 'GET';
    }

    public function setNamespace($namespace): GetServiceAccounts
    {
        if (isset($namespace) !== true) {
            return $this;
        }
        $this->namespace = $namespace;

        return $this;
    }

    public function setService($service): GetServiceAccounts
    {
        if (isset($service) !== true) {
            return $this;
        }
        $this->service = $service;

        return $this;
    }
}
