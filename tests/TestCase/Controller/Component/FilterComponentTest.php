<?php
declare(strict_types=1);

namespace Avolle\Filterable\Test\TestCase\Controller\Component;

use Avolle\Filterable\Controller\Component\FilterComponent;
use Avolle\Filterable\Test\RequestTrait;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;

/**
 * FilterComponent Test Case
 */
class FilterComponentTest extends TestCase
{
    use RequestTrait;

    /**
     * Test subject
     *
     * @var \Avolle\Filterable\Controller\Component\FilterComponent
     */
    protected FilterComponent $Filter;

    /**
     * Test unpackFiltersFromRequest method
     * No query parameters, so the filter conditional array should be empty
     *
     * @return void
     * @uses FilterComponent::unpackFiltersFromRequest()
     */
    public function testUnpackFiltersFromRequestNoQueryParameters(): void
    {
        $this->createComponentWithControllerRequest();
        $this->assertEmpty($this->Filter->getConditions());
    }

    /**
     * Test unpackFiltersFromRequest method
     * Query parameter contains filter[0] but not a value[0]. Should be invalid and conditional empty
     *
     * @return void
     * @uses FilterComponent::unpackFiltersFromRequest()
     */
    public function testUnpackFiltersFromRequestInvalidArray(): void
    {
        $this->createComponentWithControllerRequest(['filter' => ['type']], ['allowAll' => true]);
        $this->assertEmpty($this->Filter->getConditions());

        $this->createComponentWithControllerRequest(['filter' => ['type'], 'value' => [1 => 'someType']], ['allowAll' => true]);
        $this->assertEmpty($this->Filter->getConditions());

        $this->createComponentWithControllerRequest(['filter' => [1 => 'type'], 'value' => ['someType']], ['allowAll' => true]);
        $this->assertEmpty($this->Filter->getConditions());
    }

    /**
     * Test unpackFiltersFromRequest method
     * Allow all filters enabled
     * Query contains filter fields location and type, and values firstLocation and firstType
     * Conditional should be [location => firstLocation, type => firstType]
     *
     * @return void
     * @uses FilterComponent::unpackFiltersFromRequest()
     */
    public function testUnpackFiltersFromRequestAllowAllIsTrue(): void
    {
        $this->createComponentWithControllerRequest(
            ['filter' => ['location', 'type'], 'value' => ['firstLocation', 'firstType']],
            ['allowAll' => true],
        );
        $expected = [
            'location' => 'firstLocation',
            'type' => 'firstType',
        ];
        $actual = $this->Filter->getConditions();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test unpackFiltersFromRequest method
     * Allow all filters disabled
     * Query contains filter fields location and type, and values firstLocation and firstType
     * Conditional should be [location => firstLocation, type => firstType]
     *
     * @return void
     * @uses FilterComponent::unpackFiltersFromRequest()
     */
    public function testUnpackFiltersFromRequestAllowAllIsFalse(): void
    {
        $this->createComponentWithControllerRequest(
            ['filter' => ['location', 'type'], 'value' => ['firstLocation', 'firstType']],
            ['allowAll' => false],
        );
        $this->assertEmpty($this->Filter->getConditions());
    }

    /**
     * Test unpackFiltersFromRequest method
     * Allow all filters disabled
     * Allow list configured with requested filter
     * Query contains filter fields location and type, and values firstLocation and firstType
     * Conditional should be [location => firstLocation, type => firstType]
     *
     * @return void
     * @uses FilterComponent::unpackFiltersFromRequest()
     */
    public function testUnpackFiltersFromRequestAllowListMatches(): void
    {
        $this->createComponentWithControllerRequest(
            ['filter' => ['location', 'type'], 'value' => ['firstLocation', 'firstType']],
            ['allowList' => ['location', 'type']],
        );
        $expected = [
            'location' => 'firstLocation',
            'type' => 'firstType',
        ];
        $actual = $this->Filter->getConditions();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test unpackFiltersFromRequest method
     * Allow all filters disabled
     * Allow list configured with requested filter
     * Query contains filter fields location and type, and values firstLocation and firstType
     * Conditional should be [location => firstLocation, type => firstType]
     *
     * @return void
     * @uses FilterComponent::unpackFiltersFromRequest()
     */
    public function testUnpackFiltersFromRequestAllowListMatchesOnlyOneFilter(): void
    {
        $this->createComponentWithControllerRequest(
            ['filter' => ['location', 'type'], 'value' => ['firstLocation', 'firstType']],
            ['allowList' => ['location']],
        );
        $expected = [
            'location' => 'firstLocation',
        ];
        $actual = $this->Filter->getConditions();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test unpackFiltersFromRequest method
     * Query is of invalid type. Filter and value parameters should be array. Ignore everything else
     *
     * @return void
     * @uses FilterComponent::unpackFiltersFromRequest()
     */
    public function testUnpackFiltersFromRequestQueryNotArray(): void
    {
        $this->createComponentWithControllerRequest(['filter' => 'location', 'value' => 'firstLocation']);
        $this->assertEmpty($this->Filter->getConditions());

        $this->createComponentWithControllerRequest(['filter' => ['location'], 'value' => 'firstLocation']);
        $this->assertEmpty($this->Filter->getConditions());
        $this->createComponentWithControllerRequest(['filter' => 'location', 'value' => ['firstLocation']]);
        $this->assertEmpty($this->Filter->getConditions());
    }

    /**
     * Create a FilterComponent instance with a predetermined ServerRequest
     *
     * @param array $query Query parameters
     * @param array $config Component Config
     * @return void
     */
    protected function createComponentWithControllerRequest(array $query = [], array $config = []): void
    {
        $request = $this->makeRequest('/', $query);
        $controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setConstructorArgs([$request])
            ->addMethods(['getParam', 'getQueryParam'])
            ->getMock();
        $registry = new ComponentRegistry($controller);
        $this->Filter = new FilterComponent($registry, $config);
    }
}
