<?php
declare(strict_types=1);

namespace Avolle\Filterable\Test\TestCase\View\Helper;

use Avolle\Filterable\Test\RequestTrait;
use Avolle\Filterable\View\Helper\FilterHelper;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * FilterHelper Test Case
 */
class FilterHelperTest extends TestCase
{
    use RequestTrait;

    /**
     * Test subject
     *
     * @var \Avolle\Filterable\View\Helper\FilterHelper
     */
    protected FilterHelper $Filter;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $request = $this->makeRequest();
        $view = new View($request);
        $this->Filter = new FilterHelper($view);

        $routeBuilder = Router::createRouteBuilder('/');
        $routeBuilder->connect('/{controller}/{action}/*');
        Router::setRequest($request);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Filter);

        parent::tearDown();
    }

    /**
     * Test link method
     * No prior filters applied. Link should add requested filter
     *
     * @return void
     * @uses \Avolle\Filterable\View\Helper\FilterHelper::link
     */
    public function testLinkWithoutPriorFilter(): void
    {
        $actual = $this->Filter->link('Title', 'field', 'value');
        $expected = [
            'a' => ['href' => $this->replaceBrackets('/tools/index?filter[0]=field&amp;value[0]=value')],
            'Title',
            '/a',
        ];
        $this->assertHtml($expected, $actual);
    }

    /**
     * Test link method
     * Prior filter applied, and the requested link is same as prior filter. Link should remove filter
     *
     * @return void
     * @uses \Avolle\Filterable\View\Helper\FilterHelper::link
     */
    public function testLinkWithPriorFilter(): void
    {
        $request = $this->makeRequest('/', ['filter' => ['field'], 'value' => ['value']]);
        $view = new View($request);
        $this->Filter = new FilterHelper($view);

        $actual = $this->Filter->link('Title', 'field', 'value');
        $expected = [
            'a' => ['href' => '/tools/index'],
            'Title',
            '/a',
        ];
        $this->assertHtml($expected, $actual);
    }

    /**
     * Test link method
     * Prior filter applied, but the requested link is not the same as prior filter. Link should append filter
     *
     * @return void
     * @uses \Avolle\Filterable\View\Helper\FilterHelper::link
     */
    public function testLinkWithPriorFilterButAddAnother(): void
    {
        $request = $this->makeRequest('/', ['filter' => ['location'], 'value' => ['firstLocation']]);
        $view = new View($request);
        $this->Filter = new FilterHelper($view);

        $actual = $this->Filter->link('Title', 'type', 'firstType');
        $expected = [
            'a' => ['href' => $this->replaceBrackets('/tools/index?filter[0]=location&amp;filter[1]=type&amp;value[0]=firstLocation&amp;value[1]=firstType')],
            'Title',
            '/a',
        ];
        $this->assertHtml($expected, $actual);
    }

    /**
     * Test link method
     * Two prior filters applied, and the requested link is the same as one of the filters.
     * Link should remove that filter
     *
     * @return void
     * @uses \Avolle\Filterable\View\Helper\FilterHelper::link
     */
    public function testLinkWithPriorFiltersButRemoveOne(): void
    {
        $request = $this->makeRequest('/', ['filter' => ['location', 'type'], 'value' => ['firstLocation', 'firstType']]);
        $view = new View($request);
        $this->Filter = new FilterHelper($view);

        $actual = $this->Filter->link('Title', 'type', 'firstType');
        $expected = [
            'a' => ['href' => $this->replaceBrackets('/tools/index?filter[0]=location&amp;value[0]=firstLocation')],
            'Title',
            '/a',
        ];
        $this->assertHtml($expected, $actual);
    }

    /**
     * Test link method
     * Link does not remove unrelated query string (?something=else).
     *
     * @return void
     * @uses \Avolle\Filterable\View\Helper\FilterHelper::link()
     */
    public function testLinkDoesNotRemoveUnrelatedQueryString(): void
    {
        $request = $this->makeRequest(
            '/',
            ['something' => 'else', 'filter' => ['location', 'type'], 'value' => ['firstLocation', 'firstType']],
        );
        $view = new View($request);
        $this->Filter = new FilterHelper($view);

        $actual = $this->Filter->link('Title', 'type', 'firstType');
        $expected = [
            'a' => ['href' => $this->replaceBrackets('/tools/index?something=else&amp;filter[0]=location&amp;value[0]=firstLocation')],
            'Title',
            '/a',
        ];
        $this->assertHtml($expected, $actual);
    }

    /**
     * Test isCurrentFilter method
     *
     * @return void
     * @uses \Avolle\Filterable\View\Helper\FilterHelper::isCurrentFilter()
     */
    public function testIsCurrentFilter(): void
    {
        // No filter applied. Should be false
        $this->assertFalse($this->Filter->isCurrentFilter('type', 'firstType'));

        // One filter applied, and both filter field and filter value matches the one asked in method. Should be true
        $request = $this->makeRequest('/', ['filter' => ['location'], 'value' => ['firstLocation']]);
        $view = new View($request);
        $this->Filter = new FilterHelper($view);
        $this->assertTrue($this->Filter->isCurrentFilter('location', 'firstLocation'));

        // Multiple filters applied, and filter field and filter value matches one asked in method. Should be true
        $request = $this->makeRequest('/', ['filter' => ['location', 'type'], 'value' => ['firstLocation', 'firstType']]);
        $view = new View($request);
        $this->Filter = new FilterHelper($view);
        $this->assertTrue($this->Filter->isCurrentFilter('location', 'firstLocation'));

        // Multiple filters applied. Filter field matches one of the filters, but the corresponding value matches
        // the other filter field. Should  be false
        $request = $this->makeRequest('/', ['filter' => ['location', 'type'], 'value' => ['firstLocation', 'firstType']]);
        $view = new View($request);
        $this->Filter = new FilterHelper($view);
        $this->assertFalse($this->Filter->isCurrentFilter('location', 'firstType'));

        // One filter applied, but not the one asked in method. Should be false
        $request = $this->makeRequest('/', ['filter' => ['location'], 'value' => ['firstLocation']]);
        $view = new View($request);
        $this->Filter = new FilterHelper($view);
        $this->assertFalse($this->Filter->isCurrentFilter('type', 'firstType'));

        // One filter applied, but only the filter value matches the one asked in method. Should be false
        $request = $this->makeRequest('/', ['filter' => ['location'], 'value' => ['firstLocation']]);
        $view = new View($request);
        $this->Filter = new FilterHelper($view);
        $this->assertFalse($this->Filter->isCurrentFilter('location', 'secondLocation'));

        // One filter applied, but only the filter field matches the one asked in method. Should be false
        $request = $this->makeRequest('/', ['filter' => ['location'], 'value' => ['firstLocation']]);
        $view = new View($request);
        $this->Filter = new FilterHelper($view);
        $this->assertFalse($this->Filter->isCurrentFilter('type', 'firstLocation'));
    }

    /**
     * Replace brackets [ and ] with the urlencoded equivalent
     *
     * @param string $string
     * @return string
     */
    protected function replaceBrackets(string $string): string
    {
        return str_replace(
            ['[', ']'],
            [urlencode('['), urlencode(']')],
            $string,
        );
    }
}
