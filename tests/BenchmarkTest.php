<?php

namespace Hrw\Tests;

class BenchmarkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function decideNode()
    {
        $keys = [];
        for ($i = 0; $i < 100000; $i++) {
            $keys[] = md5($i);
        }

        $nodes = ['node-a', 'node-b', 'node-c', 'node-d', 'node-e',];

        $result = $this->rendezvous($keys, $nodes);

        $this->assertSame(count($nodes), count($result));

        $border = count($keys) * 0.02;
        $expectCount = count($keys) / count($nodes);
        foreach ($result as $node => $values) {
            $this->assertTrue(count($values) >= $expectCount - $border && count($values) <= $expectCount + $border);
        }
    }

    /**
     * @test
     */
    public function modifyNodeNumber()
    {
        $keys = [];
        for ($i = 0; $i < 100000; $i++) {
            $keys[] = md5($i);
        }

        $nodes = ['node-a', 'node-b', 'node-c', 'node-d', 'node-e',];
        $result = $this->rendezvous($keys, $nodes);
        $this->assertSame(count($nodes), count($result));

        $nodes2 = ['node-a', 'node-b', 'node-c', 'node-d', 'node-e', 'node-f'];
        $result2 = $this->rendezvous($keys, $nodes2);
        $this->assertSame(count($nodes2), count($result2));

        $this->reportDiff($result, $result2);
    }

    private function rendezvous($keys, $nodes)
    {
        $result = [];
        $service = new \Hrw\Hrw($nodes);
        foreach ($keys as $key) {
            $node = $service->decideNode($key);
            if (!isset($result[$node])) {
                $result[$node] = [];
            }
            $result[$node][] = $key;
        }

        return $result;
    }

    private function report($result)
    {
        ksort($result);
        foreach ($result as $k => $d) {
            echo $k . ':' . count($d) . "\n";
        }
    }

    private function reportDiff(array $result, array $result2)
    {
        $nodes = array_keys($result);
        $intersects = [];

        foreach ($nodes as $node) {
            $intersects[$node] = count(array_intersect($result[$node], $result2[$node]));
        }
        ksort($intersects);
        echo "\n--- node(5) keys ---\n";
        $this->report($result);
        echo "\n--- node(7) keys ---\n";
        $this->report($result2);

        echo "\n--- node(5) vs node(7) intersect keys ---\n";
        foreach ($intersects as $node => $count) {
            echo $node . ":" . $count . "\n";
        }
    }
}
