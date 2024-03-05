<?php

declare(strict_types=1);

namespace CygnusUy\FormSecurity;

final class FormChecker
{
    private array $formData;
    private ?ConfigInterface $config;
    private ?array $errors;

    /**
     * @var HandlerInterface[]|null
     */
    private ?array $checkers;

    public function __construct(array $formData, ?ConfigInterface $initialConfig = null)
    {
        $this->formData = $formData;
        $this->config = $initialConfig;
        $this->errors = [];
        $this->checkers = [];
    }

    public function setFormData(array $formData): self
    {
        $this->formData = $formData;

        return $this;
    }

    public function addCheckerHandler(string $key, HandlerInterface $handler): self
    {
        if (!$this->checkers) {
            $this->checkers = [];
        }

        $this->checkers[$key] = $handler;

        return $this;
    }

    public function getRequiredEntries(): array
    {
        $requiredEntries = [];

        if ($this->checkers) {

            foreach ($this->checkers as $key => $handler) {

                $requiredEntries = array_merge($requiredEntries, $handler->getRequiredEntries());
            }
        }

        return $requiredEntries;
    }

    public function isSubmitted(): bool
    {
        $submitted = false;

        if ($this->checkers) {

            foreach ($this->checkers as $key => $handler) {

                $this->errors[$key] = !empty($this->errors[$key]) ? $this->errors[$key] : [];

                try {

                    if ($handler) {

                        $submitted = $handler->run($this->formData);
                    }
                } catch (\Throwable $th) {

                    $submitted = $th->getCode() === HandlerInterface::NOT_ENABLED_CODE;

                    $this->errors[$key]['code'] = $th->getCode();
                    $this->errors[$key]['msg'] = $th->getMessage();
                }

                if (!$submitted) {
                    break;
                }
            }
        } else {

            return true;
        }

        return $submitted;
    }

    public function getErrorMessages(): array
    {
        return $this->errors;
    }
}
