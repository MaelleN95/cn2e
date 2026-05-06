<?php

namespace App\Security;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class FormSecurityGuard
{
    public function __construct(
        private RateLimiterFactory $defaultLimiter
    ) {}

    public function check(Request $request, FormInterface $form, string $scope = 'default'): void
    {
        $this->checkRateLimit($request, $scope);
        $this->checkHoneypot($form);
        $this->checkTiming($request);
        $this->checkHeaders($request);
    }

    private function checkRateLimit(Request $request, string $scope): void
    {
        $limiter = $this->defaultLimiter->create(
            $scope . ':' . ($request->getClientIp() ?? 'unknown')
        );

        if (!$limiter->consume(1)->isAccepted()) {
            throw new AccessDeniedHttpException('Too many requests');
        }
    }

    private function checkHoneypot(FormInterface $form): void
    {
        $honeypot = $form->get('website')->getData();

        if (!empty($honeypot)) {
            throw new AccessDeniedHttpException('Bot detected');
        }
    }

    private function checkTiming(Request $request): void
    {
        $start = $request->request->get('_form_start_time');

        if (!$start) {
            throw new AccessDeniedHttpException('Missing form timestamp');
        }

        $elapsed = time() - (int) $start;

        if ($elapsed < 2) {
            throw new AccessDeniedHttpException('Form submitted too fast');
        }
    }

    private function checkHeaders(Request $request): void
    {
        $ua = $request->headers->get('User-Agent');

        if (!$ua || strlen($ua) < 10) {
            throw new AccessDeniedHttpException('Invalid request');
        }

        if (preg_match('/curl|wget|python|bot|spider|scrapy/i', $ua)) {
            throw new AccessDeniedHttpException('Automated client blocked');
        }
    }
}