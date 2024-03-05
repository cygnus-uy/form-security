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

    public const DEFAULT_TOKEN_ID = 'TokenId';

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

        if (!$this->config->has('CSRF_ATTACK_HIDE_TOKEN_ID')) {

            $CSRF_ATTACK_HIDE_TOKEN_ID = getenv('CSRF_ATTACK_HIDE_TOKEN_ID') ? getenv('CSRF_ATTACK_HIDE_TOKEN_ID') : (isset($_ENV['CSRF_ATTACK_HIDE_TOKEN_ID']) ? $_ENV['CSRF_ATTACK_HIDE_TOKEN_ID'] : null);
            $CSRF_ATTACK_HIDE_TOKEN_ID = $CSRF_ATTACK_HIDE_TOKEN_ID == 'true' || $CSRF_ATTACK_HIDE_TOKEN_ID == '1' ? true : false;

            $this->config->set('CSRF_ATTACK_HIDE_TOKEN_ID', $CSRF_ATTACK_HIDE_TOKEN_ID);
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

            $this->sessionStart();

            if ($this->defaultManager) {

                $tokenId = $this->hideTokenId($this->args['tokenId']) ?? self::DEFAULT_TOKEN_ID;
                $token = $this->defaultManager->refreshToken($tokenId);

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

                    $this->sessionStart();

                    $tokenId = isset($_SESSION['tokenId']) ? $_SESSION['tokenId'] : $this->args['tokenId'];
                    $token = new CsrfToken($tokenId, $formData[$tokenId]);

                    return $this->defaultManager->isTokenValid($token);
                }
            }
        } else {

            throw new \Exception("CSRF attack not enabled", self::NOT_ENABLED_CODE);
        }

        return false;
    }

    /**
     * hideTokenId function
     *
     * @param  string $tokenId
     * @return string
     */
    private function hideTokenId(string $tokenId): string
    {
        if ($this->config->get('CSRF_ATTACK_HIDE_TOKEN_ID')) {

            $this->sessionStart();

            $_SESSION['tokenId'] = md5("{$tokenId}-" . time());

            $tokenId = $_SESSION['tokenId'];
        }

        return $tokenId;
    }

    /**
     * sessionStart function
     *
     * @return void
     *
     * @throws \Exception if headers already sent.
     */
    private function sessionStart(): void
    {
        if (php_sapi_name() === 'cli') {

            $headersWereSent = (bool) ini_get('session.use_cookies') && headers_sent($file, $line);
            $headersWereSent && throw new \Exception(
                sprintf(
                    'Session->start(): The headers have already been sent in "%s" at line %d.',
                    $file,
                    $line
                )
            );
        }

        if (\PHP_SESSION_NONE === session_status()) {

            session_start();
        }
    }
}
