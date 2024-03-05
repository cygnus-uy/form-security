<?php

declare(strict_types=1);

namespace CygnusUy\FormSecurity\Tests;

use CygnusUy\FormSecurity\FormChecker;
use CygnusUy\FormSecurity\HandlerCSRFAttack;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class FormSecurityTest extends TestCase
{
    private string $CSRF_ATTACK_ENABLE;

    private function init()
    {
        $this->CSRF_ATTACK_ENABLE = getenv('CSRF_ATTACK_ENABLE') ? getenv('CSRF_ATTACK_ENABLE') : (isset($_ENV['CSRF_ATTACK_ENABLE']) ? $_ENV['CSRF_ATTACK_ENABLE'] : null);

        $this->assertNotEmpty($this->CSRF_ATTACK_ENABLE);
    }

    /**
     * @runInSeparateProcess
     */
    public function testForm()
    {
        $this->init();

        $formChecker = new FormChecker([]);
        $tokenId = 'token_';
        $formChecker->addCheckerHandler(
            HandlerCSRFAttack::class,
            new HandlerCSRFAttack(
                null,
                [
                    'namespace' => 'test_',
                    'tokenId' => $tokenId,
                    'storage' => $this->createMock(TokenStorageInterface::class),
                ]
            )
        );
        $requiredEntries = $formChecker->getRequiredEntries();

        $this->assertNotEmpty($formChecker);

        $this->assertIsArray($requiredEntries);

        foreach ($requiredEntries as $key => $value) {

            $formData = [$key => $value];
            $formChecker->setFormData($formData);
            $isSubmitted = $formChecker->isSubmitted();

            $this->assertTrue($isSubmitted);
        }
    }
}
