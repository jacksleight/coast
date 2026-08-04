<?php

namespace Coast\Test;

use Coast\Request;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    protected function request($headers = [])
    {
        $request = new Request;
        foreach ($headers as $name => $value) {
            $request->header($name, $value);
        }

        return $request;
    }

    public function test_accepts()
    {
        $request = $this->request(['Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8']);
        $this->assertEquals(
            ['text/html', 'application/xhtml+xml', 'application/xml', '*/*'],
            $request->accepts()
        );

        $request = $this->request(['Accept' => 'text/html;q=0.3, application/json;q=0.9, text/plain;q=0.6']);
        $this->assertEquals(
            ['application/json', 'text/plain', 'text/html'],
            $request->accepts()
        );

        $request = $this->request(['Accept' => 'TEXT/HTML , application/json']);
        $this->assertEquals(['text/html', 'application/json'], $request->accepts());

        $this->assertEquals([], $this->request()->accepts());
    }

    public function test_prefers()
    {
        $types = ['text/html', 'application/json'];

        $request = $this->request(['Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8']);
        $this->assertEquals('text/html', $request->prefers($types));

        $request = $this->request(['Accept' => 'application/json, text/html;q=0.5']);
        $this->assertEquals('application/json', $request->prefers($types));

        $request = $this->request(['Accept' => 'text/*']);
        $this->assertEquals('text/html', $request->prefers($types));

        $request = $this->request(['Accept' => '*/*']);
        $this->assertNull($request->prefers($types));

        $request = $this->request(['Accept' => 'image/png']);
        $this->assertNull($request->prefers($types));

        $this->assertNull($this->request()->prefers($types));
    }

    public function test_is_document()
    {
        $request = $this->request(['Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8']);
        $this->assertTrue($request->isDocument());

        $request = $this->request(['Sec-Fetch-Dest' => 'document', 'Accept' => '*/*']);
        $this->assertTrue($request->isDocument());

        $request = $this->request(['Sec-Fetch-Dest' => 'empty', 'Accept' => '*/*']);
        $this->assertFalse($request->isDocument());

        $request = $this->request(['Accept' => '*/*']);
        $this->assertFalse($request->isDocument());

        $request = $this->request(['Accept' => 'application/json']);
        $this->assertFalse($request->isDocument());

        $request = $this->request(['Accept' => 'application/json, text/html;q=0.5']);
        $this->assertFalse($request->isDocument());

        $this->assertFalse($this->request()->isDocument());
    }
}
