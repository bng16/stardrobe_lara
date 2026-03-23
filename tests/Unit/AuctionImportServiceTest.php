<?php

namespace Tests\Unit;

use App\Services\AuctionImportService;
use PHPUnit\Framework\TestCase;

class AuctionImportServiceTest extends TestCase
{
    private AuctionImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuctionImportService();
    }

    public function test_parse_json_throws_exception_for_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON string cannot be empty');

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parseJson');
        $method->setAccessible(true);
        $method->invoke($this->service, '');
    }

    public function test_parse_json_throws_exception_for_invalid_json(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON format');

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parseJson');
        $method->setAccessible(true);
        $method->invoke($this->service, 'invalid json {');
    }

    public function test_parse_json_throws_exception_for_non_array_json(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON must decode to an array');

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parseJson');
        $method->setAccessible(true);
        $method->invoke($this->service, '"just a string"');
    }

    public function test_parse_json_returns_array_for_valid_json(): void
    {
        $json = '{"key": "value", "number": 123}';

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parseJson');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $json);

        $this->assertIsArray($result);
        $this->assertEquals('value', $result['key']);
        $this->assertEquals(123, $result['number']);
    }

    public function test_parse_json_handles_nested_structures(): void
    {
        $json = json_encode([
            'title' => 'Test',
            'creator' => [
                'id' => '123',
                'name' => 'John Doe',
            ],
            'bids' => [
                ['amount' => 100],
                ['amount' => 200],
            ],
        ]);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parseJson');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $json);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('creator', $result);
        $this->assertArrayHasKey('bids', $result);
        $this->assertCount(2, $result['bids']);
    }
}
