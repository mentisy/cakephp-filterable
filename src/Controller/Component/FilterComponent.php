<?php
declare(strict_types=1);

namespace Avolle\Filterable\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\ORM\Query;

/**
 * Filter component
 */
class FilterComponent extends Component
{
    /**
     * Default configuration.
     * - allowAll - Whether to allow filtering all fields or only from the allowList configuration
     * - allowList - Only allow filtering the configured database fields
     *
     * @var array
     */
    protected $_defaultConfig = [
        'allowAll' => false,
        'allowList' => [],
    ];

    /**
     * Filter conditionals to apply to database query
     *
     * @var array
     */
    protected array $conditions = [];

    /**
     * FilterComponent constructor.
     *
     * @param \Cake\Controller\ComponentRegistry $registry Component Registry
     * @param array $config Component config
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);

        $this->conditions = $this->unpackFiltersFromRequest();
    }

    /**
     * Apply filter conditions to query object
     *
     * @param \Cake\ORM\Query $query Query object
     * @return \Cake\ORM\Query
     */
    public function applyConditions(Query $query): Query
    {
        return $query->where($this->conditions);
    }

    /**
     * Get the filtered conditions
     *
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * Unpack filters from the request query string, and store them as a database query conditional array
     *
     * @return array
     */
    protected function unpackFiltersFromRequest(): array
    {
        $queryParams = $this->_registry->getController()->getRequest()->getQueryParams();
        $conditions = [];

        if ($this->isInvalidQuery($queryParams)) {
            return [];
        }

        foreach ($queryParams['filter'] as $key => $filterField) {
            if ($this->getConfig('allowAll') || in_array($filterField, $this->getConfig('allowList'))) {
                if (isset($queryParams['value'][$key])) {
                    $filterValue = $queryParams['value'][$key];
                    $conditions[$filterField] = $filterValue;
                }
            }
        }

        return $conditions;
    }

    /**
     * Checks if the URL query array is invalid. Both filter and value keys must be an array
     *
     * @param array $queryParams Query parameter
     * @return bool
     */
    protected function isInvalidQuery(array $queryParams): bool
    {
        return !isset($queryParams['filter']) || !is_array($queryParams['filter']) ||
            !isset($queryParams['value']) || !is_array($queryParams['value']);
    }
}
