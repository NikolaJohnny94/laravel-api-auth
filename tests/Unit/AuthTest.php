<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mockery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    public function test_login_successful()
    {
        // Arrange: Mock the User model and Hash facade
        $mockedUser = Mockery::mock('alias:App\Models\User');
        $mockedUser->password = Hash::make('password'); // Set the password property
        $mockedUser->shouldReceive('where')->with('email', 'test@example.com')->andReturnSelf();
        $mockedUser->shouldReceive('first')->andReturn($mockedUser);
        $mockedUser->shouldReceive('createToken')->andReturn((object) ['plainTextToken' => 'mocked_token']);

        Hash::shouldReceive('check')->with('password', $mockedUser->password)->andReturn(true);

        $request = Request::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $controller = new AuthController();

        // Act: Call the login method
        $response = $controller->login($request);
        $responseData = json_decode($response->getContent(), true);

        // Assert: Check the response
        $this->assertEquals(201, $response->status());
        $this->assertEquals(true, $responseData['success']);
        $this->assertEquals('mocked_token', $responseData['token']);
    }

    public function test_login_returns_invalid_credentials()
    {
        // Arrange: Mock the User model and Hash facade
        $mockedUser = Mockery::mock('alias:App\Models\User');
        $mockedUser->password = Hash::make('password'); // Set the password property
        $mockedUser->shouldReceive('where')->with('email', 'test@example.com')->andReturnSelf();
        $mockedUser->shouldReceive('first')->andReturn($mockedUser);

        Hash::shouldReceive('check')->with('wrong_password', $mockedUser->password)->andReturn(false);

        $request = Request::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $controller = new AuthController();

        // Act: Call the login method
        $response = $controller->login($request);
        $responseData = json_decode($response->getContent(), true);

        // Assert: Check the response
        $this->assertEquals(401, $response->status());
        $this->assertEquals(false, $responseData['success']);
        $this->assertEquals('Invalid credentials', $responseData['message']);
    }

    // Registration 
    // public function test_registration_successful()
    // {
    //     // Arrange: Mock the User model and Hash facade
    //     $mockedUser = Mockery::mock('alias:App\Models\User');
    //     $mockedUser->shouldReceive('create')->andReturn($mockedUser);
    //     $mockedUser->shouldReceive('createToken')->andReturn((object) ['plainTextToken' => 'mocked_token']);

    //     // Create a real Request object
    //     $request = Request::create('/register', 'POST', [
    //         'name' => 'Test User',
    //         'email' => 'test@example.com',
    //         'password' => 'password',
    //         'password_confirmation' => 'password',
    //     ]);

    //     // Mock the Hash facade
    //     Hash::shouldReceive('make')->andReturn('hashed_password');

    //     // Mock the Validator facade to bypass validation
    //     Validator::shouldReceive('make')->andReturnSelf();
    //     Validator::shouldReceive('passes')->andReturn(true);

    //     $controller = new AuthController();

    //     // Act: Call the registration method
    //     $response = $controller->registration($request);
    //     $responseData = json_decode($response->getContent(), true);

    //     // Assert: Check the response
    //     $this->assertEquals(201, $response->status());
    //     $this->assertEquals(true, $responseData['success']);
    //     $this->assertEquals('mocked_token', $responseData['token']);
    // }

    // public function test_registration_returns_validation_error()
    // {
    //     // Arrange: Mock the Request validation
    //     $request = Request::create('/register', 'POST', [
    //         'name' => '',
    //         'email' => 'invalid-email',
    //         'password' => 'password',
    //         'password_confirmation' => 'different_password',
    //     ]);

    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string',
    //         'email' => 'required|string|unique:users,email',
    //         'password' => 'required|string|confirmed',
    //     ]);

    //     $this->expectException(ValidationException::class);

    //     // Manually trigger the validation exception
    //     if ($validator->fails()) {
    //         throw new ValidationException($validator);
    //     }

    //     $controller = new AuthController();

    //     // Act: Call the registration method
    //     $controller->registration($request);
    // }

    // public function test_registration_handles_exception()
    // {
    //     // Arrange: Mock the User model to throw a generic Exception
    //     $mockedUser = Mockery::mock('alias:App\Models\User');
    //     $mockedUser->shouldReceive('create')->andThrow(new Exception('Database error'));

    //     $request = Request::create('/register', 'POST', [
    //         'name' => 'Test User',
    //         'email' => 'test@example.com',
    //         'password' => 'password',
    //         'password_confirmation' => 'password',
    //     ]);

    //     $controller = new AuthController();

    //     // Act: Call the registration method
    //     $response = $controller->registration($request);
    //     $responseData = json_decode($response->getContent(), true);

    //     // Assert: Check the response
    //     $this->assertEquals(500, $response->status());
    //     $this->assertEquals('An error occurred during registration.', $responseData['message']);
    //     $this->assertEquals('Database error', $responseData['error_message']);
    // }

    public function test_registration_successful()
    {
        // Arrange: Mock the User model and Hash facade
        $mockedUser = Mockery::mock('alias:App\Models\User');
        $mockedUser->shouldReceive('create')->andReturn($mockedUser);
        $mockedUser->shouldReceive('createToken')->andReturn((object) ['plainTextToken' => 'mocked_token']);

        // Create a real Request object
        $request = Request::create('/register', 'POST', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Mock the Hash facade
        Hash::shouldReceive('make')->andReturn('hashed_password');

        // Mock the validation to pass
        $mockedRequest = Mockery::mock(Request::class);
        $mockedRequest->shouldReceive('validate')->andReturn([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $controller = new AuthController();

        // Act: Call the registration method
        $response = $controller->registration($mockedRequest);
        $responseData = json_decode($response->getContent(), true);

        // Assert: Check the response
        $this->assertEquals(201, $response->status());
        $this->assertEquals(true, $responseData['success']);
        $this->assertEquals('mocked_token', $responseData['token']);
    }


    public function test_registration_returns_validation_error()
    {
        // Arrange: Mock the Request validation
        $request = Request::create('/register', 'POST', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'different_password',
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $this->expectException(ValidationException::class);

        // Manually trigger the validation exception
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $controller = new AuthController();

        // Act: Call the registration method
        $controller->registration($request);
        // Act: Call the registration method
        $response = $controller->registration($request);
        $responseData = json_decode($response->getContent(), true);

        // Assert: Check the response
        $this->assertEquals(422, $response->status());
        $this->assertEquals('Validation failed while user tried to register', $responseData['message']);
    }
    public function test_registration_handles_exception()
    {
        // Arrange: Mock the User model to throw a generic Exception
        $mockedUser = Mockery::mock('alias:App\Models\User');
        $mockedUser->shouldReceive('create')->andThrow(new Exception('Database error'));

        // Create a real Request object
        $request = Request::create('/register', 'POST', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Mock the Hash facade
        Hash::shouldReceive('make')->andReturn('hashed_password');

        // Mock the validation to pass
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->andReturn([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $controller = new AuthController();

        // Act: Call the registration method
        $response = $controller->registration($request);
        $responseData = json_decode($response->getContent(), true);

        // Assert: Check the response
        $this->assertEquals(500, $response->status());
        $this->assertEquals('An error occurred during registration.', $responseData['message']);
        $this->assertEquals('Database error', $responseData['error_message']);
    }
}
