<?php

namespace App\Security;

use App\Repository\ApiTokenRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiTokenHandler implements AccessTokenHandlerInterface
{
	public function __construct(
		private readonly ApiTokenRepository $apiTokenRepository
	) {
	}

	public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
	{
		$token = $this->apiTokenRepository->findOneBy(['token' => $accessToken]);

		if (!$token) {
			throw new BadCredentialsException();
		}

		if (!$token->getOwnedBy()) {
			throw new LogicException('Token is owned by no-one.');
		}

		if (!$token->isValid()) {
			throw new CustomUserMessageAuthenticationException('Token expired.');
		}

		return new UserBadge($token->getOwnedBy()->getUserIdentifier());
	}
}