<?php

declare(strict_types=1);

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

    public function testForm()
    {
        $this->init();

        $formChecker = new FormChecker([]);

        $formChecker->addCheckerHandler(
            HandlerCSRFAttack::class,
            new HandlerCSRFAttack(null, [
                'namespace' => 'test_',
                'tokenId' => 'token_',
                'storage' => $this->createMock(TokenStorageInterface::class),
            ])
        );
        $requiredEntries = $formChecker->getRequiredEntries();

        $this->assertNotEmpty($formChecker);

        var_dump(['token_' => $requiredEntries['token_']]);
        echo "\n";

        $this->assertIsArray($requiredEntries);

        $formChecker->setFormData(['token_' => $requiredEntries['token_']]);

        $isSubmitted = $formChecker->isSubmitted();
        
        $this->assertFalse($isSubmitted);
    }
}
