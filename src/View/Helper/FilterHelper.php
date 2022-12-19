<?php
declare(strict_types=1);

namespace Avolle\Filterable\View\Helper;

use Cake\View\Helper;
use Cake\View\View;

/**
 * Filter helper
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class FilterHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    protected $helpers = [
        'Html',
    ];

    /**
     * Unmodified query params from request.
     *
     * @var array
     */
    protected array $queryParams = [];

    /**
     * URI query from request
     *
     * @var array
     */
    protected array $requestQuery = [];

    /**
     * FilterHelper constructor.
     *
     * @param \Cake\View\View $view View
     * @param array $config Config
     */
    public function __construct(View $view, array $config = [])
    {
        parent::__construct($view, $config);

        $this->queryParams = $queryParams = $view->getRequest()->getQueryParams();
        if (isset($queryParams['filter']) && isset($queryParams['value'])) {
            $this->collectActiveFilters($queryParams);
        }
    }

    /**
     * Creates an HTML query string link
     *
     * @param string|array $title The content to be wrapped by `<a>` tags.
     * @param string $field Field
     * @param string $value Value
     * @param array $options Array of options and HTML attributes.
     * @return string An `<a />` element.
     */
    public function link($title, string $field, string $value, array $options = []): string
    {
        $queryUrl = $this->buildQuery($field, $value);

        return $this->Html->link($title, ['?' => $queryUrl], $options);
    }

    /**
     * Whether the input $field and $value is the current filter in the request query
     *
     * @param string $field Filter field
     * @param string $value Filter value
     * @return bool
     */
    public function isCurrentFilter(string $field, string $value): bool
    {
        if (isset($this->requestQuery[$field])) {
            return $this->requestQuery[$field] === $value;
        }

        return false;
    }

    /**
     * Gather the active filters
     *
     * @param array $queryParams Request query parameters
     * @return void
     */
    protected function collectActiveFilters(array $queryParams): void
    {
        $filter = is_array($queryParams['filter']) ? $queryParams['filter'] : [$queryParams['filter']];
        $value = is_array($queryParams['value']) ? $queryParams['value'] : [$queryParams['value']];
        if (count($filter) && count($value)) {
            $this->requestQuery = array_combine($filter, $value);
        }
    }

    /**
     * Generate the query string array
     * If user re-clicks the same filter and value, it will remove that filter. Otherwise, add to current filter values
     *
     * @param string $field Field to add or remove filter
     * @param string $value Value to filter field
     * @return array[]
     */
    protected function buildQuery(string $field, string $value): array
    {
        $query = $this->requestQuery;
        if ($this->isCurrentFilter($field, $value)) {
            unset($query[$field]);
        } else {
            $query[$field] = $value;
        }

        $queryUrl = ['filter' => [], 'value' => []];
        foreach ($query as $field => $value) {
            $queryUrl['filter'][] = $field;
            $queryUrl['value'][] = $value;
        }
        $otherQueries = array_diff_key($this->queryParams, $queryUrl);

        return array_merge($otherQueries, $queryUrl);
    }
}
