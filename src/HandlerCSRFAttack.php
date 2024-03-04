<?php

declare(strict_types=1);

namespace CygnusUy\FormSecurity;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class HandlerCSRFAttack implements HandlerInterface
{
    private $manager;
    private ?CsrfTokenManagerInterface $defaultManager;

    private ConfigInterface $config;
    private ?array $args;

    public function __construct(?callable $manager = null, ?array $args = null, ?ConfigInterface $initialConfig = null)
    {
        $this->manager = $manager;
        $this->config = new Config([]);
        $this->config = $initialConfig ?? $this->config;
        $this->args = $args;
        $this->defaultManager = null;

        if (!$this->config->has('CSRF_ATTACK_ENABLE')) {

            $CSRF_ATTACK_ENABLE = getenv('CSRF_ATTACK_ENABLE') ? getenv('CSRF_ATTACK_ENABLE') : (isset($_ENV['CSRF_ATTACK_ENABLE']) ? $_ENV['CSRF_ATTACK_ENABLE'] : null);
            $CSRF_ATTACK_ENABLE = $CSRF_ATTACK_ENABLE == 'true' || $CSRF_ATTACK_ENABLE == '1' ? true : false;

            $this->config->set('CSRF_ATTACK_ENABLE', $CSRF_ATTACK_ENABLE);
        }

        if ($this->config->get('CSRF_ATTACK_ENABLE')) {

            $namespace = $this->args['namespace'] ?? time() . "_";
            $generator = $this->args['generator'] ?? null;
            $storage = $this->args['storage'] ?? null;

            $this->defaultManager = new CsrfTokenManager($generator, $storage, $namespace);
        }
    }

    public function getRequiredEntries(): array
    {
        if ($this->config->get('CSRF_ATTACK_ENABLE')) {

            if ($this->defaultManager) {

                $tokenId = $this->args['tokenId'] ?? 'tokenId';
                $token = $this->defaultManager->getToken($tokenId);

                return [
                    $token->getId() => $token->getValue()
                ];
            } elseif (isset($this->args['requiredEntries'])) {

                return $this->args['requiredEntries'];
            }
        }

        return [];
    }

    public function run(array $formData): bool
    {
        $manager = $this->manager;

        if ($this->config->get('CSRF_ATTACK_ENABLE')) {

            if ($manager) {

                return $manager($formData, $this->args);
            } else {

                if ($this->defaultManager) {

                    $tokenId = $this->args['tokenId'] ?? 'tokenId';

                    return $this->defaultManager->isTokenValid(new CsrfToken($tokenId, $formData[$tokenId]));
                }
            }
        } else {

            throw new \Exception("CSRF attack not enabled", self::NOT_ENABLED_CODE);
        }

        return false;
    }
}
