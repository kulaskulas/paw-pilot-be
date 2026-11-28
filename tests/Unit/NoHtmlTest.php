<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Rules\NoHtml;
use Illuminate\Support\Facades\Validator;

class NoHtmlTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_plain_text_input_is_accepted(): void
    {
        $validator = Validator::make(
            ['first_name' => 'Karl Cabangon'],
            ['first_name' => [new NoHtml()]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_html_tags_are_rejected(): void
    {
        $validator = Validator::make(
            ['first_name' => '<h1>Karl</h1>'],
            ['first_name' => [new NoHtml()]]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_js_script_are_rejected(): void
    {
        $validator = Validator::make(
            ['first_name' => '<script>alert("eyy")</script>'],
            ['first_name' => [new NoHtml()]]
        );

        $this->assertTrue($validator->fails());
    }
}
