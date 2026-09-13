<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Exceptions\ValidationException;
use App\Services\AuthService;
use App\Validators\UserValidator;

final class AuthController extends BaseController
{
    public function __construct(
        View $view,
        private readonly AuthService $auth,
        private readonly UserValidator $validator,
        private readonly Csrf $csrf
    ) {
        parent::__construct($view);
    }

    public function showLogin(Request $request): Response
    {
        return $this->loginForm($request);
    }

    public function login(Request $request): Response
    {
        if (!$this->csrf->validate($request->body('_token'))) {
            return $this->expiredRequest();
        }

        $result = $this->validator->login($request->body());

        if ($result['errors'] !== []) {
            return $this->loginForm($request, $result['errors'], [
                'identifier' => $result['data']['identifier'],
            ], 422);
        }

        $user = $this->auth->attempt(
            $result['data']['identifier'],
            $result['data']['password']
        );

        if ($user === null) {
            return $this->loginForm($request, [
                'credentials' => 'The username/email or password is incorrect.',
            ], [
                'identifier' => $result['data']['identifier'],
            ], 422);
        }

        $this->csrf->rotate();

        return Response::redirect(
            $request->url($user->isAdmin() ? '/admin' : '/home'),
            303
        );
    }

    public function showRegister(Request $request): Response
    {
        return $this->registerForm($request);
    }

    public function register(Request $request): Response
    {
        if (!$this->csrf->validate($request->body('_token'))) {
            return $this->expiredRequest();
        }

        $result = $this->validator->registration($request->body());
        $old = [
            'username' => $result['data']['username'],
            'email' => $result['data']['email'],
            'phone' => $result['data']['phone'] ?? '',
        ];

        if ($result['errors'] !== []) {
            return $this->registerForm($request, $result['errors'], $old, 422);
        }

        try {
            $this->auth->register($result['data']);
        } catch (ValidationException $exception) {
            return $this->registerForm($request, $exception->errors(), $old, 422);
        }

        $this->csrf->rotate();

        return Response::redirect($request->url('/home'), 303);
    }

    public function logout(Request $request): Response
    {
        if (!$this->csrf->validate($request->body('_token'))) {
            return $this->expiredRequest();
        }

        $this->auth->logout();

        return Response::redirect($request->url('/login'), 303);
    }

    /** @param array<string, string> $errors @param array<string, string> $old */
    private function loginForm(
        Request $request,
        array $errors = [],
        array $old = [],
        int $status = 200
    ): Response {
        return $this->render('auth/login', [
            'errors' => $errors,
            'old' => $old,
            'csrfToken' => $this->csrf->token(),
            'loginUrl' => $request->url('/login'),
            'registerUrl' => $request->url('/register'),
            ...$this->assetUrls($request),
        ], $status);
    }

    /** @param array<string, string> $errors @param array<string, string> $old */
    private function registerForm(
        Request $request,
        array $errors = [],
        array $old = [],
        int $status = 200
    ): Response {
        return $this->render('auth/register', [
            'errors' => $errors,
            'old' => $old,
            'csrfToken' => $this->csrf->token(),
            'registerUrl' => $request->url('/register'),
            'loginUrl' => $request->url('/login'),
            ...$this->assetUrls($request),
        ], $status);
    }

    /** @return array{authCssUrl: string, authJsUrl: string, logoUrl: string} */
    private function assetUrls(Request $request): array
    {
        return [
            'authCssUrl' => $request->url('/assets/css/auth.css'),
            'authJsUrl' => $request->url('/assets/js/auth.js'),
            'logoUrl' => $request->url('/assets/images/logo/logo.svg'),
        ];
    }
}
