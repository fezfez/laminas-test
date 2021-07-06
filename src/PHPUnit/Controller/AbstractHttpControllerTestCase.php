<?php

declare(strict_types=1);

namespace Laminas\Test\PHPUnit\Controller;

use ArrayIterator;
use Laminas\Dom\Document;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Test\PHPUnit\Constraint\HasRedirectConstraint;
use Laminas\Test\PHPUnit\Constraint\IsRedirectedRouteNameConstraint;
use PHPUnit\Framework\ExpectationFailedException;

use function count;
use function implode;
use function preg_match;
use function sprintf;

abstract class AbstractHttpControllerTestCase extends AbstractControllerTestCase
{
    /**
     * XPath namespaces
     *
     * @var array
     */
    protected $xpathNamespaces = [];

    /**
     * Get response header by key
     *
     * @return HeaderInterface|false
     */
    protected function getResponseHeader(string $header)
    {
        return $this->getResponse()->getHeaders()->get($header);
    }

    /**
     * Assert response has the given reason phrase
     */
    public function assertResponseReasonPhrase(string $phrase): void
    {
        $this->assertEquals($phrase, $this->getResponse()->getReasonPhrase());
    }

    /**
     * Assert response header exists
     */
    public function assertHasResponseHeader(string $header): void
    {
        $responseHeader = $this->getResponseHeader($header);
        if (false === $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response header "%s" found',
                $header
            )));
        }
        $this->assertNotEquals(false, $responseHeader);
    }

    /**
     * Assert response header does not exist
     */
    public function assertNotHasResponseHeader(string $header): void
    {
        $responseHeader = $this->getResponseHeader($header);
        if (false !== $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response header "%s" WAS NOT found',
                $header
            )));
        }
        $this->assertFalse($responseHeader);
    }

    /**
     * Assert response header exists and contains the given string
     */
    public function assertResponseHeaderContains(string $header, string $match): void
    {
        $responseHeader = $this->getResponseHeader($header);
        if (! $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response header, header "%s" doesn\'t exist',
                $header
            )));
        }

        if (! $responseHeader instanceof ArrayIterator) {
            $responseHeader = [$responseHeader];
        }

        $headerMatched = false;

        foreach ($responseHeader as $currentHeader) {
            if ($match === $currentHeader->getFieldValue()) {
                $headerMatched = true;
                break;
            }
        }

        if (! $headerMatched) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response header "%s" exists and contains "%s", actual content is "%s"',
                $header,
                $match,
                $currentHeader->getFieldValue()
            )));
        }

        $this->assertEquals($match, $currentHeader->getFieldValue());
    }

    /**
     * Assert response header exists and contains the given string
     */
    public function assertNotResponseHeaderContains(string $header, string $match): void
    {
        $responseHeader = $this->getResponseHeader($header);
        if (! $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response header, header "%s" doesn\'t exist',
                $header
            )));
        }

        if (! $responseHeader instanceof ArrayIterator) {
            $responseHeader = [$responseHeader];
        }

        foreach ($responseHeader as $currentHeader) {
            if ($match === $currentHeader->getFieldValue()) {
                throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                    'Failed asserting response header "%s" DOES NOT CONTAIN "%s"',
                    $header,
                    $match
                )));
            }
        }

        $this->assertNotEquals($match, $currentHeader->getFieldValue());
    }

    /**
     * Assert response header exists and matches the given pattern
     */
    public function assertResponseHeaderRegex(string $header, string $pattern): void
    {
        $responseHeader = $this->getResponseHeader($header);
        if (! $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response header, header "%s" doesn\'t exist',
                $header
            )));
        }

        if (! $responseHeader instanceof ArrayIterator) {
            $responseHeader = [$responseHeader];
        }

        $headerMatched = false;

        foreach ($responseHeader as $currentHeader) {
            $headerMatched = (bool) preg_match($pattern, $currentHeader->getFieldValue());

            if ($headerMatched) {
                break;
            }
        }

        if (! $headerMatched) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response header "%s" exists and matches regex "%s", actual content is "%s"',
                $header,
                $pattern,
                $currentHeader->getFieldValue()
            )));
        }

        $this->assertTrue($headerMatched);
    }

    /**
     * Assert response header does not exist and/or does not match the given regex
     */
    public function assertNotResponseHeaderRegex(string $header, string $pattern): void
    {
        $responseHeader = $this->getResponseHeader($header);
        if (! $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response header, header "%s" doesn\'t exist',
                $header
            )));
        }

        if (! $responseHeader instanceof ArrayIterator) {
            $responseHeader = [$responseHeader];
        }

        $headerMatched = false;

        foreach ($responseHeader as $currentHeader) {
            $headerMatched = (bool) preg_match($pattern, $currentHeader->getFieldValue());

            if ($headerMatched) {
                throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                    'Failed asserting response header "%s" DOES NOT MATCH regex "%s"',
                    $header,
                    $pattern
                )));
            }
        }

        $this->assertFalse($headerMatched);
    }

    /**
     * Assert that response is a redirect
     */
    public function assertRedirect(): void
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (false === $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(
                'Failed asserting response is a redirect'
            ));
        }
        $this->assertNotEquals(false, $responseHeader);
    }

    /**
     * Assert that response is NOT a redirect
     */
    public function assertNotRedirect(): void
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (false !== $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response is NOT a redirect, actual redirection is "%s"',
                $responseHeader->getFieldValue()
            )));
        }
        $this->assertFalse($responseHeader);
    }

    /**
     * Assert that response redirects to given URL
     */
    public function assertRedirectTo(string $url): void
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (! $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(
                'Failed asserting response is a redirect'
            ));
        }
        if ($url !== $responseHeader->getFieldValue()) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response redirects to "%s", actual redirection is "%s"',
                $url,
                $responseHeader->getFieldValue()
            )));
        }
        $this->assertEquals($url, $responseHeader->getFieldValue());
    }

    /**
     * Assert that response does not redirect to given URL
     */
    public function assertNotRedirectTo(string $url): void
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (! $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(
                'Failed asserting response is a redirect'
            ));
        }
        if ($url === $responseHeader->getFieldValue()) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response redirects to "%s"',
                $url
            )));
        }
        $this->assertNotEquals($url, $responseHeader->getFieldValue());
    }

    /**
     * Assert that response redirects to given route
     */
    final public function assertRedirectToRoute(string $route): void
    {
        self::assertThat(
            $route,
            self::logicalAnd(
                new HasRedirectConstraint($this),
                new IsRedirectedRouteNameConstraint($this)
            )
        );
    }

    /**
     * Assert that response does not redirect to given route
     */
    final public function assertNotRedirectToRoute(string $route): void
    {
        self::assertThat(
            $route,
            self::logicalNot(
                self::logicalAnd(
                    new HasRedirectConstraint($this),
                    new IsRedirectedRouteNameConstraint($this)
                )
            )
        );
    }

    /**
     * Assert that redirect location matches pattern
     */
    public function assertRedirectRegex(string $pattern): void
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (! $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(
                'Failed asserting response is a redirect'
            ));
        }
        if (! preg_match($pattern, $responseHeader->getFieldValue())) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response redirects to URL MATCHING "%s", actual redirection is "%s"',
                $pattern,
                $responseHeader->getFieldValue()
            )));
        }
        $this->assertTrue((bool) preg_match($pattern, $responseHeader->getFieldValue()));
    }

    /**
     * Assert that redirect location does not match pattern
     */
    public function assertNotRedirectRegex(string $pattern): void
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (! $responseHeader) {
            throw new ExpectationFailedException($this->createFailureMessage(
                'Failed asserting response is a redirect'
            ));
        }
        if (preg_match($pattern, $responseHeader->getFieldValue())) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting response DOES NOT redirect to URL MATCHING "%s"',
                $pattern
            )));
        }
        $this->assertFalse((bool) preg_match($pattern, $responseHeader->getFieldValue()));
    }

    /**
     * Register XPath namespaces
     *
     * @param array $xpathNamespaces
     */
    public function registerXpathNamespaces(array $xpathNamespaces): void
    {
        $this->xpathNamespaces = $xpathNamespaces;
    }

    /**
     * Execute a DOM/XPath query
     *
     * @return Document\NodeList
     */
    private function query(string $path, bool $useXpath = false)
    {
        $response = $this->getResponse();
        $document = new Document($response->getContent());

        if ($useXpath) {
            $document->registerXpathNamespaces($this->xpathNamespaces);
        }

        return Document\Query::execute(
            $path,
            $document,
            $useXpath ? Document\Query::TYPE_XPATH : Document\Query::TYPE_CSS
        );
    }

    /**
     * Execute a xpath query
     */
    private function xpathQuery(string $path): Document\NodeList
    {
        return $this->query($path, true);
    }

    /**
     * Count the dom query executed
     */
    private function queryCount(string $path): int
    {
        return count($this->query($path, false));
    }

    /**
     * Count the dom query executed
     */
    private function xpathQueryCount(string $path): int
    {
        return count($this->xpathQuery($path));
    }

    private function queryCountOrxpathQueryCount(string $path, bool $useXpath = false): int
    {
        if ($useXpath) {
            return $this->xpathQueryCount($path);
        }

        return $this->queryCount($path);
    }

    /**
     * Assert against DOM/XPath selection
     */
    private function queryAssertion(string $path, bool $useXpath = false): void
    {
        $match = $this->queryCountOrxpathQueryCount($path, $useXpath);
        if (! $match > 0) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            )));
        }
        $this->assertTrue($match > 0);
    }

    /**
     * Assert against DOM selection
     *
     * @param string $path CSS selector path
     */
    public function assertQuery(string $path): void
    {
        $this->queryAssertion($path, false);
    }

    /**
     * Assert against XPath selection
     *
     * @param string $path XPath path
     */
    public function assertXpathQuery(string $path): void
    {
        $this->queryAssertion($path, true);
    }

    /**
     * Assert against DOM/XPath selection
     *
     * @param string $path CSS selector path
     */
    private function notQueryAssertion(string $path, bool $useXpath = false): void
    {
        $match = $this->queryCountOrxpathQueryCount($path, $useXpath);
        if ($match !== 0) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s DOES NOT EXIST',
                $path
            )));
        }
        $this->assertEquals(0, $match);
    }

    /**
     * Assert against DOM selection
     *
     * @param string $path CSS selector path
     */
    public function assertNotQuery(string $path): void
    {
        $this->notQueryAssertion($path, false);
    }

    /**
     * Assert against XPath selection
     *
     * @param string $path XPath path
     */
    public function assertNotXpathQuery(string $path): void
    {
        $this->notQueryAssertion($path, true);
    }

    /**
     * Assert against DOM/XPath selection; should contain exact number of nodes
     *
     * @param string $path CSS selector path
     * @param int $count Number of nodes that should match
     */
    private function queryCountAssertion(string $path, int $count, bool $useXpath = false): void
    {
        $match = $this->queryCountOrxpathQueryCount($path, $useXpath);
        if ($match !== $count) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s OCCURS EXACTLY %d times, actually occurs %d times',
                $path,
                $count,
                $match
            )));
        }
        $this->assertEquals($match, $count);
    }

    /**
     * Assert against DOM selection; should contain exact number of nodes
     *
     * @param string $path CSS selector path
     * @param int $count Number of nodes that should match
     */
    public function assertQueryCount(string $path, int $count): void
    {
        $this->queryCountAssertion($path, $count, false);
    }

    /**
     * Assert against XPath selection; should contain exact number of nodes
     *
     * @param string $path XPath path
     * @param int $count Number of nodes that should match
     */
    public function assertXpathQueryCount(string $path, int $count): void
    {
        $this->queryCountAssertion($path, $count, true);
    }

    /**
     * Assert against DOM/XPath selection; should NOT contain exact number of nodes
     *
     * @param string $path CSS selector path
     * @param int $count Number of nodes that should NOT match
     */
    private function notQueryCountAssertion(string $path, int $count, bool $useXpath = false): void
    {
        $match = $this->queryCountOrxpathQueryCount($path, $useXpath);
        if ($match === $count) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s DOES NOT OCCUR EXACTLY %d times',
                $path,
                $count
            )));
        }
        $this->assertNotEquals($match, $count);
    }

    /**
     * Assert against DOM selection; should NOT contain exact number of nodes
     *
     * @param string $path CSS selector path
     * @param int $count Number of nodes that should NOT match
     */
    public function assertNotQueryCount(string $path, int $count): void
    {
        $this->notQueryCountAssertion($path, $count, false);
    }

    /**
     * Assert against XPath selection; should NOT contain exact number of nodes
     *
     * @param string $path XPath path
     * @param int $count Number of nodes that should NOT match
     */
    public function assertNotXpathQueryCount(string $path, int $count): void
    {
        $this->notQueryCountAssertion($path, $count, true);
    }

    /**
     * Assert against DOM/XPath selection; should contain at least this number of nodes
     *
     * @param string $path CSS selector path
     * @param int $count Minimum number of nodes that should match
     */
    private function queryCountMinAssertion(string $path, int $count, bool $useXpath = false): void
    {
        $match = $this->queryCountOrxpathQueryCount($path, $useXpath);
        if ($match < $count) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s OCCURS AT LEAST %d times, actually occurs %d times',
                $path,
                $count,
                $match
            )));
        }
        $this->assertTrue($match >= $count);
    }

    /**
     * Assert against DOM selection; should contain at least this number of nodes
     *
     * @param string $path CSS selector path
     * @param int $count Minimum number of nodes that should match
     */
    public function assertQueryCountMin(string $path, int $count): void
    {
        $this->queryCountMinAssertion($path, $count, false);
    }

    /**
     * Assert against XPath selection; should contain at least this number of nodes
     *
     * @param string $path XPath path
     * @param int $count Minimum number of nodes that should match
     */
    public function assertXpathQueryCountMin(string $path, int $count): void
    {
        $this->queryCountMinAssertion($path, $count, true);
    }

    /**
     * Assert against DOM/XPath selection; should contain no more than this number of nodes
     *
     * @param string $path CSS selector path
     * @param int $count Maximum number of nodes that should match
     */
    private function queryCountMaxAssertion(string $path, int $count, bool $useXpath = false): void
    {
        $match = $this->queryCountOrxpathQueryCount($path, $useXpath);
        if ($match > $count) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s OCCURS AT MOST %d times, actually occurs %d times',
                $path,
                $count,
                $match
            )));
        }
        $this->assertTrue($match <= $count);
    }

    /**
     * Assert against DOM selection; should contain no more than this number of nodes
     *
     * @param string $path CSS selector path
     * @param int $count Maximum number of nodes that should match
     */
    public function assertQueryCountMax(string $path, int $count): void
    {
        $this->queryCountMaxAssertion($path, $count, false);
    }

    /**
     * Assert against XPath selection; should contain no more than this number of nodes
     *
     * @param string $path XPath path
     * @param int $count Maximum number of nodes that should match
     */
    public function assertXpathQueryCountMax(string $path, int $count): void
    {
        $this->queryCountMaxAssertion($path, $count, true);
    }

    /**
     * Assert against DOM/XPath selection; node should contain content
     *
     * @param string $path CSS selector path
     * @param string $match content that should be contained in matched nodes
     */
    private function queryContentContainsAssertion(string $path, string $match, bool $useXpath = false): void
    {
        $result = $this->query($path, $useXpath);

        if ($result->count() === 0) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            )));
        }

        $nodeValues = [];

        foreach ($result as $node) {
            if ($node->nodeValue === $match) {
                $this->assertEquals($match, $node->nodeValue);
                return;
            }

            $nodeValues[] = $node->nodeValue;
        }

        throw new ExpectationFailedException($this->createFailureMessage(sprintf(
            'Failed asserting node denoted by %s CONTAINS content "%s", Contents: [%s]',
            $path,
            $match,
            implode(',', $nodeValues)
        )));
    }

    /**
     * Assert against DOM selection; node should contain content
     *
     * @param string $path CSS selector path
     * @param string $match content that should be contained in matched nodes
     */
    public function assertQueryContentContains(string $path, string $match): void
    {
        $this->queryContentContainsAssertion($path, $match, false);
    }

    /**
     * Assert against XPath selection; node should contain content
     *
     * @param string $path XPath path
     * @param string $match content that should be contained in matched nodes
     */
    public function assertXpathQueryContentContains(string $path, string $match): void
    {
        $this->queryContentContainsAssertion($path, $match, true);
    }

    /**
     * Assert against DOM/XPath selection; node should NOT contain content
     *
     * @param string $path CSS selector path
     * @param string $match content that should NOT be contained in matched nodes
     */
    private function notQueryContentContainsAssertion(string $path, string $match, bool $useXpath = false): void
    {
        $result = $this->query($path, $useXpath);
        if ($result->count() === 0) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            )));
        }
        foreach ($result as $node) {
            if ($node->nodeValue === $match) {
                throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                    'Failed asserting node DENOTED BY %s DOES NOT CONTAIN content "%s"',
                    $path,
                    $match
                )));
            }
        }
        $currentValue = $node->nodeValue;
        $this->assertNotEquals($currentValue, $match);
    }

    /**
     * Assert against DOM selection; node should NOT contain content
     *
     * @param string $path CSS selector path
     * @param string $match content that should NOT be contained in matched nodes
     */
    public function assertNotQueryContentContains(string $path, string $match): void
    {
        $this->notQueryContentContainsAssertion($path, $match, false);
    }

    /**
     * Assert against XPath selection; node should NOT contain content
     *
     * @param string $path XPath path
     * @param string $match content that should NOT be contained in matched nodes
     */
    public function assertNotXpathQueryContentContains(string $path, string $match): void
    {
        $this->notQueryContentContainsAssertion($path, $match, true);
    }

    /**
     * Assert against DOM/XPath selection; node should match content
     *
     * @param string $path CSS selector path
     * @param string $pattern Pattern that should be contained in matched nodes
     */
    private function queryContentRegexAssertion(string $path, string $pattern, bool $useXpath = false): void
    {
        $result = $this->query($path, $useXpath);
        if ($result->count() === 0) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            )));
        }

        $found      = false;
        $nodeValues = [];

        foreach ($result as $node) {
            $nodeValues[] = $node->nodeValue;
            if (preg_match($pattern, $node->nodeValue)) {
                $found = true;
                break;
            }
        }

        if (! $found) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node denoted by %s CONTAINS content MATCHING "%s", actual content is "%s"',
                $path,
                $pattern,
                implode('', $nodeValues)
            )));
        }

        $this->assertTrue($found);
    }

    /**
     * Assert against DOM selection; node should match content
     *
     * @param string $path CSS selector path
     * @param string $pattern Pattern that should be contained in matched nodes
     */
    public function assertQueryContentRegex(string $path, string $pattern): void
    {
        $this->queryContentRegexAssertion($path, $pattern, false);
    }

    /**
     * Assert against XPath selection; node should match content
     *
     * @param string $path XPath path
     * @param string $pattern Pattern that should be contained in matched nodes
     */
    public function assertXpathQueryContentRegex(string $path, string $pattern): void
    {
        $this->queryContentRegexAssertion($path, $pattern, true);
    }

    /**
     * Assert against DOM/XPath selection; node should NOT match content
     *
     * @param string $path CSS selector path
     * @param string $pattern pattern that should NOT be contained in matched nodes
     */
    private function notQueryContentRegexAssertion(string $path, string $pattern, bool $useXpath = false): void
    {
        $result = $this->query($path, $useXpath);
        if ($result->count() === 0) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            )));
        }
        if (preg_match($pattern, $result->current()->nodeValue)) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting node DENOTED BY %s DOES NOT CONTAIN content MATCHING "%s"',
                $path,
                $pattern
            )));
        }
        $this->assertFalse((bool) preg_match($pattern, $result->current()->nodeValue));
    }

    /**
     * Assert against DOM selection; node should NOT match content
     *
     * @param string $path CSS selector path
     * @param string $pattern pattern that should NOT be contained in matched nodes
     */
    public function assertNotQueryContentRegex(string $path, string $pattern): void
    {
        $this->notQueryContentRegexAssertion($path, $pattern, false);
    }

    /**
     * Assert against XPath selection; node should NOT match content
     *
     * @param string $path XPath path
     * @param string $pattern pattern that should NOT be contained in matched nodes
     */
    public function assertNotXpathQueryContentRegex(string $path, string $pattern): void
    {
        $this->notQueryContentRegexAssertion($path, $pattern, true);
    }
}
