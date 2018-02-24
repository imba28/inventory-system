<?php
use PHPUnit\Framework\TestCase;
use App\Validator;

class ValidatorTest extends TestCase
{
    public function testRequiredValidation()
    {
        $validator = new Validator(
            [
                'name' => 'required'
            ],
            [
                'name' => 'John'
            ]
        );
        $this->assertTrue($validator->passes());

        $validator->setData([]);
        $this->assertTrue($validator->fails());
    }

    public function testEmailValidation()
    {
        $validator = new Validator(
            [
                'email' => 'email'
            ],
            [
                'email' => 'john@doe.com'
            ]
        );
        $this->assertTrue($validator->passes());

        $validator->setData(['email' => 'john@doe']);
        $this->assertTrue($validator->fails());
    }

    public function testMustBeNullValidation()
    {
        $validator = new Validator(
            [
                'test' => 'mustBeNull'
            ],
            [
                'test' => null
            ]
        );
        $this->assertTrue($validator->passes());

        $validator->setData(['test' => 'definitelyNotNull']);
        $this->assertTrue($validator->fails());
    }

    public function testMultipleValidator()
    {
        $validator = new Validator(
            [
                'name' => 'required|max:5',
                'email' => 'email',
                'test' => 'mustBeNull'
            ],
            [
                'name' => 'John',
                'email' => 'john@doe.com',
                'test' => null
            ]
        );

        $this->assertTrue($validator->passes());
    }

    public function testGetErrors()
    {
        $validator = new Validator(
            [
                'name' => 'required|max:5',
                'email' => 'email',
                'test' => 'mustBeNull',
                'password' => 'min:6'
            ],
            [
                'name' => 'John',
                'email' => 'john@doe.com',
                'test' => 'notNull',
                'password' => 'weak'
            ]
        );

        $this->assertTrue($validator->fails());
        $this->assertContains('test', $validator->getFailedFields());
        $this->assertContains('password', $validator->getFailedFields());

        $this->assertNotContains('name', $validator->getFailedFields());
        $this->assertNotContains('email', $validator->getFailedFields());
    }
}
